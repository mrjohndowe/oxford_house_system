<?php
declare(strict_types=1);

/**
 * Comptroller - State Assoc Dues Ledger
 * Single-file PHP/MySQL app
 * - Fillable ledger closely matching uploaded sheet
 * - Auto-save to MySQL
 * - History dropdown by date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';
$chapterRows = ['1','2','3','4','5','6','7','8','9','10','11','13','14'];

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

function money_value(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $clean = preg_replace('/[^0-9.\-]/', '', $value);
    if ($clean === '' || !is_numeric($clean)) {
        return '';
    }

    return number_format((float)$clean, 2, '.', '');
}

function numeric_value(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $clean = preg_replace('/[^0-9\-]/', '', $value);
    if ($clean === '' || !preg_match('/^-?\d+$/', $clean)) {
        return '';
    }

    return (string)((int)$clean);
}

function default_row(string $chapter): array
{
    return [
        'chapter_label' => $chapter,
        'beds' => '',
        'past_due' => '',
        'current_dues' => '',
        'fines' => '',
        'total_due' => '',
        'amount_paid' => '',
        'ending_balance' => '',
        'loan_balance' => '',
        'loan_payment' => '',
        'ending_loan_balance' => '',
    ];
}

function normalize_rows(array $sourceRows, array $chapterRows): array
{
    $normalized = [];

    foreach ($chapterRows as $index => $chapter) {
        $row = $sourceRows[$index] ?? [];
        $base = default_row($chapter);

        $beds = numeric_value($row['beds'] ?? '');
        $pastDue = money_value($row['past_due'] ?? '');
        $currentDues = money_value($row['current_dues'] ?? '');
        $fines = money_value($row['fines'] ?? '');
        $amountPaid = money_value($row['amount_paid'] ?? '');
        $loanBalance = money_value($row['loan_balance'] ?? '');
        $loanPayment = money_value($row['loan_payment'] ?? '');

        $totalDueFloat = (float)($pastDue !== '' ? $pastDue : 0) + (float)($currentDues !== '' ? $currentDues : 0) + (float)($fines !== '' ? $fines : 0);
        $endingBalanceFloat = $totalDueFloat - (float)($amountPaid !== '' ? $amountPaid : 0);
        $endingLoanFloat = (float)($loanBalance !== '' ? $loanBalance : 0) - (float)($loanPayment !== '' ? $loanPayment : 0);

        $normalized[] = array_merge($base, [
            'beds' => $beds,
            'past_due' => $pastDue,
            'current_dues' => $currentDues,
            'fines' => $fines,
            'total_due' => ($pastDue !== '' || $currentDues !== '' || $fines !== '') ? number_format($totalDueFloat, 2, '.', '') : '',
            'amount_paid' => $amountPaid,
            'ending_balance' => ($pastDue !== '' || $currentDues !== '' || $fines !== '' || $amountPaid !== '') ? number_format($endingBalanceFloat, 2, '.', '') : '',
            'loan_balance' => $loanBalance,
            'loan_payment' => $loanPayment,
            'ending_loan_balance' => ($loanBalance !== '' || $loanPayment !== '') ? number_format($endingLoanFloat, 2, '.', '') : '',
        ]);
    }

    return $normalized;
}

function empty_form(array $chapterRows): array
{
    $rows = [];
    foreach ($chapterRows as $chapter) {
        $rows[] = default_row($chapter);
    }

    return [
        'id' => 0,
        'ledger_date' => date('Y-m-d'),
        'month' => '',
        'year' => '',
        'amount_per_bed' => '',
        'rows' => $rows,
        'updated_at' => null,
        'created_at' => null,
    ];
}

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS state_assoc_dues_ledgers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ledger_date DATE NOT NULL,
    month_label VARCHAR(30) NOT NULL DEFAULT '',
    year_label VARCHAR(10) NOT NULL DEFAULT '',
    amount_per_bed DECIMAL(10,2) NULL,
    rows_json LONGTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_ledger_date (ledger_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

if (isset($_GET['history'])) {
    $stmt = $pdo->query('SELECT id, ledger_date, month_label, year_label, updated_at FROM state_assoc_dues_ledgers ORDER BY ledger_date DESC, id DESC');
    json_response(['ok' => true, 'records' => $stmt->fetchAll()]);
}

if (isset($_GET['load'])) {
    $id = (int)($_GET['load'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM state_assoc_dues_ledgers WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $record = $stmt->fetch();

    if (!$record) {
        json_response(['ok' => false, 'message' => 'Record not found.'], 404);
    }

    $rows = json_decode((string)$record['rows_json'], true);
    if (!is_array($rows)) {
        $rows = [];
    }

    json_response([
        'ok' => true,
        'record' => [
            'id' => (int)$record['id'],
            'ledger_date' => $record['ledger_date'],
            'month' => $record['month_label'],
            'year' => $record['year_label'],
            'amount_per_bed' => $record['amount_per_bed'] !== null ? number_format((float)$record['amount_per_bed'], 2, '.', '') : '',
            'rows' => normalize_rows($rows, $chapterRows),
            'created_at' => $record['created_at'],
            'updated_at' => $record['updated_at'],
        ],
    ]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (($_POST['action'] ?? '') === 'autosave')) {
    $id = (int)($_POST['id'] ?? 0);
    $ledgerDate = trim((string)($_POST['ledger_date'] ?? ''));
    $month = trim((string)($_POST['month'] ?? ''));
    $year = trim((string)($_POST['year'] ?? ''));
    $amountPerBed = money_value($_POST['amount_per_bed'] ?? '');
    $rowsInput = $_POST['rows'] ?? [];

    if ($ledgerDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ledgerDate)) {
        json_response(['ok' => false, 'message' => 'A valid ledger date is required.'], 422);
    }

    $rows = normalize_rows(is_array($rowsInput) ? $rowsInput : [], $chapterRows);
    $rowsJson = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($rowsJson === false) {
        json_response(['ok' => false, 'message' => 'Unable to encode ledger rows.'], 500);
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE state_assoc_dues_ledgers SET ledger_date = :ledger_date, month_label = :month_label, year_label = :year_label, amount_per_bed = :amount_per_bed, rows_json = :rows_json WHERE id = :id');
            $stmt->execute([
                ':ledger_date' => $ledgerDate,
                ':month_label' => $month,
                ':year_label' => $year,
                ':amount_per_bed' => $amountPerBed !== '' ? $amountPerBed : null,
                ':rows_json' => $rowsJson,
                ':id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO state_assoc_dues_ledgers (ledger_date, month_label, year_label, amount_per_bed, rows_json) VALUES (:ledger_date, :month_label, :year_label, :amount_per_bed, :rows_json) ON DUPLICATE KEY UPDATE month_label = VALUES(month_label), year_label = VALUES(year_label), amount_per_bed = VALUES(amount_per_bed), rows_json = VALUES(rows_json)');
            $stmt->execute([
                ':ledger_date' => $ledgerDate,
                ':month_label' => $month,
                ':year_label' => $year,
                ':amount_per_bed' => $amountPerBed !== '' ? $amountPerBed : null,
                ':rows_json' => $rowsJson,
            ]);

            $existingStmt = $pdo->prepare('SELECT id FROM state_assoc_dues_ledgers WHERE ledger_date = ? LIMIT 1');
            $existingStmt->execute([$ledgerDate]);
            $id = (int)($existingStmt->fetchColumn() ?: 0);
        }

        $metaStmt = $pdo->prepare('SELECT updated_at, created_at FROM state_assoc_dues_ledgers WHERE id = ? LIMIT 1');
        $metaStmt->execute([$id]);
        $meta = $metaStmt->fetch() ?: ['updated_at' => null, 'created_at' => null];

        json_response([
            'ok' => true,
            'id' => $id,
            'message' => 'Saved.',
            'updated_at' => $meta['updated_at'],
            'created_at' => $meta['created_at'],
        ]);
    } catch (PDOException $e) {
        json_response(['ok' => false, 'message' => 'Save failed: ' . $e->getMessage()], 500);
    }
}

$current = empty_form($chapterRows);

$stmt = $pdo->query('SELECT * FROM state_assoc_dues_ledgers ORDER BY ledger_date DESC, id DESC LIMIT 1');
$latest = $stmt->fetch();
if ($latest) {
    $decodedRows = json_decode((string)$latest['rows_json'], true);
    $current = [
        'id' => (int)$latest['id'],
        'ledger_date' => $latest['ledger_date'],
        'month' => $latest['month_label'],
        'year' => $latest['year_label'],
        'amount_per_bed' => $latest['amount_per_bed'] !== null ? number_format((float)$latest['amount_per_bed'], 2, '.', '') : '',
        'rows' => normalize_rows(is_array($decodedRows) ? $decodedRows : [], $chapterRows),
        'updated_at' => $latest['updated_at'],
        'created_at' => $latest['created_at'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptroller - State Assoc Dues Ledger</title>
    <style>
        :root {
            --page-width: 1024px;
            --page-bg: #efefef;
            --line: #333;
            --text: #111;
            --muted: #666;
            --header-bg: #f6f6f6;
            --panel-bg: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #d8d8d8;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
        }

        .toolbar {
            max-width: calc(var(--page-width) + 40px);
            margin: 18px auto 0;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding: 0 10px;
        }

        .toolbar select,
        .toolbar input,
        .toolbar button {
            height: 36px;
            border: 1px solid #b9b9b9;
            background: #fff;
            border-radius: 6px;
            padding: 0 12px;
            font-size: 14px;
        }

        .toolbar button {
            cursor: pointer;
            font-weight: 700;
        }

        .status {
            font-size: 13px;
            color: var(--muted);
            min-height: 18px;
        }

        .sheet-wrap {
            max-width: calc(var(--page-width) + 40px);
            margin: 12px auto 24px;
            padding: 10px;
        }

        .sheet {
            width: var(--page-width);
            margin: 0 auto;
            background: var(--page-bg);
            border: 1px solid #bcbcbc;
            padding: 16px 18px 18px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.10);
        }

        .topbar {
            display: grid;
            grid-template-columns: 1.45fr 0.85fr 0.9fr;
            gap: 24px;
            align-items: start;
            margin-bottom: 8px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 56px;
        }

        .brand img {
            width: 88px;
            height: auto;
            display: block;
            object-fit: contain;
        }

        .brand-title {
            font-size: 21px;
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
        }

        .amount-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 56px;
            font-size: 17px;
            font-weight: 700;
            white-space: nowrap;
        }

        .line-input {
            border: 0;
            border-bottom: 2px solid var(--line);
            background: transparent;
            outline: none;
            font-size: 16px;
            font-weight: 700;
            padding: 2px 4px;
            width: 86px;
            text-align: center;
        }

        .month-year {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: transparent;
        }

        .month-year th,
        .month-year td {
            border: 1px solid var(--line);
            height: 27px;
            text-align: center;
            font-size: 14px;
            background: transparent;
            padding: 0;
        }

        .month-year th {
            width: 54%;
            background: var(--header-bg);
            font-weight: 700;
        }

        .month-year input {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            text-align: center;
            font-size: 14px;
            padding: 0 6px;
            outline: none;
        }

        .ledger-area {
            display: grid;
            grid-template-columns: 156px 18px 78px 18px 78px 18px 78px 18px 237px 22px 238px;
            gap: 0;
            align-items: start;
        }

        .symbol-col {
            text-align: center;
            font-size: 35px;
            font-weight: 700;
            line-height: 43px;
            padding-top: 2px;
            user-select: none;
        }

        table.ledger {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: transparent;
        }

        table.ledger th,
        table.ledger td {
            border: 1px solid var(--line);
            height: 41px;
            background: transparent;
            padding: 0;
            vertical-align: middle;
        }

        table.ledger th {
            background: var(--header-bg);
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            line-height: 1.15;
            padding: 4px 2px;
        }

        table.ledger td.chapter-cell {
            font-size: 30px;
            font-weight: 700;
            text-align: center;
        }

        .cell-input {
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            outline: none;
            font-size: 15px;
            text-align: center;
            padding: 0 4px;
        }

        .readonly {
            background: rgba(0,0,0,0.02);
            font-weight: 700;
        }

        .meta {
            max-width: calc(var(--page-width) + 40px);
            margin: 0 auto 18px;
            padding: 0 10px;
            color: #555;
            font-size: 12px;
        }

        @media print {
            body {
                background: #fff;
            }
            .toolbar, .meta {
                display: none !important;
            }
            .sheet-wrap {
                max-width: none;
                margin: 0;
                padding: 0;
            }
            .sheet {
                width: 100%;
                box-shadow: none;
                border: 0;
                margin: 0;
                padding: 8px 10px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <select id="historySelect">
            <option value="">History by date...</option>
        </select>
        <input type="date" id="quickDate" value="<?= h($current['ledger_date']) ?>">
        <button type="button" id="saveNowBtn">Save now</button>
        <button type="button" onclick="window.print()">Print</button>
        <div class="status" id="saveStatus">Ready.</div>
    </div>

    <div class="sheet-wrap">
        <form id="ledgerForm" class="sheet" autocomplete="off">
            <input type="hidden" name="action" value="autosave">
            <input type="hidden" name="id" id="recordId" value="<?= (int)$current['id'] ?>">
            <input type="hidden" name="ledger_date" id="ledgerDate" value="<?= h($current['ledger_date']) ?>">

            <div class="topbar">
                <div class="brand">
                    <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
                    <div class="brand-title">State Association Dues Ledger</div>
                </div>

                <div class="amount-box">
                    <span>Amount per bed $</span>
                    <input class="line-input" type="text" name="amount_per_bed" id="amountPerBed" value="<?= h($current['amount_per_bed']) ?>">
                </div>

                <table class="month-year">
                    <tr>
                        <th>Month</th>
                        <td><input type="text" name="month" id="monthField" value="<?= h($current['month']) ?>"></td>
                    </tr>
                    <tr>
                        <th>Year</th>
                        <td><input type="text" name="year" id="yearField" value="<?= h($current['year']) ?>"></td>
                    </tr>
                </table>
            </div>

            <div class="ledger-area">
                <table class="ledger">
                    <colgroup>
                        <col style="width: 48%">
                        <col style="width: 52%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Chapter</th>
                            <th>Beds</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['rows'] as $i => $row): ?>
                            <tr>
                                <td class="chapter-cell"><?= h($row['chapter_label']) ?></td>
                                <td><input class="cell-input numeric" type="text" name="rows[<?= $i ?>][beds]" value="<?= h($row['beds']) ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <div class="symbol-col">+</div>

                <table class="ledger">
                    <thead>
                        <tr><th>Past<br>Due</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['rows'] as $i => $row): ?>
                            <tr><td><input class="cell-input money" type="text" name="rows[<?= $i ?>][past_due]" value="<?= h($row['past_due']) ?>"></td></tr>
                        <?php endforeach; ?>
                        <tr><td></td></tr>
                    </tbody>
                </table>

                <div class="symbol-col">+</div>

                <table class="ledger">
                    <thead>
                        <tr><th>Current<br>Dues</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['rows'] as $i => $row): ?>
                            <tr><td><input class="cell-input money" type="text" name="rows[<?= $i ?>][current_dues]" value="<?= h($row['current_dues']) ?>"></td></tr>
                        <?php endforeach; ?>
                        <tr><td></td></tr>
                    </tbody>
                </table>

                <div class="symbol-col">+</div>

                <table class="ledger">
                    <thead>
                        <tr><th>Fines</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['rows'] as $i => $row): ?>
                            <tr><td><input class="cell-input money" type="text" name="rows[<?= $i ?>][fines]" value="<?= h($row['fines']) ?>"></td></tr>
                        <?php endforeach; ?>
                        <tr><td></td></tr>
                    </tbody>
                </table>

                <div class="symbol-col">=</div>

                <table class="ledger">
                    <colgroup>
                        <col style="width: 34%">
                        <col style="width: 33%">
                        <col style="width: 33%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Total<br>Due</th>
                            <th>Amount<br>Paid</th>
                            <th>Ending<br>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['rows'] as $i => $row): ?>
                            <tr>
                                <td><input class="cell-input readonly" type="text" name="rows[<?= $i ?>][total_due]" value="<?= h($row['total_due']) ?>" readonly></td>
                                <td><input class="cell-input money" type="text" name="rows[<?= $i ?>][amount_paid]" value="<?= h($row['amount_paid']) ?>"></td>
                                <td><input class="cell-input readonly" type="text" name="rows[<?= $i ?>][ending_balance]" value="<?= h($row['ending_balance']) ?>" readonly></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <div></div>

                <table class="ledger">
                    <colgroup>
                        <col style="width: 32.5%">
                        <col style="width: 34%">
                        <col style="width: 33.5%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Loan<br>Balance</th>
                            <th>Loan<br>Payment</th>
                            <th>Ending<br>Loan Bal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['rows'] as $i => $row): ?>
                            <tr>
                                <td><input class="cell-input money" type="text" name="rows[<?= $i ?>][loan_balance]" value="<?= h($row['loan_balance']) ?>"></td>
                                <td><input class="cell-input money" type="text" name="rows[<?= $i ?>][loan_payment]" value="<?= h($row['loan_payment']) ?>"></td>
                                <td><input class="cell-input readonly" type="text" name="rows[<?= $i ?>][ending_loan_balance]" value="<?= h($row['ending_loan_balance']) ?>" readonly></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <div class="meta" id="metaLine">
        <?php if (!empty($current['updated_at'])): ?>
            Last saved: <?= h($current['updated_at']) ?>
        <?php endif; ?>
    </div>

    <script>
        const form = document.getElementById('ledgerForm');
        const historySelect = document.getElementById('historySelect');
        const saveStatus = document.getElementById('saveStatus');
        const recordId = document.getElementById('recordId');
        const ledgerDate = document.getElementById('ledgerDate');
        const quickDate = document.getElementById('quickDate');
        const metaLine = document.getElementById('metaLine');
        const saveNowBtn = document.getElementById('saveNowBtn');

        let saveTimer = null;
        let loadingRecord = false;

        function parseMoney(value) {
            const cleaned = String(value || '').replace(/[^0-9.\-]/g, '');
            const n = parseFloat(cleaned);
            return Number.isFinite(n) ? n : 0;
        }

        function formatMoney(value, showBlankWhenZero = true) {
            if (showBlankWhenZero && Math.abs(value) < 0.00001) {
                return '';
            }
            return value.toFixed(2);
        }

        function recalc() {
            const rowCount = <?= count($chapterRows) ?>;
            for (let i = 0; i < rowCount; i++) {
                const pastDue = parseMoney(form.elements[`rows[${i}][past_due]`].value);
                const currentDues = parseMoney(form.elements[`rows[${i}][current_dues]`].value);
                const fines = parseMoney(form.elements[`rows[${i}][fines]`].value);
                const amountPaid = parseMoney(form.elements[`rows[${i}][amount_paid]`].value);
                const loanBalance = parseMoney(form.elements[`rows[${i}][loan_balance]`].value);
                const loanPayment = parseMoney(form.elements[`rows[${i}][loan_payment]`].value);

                const totalDue = pastDue + currentDues + fines;
                const endingBalance = totalDue - amountPaid;
                const endingLoanBalance = loanBalance - loanPayment;

                form.elements[`rows[${i}][total_due]`].value = formatMoney(totalDue, totalDue === 0 && !form.elements[`rows[${i}][past_due]`].value && !form.elements[`rows[${i}][current_dues]`].value && !form.elements[`rows[${i}][fines]`].value);
                form.elements[`rows[${i}][ending_balance]`].value = formatMoney(endingBalance, totalDue === 0 && amountPaid === 0 && !form.elements[`rows[${i}][amount_paid]`].value);
                form.elements[`rows[${i}][ending_loan_balance]`].value = formatMoney(endingLoanBalance, loanBalance === 0 && loanPayment === 0 && !form.elements[`rows[${i}][loan_balance]`].value && !form.elements[`rows[${i}][loan_payment]`].value);
            }
        }

        function setStatus(message) {
            saveStatus.textContent = message;
        }

        async function refreshHistory(selectedId = '') {
            const res = await fetch('?history=1', { credentials: 'same-origin' });
            const data = await res.json();
            historySelect.innerHTML = '<option value="">History by date...</option>';

            if (data.records) {
                data.records.forEach(record => {
                    const option = document.createElement('option');
                    option.value = record.id;
                    let label = record.ledger_date;
                    if (record.month_label || record.year_label) {
                        label += ` — ${record.month_label || ''} ${record.year_label || ''}`.trim();
                    }
                    historySelect.appendChild(option);
                    option.textContent = label;
                });
            }

            if (selectedId) {
                historySelect.value = String(selectedId);
            }
        }

        async function saveForm(manual = false) {
            if (loadingRecord) return;

            recalc();
            const fd = new FormData(form);
            try {
                setStatus(manual ? 'Saving...' : 'Autosaving...');
                const res = await fetch('', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (!data.ok) {
                    throw new Error(data.message || 'Save failed.');
                }
                if (data.id) {
                    recordId.value = data.id;
                    await refreshHistory(data.id);
                }
                metaLine.textContent = data.updated_at ? `Last saved: ${data.updated_at}` : '';
                setStatus(manual ? 'Saved.' : 'Autosaved.');
            } catch (error) {
                setStatus(error.message || 'Save failed.');
            }
        }

        function queueSave() {
            if (loadingRecord) return;
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => saveForm(false), 600);
        }

        async function loadRecord(id) {
            if (!id) return;
            loadingRecord = true;
            setStatus('Loading...');
            try {
                const res = await fetch(`?load=${encodeURIComponent(id)}`, { credentials: 'same-origin' });
                const data = await res.json();
                if (!data.ok || !data.record) {
                    throw new Error(data.message || 'Unable to load record.');
                }

                const record = data.record;
                recordId.value = record.id || 0;
                ledgerDate.value = record.ledger_date || '';
                quickDate.value = record.ledger_date || '';
                form.elements['month'].value = record.month || '';
                form.elements['year'].value = record.year || '';
                form.elements['amount_per_bed'].value = record.amount_per_bed || '';

                (record.rows || []).forEach((row, i) => {
                    Object.keys(row).forEach(key => {
                        const fieldName = `rows[${i}][${key}]`;
                        if (form.elements[fieldName]) {
                            form.elements[fieldName].value = row[key] ?? '';
                        }
                    });
                });

                recalc();
                metaLine.textContent = record.updated_at ? `Last saved: ${record.updated_at}` : '';
                setStatus('Loaded.');
            } catch (error) {
                setStatus(error.message || 'Load failed.');
            } finally {
                loadingRecord = false;
            }
        }

        quickDate.addEventListener('change', () => {
            ledgerDate.value = quickDate.value;
            queueSave();
        });

        historySelect.addEventListener('change', () => {
            loadRecord(historySelect.value);
        });

        saveNowBtn.addEventListener('click', () => saveForm(true));

        form.addEventListener('input', (event) => {
            const target = event.target;
            if (target.classList.contains('numeric')) {
                target.value = target.value.replace(/[^0-9\-]/g, '');
            }
            if (target.classList.contains('money')) {
                target.value = target.value.replace(/[^0-9.\-]/g, '');
            }
            recalc();
            queueSave();
        });

        form.addEventListener('change', () => {
            recalc();
            queueSave();
        });

        recalc();
        refreshHistory(recordId.value || '');
    </script>
</body>
</html>