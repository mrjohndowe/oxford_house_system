<?php
declare(strict_types=1);

/**
 * CO Loan Officer Ledger
 * Single-file PHP/MySQL app
 * - Fillable form matching uploaded ledger layout closely
 * - Auto-save with debounce
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
$totalRows = 14;

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

function money_or_zero(mixed $value): float
{
    $value = normalize_money($value);
    return $value === '' ? 0.0 : (float)$value;
}

function build_rows_from_request(int $totalRows): array
{
    $rows = [];
    for ($i = 0; $i < $totalRows; $i++) {
        $houseChapter = trim((string)($_POST['house_chapter'][$i] ?? ''));
        $pastDue = normalize_money($_POST['past_due'][$i] ?? '');
        $currentPayment = normalize_money($_POST['current_payment'][$i] ?? '');
        $amountPaid = normalize_money($_POST['amount_paid'][$i] ?? '');
        $checkAccount = trim((string)($_POST['check_account'][$i] ?? ''));

        $totalDue = '';
        $endingBalance = '';

        if ($pastDue !== '' || $currentPayment !== '') {
            $totalDue = number_format(money_or_zero($pastDue) - money_or_zero($currentPayment), 2, '.', '');
        }

        if ($totalDue !== '' || $amountPaid !== '') {
            $endingBalance = number_format(money_or_zero($totalDue) - money_or_zero($amountPaid), 2, '.', '');
        }

        $rows[] = [
            'house_chapter' => $houseChapter,
            'past_due' => $pastDue,
            'current_payment' => $currentPayment,
            'total_due' => $totalDue,
            'amount_paid' => $amountPaid,
            'ending_balance' => $endingBalance,
            'check_account' => $checkAccount,
        ];
    }

    return $rows;
}

function default_rows(int $totalRows): array
{
    $rows = [];
    for ($i = 0; $i < $totalRows; $i++) {
        $rows[] = [
            'house_chapter' => '',
            'past_due' => '',
            'current_payment' => '',
            'total_due' => '',
            'amount_paid' => '',
            'ending_balance' => '',
            'check_account' => '',
        ];
    }
    return $rows;
}

function calculate_revolving_total(array $rows): string
{
    $sum = 0.0;
    foreach ($rows as $row) {
        $sum += money_or_zero($row['amount_paid'] ?? '');
    }
    return number_format($sum, 2, '.', '');
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS co_loan_officer_ledgers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ledger_date VARCHAR(25) NOT NULL DEFAULT '',
    month_year VARCHAR(25) NOT NULL DEFAULT '',
    rows_json LONGTEXT NOT NULL,
    revolving_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_ledger_date (ledger_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
    $stmt = $pdo->query('SELECT id, ledger_date, month_year, updated_at FROM co_loan_officer_ledgers ORDER BY STR_TO_DATE(NULLIF(ledger_date, ""), "%m/%d/%Y") DESC, updated_at DESC');
    json_response(['success' => true, 'records' => $stmt->fetchAll()]);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM co_loan_officer_ledgers WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $record = $stmt->fetch();

    if (!$record) {
        json_response(['success' => false, 'message' => 'Record not found.'], 404);
    }

    $record['rows'] = json_decode((string)$record['rows_json'], true) ?: default_rows($totalRows);
    unset($record['rows_json']);
    json_response(['success' => true, 'record' => $record]);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'save') {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $ledgerDate = trim((string)($_POST['ledger_date'] ?? ''));
    $monthYear = trim((string)($_POST['month_year'] ?? ''));

    if ($ledgerDate === '') {
        json_response(['success' => false, 'message' => 'Date is required.'], 422);
    }

    $rows = build_rows_from_request($totalRows);
    $revolvingTotal = calculate_revolving_total($rows);
    $rowsJson = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $existingId = (int)($_POST['record_id'] ?? 0);

    if ($existingId > 0) {
        $stmt = $pdo->prepare('UPDATE co_loan_officer_ledgers SET ledger_date = ?, month_year = ?, rows_json = ?, revolving_total = ? WHERE id = ?');
        $stmt->execute([$ledgerDate, $monthYear, $rowsJson, $revolvingTotal, $existingId]);
        $savedId = $existingId;
    } else {
        $check = $pdo->prepare('SELECT id FROM co_loan_officer_ledgers WHERE ledger_date = ? LIMIT 1');
        $check->execute([$ledgerDate]);
        $found = $check->fetchColumn();

        if ($found) {
            $savedId = (int)$found;
            $stmt = $pdo->prepare('UPDATE co_loan_officer_ledgers SET month_year = ?, rows_json = ?, revolving_total = ? WHERE id = ?');
            $stmt->execute([$monthYear, $rowsJson, $revolvingTotal, $savedId]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO co_loan_officer_ledgers (ledger_date, month_year, rows_json, revolving_total) VALUES (?, ?, ?, ?)');
            $stmt->execute([$ledgerDate, $monthYear, $rowsJson, $revolvingTotal]);
            $savedId = (int)$pdo->lastInsertId();
        }
    }

    json_response([
        'success' => true,
        'message' => 'Ledger saved.',
        'record_id' => $savedId,
        'revolving_total' => $revolvingTotal,
        'saved_at' => date('Y-m-d H:i:s'),
    ]);
}

$rows = default_rows($totalRows);
$recordId = 0;
$ledgerDate = '';
$monthYear = '';
$revolvingTotal = calculate_revolving_total($rows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan - Ledger</title>
    <style>
        :root {
            --sheet-width: 1180px;
            --line: #b8b8b8;
            --header-fill: #e9e9e9;
            --text: #111;
            --bg: #efefef;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #d9d9d9;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
        }

        .toolbar {
            width: var(--sheet-width);
            margin: 12px auto 8px;
            background: #fff;
            border: 1px solid #cfcfcf;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px 12px;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .toolbar label {
            font-size: 12px;
            font-weight: 700;
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .toolbar input,
        .toolbar select,
        .toolbar button {
            height: 32px;
            border: 1px solid #bdbdbd;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 13px;
        }

        .toolbar button {
            cursor: pointer;
            background: #f7f7f7;
            font-weight: 700;
        }

        .status {
            margin-left: auto;
            font-size: 12px;
            font-weight: 700;
            color: #256029;
        }

        .sheet {
            width: var(--sheet-width);
            min-height: auto;
            margin: 0 auto 16px;
            background: var(--bg);
            padding: 18px 18px 16px;
            position: relative;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .top-grid {
            width: 330px;
            border: 1px solid var(--line);
            border-bottom: 0;
            background: var(--header-fill);
            font-weight: 700;
            font-size: 16px;
        }

        .top-grid .row {
            display: grid;
            border-bottom: 1px solid var(--line);
        }

        .top-grid .row.single { grid-template-columns: 1fr; }
        .top-grid .row.double { grid-template-columns: 145px 1fr; }
        .top-grid .cell {
            min-height: 30px;
            display: flex;
            align-items: center;
            padding: 0 8px;
        }
        .top-grid .row.double .cell:first-child {
            border-right: 1px solid var(--line);
            justify-content: center;
        }

        .logo-title {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin: 4px 0 6px;
        }

        .logo-title img,
        .logo-fallback {
            width: 88px;
            height: 88px;
        }

        .logo-title img {
            object-fit: contain;
            display: block;
        }

        .logo-fallback {
            border: 1px solid var(--line);
            display: none;
            align-items: center;
            justify-content: center;
            background: #fff;
            font-weight: 700;
            color: #1c6ab3;
        }

        .main-title {
            font-size: 28px;
            line-height: 1;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .ledger-grid {
            display: grid;
            grid-template-columns: 225px 135px 26px 140px 26px 136px 136px 136px 105px;
            column-gap: 10px;
            align-items: start;
        }

        .symbol-col {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            padding-top: 4px;
        }

        .colbox {
            border: 1px solid var(--line);
            background: transparent;
        }

        .col-header {
            min-height: 44px;
            background: var(--header-fill);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 800;
            font-size: 16px;
            line-height: 1.15;
            padding: 3px 5px;
        }

        .cell-row {
            height: 38px;
            border-bottom: 1px solid var(--line);
        }

        .cell-row:last-child { border-bottom: 0; }

        .cell-row input {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            padding: 5px 8px;
            font-size: 16px;
            outline: none;
            text-align: center;
        }

        .col-house .cell-row input { text-align: left; }
        .readonly {
            background: rgba(0,0,0,0.02) !important;
            font-weight: 700;
        }

        .revolving-wrap {
            margin-top: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4px;
            font-size: 22px;
            font-weight: 800;
        }

        .revolving-input {
            width: 120px;
            border: 0;
            border-bottom: 2px solid #111;
            background: transparent;
            font-size: 22px;
            font-weight: 800;
            text-align: center;
            outline: none;
            padding: 0 2px 2px;
        }

        @page {
            size: letter landscape;
            margin: 0.28in;
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet {
                box-shadow: none;
                margin: 0 auto;
                width: 100%;
                padding: 0;
                background: #fff;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <label>Date
            <input type="text" id="ledger_date" name="ledger_date" placeholder="MM/DD/YYYY" value="<?= h($ledgerDate) ?>">
        </label>
        <label>Month / Year
            <input type="text" id="month_year" name="month_year" placeholder="MM/YYYY" value="<?= h($monthYear) ?>">
        </label>
        <label>History by Date
            <select id="history_select">
                <option value="">Select saved ledger</option>
            </select>
        </label>
        <button type="button" id="saveBtn">Save Now</button>
        <button type="button" onclick="window.print()">Print</button>
        <span class="status" id="saveStatus">Ready</span>
    </div>

    <form id="ledgerForm" autocomplete="off">
        <input type="hidden" name="record_id" id="record_id" value="<?= (int)$recordId ?>">
        <div class="sheet">
            <div class="top-grid">
                <div class="row single">
                    <div class="cell">CO State Association</div>
                </div>
                <div class="row double">
                    <div class="cell">Month/ Year</div>
                    <div class="cell" id="monthYearDisplay"></div>
                </div>
            </div>

            <div class="logo-title">
                <img src="<?= h($logoPath) ?>" alt="Oxford House Logo" onerror="this.style.display='none';document.getElementById('logoFallback').style.display='flex';">
                <div id="logoFallback" class="logo-fallback">Logo</div>
                <div class="main-title">CO Loan Officer Ledger</div>
            </div>

            <div class="ledger-grid">
                <div class="colbox col-house">
                    <div class="col-header">House/Chapter</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="house_chapter[]" data-index="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>

                <div class="colbox">
                    <div class="col-header">Past<br>Due</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="past_due[]" class="money-input" data-index="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>

                <div class="symbol-col">-</div>

                <div class="colbox">
                    <div class="col-header">Current<br>Payment</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="current_payment[]" class="money-input" data-index="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>

                <div class="symbol-col">=</div>

                <div class="colbox">
                    <div class="col-header">Total<br>Due</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="total_due[]" class="readonly" data-index="<?= $i ?>" readonly></div>
                    <?php endfor; ?>
                </div>

                <div class="colbox">
                    <div class="col-header">Amount<br>Paid</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="amount_paid[]" class="money-input" data-index="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>

                <div class="colbox">
                    <div class="col-header">Ending<br>Balance</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="ending_balance[]" class="readonly" data-index="<?= $i ?>" readonly></div>
                    <?php endfor; ?>
                </div>

                <div class="colbox">
                    <div class="col-header">Check<br>Account</div>
                    <?php for ($i = 0; $i < $totalRows; $i++): ?>
                        <div class="cell-row"><input type="text" name="check_account[]" data-index="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="revolving-wrap">
                <span>Amount to be paid to Revolving Loan $</span>
                <input type="text" id="revolving_total" class="revolving-input" value="<?= h($revolvingTotal) ?>" readonly>
            </div>
        </div>
    </form>

    <script>
        const form = document.getElementById('ledgerForm');
        const saveStatus = document.getElementById('saveStatus');
        const historySelect = document.getElementById('history_select');
        const recordIdField = document.getElementById('record_id');
        const ledgerDateField = document.getElementById('ledger_date');
        const monthYearField = document.getElementById('month_year');
        const monthYearDisplay = document.getElementById('monthYearDisplay');
        const revolvingTotalField = document.getElementById('revolving_total');
        let saveTimer = null;

        function cleanMoney(value) {
            const cleaned = String(value || '').replace(/[^0-9.-]/g, '');
            return cleaned === '' || isNaN(cleaned) ? '' : parseFloat(cleaned).toFixed(2);
        }

        function num(value) {
            const cleaned = cleanMoney(value);
            return cleaned === '' ? 0 : parseFloat(cleaned);
        }

        function setStatus(text, color = '#256029') {
            saveStatus.textContent = text;
            saveStatus.style.color = color;
        }

        function updateMonthYearDisplay() {
            monthYearDisplay.textContent = monthYearField.value;
        }

        function recalc() {
            const past = [...document.querySelectorAll('input[name="past_due[]"]')];
            const current = [...document.querySelectorAll('input[name="current_payment[]"]')];
            const total = [...document.querySelectorAll('input[name="total_due[]"]')];
            const paid = [...document.querySelectorAll('input[name="amount_paid[]"]')];
            const ending = [...document.querySelectorAll('input[name="ending_balance[]"]')];

            let revolvingTotal = 0;

            for (let i = 0; i < total.length; i++) {
                const hasPast = past[i].value.trim() !== '';
                const hasCurrent = current[i].value.trim() !== '';
                const hasPaid = paid[i].value.trim() !== '';

                if (hasPast || hasCurrent) {
                    total[i].value = (num(past[i].value) - num(current[i].value)).toFixed(2);
                } else {
                    total[i].value = '';
                }

                if (total[i].value !== '' || hasPaid) {
                    ending[i].value = (num(total[i].value) - num(paid[i].value)).toFixed(2);
                } else {
                    ending[i].value = '';
                }

                revolvingTotal += num(paid[i].value);
            }

            revolvingTotalField.value = revolvingTotal.toFixed(2);
        }

        async function fetchHistory(selectedId = '') {
            const response = await fetch('?ajax=history');
            const data = await response.json();
            if (!data.success) return;

            historySelect.innerHTML = '<option value="">Select saved ledger</option>';
            data.records.forEach(record => {
                const option = document.createElement('option');
                option.value = record.id;
                option.textContent = `${record.ledger_date || 'No Date'}${record.month_year ? ' - ' + record.month_year : ''}`;
                if (String(selectedId) === String(record.id)) {
                    option.selected = true;
                }
                historySelect.appendChild(option);
            });
        }

        function fillInputs(name, values, key) {
            const fields = document.querySelectorAll(`input[name="${name}[]"]`);
            fields.forEach((field, index) => {
                field.value = values[index]?.[key] ?? '';
            });
        }

        async function loadRecord(id) {
            if (!id) return;
            const response = await fetch(`?ajax=load&id=${encodeURIComponent(id)}`);
            const data = await response.json();
            if (!data.success) {
                setStatus(data.message || 'Unable to load record.', '#a12626');
                return;
            }

            const record = data.record;
            recordIdField.value = record.id || 0;
            ledgerDateField.value = record.ledger_date || '';
            monthYearField.value = record.month_year || '';
            updateMonthYearDisplay();

            fillInputs('house_chapter', record.rows, 'house_chapter');
            fillInputs('past_due', record.rows, 'past_due');
            fillInputs('current_payment', record.rows, 'current_payment');
            fillInputs('total_due', record.rows, 'total_due');
            fillInputs('amount_paid', record.rows, 'amount_paid');
            fillInputs('ending_balance', record.rows, 'ending_balance');
            fillInputs('check_account', record.rows, 'check_account');

            recalc();
            setStatus('Record loaded.');
        }

        async function saveLedger() {
            recalc();
            const fd = new FormData(form);
            fd.append('ledger_date', ledgerDateField.value);
            fd.append('month_year', monthYearField.value);

            setStatus('Saving...', '#8a6200');

            try {
                const response = await fetch('?ajax=save', {
                    method: 'POST',
                    body: fd
                });
                const data = await response.json();
                if (!data.success) {
                    setStatus(data.message || 'Save failed.', '#a12626');
                    return;
                }

                recordIdField.value = data.record_id;
                revolvingTotalField.value = data.revolving_total;
                await fetchHistory(data.record_id);
                setStatus(`Auto-saved ${data.saved_at}`);
            } catch (error) {
                setStatus('Save failed.', '#a12626');
            }
        }

        function queueSave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveLedger, 700);
        }

        document.getElementById('saveBtn').addEventListener('click', saveLedger);
        historySelect.addEventListener('change', function () {
            loadRecord(this.value);
        });

        document.querySelectorAll('#ledgerForm input').forEach(input => {
            if (input.readOnly) return;
            input.addEventListener('input', () => {
                if (input.classList.contains('money-input')) {
                    input.value = input.value.replace(/[^0-9.\-]/g, '');
                }
                updateMonthYearDisplay();
                recalc();
                queueSave();
            });
            input.addEventListener('change', () => {
                updateMonthYearDisplay();
                recalc();
                queueSave();
            });
        });

        monthYearField.addEventListener('input', updateMonthYearDisplay);
        updateMonthYearDisplay();
        recalc();
        fetchHistory();
    </script>
</body>
</html>
