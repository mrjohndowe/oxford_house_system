<?php
declare(strict_types=1);

/**
 * Oxford House Financial Audit
 * - Single-file PHP app
 * - Fillable layout modeled after the supplied audit sheet
 * - Auto-saves to MySQL
 * - History dropdown by completed date
 * - Reload prior audits for editing
 *
 * Logo path expected by user:
 *   ../images/oxford_house_logo.png
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

/* =========================
   DB CONNECTION
========================= */
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS oxford_financial_audits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) DEFAULT NULL,
    date_completed DATE DEFAULT NULL,
    bank_statement_ending_date DATE DEFAULT NULL,
    bank_statement_ending_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_past_due_bills DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    savings_account_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_outstanding_ees DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    deposits_json LONGTEXT NULL,
    checks_json LONGTEXT NULL,
    treasurer_signature VARCHAR(255) DEFAULT NULL,
    comptroller_signature VARCHAR(255) DEFAULT NULL,
    president_signature VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date_completed (date_completed),
    INDEX idx_house_name (house_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function moneyToFloat(mixed $value): float
{
    $clean = preg_replace('/[^0-9.\-]/', '', (string)$value);
    if ($clean === '' || $clean === '-' || $clean === '.' || $clean === '-.') {
        return 0.0;
    }
    return round((float)$clean, 2);
}

function normalizeDate(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : null;
}

function postedArray(array $source, string $key, int $defaultRows = 0): array
{
    $val = $source[$key] ?? [];
    if (!is_array($val)) {
        $val = [];
    }
    if ($defaultRows > 0 && count($val) < $defaultRows) {
        $val = array_pad($val, $defaultRows, '');
    }
    return $val;
}

function buildRows(array $a, array $b = [], array $c = [], array $d = []): array
{
    $max = max(count($a), count($b), count($c), count($d));
    $rows = [];
    for ($i = 0; $i < $max; $i++) {
        $row = [];
        if ($a !== []) $row[] = (string)($a[$i] ?? '');
        if ($b !== []) $row[] = (string)($b[$i] ?? '');
        if ($c !== []) $row[] = (string)($c[$i] ?? '');
        if ($d !== []) $row[] = (string)($d[$i] ?? '');
        $rows[] = $row;
    }
    return $rows;
}

function decodeRows(?string $json, int $columns, int $defaultRows): array
{
    $rows = json_decode((string)$json, true);
    if (!is_array($rows)) {
        $rows = [];
    }
    $normalized = [];
    foreach ($rows as $row) {
        $row = is_array($row) ? array_values($row) : [];
        $row = array_pad($row, $columns, '');
        $normalized[] = array_slice($row, 0, $columns);
    }
    while (count($normalized) < $defaultRows) {
        $normalized[] = array_fill(0, $columns, '');
    }
    return $normalized;
}

function sumColumn(array $rows, int $index): float
{
    $sum = 0.0;
    foreach ($rows as $row) {
        $sum += moneyToFloat($row[$index] ?? 0);
    }
    return round($sum, 2);
}

/* =========================
   AJAX AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'autosave')) {
    header('Content-Type: application/json; charset=utf-8');

    $id = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
    $houseName = trim((string)($_POST['house_name'] ?? ''));
    $dateCompleted = normalizeDate($_POST['date_completed'] ?? null);
    $endingDate = normalizeDate($_POST['bank_statement_ending_date'] ?? null);
    $endingBalance = moneyToFloat($_POST['bank_statement_ending_balance'] ?? 0);
    $pastDueBills = moneyToFloat($_POST['total_past_due_bills'] ?? 0);
    $savingsBalance = moneyToFloat($_POST['savings_account_balance'] ?? 0);
    $outstandingEES = moneyToFloat($_POST['total_outstanding_ees'] ?? 0);

    $depositAmounts = postedArray($_POST, 'deposit_amount', 8);
    $deposits = buildRows($depositAmounts);

    $checkNumbers = postedArray($_POST, 'check_number', 12);
    $checkPurpose = postedArray($_POST, 'check_purpose', 12);
    $checkDates   = postedArray($_POST, 'check_date', 12);
    $checkAmounts = postedArray($_POST, 'check_amount', 12);
    $checks = buildRows($checkNumbers, $checkPurpose, $checkDates, $checkAmounts);

    $treasurerSignature = trim((string)($_POST['treasurer_signature'] ?? ''));
    $comptrollerSignature = trim((string)($_POST['comptroller_signature'] ?? ''));
    $presidentSignature = trim((string)($_POST['president_signature'] ?? ''));

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE oxford_financial_audits SET
                house_name = :house_name,
                date_completed = :date_completed,
                bank_statement_ending_date = :bank_statement_ending_date,
                bank_statement_ending_balance = :bank_statement_ending_balance,
                total_past_due_bills = :total_past_due_bills,
                savings_account_balance = :savings_account_balance,
                total_outstanding_ees = :total_outstanding_ees,
                deposits_json = :deposits_json,
                checks_json = :checks_json,
                treasurer_signature = :treasurer_signature,
                comptroller_signature = :comptroller_signature,
                president_signature = :president_signature
             WHERE id = :id'
        );
        $stmt->execute([
            ':house_name' => $houseName,
            ':date_completed' => $dateCompleted,
            ':bank_statement_ending_date' => $endingDate,
            ':bank_statement_ending_balance' => $endingBalance,
            ':total_past_due_bills' => $pastDueBills,
            ':savings_account_balance' => $savingsBalance,
            ':total_outstanding_ees' => $outstandingEES,
            ':deposits_json' => json_encode($deposits, JSON_UNESCAPED_UNICODE),
            ':checks_json' => json_encode($checks, JSON_UNESCAPED_UNICODE),
            ':treasurer_signature' => $treasurerSignature,
            ':comptroller_signature' => $comptrollerSignature,
            ':president_signature' => $presidentSignature,
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO oxford_financial_audits (
                house_name, date_completed, bank_statement_ending_date,
                bank_statement_ending_balance, total_past_due_bills,
                savings_account_balance, total_outstanding_ees,
                deposits_json, checks_json,
                treasurer_signature, comptroller_signature, president_signature
            ) VALUES (
                :house_name, :date_completed, :bank_statement_ending_date,
                :bank_statement_ending_balance, :total_past_due_bills,
                :savings_account_balance, :total_outstanding_ees,
                :deposits_json, :checks_json,
                :treasurer_signature, :comptroller_signature, :president_signature
            )'
        );
        $stmt->execute([
            ':house_name' => $houseName,
            ':date_completed' => $dateCompleted,
            ':bank_statement_ending_date' => $endingDate,
            ':bank_statement_ending_balance' => $endingBalance,
            ':total_past_due_bills' => $pastDueBills,
            ':savings_account_balance' => $savingsBalance,
            ':total_outstanding_ees' => $outstandingEES,
            ':deposits_json' => json_encode($deposits, JSON_UNESCAPED_UNICODE),
            ':checks_json' => json_encode($checks, JSON_UNESCAPED_UNICODE),
            ':treasurer_signature' => $treasurerSignature,
            ':comptroller_signature' => $comptrollerSignature,
            ':president_signature' => $presidentSignature,
        ]);
        $id = (int)$pdo->lastInsertId();
    }

    echo json_encode([
        'ok' => true,
        'record_id' => $id,
        'saved_at' => date('Y-m-d H:i:s'),
    ]);
    exit;
}

/* =========================
   LOAD RECORD / DEFAULTS
========================= */
$defaultDepositRows = 8;
$defaultCheckRows = 12;
$recordId = isset($_GET['load']) ? (int)$_GET['load'] : 0;

$form = [
    'id' => 0,
    'house_name' => '',
    'date_completed' => '',
    'bank_statement_ending_date' => '',
    'bank_statement_ending_balance' => '0.00',
    'total_past_due_bills' => '0.00',
    'savings_account_balance' => '0.00',
    'total_outstanding_ees' => '0.00',
    'deposits' => array_fill(0, $defaultDepositRows, ['']),
    'checks' => array_fill(0, $defaultCheckRows, ['', '', '', '']),
    'treasurer_signature' => '',
    'comptroller_signature' => '',
    'president_signature' => '',
];

if ($recordId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM oxford_financial_audits WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $recordId]);
    $row = $stmt->fetch();
    if ($row) {
        $form = [
            'id' => (int)$row['id'],
            'house_name' => (string)($row['house_name'] ?? ''),
            'date_completed' => (string)($row['date_completed'] ?? ''),
            'bank_statement_ending_date' => (string)($row['bank_statement_ending_date'] ?? ''),
            'bank_statement_ending_balance' => number_format((float)$row['bank_statement_ending_balance'], 2, '.', ''),
            'total_past_due_bills' => number_format((float)$row['total_past_due_bills'], 2, '.', ''),
            'savings_account_balance' => number_format((float)$row['savings_account_balance'], 2, '.', ''),
            'total_outstanding_ees' => number_format((float)$row['total_outstanding_ees'], 2, '.', ''),
            'deposits' => decodeRows($row['deposits_json'] ?? null, 1, $defaultDepositRows),
            'checks' => decodeRows($row['checks_json'] ?? null, 4, $defaultCheckRows),
            'treasurer_signature' => (string)($row['treasurer_signature'] ?? ''),
            'comptroller_signature' => (string)($row['comptroller_signature'] ?? ''),
            'president_signature' => (string)($row['president_signature'] ?? ''),
        ];
    }
}

$history = $pdo->query(
    'SELECT id, house_name, date_completed, updated_at
     FROM oxford_financial_audits
     ORDER BY COALESCE(date_completed, DATE(updated_at)) DESC, id DESC'
)->fetchAll();

$depositTotal = sumColumn($form['deposits'], 0);
$checkTotal   = sumColumn($form['checks'], 3);
$endingBalance = moneyToFloat($form['bank_statement_ending_balance']);
$balanceAfterAudit = round($endingBalance + $depositTotal - $checkTotal, 2);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oxford House Financial Audit</title>
    <style>
        @page { size: letter; margin: 0.45in; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #e9ecef; font-family: Arial, Helvetica, sans-serif; color: #111; }
        body { padding: 18px; }
        .toolbar {
            max-width: 980px; margin: 0 auto 14px auto; background: #fff; border: 1px solid #bdbdbd;
            padding: 12px 14px; display: flex; gap: 12px; align-items: end; flex-wrap: wrap;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .toolbar .field { display: flex; flex-direction: column; gap: 5px; }
        .toolbar label { font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .toolbar select, .toolbar button {
            height: 36px; border: 1px solid #7b7b7b; background: #fff; padding: 0 10px; font-size: 14px;
        }
        .toolbar button { cursor: pointer; font-weight: 700; }
        .save-state { margin-left: auto; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .sheet {
            width: 8.5in; min-height: 11in; margin: 0 auto; background: #fff; padding: 0.18in 0.22in 0.2in 0.22in;
            box-shadow: 0 5px 18px rgba(0,0,0,.12); border: 1px solid #bdbdbd;
        }
        .logo-row {
            display: grid; grid-template-columns: 84px 1fr; gap: 10px; align-items: start; margin-bottom: 4px;
        }
        .logo-box img { max-width: 78px; max-height: 78px; display: block; }
        .top-right { display: flex; justify-content: space-between; align-items: start; gap: 14px; }
        .title-block { text-align: center; flex: 1; }
        .title-main { font-size: 26px; font-weight: 800; letter-spacing: .5px; line-height: 1; }
        .house-line { display: inline-flex; align-items: flex-end; gap: 6px; font-size: 24px; font-weight: 800; margin-top: 2px; }
        .inline-line { border: 0; border-bottom: 1px solid #000; outline: 0; background: transparent; padding: 0 3px 1px; height: 24px; }
        .inline-line.house { width: 300px; font-size: 20px; font-weight: 700; text-align: center; }
        .date-completed { font-size: 14px; font-weight: 700; white-space: nowrap; margin-top: 10px; }
        .date-completed input { width: 140px; height: 22px; border: 0; border-bottom: 1px solid #000; background: transparent; }
        .top-grid {
            display: grid; grid-template-columns: 1.2fr .9fr; gap: 10px; align-items: start; margin-top: 2px;
        }
        .instructions {
            font-size: 12px; line-height: 1.24; padding-right: 8px;
        }
        .instructions ol { margin: 0; padding-left: 18px; }
        .figures-box { font-size: 13px; }
        .figures-title { text-align: center; font-weight: 800; margin-bottom: 6px; }
        .fig-row { display: grid; grid-template-columns: 1fr 100px; align-items: end; gap: 8px; margin-bottom: 3px; }
        .fig-row label { font-weight: 700; }
        .money-wrap { position: relative; }
        .money-wrap::before {
            content: '$'; position: absolute; left: 6px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #111;
        }
        .money-input {
            width: 100%; height: 24px; border: 0; border-bottom: 1px solid #000; padding: 2px 4px 2px 18px; background: transparent; text-align: right;
        }
        .small-date-row { margin-top: 10px; display: grid; grid-template-columns: 1fr 120px; gap: 8px; align-items: end; }
        .small-date-row label, .end-balance-row label { font-weight: 700; font-size: 12px; }
        .date-input, .text-input {
            width: 100%; height: 24px; border: 0; border-bottom: 1px solid #000; background: transparent; padding: 2px 4px;
        }
        .end-balance-row { display: grid; grid-template-columns: 1fr 120px; gap: 8px; align-items: end; margin-top: 6px; }
        .section-title {
            text-align: center; font-size: 15px; font-weight: 800; margin: 10px 0 4px;
        }
        table { width: 100%; border-collapse: collapse; }
        .deposit-table th, .deposit-table td,
        .check-table th, .check-table td,
        .math-table th, .math-table td {
            border: 1px solid #000; padding: 0; vertical-align: middle;
        }
        .deposit-table th, .check-table th, .math-table th {
            font-size: 12px; font-weight: 800; text-align: center; padding: 4px 5px;
        }
        .deposit-table td, .check-table td, .math-table td { height: 26px; }
        .cell-input, .cell-date {
            width: 100%; height: 25px; border: 0; padding: 3px 6px; background: transparent; font-size: 13px;
        }
        .cell-money {
            width: 100%; height: 25px; border: 0; padding: 3px 6px 3px 16px; background: transparent; text-align: right; font-size: 13px;
        }
        .money-cell { position: relative; }
        .money-cell::before {
            content: '$'; position: absolute; left: 5px; top: 50%; transform: translateY(-50%); font-weight: 700; font-size: 13px;
        }
        .totals-row td { font-weight: 800; font-size: 13px; }
        .totals-label { text-align: right; padding-right: 8px !important; }
        .totals-value { text-align: right; padding-right: 6px !important; }
        .math-wrap { margin-top: 8px; }
        .math-equation {
            display: grid; grid-template-columns: 1.2fr 38px 1.2fr 38px 1.2fr 38px 1.2fr; gap: 6px; align-items: center; margin-top: 4px;
        }
        .math-label { font-size: 12px; font-weight: 800; text-align: center; }
        .math-sign { font-size: 18px; font-weight: 800; text-align: center; }
        .math-box { border: 1px solid #000; height: 32px; position: relative; }
        .math-box::before {
            content: '$'; position: absolute; left: 6px; top: 50%; transform: translateY(-50%); font-weight: 700;
        }
        .math-box input {
            width: 100%; height: 100%; border: 0; padding: 5px 6px 5px 18px; text-align: right; background: transparent; font-size: 14px;
        }
        .math-box.output { background: #f7f7f7; }
        .signatures {
            margin-top: 18px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: end;
        }
        .sig { text-align: center; }
        .sig input {
            width: 100%; height: 30px; border: 0; border-bottom: 1px solid #000; text-align: center; background: transparent; font-family: "Segoe Script", "Brush Script MT", cursive; font-size: 22px;
        }
        .sig label { display: block; margin-top: 4px; font-size: 12px; font-weight: 700; }
        .note-top { font-size: 12px; font-weight: 700; margin-bottom: 4px; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; border: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="field" style="min-width: 300px;">
            <label for="history_id">History by Date</label>
            <select id="history_id">
                <option value="">Select saved audit...</option>
                <?php foreach ($history as $item): ?>
                    <option value="<?= (int)$item['id'] ?>" <?= $form['id'] === (int)$item['id'] ? 'selected' : '' ?>>
                        <?= h(($item['date_completed'] ?: substr((string)$item['updated_at'], 0, 10)) . ' - ' . ($item['house_name'] ?: 'Oxford House')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" id="newRecordBtn">New Audit</button>
        <button type="button" onclick="window.print()">Print</button>
        <div class="save-state" id="saveState">Ready</div>
    </div>

    <form id="auditForm" class="sheet" method="post" action="">
        <input type="hidden" name="record_id" id="record_id" value="<?= (int)$form['id'] ?>">
        <input type="hidden" name="action" value="autosave">

        <div class="note-top">Post completed audit in the house. Make a copy for the Chapter Meeting</div>

        <div class="logo-row">
            <div class="logo-box">
                <img src="../images/oxford_house_logo.png" alt="Oxford House Logo" onerror="this.style.display='none';">
            </div>
            <div>
                <div class="top-right">
                    <div class="title-block">
                        <div class="house-line">OXFORD HOUSE - <input class="inline-line house" type="text" name="house_name" value="<?= h($form['house_name']) ?>"></div>
                        <div class="title-main">FINANCIAL AUDIT</div>
                    </div>
                    <div class="date-completed">DATE COMPLETED: <input type="date" name="date_completed" value="<?= h($form['date_completed']) ?>"></div>
                </div>
            </div>
        </div>

        <div class="top-grid">
            <div class="instructions">
                <ol>
                    <li>The Treasurer, Comptroller, &amp; President do the audit together.</li>
                    <li>Use the bank statement, checkbook, Financial Status Report, and Meeting Minutes for references.</li>
                    <li>Document all deposits and checks that are not listed on the most recent bank statement.</li>
                    <li>Highlight or circle the check numbers on the check stubs for all checks that are listed on the bank statement.</li>
                    <li>Count the checks in the checkbook, by check number, to ensure no checks are missing.</li>
                    <li>The final audited balance should match the ending balance on the check stub of the last check written.</li>
                    <li>If fraud, theft, or embezzlement has occured, immediately notify the house, OHI staff, the bank, and the police.</li>
                </ol>
            </div>

            <div class="figures-box">
                <div class="figures-title">OTHER FIGURES</div>
                <div class="fig-row">
                    <label>Total of past due bills</label>
                    <div class="money-wrap"><input class="money-input" type="text" name="total_past_due_bills" value="<?= h($form['total_past_due_bills']) ?>"></div>
                </div>
                <div class="fig-row">
                    <label>Savings Account balance</label>
                    <div class="money-wrap"><input class="money-input" type="text" name="savings_account_balance" value="<?= h($form['savings_account_balance']) ?>"></div>
                </div>
                <div class="fig-row">
                    <label>Total outstanding EES</label>
                    <div class="money-wrap"><input class="money-input" type="text" name="total_outstanding_ees" value="<?= h($form['total_outstanding_ees']) ?>"></div>
                </div>
                <div class="small-date-row">
                    <label>BANK STATEMENT ENDING DATE</label>
                    <input class="date-input" type="date" name="bank_statement_ending_date" value="<?= h($form['bank_statement_ending_date']) ?>">
                </div>
                <div class="end-balance-row">
                    <label>BANK STATEMENT ENDING BALANCE</label>
                    <div class="money-wrap"><input class="money-input" type="text" name="bank_statement_ending_balance" id="bank_statement_ending_balance" value="<?= h($form['bank_statement_ending_balance']) ?>"></div>
                </div>
            </div>
        </div>

        <div class="section-title">DEPOSITS NOT ON STATEMENT</div>
        <table class="deposit-table">
            <thead>
                <tr>
                    <th>Amount $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($form['deposits'] as $i => $row): ?>
                    <tr>
                        <td class="money-cell"><input class="cell-money deposit-amount" type="text" name="deposit_amount[]" value="<?= h($row[0] ?? '') ?>"></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="totals-row">
                    <td class="totals-value money-cell"><input class="cell-money" type="text" id="deposit_total" value="<?= number_format($depositTotal, 2, '.', '') ?>" readonly></td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">CHECKS NOT ON STATEMENT</div>
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Check #</th>
                    <th style="width: 45%;">To Whom / Purpose</th>
                    <th style="width: 18%;">Date</th>
                    <th style="width: 22%;">Amount $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($form['checks'] as $i => $row): ?>
                    <tr>
                        <td><input class="cell-input" type="text" name="check_number[]" value="<?= h($row[0] ?? '') ?>"></td>
                        <td><input class="cell-input" type="text" name="check_purpose[]" value="<?= h($row[1] ?? '') ?>"></td>
                        <td><input class="cell-date" type="date" name="check_date[]" value="<?= h($row[2] ?? '') ?>"></td>
                        <td class="money-cell"><input class="cell-money check-amount" type="text" name="check_amount[]" value="<?= h($row[3] ?? '') ?>"></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="totals-row">
                    <td colspan="3" class="totals-label">TOTAL:</td>
                    <td class="totals-value money-cell"><input class="cell-money" type="text" id="check_total" value="<?= number_format($checkTotal, 2, '.', '') ?>" readonly></td>
                </tr>
            </tbody>
        </table>

        <div class="math-wrap">
            <div class="math-equation">
                <div>
                    <div class="math-label">Bank Statement<br>Ending Balance</div>
                    <div class="math-box"><input type="text" id="math_ending_balance" value="<?= number_format($endingBalance, 2, '.', '') ?>" readonly></div>
                </div>
                <div class="math-sign">+</div>
                <div>
                    <div class="math-label">Deposits Total</div>
                    <div class="math-box"><input type="text" id="math_deposit_total" value="<?= number_format($depositTotal, 2, '.', '') ?>" readonly></div>
                </div>
                <div class="math-sign">-</div>
                <div>
                    <div class="math-label">Checks Total</div>
                    <div class="math-box"><input type="text" id="math_check_total" value="<?= number_format($checkTotal, 2, '.', '') ?>" readonly></div>
                </div>
                <div class="math-sign">=</div>
                <div>
                    <div class="math-label">Balance After Audit</div>
                    <div class="math-box output"><input type="text" id="balance_after_audit" value="<?= number_format($balanceAfterAudit, 2, '.', '') ?>" readonly></div>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sig">
                <input type="text" name="treasurer_signature" value="<?= h($form['treasurer_signature']) ?>">
                <label>Treasurer Signature</label>
            </div>
            <div class="sig">
                <input type="text" name="comptroller_signature" value="<?= h($form['comptroller_signature']) ?>">
                <label>Comptroller Signature</label>
            </div>
            <div class="sig">
                <input type="text" name="president_signature" value="<?= h($form['president_signature']) ?>">
                <label>President Signature</label>
            </div>
        </div>
    </form>

    <script>
        const form = document.getElementById('auditForm');
        const saveState = document.getElementById('saveState');
        const recordId = document.getElementById('record_id');
        const historySelect = document.getElementById('history_id');
        const newRecordBtn = document.getElementById('newRecordBtn');
        let saveTimer = null;
        let isSaving = false;

        function toMoney(value) {
            const n = parseFloat(String(value).replace(/[^0-9.-]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function fmt(value) {
            return toMoney(value).toFixed(2);
        }

        function recalcTotals() {
            let depositTotal = 0;
            document.querySelectorAll('.deposit-amount').forEach(el => depositTotal += toMoney(el.value));

            let checkTotal = 0;
            document.querySelectorAll('.check-amount').forEach(el => checkTotal += toMoney(el.value));

            const endingBalance = toMoney(document.getElementById('bank_statement_ending_balance').value);
            const afterAudit = endingBalance + depositTotal - checkTotal;

            document.getElementById('deposit_total').value = fmt(depositTotal);
            document.getElementById('check_total').value = fmt(checkTotal);
            document.getElementById('math_ending_balance').value = fmt(endingBalance);
            document.getElementById('math_deposit_total').value = fmt(depositTotal);
            document.getElementById('math_check_total').value = fmt(checkTotal);
            document.getElementById('balance_after_audit').value = fmt(afterAudit);
        }

        async function autosave() {
            if (isSaving) return;
            isSaving = true;
            saveState.textContent = 'Saving...';

            try {
                const formData = new FormData(form);
                const response = await fetch(window.location.pathname + window.location.search.replace(/([?&])load=\d+/, ''), {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.ok) {
                    recordId.value = data.record_id;
                    saveState.textContent = 'Saved ' + data.saved_at;
                    if (!historySelect.querySelector(`option[value="${data.record_id}"]`)) {
                        const dateCompleted = form.querySelector('[name="date_completed"]').value || data.saved_at.substring(0, 10);
                        const houseName = form.querySelector('[name="house_name"]').value || 'Oxford House';
                        const opt = document.createElement('option');
                        opt.value = data.record_id;
                        opt.textContent = `${dateCompleted} - ${houseName}`;
                        historySelect.prepend(opt);
                    }
                    historySelect.value = data.record_id;
                } else {
                    saveState.textContent = 'Save failed';
                }
            } catch (e) {
                saveState.textContent = 'Save failed';
            } finally {
                isSaving = false;
            }
        }

        function queueSave() {
            recalcTotals();
            saveState.textContent = 'Unsaved changes';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosave, 700);
        }

        form.addEventListener('input', queueSave);
        form.addEventListener('change', queueSave);

        historySelect.addEventListener('change', function () {
            if (this.value) {
                window.location.href = window.location.pathname + '?load=' + encodeURIComponent(this.value);
            }
        });

        newRecordBtn.addEventListener('click', function () {
            window.location.href = window.location.pathname;
        });

        recalcTotals();
    </script>
</body>
</html>
