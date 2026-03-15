<?php
/**
 * Treasurer - State Audit
 * Single-file PHP/MySQL form closely matching the uploaded Treasurer - State Audit sheet.
 * Source reference: Treasurer - State Audit.pdf
 * - Fillable form
 * - Auto-save to MySQL
 * - History dropdown by audit date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 * - Signature blocks (typed names + drawn signatures)
 *
 * DB Config:
 * $dbHost = 'localhost';
 * $dbName = 'secretary';
 * $dbUser = 'secretary';
 * $dbPass = 'EK@rL4mIpKgU5b)P';
 */

declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_money(mixed $value): string
{
    $v = trim((string)$value);
    if ($v === '') {
        return '';
    }
    $v = str_replace([',', '$', ' '], '', $v);
    if (!is_numeric($v)) {
        return '';
    }
    return number_format((float)$v, 2, '.', '');
}

function money_float(mixed $value): float
{
    $v = normalize_money($value);
    return $v === '' ? 0.0 : (float)$v;
}

function normalize_date_ymd(?string $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : '';
}

function format_date_mdy(?string $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('m/d/Y', $ts) : '';
}

function empty_check_row(): array
{
    return [
        'check_no' => '',
        'purpose' => '',
        'date' => '',
        'amount' => '',
    ];
}

function empty_deposit_row(): array
{
    return [
        'date' => '',
        'amount' => '',
    ];
}

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
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS treasurer_state_audits (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        audit_date DATE NOT NULL,
        bank_statement_ending_date DATE NULL,
        bank_statement_ending_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        total_outstanding_loans DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        total_outstanding_fines DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        total_outstanding_dues DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        bank_statement_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        total_deposits DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        total_checks DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        audited_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        completed_audit_email_sent TINYINT(1) NOT NULL DEFAULT 0,
        treasurer_name VARCHAR(255) NOT NULL DEFAULT '',
        chair_name VARCHAR(255) NOT NULL DEFAULT '',
        treasurer_signature LONGTEXT NULL,
        chair_signature LONGTEXT NULL,
        checks_json LONGTEXT NULL,
        deposits_json LONGTEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_audit_date (audit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

if (isset($_GET['history'])) {
    $stmt = $pdo->query("SELECT id, audit_date FROM treasurer_state_audits ORDER BY audit_date DESC, id DESC");
    json_response(['records' => $stmt->fetchAll()]);
}

if (isset($_GET['load_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM treasurer_state_audits WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['load_id']]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['ok' => false, 'message' => 'Record not found.'], 404);
    }

    $row['checks'] = json_decode((string)($row['checks_json'] ?? '[]'), true) ?: [];
    $row['deposits'] = json_decode((string)($row['deposits_json'] ?? '[]'), true) ?: [];
    json_response(['ok' => true, 'record' => $row]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (($_POST['action'] ?? '') === 'autosave')) {
    $auditDate = normalize_date_ymd((string)($_POST['audit_date'] ?? ''));
    if ($auditDate === '') {
        json_response(['ok' => false, 'message' => 'Audit date is required.'], 422);
    }

    $checks = $_POST['checks'] ?? [];
    $deposits = $_POST['deposits'] ?? [];
    if (!is_array($checks)) {
        $checks = [];
    }
    if (!is_array($deposits)) {
        $deposits = [];
    }

    $cleanChecks = [];
    $totalChecks = 0.0;
    foreach ($checks as $row) {
        $item = [
            'check_no' => trim((string)($row['check_no'] ?? '')),
            'purpose' => trim((string)($row['purpose'] ?? '')),
            'date' => normalize_date_ymd((string)($row['date'] ?? '')),
            'amount' => normalize_money($row['amount'] ?? ''),
        ];
        if ($item['check_no'] !== '' || $item['purpose'] !== '' || $item['date'] !== '' || $item['amount'] !== '') {
            $cleanChecks[] = $item;
            $totalChecks += money_float($item['amount']);
        }
    }

    $cleanDeposits = [];
    $totalDeposits = 0.0;
    foreach ($deposits as $row) {
        $item = [
            'date' => normalize_date_ymd((string)($row['date'] ?? '')),
            'amount' => normalize_money($row['amount'] ?? ''),
        ];
        if ($item['date'] !== '' || $item['amount'] !== '') {
            $cleanDeposits[] = $item;
            $totalDeposits += money_float($item['amount']);
        }
    }

    $bankStatementBalance = money_float($_POST['bank_statement_balance'] ?? '');
    $auditedBalance = $bankStatementBalance + $totalDeposits - $totalChecks;

    $payload = [
        ':audit_date' => $auditDate,
        ':bank_statement_ending_date' => normalize_date_ymd((string)($_POST['bank_statement_ending_date'] ?? '')) ?: null,
        ':bank_statement_ending_balance' => normalize_money($_POST['bank_statement_ending_balance'] ?? '0') ?: '0.00',
        ':total_outstanding_loans' => normalize_money($_POST['total_outstanding_loans'] ?? '0') ?: '0.00',
        ':total_outstanding_fines' => normalize_money($_POST['total_outstanding_fines'] ?? '0') ?: '0.00',
        ':total_outstanding_dues' => normalize_money($_POST['total_outstanding_dues'] ?? '0') ?: '0.00',
        ':bank_statement_balance' => number_format($bankStatementBalance, 2, '.', ''),
        ':total_deposits' => number_format($totalDeposits, 2, '.', ''),
        ':total_checks' => number_format($totalChecks, 2, '.', ''),
        ':audited_balance' => number_format($auditedBalance, 2, '.', ''),
        ':completed_audit_email_sent' => !empty($_POST['completed_audit_email_sent']) ? 1 : 0,
        ':treasurer_name' => trim((string)($_POST['treasurer_name'] ?? '')),
        ':chair_name' => trim((string)($_POST['chair_name'] ?? '')),
        ':treasurer_signature' => trim((string)($_POST['treasurer_signature'] ?? '')),
        ':chair_signature' => trim((string)($_POST['chair_signature'] ?? '')),
        ':checks_json' => json_encode(array_values($cleanChecks), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':deposits_json' => json_encode(array_values($cleanDeposits), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];

    $sql = "INSERT INTO treasurer_state_audits (
                audit_date,
                bank_statement_ending_date,
                bank_statement_ending_balance,
                total_outstanding_loans,
                total_outstanding_fines,
                total_outstanding_dues,
                bank_statement_balance,
                total_deposits,
                total_checks,
                audited_balance,
                completed_audit_email_sent,
                treasurer_name,
                chair_name,
                treasurer_signature,
                chair_signature,
                checks_json,
                deposits_json
            ) VALUES (
                :audit_date,
                :bank_statement_ending_date,
                :bank_statement_ending_balance,
                :total_outstanding_loans,
                :total_outstanding_fines,
                :total_outstanding_dues,
                :bank_statement_balance,
                :total_deposits,
                :total_checks,
                :audited_balance,
                :completed_audit_email_sent,
                :treasurer_name,
                :chair_name,
                :treasurer_signature,
                :chair_signature,
                :checks_json,
                :deposits_json
            )
            ON DUPLICATE KEY UPDATE
                bank_statement_ending_date = VALUES(bank_statement_ending_date),
                bank_statement_ending_balance = VALUES(bank_statement_ending_balance),
                total_outstanding_loans = VALUES(total_outstanding_loans),
                total_outstanding_fines = VALUES(total_outstanding_fines),
                total_outstanding_dues = VALUES(total_outstanding_dues),
                bank_statement_balance = VALUES(bank_statement_balance),
                total_deposits = VALUES(total_deposits),
                total_checks = VALUES(total_checks),
                audited_balance = VALUES(audited_balance),
                completed_audit_email_sent = VALUES(completed_audit_email_sent),
                treasurer_name = VALUES(treasurer_name),
                chair_name = VALUES(chair_name),
                treasurer_signature = VALUES(treasurer_signature),
                chair_signature = VALUES(chair_signature),
                checks_json = VALUES(checks_json),
                deposits_json = VALUES(deposits_json)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($payload);

    $idStmt = $pdo->prepare('SELECT id, updated_at FROM treasurer_state_audits WHERE audit_date = ? LIMIT 1');
    $idStmt->execute([$auditDate]);
    $saved = $idStmt->fetch() ?: ['id' => null, 'updated_at' => null];

    json_response([
        'ok' => true,
        'message' => 'Audit saved successfully.',
        'record_id' => $saved['id'],
        'updated_at' => $saved['updated_at'],
        'totals' => [
            'total_deposits' => number_format($totalDeposits, 2, '.', ''),
            'total_checks' => number_format($totalChecks, 2, '.', ''),
            'audited_balance' => number_format($auditedBalance, 2, '.', ''),
        ]
    ]);
}

$checks = array_fill(0, 8, empty_check_row());
$deposits = array_fill(0, 8, empty_deposit_row());
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer - State Audit</title>
    <style>
        :root {
            --page-width: 8.5in;
            --page-min-height: 11in;
            --border: #111;
            --text: #111;
            --bg: #fff;
            --line: #333;
            --muted: #555;
        }

        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #d7d7d7;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            width: var(--page-width);
            margin: 16px auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .toolbar .group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .toolbar button,
        .toolbar select,
        .toolbar input {
            height: 36px;
            padding: 6px 10px;
            font-size: 14px;
        }

        .status {
            font-size: 13px;
            color: #0f5132;
            min-height: 18px;
        }

        .page {
            width: var(--page-width);
            min-height: var(--page-min-height);
            margin: 10px auto 28px;
            background: var(--bg);
            box-shadow: 0 0 0 1px #bbb, 0 4px 18px rgba(0,0,0,.08);
            padding: 18px 24px 20px;
            position: relative;
        }

        .header {
            display: grid;
            grid-template-columns: 90px 1fr 180px;
            align-items: start;
            gap: 10px;
        }

        .logo {
            width: 78px;
            height: 78px;
            object-fit: contain;
        }

        .title-block {
            text-align: center;
            padding-top: 2px;
        }

        .title-block .kicker {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .title-block .title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: .4px;
            line-height: 1.05;
            margin-top: 4px;
        }

        .completed-date {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            padding-top: 6px;
            font-size: 13px;
            font-weight: 700;
        }

        .completed-date label {
            white-space: nowrap;
            margin-right: 6px;
        }

        .date-line-input {
            border: none;
            border-bottom: 1px solid #111;
            font-size: 13px;
            padding: 1px 2px;
            width: 125px;
            text-align: center;
            background: transparent;
        }

        .section {
            margin-top: 10px;
        }

        .row-2 {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 16px;
        }

        .instruction {
            font-size: 12px;
            line-height: 1.35;
            margin: 0;
            padding-left: 18px;
        }

        .instruction li { margin-bottom: 3px; }

        .other-figures {
            border: 1px solid #111;
            padding: 6px 8px 8px;
        }

        .other-figures h3 {
            margin: 0 0 6px;
            text-align: center;
            font-size: 16px;
            letter-spacing: .4px;
        }

        .money-row {
            display: grid;
            grid-template-columns: 1fr 100px;
            align-items: end;
            gap: 8px;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .money-row label { font-weight: 700; }

        .money-input,
        .text-input,
        .small-date,
        .small-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #111;
            background: transparent;
            padding: 1px 2px;
            font-size: 12px;
            height: 22px;
        }

        .bank-strip {
            display: grid;
            grid-template-columns: 1fr 160px 1fr 120px;
            gap: 8px;
            align-items: center;
            margin-top: 10px;
            font-size: 12px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .audit-table-wrap {
            margin-top: 12px;
        }

        .check-table th,
        .check-table td,
        .deposit-table th,
        .deposit-table td {
            border: 1px solid #111;
            padding: 0;
            font-size: 12px;
            height: 26px;
        }

        .check-table thead th,
        .deposit-table thead th {
            font-weight: 700;
            text-align: center;
            padding: 3px 4px;
            background: #fafafa;
        }

        .cell-input {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            padding: 4px 5px;
            font-size: 12px;
        }

        .table-title {
            text-align: center;
            font-weight: 800;
            font-size: 15px;
            margin: 4px 0 4px;
            letter-spacing: .3px;
        }

        .split {
            display: grid;
            grid-template-columns: 1.3fr .9fr;
            gap: 16px;
            align-items: start;
        }

        .total-box {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .total-box .total-readonly {
            display: inline-block;
            min-width: 95px;
            border-bottom: 1px solid #111;
            text-align: right;
            padding: 2px 2px 1px;
        }

        .equation {
            margin-top: 8px;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .equation-grid {
            margin-top: 6px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            align-items: end;
        }

        .eq-box {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        .eq-box .display {
            display: block;
            width: 100%;
            border-bottom: 1px solid #111;
            min-height: 24px;
            padding: 3px 2px 2px;
            text-align: center;
            font-size: 13px;
        }

        .email-line {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
        }

        .checkbox {
            width: 16px;
            height: 16px;
            border: 1px solid #111;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .checkbox input {
            width: 16px;
            height: 16px;
            margin: 0;
        }

        .signature-section {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }

        .signature-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .signature-pad {
            width: 100%;
            height: 120px;
            border: 1px solid #111;
            background: #fff;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .sig-line {
            border-bottom: 1px solid #111;
            min-height: 26px;
            display: flex;
            align-items: flex-end;
            padding: 2px 2px;
            font-size: 12px;
        }

        .sig-caption {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            margin-top: 2px;
        }

        .footnote {
            margin-top: 8px;
            font-size: 11px;
            color: var(--muted);
        }

        @media print {
            body { background: #fff; }
            .toolbar, .footnote { display: none !important; }
            .page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 0.22in 0.3in;
            }
            @page {
                size: letter portrait;
                margin: 0.25in;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="group">
            <label for="historySelect"><strong>History by Date:</strong></label>
            <select id="historySelect">
                <option value="">Loading history...</option>
            </select>
            <button type="button" id="printBtn">Print</button>
        </div>
        <div class="group">
            <span id="saveStatus" class="status">Ready.</span>
        </div>
    </div>

    <form id="auditForm" class="page" autocomplete="off">
        <div class="header">
            <div>
                <img src="<?= h($logoPath) ?>" alt="Oxford House Logo" class="logo">
            </div>
            <div class="title-block">
                <div class="kicker">STATE ASSOCIATION</div>
                <div class="title">FINANCIAL AUDIT</div>
            </div>
            <div class="completed-date">
                <label for="audit_date">DATE COMPLETED:</label>
                <input type="date" id="audit_date" name="audit_date" class="date-line-input" required>
            </div>
        </div>

        <div class="section row-2">
            <ol class="instruction">
                <li>The Treasurer and Chairperson complete the audit together.</li>
                <li>Use the bank statement, checkbook, Financial Report, and Meeting Minutes for references.</li>
                <li>Document all deposits and checks that are not listed on the most recent bank statement.</li>
                <li>Highlight or circle the check numbers on the check stubs for all checks that are listed on the bank statement.</li>
                <li>Count the checks in the checkbook, by check number, to ensure no checks are missing.</li>
                <li>The final audited balance should match the ending balance on the check stub of the last check written.</li>
                <li>If fraud, theft, or embezzlement has occured, immediately notify OHI staff, the bank, and the police.</li>
            </ol>

            <div class="other-figures">
                <h3>OTHER FIGURES</h3>
                <div class="money-row">
                    <label for="total_outstanding_loans">Total outstanding Loans</label>
                    <input type="text" id="total_outstanding_loans" name="total_outstanding_loans" class="money-input money-field" inputmode="decimal">
                </div>
                <div class="money-row">
                    <label for="total_outstanding_fines">Total outstanding Fines</label>
                    <input type="text" id="total_outstanding_fines" name="total_outstanding_fines" class="money-input money-field" inputmode="decimal">
                </div>
                <div class="money-row">
                    <label for="total_outstanding_dues">Total outstanding Dues</label>
                    <input type="text" id="total_outstanding_dues" name="total_outstanding_dues" class="money-input money-field" inputmode="decimal">
                </div>
                <div class="money-row" style="margin-top:12px; grid-template-columns: 1fr 100px;">
                    <label for="bank_statement_ending_balance">BANK STATEMENT ENDING BALANCE</label>
                    <input type="text" id="bank_statement_ending_balance" name="bank_statement_ending_balance" class="money-input money-field" inputmode="decimal">
                </div>
            </div>
        </div>

        <div class="bank-strip">
            <div>BANK STATEMENT ENDING DATE</div>
            <input type="date" id="bank_statement_ending_date" name="bank_statement_ending_date" class="small-date">
            <div></div>
            <div></div>
        </div>

        <div class="section split audit-table-wrap">
            <div>
                <div class="table-title">CHECKS NOT ON STATEMENT</div>
                <table class="check-table">
                    <thead>
                        <tr>
                            <th style="width: 16%;">Check #</th>
                            <th style="width: 47%;">To Whom / Purpose</th>
                            <th style="width: 17%;">Date</th>
                            <th style="width: 20%;">Amount $</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checks as $i => $row): ?>
                        <tr>
                            <td><input type="text" name="checks[<?= $i ?>][check_no]" class="cell-input"></td>
                            <td><input type="text" name="checks[<?= $i ?>][purpose]" class="cell-input"></td>
                            <td><input type="date" name="checks[<?= $i ?>][date]" class="cell-input"></td>
                            <td><input type="text" name="checks[<?= $i ?>][amount]" class="cell-input money-field check-amount" inputmode="decimal"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total-box">
                    <span>TOTAL: $</span>
                    <span class="total-readonly" id="checksTotalDisplay">0.00</span>
                </div>
            </div>

            <div>
                <div class="table-title">DEPOSITS NOT ON STATEMENT</div>
                <table class="deposit-table">
                    <thead>
                        <tr>
                            <th style="width: 46%;">Date</th>
                            <th style="width: 54%;">Amount $</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deposits as $i => $row): ?>
                        <tr>
                            <td><input type="date" name="deposits[<?= $i ?>][date]" class="cell-input"></td>
                            <td><input type="text" name="deposits[<?= $i ?>][amount]" class="cell-input money-field deposit-amount" inputmode="decimal"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total-box">
                    <span>TOTAL: $</span>
                    <span class="total-readonly" id="depositsTotalDisplay">0.00</span>
                </div>
            </div>
        </div>

        <div class="equation">$ + $ - $ = $</div>
        <div class="equation-grid">
            <div class="eq-box">
                <span class="display" id="eqBankBalance">0.00</span>
                <div>Bank Statement Bal</div>
            </div>
            <div class="eq-box">
                <span class="display" id="eqDeposits">0.00</span>
                <div>Total Deposits</div>
            </div>
            <div class="eq-box">
                <span class="display" id="eqChecks">0.00</span>
                <div>Total of Checks</div>
            </div>
            <div class="eq-box">
                <span class="display" id="eqAuditedBalance">0.00</span>
                <div>Balance After Audit</div>
            </div>
        </div>

        <input type="hidden" name="bank_statement_balance" id="bank_statement_balance" value="0.00">
        <input type="hidden" name="total_deposits" id="total_deposits" value="0.00">
        <input type="hidden" name="total_checks" id="total_checks" value="0.00">
        <input type="hidden" name="audited_balance" id="audited_balance" value="0.00">

        <div class="email-line">
            <span class="checkbox"><input type="checkbox" id="completed_audit_email_sent" name="completed_audit_email_sent" value="1"></span>
            <label for="completed_audit_email_sent">Email completed audit to chapters and officers.</label>
        </div>

        <div class="signature-section">
            <div class="signature-card">
                <canvas id="treasurerPad" class="signature-pad"></canvas>
                <div class="signature-controls">
                    <input type="text" id="treasurer_name" name="treasurer_name" class="text-input" placeholder="Treasurer name">
                    <button type="button" data-clear="treasurer">Clear Signature</button>
                </div>
                <input type="hidden" id="treasurer_signature" name="treasurer_signature">
                <div class="sig-line" id="treasurerSigPreview"></div>
                <div class="sig-caption">Treasurer Signature</div>
            </div>

            <div class="signature-card">
                <canvas id="chairPad" class="signature-pad"></canvas>
                <div class="signature-controls">
                    <input type="text" id="chair_name" name="chair_name" class="text-input" placeholder="Chair name">
                    <button type="button" data-clear="chair">Clear Signature</button>
                </div>
                <input type="hidden" id="chair_signature" name="chair_signature">
                <div class="sig-line" id="chairSigPreview"></div>
                <div class="sig-caption">Chair Signature</div>
            </div>
        </div>

        <div class="footnote">This single file includes its own CREATE TABLE statement, auto-save, history loading, printing support, signature pads, and audit math.</div>
    </form>

    <script>
        const form = document.getElementById('auditForm');
        const saveStatus = document.getElementById('saveStatus');
        const historySelect = document.getElementById('historySelect');
        const printBtn = document.getElementById('printBtn');
        let saveTimer = null;
        let loadingRecord = false;

        function parseMoney(value) {
            const cleaned = String(value || '').replace(/[$,\s]/g, '');
            const num = parseFloat(cleaned);
            return Number.isFinite(num) ? num : 0;
        }

        function formatMoney(value) {
            return parseMoney(value).toFixed(2);
        }

        function updateTotals() {
            const bankBalance = parseMoney(document.getElementById('bank_statement_ending_balance').value);
            let checksTotal = 0;
            let depositsTotal = 0;

            document.querySelectorAll('.check-amount').forEach(el => {
                checksTotal += parseMoney(el.value);
            });
            document.querySelectorAll('.deposit-amount').forEach(el => {
                depositsTotal += parseMoney(el.value);
            });

            const audited = bankBalance + depositsTotal - checksTotal;

            document.getElementById('checksTotalDisplay').textContent = formatMoney(checksTotal);
            document.getElementById('depositsTotalDisplay').textContent = formatMoney(depositsTotal);
            document.getElementById('eqBankBalance').textContent = formatMoney(bankBalance);
            document.getElementById('eqDeposits').textContent = formatMoney(depositsTotal);
            document.getElementById('eqChecks').textContent = formatMoney(checksTotal);
            document.getElementById('eqAuditedBalance').textContent = formatMoney(audited);

            document.getElementById('bank_statement_balance').value = formatMoney(bankBalance);
            document.getElementById('total_deposits').value = formatMoney(depositsTotal);
            document.getElementById('total_checks').value = formatMoney(checksTotal);
            document.getElementById('audited_balance').value = formatMoney(audited);
        }

        function setStatus(message, isError = false) {
            saveStatus.textContent = message;
            saveStatus.style.color = isError ? '#842029' : '#0f5132';
        }

        async function loadHistory() {
            try {
                const res = await fetch('?history=1');
                const data = await res.json();
                historySelect.innerHTML = '<option value="">Select saved date...</option>';
                (data.records || []).forEach(row => {
                    const option = document.createElement('option');
                    option.value = row.id;
                    option.textContent = row.audit_date;
                    historySelect.appendChild(option);
                });
            } catch (err) {
                historySelect.innerHTML = '<option value="">Unable to load history</option>';
            }
        }

        async function saveForm() {
            if (loadingRecord) return;
            const auditDate = document.getElementById('audit_date').value;
            if (!auditDate) {
                setStatus('Enter the date completed to enable auto-save.', true);
                return;
            }

            updateTotals();
            setStatus('Saving...');

            const fd = new FormData(form);
            fd.append('action', 'autosave');

            try {
                const res = await fetch('', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.ok) {
                    setStatus(data.message || 'Save failed.', true);
                    return;
                }
                setStatus('Saved: ' + (data.updated_at || 'just now'));
                await loadHistory();
            } catch (err) {
                setStatus('Save failed. Check database connection or PHP errors.', true);
            }
        }

        function queueSave() {
            if (loadingRecord) return;
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveForm, 700);
        }

        async function loadRecord(id) {
            if (!id) return;
            loadingRecord = true;
            try {
                const res = await fetch('?load_id=' + encodeURIComponent(id));
                const data = await res.json();
                if (!data.ok) {
                    setStatus(data.message || 'Unable to load record.', true);
                    loadingRecord = false;
                    return;
                }
                const r = data.record;
                document.getElementById('audit_date').value = r.audit_date || '';
                document.getElementById('bank_statement_ending_date').value = r.bank_statement_ending_date || '';
                document.getElementById('bank_statement_ending_balance').value = r.bank_statement_ending_balance || '';
                document.getElementById('total_outstanding_loans').value = r.total_outstanding_loans || '';
                document.getElementById('total_outstanding_fines').value = r.total_outstanding_fines || '';
                document.getElementById('total_outstanding_dues').value = r.total_outstanding_dues || '';
                document.getElementById('completed_audit_email_sent').checked = String(r.completed_audit_email_sent) === '1';
                document.getElementById('treasurer_name').value = r.treasurer_name || '';
                document.getElementById('chair_name').value = r.chair_name || '';
                document.getElementById('treasurer_signature').value = r.treasurer_signature || '';
                document.getElementById('chair_signature').value = r.chair_signature || '';

                document.querySelectorAll('input[name^="checks["]').forEach(el => el.value = '');
                document.querySelectorAll('input[name^="deposits["]').forEach(el => el.value = '');

                (r.checks || []).forEach((row, i) => {
                    const a = document.querySelector(`input[name="checks[${i}][check_no]"]`);
                    const b = document.querySelector(`input[name="checks[${i}][purpose]"]`);
                    const c = document.querySelector(`input[name="checks[${i}][date]"]`);
                    const d = document.querySelector(`input[name="checks[${i}][amount]"]`);
                    if (a) a.value = row.check_no || '';
                    if (b) b.value = row.purpose || '';
                    if (c) c.value = row.date || '';
                    if (d) d.value = row.amount || '';
                });

                (r.deposits || []).forEach((row, i) => {
                    const a = document.querySelector(`input[name="deposits[${i}][date]"]`);
                    const b = document.querySelector(`input[name="deposits[${i}][amount]"]`);
                    if (a) a.value = row.date || '';
                    if (b) b.value = row.amount || '';
                });

                treasurerPad.load(r.treasurer_signature || '');
                chairPad.load(r.chair_signature || '');
                document.getElementById('treasurerSigPreview').textContent = document.getElementById('treasurer_name').value;
                document.getElementById('chairSigPreview').textContent = document.getElementById('chair_name').value;
                updateTotals();
                setStatus('Loaded saved record.');
            } catch (err) {
                setStatus('Unable to load record.', true);
            }
            loadingRecord = false;
        }

        function setupSignaturePad(canvasId, hiddenId, previewId) {
            const canvas = document.getElementById(canvasId);
            const hidden = document.getElementById(hiddenId);
            const preview = document.getElementById(previewId);
            const ctx = canvas.getContext('2d');
            let drawing = false;
            let hasDrawn = false;

            function resize() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                const oldData = hidden.value;
                canvas.width = Math.floor(rect.width * ratio);
                canvas.height = Math.floor(rect.height * ratio);
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#111';
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, rect.width, rect.height);
                if (oldData) {
                    const img = new Image();
                    img.onload = () => ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    img.src = oldData;
                }
            }

            function point(e) {
                const rect = canvas.getBoundingClientRect();
                if (e.touches && e.touches[0]) {
                    return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
                }
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            }

            function start(e) {
                drawing = true;
                hasDrawn = true;
                const p = point(e);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                e.preventDefault();
            }

            function move(e) {
                if (!drawing) return;
                const p = point(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
                hidden.value = canvas.toDataURL('image/png');
                e.preventDefault();
            }

            function end() {
                if (!drawing) return;
                drawing = false;
                hidden.value = hasDrawn ? canvas.toDataURL('image/png') : '';
                queueSave();
            }

            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', move, { passive: false });
            window.addEventListener('touchend', end);
            window.addEventListener('resize', resize);
            resize();

            return {
                clear() {
                    hidden.value = '';
                    hasDrawn = false;
                    resize();
                    queueSave();
                },
                load(dataUrl) {
                    hidden.value = dataUrl || '';
                    hasDrawn = !!dataUrl;
                    resize();
                },
                canvas,
                hidden,
                preview,
            };
        }

        const treasurerPad = setupSignaturePad('treasurerPad', 'treasurer_signature', 'treasurerSigPreview');
        const chairPad = setupSignaturePad('chairPad', 'chair_signature', 'chairSigPreview');

        document.querySelectorAll('[data-clear="treasurer"]').forEach(btn => {
            btn.addEventListener('click', () => treasurerPad.clear());
        });
        document.querySelectorAll('[data-clear="chair"]').forEach(btn => {
            btn.addEventListener('click', () => chairPad.clear());
        });

        document.getElementById('treasurer_name').addEventListener('input', e => {
            document.getElementById('treasurerSigPreview').textContent = e.target.value;
            queueSave();
        });
        document.getElementById('chair_name').addEventListener('input', e => {
            document.getElementById('chairSigPreview').textContent = e.target.value;
            queueSave();
        });

        form.addEventListener('input', (e) => {
            if (e.target.matches('#treasurer_name, #chair_name')) return;
            updateTotals();
            queueSave();
        });

        form.addEventListener('change', () => {
            updateTotals();
            queueSave();
        });

        historySelect.addEventListener('change', () => loadRecord(historySelect.value));
        printBtn.addEventListener('click', () => window.print());

        loadHistory();
        updateTotals();
    </script>
</body>
</html>
