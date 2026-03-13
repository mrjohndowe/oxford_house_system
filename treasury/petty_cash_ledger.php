<?php
declare(strict_types=1);

/**
 * Oxford House Petty Cash Ledger
 * - Single-file PHP app
 * - Auto-saves to MySQL
 * - Reloads and edits prior records
 * - Auto-calculates running balance
 * - Print friendly
 * - Designed to closely match the uploaded Petty Cash Ledger sheet
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

/* =========================
   SETTINGS
========================= */
$logoPath = '../images/oxford_house_logo.png';
$rowCount = 24;

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
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/* =========================
   TABLES
========================= */
/* $pdo->exec("
    DROP TABLE IF EXISTS petty_cash_ledger_rows;
    DROP TABLE IF EXISTS petty_cash_ledgers;    
"); */
$pdo->exec("
CREATE TABLE IF NOT EXISTS petty_cash_ledgers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    ledger_date DATE NULL,
    beginning_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_house_date (house_name, ledger_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS petty_cash_ledger_rows (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ledger_id INT UNSIGNED NOT NULL,
    row_index INT NOT NULL,
    txn_date VARCHAR(50) NOT NULL DEFAULT '',
    products_purchased VARCHAR(255) NOT NULL DEFAULT '',
    vendor VARCHAR(255) NOT NULL DEFAULT '',
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    reimbursement_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_petty_cash_ledger_rows_ledger
        FOREIGN KEY (ledger_id) REFERENCES petty_cash_ledgers(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function asMoney(mixed $value): string
{
    if ($value === '' || $value === null) {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function posted(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function parseMoney(mixed $value): float
{
    $clean = preg_replace('/[^0-9.\-]/', '', (string)$value);
    return is_numeric($clean) ? (float)$clean : 0.0;
}

function emptyRow(): array
{
    return [
        'txn_date' => '',
        'products_purchased' => '',
        'vendor' => '',
        'amount' => '',
        'reimbursement_amount' => '',
        'balance' => '',
    ];
}

function getRowsFromPost(int $rowCount): array
{
    $rows = [];
    for ($i = 0; $i < $rowCount; $i++) {
        $rows[] = [
            'txn_date' => trim((string)($_POST['txn_date'][$i] ?? '')),
            'products_purchased' => trim((string)($_POST['products_purchased'][$i] ?? '')),
            'vendor' => trim((string)($_POST['vendor'][$i] ?? '')),
            'amount' => (string)($_POST['amount'][$i] ?? ''),
            'reimbursement_amount' => (string)($_POST['reimbursement_amount'][$i] ?? ''),
            'balance' => (string)($_POST['balance'][$i] ?? ''),
        ];
    }
    return $rows;
}

function saveLedger(PDO $pdo, string $houseName, ?string $ledgerDate, float $beginningBalance, array $rows): int
{
    $pdo->beginTransaction();

    try {
        $check = $pdo->prepare('SELECT id FROM petty_cash_ledgers WHERE house_name = :house_name AND ledger_date <=> :ledger_date LIMIT 1');
        $check->execute([
            ':house_name' => $houseName,
            ':ledger_date' => $ledgerDate !== '' ? $ledgerDate : null,
        ]);
        $existingId = $check->fetchColumn();

        if ($existingId) {
            $ledgerId = (int)$existingId;
            $update = $pdo->prepare('UPDATE petty_cash_ledgers SET beginning_balance = :beginning_balance WHERE id = :id');
            $update->execute([
                ':beginning_balance' => $beginningBalance,
                ':id' => $ledgerId,
            ]);

            $pdo->prepare('DELETE FROM petty_cash_ledger_rows WHERE ledger_id = :ledger_id')->execute([
                ':ledger_id' => $ledgerId,
            ]);
        } else {
            $insert = $pdo->prepare('INSERT INTO petty_cash_ledgers (house_name, ledger_date, beginning_balance) VALUES (:house_name, :ledger_date, :beginning_balance)');
            $insert->execute([
                ':house_name' => $houseName,
                ':ledger_date' => $ledgerDate !== '' ? $ledgerDate : null,
                ':beginning_balance' => $beginningBalance,
            ]);
            $ledgerId = (int)$pdo->lastInsertId();
        }

        $rowInsert = $pdo->prepare('
            INSERT INTO petty_cash_ledger_rows
            (ledger_id, row_index, txn_date, products_purchased, vendor, amount, reimbursement_amount, balance)
            VALUES
            (:ledger_id, :row_index, :txn_date, :products_purchased, :vendor, :amount, :reimbursement_amount, :balance)
        ');

        foreach ($rows as $index => $row) {
            $rowInsert->execute([
                ':ledger_id' => $ledgerId,
                ':row_index' => $index,
                ':txn_date' => trim((string)$row['txn_date']),
                ':products_purchased' => trim((string)$row['products_purchased']),
                ':vendor' => trim((string)$row['vendor']),
                ':amount' => parseMoney($row['amount']),
                ':reimbursement_amount' => parseMoney($row['reimbursement_amount']),
                ':balance' => parseMoney($row['balance']),
            ]);
        }

        $pdo->commit();
        return $ledgerId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function loadLedger(PDO $pdo, int $ledgerId, int $rowCount): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM petty_cash_ledgers WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $ledgerId]);
    $ledger = $stmt->fetch();

    if (!$ledger) {
        return null;
    }

    $rowsStmt = $pdo->prepare('SELECT * FROM petty_cash_ledger_rows WHERE ledger_id = :ledger_id ORDER BY row_index ASC');
    $rowsStmt->execute([':ledger_id' => $ledgerId]);
    $dbRows = $rowsStmt->fetchAll();

    $rows = [];
    for ($i = 0; $i < $rowCount; $i++) {
        $rows[$i] = emptyRow();
    }

    foreach ($dbRows as $dbRow) {
        $idx = (int)$dbRow['row_index'];
        if ($idx >= 0 && $idx < $rowCount) {
            $rows[$idx] = [
                'txn_date' => (string)$dbRow['txn_date'],
                'products_purchased' => (string)$dbRow['products_purchased'],
                'vendor' => (string)$dbRow['vendor'],
                'amount' => asMoney($dbRow['amount']),
                'reimbursement_amount' => asMoney($dbRow['reimbursement_amount']),
                'balance' => asMoney($dbRow['balance']),
            ];
        }
    }

    return [
        'id' => (int)$ledger['id'],
        'house_name' => (string)$ledger['house_name'],
        'ledger_date' => (string)($ledger['ledger_date'] ?? ''),
        'beginning_balance' => asMoney($ledger['beginning_balance']),
        'rows' => $rows,
    ];
}

/* =========================
   DEFAULT FORM DATA
========================= */
$message = '';
$messageType = 'success';
$currentLedgerId = isset($_GET['load_id']) ? (int)$_GET['load_id'] : 0;

$form = [
    'house_name' => '',
    'ledger_date' => '',
    'beginning_balance' => '',
    'rows' => array_fill(0, $rowCount, emptyRow()),
];

/* =========================
   SAVE / AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)posted('action', 'save');

    $form['house_name'] = trim((string)posted('house_name', ''));
    $form['ledger_date'] = trim((string)posted('ledger_date', ''));
    $form['beginning_balance'] = (string)posted('beginning_balance', '');
    $form['rows'] = getRowsFromPost($rowCount);

    if ($form['house_name'] === '') {
        if ($action === 'autosave') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'House name is required before auto-save can run.',
            ]);
            exit;
        }
        $message = 'House name is required.';
        $messageType = 'error';
    } else {
        try {
            $currentLedgerId = saveLedger(
                $pdo,
                $form['house_name'],
                $form['ledger_date'] !== '' ? $form['ledger_date'] : null,
                parseMoney($form['beginning_balance']),
                $form['rows']
            );

            if ($action === 'autosave') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'ok',
                    'message' => 'Auto-saved',
                    'ledger_id' => $currentLedgerId,
                ]);
                exit;
            }

            $message = 'Ledger saved successfully.';
            $messageType = 'success';
        } catch (Throwable $e) {
            if ($action === 'autosave') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Save failed: ' . $e->getMessage(),
                ]);
                exit;
            }
            $message = 'Save failed: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

/* =========================
   LOAD EXISTING RECORD
========================= */
if ($currentLedgerId > 0) {
    $loaded = loadLedger($pdo, $currentLedgerId, $rowCount);
    if ($loaded) {
        $form = $loaded;
    }
}

/* =========================
   HISTORY LIST
========================= */
$historyStmt = $pdo->query('
    SELECT id, house_name, ledger_date, updated_at
    FROM petty_cash_ledgers
    ORDER BY COALESCE(ledger_date, DATE(updated_at)) DESC, house_name ASC, id DESC
');
$historyRows = $historyStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petty Cash Ledger</title>
    <style>
        @page { size: Letter portrait; margin: 0.35in; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #f2f2f2; font-family: Arial, Helvetica, sans-serif; color: #111; }
        body { padding: 18px; }

        .toolbar {
            width: 100%;
            max-width: 860px;
            margin: 0 auto 14px auto;
            background: #fff;
            border: 1px solid #cfcfcf;
            padding: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .toolbar label {
            font-size: 12px;
            font-weight: 700;
        }

        .toolbar select,
        .toolbar button {
            height: 34px;
            border: 1px solid #999;
            background: #fff;
            padding: 6px 10px;
            font-size: 13px;
        }

        .toolbar button {
            cursor: pointer;
            font-weight: 700;
        }

        .message {
            width: 100%;
            max-width: 860px;
            margin: 0 auto 12px auto;
            padding: 10px 12px;
            border: 1px solid;
            font-size: 13px;
            background: #fff;
        }
        .message.success { border-color: #72b17f; color: #1f5e2f; }
        .message.error { border-color: #d57d7d; color: #8b1e1e; }

        .page {
            position: relative;
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
            background: #fff;
            border: 4px double #222;
            padding: 6px;
            box-shadow: 0 3px 16px rgba(0,0,0,0.08);
        }

        .sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sheet td,
        .sheet th {
            border: 2px solid #222;
            padding: 0;
            vertical-align: middle;
        }

        .sheet .no-border { border: 0 !important; }

        .title-row td {
            border-left: 0;
            border-right: 0;
            border-top: 0;
            height: 58px;
            position: relative;
        }

        .logo-box {
            position: absolute;
            left: 6px;
            top: 4px;
            width: 72px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .title {
            text-align: center;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .meta-wrap {
            display: grid;
            grid-template-columns: 1.15fr 0.95fr;
            gap: 0;
        }

        .meta-cell {
            height: 56px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 400;
            white-space: nowrap;
        }

        .meta-cell span {
            flex: 0 0 auto;
        }

        .line-input {
            flex: 1 1 auto;
            min-width: 0;
            border: 0;
            border-bottom: 2px solid #333;
            outline: none;
            font-size: 18px;
            height: 28px;
            background: transparent;
            padding: 0 2px;
        }

        .col-head th {
            height: 48px;
            text-align: center;
            font-size: 15px;
            font-weight: 400;
            line-height: 1.05;
            padding: 4px 4px 2px 4px;
            vertical-align: bottom;
        }

        .data-row td {
            height: 37px;
        }

        .cell-input {
            width: 100%;
            height: 100%;
            border: 0;
            outline: none;
            background: transparent;
            padding: 6px 7px;
            font-size: 14px;
        }

        .cell-input.center { text-align: center; }
        .cell-input.money { text-align: right; padding-right: 8px; }

        .autosave-status {
            font-size: 12px;
            color: #555;
            min-width: 85px;
        }

        .screen-only { display: block; }
        .print-only { display: none; }

        @media print {
            html, body {
                background: #fff;
                padding: 0;
            }
            .screen-only { display: none !important; }
            .print-only { display: block !important; }
            .page {
                max-width: none;
                margin: 0;
                box-shadow: none;
            }
            .line-input,
            .cell-input {
                color: #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar screen-only">
        <div class="toolbar-left">
            <label for="history_load">Reload/Edit Prior Record</label>
            <select id="history_load">
                <option value="">Select saved ledger</option>
                <?php foreach ($historyRows as $history): ?>
                    <option value="<?= (int)$history['id'] ?>" <?= ((int)$history['id'] === (int)$currentLedgerId) ? 'selected' : '' ?>>
                        <?= h($history['house_name']) ?>
                        <?= !empty($history['ledger_date']) ? ' - ' . h($history['ledger_date']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="loadHistoryBtn">Load</button>
        </div>
        <div class="toolbar-right">
            <span class="autosave-status" id="autosaveStatus">Ready</span>
            <button type="submit" form="ledgerForm">Save</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= h($messageType) ?> screen-only"><?= h($message) ?></div>
    <?php endif; ?>

    <form id="ledgerForm" method="post" action="">
        <input type="hidden" name="action" id="formAction" value="save">

        <div class="page">
            <table class="sheet">
                <colgroup>
                    <col style="width:11%;">
                    <col style="width:22%;">
                    <col style="width:22%;">
                    <col style="width:11%;">
                    <col style="width:22%;">
                    <col style="width:12%;">
                </colgroup>

                <tr class="title-row">
                    <td colspan="6">
                        <div class="logo-box">
                            <?php if (is_file($logoPath)): ?>
                                <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
                            <?php endif; ?>
                        </div>
                        <div class="title">PETTY CASH LEDGER</div>
                    </td>
                </tr>

                <tr>
                    <td colspan="6" class="no-border" style="padding:0; border-left:0; border-right:0;">
                        <div class="meta-wrap">
                            <div class="meta-cell" style="border-right:2px solid #222; border-top:2px solid #222; border-bottom:2px solid #222;">
                                <span>HOUSE NAME:</span>
                                <input class="line-input" type="text" name="house_name" id="house_name" value="<?= h($form['house_name']) ?>" required>
                            </div>
                            <div class="meta-cell" style="border-top:2px solid #222; border-bottom:2px solid #222;">
                                <span>BEGINNING BALANCE:</span>
                                <input class="line-input" type="text" name="beginning_balance" id="beginning_balance" value="<?= h($form['beginning_balance']) ?>" inputmode="decimal">
                                <input type="hidden" name="ledger_date" id="ledger_date" value="<?= h($form['ledger_date']) ?>">
                            </div>
                        </div>
                    </td>
                </tr>

                <tr class="col-head">
                    <th>DATE</th>
                    <th>PRODUCTS<br>PURCHASED</th>
                    <th>VENDOR</th>
                    <th>AMOUNT</th>
                    <th>REIMBURSEMENT<br>AMOUNT</th>
                    <th>BALANCE</th>
                </tr>

                <?php for ($i = 0; $i < $rowCount; $i++): ?>
                    <tr class="data-row">
                        <td><input class="cell-input center" type="text" name="txn_date[]" value="<?= h($form['rows'][$i]['txn_date']) ?>"></td>
                        <td><input class="cell-input" type="text" name="products_purchased[]" value="<?= h($form['rows'][$i]['products_purchased']) ?>"></td>
                        <td><input class="cell-input" type="text" name="vendor[]" value="<?= h($form['rows'][$i]['vendor']) ?>"></td>
                        <td><input class="cell-input money js-amount" type="text" name="amount[]" value="<?= h($form['rows'][$i]['amount']) ?>" inputmode="decimal"></td>
                        <td><input class="cell-input money js-reimbursement" type="text" name="reimbursement_amount[]" value="<?= h($form['rows'][$i]['reimbursement_amount']) ?>" inputmode="decimal"></td>
                        <td><input class="cell-input money js-balance" type="text" name="balance[]" value="<?= h($form['rows'][$i]['balance']) ?>" readonly tabindex="-1"></td>
                    </tr>
                <?php endfor; ?>
            </table>
        </div>
    </form>

    <script>
        const form = document.getElementById('ledgerForm');
        const autosaveStatus = document.getElementById('autosaveStatus');
        const formAction = document.getElementById('formAction');
        const beginningBalanceInput = document.getElementById('beginning_balance');
        const loadHistoryBtn = document.getElementById('loadHistoryBtn');
        const historySelect = document.getElementById('history_load');
        const ledgerDateInput = document.getElementById('ledger_date');
        let autosaveTimer = null;

        function parseMoney(value) {
            const cleaned = String(value || '').replace(/[^0-9.\-]/g, '');
            const num = parseFloat(cleaned);
            return Number.isFinite(num) ? num : 0;
        }

        function formatMoney(value) {
            return Number(value).toFixed(2);
        }

        function calculateRunningBalances() {
            let running = parseMoney(beginningBalanceInput.value);
            const amountInputs = document.querySelectorAll('.js-amount');
            const reimbursementInputs = document.querySelectorAll('.js-reimbursement');
            const balanceInputs = document.querySelectorAll('.js-balance');

            for (let i = 0; i < balanceInputs.length; i++) {
                const amount = parseMoney(amountInputs[i].value);
                const reimbursement = parseMoney(reimbursementInputs[i].value);

                if (amount !== 0) {
                    running -= amount;
                }
                if (reimbursement !== 0) {
                    running += reimbursement;
                }

                balanceInputs[i].value = (amount !== 0 || reimbursement !== 0 || i === 0 || beginningBalanceInput.value !== '')
                    ? formatMoney(running)
                    : '';
            }
        }

        function ensureLedgerDate() {
            if (!ledgerDateInput.value) {
                const now = new Date();
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, '0');
                const dd = String(now.getDate()).padStart(2, '0');
                ledgerDateInput.value = `${yyyy}-${mm}-${dd}`;
            }
        }

        async function autosave() {
            calculateRunningBalances();
            ensureLedgerDate();
            formAction.value = 'autosave';
            autosaveStatus.textContent = 'Saving...';

            try {
                const formData = new FormData(form);
                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.status === 'ok') {
                    autosaveStatus.textContent = 'Auto-saved';
                } else {
                    autosaveStatus.textContent = data.message || 'Save failed';
                }
            } catch (error) {
                autosaveStatus.textContent = 'Auto-save error';
            } finally {
                formAction.value = 'save';
            }
        }

        function scheduleAutosave() {
            calculateRunningBalances();
            autosaveStatus.textContent = 'Pending...';
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(autosave, 900);
        }

        form.addEventListener('input', scheduleAutosave);
        form.addEventListener('change', scheduleAutosave);
        beginningBalanceInput.addEventListener('input', calculateRunningBalances);

        form.addEventListener('submit', function() {
            calculateRunningBalances();
            ensureLedgerDate();
            formAction.value = 'save';
        });

        loadHistoryBtn.addEventListener('click', function() {
            if (historySelect.value) {
                window.location.href = '?load_id=' + encodeURIComponent(historySelect.value);
            }
        });

        calculateRunningBalances();
    </script>
</body>
</html>
