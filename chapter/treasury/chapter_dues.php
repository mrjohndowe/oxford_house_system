<?php
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';
$logoPath = '../../images/oxford_house_logo.png';
$rowCount = 14;

function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function clean_num(mixed $value): string {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = preg_replace('/[^0-9.\-]/', '', $value) ?? '';
    if ($value === '' || !is_numeric($value)) {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function num_val(mixed $value): float {
    $clean = clean_num($value);
    return $clean === '' ? 0.0 : (float)$clean;
}

function blank_rows(int $count): array {
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'house' => '',
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
            'ending_loan_bal' => '',
        ];
    }
    return $rows;
}

function normalize_rows(mixed $rows, int $count): array {
    $defaults = blank_rows($count);
    if (!is_array($rows)) {
        return $defaults;
    }
    for ($i = 0; $i < $count; $i++) {
        $row = is_array($rows[$i] ?? null) ? $rows[$i] : [];
        $defaults[$i] = [
            'house' => trim((string)($row['house'] ?? '')),
            'beds' => trim((string)($row['beds'] ?? '')),
            'past_due' => clean_num($row['past_due'] ?? ''),
            'current_dues' => clean_num($row['current_dues'] ?? ''),
            'fines' => clean_num($row['fines'] ?? ''),
            'bed_dues' => clean_num($row['bed_dues'] ?? ''),
            'total_due' => clean_num($row['total_due'] ?? ''),
            'amount_paid' => clean_num($row['amount_paid'] ?? ''),
            'ending_balance' => clean_num($row['ending_balance'] ?? ''),
            'loan_balance' => clean_num($row['loan_balance'] ?? ''),
            'loan_payment' => clean_num($row['loan_payment'] ?? ''),
            'ending_loan_bal' => clean_num($row['ending_loan_bal'] ?? ''),
        ];
    }
    return $defaults;
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
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Database connection failed: ' . h($e->getMessage());
    exit;
}

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS treasurer_chapter_dues_ledger (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ledger_date DATE NOT NULL,
    chapter_name VARCHAR(255) NOT NULL DEFAULT '',
    month_year VARCHAR(255) NOT NULL DEFAULT '',
    amount_per_bed DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    rows_json LONGTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_ledger_date (ledger_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$action = (string)($_REQUEST['action'] ?? '');

if ($action === 'history') {
    $stmt = $pdo->query("SELECT id, ledger_date, chapter_name, month_year FROM treasurer_chapter_dues_ledger ORDER BY ledger_date DESC, id DESC");
    json_response(['ok' => true, 'records' => $stmt->fetchAll()]);
}

if ($action === 'load') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        json_response(['ok' => false, 'message' => 'Invalid record ID.'], 422);
    }
    $stmt = $pdo->prepare("SELECT * FROM treasurer_chapter_dues_ledger WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $record = $stmt->fetch();
    if (!$record) {
        json_response(['ok' => false, 'message' => 'Record not found.'], 404);
    }
    $record['rows'] = normalize_rows(json_decode((string)$record['rows_json'], true), $rowCount);
    unset($record['rows_json']);
    json_response(['ok' => true, 'record' => $record]);
}

if ($action === 'save') {
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($payload)) {
        json_response(['ok' => false, 'message' => 'Invalid payload.'], 422);
    }

    $recordId = (int)($payload['record_id'] ?? 0);
    $ledgerDate = trim((string)($payload['ledger_date'] ?? ''));
    $chapterName = trim((string)($payload['chapter_name'] ?? ''));
    $monthYear = trim((string)($payload['month_year'] ?? ''));
    $amountPerBed = clean_num($payload['amount_per_bed'] ?? '');
    $rows = normalize_rows($payload['rows'] ?? [], $rowCount);

    if ($ledgerDate === '') {
        json_response(['ok' => false, 'message' => 'Date is required.'], 422);
    }

    foreach ($rows as &$row) {
        $beds = trim((string)$row['beds']);
        $bedsNumber = ($beds !== '' && is_numeric($beds)) ? (float)$beds : 0.0;
        $pastDue = num_val($row['past_due']);
        $currentDues = num_val($row['current_dues']);
        $fines = num_val($row['fines']);
        $bedDues = num_val($row['bed_dues']);
        $amountPaid = num_val($row['amount_paid']);
        $loanBalance = num_val($row['loan_balance']);
        $loanPayment = num_val($row['loan_payment']);

        if ($currentDues <= 0 && $amountPerBed !== '' && $bedsNumber > 0) {
            $currentDues = $bedsNumber * (float)$amountPerBed;
            $row['current_dues'] = number_format($currentDues, 2, '.', '');
        }

        $totalDue = $pastDue + $currentDues + $fines + $bedDues;
        $endingBalance = $totalDue - $amountPaid;
        $endingLoan = $loanBalance - $loanPayment;

        $row['total_due'] = $totalDue != 0.0 ? number_format($totalDue, 2, '.', '') : '';
        $row['ending_balance'] = $endingBalance != 0.0 ? number_format($endingBalance, 2, '.', '') : '';
        $row['ending_loan_bal'] = $endingLoan != 0.0 ? number_format($endingLoan, 2, '.', '') : '';
    }
    unset($row);

    $rowsJson = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($recordId > 0) {
        $stmt = $pdo->prepare("UPDATE treasurer_chapter_dues_ledger SET ledger_date = ?, chapter_name = ?, month_year = ?, amount_per_bed = ?, rows_json = ? WHERE id = ?");
        $stmt->execute([$ledgerDate, $chapterName, $monthYear, $amountPerBed === '' ? '0.00' : $amountPerBed, $rowsJson, $recordId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM treasurer_chapter_dues_ledger WHERE ledger_date = ? LIMIT 1");
        $stmt->execute([$ledgerDate]);
        $existingId = (int)($stmt->fetchColumn() ?: 0);
        if ($existingId > 0) {
            $update = $pdo->prepare("UPDATE treasurer_chapter_dues_ledger SET chapter_name = ?, month_year = ?, amount_per_bed = ?, rows_json = ? WHERE id = ?");
            $update->execute([$chapterName, $monthYear, $amountPerBed === '' ? '0.00' : $amountPerBed, $rowsJson, $existingId]);
            $recordId = $existingId;
        } else {
            $insert = $pdo->prepare("INSERT INTO treasurer_chapter_dues_ledger (ledger_date, chapter_name, month_year, amount_per_bed, rows_json) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$ledgerDate, $chapterName, $monthYear, $amountPerBed === '' ? '0.00' : $amountPerBed, $rowsJson]);
            $recordId = (int)$pdo->lastInsertId();
        }
    }

    json_response(['ok' => true, 'record_id' => $recordId, 'rows' => $rows]);
}

$rows = blank_rows($rowCount);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Treasurer - Chapter Dues Ledger</title>
<style>
    * { box-sizing: border-box; }
    body {
        margin: 0;
        background: #dddddd;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
    }
    .topbar {
        width: 1040px;
        margin: 12px auto 0;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
        background: #f7f7f7;
        border: 1px solid #cfcfcf;
        padding: 8px 10px;
    }
    .topbar label { font-size: 12px; font-weight: 700; }
    .topbar input, .topbar select, .topbar button {
        height: 28px;
        font-size: 12px;
        border: 1px solid #a8a8a8;
        padding: 4px 8px;
        background: #fff;
    }
    .topbar button { cursor: pointer; font-weight: 700; }
    .save-status { margin-left: auto; font-size: 12px; color: #333; min-width: 150px; text-align: right; }

    .page {
        width: 1040px;
        margin: 0 auto;
        background: #f2f2f2;
        min-height: 790px;
        padding: 41px 31px 44px 36px;
    }

    .meta-table {
        border-collapse: collapse;
        width: 267px;
        table-layout: fixed;
        margin-left: 55px;
    }
    .meta-table th,
    .meta-table td {
        border: 1px solid #b8b8b8;
        height: 25px;
        background: #ededed;
        padding: 0;
    }
    .meta-table th {
        width: 120px;
        text-align: center;
        font-weight: 700;
        font-size: 13px;
    }
    .meta-table td {
        width: 147px;
        background: #f7f7f7;
    }
    .meta-table input {
        width: 100%;
        height: 24px;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 13px;
        padding: 0 6px;
        text-align: center;
    }

    .title-row {
        display: flex;
        align-items: flex-end;
        margin-top: 0;
        margin-left: 60px;
        gap: 8px;
    }
    .logo-wrap {
        width: 90px;
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }
    .logo-wrap img {
        max-width: 88px;
        max-height: 88px;
        object-fit: contain;
    }
    .title-text {
        font-size: 18px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.1;
        padding-bottom: 11px;
    }
    .chapter-line {
        display: inline-block;
        min-width: 18px;
        border-bottom: 1px solid transparent;
    }
    .amount-area {
        font-size: 18px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.1;
        padding-bottom: 11px;
        margin-left: 24px;
    }
    .amount-inline {
        display: inline-block;
        width: 85px;
        border-bottom: 2px solid #000;
        height: 20px;
        vertical-align: middle;
        position: relative;
        top: 1px;
    }
    .amount-inline input {
        width: 100%;
        height: 18px;
        border: 0;
        background: transparent;
        outline: none;
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        padding: 0 2px;
    }

    .ledger-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-top: 18px;
        margin-left: 55px;
    }
    .symbol {
        width: 12px;
        text-align: center;
        font-weight: 700;
        font-size: 32px;
        line-height: 39px;
        color: #000;
    }
    .spacer {
        width: 10px;
    }
    table.ledger {
        border-collapse: collapse;
        table-layout: fixed;
        background: transparent;
    }
    table.ledger th,
    table.ledger td {
        border: 1px solid #b8b8b8;
        padding: 0;
        height: 39px;
        background: #f2f2f2;
    }
    table.ledger th {
        background: #ededed;
        text-align: center;
        font-weight: 700;
        font-size: 13px;
        line-height: 1.15;
        vertical-align: top;
        padding-top: 2px;
    }
    table.ledger td input {
        width: 100%;
        height: 38px;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 13px;
        padding: 0 5px;
    }
    .left input { text-align: left; }
    .center input { text-align: center; }
    .right input { text-align: right; }

    .w-house { width: 74px; }
    .w-beds { width: 71px; }
    .w-small { width: 73px; }
    .w-med { width: 74px; }

    .history-select { min-width: 210px; }

    @media print {
        body { background: #fff; }
        .topbar { display: none; }
        .page {
            width: 100%;
            margin: 0;
            padding: 20px 20px 25px 20px;
            background: #fff;
            min-height: auto;
        }
        .meta-table th, .meta-table td, table.ledger th, table.ledger td {
            background: #fff !important;
        }
        input, select {
            -webkit-appearance: none;
            appearance: none;
            color: #000;
        }
    }
</style>
</head>
<body>
<div class="topbar">
    <label for="ledger_date">Date</label>
    <input type="date" id="ledger_date" value="<?= h(date('Y-m-d')) ?>">

    <label for="history">History</label>
    <select id="history" class="history-select">
        <option value="">Select saved date...</option>
    </select>

    <button type="button" id="printBtn">Print</button>
    <button type="button" id="saveBtn">Save Now</button>
    <span class="save-status" id="saveStatus">Ready</span>
</div>

<div class="page">
    <table class="meta-table">
        <tr>
            <th>Chapter</th>
            <td><input type="text" id="chapter_name" autocomplete="off"></td>
        </tr>
        <tr>
            <th>Month/ Year</th>
            <td><input type="text" id="month_year" autocomplete="off"></td>
        </tr>
    </table>

    <div class="title-row">
        <div class="logo-wrap">
            <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
        </div>
        <div class="title-text">Colorado Chapter<span class="chapter-line">__</span> Dues Ledger</div>
        <div class="amount-area">Amount per bed $<span class="amount-inline"><input type="text" id="amount_per_bed" inputmode="decimal" autocomplete="off"></span></div>
    </div>

    <div class="ledger-row">
        <table class="ledger" aria-label="House and bed columns">
            <thead>
                <tr>
                    <th class="w-house">House</th>
                    <th class="w-beds">Beds</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr>
                    <td class="left w-house"><input type="text" data-row="<?= $i ?>" data-field="house"></td>
                    <td class="center w-beds"><input type="text" data-row="<?= $i ?>" data-field="beds" inputmode="numeric"></td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <table class="ledger" aria-label="Past due column">
            <thead>
                <tr><th class="w-small">Past<br>Due</th></tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr><td class="right w-small"><input type="text" data-row="<?= $i ?>" data-field="past_due" inputmode="decimal"></td></tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="symbol">+</div>

        <table class="ledger" aria-label="Current dues column">
            <thead>
                <tr><th class="w-small">Current<br>Dues</th></tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr><td class="right w-small"><input type="text" data-row="<?= $i ?>" data-field="current_dues" inputmode="decimal"></td></tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="symbol">+</div>

        <table class="ledger" aria-label="Fines column">
            <thead>
                <tr><th class="w-small">Fines</th></tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr><td class="right w-small"><input type="text" data-row="<?= $i ?>" data-field="fines" inputmode="decimal"></td></tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="symbol">+</div>

        <table class="ledger" aria-label="Bed dues column">
            <thead>
                <tr><th class="w-small">Bed<br>Dues</th></tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr><td class="right w-small"><input type="text" data-row="<?= $i ?>" data-field="bed_dues" inputmode="decimal"></td></tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="symbol">=</div>

        <table class="ledger" aria-label="Totals group">
            <thead>
                <tr>
                    <th class="w-med">Total<br>Due</th>
                    <th class="w-med">Amount<br>Paid</th>
                    <th class="w-med">Ending<br>Balance</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr>
                    <td class="right w-med"><input type="text" data-row="<?= $i ?>" data-field="total_due" inputmode="decimal" readonly></td>
                    <td class="right w-med"><input type="text" data-row="<?= $i ?>" data-field="amount_paid" inputmode="decimal"></td>
                    <td class="right w-med"><input type="text" data-row="<?= $i ?>" data-field="ending_balance" inputmode="decimal" readonly></td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="spacer"></div>

        <table class="ledger" aria-label="Loan group">
            <thead>
                <tr>
                    <th class="w-med">Loan<br>Balance</th>
                    <th class="w-med">Loan<br>Payment</th>
                    <th class="w-med">Ending<br>Loan Bal</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < $rowCount; $i++): ?>
                <tr>
                    <td class="right w-med"><input type="text" data-row="<?= $i ?>" data-field="loan_balance" inputmode="decimal"></td>
                    <td class="right w-med"><input type="text" data-row="<?= $i ?>" data-field="loan_payment" inputmode="decimal"></td>
                    <td class="right w-med"><input type="text" data-row="<?= $i ?>" data-field="ending_loan_bal" inputmode="decimal" readonly></td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const rowCount = <?= (int)$rowCount ?>;
const saveStatus = document.getElementById('saveStatus');
const historySelect = document.getElementById('history');
const dateInput = document.getElementById('ledger_date');
const chapterInput = document.getElementById('chapter_name');
const monthYearInput = document.getElementById('month_year');
const amountPerBedInput = document.getElementById('amount_per_bed');
let recordId = 0;
let saveTimer = null;
let loadingRecord = false;

function setStatus(text) {
    saveStatus.textContent = text;
}

function numericValue(value) {
    const clean = String(value || '').replace(/[^0-9.\-]/g, '');
    const num = parseFloat(clean);
    return Number.isFinite(num) ? num : 0;
}

function formatMoney(value) {
    if (value === '' || value === null || Number.isNaN(value)) return '';
    const num = Number(value);
    if (!Number.isFinite(num)) return '';
    return num.toFixed(2);
}

function field(row, key) {
    return document.querySelector(`[data-row="${row}"][data-field="${key}"]`);
}

function recalcRow(i) {
    const beds = numericValue(field(i, 'beds').value);
    const amountPerBed = numericValue(amountPerBedInput.value);
    const pastDue = numericValue(field(i, 'past_due').value);
    const fines = numericValue(field(i, 'fines').value);
    const bedDues = numericValue(field(i, 'bed_dues').value);
    const amountPaid = numericValue(field(i, 'amount_paid').value);
    const loanBalance = numericValue(field(i, 'loan_balance').value);
    const loanPayment = numericValue(field(i, 'loan_payment').value);

    if (amountPerBed > 0 && beds > 0 && field(i, 'current_dues').value.trim() === '') {
        field(i, 'current_dues').value = formatMoney(beds * amountPerBed);
    }

    const currentDues = numericValue(field(i, 'current_dues').value);
    const totalDue = pastDue + currentDues + fines + bedDues;
    const endingBalance = totalDue - amountPaid;
    const endingLoan = loanBalance - loanPayment;

    field(i, 'total_due').value = totalDue !== 0 ? formatMoney(totalDue) : '';
    field(i, 'ending_balance').value = endingBalance !== 0 ? formatMoney(endingBalance) : '';
    field(i, 'ending_loan_bal').value = endingLoan !== 0 ? formatMoney(endingLoan) : '';
}

function recalcAll() {
    for (let i = 0; i < rowCount; i++) {
        recalcRow(i);
    }
}

function collectRows() {
    const rows = [];
    for (let i = 0; i < rowCount; i++) {
        rows.push({
            house: field(i, 'house').value,
            beds: field(i, 'beds').value,
            past_due: field(i, 'past_due').value,
            current_dues: field(i, 'current_dues').value,
            fines: field(i, 'fines').value,
            bed_dues: field(i, 'bed_dues').value,
            total_due: field(i, 'total_due').value,
            amount_paid: field(i, 'amount_paid').value,
            ending_balance: field(i, 'ending_balance').value,
            loan_balance: field(i, 'loan_balance').value,
            loan_payment: field(i, 'loan_payment').value,
            ending_loan_bal: field(i, 'ending_loan_bal').value,
        });
    }
    return rows;
}

async function saveNow() {
    if (loadingRecord) return;
    recalcAll();
    setStatus('Saving...');
    try {
        const response = await fetch('?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                record_id: recordId,
                ledger_date: dateInput.value,
                chapter_name: chapterInput.value,
                month_year: monthYearInput.value,
                amount_per_bed: amountPerBedInput.value,
                rows: collectRows(),
            }),
        });
        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Save failed.');
        }
        recordId = Number(data.record_id || 0);
        if (Array.isArray(data.rows)) {
            data.rows.forEach((row, i) => {
                Object.entries(row).forEach(([key, value]) => {
                    const el = field(i, key);
                    if (el) el.value = value ?? '';
                });
            });
        }
        await loadHistory();
        if (recordId) historySelect.value = String(recordId);
        setStatus('Saved');
    } catch (error) {
        setStatus(error.message || 'Save failed');
    }
}

function queueSave() {
    setStatus('Typing...');
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveNow, 700);
}

async function loadHistory() {
    const response = await fetch('?action=history');
    const data = await response.json();
    if (!data.ok) return;
    historySelect.innerHTML = '<option value="">Select saved date...</option>';
    for (const record of data.records) {
        const option = document.createElement('option');
        option.value = record.id;
        option.textContent = `${record.ledger_date}${record.chapter_name ? ' - ' + record.chapter_name : ''}`;
        historySelect.appendChild(option);
    }
}

async function loadRecord(id) {
    if (!id) return;
    loadingRecord = true;
    setStatus('Loading...');
    try {
        const response = await fetch(`?action=load&id=${encodeURIComponent(id)}`);
        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Load failed.');
        }
        const record = data.record;
        recordId = Number(record.id || 0);
        dateInput.value = record.ledger_date || '';
        chapterInput.value = record.chapter_name || '';
        monthYearInput.value = record.month_year || '';
        amountPerBedInput.value = record.amount_per_bed || '';
        (record.rows || []).forEach((row, i) => {
            Object.entries(row).forEach(([key, value]) => {
                const el = field(i, key);
                if (el) el.value = value ?? '';
            });
        });
        recalcAll();
        setStatus('Loaded');
    } catch (error) {
        setStatus(error.message || 'Load failed');
    } finally {
        loadingRecord = false;
    }
}

document.querySelectorAll('input[data-row], #ledger_date, #chapter_name, #month_year, #amount_per_bed').forEach((el) => {
    el.addEventListener('input', () => {
        const row = el.dataset.row;
        if (row !== undefined) recalcRow(Number(row));
        if (el.id === 'amount_per_bed') recalcAll();
        queueSave();
    });
    el.addEventListener('blur', () => {
        const row = el.dataset.row;
        if (el.matches('[inputmode="decimal"]')) {
            const num = numericValue(el.value);
            el.value = el.value.trim() === '' ? '' : formatMoney(num);
        }
        if (row !== undefined) recalcRow(Number(row));
        if (el.id === 'amount_per_bed') recalcAll();
        queueSave();
    });
});

document.getElementById('saveBtn').addEventListener('click', saveNow);
document.getElementById('printBtn').addEventListener('click', () => window.print());
historySelect.addEventListener('change', () => loadRecord(historySelect.value));

loadHistory();
recalcAll();
</script>
</body>
</html>
