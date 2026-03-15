<?php
declare(strict_types=1);

/**
 * Treasurer - Chapter Audit
 * Single-file PHP/MySQL app
 * - Fillable layout closely matching uploaded sheet
 * - Auto-save to MySQL
 * - History dropdown by audit date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 */
/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_money(mixed $value): string
{
    $raw = trim((string)$value);
    if ($raw === '') {
        return '';
    }
    $clean = preg_replace('/[^0-9.\-]/', '', $raw) ?? '';
    if ($clean === '' || $clean === '-' || $clean === '.' || $clean === '-.') {
        return '';
    }
    return number_format((float)$clean, 2, '.', '');
}

function money_float(mixed $value): float
{
    $clean = preg_replace('/[^0-9.\-]/', '', (string)$value) ?? '0';
    if ($clean === '' || $clean === '-' || $clean === '.' || $clean === '-.') {
        return 0.0;
    }
    return round((float)$clean, 2);
}

function normalize_date_input(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }
    return null;
}

function slash_date(?string $ymd): string
{
    if (!$ymd) {
        return '';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $ymd);
    return $dt ? $dt->format('m / d / Y') : '';
}

function blank_row(): array
{
    return [
        'check_number' => '',
        'purpose' => '',
        'date' => '',
        'amount' => '',
    ];
}

function default_form_data(): array
{
    $checks = [];
    for ($i = 0; $i < 10; $i++) {
        $checks[] = blank_row();
    }

    $deposits = array_fill(0, 10, '');

    return [
        'chapter_number' => '',
        'date_completed' => date('Y-m-d'),
        'bank_statement_ending_date' => '',
        'bank_statement_ending_balance' => '',
        'treasurer_signature' => '',
        'chair_signature' => '',
        'outstanding_loans' => '',
        'outstanding_fines' => '',
        'outstanding_dues' => '',
        'checks' => $checks,
        'deposits' => $deposits,
    ];
}

function calculate_totals(array $data): array
{
    $depositTotal = 0.0;
    foreach (($data['deposits'] ?? []) as $deposit) {
        $depositTotal += money_float($deposit);
    }

    $checksTotal = 0.0;
    foreach (($data['checks'] ?? []) as $row) {
        $checksTotal += money_float($row['amount'] ?? '');
    }

    $bankEnding = money_float($data['bank_statement_ending_balance'] ?? '');
    $balanceAfterAudit = round($bankEnding + $depositTotal - $checksTotal, 2);

    $outstandingLoans = money_float($data['outstanding_loans'] ?? '');
    $outstandingFines = money_float($data['outstanding_fines'] ?? '');
    $outstandingDues = money_float($data['outstanding_dues'] ?? '');
    $otherFiguresTotal = round($outstandingLoans + $outstandingFines + $outstandingDues, 2);

    return [
        'deposits_total' => number_format($depositTotal, 2, '.', ''),
        'checks_total' => number_format($checksTotal, 2, '.', ''),
        'balance_after_audit' => number_format($balanceAfterAudit, 2, '.', ''),
        'other_figures_total' => number_format($otherFiguresTotal, 2, '.', ''),
    ];
}

function sanitize_post_data(array $post): array
{
    $data = default_form_data();
    $data['chapter_number'] = trim((string)($post['chapter_number'] ?? ''));
    $data['date_completed'] = normalize_date_input((string)($post['date_completed'] ?? '')) ?? date('Y-m-d');
    $data['bank_statement_ending_date'] = normalize_date_input((string)($post['bank_statement_ending_date'] ?? ''));
    $data['bank_statement_ending_balance'] = normalize_money($post['bank_statement_ending_balance'] ?? '');
    $data['treasurer_signature'] = trim((string)($post['treasurer_signature'] ?? ''));
    $data['chair_signature'] = trim((string)($post['chair_signature'] ?? ''));
    $data['outstanding_loans'] = normalize_money($post['outstanding_loans'] ?? '');
    $data['outstanding_fines'] = normalize_money($post['outstanding_fines'] ?? '');
    $data['outstanding_dues'] = normalize_money($post['outstanding_dues'] ?? '');

    $checks = $post['checks'] ?? [];
    $normalizedChecks = [];
    for ($i = 0; $i < 10; $i++) {
        $row = $checks[$i] ?? [];
        $normalizedChecks[] = [
            'check_number' => trim((string)($row['check_number'] ?? '')),
            'purpose' => trim((string)($row['purpose'] ?? '')),
            'date' => normalize_date_input((string)($row['date'] ?? '')),
            'amount' => normalize_money($row['amount'] ?? ''),
        ];
    }
    $data['checks'] = $normalizedChecks;

    $deposits = $post['deposits'] ?? [];
    $normalizedDeposits = [];
    for ($i = 0; $i < 10; $i++) {
        $normalizedDeposits[] = normalize_money($deposits[$i] ?? '');
    }
    $data['deposits'] = $normalizedDeposits;

    return $data;
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
    "CREATE TABLE IF NOT EXISTS treasurer_chapter_audits (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        audit_date DATE NOT NULL,
        chapter_number VARCHAR(50) NOT NULL DEFAULT '',
        bank_statement_ending_date DATE NULL,
        bank_statement_ending_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        outstanding_loans DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        outstanding_fines DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        outstanding_dues DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        other_figures_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        deposits_json LONGTEXT NOT NULL,
        deposits_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        checks_json LONGTEXT NOT NULL,
        checks_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        balance_after_audit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        treasurer_signature VARCHAR(255) NOT NULL DEFAULT '',
        chair_signature VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_chapter_audit_date (chapter_number, audit_date),
        KEY idx_audit_date (audit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
    $stmt = $pdo->query(
        "SELECT id, chapter_number, audit_date, updated_at
         FROM treasurer_chapter_audits
         ORDER BY audit_date DESC, updated_at DESC, id DESC"
    );

    $items = [];
    while ($row = $stmt->fetch()) {
        $items[] = [
            'id' => (int)$row['id'],
            'label' => trim(($row['chapter_number'] !== '' ? 'Chapter ' . $row['chapter_number'] . ' — ' : '') . date('m/d/Y', strtotime((string)$row['audit_date']))),
            'audit_date' => (string)$row['audit_date'],
            'chapter_number' => (string)$row['chapter_number'],
        ];
    }

    json_response(['ok' => true, 'items' => $items]);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        json_response(['ok' => false, 'message' => 'Invalid record id.'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM treasurer_chapter_audits WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(['ok' => false, 'message' => 'Record not found.'], 404);
    }

    $data = default_form_data();
    $data['chapter_number'] = (string)$row['chapter_number'];
    $data['date_completed'] = (string)$row['audit_date'];
    $data['bank_statement_ending_date'] = $row['bank_statement_ending_date'] ?: '';
    $data['bank_statement_ending_balance'] = number_format((float)$row['bank_statement_ending_balance'], 2, '.', '');
    $data['treasurer_signature'] = (string)$row['treasurer_signature'];
    $data['chair_signature'] = (string)$row['chair_signature'];
    $data['outstanding_loans'] = number_format((float)$row['outstanding_loans'], 2, '.', '');
    $data['outstanding_fines'] = number_format((float)$row['outstanding_fines'], 2, '.', '');
    $data['outstanding_dues'] = number_format((float)$row['outstanding_dues'], 2, '.', '');

    $checks = json_decode((string)$row['checks_json'], true);
    if (is_array($checks)) {
        for ($i = 0; $i < 10; $i++) {
            if (isset($checks[$i]) && is_array($checks[$i])) {
                $data['checks'][$i] = [
                    'check_number' => (string)($checks[$i]['check_number'] ?? ''),
                    'purpose' => (string)($checks[$i]['purpose'] ?? ''),
                    'date' => (string)($checks[$i]['date'] ?? ''),
                    'amount' => normalize_money($checks[$i]['amount'] ?? ''),
                ];
            }
        }
    }

    $deposits = json_decode((string)$row['deposits_json'], true);
    if (is_array($deposits)) {
        for ($i = 0; $i < 10; $i++) {
            $data['deposits'][$i] = normalize_money($deposits[$i] ?? '');
        }
    }

    json_response([
        'ok' => true,
        'record' => [
            'id' => (int)$row['id'],
            'data' => $data,
            'totals' => calculate_totals($data),
        ],
    ]);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'autosave' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $payload = sanitize_post_data($_POST);
    $totals = calculate_totals($payload);

    $sql = "INSERT INTO treasurer_chapter_audits (
                audit_date,
                chapter_number,
                bank_statement_ending_date,
                bank_statement_ending_balance,
                outstanding_loans,
                outstanding_fines,
                outstanding_dues,
                other_figures_total,
                deposits_json,
                deposits_total,
                checks_json,
                checks_total,
                balance_after_audit,
                treasurer_signature,
                chair_signature
            ) VALUES (
                :audit_date,
                :chapter_number,
                :bank_statement_ending_date,
                :bank_statement_ending_balance,
                :outstanding_loans,
                :outstanding_fines,
                :outstanding_dues,
                :other_figures_total,
                :deposits_json,
                :deposits_total,
                :checks_json,
                :checks_total,
                :balance_after_audit,
                :treasurer_signature,
                :chair_signature
            )
            ON DUPLICATE KEY UPDATE
                bank_statement_ending_date = VALUES(bank_statement_ending_date),
                bank_statement_ending_balance = VALUES(bank_statement_ending_balance),
                outstanding_loans = VALUES(outstanding_loans),
                outstanding_fines = VALUES(outstanding_fines),
                outstanding_dues = VALUES(outstanding_dues),
                other_figures_total = VALUES(other_figures_total),
                deposits_json = VALUES(deposits_json),
                deposits_total = VALUES(deposits_total),
                checks_json = VALUES(checks_json),
                checks_total = VALUES(checks_total),
                balance_after_audit = VALUES(balance_after_audit),
                treasurer_signature = VALUES(treasurer_signature),
                chair_signature = VALUES(chair_signature)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':audit_date' => $payload['date_completed'],
        ':chapter_number' => $payload['chapter_number'],
        ':bank_statement_ending_date' => $payload['bank_statement_ending_date'] ?: null,
        ':bank_statement_ending_balance' => $payload['bank_statement_ending_balance'] !== '' ? $payload['bank_statement_ending_balance'] : '0.00',
        ':outstanding_loans' => $payload['outstanding_loans'] !== '' ? $payload['outstanding_loans'] : '0.00',
        ':outstanding_fines' => $payload['outstanding_fines'] !== '' ? $payload['outstanding_fines'] : '0.00',
        ':outstanding_dues' => $payload['outstanding_dues'] !== '' ? $payload['outstanding_dues'] : '0.00',
        ':other_figures_total' => $totals['other_figures_total'],
        ':deposits_json' => json_encode($payload['deposits'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':deposits_total' => $totals['deposits_total'],
        ':checks_json' => json_encode($payload['checks'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':checks_total' => $totals['checks_total'],
        ':balance_after_audit' => $totals['balance_after_audit'],
        ':treasurer_signature' => $payload['treasurer_signature'],
        ':chair_signature' => $payload['chair_signature'],
    ]);

    $lookup = $pdo->prepare("SELECT id FROM treasurer_chapter_audits WHERE chapter_number = :chapter_number AND audit_date = :audit_date LIMIT 1");
    $lookup->execute([
        ':chapter_number' => $payload['chapter_number'],
        ':audit_date' => $payload['date_completed'],
    ]);
    $savedId = (int)($lookup->fetchColumn() ?: 0);

    json_response([
        'ok' => true,
        'message' => 'Saved',
        'id' => $savedId,
        'totals' => $totals,
    ]);
}

$formData = default_form_data();
$currentTotals = calculate_totals($formData);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer - Chapter Audit</title>
    <style>
        :root {
            --ink: #111;
            --line: #111;
            --paper: #fff;
            --bg: #d7d7d7;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            padding: 18px;
        }

        .toolbar {
            width: 8.5in;
            margin: 0 auto 12px auto;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .toolbar .group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            background: #fff;
            border: 1px solid #c8c8c8;
            padding: 8px 10px;
            border-radius: 8px;
        }

        .toolbar label,
        .toolbar select,
        .toolbar button,
        .toolbar .status {
            font-size: 12px;
        }

        .toolbar select,
        .toolbar button {
            height: 32px;
            border: 1px solid #888;
            background: #fff;
            padding: 0 10px;
            border-radius: 4px;
        }

        .status {
            min-width: 110px;
            font-weight: 700;
        }

        .sheet {
            width: 8.5in;
            min-height: 11in;
            margin: 0 auto;
            background: var(--paper);
            border: 1px solid #bbb;
            box-shadow: 0 2px 14px rgba(0,0,0,.08);
            padding: 18px 24px 20px;
            position: relative;
        }

        .topline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .logo {
            width: 104px;
            height: auto;
            object-fit: contain;
        }

        .top-right-note {
            width: 250px;
            text-align: right;
            font-size: 13px;
            line-height: 1.25;
            margin-top: 6px;
        }

        h1 {
            margin: 6px 0 4px;
            text-align: center;
            font-size: 28px;
            letter-spacing: .3px;
            font-weight: 800;
        }

        .audit-meta {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 18px;
            font-size: 18px;
            margin: 8px 0 18px;
            font-weight: 700;
        }

        .inline-line {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .line-input, .money-input, .date-input, .text-input {
            border: none;
            border-bottom: 1px solid var(--line);
            border-radius: 0;
            background: transparent;
            outline: none;
            padding: 1px 2px;
            font: inherit;
            color: inherit;
        }

        .line-input { width: 72px; text-align: center; }
        .date-input { width: 126px; text-align: center; }
        .money-input { width: 86px; text-align: right; }
        .money-input.wide { width: 118px; }
        .text-input { width: 100%; }

        .section-title {
            text-align: center;
            font-weight: 800;
            font-size: 20px;
            margin: 8px 0 6px;
        }

        .other-figures {
            width: 100%;
            margin: 2px 0 10px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .other-figures td {
            padding: 2px 0;
            font-size: 17px;
            vertical-align: middle;
        }

        .other-figures .label-cell { width: 72%; }
        .other-figures .dollar-cell { width: 2.5%; text-align: center; font-weight: 700; }
        .other-figures .input-cell { width: 25.5%; }

        .bank-line {
            font-size: 19px;
            font-weight: 700;
            margin: 8px 0 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .instruction-list {
            margin: 4px 0 10px 18px;
            padding: 0 0 0 16px;
            font-size: 13px;
            line-height: 1.35;
        }

        .checks-section-title, .deposits-section-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            margin: 10px 0 4px;
        }

        table.audit-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .audit-table thead th {
            font-size: 16px;
            font-weight: 700;
            text-align: left;
            padding: 0 4px 2px;
        }

        .audit-table td {
            padding: 1px 3px;
            height: 27px;
            vertical-align: middle;
        }

        .audit-table .col-check { width: 11%; }
        .audit-table .col-purpose { width: 49%; }
        .audit-table .col-date { width: 18%; }
        .audit-table .col-amount { width: 22%; text-align: right; }
        .audit-table .col-deposit { width: 100%; }

        .audit-table input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #111;
            padding: 1px 2px;
            font-size: 15px;
            background: transparent;
            outline: none;
        }

        .audit-table .money-cell input {
            text-align: right;
        }

        .totals-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin: 6px 0 12px;
            font-size: 21px;
            font-weight: 800;
        }

        .equation-row {
            display: grid;
            grid-template-columns: 1.3fr 42px 1.1fr 42px 1.1fr 42px 1.2fr;
            align-items: end;
            gap: 6px;
            margin: 4px 0 14px;
        }

        .equation-block {
            text-align: center;
        }

        .equation-label {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .equation-money {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 26px;
            font-weight: 800;
        }

        .equation-money input {
            width: 100%;
            max-width: 132px;
            border: none;
            border-bottom: 1px solid #111;
            text-align: center;
            font-size: 23px;
            background: transparent;
            outline: none;
        }

        .symbol {
            text-align: center;
            font-size: 36px;
            font-weight: 800;
            padding-bottom: 2px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 42px;
            margin-top: 30px;
        }

        .signature-block {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }

        .signature-block input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #111;
            font-size: 18px;
            padding: 4px 2px 3px;
            text-align: center;
            background: transparent;
            outline: none;
        }

        .form-dollar {
            display: inline-block;
            width: 12px;
            text-align: center;
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .sheet {
                margin: 0;
                border: none;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }
            @page {
                size: letter portrait;
                margin: 0.35in;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="group">
            <label for="historySelect"><strong>History</strong></label>
            <select id="historySelect">
                <option value="">Select saved audit by date</option>
            </select>
        </div>
        <div class="group">
            <button type="button" id="printBtn">Print</button>
            <button type="button" id="newBtn">New</button>
            <span class="status" id="saveStatus">Ready</span>
        </div>
    </div>

    <form id="auditForm" class="sheet" autocomplete="off">
        <div class="topline">
            <img src="<?= h($logoPath) ?>" alt="Oxford House Logo" class="logo">
            <div class="top-right-note">Email completed audit to houses and officers.</div>
        </div>

        <div class="section-title">OTHER FIGURES</div>
        <table class="other-figures">
            <tr>
                <td class="label-cell">Total outstanding Loans</td>
                <td class="dollar-cell">$</td>
                <td class="input-cell"><input class="money-input wide calc-source" type="text" name="outstanding_loans" value="<?= h($formData['outstanding_loans']) ?>"></td>
            </tr>
            <tr>
                <td class="label-cell">Total outstanding Fines</td>
                <td class="dollar-cell">$</td>
                <td class="input-cell"><input class="money-input wide calc-source" type="text" name="outstanding_fines" value="<?= h($formData['outstanding_fines']) ?>"></td>
            </tr>
            <tr>
                <td class="label-cell">Total outstanding Dues</td>
                <td class="dollar-cell">$</td>
                <td class="input-cell"><input class="money-input wide calc-source" type="text" name="outstanding_dues" value="<?= h($formData['outstanding_dues']) ?>"></td>
            </tr>
            <tr>
                <td class="label-cell"><strong>TOTAL:</strong></td>
                <td class="dollar-cell">$</td>
                <td class="input-cell"><input class="money-input wide" type="text" id="otherFiguresTotal" value="<?= h($currentTotals['other_figures_total']) ?>" readonly></td>
            </tr>
        </table>

        <h1>FINANCIAL AUDIT</h1>

        <div class="audit-meta">
            <span class="inline-line">OXFORD HOUSE CHAPTER <input class="line-input autosave-field" type="text" name="chapter_number" value="<?= h($formData['chapter_number']) ?>"></span>
            <span class="inline-line">DATE COMPLETED: <input class="date-input autosave-field" type="date" name="date_completed" value="<?= h($formData['date_completed']) ?>"></span>
        </div>

        <div class="bank-line">
            <span>BANK STATEMENT ENDING DATE</span>
            <input class="date-input autosave-field" type="date" name="bank_statement_ending_date" value="<?= h($formData['bank_statement_ending_date']) ?>">
            <span style="margin-left:14px;">BANK STATEMENT ENDING BALANCE</span>
            <span class="form-dollar">$</span>
            <input class="money-input wide calc-source" type="text" name="bank_statement_ending_balance" value="<?= h($formData['bank_statement_ending_balance']) ?>">
        </div>

        <ol class="instruction-list">
            <li>The Treasurer, and Chairperson complete the audit together.</li>
            <li>Use the bank statement, checkbook, Financial Report, and Meeting Minutes for references.</li>
            <li>Document all deposits and checks that are not listed on the most recent bank statement.</li>
            <li>Highlight or circle the check numbers on the check stubs for all checks that are listed on the bank statement.</li>
            <li>Count the checks in the checkbook, by check number, to ensure no checks are missing.</li>
            <li>The final audited balance should match the ending balance on the check stub of the last check written.</li>
            <li>If fraud, theft, or embezzlement has occured, immediately notify OHI staff, the bank, and the police.</li>
        </ol>

        <div class="checks-section-title">CHECKS NOT ON STATEMENT</div>
        <table class="audit-table">
            <thead>
                <tr>
                    <th class="col-check">Check #</th>
                    <th class="col-purpose">To Whom / Purpose</th>
                    <th class="col-date">Date</th>
                    <th class="col-amount">Amount $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formData['checks'] as $i => $row): ?>
                    <tr>
                        <td><input class="autosave-field" type="text" name="checks[<?= $i ?>][check_number]" value="<?= h($row['check_number']) ?>"></td>
                        <td><input class="autosave-field" type="text" name="checks[<?= $i ?>][purpose]" value="<?= h($row['purpose']) ?>"></td>
                        <td><input class="autosave-field" type="date" name="checks[<?= $i ?>][date]" value="<?= h((string)$row['date']) ?>"></td>
                        <td class="money-cell"><input class="autosave-field calc-source" type="text" name="checks[<?= $i ?>][amount]" value="<?= h($row['amount']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals-row">
            <span>TOTAL:</span>
            <span>$</span>
            <input class="money-input wide" type="text" id="checksTotal" value="<?= h($currentTotals['checks_total']) ?>" readonly>
        </div>

        <div class="equation-row">
            <div class="equation-block">
                <div class="equation-label">Bank Statement<br>Ending Bal</div>
                <div class="equation-money"><span>$</span><input type="text" id="equationBankEnding" value="<?= h($formData['bank_statement_ending_balance']) ?>" readonly></div>
            </div>
            <div class="symbol">+</div>
            <div class="equation-block">
                <div class="equation-label">Total Deposits</div>
                <div class="equation-money"><span>$</span><input type="text" id="depositsTotal" value="<?= h($currentTotals['deposits_total']) ?>" readonly></div>
            </div>
            <div class="symbol">-</div>
            <div class="equation-block">
                <div class="equation-label">Total of Checks</div>
                <div class="equation-money"><span>$</span><input type="text" id="equationChecksTotal" value="<?= h($currentTotals['checks_total']) ?>" readonly></div>
            </div>
            <div class="symbol">=</div>
            <div class="equation-block">
                <div class="equation-label">Balance After Audit</div>
                <div class="equation-money"><span>$</span><input type="text" id="balanceAfterAudit" value="<?= h($currentTotals['balance_after_audit']) ?>" readonly></div>
            </div>
        </div>

        <div class="deposits-section-title">DEPOSITS NOT ON STATEMENT</div>
        <table class="audit-table">
            <thead>
                <tr>
                    <th class="col-deposit">Amount $</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formData['deposits'] as $i => $deposit): ?>
                    <tr>
                        <td class="money-cell"><input class="autosave-field calc-source" type="text" name="deposits[<?= $i ?>]" value="<?= h($deposit) ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals-row">
            <span>TOTAL:</span>
            <span>$</span>
            <input class="money-input wide" type="text" id="depositsTotalBottom" value="<?= h($currentTotals['deposits_total']) ?>" readonly>
        </div>

        <div class="signature-row">
            <div class="signature-block">
                <input class="autosave-field" type="text" name="treasurer_signature" value="<?= h($formData['treasurer_signature']) ?>">
                <div>Treasurer Signature</div>
            </div>
            <div class="signature-block">
                <input class="autosave-field" type="text" name="chair_signature" value="<?= h($formData['chair_signature']) ?>">
                <div>Chair Signature</div>
            </div>
        </div>
    </form>

    <script>
        const form = document.getElementById('auditForm');
        const saveStatus = document.getElementById('saveStatus');
        const historySelect = document.getElementById('historySelect');
        const printBtn = document.getElementById('printBtn');
        const newBtn = document.getElementById('newBtn');

        let saveTimer = null;
        let loadingRecord = false;

        function parseMoney(value) {
            const cleaned = String(value || '').replace(/[^0-9.-]/g, '');
            const num = parseFloat(cleaned);
            return Number.isFinite(num) ? num : 0;
        }

        function moneyFormat(value) {
            return parseMoney(value).toFixed(2);
        }

        function recalc() {
            const loans = parseMoney(form.outstanding_loans.value);
            const fines = parseMoney(form.outstanding_fines.value);
            const dues = parseMoney(form.outstanding_dues.value);
            document.getElementById('otherFiguresTotal').value = (loans + fines + dues).toFixed(2);

            let checksTotal = 0;
            form.querySelectorAll('input[name^="checks"][name$="[amount]"]').forEach((input) => {
                checksTotal += parseMoney(input.value);
            });

            let depositsTotal = 0;
            form.querySelectorAll('input[name^="deposits["]').forEach((input) => {
                depositsTotal += parseMoney(input.value);
            });

            const bankEnding = parseMoney(form.bank_statement_ending_balance.value);
            const auditBalance = bankEnding + depositsTotal - checksTotal;

            document.getElementById('checksTotal').value = checksTotal.toFixed(2);
            document.getElementById('equationChecksTotal').value = checksTotal.toFixed(2);
            document.getElementById('depositsTotal').value = depositsTotal.toFixed(2);
            document.getElementById('depositsTotalBottom').value = depositsTotal.toFixed(2);
            document.getElementById('equationBankEnding').value = bankEnding.toFixed(2);
            document.getElementById('balanceAfterAudit').value = auditBalance.toFixed(2);
        }

        async function loadHistory() {
            const res = await fetch('?ajax=history', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            historySelect.innerHTML = '<option value="">Select saved audit by date</option>';
            if (data.items) {
                data.items.forEach((item) => {
                    const opt = document.createElement('option');
                    opt.value = String(item.id);
                    opt.textContent = item.label;
                    historySelect.appendChild(opt);
                });
            }
        }

        async function loadRecord(id) {
            if (!id) return;
            loadingRecord = true;
            saveStatus.textContent = 'Loading...';
            const res = await fetch('?ajax=load&id=' + encodeURIComponent(id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!data.ok || !data.record) {
                saveStatus.textContent = 'Load failed';
                loadingRecord = false;
                return;
            }
            const record = data.record.data;

            form.chapter_number.value = record.chapter_number || '';
            form.date_completed.value = record.date_completed || '';
            form.bank_statement_ending_date.value = record.bank_statement_ending_date || '';
            form.bank_statement_ending_balance.value = record.bank_statement_ending_balance || '';
            form.outstanding_loans.value = record.outstanding_loans || '';
            form.outstanding_fines.value = record.outstanding_fines || '';
            form.outstanding_dues.value = record.outstanding_dues || '';
            form.treasurer_signature.value = record.treasurer_signature || '';
            form.chair_signature.value = record.chair_signature || '';

            const checkNumbers = form.querySelectorAll('input[name^="checks"][name$="[check_number]"]');
            const purposes = form.querySelectorAll('input[name^="checks"][name$="[purpose]"]');
            const dates = form.querySelectorAll('input[name^="checks"][name$="[date]"]');
            const amounts = form.querySelectorAll('input[name^="checks"][name$="[amount]"]');
            const deposits = form.querySelectorAll('input[name^="deposits["]');

            (record.checks || []).forEach((row, idx) => {
                if (checkNumbers[idx]) checkNumbers[idx].value = row.check_number || '';
                if (purposes[idx]) purposes[idx].value = row.purpose || '';
                if (dates[idx]) dates[idx].value = row.date || '';
                if (amounts[idx]) amounts[idx].value = row.amount || '';
            });

            for (let i = 0; i < deposits.length; i++) {
                deposits[i].value = (record.deposits && record.deposits[i]) ? record.deposits[i] : '';
            }

            recalc();
            saveStatus.textContent = 'Loaded';
            loadingRecord = false;
        }

        async function autosave() {
            if (loadingRecord) return;
            const fd = new FormData(form);
            saveStatus.textContent = 'Saving...';
            const res = await fetch('?ajax=autosave', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.ok) {
                saveStatus.textContent = 'Saved';
                if (data.totals) {
                    document.getElementById('otherFiguresTotal').value = data.totals.other_figures_total;
                    document.getElementById('checksTotal').value = data.totals.checks_total;
                    document.getElementById('equationChecksTotal').value = data.totals.checks_total;
                    document.getElementById('depositsTotal').value = data.totals.deposits_total;
                    document.getElementById('depositsTotalBottom').value = data.totals.deposits_total;
                    document.getElementById('balanceAfterAudit').value = data.totals.balance_after_audit;
                    document.getElementById('equationBankEnding').value = moneyFormat(form.bank_statement_ending_balance.value);
                }
                await loadHistory();
                if (data.id) {
                    historySelect.value = String(data.id);
                }
            } else {
                saveStatus.textContent = 'Save failed';
            }
        }

        function queueAutosave() {
            recalc();
            saveStatus.textContent = 'Typing...';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosave, 700);
        }

        form.querySelectorAll('.autosave-field, .calc-source').forEach((field) => {
            field.addEventListener('input', queueAutosave);
            field.addEventListener('change', queueAutosave);
        });

        historySelect.addEventListener('change', (e) => loadRecord(e.target.value));
        printBtn.addEventListener('click', () => window.print());
        newBtn.addEventListener('click', () => {
            form.reset();
            form.date_completed.value = new Date().toISOString().slice(0, 10);
            historySelect.value = '';
            recalc();
            saveStatus.textContent = 'Ready';
        });

        recalc();
        loadHistory();
    </script>
</body>
</html>
