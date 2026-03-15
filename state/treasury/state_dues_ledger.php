<?php
declare(strict_types=1);

/**
 * State Association Dues Ledger
 * Single-file PHP/MySQL app
 * - Fillable ledger closely matching uploaded sheet
 * - Auto-save to MySQL
 * - History dropdown by ledger date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';
$chapterLabels = ['1','2','3','4','5','6','7','8','9','10','13','Rural','',''];

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_money($value): string {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = str_replace([',', '$', ' '], '', $value);
    if (!is_numeric($value)) {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function to_float($value): float {
    $value = trim((string)$value);
    if ($value === '') {
        return 0.0;
    }
    $value = str_replace([',', '$', ' '], '', $value);
    return is_numeric($value) ? (float)$value : 0.0;
}

function build_default_rows(array $labels): array {
    $rows = [];
    foreach ($labels as $label) {
        $rows[] = [
            'chapter' => $label,
            'beds' => '',
            'past_due' => '',
            'current_dues' => '',
            'fines' => '',
            'bed_dues' => '',
            'total_due' => '',
            'amount_paid' => '',
            'ending_balance' => '',
            'loan_balance' => '',
            'loan_payment' => '',
            'ending_loan_balance' => '',
        ];
    }
    return $rows;
}

function sanitize_rows(array $rows, array $labels): array {
    $clean = [];
    foreach ($labels as $i => $label) {
        $row = $rows[$i] ?? [];
        $beds = trim((string)($row['beds'] ?? ''));
        $pastDue = normalize_money($row['past_due'] ?? '');
        $currentDues = normalize_money($row['current_dues'] ?? '');
        $fines = normalize_money($row['fines'] ?? '');
        $bedDues = normalize_money($row['bed_dues'] ?? '');
        $amountPaid = normalize_money($row['amount_paid'] ?? '');
        $loanBalance = normalize_money($row['loan_balance'] ?? '');
        $loanPayment = normalize_money($row['loan_payment'] ?? '');

        $totalDue = to_float($pastDue) + to_float($currentDues) + to_float($fines) + to_float($bedDues);
        $endingBalance = $totalDue - to_float($amountPaid);
        $endingLoanBal = to_float($loanBalance) - to_float($loanPayment);

        $clean[] = [
            'chapter' => $label,
            'beds' => $beds,
            'past_due' => $pastDue,
            'current_dues' => $currentDues,
            'fines' => $fines,
            'bed_dues' => $bedDues,
            'total_due' => $totalDue !== 0.0 || $pastDue !== '' || $currentDues !== '' || $fines !== '' || $bedDues !== '' ? number_format($totalDue, 2, '.', '') : '',
            'amount_paid' => $amountPaid,
            'ending_balance' => $endingBalance !== 0.0 || $amountPaid !== '' || $pastDue !== '' || $currentDues !== '' || $fines !== '' || $bedDues !== '' ? number_format($endingBalance, 2, '.', '') : '',
            'loan_balance' => $loanBalance,
            'loan_payment' => $loanPayment,
            'ending_loan_balance' => $endingLoanBal !== 0.0 || $loanBalance !== '' || $loanPayment !== '' ? number_format($endingLoanBal, 2, '.', '') : '',
        ];
    }
    return $clean;
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
    "CREATE TABLE IF NOT EXISTS state_dues_ledger_records (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ledger_date DATE NOT NULL,
        month_label VARCHAR(30) NOT NULL DEFAULT '',
        year_label VARCHAR(10) NOT NULL DEFAULT '',
        amount_per_bed DECIMAL(10,2) DEFAULT NULL,
        rows_json LONGTEXT NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_ledger_date (ledger_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'autosave') {
    $ledgerDate = trim((string)($_POST['ledger_date'] ?? ''));
    $monthLabel = trim((string)($_POST['month_label'] ?? ''));
    $yearLabel = trim((string)($_POST['year_label'] ?? ''));
    $amountPerBed = normalize_money($_POST['amount_per_bed'] ?? '');
    $rows = json_decode((string)($_POST['rows_json'] ?? '[]'), true);

    if (!$ledgerDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ledgerDate)) {
        json_response(['ok' => false, 'message' => 'A valid ledger date is required.'], 422);
    }

    if (!is_array($rows)) {
        $rows = [];
    }

    $rows = sanitize_rows($rows, $chapterLabels);
    $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $sql = "INSERT INTO state_dues_ledger_records (ledger_date, month_label, year_label, amount_per_bed, rows_json)
            VALUES (:ledger_date, :month_label, :year_label, :amount_per_bed, :rows_json)
            ON DUPLICATE KEY UPDATE
                month_label = VALUES(month_label),
                year_label = VALUES(year_label),
                amount_per_bed = VALUES(amount_per_bed),
                rows_json = VALUES(rows_json)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ledger_date' => $ledgerDate,
        ':month_label' => $monthLabel,
        ':year_label' => $yearLabel,
        ':amount_per_bed' => $amountPerBed !== '' ? $amountPerBed : null,
        ':rows_json' => $json,
    ]);

    $savedId = (int)$pdo->query("SELECT id FROM state_dues_ledger_records WHERE ledger_date = " . $pdo->quote($ledgerDate))->fetchColumn();

    json_response([
        'ok' => true,
        'message' => 'Auto-saved',
        'id' => $savedId,
        'ledger_date' => $ledgerDate,
        'rows' => $rows,
    ]);
}

if ($action === 'load') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        json_response(['ok' => false, 'message' => 'Invalid record ID.'], 422);
    }

    $stmt = $pdo->prepare("SELECT * FROM state_dues_ledger_records WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        json_response(['ok' => false, 'message' => 'Record not found.'], 404);
    }

    $rows = json_decode((string)$record['rows_json'], true);
    if (!is_array($rows)) {
        $rows = build_default_rows($chapterLabels);
    }
    $rows = sanitize_rows($rows, $chapterLabels);

    json_response([
        'ok' => true,
        'record' => [
            'id' => (int)$record['id'],
            'ledger_date' => $record['ledger_date'],
            'month_label' => $record['month_label'],
            'year_label' => $record['year_label'],
            'amount_per_bed' => $record['amount_per_bed'] !== null ? number_format((float)$record['amount_per_bed'], 2, '.', '') : '',
            'rows' => $rows,
        ],
    ]);
}

$history = $pdo->query("SELECT id, ledger_date, month_label, year_label, updated_at FROM state_dues_ledger_records ORDER BY ledger_date DESC, id DESC")->fetchAll();
$today = date('Y-m-d');
$defaultRows = build_default_rows($chapterLabels);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>State Association Dues Ledger</title>
    <style>
        :root {
            --line: #1b1b1b;
            --bg: #efefef;
            --font: Arial, Helvetica, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #dcdcdc;
            font-family: var(--font);
            color: #111;
        }
        .page-wrap {
            padding: 16px;
        }
        .toolbar {
            max-width: 1300px;
            margin: 0 auto 14px;
            background: #fff;
            border: 1px solid #cfcfcf;
            padding: 12px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            border-radius: 8px;
        }
        .toolbar-left,
        .toolbar-right {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .toolbar label {
            font-size: 13px;
            font-weight: 700;
        }
        .toolbar input,
        .toolbar select,
        .toolbar button {
            height: 36px;
            font-size: 14px;
            padding: 6px 10px;
        }
        .toolbar button {
            cursor: pointer;
            border: 1px solid #777;
            background: #f7f7f7;
            border-radius: 6px;
            font-weight: 700;
        }
        .status {
            font-size: 12px;
            font-weight: 700;
            min-width: 110px;
        }
        .sheet {
            width: 1180px;
            margin: 0 auto;
            background: var(--bg);
            border: 0;
            padding: 10px 14px 14px;
            position: relative;
        }
        .sheet-inner {
            width: 100%;
        }
        .title-row {
            display: grid;
            grid-template-columns: 1.55fr 0.9fr 0.78fr;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }
        .title-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .logo {
            width: 84px;
            height: 84px;
            object-fit: contain;
            flex: 0 0 auto;
        }
        .main-title {
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }
        .amount-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 18px;
            font-weight: 700;
            white-space: nowrap;
        }
        .amount-line {
            width: 104px;
            border: 0;
            border-bottom: 3px solid #444;
            background: transparent;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            outline: none;
            padding: 0 2px 2px;
            border-radius: 0;
        }
        .month-year-box {
            width: 238px;
            justify-self: end;
        }
        table { border-collapse: collapse; }
        .meta-table,
        .ledger-table,
        .loan-table {
            width: 100%;
            table-layout: fixed;
        }
        .meta-table th,
        .meta-table td,
        .ledger-table th,
        .ledger-table td,
        .loan-table th,
        .loan-table td {
            border: 1.6px solid var(--line);
            padding: 0;
            background: rgba(255,255,255,0.22);
        }
        .meta-table th,
        .ledger-table th,
        .loan-table th {
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            height: 50px;
            line-height: 1.05;
            background: rgba(255,255,255,0.18);
        }
        .meta-table td {
            height: 50px;
        }
        .meta-label { width: 56%; }
        .meta-input {
            width: 100%;
            height: 48px;
            border: 0;
            background: transparent;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            outline: none;
        }
        .tables-row {
            display: grid;
            grid-template-columns: 158px 18px 80px 22px 80px 22px 80px 22px 80px 22px 238px 26px 238px;
            align-items: start;
        }
        .op-col {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            font-size: 34px;
            font-weight: 700;
            padding-top: 6px;
            line-height: 48px;
        }
        .eq-col {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            font-size: 34px;
            font-weight: 700;
            padding-top: 6px;
            line-height: 48px;
        }
        .ledger-table td,
        .loan-table td { height: 41px; }
        .cell-input {
            width: 100%;
            height: 39px;
            border: 0;
            background: transparent;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            outline: none;
            padding: 2px 4px;
        }
        .left-label {
            font-size: 15px;
            font-weight: 700;
            text-align: center;
        }
        .readonly {
            background: rgba(0,0,0,0.03);
        }
        @media print {
            body {
                background: #fff;
            }
            .page-wrap {
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .sheet {
                width: 100%;
                padding: 8px 10px;
                background: #fff;
            }
            @page {
                size: landscape;
                margin: 0.3in;
            }
        }
        @media (max-width: 1300px) {
            body { overflow-x: auto; }
            .sheet { margin-left: 16px; margin-right: 16px; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="toolbar">
        <div class="toolbar-left">
            <label>
                History by date:
                <select id="historySelect">
                    <option value="">New / Select saved record</option>
                    <?php foreach ($history as $item): ?>
                        <option value="<?= (int)$item['id'] ?>">
                            <?= h($item['ledger_date']) ?><?= $item['month_label'] !== '' ? ' - ' . h($item['month_label']) : '' ?><?= $item['year_label'] !== '' ? ' ' . h($item['year_label']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Record date:
                <input type="date" id="ledgerDate" value="<?= h($today) ?>">
            </label>
        </div>
        <div class="toolbar-right">
            <button type="button" id="newBtn">New</button>
            <button type="button" id="printBtn">Print</button>
            <span class="status" id="saveStatus">Ready</span>
        </div>
    </div>

    <div class="sheet">
        <div class="sheet-inner">
            <div class="title-row">
                <div class="title-left">
                    <img src="<?= h($logoPath) ?>" alt="Oxford House Logo" class="logo">
                    <div class="main-title">State Association Dues Ledger</div>
                </div>
                <div class="amount-box">
                    <span>Amount per bed $</span>
                    <input class="amount-line" type="text" id="amountPerBed" inputmode="decimal" autocomplete="off">
                </div>
                <div class="month-year-box">
                    <table class="meta-table">
                        <tr>
                            <th class="meta-label">Month</th>
                            <td><input type="text" id="monthLabel" class="meta-input" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <th class="meta-label">Year</th>
                            <td><input type="text" id="yearLabel" class="meta-input" autocomplete="off"></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="tables-row">
                <table class="ledger-table">
                    <colgroup>
                        <col style="width: 78px;">
                        <col style="width: 80px;">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>Chapter</th>
                        <th>Beds</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr>
                            <td class="left-label"><?= h($row['chapter']) ?></td>
                            <td><input type="text" class="cell-input beds" data-row="<?= $i ?>" inputmode="numeric" autocomplete="off"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="op-col"> </div>

                <table class="ledger-table">
                    <thead><tr><th>Past<br>Due</th></tr></thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr><td><input type="text" class="cell-input money past_due" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="op-col">+</div>

                <table class="ledger-table">
                    <thead><tr><th>Current<br>Dues</th></tr></thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr><td><input type="text" class="cell-input money current_dues" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="op-col">+</div>

                <table class="ledger-table">
                    <thead><tr><th>Fines</th></tr></thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr><td><input type="text" class="cell-input money fines" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="op-col">+</div>

                <table class="ledger-table">
                    <thead><tr><th>Bed<br>Dues</th></tr></thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr><td><input type="text" class="cell-input money bed_dues" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="eq-col">=</div>

                <table class="ledger-table">
                    <colgroup>
                        <col style="width: 78px;">
                        <col style="width: 78px;">
                        <col style="width: 82px;">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>Total<br>Due</th>
                        <th>Amount<br>Paid</th>
                        <th>Ending<br>Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr>
                            <td><input type="text" class="cell-input readonly total_due" data-row="<?= $i ?>" readonly></td>
                            <td><input type="text" class="cell-input money amount_paid" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td>
                            <td><input type="text" class="cell-input readonly ending_balance" data-row="<?= $i ?>" readonly></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="op-col"> </div>

                <table class="loan-table">
                    <colgroup>
                        <col style="width: 78px;">
                        <col style="width: 78px;">
                        <col style="width: 82px;">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>Loan<br>Balance</th>
                        <th>Loan<br>Payment</th>
                        <th>Ending<br>Loan Bal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($defaultRows as $i => $row): ?>
                        <tr>
                            <td><input type="text" class="cell-input money loan_balance" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td>
                            <td><input type="text" class="cell-input money loan_payment" data-row="<?= $i ?>" inputmode="decimal" autocomplete="off"></td>
                            <td><input type="text" class="cell-input readonly ending_loan_balance" data-row="<?= $i ?>" readonly></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const chapterLabels = <?php echo json_encode($chapterLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const statusEl = document.getElementById('saveStatus');
const historySelect = document.getElementById('historySelect');
const ledgerDate = document.getElementById('ledgerDate');
const monthLabel = document.getElementById('monthLabel');
const yearLabel = document.getElementById('yearLabel');
const amountPerBed = document.getElementById('amountPerBed');
const newBtn = document.getElementById('newBtn');
const printBtn = document.getElementById('printBtn');
let saveTimer = null;
let loadingRecord = false;

function moneyToNumber(value) {
    value = String(value ?? '').replace(/[$,\s]/g, '').trim();
    const num = parseFloat(value);
    return Number.isFinite(num) ? num : 0;
}

function formatMoney(value) {
    if (value === '' || value === null || value === undefined) return '';
    const num = Number(value);
    if (!Number.isFinite(num)) return '';
    return num.toFixed(2);
}

function allRows() {
    const rows = [];
    for (let i = 0; i < chapterLabels.length; i++) {
        rows.push({
            chapter: chapterLabels[i],
            beds: document.querySelector(`.beds[data-row="${i}"]`).value,
            past_due: document.querySelector(`.past_due[data-row="${i}"]`).value,
            current_dues: document.querySelector(`.current_dues[data-row="${i}"]`).value,
            fines: document.querySelector(`.fines[data-row="${i}"]`).value,
            bed_dues: document.querySelector(`.bed_dues[data-row="${i}"]`).value,
            total_due: document.querySelector(`.total_due[data-row="${i}"]`).value,
            amount_paid: document.querySelector(`.amount_paid[data-row="${i}"]`).value,
            ending_balance: document.querySelector(`.ending_balance[data-row="${i}"]`).value,
            loan_balance: document.querySelector(`.loan_balance[data-row="${i}"]`).value,
            loan_payment: document.querySelector(`.loan_payment[data-row="${i}"]`).value,
            ending_loan_balance: document.querySelector(`.ending_loan_balance[data-row="${i}"]`).value,
        });
    }
    return rows;
}

function recalcRow(i) {
    const pastDue = moneyToNumber(document.querySelector(`.past_due[data-row="${i}"]`).value);
    const currentDues = moneyToNumber(document.querySelector(`.current_dues[data-row="${i}"]`).value);
    const fines = moneyToNumber(document.querySelector(`.fines[data-row="${i}"]`).value);
    const bedDues = moneyToNumber(document.querySelector(`.bed_dues[data-row="${i}"]`).value);
    const amountPaid = moneyToNumber(document.querySelector(`.amount_paid[data-row="${i}"]`).value);
    const loanBalance = moneyToNumber(document.querySelector(`.loan_balance[data-row="${i}"]`).value);
    const loanPayment = moneyToNumber(document.querySelector(`.loan_payment[data-row="${i}"]`).value);

    const totalDue = pastDue + currentDues + fines + bedDues;
    const endingBalance = totalDue - amountPaid;
    const endingLoanBal = loanBalance - loanPayment;

    document.querySelector(`.total_due[data-row="${i}"]`).value = (pastDue || currentDues || fines || bedDues) ? formatMoney(totalDue) : '';
    document.querySelector(`.ending_balance[data-row="${i}"]`).value = (pastDue || currentDues || fines || bedDues || amountPaid) ? formatMoney(endingBalance) : '';
    document.querySelector(`.ending_loan_balance[data-row="${i}"]`).value = (loanBalance || loanPayment) ? formatMoney(endingLoanBal) : '';
}

function recalcAll() {
    for (let i = 0; i < chapterLabels.length; i++) {
        recalcRow(i);
    }
}

function setStatus(text, bad = false) {
    statusEl.textContent = text;
    statusEl.style.color = bad ? '#a10000' : '#1b4d1b';
}

function scheduleSave() {
    if (loadingRecord) return;
    clearTimeout(saveTimer);
    setStatus('Saving...');
    saveTimer = setTimeout(doSave, 500);
}

async function doSave() {
    if (!ledgerDate.value) {
        setStatus('Date required', true);
        return;
    }
    recalcAll();
    const formData = new FormData();
    formData.append('action', 'autosave');
    formData.append('ledger_date', ledgerDate.value);
    formData.append('month_label', monthLabel.value);
    formData.append('year_label', yearLabel.value);
    formData.append('amount_per_bed', amountPerBed.value);
    formData.append('rows_json', JSON.stringify(allRows()));

    try {
        const res = await fetch('', { method: 'POST', body: formData });
        const data = await res.json();
        if (!data.ok) throw new Error(data.message || 'Save failed');
        setStatus('Saved');
        refreshHistoryOption(data.id, data.ledger_date);
    } catch (err) {
        setStatus(err.message || 'Save failed', true);
    }
}

function refreshHistoryOption(id, dateLabel) {
    if (!id) return;
    let found = false;
    [...historySelect.options].forEach(opt => {
        if (opt.value === String(id)) {
            opt.textContent = dateLabel + (monthLabel.value ? ' - ' + monthLabel.value : '') + (yearLabel.value ? ' ' + yearLabel.value : '');
            found = true;
        }
    });
    if (!found) {
        const opt = document.createElement('option');
        opt.value = String(id);
        opt.textContent = dateLabel + (monthLabel.value ? ' - ' + monthLabel.value : '') + (yearLabel.value ? ' ' + yearLabel.value : '');
        historySelect.appendChild(opt);
    }
    historySelect.value = String(id);
}

function clearForm() {
    loadingRecord = true;
    ledgerDate.value = new Date().toISOString().slice(0, 10);
    monthLabel.value = '';
    yearLabel.value = '';
    amountPerBed.value = '';
    document.querySelectorAll('.cell-input').forEach(el => { el.value = ''; });
    document.querySelectorAll('.left-label').forEach((el, i) => { el.textContent = chapterLabels[i]; });
    historySelect.value = '';
    recalcAll();
    loadingRecord = false;
    setStatus('Ready');
}

function fillForm(record) {
    loadingRecord = true;
    ledgerDate.value = record.ledger_date || '';
    monthLabel.value = record.month_label || '';
    yearLabel.value = record.year_label || '';
    amountPerBed.value = record.amount_per_bed || '';

    (record.rows || []).forEach((row, i) => {
        if (!document.querySelector(`.beds[data-row="${i}"]`)) return;
        document.querySelector(`.beds[data-row="${i}"]`).value = row.beds || '';
        document.querySelector(`.past_due[data-row="${i}"]`).value = row.past_due || '';
        document.querySelector(`.current_dues[data-row="${i}"]`).value = row.current_dues || '';
        document.querySelector(`.fines[data-row="${i}"]`).value = row.fines || '';
        document.querySelector(`.bed_dues[data-row="${i}"]`).value = row.bed_dues || '';
        document.querySelector(`.total_due[data-row="${i}"]`).value = row.total_due || '';
        document.querySelector(`.amount_paid[data-row="${i}"]`).value = row.amount_paid || '';
        document.querySelector(`.ending_balance[data-row="${i}"]`).value = row.ending_balance || '';
        document.querySelector(`.loan_balance[data-row="${i}"]`).value = row.loan_balance || '';
        document.querySelector(`.loan_payment[data-row="${i}"]`).value = row.loan_payment || '';
        document.querySelector(`.ending_loan_balance[data-row="${i}"]`).value = row.ending_loan_balance || '';
    });

    recalcAll();
    loadingRecord = false;
    setStatus('Loaded');
}

async function loadRecord(id) {
    if (!id) return;
    setStatus('Loading...');
    try {
        const res = await fetch(`?action=load&id=${encodeURIComponent(id)}`);
        const data = await res.json();
        if (!data.ok) throw new Error(data.message || 'Load failed');
        fillForm(data.record);
    } catch (err) {
        setStatus(err.message || 'Load failed', true);
    }
}

function sanitizeMoneyInput(input) {
    input.value = input.value.replace(/[^0-9.\-]/g, '');
}

document.querySelectorAll('.money').forEach(el => {
    el.addEventListener('input', () => {
        sanitizeMoneyInput(el);
        recalcRow(Number(el.dataset.row));
        scheduleSave();
    });
    el.addEventListener('blur', () => {
        const raw = el.value.trim();
        if (raw !== '') {
            const num = moneyToNumber(raw);
            el.value = formatMoney(num);
            recalcRow(Number(el.dataset.row));
            scheduleSave();
        }
    });
});

document.querySelectorAll('.beds').forEach(el => {
    el.addEventListener('input', scheduleSave);
});

[ledgerDate, monthLabel, yearLabel, amountPerBed].forEach(el => {
    el.addEventListener('input', scheduleSave);
    el.addEventListener('change', scheduleSave);
});

historySelect.addEventListener('change', () => {
    if (historySelect.value) loadRecord(historySelect.value);
});

newBtn.addEventListener('click', clearForm);
printBtn.addEventListener('click', () => window.print());
recalcAll();
</script>
</body>
</html>