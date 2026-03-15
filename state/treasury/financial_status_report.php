<?php
declare(strict_types=1);

/**
 * Treasurer - State Assoc Financial Status Report
 * Based on uploaded sheet: Treasurer - State Assoc Financial Status Report.pdf
 * - Fillable form closely matching original layout
 * - Auto-save to MySQL
 * - History dropdown by report date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 * - Replaces AZ with CO
 * - Oxford House logo path: ../../images/oxford_house_logo.png
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

const RECEIVED_ROW_COUNT = 15;
const SPENT_ROW_COUNT = 15;

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function empty_received_row(): array
{
    return [
        'entry_date' => '',
        'source_purpose' => '',
        'amount' => '',
    ];
}

function empty_spent_row(): array
{
    return [
        'entry_date' => '',
        'source_purpose' => '',
        'check_no' => '',
        'amount' => '',
    ];
}

function normalize_money($value): string
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

function decode_json_array(?string $json, callable $fallbackFactory, int $count): array
{
    $decoded = json_decode((string)$json, true);
    if (!is_array($decoded)) {
        $decoded = [];
    }

    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = isset($decoded[$i]) && is_array($decoded[$i]) ? $decoded[$i] : $fallbackFactory();
    }

    return $rows;
}

function sanitize_received_rows(array $rows): array
{
    $clean = [];
    for ($i = 0; $i < RECEIVED_ROW_COUNT; $i++) {
        $row = $rows[$i] ?? [];
        $clean[] = [
            'entry_date' => trim((string)($row['entry_date'] ?? '')),
            'source_purpose' => trim((string)($row['source_purpose'] ?? '')),
            'amount' => normalize_money($row['amount'] ?? ''),
        ];
    }
    return $clean;
}

function sanitize_spent_rows(array $rows): array
{
    $clean = [];
    for ($i = 0; $i < SPENT_ROW_COUNT; $i++) {
        $row = $rows[$i] ?? [];
        $clean[] = [
            'entry_date' => trim((string)($row['entry_date'] ?? '')),
            'source_purpose' => trim((string)($row['source_purpose'] ?? '')),
            'check_no' => trim((string)($row['check_no'] ?? '')),
            'amount' => normalize_money($row['amount'] ?? ''),
        ];
    }
    return $clean;
}

function sum_amounts(array $rows): float
{
    $sum = 0.0;
    foreach ($rows as $row) {
        $amount = (string)($row['amount'] ?? '');
        if ($amount !== '' && is_numeric($amount)) {
            $sum += (float)$amount;
        }
    }
    return $sum;
}

$pdo = null;
$error = '';
$success = '';

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

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS state_assoc_financial_status_reports (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            report_date DATE DEFAULT NULL,
            beginning_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            total_received DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            total_spent DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            ending_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            received_rows LONGTEXT NOT NULL,
            spent_rows LONGTEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_report_date (report_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (Throwable $e) {
    $error = 'Database connection failed: ' . $e->getMessage();
}

$form = [
    'id' => '',
    'report_date' => '',
    'beginning_balance' => '',
    'received_rows' => array_map(fn() => empty_received_row(), range(1, RECEIVED_ROW_COUNT)),
    'spent_rows' => array_map(fn() => empty_spent_row(), range(1, SPENT_ROW_COUNT)),
];

$historyOptions = [];

if ($pdo) {
    try {
        $historyStmt = $pdo->query("SELECT id, report_date, updated_at FROM state_assoc_financial_status_reports ORDER BY report_date DESC, id DESC");
        $historyOptions = $historyStmt->fetchAll();
    } catch (Throwable $e) {
        $error = 'Unable to load history: ' . $e->getMessage();
    }
}

$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? 'save');

    $form['id'] = trim((string)($_POST['id'] ?? ''));
    $form['report_date'] = trim((string)($_POST['report_date'] ?? ''));
    $form['beginning_balance'] = normalize_money($_POST['beginning_balance'] ?? '');
    $form['received_rows'] = sanitize_received_rows($_POST['received'] ?? []);
    $form['spent_rows'] = sanitize_spent_rows($_POST['spent'] ?? []);

    $selectedId = (int)($form['id'] ?: 0);

    if ($pdo && $action === 'save') {
        try {
            $beginningBalance = $form['beginning_balance'] === '' ? 0.00 : (float)$form['beginning_balance'];
            $totalReceived = sum_amounts($form['received_rows']);
            $totalSpent = sum_amounts($form['spent_rows']);
            $endingBalance = $beginningBalance + $totalReceived - $totalSpent;

            if ($selectedId > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE state_assoc_financial_status_reports
                     SET report_date = :report_date,
                         beginning_balance = :beginning_balance,
                         total_received = :total_received,
                         total_spent = :total_spent,
                         ending_balance = :ending_balance,
                         received_rows = :received_rows,
                         spent_rows = :spent_rows
                     WHERE id = :id"
                );
                $stmt->execute([
                    ':report_date' => $form['report_date'] !== '' ? $form['report_date'] : null,
                    ':beginning_balance' => number_format($beginningBalance, 2, '.', ''),
                    ':total_received' => number_format($totalReceived, 2, '.', ''),
                    ':total_spent' => number_format($totalSpent, 2, '.', ''),
                    ':ending_balance' => number_format($endingBalance, 2, '.', ''),
                    ':received_rows' => json_encode($form['received_rows'], JSON_UNESCAPED_UNICODE),
                    ':spent_rows' => json_encode($form['spent_rows'], JSON_UNESCAPED_UNICODE),
                    ':id' => $selectedId,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO state_assoc_financial_status_reports
                     (report_date, beginning_balance, total_received, total_spent, ending_balance, received_rows, spent_rows)
                     VALUES (:report_date, :beginning_balance, :total_received, :total_spent, :ending_balance, :received_rows, :spent_rows)"
                );
                $stmt->execute([
                    ':report_date' => $form['report_date'] !== '' ? $form['report_date'] : null,
                    ':beginning_balance' => number_format($beginningBalance, 2, '.', ''),
                    ':total_received' => number_format($totalReceived, 2, '.', ''),
                    ':total_spent' => number_format($totalSpent, 2, '.', ''),
                    ':ending_balance' => number_format($endingBalance, 2, '.', ''),
                    ':received_rows' => json_encode($form['received_rows'], JSON_UNESCAPED_UNICODE),
                    ':spent_rows' => json_encode($form['spent_rows'], JSON_UNESCAPED_UNICODE),
                ]);
                $selectedId = (int)$pdo->lastInsertId();
                $form['id'] = (string)$selectedId;
            }

            $success = 'Report saved successfully.';

            $historyStmt = $pdo->query("SELECT id, report_date, updated_at FROM state_assoc_financial_status_reports ORDER BY report_date DESC, id DESC");
            $historyOptions = $historyStmt->fetchAll();
        } catch (Throwable $e) {
            $error = 'Unable to save report: ' . $e->getMessage();
        }
    }
}

if ($pdo && $selectedId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM state_assoc_financial_status_reports WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $selectedId]);
        $record = $stmt->fetch();

        if ($record) {
            $form['id'] = (string)$record['id'];
            $form['report_date'] = (string)($record['report_date'] ?? '');
            $form['beginning_balance'] = number_format((float)$record['beginning_balance'], 2, '.', '');
            $form['received_rows'] = decode_json_array($record['received_rows'] ?? '', 'empty_received_row', RECEIVED_ROW_COUNT);
            $form['spent_rows'] = decode_json_array($record['spent_rows'] ?? '', 'empty_spent_row', SPENT_ROW_COUNT);
        }
    } catch (Throwable $e) {
        $error = 'Unable to load selected report: ' . $e->getMessage();
    }
}

$receivedTotal = sum_amounts($form['received_rows']);
$spentTotal = sum_amounts($form['spent_rows']);
$beginningBalanceValue = $form['beginning_balance'] !== '' && is_numeric($form['beginning_balance']) ? (float)$form['beginning_balance'] : 0.0;
$endingBalance = $beginningBalanceValue + $receivedTotal - $spentTotal;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer - State Assoc Financial Status Report</title>
    <style>
        :root {
            --border: #111;
            --bg: #f3f3f3;
            --paper: #ffffff;
            --text: #111;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 18px;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: #ececec;
        }

        .toolbar {
            max-width: 760px;
            margin: 0 auto 14px auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .toolbar label {
            font-size: 13px;
            font-weight: 700;
        }

        select, button {
            height: 36px;
            border: 1px solid #999;
            border-radius: 6px;
            background: #fff;
            padding: 0 10px;
            font-size: 14px;
        }

        button {
            cursor: pointer;
            font-weight: 700;
        }

        .paper {
            max-width: 760px;
            margin: 0 auto;
            background: var(--paper);
            padding: 12px 14px 18px 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,.12);
        }

        .notice {
            max-width: 760px;
            margin: 0 auto 12px auto;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .notice.error {
            background: #fdeaea;
            border: 1px solid #e3b0b0;
            color: #9a2323;
        }

        .notice.success {
            background: #e9f8eb;
            border: 1px solid #b7dfbc;
            color: #1e6a29;
        }

        .header {
            text-align: center;
            margin-bottom: 6px;
        }

        .header img {
            width: 92px;
            height: auto;
            display: block;
            margin: 0 auto 4px auto;
        }

        .title-1, .title-2 {
            font-weight: 700;
            font-size: 20px;
            line-height: 1.15;
        }

        .title-2 {
            margin-top: 2px;
        }

        .date-line-wrap {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            margin: 6px 0 8px;
        }

        .date-line {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .line-input {
            border: 0;
            border-bottom: 2px solid #222;
            background: transparent;
            outline: none;
            padding: 2px 4px;
            font-size: 16px;
            min-width: 190px;
            text-align: center;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: var(--bg);
        }

        table.report td, table.report th {
            border: 1px solid var(--border);
            height: 24px;
            padding: 0;
            vertical-align: middle;
            font-size: 14px;
        }

        table.report th,
        .section-label,
        .total-label {
            font-weight: 700;
        }

        .center { text-align: center; }
        .right { text-align: right; }

        .cell-input {
            width: 100%;
            height: 100%;
            border: 0;
            outline: none;
            background: transparent;
            font-size: 14px;
            padding: 3px 6px;
        }

        .money {
            text-align: right;
            padding-right: 8px;
        }

        .readonly-cell {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 8px;
            font-weight: 700;
        }

        .spacer {
            height: 20px;
        }

        .top-balance-cell {
            font-size: 16px;
            text-align: center;
            font-weight: 700;
            background: #f1f1f1;
        }

        .footer-box {
            width: 290px;
            margin-left: auto;
            margin-top: 18px;
        }

        .footer-box table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: var(--bg);
        }

        .footer-box td {
            border: 1px solid var(--border);
            height: 24px;
            font-size: 14px;
            padding: 0;
        }

        .footer-box .label {
            font-weight: 700;
            padding-left: 8px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar,
            .notice {
                display: none !important;
            }

            .paper {
                box-shadow: none;
                margin: 0;
                max-width: none;
                width: 100%;
                padding: 8px 14px 14px;
            }

            .cell-input,
            .line-input {
                color: #000;
            }
        }
    </style>
</head>
<body>
    <?php if ($error !== ''): ?>
        <div class="notice error"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="notice success"><?= h($success) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div class="toolbar-left">
            <label for="history_id">History by Date:</label>
            <select id="history_id" onchange="if(this.value){ window.location='?id=' + this.value; } else { window.location='?'; }">
                <option value="">New / Select saved report</option>
                <?php foreach ($historyOptions as $option): ?>
                    <?php
                        $dateLabel = $option['report_date'] ? date('m/d/Y', strtotime((string)$option['report_date'])) : 'Undated';
                        $selected = ((string)$selectedId === (string)$option['id']) ? 'selected' : '';
                    ?>
                    <option value="<?= (int)$option['id'] ?>" <?= $selected ?>>
                        <?= h($dateLabel) ?> - #<?= (int)$option['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="toolbar-right">
            <button type="submit" form="reportForm">Save</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </div>

    <form id="reportForm" method="post" autocomplete="off">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= h($form['id']) ?>">

        <div class="paper">
            <div class="header">
                <img src="../../images/oxford_house_logo.png" alt="Oxford House Logo">
                <div class="title-1">Oxford House CO State Association</div>
                <div class="title-2">Financial Status Report</div>
            </div>

            <div class="date-line-wrap">
                <div class="date-line">
                    <span>Date:</span>
                    <input class="line-input" type="date" name="report_date" value="<?= h($form['report_date']) ?>">
                </div>
            </div>

            <table class="report">
                <colgroup>
                    <col style="width: 15%;">
                    <col style="width: 52%;">
                    <col style="width: 22%;">
                    <col style="width: 11%;">
                </colgroup>
                <tr>
                    <td colspan="2" style="border:0;background:#fff;"></td>
                    <td class="top-balance-cell">Beginning Balance</td>
                    <td>
                        <input class="cell-input money" type="number" step="0.01" name="beginning_balance" value="<?= h($form['beginning_balance']) ?>">
                    </td>
                </tr>
                <tr>
                    <th class="center">Date</th>
                    <th class="center">Money Received Source &amp; Purpose</th>
                    <th class="center" colspan="2">Amount</th>
                </tr>
                <?php foreach ($form['received_rows'] as $i => $row): ?>
                    <tr>
                        <td><input class="cell-input center" type="date" name="received[<?= $i ?>][entry_date]" value="<?= h((string)$row['entry_date']) ?>"></td>
                        <td><input class="cell-input" type="text" name="received[<?= $i ?>][source_purpose]" value="<?= h((string)$row['source_purpose']) ?>"></td>
                        <td colspan="2"><input class="cell-input money" type="number" step="0.01" name="received[<?= $i ?>][amount]" value="<?= h((string)$row['amount']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2" style="border:0;background:#fff;"></td>
                    <td class="center total-label">Total Received</td>
                    <td><div class="readonly-cell"><?= number_format($receivedTotal, 2) ?></div></td>
                </tr>
            </table>

            <div class="spacer"></div>

            <table class="report">
                <colgroup>
                    <col style="width: 15%;">
                    <col style="width: 59%;">
                    <col style="width: 13.5%;">
                    <col style="width: 12.5%;">
                </colgroup>
                <tr>
                    <th class="center">Date</th>
                    <th class="center">Money Spent Source &amp; Purpose</th>
                    <th class="center">Check #</th>
                    <th class="center">Amount</th>
                </tr>
                <?php foreach ($form['spent_rows'] as $i => $row): ?>
                    <tr>
                        <td><input class="cell-input center" type="date" name="spent[<?= $i ?>][entry_date]" value="<?= h((string)$row['entry_date']) ?>"></td>
                        <td><input class="cell-input" type="text" name="spent[<?= $i ?>][source_purpose]" value="<?= h((string)$row['source_purpose']) ?>"></td>
                        <td><input class="cell-input center" type="text" name="spent[<?= $i ?>][check_no]" value="<?= h((string)$row['check_no']) ?>"></td>
                        <td><input class="cell-input money" type="number" step="0.01" name="spent[<?= $i ?>][amount]" value="<?= h((string)$row['amount']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="footer-box">
                <table>
                    <colgroup>
                        <col style="width: 55%;">
                        <col style="width: 45%;">
                    </colgroup>
                    <tr>
                        <td class="label">Total Spent:</td>
                        <td><div class="readonly-cell"><?= number_format($spentTotal, 2) ?></div></td>
                    </tr>
                    <tr>
                        <td class="label">Ending Balance:</td>
                        <td><div class="readonly-cell"><?= number_format($endingBalance, 2) ?></div></td>
                    </tr>
                </table>
            </div>
        </div>
    </form>

    <script>
        const form = document.getElementById('reportForm');
        let saveTimer = null;

        function autoSubmit() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => {
                form.requestSubmit();
            }, 900);
        }

        form.querySelectorAll('input, textarea, select').forEach((el) => {
            if (el.name !== 'action') {
                el.addEventListener('change', autoSubmit);
                el.addEventListener('input', (event) => {
                    if (event.target.type !== 'date') {
                        autoSubmit();
                    }
                });
            }
        });
    </script>
</body>
</html>
