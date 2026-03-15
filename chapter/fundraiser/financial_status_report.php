<?php

declare(strict_types=1);

/**
 * Oxford House Fundraising Financial Status Report
 * Single-file PHP/MySQL app
 * - Fillable layout closely matching uploaded sheet
 * - Auto-save to MySQL
 * - History dropdown by report date
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
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function blank_money_rows(): array
{
    return array_fill(0, 10, ['date' => '', 'source' => '', 'amount' => '']);
}

function blank_expense_rows(): array
{
    return array_fill(0, 4, ['date' => '', 'purpose' => '', 'check_no' => '', 'amount' => '']);
}

function blank_inventory_rows(): array
{
    return array_fill(0, 4, ['date' => '', 'description' => '', 'taken' => '', 'remaining' => '']);
}

function normalize_money_string(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = preg_replace('/[^0-9.\-]/', '', $value) ?? '';
    if ($value === '' || $value === '-' || $value === '.' || $value === '-.') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function normalize_int_string(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = preg_replace('/[^0-9\-]/', '', $value) ?? '';
    return $value;
}

function clean_rows(array $rows, string $type): array
{
    $clean = [];
    foreach ($rows as $row) {
        $row = is_array($row) ? $row : [];
        if ($type === 'money') {
            $clean[] = [
                'date' => trim((string)($row['date'] ?? '')),
                'source' => trim((string)($row['source'] ?? '')),
                'amount' => normalize_money_string($row['amount'] ?? ''),
            ];
        } elseif ($type === 'expense') {
            $clean[] = [
                'date' => trim((string)($row['date'] ?? '')),
                'purpose' => trim((string)($row['purpose'] ?? '')),
                'check_no' => trim((string)($row['check_no'] ?? '')),
                'amount' => normalize_money_string($row['amount'] ?? ''),
            ];
        } else {
            $taken = strtolower(trim((string)($row['taken'] ?? '')));
            if (!in_array($taken, ['yes', 'no'], true)) {
                $taken = '';
            }
            $clean[] = [
                'date' => trim((string)($row['date'] ?? '')),
                'description' => trim((string)($row['description'] ?? '')),
                'taken' => $taken,
                'remaining' => normalize_int_string($row['remaining'] ?? ''),
            ];
        }
    }
    return $clean;
}

function sum_amounts(array $rows, string $field = 'amount'): float
{
    $sum = 0.0;
    foreach ($rows as $row) {
        $sum += (float)($row[$field] ?? 0);
    }
    return $sum;
}

function ensure_schema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS fundraising_financial_status_reports (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            dates_start_month VARCHAR(2) NOT NULL DEFAULT '',
            dates_start_day VARCHAR(2) NOT NULL DEFAULT '',
            dates_start_year VARCHAR(4) NOT NULL DEFAULT '',
            dates_end_month VARCHAR(2) NOT NULL DEFAULT '',
            dates_end_day VARCHAR(2) NOT NULL DEFAULT '',
            dates_end_year VARCHAR(4) NOT NULL DEFAULT '',
            beginning_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            total_received DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            total_spent DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            ending_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            money_received_json LONGTEXT NULL,
            approved_expenses_json LONGTEXT NULL,
            merchandise_inventory_json LONGTEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_report_dates (dates_start_year, dates_start_month, dates_start_day, dates_end_year, dates_end_month, dates_end_day),
            INDEX idx_updated_at (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
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
    ensure_schema($pdo);
} catch (Throwable $e) {
    if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
        json_response(['ok' => false, 'message' => 'Database connection failed: ' . $e->getMessage()], 500);
    }
    die('Database connection failed: ' . h($e->getMessage()));
}

if (($_GET['ajax'] ?? $_POST['ajax'] ?? '') === 'save') {
    $payload = $_POST;
    $recordId = (int)($payload['record_id'] ?? 0);

    $moneyRows = clean_rows($payload['money_received'] ?? [], 'money');
    $expenseRows = clean_rows($payload['approved_expenses'] ?? [], 'expense');
    $inventoryRows = clean_rows($payload['merchandise_inventory'] ?? [], 'inventory');

    $beginningBalance = (float)(normalize_money_string($payload['beginning_balance'] ?? '0') ?: 0);
    $totalReceived = sum_amounts($moneyRows);
    $totalSpent = sum_amounts($expenseRows);
    $endingBalance = $beginningBalance + $totalReceived - $totalSpent;

    $data = [
        'dates_start_month' => trim((string)($payload['dates_start_month'] ?? '')),
        'dates_start_day' => trim((string)($payload['dates_start_day'] ?? '')),
        'dates_start_year' => trim((string)($payload['dates_start_year'] ?? '')),
        'dates_end_month' => trim((string)($payload['dates_end_month'] ?? '')),
        'dates_end_day' => trim((string)($payload['dates_end_day'] ?? '')),
        'dates_end_year' => trim((string)($payload['dates_end_year'] ?? '')),
        'beginning_balance' => number_format($beginningBalance, 2, '.', ''),
        'total_received' => number_format($totalReceived, 2, '.', ''),
        'total_spent' => number_format($totalSpent, 2, '.', ''),
        'ending_balance' => number_format($endingBalance, 2, '.', ''),
        'money_received_json' => json_encode($moneyRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'approved_expenses_json' => json_encode($expenseRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'merchandise_inventory_json' => json_encode($inventoryRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];

    if ($recordId > 0) {
        $sql = "UPDATE fundraising_financial_status_reports SET
            dates_start_month = :dates_start_month,
            dates_start_day = :dates_start_day,
            dates_start_year = :dates_start_year,
            dates_end_month = :dates_end_month,
            dates_end_day = :dates_end_day,
            dates_end_year = :dates_end_year,
            beginning_balance = :beginning_balance,
            total_received = :total_received,
            total_spent = :total_spent,
            ending_balance = :ending_balance,
            money_received_json = :money_received_json,
            approved_expenses_json = :approved_expenses_json,
            merchandise_inventory_json = :merchandise_inventory_json
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $recordId;
        $stmt->execute($data);
    } else {
        $sql = "INSERT INTO fundraising_financial_status_reports (
            dates_start_month, dates_start_day, dates_start_year,
            dates_end_month, dates_end_day, dates_end_year,
            beginning_balance, total_received, total_spent, ending_balance,
            money_received_json, approved_expenses_json, merchandise_inventory_json
        ) VALUES (
            :dates_start_month, :dates_start_day, :dates_start_year,
            :dates_end_month, :dates_end_day, :dates_end_year,
            :beginning_balance, :total_received, :total_spent, :ending_balance,
            :money_received_json, :approved_expenses_json, :merchandise_inventory_json
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $recordId = (int)$pdo->lastInsertId();
    }

    json_response([
        'ok' => true,
        'record_id' => $recordId,
        'totals' => [
            'total_received' => number_format($totalReceived, 2, '.', ''),
            'total_spent' => number_format($totalSpent, 2, '.', ''),
            'ending_balance' => number_format($endingBalance, 2, '.', ''),
        ],
        'message' => 'Saved',
    ]);
}

$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$record = [
    'id' => 0,
    'dates_start_month' => '',
    'dates_start_day' => '',
    'dates_start_year' => '',
    'dates_end_month' => '',
    'dates_end_day' => '',
    'dates_end_year' => '',
    'beginning_balance' => '',
    'total_received' => '0.00',
    'total_spent' => '0.00',
    'ending_balance' => '0.00',
    'money_received_json' => json_encode(blank_money_rows(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'approved_expenses_json' => json_encode(blank_expense_rows(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'merchandise_inventory_json' => json_encode(blank_inventory_rows(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
];

if ($currentId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM fundraising_financial_status_reports WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $currentId]);
    $found = $stmt->fetch();
    if ($found) {
        $record = array_merge($record, $found);
    }
}

$historyStmt = $pdo->query(
    "SELECT id,
        dates_start_month, dates_start_day, dates_start_year,
        dates_end_month, dates_end_day, dates_end_year,
        updated_at
     FROM fundraising_financial_status_reports
     ORDER BY
        CAST(NULLIF(dates_end_year, '') AS UNSIGNED) DESC,
        CAST(NULLIF(dates_end_month, '') AS UNSIGNED) DESC,
        CAST(NULLIF(dates_end_day, '') AS UNSIGNED) DESC,
        updated_at DESC,
        id DESC"
);
$historyRows = $historyStmt->fetchAll();

$moneyRows = json_decode((string)$record['money_received_json'], true);
$moneyRows = is_array($moneyRows) ? $moneyRows : blank_money_rows();
$moneyRows = array_pad($moneyRows, 10, ['date' => '', 'source' => '', 'amount' => '']);

$expenseRows = json_decode((string)$record['approved_expenses_json'], true);
$expenseRows = is_array($expenseRows) ? $expenseRows : blank_expense_rows();
$expenseRows = array_pad($expenseRows, 4, ['date' => '', 'purpose' => '', 'check_no' => '', 'amount' => '']);

$inventoryRows = json_decode((string)$record['merchandise_inventory_json'], true);
$inventoryRows = is_array($inventoryRows) ? $inventoryRows : blank_inventory_rows();
$inventoryRows = array_pad($inventoryRows, 4, ['date' => '', 'description' => '', 'taken' => '', 'remaining' => '']);

$beginningBalance = (float)($record['beginning_balance'] !== '' ? $record['beginning_balance'] : 0);
$totalReceived = sum_amounts($moneyRows);
$totalSpent = sum_amounts($expenseRows);
$endingBalance = $beginningBalance + $totalReceived - $totalSpent;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundrasing - Financial Status Report</title>
    <style>
        :root {
            --page-width: 8.5in;
            --page-height: 11in;
            --page-padding-top: 0.55in;
            --page-padding-side: 0.72in;
            --line: #111;
            --text: #111;
            --bg: #e6e6e6;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #cfcfcf;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            max-width: var(--page-width);
            margin: 18px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 0 8px;
            flex-wrap: wrap;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar label,
        .toolbar select,
        .toolbar button,
        .toolbar .status {
            font-size: 14px;
        }

        .toolbar select,
        .toolbar button {
            height: 36px;
            border: 1px solid #444;
            background: #fff;
            padding: 0 12px;
        }

        .toolbar button {
            cursor: pointer;
            font-weight: 700;
        }

        .status {
            min-width: 92px;
            font-weight: 700;
        }

        .page {
            width: var(--page-width);
            min-height: var(--page-height);
            background: var(--bg);
            margin: 12px auto 28px;
            padding: var(--page-padding-top) var(--page-padding-side) 0.6in;
            box-shadow: 0 2px 18px rgba(0,0,0,0.16);
            position: relative;
        }

        .header {
            display: grid;
            grid-template-columns: 120px 1fr;
            align-items: center;
            column-gap: 16px;
            margin-bottom: 14px;
        }

        .header img {
            width: 108px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .title-wrap {
            text-align: center;
            transform: translateX(-18px);
        }

        .title-main {
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0;
        }

        .title-sub {
            font-size: 25px;
            line-height: 1.05;
            margin-top: 2px;
            font-weight: 400;
            letter-spacing: 0;
        }

        .dates-covered {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 18px;
            margin: 10px 0 34px;
        }

        .slash, .date-to {
            font-size: 18px;
        }

        .date-mini {
            width: 58px;
            border: 0;
            border-bottom: 2px solid var(--line);
            background: transparent;
            height: 24px;
            text-align: center;
            font-size: 18px;
            padding: 0 2px;
            outline: none;
        }

        .section-title {
            font-size: 26px;
            line-height: 1;
            font-weight: 700;
            margin: 0 0 4px 6px;
        }

        table.sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 13px;
        }

        table.sheet thead th {
            border: 0;
            font-weight: 400;
            font-size: 16px;
            padding: 0 4px 1px;
            text-align: center;
        }

        table.sheet tbody td {
            border: 2px solid var(--line);
            padding: 0;
            height: 38px;
            background: transparent;
        }

        .money-table col:nth-child(1) { width: 20%; }
        .money-table col:nth-child(2) { width: 59%; }
        .money-table col:nth-child(3) { width: 21%; }

        .expense-table col:nth-child(1) { width: 20%; }
        .expense-table col:nth-child(2) { width: 40%; }
        .expense-table col:nth-child(3) { width: 19%; }
        .expense-table col:nth-child(4) { width: 21%; }

        .inventory-table col:nth-child(1) { width: 20%; }
        .inventory-table col:nth-child(2) { width: 40%; }
        .inventory-table col:nth-child(3) { width: 19%; }
        .inventory-table col:nth-child(4) { width: 21%; }

        .cell-input,
        .cell-select {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            font-size: 16px;
            padding: 0 8px;
            color: var(--text);
            outline: none;
        }

        .cell-input.center,
        .cell-select.center {
            text-align: center;
        }

        .cell-input.right {
            text-align: right;
            padding-right: 10px;
        }

        .cell-select {
            text-align-last: center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .total-box {
            width: 430px;
            margin: 4px 0 18px auto;
            border: 2px solid var(--line);
            display: grid;
            grid-template-columns: 1fr 183px;
            background: transparent;
        }

        .total-box .label,
        .total-box .value {
            height: 54px;
            display: flex;
            align-items: center;
            font-size: 18px;
            font-weight: 700;
        }

        .total-box .label {
            justify-content: center;
            border-right: 2px solid var(--line);
        }

        .total-box .value {
            position: relative;
            padding-left: 20px;
            background: transparent;
        }

        .total-box .dollar {
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 700;
        }

        .total-box input {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            font-size: 18px;
            font-weight: 700;
            padding: 0 8px 0 18px;
            outline: none;
        }

        .total-box input[readonly] {
            pointer-events: none;
        }

        .inventory-table tbody td { height: 35px; }

        .formula-row {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
            align-items: start;
            column-gap: 18px;
        }

        .formula-symbol {
            font-size: 30px;
            font-weight: 700;
            line-height: 54px;
            text-align: center;
            padding-top: 1px;
        }

        .formula-box {
            width: 100%;
        }

        .formula-rect {
            border: 2px solid var(--line);
            height: 44px;
            position: relative;
            background: transparent;
        }

        .formula-rect .dollar {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }

        .formula-rect input {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            font-size: 18px;
            padding: 0 8px 0 22px;
            outline: none;
        }

        .formula-label {
            font-size: 17px;
            text-align: center;
            margin-top: 2px;
        }

        .readonly { pointer-events: none; }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-left">
            <label for="history_id"><strong>History by date:</strong></label>
            <select id="history_id" onchange="if(this.value){window.location='?id='+this.value;}">
                <option value="">New report</option>
                <?php foreach ($historyRows as $row):
                    $labelStart = trim(($row['dates_start_month'] ?: '__') . '/' . ($row['dates_start_day'] ?: '__') . '/' . ($row['dates_start_year'] ?: '____'));
                    $labelEnd = trim(($row['dates_end_month'] ?: '__') . '/' . ($row['dates_end_day'] ?: '__') . '/' . ($row['dates_end_year'] ?: '____'));
                    $selected = ((int)$row['id'] === (int)$record['id']) ? 'selected' : '';
                ?>
                    <option value="<?= (int)$row['id'] ?>" <?= $selected ?>><?= h($labelStart) ?> to <?= h($labelEnd) ?> - Saved <?= h($row['updated_at']) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="status" id="saveStatus">Ready</span>
        </div>
        <div class="toolbar-right">
            <button type="button" onclick="window.location='?'">New Blank</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </div>

    <form id="reportForm" class="page" method="post" action="">
        <input type="hidden" name="record_id" id="record_id" value="<?= (int)$record['id'] ?>">

        <div class="header">
            <div>
                <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
            </div>
            <div class="title-wrap">
                <div class="title-main">OXFORD HOUSE FUNDRAISING</div>
                <div class="title-sub">FINANCIAL STATUS REPORT</div>
            </div>
        </div>

        <div class="dates-covered">
            <span>Dates Covered:</span>
            <input class="date-mini" maxlength="2" name="dates_start_month" value="<?= h($record['dates_start_month']) ?>">
            <span class="slash">/</span>
            <input class="date-mini" maxlength="2" name="dates_start_day" value="<?= h($record['dates_start_day']) ?>">
            <span class="slash">/</span>
            <input class="date-mini" maxlength="4" name="dates_start_year" value="<?= h($record['dates_start_year']) ?>">
            <span class="date-to">to</span>
            <input class="date-mini" maxlength="2" name="dates_end_month" value="<?= h($record['dates_end_month']) ?>">
            <span class="slash">/</span>
            <input class="date-mini" maxlength="2" name="dates_end_day" value="<?= h($record['dates_end_day']) ?>">
            <span class="slash">/</span>
            <input class="date-mini" maxlength="4" name="dates_end_year" value="<?= h($record['dates_end_year']) ?>">
        </div>

        <div class="section-title">Money Received</div>
        <table class="sheet money-table">
            <colgroup><col><col><col></colgroup>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Amount $</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 10; $i++): $row = $moneyRows[$i] ?? ['date' => '', 'source' => '', 'amount' => '']; ?>
                <tr>
                    <td><input class="cell-input center" name="money_received[<?= $i ?>][date]" value="<?= h($row['date'] ?? '') ?>"></td>
                    <td><input class="cell-input" name="money_received[<?= $i ?>][source]" value="<?= h($row['source'] ?? '') ?>"></td>
                    <td><input class="cell-input right money-amount received-amount" name="money_received[<?= $i ?>][amount]" value="<?= h($row['amount'] ?? '') ?>"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="total-box">
            <div class="label">Total Received:</div>
            <div class="value">
                <span class="dollar">$</span>
                <input type="text" id="total_received" name="total_received_display" value="<?= h(number_format($totalReceived, 2, '.', '')) ?>" readonly>
            </div>
        </div>

        <div class="section-title" style="margin-top: 6px;">Approved Expenses</div>
        <table class="sheet expense-table">
            <colgroup><col><col><col><col></colgroup>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>To Whom / Purpose</th>
                    <th>Check No.</th>
                    <th>Amount $</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 4; $i++): $row = $expenseRows[$i] ?? ['date' => '', 'purpose' => '', 'check_no' => '', 'amount' => '']; ?>
                <tr>
                    <td><input class="cell-input center" name="approved_expenses[<?= $i ?>][date]" value="<?= h($row['date'] ?? '') ?>"></td>
                    <td><input class="cell-input" name="approved_expenses[<?= $i ?>][purpose]" value="<?= h($row['purpose'] ?? '') ?>"></td>
                    <td><input class="cell-input center" name="approved_expenses[<?= $i ?>][check_no]" value="<?= h($row['check_no'] ?? '') ?>"></td>
                    <td><input class="cell-input right money-amount spent-amount" name="approved_expenses[<?= $i ?>][amount]" value="<?= h($row['amount'] ?? '') ?>"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="total-box">
            <div class="label">Total Spent:</div>
            <div class="value">
                <span class="dollar">$</span>
                <input type="text" id="total_spent" name="total_spent_display" value="<?= h(number_format($totalSpent, 2, '.', '')) ?>" readonly>
            </div>
        </div>

        <div class="section-title" style="margin-top: 8px;">Merchandise Inventory</div>
        <table class="sheet inventory-table">
            <colgroup><col><col><col><col></colgroup>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item Description</th>
                    <th>Inventory Taken</th>
                    <th>Number Remaining</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 4; $i++): $row = $inventoryRows[$i] ?? ['date' => '', 'description' => '', 'taken' => '', 'remaining' => '']; ?>
                <tr>
                    <td><input class="cell-input center" name="merchandise_inventory[<?= $i ?>][date]" value="<?= h($row['date'] ?? '') ?>"></td>
                    <td><input class="cell-input" name="merchandise_inventory[<?= $i ?>][description]" value="<?= h($row['description'] ?? '') ?>"></td>
                    <td>
                        <select class="cell-select center" name="merchandise_inventory[<?= $i ?>][taken]">
                            <option value=""></option>
                            <option value="yes" <?= (($row['taken'] ?? '') === 'yes') ? 'selected' : '' ?>>yes / no</option>
                            <option value="no" <?= (($row['taken'] ?? '') === 'no') ? 'selected' : '' ?>>no / yes</option>
                        </select>
                    </td>
                    <td><input class="cell-input center" name="merchandise_inventory[<?= $i ?>][remaining]" value="<?= h($row['remaining'] ?? '') ?>"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="formula-row">
            <div class="formula-box">
                <div class="formula-rect">
                    <span class="dollar">$</span>
                    <input type="text" id="beginning_balance" name="beginning_balance" value="<?= h($record['beginning_balance'] !== '' ? number_format((float)$record['beginning_balance'], 2, '.', '') : '') ?>">
                </div>
                <div class="formula-label">Beginning Balance</div>
            </div>
            <div class="formula-symbol">+</div>
            <div class="formula-box">
                <div class="formula-rect readonly">
                    <span class="dollar">$</span>
                    <input type="text" id="formula_total_received" value="<?= h(number_format($totalReceived, 2, '.', '')) ?>" readonly>
                </div>
                <div class="formula-label">Total Received</div>
            </div>
            <div class="formula-symbol">-</div>
            <div class="formula-box">
                <div class="formula-rect readonly">
                    <span class="dollar">$</span>
                    <input type="text" id="formula_total_spent" value="<?= h(number_format($totalSpent, 2, '.', '')) ?>" readonly>
                </div>
                <div class="formula-label">Total Spent</div>
            </div>
            <div class="formula-symbol">=</div>
            <div class="formula-box">
                <div class="formula-rect readonly">
                    <span class="dollar">$</span>
                    <input type="text" id="ending_balance" value="<?= h(number_format($endingBalance, 2, '.', '')) ?>" readonly>
                </div>
                <div class="formula-label">Ending Balance</div>
            </div>
        </div>
    </form>

    <script>
        const form = document.getElementById('reportForm');
        const saveStatus = document.getElementById('saveStatus');
        const recordIdInput = document.getElementById('record_id');
        const totalReceivedInput = document.getElementById('total_received');
        const totalSpentInput = document.getElementById('total_spent');
        const formulaTotalReceived = document.getElementById('formula_total_received');
        const formulaTotalSpent = document.getElementById('formula_total_spent');
        const beginningBalance = document.getElementById('beginning_balance');
        const endingBalance = document.getElementById('ending_balance');

        let saveTimer = null;
        let isSaving = false;

        function parseMoney(value) {
            const clean = String(value || '').replace(/[^0-9.-]/g, '');
            const num = parseFloat(clean);
            return Number.isFinite(num) ? num : 0;
        }

        function moneyFormat(num) {
            return Number(num || 0).toFixed(2);
        }

        function updateTotals() {
            let totalReceived = 0;
            document.querySelectorAll('.received-amount').forEach((input) => {
                totalReceived += parseMoney(input.value);
            });

            let totalSpent = 0;
            document.querySelectorAll('.spent-amount').forEach((input) => {
                totalSpent += parseMoney(input.value);
            });

            const begin = parseMoney(beginningBalance.value);
            const end = begin + totalReceived - totalSpent;

            totalReceivedInput.value = moneyFormat(totalReceived);
            formulaTotalReceived.value = moneyFormat(totalReceived);
            totalSpentInput.value = moneyFormat(totalSpent);
            formulaTotalSpent.value = moneyFormat(totalSpent);
            endingBalance.value = moneyFormat(end);
        }

        function queueSave() {
            updateTotals();
            saveStatus.textContent = 'Saving...';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveForm, 700);
        }

        async function saveForm() {
            if (isSaving) {
                return;
            }
            isSaving = true;
            updateTotals();
            saveStatus.textContent = 'Saving...';

            const formData = new FormData(form);
            formData.append('ajax', 'save');

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Save failed');
                }
                recordIdInput.value = data.record_id;
                totalReceivedInput.value = data.totals.total_received;
                formulaTotalReceived.value = data.totals.total_received;
                totalSpentInput.value = data.totals.total_spent;
                formulaTotalSpent.value = data.totals.total_spent;
                endingBalance.value = data.totals.ending_balance;
                saveStatus.textContent = 'Saved';
            } catch (error) {
                saveStatus.textContent = 'Save failed';
                console.error(error);
            } finally {
                isSaving = false;
            }
        }

        form.querySelectorAll('input, select').forEach((field) => {
            field.addEventListener('input', queueSave);
            field.addEventListener('change', queueSave);
        });

        updateTotals();
    </script>
</body>
</html>
