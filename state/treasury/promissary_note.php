<?php
declare(strict_types=1);

/**
 * Oxford House Combined Promissory Note
 *
 * Combines the two uploaded promissory note sheets into one fillable,
 * auto-saving PHP form with history by house name + loan date.
 *
 * Database Config:
 * $dbHost = 'localhost';
 * $dbName = 'secretary';
 * $dbUser = 'secretary';
 * $dbPass = 'EK@rL4mIpKgU5b)P';
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money_fmt($value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function normalize_date(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : '';
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

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS chapter_promissory_notes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    chapter_name VARCHAR(255) NOT NULL DEFAULT '',
    issued_by VARCHAR(255) NOT NULL DEFAULT '',
    loan_date DATE DEFAULT NULL,
    first_payment_date DATE DEFAULT NULL,
    start_month VARCHAR(50) NOT NULL DEFAULT '',
    start_year VARCHAR(10) NOT NULL DEFAULT '',
    loan_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    repayment_months INT NOT NULL DEFAULT 12,
    presentations_per_month INT NOT NULL DEFAULT 0,
    reason_for_loan TEXT NULL,
    action_plan TEXT NULL,
    chapter_chairperson VARCHAR(255) NOT NULL DEFAULT '',
    chapter_treasurer VARCHAR(255) NOT NULL DEFAULT '',
    house_president VARCHAR(255) NOT NULL DEFAULT '',
    house_treasurer VARCHAR(255) NOT NULL DEFAULT '',
    recipient_signature VARCHAR(255) NOT NULL DEFAULT '',
    issuer_signature VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_house_date (house_name, loan_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS chapter_promissory_note_payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    note_id INT UNSIGNED NOT NULL,
    payment_number INT NOT NULL,
    payment_date DATE DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance_remaining DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_promissory_payment_note
        FOREIGN KEY (note_id) REFERENCES chapter_promissory_notes(id)
        ON DELETE CASCADE,
    UNIQUE KEY uniq_note_payment_number (note_id, payment_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$defaults = [
    'id' => '',
    'house_name' => '',
    'chapter_name' => '',
    'issued_by' => '',
    'loan_date' => '',
    'first_payment_date' => '',
    'start_month' => '',
    'start_year' => '',
    'loan_amount' => '',
    'payment_amount' => '',
    'repayment_months' => '12',
    'presentations_per_month' => '',
    'reason_for_loan' => '',
    'action_plan' => '',
    'chapter_chairperson' => '',
    'chapter_treasurer' => '',
    'house_president' => '',
    'house_treasurer' => '',
    'recipient_signature' => '',
    'issuer_signature' => '',
];

$record = $defaults;
$payments = [];
for ($i = 1; $i <= 12; $i++) {
    $payments[$i] = [
        'payment_date' => '',
        'amount' => '',
        'balance_remaining' => '',
    ];
}

$message = '';
$messageType = 'success';
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $currentId = (int)($_POST['id'] ?? 0);

    foreach ($defaults as $key => $default) {
        if ($key === 'id') {
            continue;
        }
        $record[$key] = trim((string)($_POST[$key] ?? ''));
    }

    for ($i = 1; $i <= 12; $i++) {
        $payments[$i] = [
            'payment_date' => trim((string)($_POST['payment_date'][$i] ?? '')),
            'amount' => trim((string)($_POST['payment_amounts'][$i] ?? '')),
            'balance_remaining' => trim((string)($_POST['balance_remaining'][$i] ?? '')),
        ];
    }

    try {
        $pdo->beginTransaction();

        if ($currentId > 0) {
            $stmt = $pdo->prepare(<<<SQL
                UPDATE chapter_promissory_notes SET
                    house_name = :house_name,
                    chapter_name = :chapter_name,
                    issued_by = :issued_by,
                    loan_date = :loan_date,
                    first_payment_date = :first_payment_date,
                    start_month = :start_month,
                    start_year = :start_year,
                    loan_amount = :loan_amount,
                    payment_amount = :payment_amount,
                    repayment_months = :repayment_months,
                    presentations_per_month = :presentations_per_month,
                    reason_for_loan = :reason_for_loan,
                    action_plan = :action_plan,
                    chapter_chairperson = :chapter_chairperson,
                    chapter_treasurer = :chapter_treasurer,
                    house_president = :house_president,
                    house_treasurer = :house_treasurer,
                    recipient_signature = :recipient_signature,
                    issuer_signature = :issuer_signature
                WHERE id = :id
            SQL);
            $stmt->execute([
                ':house_name' => $record['house_name'],
                ':chapter_name' => $record['chapter_name'],
                ':issued_by' => $record['issued_by'],
                ':loan_date' => normalize_date($record['loan_date']) ?: null,
                ':first_payment_date' => normalize_date($record['first_payment_date']) ?: null,
                ':start_month' => $record['start_month'],
                ':start_year' => $record['start_year'],
                ':loan_amount' => $record['loan_amount'] === '' ? 0 : (float)$record['loan_amount'],
                ':payment_amount' => $record['payment_amount'] === '' ? 0 : (float)$record['payment_amount'],
                ':repayment_months' => max(1, (int)$record['repayment_months']),
                ':presentations_per_month' => max(0, (int)$record['presentations_per_month']),
                ':reason_for_loan' => $record['reason_for_loan'],
                ':action_plan' => $record['action_plan'],
                ':chapter_chairperson' => $record['chapter_chairperson'],
                ':chapter_treasurer' => $record['chapter_treasurer'],
                ':house_president' => $record['house_president'],
                ':house_treasurer' => $record['house_treasurer'],
                ':recipient_signature' => $record['recipient_signature'],
                ':issuer_signature' => $record['issuer_signature'],
                ':id' => $currentId,
            ]);

            $pdo->prepare('DELETE FROM chapter_promissory_note_payments WHERE note_id = ?')->execute([$currentId]);
        } else {
            $stmt = $pdo->prepare(<<<SQL
                INSERT INTO chapter_promissory_notes (
                    house_name, chapter_name, issued_by, loan_date, first_payment_date, start_month, start_year,
                    loan_amount, payment_amount, repayment_months, presentations_per_month, reason_for_loan,
                    action_plan, chapter_chairperson, chapter_treasurer, house_president, house_treasurer,
                    recipient_signature, issuer_signature
                ) VALUES (
                    :house_name, :chapter_name, :issued_by, :loan_date, :first_payment_date, :start_month, :start_year,
                    :loan_amount, :payment_amount, :repayment_months, :presentations_per_month, :reason_for_loan,
                    :action_plan, :chapter_chairperson, :chapter_treasurer, :house_president, :house_treasurer,
                    :recipient_signature, :issuer_signature
                )
            SQL);
            $stmt->execute([
                ':house_name' => $record['house_name'],
                ':chapter_name' => $record['chapter_name'],
                ':issued_by' => $record['issued_by'],
                ':loan_date' => normalize_date($record['loan_date']) ?: null,
                ':first_payment_date' => normalize_date($record['first_payment_date']) ?: null,
                ':start_month' => $record['start_month'],
                ':start_year' => $record['start_year'],
                ':loan_amount' => $record['loan_amount'] === '' ? 0 : (float)$record['loan_amount'],
                ':payment_amount' => $record['payment_amount'] === '' ? 0 : (float)$record['payment_amount'],
                ':repayment_months' => max(1, (int)$record['repayment_months']),
                ':presentations_per_month' => max(0, (int)$record['presentations_per_month']),
                ':reason_for_loan' => $record['reason_for_loan'],
                ':action_plan' => $record['action_plan'],
                ':chapter_chairperson' => $record['chapter_chairperson'],
                ':chapter_treasurer' => $record['chapter_treasurer'],
                ':house_president' => $record['house_president'],
                ':house_treasurer' => $record['house_treasurer'],
                ':recipient_signature' => $record['recipient_signature'],
                ':issuer_signature' => $record['issuer_signature'],
            ]);
            $currentId = (int)$pdo->lastInsertId();
        }

        $payStmt = $pdo->prepare(<<<SQL
            INSERT INTO chapter_promissory_note_payments (
                note_id, payment_number, payment_date, amount, balance_remaining
            ) VALUES (
                :note_id, :payment_number, :payment_date, :amount, :balance_remaining
            )
        SQL);

        for ($i = 1; $i <= 12; $i++) {
            $row = $payments[$i];
            if ($row['payment_date'] === '' && $row['amount'] === '' && $row['balance_remaining'] === '') {
                continue;
            }

            $payStmt->execute([
                ':note_id' => $currentId,
                ':payment_number' => $i,
                ':payment_date' => normalize_date($row['payment_date']) ?: null,
                ':amount' => $row['amount'] === '' ? 0 : (float)$row['amount'],
                ':balance_remaining' => $row['balance_remaining'] === '' ? 0 : (float)$row['balance_remaining'],
            ]);
        }

        $pdo->commit();
        $message = 'Promissory note saved successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = 'Save failed: ' . $e->getMessage();
        $messageType = 'error';
    }
}

if ($currentId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM chapter_promissory_notes WHERE id = ? LIMIT 1');
    $stmt->execute([$currentId]);
    $found = $stmt->fetch();
    if ($found) {
        $record = array_merge($record, $found);
        $record['loan_date'] = normalize_date($record['loan_date']);
        $record['first_payment_date'] = normalize_date($record['first_payment_date']);

        $stmtPay = $pdo->prepare('SELECT * FROM chapter_promissory_note_payments WHERE note_id = ? ORDER BY payment_number ASC');
        $stmtPay->execute([$currentId]);
        foreach ($stmtPay->fetchAll() as $row) {
            $num = (int)$row['payment_number'];
            if ($num >= 1 && $num <= 12) {
                $payments[$num] = [
                    'payment_date' => normalize_date($row['payment_date']),
                    'amount' => money_fmt($row['amount']),
                    'balance_remaining' => money_fmt($row['balance_remaining']),
                ];
            }
        }
    }
}

$history = $pdo->query(<<<SQL
    SELECT id, house_name, loan_date, loan_amount, updated_at
    FROM chapter_promissory_notes
    ORDER BY house_name ASC, loan_date DESC, updated_at DESC
SQL)->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Combined Promissory Note</title>
    <style>
        :root {
            --border: #111;
            --muted: #555;
            --bg: #f5f5f5;
            --card: #fff;
            --accent: #203864;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: var(--bg);
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
        }
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid #d8d8d8;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
            padding: 28px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }
        .logo-title {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo-title img {
            width: 90px;
            height: auto;
            object-fit: contain;
        }
        h1 {
            margin: 0;
            font-size: 30px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        h2 {
            margin: 18px 0 10px;
            font-size: 18px;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }
        .subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn, button, select, input, textarea {
            font: inherit;
        }
        .btn, button {
            border: 1px solid var(--border);
            background: #fff;
            padding: 10px 14px;
            cursor: pointer;
        }
        .btn.primary, button.primary {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }
        .notice {
            padding: 10px 12px;
            margin: 12px 0 16px;
            border: 1px solid;
            font-size: 14px;
        }
        .notice.success { background: #edf8ef; border-color: #89b38f; }
        .notice.error { background: #fdeeee; border-color: #d59292; }
        .history-bar {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 18px;
            padding: 14px;
            border: 1px solid #ddd;
            background: #fafafa;
        }
        .history-label { font-weight: 700; margin-bottom: 6px; display:block; }
        select, input[type="text"], input[type="date"], input[type="number"], textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #222;
            background: #fff;
        }
        textarea { min-height: 88px; resize: vertical; }
        .grid-2, .grid-3, .grid-4 {
            display: grid;
            gap: 12px;
        }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .field label {
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 13px;
            text-transform: uppercase;
        }
        .tradition-box {
            border: 1px solid var(--border);
            padding: 14px;
            margin-bottom: 14px;
            font-size: 14px;
            line-height: 1.45;
        }
        .tradition-box strong { text-transform: uppercase; }
        .stipulations {
            border: 1px solid var(--border);
            padding: 14px 16px;
            margin-top: 14px;
        }
        .stipulations ol {
            margin: 8px 0 0 20px;
            padding: 0;
            line-height: 1.45;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid var(--border);
            padding: 8px;
            vertical-align: top;
            font-size: 14px;
        }
        th {
            background: #f1f1f1;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .4px;
        }
        .sign-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .signature-line {
            border-bottom: 1px solid #111;
            min-height: 34px;
            padding-top: 10px;
        }
        .signature-label {
            font-size: 12px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .print-only { display: none; }
        @media print {
            body { background: #fff; padding: 0; }
            .wrap { box-shadow: none; border: 0; max-width: none; padding: 10px; }
            .no-print { display: none !important; }
            .print-only { display: block; }
            input, textarea, select {
                border: 0;
                padding: 0;
                background: transparent;
                appearance: none;
            }
        }
        @media (max-width: 820px) {
            .grid-2, .grid-3, .grid-4, .sign-grid, .history-bar, .topbar {
                grid-template-columns: 1fr;
                display: grid;
            }
            .logo-title { align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="topbar no-print">
        <div class="logo-title">
            <img src="../../images/oxford_house_logo.png" alt="Oxford House Logo">
            <div>
                <h1>Oxford House Chapter Promissory Note</h1>
                <!-- <div class="subtitle">Combined chapter agreement + 12-payment tracking form</div> -->
            </div>
        </div>
        <div class="actions">
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="notice <?= $messageType === 'error' ? 'error' : 'success' ?> no-print"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="history-bar no-print">
        <div>
            <label class="history-label" for="history_select">History (House Name + Loan Date)</label>
            <select id="history_select">
                <option value="">-- Select saved promissory note --</option>
                <?php foreach ($history as $row): ?>
                    <option value="<?= (int)$row['id'] ?>" <?= ((int)$row['id'] === (int)$currentId) ? 'selected' : '' ?>>
                        <?= h($row['house_name']) ?> - <?= h(normalize_date($row['loan_date'])) ?> - $<?= h(money_fmt($row['loan_amount'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="actions">
            <button type="button" onclick="loadSelectedRecord()">Load Record</button>
            <button type="button" onclick="window.location.href=window.location.pathname">New Record</button>
        </div>
    </div>

    <form method="post" id="promissoryForm">
        <input type="hidden" name="id" value="<?= (int)$currentId ?>">

        <div style="text-align:center; margin-bottom: 16px;" class="print-only">
            <img src="../images/oxford_house_logo.png" alt="Oxford House Logo" style="width:90px; height:auto;">
            <h1 style="font-size:26px; margin:8px 0 0;">Oxford House Chapter Promissory Note</h1>
        </div>

        <div class="tradition-box">
            <strong>Oxford House Tradition Six:</strong><br>
            Each Oxford House should be financially self-supporting although financially secure houses may, with approval or encouragement of Oxford House, Inc., provide new or financially needy houses a loan for a term not to exceed one year.
            <br><br>
            Tradition Six emphasizes self-support, recognizes that some houses may temporarily need a loan, and requires that any loan stay limited in duration so dependency does not develop.
        </div>

        <div class="tradition-box">
            <strong>Oxford House Tradition Five:</strong><br>
            Each Oxford House should be autonomous except in matters affecting other houses or Oxford House, Inc., as a whole.
            <br><br>
            When a house fails to meet financial obligations, that can affect other houses and Oxford House as a whole. This combined note documents the loan terms, repayment structure, and chapter oversight expectations.
        </div>

        <h2>Loan Information</h2>
        <div class="grid-4">
            <div class="field">
                <label for="house_name">Loan Issued To / Oxford House</label>
                <input type="text" id="house_name" name="house_name" value="<?= h($record['house_name']) ?>" required>
            </div>
            <div class="field">
                <label for="chapter_name">Chapter</label>
                <input type="text" id="chapter_name" name="chapter_name" value="<?= h($record['chapter_name']) ?>">
            </div>
            <div class="field">
                <label for="issued_by">Loan Issued By</label>
                <input type="text" id="issued_by" name="issued_by" value="<?= h($record['issued_by']) ?>">
            </div>
            <div class="field">
                <label for="loan_date">Date of Loan</label>
                <input type="date" id="loan_date" name="loan_date" value="<?= h($record['loan_date']) ?>">
            </div>
        </div>

        <div class="grid-4" style="margin-top:12px;">
            <div class="field">
                <label for="loan_amount">Amount of Loan</label>
                <input type="number" id="loan_amount" name="loan_amount" min="0" step="0.01" value="<?= h($record['loan_amount']) ?>">
            </div>
            <div class="field">
                <label for="first_payment_date">Date of 1st Payment</label>
                <input type="date" id="first_payment_date" name="first_payment_date" value="<?= h($record['first_payment_date']) ?>">
            </div>
            <div class="field">
                <label for="payment_amount">Payment Amount</label>
                <input type="number" id="payment_amount" name="payment_amount" min="0" step="0.01" value="<?= h($record['payment_amount']) ?>">
            </div>
            <div class="field">
                <label for="repayment_months">Repayment Months</label>
                <input type="number" id="repayment_months" name="repayment_months" min="1" max="12" value="<?= h($record['repayment_months']) ?>">
            </div>
        </div>

        <div class="grid-3" style="margin-top:12px;">
            <div class="field">
                <label for="start_month">Repayment Start Month</label>
                <input type="text" id="start_month" name="start_month" value="<?= h($record['start_month']) ?>">
            </div>
            <div class="field">
                <label for="start_year">Repayment Start Year</label>
                <input type="text" id="start_year" name="start_year" value="<?= h($record['start_year']) ?>">
            </div>
            <div class="field">
                <label for="presentations_per_month">Presentations Per Month</label>
                <input type="number" id="presentations_per_month" name="presentations_per_month" min="0" step="1" value="<?= h($record['presentations_per_month']) ?>">
            </div>
        </div>

        <div class="stipulations">
            <strong>House Stipulations While Loan Is Outstanding</strong>
            <ol>
                <li>One or more members of the house must attend the required number of presentations each month and report back to the chapter on attendance and outcome.</li>
                <li>Members of the house must remain financially current with the house. Anyone behind in EES is subject to immediate eviction.</li>
                <li>The house will agree to forfeit the house checkbook if chapter, HSC, alumni, or outreach representatives believe the finances are in jeopardy.</li>
                <li>The house agrees to allow random and scheduled visits to review house stability, structure, unity, and finances.</li>
                <li>The house agrees to allow visiting representatives to override house autonomy when necessary for the best interest of the house or Oxford House, Inc.</li>
                <li>The house agrees to chapter-implemented fines associated with non-payment or late payment of the loan.</li>
                <li>The house agrees to provide a plan of action describing steps to correct the financial problem.</li>
            </ol>
        </div>

        <div class="grid-2" style="margin-top:12px;">
            <div class="field">
                <label for="reason_for_loan">Reason for Loan</label>
                <textarea id="reason_for_loan" name="reason_for_loan"><?= h($record['reason_for_loan']) ?></textarea>
            </div>
            <div class="field">
                <label for="action_plan">Plan of Action</label>
                <textarea id="action_plan" name="action_plan"><?= h($record['action_plan']) ?></textarea>
            </div>
        </div>

        <h2>Payment Schedule</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Payment #</th>
                    <th style="width: 28%;">Date</th>
                    <th style="width: 30%;">Amount</th>
                    <th style="width: 30%;">Balance Remaining</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <tr>
                        <td><strong>Payment <?= $i ?></strong></td>
                        <td><input type="date" name="payment_date[<?= $i ?>]" value="<?= h($payments[$i]['payment_date']) ?>"></td>
                        <td><input type="number" min="0" step="0.01" name="payment_amounts[<?= $i ?>]" value="<?= h($payments[$i]['amount']) ?>"></td>
                        <td><input type="number" min="0" step="0.01" name="balance_remaining[<?= $i ?>]" value="<?= h($payments[$i]['balance_remaining']) ?>"></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <h2>Signatures</h2>
        <div class="sign-grid">
            <div class="field">
                <label for="chapter_chairperson">Chapter Chairperson</label>
                <input type="text" id="chapter_chairperson" name="chapter_chairperson" value="<?= h($record['chapter_chairperson']) ?>">
            </div>
            <div class="field">
                <label for="chapter_treasurer">Chapter Treasurer</label>
                <input type="text" id="chapter_treasurer" name="chapter_treasurer" value="<?= h($record['chapter_treasurer']) ?>">
            </div>
            <div class="field">
                <label for="house_president">House President</label>
                <input type="text" id="house_president" name="house_president" value="<?= h($record['house_president']) ?>">
            </div>
            <div class="field">
                <label for="house_treasurer">House Treasurer</label>
                <input type="text" id="house_treasurer" name="house_treasurer" value="<?= h($record['house_treasurer']) ?>">
            </div>
            <div class="field">
                <label for="recipient_signature">Signature for Recipient</label>
                <input type="text" id="recipient_signature" name="recipient_signature" value="<?= h($record['recipient_signature']) ?>">
            </div>
            <div class="field">
                <label for="issuer_signature">Signature for Issuer</label>
                <input type="text" id="issuer_signature" name="issuer_signature" value="<?= h($record['issuer_signature']) ?>">
            </div>
        </div>

        <div class="actions no-print" style="margin-top:18px;">
            <button type="submit" class="primary">Save Record</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </form>
</div>

<script>
function loadSelectedRecord() {
    const id = document.getElementById('history_select').value;
    if (!id) return;
    window.location.href = window.location.pathname + '?id=' + encodeURIComponent(id);
}

(function setupAutosave() {
    const form = document.getElementById('promissoryForm');
    let timer = null;
    const debouncedSave = () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            form.requestSubmit();
        }, 1200);
    };

    form.querySelectorAll('input, textarea, select').forEach((el) => {
        if (el.type === 'button' || el.type === 'submit') return;
        el.addEventListener('change', debouncedSave);
        el.addEventListener('blur', debouncedSave);
    });
})();
</script>
</body>
</html>
