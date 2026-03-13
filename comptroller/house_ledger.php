<?php
/**
 * Oxford House - Equal Expense Share House Ledger
 * Single-file PHP app
 * - Closely matches uploaded ledger layout
 * - Auto-save to MySQL
 * - History search by house name + week dates
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 */

declare(strict_types=1);

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

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function normalizeDate(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $formats = ['Y-m-d', 'm/d/Y', 'n/j/Y', 'm-d-Y', 'n-j-Y'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }

    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : null;
}

function displayDate(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('m/d/Y', $ts) : '';
}

function emptyRow(): array
{
    return [
        'member_name' => '',
        'previous_balance' => '',
        'ees_due' => '',
        'fines_other' => '',
        'approved_receipts' => '',
        'total' => '',
        'amount_paid' => '',
        'ending_balance' => '',
        'ending_status' => '',
    ];
}

function buildRowsFromPost(): array
{
    $rows = [];
    for ($i = 0; $i < 16; $i++) {
        $rows[] = [
            'member_name' => trim((string)($_POST['member_name'][$i] ?? '')),
            'previous_balance' => trim((string)($_POST['previous_balance'][$i] ?? '')),
            'ees_due' => trim((string)($_POST['ees_due'][$i] ?? '')),
            'fines_other' => trim((string)($_POST['fines_other'][$i] ?? '')),
            'approved_receipts' => trim((string)($_POST['approved_receipts'][$i] ?? '')),
            'total' => trim((string)($_POST['total'][$i] ?? '')),
            'amount_paid' => trim((string)($_POST['amount_paid'][$i] ?? '')),
            'ending_balance' => trim((string)($_POST['ending_balance'][$i] ?? '')),
            'ending_status' => trim((string)($_POST['ending_status'][$i] ?? '')),
        ];
    }
    return $rows;
}

function defaultRows(): array
{
    $rows = [];
    for ($i = 0; $i < 16; $i++) {
        $rows[] = emptyRow();
    }
    return $rows;
}

/* =========================
   SCHEMA
========================= */
$pdo->exec("CREATE TABLE IF NOT EXISTS oxford_house_ledger_forms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    week_start DATE DEFAULT NULL,
    week_end DATE DEFAULT NULL,
    notes LONGTEXT DEFAULT NULL,
    rows_json LONGTEXT DEFAULT NULL,
    totals_json LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_house_name (house_name),
    KEY idx_week_start (week_start),
    KEY idx_week_end (week_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

/* =========================
   AJAX ACTIONS
========================= */
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $action = (string)$_POST['ajax_action'];

        if ($action === 'autosave') {
            $id = isset($_POST['record_id']) && $_POST['record_id'] !== '' ? (int)$_POST['record_id'] : 0;
            $houseName = trim((string)($_POST['house_name'] ?? ''));
            $weekStart = normalizeDate($_POST['week_start'] ?? null);
            $weekEnd = normalizeDate($_POST['week_end'] ?? null);
            $notes = trim((string)($_POST['notes'] ?? ''));
            $rows = buildRowsFromPost();
            $totals = [
                'previous_balance_total' => (string)($_POST['previous_balance_total'] ?? ''),
                'ees_due_total' => (string)($_POST['ees_due_total'] ?? ''),
                'fines_other_total' => (string)($_POST['fines_other_total'] ?? ''),
                'approved_receipts_total' => (string)($_POST['approved_receipts_total'] ?? ''),
                'total_total' => (string)($_POST['total_total'] ?? ''),
                'amount_paid_total' => (string)($_POST['amount_paid_total'] ?? ''),
                'ending_behind_total' => (string)($_POST['ending_behind_total'] ?? ''),
                'ending_ahead_total' => (string)($_POST['ending_ahead_total'] ?? ''),
            ];

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE oxford_house_ledger_forms
                    SET house_name = :house_name,
                        week_start = :week_start,
                        week_end = :week_end,
                        notes = :notes,
                        rows_json = :rows_json,
                        totals_json = :totals_json
                    WHERE id = :id");
                $stmt->execute([
                    ':house_name' => $houseName,
                    ':week_start' => $weekStart,
                    ':week_end' => $weekEnd,
                    ':notes' => $notes,
                    ':rows_json' => json_encode($rows, JSON_UNESCAPED_UNICODE),
                    ':totals_json' => json_encode($totals, JSON_UNESCAPED_UNICODE),
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO oxford_house_ledger_forms
                    (house_name, week_start, week_end, notes, rows_json, totals_json)
                    VALUES (:house_name, :week_start, :week_end, :notes, :rows_json, :totals_json)");
                $stmt->execute([
                    ':house_name' => $houseName,
                    ':week_start' => $weekStart,
                    ':week_end' => $weekEnd,
                    ':notes' => $notes,
                    ':rows_json' => json_encode($rows, JSON_UNESCAPED_UNICODE),
                    ':totals_json' => json_encode($totals, JSON_UNESCAPED_UNICODE),
                ]);
                $id = (int)$pdo->lastInsertId();
            }

            echo json_encode([
                'ok' => true,
                'record_id' => $id,
                'saved_at' => date('m/d/Y h:i:s A'),
            ]);
            exit;
        }

        if ($action === 'history_by_house') {
            $houseName = trim((string)($_POST['house_name'] ?? ''));
            if ($houseName === '') {
                echo json_encode(['ok' => true, 'records' => []]);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id, house_name, week_start, week_end, updated_at
                                  FROM oxford_house_ledger_forms
                                  WHERE house_name = :house_name
                                  ORDER BY week_start DESC, week_end DESC, updated_at DESC");
            $stmt->execute([':house_name' => $houseName]);
            $records = $stmt->fetchAll();

            foreach ($records as &$record) {
                $record['week_start_display'] = displayDate($record['week_start']);
                $record['week_end_display'] = displayDate($record['week_end']);
            }

            echo json_encode(['ok' => true, 'records' => $records]);
            exit;
        }

        echo json_encode(['ok' => false, 'message' => 'Invalid action.']);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

/* =========================
   LOAD RECORD / INITIAL DATA
========================= */
$form = [
    'id' => 0,
    'house_name' => '',
    'week_start' => '',
    'week_end' => '',
    'notes' => '',
    'rows' => defaultRows(),
    'totals' => [
        'previous_balance_total' => '',
        'ees_due_total' => '',
        'fines_other_total' => '',
        'approved_receipts_total' => '',
        'total_total' => '',
        'amount_paid_total' => '',
        'ending_behind_total' => '',
        'ending_ahead_total' => '',
    ],
];

$loadId = isset($_GET['load_id']) ? (int)$_GET['load_id'] : 0;
if ($loadId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM oxford_house_ledger_forms WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loadId]);
    $record = $stmt->fetch();
    if ($record) {
        $rows = json_decode((string)$record['rows_json'], true);
        $totals = json_decode((string)$record['totals_json'], true);

        $form['id'] = (int)$record['id'];
        $form['house_name'] = (string)$record['house_name'];
        $form['week_start'] = (string)($record['week_start'] ?? '');
        $form['week_end'] = (string)($record['week_end'] ?? '');
        $form['notes'] = (string)($record['notes'] ?? '');
        $form['rows'] = is_array($rows) ? array_replace(defaultRows(), $rows) : defaultRows();
        $form['totals'] = is_array($totals) ? array_merge($form['totals'], $totals) : $form['totals'];
    }
}

$houseNames = $pdo->query("SELECT DISTINCT house_name FROM oxford_house_ledger_forms WHERE house_name <> '' ORDER BY house_name ASC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Equal Expense Share House Ledger</title>
    <style>
        @page { size: letter portrait; margin: 0.35in; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e9e9e9;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
        }
        .app-shell {
            max-width: 1080px;
            margin: 16px auto;
            padding: 12px;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: end;
            background: #fff;
            border: 1px solid #cfcfcf;
            padding: 12px;
            margin-bottom: 12px;
        }
        .toolbar-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 180px;
        }
        .toolbar label {
            font-size: 12px;
            font-weight: 700;
        }
        .toolbar input,
        .toolbar select,
        .toolbar button {
            height: 38px;
            padding: 6px 10px;
            font-size: 14px;
        }
        .toolbar button {
            cursor: pointer;
            border: 1px solid #999;
            background: #f5f5f5;
            font-weight: 700;
        }
        .statusbar {
            font-size: 13px;
            font-weight: 700;
            min-height: 20px;
            color: #0a5d1a;
        }
        .sheet {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: #f3f3f3;
            border: 1px solid #999;
            padding: 14px 14px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .top-header {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 8px;
            align-items: start;
        }
        .logo-wrap img {
            width: 104px;
            height: auto;
            display: block;
        }
        .title-wrap {
            text-align: center;
            padding-top: 2px;
        }
        .title-line {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.05;
        }
        .house-line {
            display: inline-flex;
            align-items: center;
            min-width: 280px;
            border-bottom: 2px solid #333;
            margin-top: 2px;
            padding: 0 4px 1px;
        }
        .house-line input {
            border: 0;
            background: transparent;
            width: 100%;
            font-size: 24px;
            font-weight: 700;
            text-align: left;
            outline: none;
        }
        .main-title {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.05;
            margin-top: 2px;
        }
        .subtext {
            font-size: 14px;
            text-align: center;
            margin-top: 4px;
            line-height: 1.2;
        }
        .week-row {
            margin: 18px 0 14px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            align-items: center;
        }
        .week-block {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 700;
        }
        .week-block input {
            width: 145px;
            border: 0;
            border-bottom: 2px solid #333;
            background: transparent;
            font-size: 17px;
            font-weight: 700;
            text-align: center;
            outline: none;
            padding: 2px 4px;
        }
        table.ledger {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: #f7f7f7;
        }
        table.ledger col.member   { width: 25%; }
        table.ledger col.money    { width: 10.71%; }
        table.ledger th,
        table.ledger td {
            border: 2px solid #444;
            padding: 0;
            vertical-align: middle;
        }
        table.ledger thead th {
            border: 0;
            background: transparent;
            padding-bottom: 4px;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.05;
            text-align: center;
        }
        .formula-row th {
            border: 0 !important;
            font-size: 18px !important;
            padding-bottom: 3px !important;
        }
        .grid-row td {
            height: 30px;
            background: #f7f7f7;
        }
        .grid-row input,
        .grid-row select {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            padding: 4px 6px;
            font-size: 14px;
            outline: none;
        }
        .grid-row .money-input {
            text-align: center;
        }
        .totals-wrap {
            display: grid;
            grid-template-columns: 25% 10.71% 10.71% 10.71% 10.71% 10.71% 10.71% 10.71%;
            width: 100%;
            margin-top: 0;
        }
        .totals-label {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            font-size: 18px;
            font-weight: 800;
            min-height: 58px;
        }
        .totals-box,
        .status-stack {
            border-left: 2px solid #444;
            border-right: 2px solid #444;
            border-bottom: 2px solid #444;
            min-height: 58px;
            background: #f7f7f7;
        }
        .totals-box input {
            width: 100%;
            height: 58px;
            border: 0;
            background: transparent;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            outline: none;
        }
        .status-stack {
            display: grid;
            grid-template-rows: 1fr 1fr;
        }
        .status-row {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            min-height: 29px;
        }
        .status-row + .status-row {
            border-top: 2px solid #444;
        }
        .status-row input {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            outline: none;
            padding: 3px 6px;
        }
        .status-row span {
            font-size: 10px;
            font-weight: 800;
            padding-right: 6px;
        }
        .notes-title {
            font-size: 16px;
            font-weight: 500;
            margin: 10px 0 2px;
        }
        .notes-box {
            border: 2px solid #444;
            background: #f7f7f7;
            min-height: 220px;
        }
        .notes-box textarea {
            width: 100%;
            min-height: 220px;
            border: 0;
            background: transparent;
            padding: 8px;
            resize: vertical;
            font-size: 14px;
            outline: none;
        }
        .footnote {
            margin-top: 8px;
            font-size: 11px;
            color: #444;
        }
        @media print {
            body { background: #fff; }
            .app-shell { max-width: none; margin: 0; padding: 0; }
            .toolbar, .statusbar, .footnote { display: none !important; }
            .sheet {
                box-shadow: none;
                border: 0;
                margin: 0;
                max-width: none;
                padding: 0;
                background: #fff;
            }
            .notes-box textarea,
            .grid-row input,
            .grid-row select,
            .totals-box input,
            .status-row input,
            .week-block input,
            .house-line input {
                color: #000;
            }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <form id="ledgerForm" method="get" action="">
        <input type="hidden" name="load_id" id="load_id" value="<?= h($form['id']) ?>">
    </form>

    <div class="toolbar">
        <div class="toolbar-group" style="min-width:220px;">
            <label for="history_house_name">History: House Name</label>
            <input type="text" id="history_house_name" list="house_name_list" placeholder="Enter or choose house name">
            <datalist id="house_name_list">
                <?php foreach ($houseNames as $house): ?>
                    <option value="<?= h($house) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <div class="toolbar-group" style="min-width:280px;">
            <label for="history_record_id">History: Week Dates</label>
            <select id="history_record_id">
                <option value="">Select saved week</option>
            </select>
        </div>

        <div class="toolbar-group" style="min-width:120px;">
            <label>&nbsp;</label>
            <button type="button" id="loadHistoryBtn">Load Record</button>
        </div>

        <div class="toolbar-group" style="min-width:120px;">
            <label>&nbsp;</label>
            <button type="button" onclick="window.print();">Print</button>
        </div>

        <div class="toolbar-group" style="flex:1; min-width:220px;">
            <label>Save Status</label>
            <div class="statusbar" id="saveStatus">Ready</div>
        </div>
    </div>

    <form id="autosaveForm" method="post" action="">
        <input type="hidden" name="record_id" id="record_id" value="<?= h($form['id']) ?>">

        <div class="sheet">
            <div class="top-header">
                <div class="logo-wrap">
                    <img src="../images/oxford_house_logo.png" alt="Oxford House Logo">
                </div>
                <div class="title-wrap">
                    <div class="title-line">
                        <span>OXFORD HOUSE -</span>
                        <span class="house-line">
                            <input type="text" name="house_name" id="house_name" value="<?= h($form['house_name']) ?>">
                        </span>
                    </div>
                    <div class="main-title">EQUAL EXPENSE SHARE</div>
                    <div class="main-title">HOUSE LEDGER</div>
                    <div class="subtext">Equal Expense Share = EES</div>
                    <div class="subtext">EES should be adjusted based on occupancy.</div>
                </div>
            </div>

            <div class="week-row">
                <div class="week-block">
                    <span>WEEK START:</span>
                    <input type="date" name="week_start" id="week_start" value="<?= h($form['week_start']) ?>">
                </div>
                <div class="week-block">
                    <span>WEEK END:</span>
                    <input type="date" name="week_end" id="week_end" value="<?= h($form['week_end']) ?>">
                </div>
            </div>

            <table class="ledger" aria-label="House Ledger">
                <colgroup>
                    <col class="member">
                    <col class="money"><col class="money"><col class="money"><col class="money"><col class="money"><col class="money"><col class="money">
                </colgroup>
                <thead>
                    <tr>
                        <th>MEMBER<br>NAME</th>
                        <th>PREVIOUS<br>BALANCE</th>
                        <th>EES<br>DUE</th>
                        <th>FINES/<br>OTHER</th>
                        <th>APPROVED<br>RECEIPTS</th>
                        <th>TOTAL</th>
                        <th>AMOUNT<br>PAID</th>
                        <th>ENDING<br>BALANCE</th>
                    </tr>
                    <tr class="formula-row">
                        <th></th>
                        <th> </th>
                        <th>+</th>
                        <th>+</th>
                        <th>-</th>
                        <th>=</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($form['rows'] as $i => $row): ?>
                        <tr class="grid-row">
                            <td><input type="text" name="member_name[]" value="<?= h($row['member_name'] ?? '') ?>"></td>
                            <td><input type="text" class="money-input calc-prev" name="previous_balance[]" value="<?= h($row['previous_balance'] ?? '') ?>"></td>
                            <td><input type="text" class="money-input calc-ees" name="ees_due[]" value="<?= h($row['ees_due'] ?? '') ?>"></td>
                            <td><input type="text" class="money-input calc-fines" name="fines_other[]" value="<?= h($row['fines_other'] ?? '') ?>"></td>
                            <td><input type="text" class="money-input calc-receipts" name="approved_receipts[]" value="<?= h($row['approved_receipts'] ?? '') ?>"></td>
                            <td><input type="text" class="money-input calc-total" name="total[]" value="<?= h($row['total'] ?? '') ?>" readonly></td>
                            <td><input type="text" class="money-input calc-paid" name="amount_paid[]" value="<?= h($row['amount_paid'] ?? '') ?>"></td>
                            <td>
                                <input type="text" class="money-input calc-ending" name="ending_balance[]" value="<?= h($row['ending_balance'] ?? '') ?>" readonly>
                                <input type="hidden" class="ending-status" name="ending_status[]" value="<?= h($row['ending_status'] ?? '') ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals-wrap">
                <div class="totals-label">TOTALS:</div>
                <div class="totals-box"><input type="text" name="previous_balance_total" id="previous_balance_total" value="<?= h($form['totals']['previous_balance_total']) ?>" readonly></div>
                <div class="totals-box"><input type="text" name="ees_due_total" id="ees_due_total" value="<?= h($form['totals']['ees_due_total']) ?>" readonly></div>
                <div class="totals-box"><input type="text" name="fines_other_total" id="fines_other_total" value="<?= h($form['totals']['fines_other_total']) ?>" readonly></div>
                <div class="totals-box"><input type="text" name="approved_receipts_total" id="approved_receipts_total" value="<?= h($form['totals']['approved_receipts_total']) ?>" readonly></div>
                <div class="status-stack">
                    <div class="status-row">
                        <input type="text" name="total_total" id="total_total" value="<?= h($form['totals']['total_total']) ?>" readonly>
                        <span>BEHIND</span>
                    </div>
                    <div class="status-row">
                        <input type="text" value="" readonly tabindex="-1">
                        <span>AHEAD</span>
                    </div>
                </div>
                <div class="totals-box"><input type="text" name="amount_paid_total" id="amount_paid_total" value="<?= h($form['totals']['amount_paid_total']) ?>" readonly></div>
                <div class="status-stack">
                    <div class="status-row">
                        <input type="text" name="ending_behind_total" id="ending_behind_total" value="<?= h($form['totals']['ending_behind_total']) ?>" readonly>
                        <span>BEHIND</span>
                    </div>
                    <div class="status-row">
                        <input type="text" name="ending_ahead_total" id="ending_ahead_total" value="<?= h($form['totals']['ending_ahead_total']) ?>" readonly>
                        <span>AHEAD</span>
                    </div>
                </div>
            </div>

            <div class="notes-title">NOTES</div>
            <div class="notes-box">
                <textarea name="notes" id="notes"><?= h($form['notes']) ?></textarea>
            </div>
        </div>
    </form>

    <div class="footnote">Designed to closely match the uploaded Oxford House ledger sheet.</div>
</div>

<script>
(function(){
    const form = document.getElementById('autosaveForm');
    const recordIdInput = document.getElementById('record_id');
    const saveStatus = document.getElementById('saveStatus');
    const historyHouse = document.getElementById('history_house_name');
    const historySelect = document.getElementById('history_record_id');
    const loadHistoryBtn = document.getElementById('loadHistoryBtn');
    let saveTimer = null;
    let loadingHistory = false;

    function parseMoney(value) {
        value = String(value || '').replace(/[^0-9.-]/g, '').trim();
        const num = parseFloat(value);
        return isNaN(num) ? 0 : num;
    }

    function formatMoney(num) {
        if (!isFinite(num)) return '';
        return num.toFixed(2);
    }

    function updateCalculations() {
        let totalPrev = 0, totalEes = 0, totalFines = 0, totalReceipts = 0, totalTotal = 0, totalPaid = 0;
        let totalEndingBehind = 0, totalEndingAhead = 0;

        const rows = document.querySelectorAll('.grid-row');
        rows.forEach(row => {
            const prev = parseMoney(row.querySelector('.calc-prev').value);
            const ees = parseMoney(row.querySelector('.calc-ees').value);
            const fines = parseMoney(row.querySelector('.calc-fines').value);
            const receipts = parseMoney(row.querySelector('.calc-receipts').value);
            const paid = parseMoney(row.querySelector('.calc-paid').value);

            const total = prev + ees + fines - receipts;
            const ending = total - paid;
            const endingStatus = row.querySelector('.ending-status');

            row.querySelector('.calc-total').value = total === 0 && !row.querySelector('.calc-prev').value && !row.querySelector('.calc-ees').value && !row.querySelector('.calc-fines').value && !row.querySelector('.calc-receipts').value ? '' : formatMoney(total);
            row.querySelector('.calc-ending').value = total === 0 && paid === 0 && !row.querySelector('.calc-prev').value && !row.querySelector('.calc-ees').value && !row.querySelector('.calc-fines').value && !row.querySelector('.calc-receipts').value && !row.querySelector('.calc-paid').value ? '' : formatMoney(Math.abs(ending));

            if (ending > 0) {
                endingStatus.value = 'BEHIND';
                totalEndingBehind += ending;
            } else if (ending < 0) {
                endingStatus.value = 'AHEAD';
                totalEndingAhead += Math.abs(ending);
            } else {
                endingStatus.value = '';
            }

            totalPrev += prev;
            totalEes += ees;
            totalFines += fines;
            totalReceipts += receipts;
            totalTotal += total;
            totalPaid += paid;
        });

        document.getElementById('previous_balance_total').value = formatMoney(totalPrev);
        document.getElementById('ees_due_total').value = formatMoney(totalEes);
        document.getElementById('fines_other_total').value = formatMoney(totalFines);
        document.getElementById('approved_receipts_total').value = formatMoney(totalReceipts);
        document.getElementById('total_total').value = formatMoney(totalTotal);
        document.getElementById('amount_paid_total').value = formatMoney(totalPaid);
        document.getElementById('ending_behind_total').value = totalEndingBehind ? formatMoney(totalEndingBehind) : '';
        document.getElementById('ending_ahead_total').value = totalEndingAhead ? formatMoney(totalEndingAhead) : '';
    }

    function setStatus(text, isError = false) {
        saveStatus.textContent = text;
        saveStatus.style.color = isError ? '#a40000' : '#0a5d1a';
    }

    function queueAutosave() {
        if (loadingHistory) return;
        updateCalculations();
        setStatus('Saving...');
        clearTimeout(saveTimer);
        saveTimer = setTimeout(autosave, 700);
    }

    function autosave() {
        const fd = new FormData(form);
        fd.append('ajax_action', 'autosave');

        fetch(location.href, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) throw new Error(data.message || 'Save failed.');
            if (data.record_id) {
                recordIdInput.value = data.record_id;
            }
            setStatus('Saved ' + (data.saved_at || ''));
            const houseName = document.getElementById('house_name').value.trim();
            if (houseName) {
                historyHouse.value = houseName;
                fetchHistoryByHouse(houseName, recordIdInput.value);
            }
        })
        .catch(err => {
            setStatus(err.message || 'Save failed.', true);
        });
    }

    function fetchHistoryByHouse(houseName, selectedId = '') {
        const fd = new FormData();
        fd.append('ajax_action', 'history_by_house');
        fd.append('house_name', houseName);

        fetch(location.href, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) throw new Error(data.message || 'Unable to load history.');
            historySelect.innerHTML = '<option value="">Select saved week</option>';
            (data.records || []).forEach(rec => {
                const opt = document.createElement('option');
                opt.value = rec.id;
                opt.textContent = `${rec.week_start_display || 'No Start'} - ${rec.week_end_display || 'No End'}`;
                if (String(selectedId) === String(rec.id)) {
                    opt.selected = true;
                }
                historySelect.appendChild(opt);
            });
        })
        .catch(err => {
            setStatus(err.message || 'Unable to load history.', true);
        });
    }

    historyHouse.addEventListener('change', function() {
        const houseName = this.value.trim();
        historySelect.innerHTML = '<option value="">Select saved week</option>';
        if (houseName) {
            fetchHistoryByHouse(houseName);
        }
    });

    loadHistoryBtn.addEventListener('click', function() {
        const id = historySelect.value;
        if (!id) return;
        const url = new URL(window.location.href);
        url.searchParams.set('load_id', id);
        window.location.href = url.toString();
    });

    form.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', queueAutosave);
        el.addEventListener('change', queueAutosave);
    });

    updateCalculations();
    const currentHouse = document.getElementById('house_name').value.trim();
    if (currentHouse) {
        historyHouse.value = currentHouse;
        fetchHistoryByHouse(currentHouse, recordIdInput.value);
    }
})();
</script>
</body>
</html>
