<?php
declare(strict_types=1);

/**
 * Oxford House - Equal Expense Share Member Ledger
 * Single-file PHP form matching the scanned layout as closely as possible.
 *
 * Features:
 * - Fillable ledger
 * - Auto-save to MySQL with debounce
 * - Save/update by member name + selected history record
 * - History dropdown filtered by member name
 * - Printable layout
 *
 * Logo path expected at:
 * ../images/oxford_house_logo.png
 */

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
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/* =========================
   TABLE SETUP
========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS oxford_house_member_ledger (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    member_name VARCHAR(255) NOT NULL DEFAULT '',
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    move_in_month VARCHAR(10) NOT NULL DEFAULT '',
    move_in_day VARCHAR(10) NOT NULL DEFAULT '',
    move_in_year VARCHAR(10) NOT NULL DEFAULT '',

    row_1 JSON NULL,
    row_2 JSON NULL,
    row_3 JSON NULL,
    row_4 JSON NULL,
    row_5 JSON NULL,
    row_6 JSON NULL,
    row_7 JSON NULL,
    row_8 JSON NULL,
    row_9 JSON NULL,
    row_10 JSON NULL,

    move_in_fee_amount VARCHAR(50) NOT NULL DEFAULT '',
    move_in_fee_date_paid VARCHAR(50) NOT NULL DEFAULT '',
    departure_date VARCHAR(50) NOT NULL DEFAULT '',
    departure_ending_balance VARCHAR(50) NOT NULL DEFAULT '',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_member_name (member_name),
    INDEX idx_house_name (house_name),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function posted(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function buildLedgerRowFromRequest(int $i): array
{
    return [
        'week_start' => posted("week_start_{$i}"),
        'previous_balance' => posted("previous_balance_{$i}"),
        'ees_due' => posted("ees_due_{$i}"),
        'fines_other' => posted("fines_other_{$i}"),
        'approved_receipts' => posted("approved_receipts_{$i}"),
        'total' => posted("total_{$i}"),
        'amount_paid' => posted("amount_paid_{$i}"),
        'ending_balance' => posted("ending_balance_{$i}"),
        'notes' => posted("notes_{$i}"),
    ];
}

function emptyLedgerRow(): array
{
    return [
        'week_start' => '',
        'previous_balance' => '',
        'ees_due' => '',
        'fines_other' => '',
        'approved_receipts' => '',
        'total' => '',
        'amount_paid' => '',
        'ending_balance' => '',
        'notes' => '',
    ];
}

function normalizeLedgerRow(mixed $row): array
{
    if (is_string($row) && $row !== '') {
        $decoded = json_decode($row, true);
        if (is_array($decoded)) {
            $row = $decoded;
        }
    }

    if (!is_array($row)) {
        $row = [];
    }

    return array_merge(emptyLedgerRow(), $row);
}

/* =========================
   AJAX: HISTORY SEARCH
========================= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
    header('Content-Type: application/json; charset=utf-8');

    $memberName = trim((string)($_GET['member_name'] ?? ''));
    if ($memberName === '') {
        echo json_encode(['ok' => true, 'items' => []]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id, member_name, house_name, updated_at,
               move_in_month, move_in_day, move_in_year
        FROM oxford_house_member_ledger
        WHERE member_name LIKE :member_name
        ORDER BY updated_at DESC, id DESC
        LIMIT 100
    ");
    $stmt->execute([
        ':member_name' => '%' . $memberName . '%',
    ]);

    echo json_encode([
        'ok' => true,
        'items' => $stmt->fetchAll(),
    ]);
    exit;
}

/* =========================
   AJAX: LOAD RECORD
========================= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
    header('Content-Type: application/json; charset=utf-8');

    $id = (int)($_GET['id'] ?? 0);
    if ($id < 1) {
        echo json_encode(['ok' => false, 'message' => 'Invalid record ID.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM oxford_house_member_ledger WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        echo json_encode(['ok' => false, 'message' => 'Record not found.']);
        exit;
    }

    for ($i = 1; $i <= 10; $i++) {
        $record["row_{$i}"] = normalizeLedgerRow($record["row_{$i}"] ?? null);
    }

    echo json_encode(['ok' => true, 'record' => $record]);
    exit;
}

/* =========================
   AJAX: AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'autosave')) {
    header('Content-Type: application/json; charset=utf-8');

    $id = (int)($_POST['record_id'] ?? 0);
    $memberName = posted('member_name');
    $houseName = posted('house_name');
    $moveInMonth = posted('move_in_month');
    $moveInDay = posted('move_in_day');
    $moveInYear = posted('move_in_year');
    $moveInFeeAmount = posted('move_in_fee_amount');
    $moveInFeeDatePaid = posted('move_in_fee_date_paid');
    $departureDate = posted('departure_date');
    $departureEndingBalance = posted('departure_ending_balance');

    $rows = [];
    for ($i = 1; $i <= 10; $i++) {
        $rows[$i] = json_encode(buildLedgerRowFromRequest($i), JSON_UNESCAPED_UNICODE);
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE oxford_house_member_ledger SET
                    member_name = :member_name,
                    house_name = :house_name,
                    move_in_month = :move_in_month,
                    move_in_day = :move_in_day,
                    move_in_year = :move_in_year,
                    row_1 = :row_1,
                    row_2 = :row_2,
                    row_3 = :row_3,
                    row_4 = :row_4,
                    row_5 = :row_5,
                    row_6 = :row_6,
                    row_7 = :row_7,
                    row_8 = :row_8,
                    row_9 = :row_9,
                    row_10 = :row_10,
                    move_in_fee_amount = :move_in_fee_amount,
                    move_in_fee_date_paid = :move_in_fee_date_paid,
                    departure_date = :departure_date,
                    departure_ending_balance = :departure_ending_balance
                WHERE id = :id
            ");
            $stmt->execute([
                ':member_name' => $memberName,
                ':house_name' => $houseName,
                ':move_in_month' => $moveInMonth,
                ':move_in_day' => $moveInDay,
                ':move_in_year' => $moveInYear,
                ':row_1' => $rows[1],
                ':row_2' => $rows[2],
                ':row_3' => $rows[3],
                ':row_4' => $rows[4],
                ':row_5' => $rows[5],
                ':row_6' => $rows[6],
                ':row_7' => $rows[7],
                ':row_8' => $rows[8],
                ':row_9' => $rows[9],
                ':row_10' => $rows[10],
                ':move_in_fee_amount' => $moveInFeeAmount,
                ':move_in_fee_date_paid' => $moveInFeeDatePaid,
                ':departure_date' => $departureDate,
                ':departure_ending_balance' => $departureEndingBalance,
                ':id' => $id,
            ]);
            echo json_encode(['ok' => true, 'record_id' => $id, 'message' => 'Updated']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO oxford_house_member_ledger (
                member_name, house_name, move_in_month, move_in_day, move_in_year,
                row_1, row_2, row_3, row_4, row_5, row_6, row_7, row_8, row_9, row_10,
                move_in_fee_amount, move_in_fee_date_paid, departure_date, departure_ending_balance
            ) VALUES (
                :member_name, :house_name, :move_in_month, :move_in_day, :move_in_year,
                :row_1, :row_2, :row_3, :row_4, :row_5, :row_6, :row_7, :row_8, :row_9, :row_10,
                :move_in_fee_amount, :move_in_fee_date_paid, :departure_date, :departure_ending_balance
            )
        ");
        $stmt->execute([
            ':member_name' => $memberName,
            ':house_name' => $houseName,
            ':move_in_month' => $moveInMonth,
            ':move_in_day' => $moveInDay,
            ':move_in_year' => $moveInYear,
            ':row_1' => $rows[1],
            ':row_2' => $rows[2],
            ':row_3' => $rows[3],
            ':row_4' => $rows[4],
            ':row_5' => $rows[5],
            ':row_6' => $rows[6],
            ':row_7' => $rows[7],
            ':row_8' => $rows[8],
            ':row_9' => $rows[9],
            ':row_10' => $rows[10],
            ':move_in_fee_amount' => $moveInFeeAmount,
            ':move_in_fee_date_paid' => $moveInFeeDatePaid,
            ':departure_date' => $departureDate,
            ':departure_ending_balance' => $departureEndingBalance,
        ]);

        echo json_encode([
            'ok' => true,
            'record_id' => (int)$pdo->lastInsertId(),
            'message' => 'Created',
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
            'ok' => false,
            'message' => 'Save failed: ' . $e->getMessage(),
        ]);
        exit;
    }
}

/* =========================
   DEFAULT FORM DATA
========================= */
$form = [
    'record_id' => 0,
    'member_name' => '',
    'house_name' => '',
    'move_in_month' => '',
    'move_in_day' => '',
    'move_in_year' => '',
    'move_in_fee_amount' => '',
    'move_in_fee_date_paid' => '',
    'departure_date' => '',
    'departure_ending_balance' => '',
    'rows' => [],
];

for ($i = 1; $i <= 10; $i++) {
    $form['rows'][$i] = emptyLedgerRow();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Equal Expense Share Member Ledger</title>
    <style>
        @page {
            size: Letter portrait;
            margin: 0.35in;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
        }

        body {
            padding: 18px;
        }

        .toolbar {
            width: 100%;
            max-width: 980px;
            margin: 0 auto 14px auto;
            background: #ffffff;
            border: 1px solid #d8d8d8;
            padding: 12px 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 14px;
            align-items: end;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
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
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .toolbar input,
        .toolbar select,
        .toolbar button {
            height: 36px;
            border: 1px solid #999;
            background: #fff;
            padding: 6px 10px;
            font-size: 14px;
        }

        .toolbar button {
            cursor: pointer;
            font-weight: 700;
        }

        .status {
            min-width: 160px;
            font-size: 13px;
            font-weight: 700;
            color: #444;
            padding-bottom: 8px;
        }

        .sheet {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
            padding: 28px 34px 30px 34px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        .form-page {
            width: 100%;
        }

        .header {
            display: grid;
            grid-template-columns: 110px 1fr;
            column-gap: 18px;
            align-items: start;
        }

        .logo-wrap {
            width: 110px;
            text-align: left;
            padding-top: 4px;
        }

        .logo-wrap img {
            width: 98px;
            height: auto;
            display: block;
        }

        .title-wrap {
            text-align: center;
            position: relative;
        }

        .house-line-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .house-title {
            font-size: 28px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: .01em;
        }

        .house-line {
            flex: 1;
            border-bottom: 2px solid #222;
            height: 18px;
            min-width: 120px;
        }

        .house-name-fill {
            position: absolute;
            top: 5px;
            right: 0;
            width: 44%;
            height: 28px;
            border: 0;
            background: transparent;
            text-align: left;
            font-size: 20px;
            font-weight: 700;
            padding: 0 4px;
        }

        .main-title {
            font-size: 29px;
            line-height: 1.02;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
        }

        .subtext {
            margin-top: 12px;
            text-align: center;
            font-size: 14px;
            line-height: 1.25;
        }

        .top-fields {
            margin-top: 24px;
            display: grid;
            grid-template-columns: 1fr 320px;
            column-gap: 20px;
            align-items: center;
        }

        .line-field {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 500;
        }

        .line-field .label {
            white-space: nowrap;
        }

        .line-input {
            border: 0;
            border-bottom: 1px solid #111;
            background: transparent;
            height: 28px;
            padding: 0 4px;
            font-size: 18px;
            width: 100%;
        }

        .date-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            font-size: 18px;
        }

        .date-box {
            width: 56px;
            border: 0;
            border-bottom: 1px solid #111;
            background: transparent;
            text-align: center;
            height: 28px;
            font-size: 18px;
        }

        .ledger-head {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 13% 12.4% 12.4% 12.4% 12.4% 12.4% 12.4% 12.6%;
            align-items: end;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .01em;
        }

        .ledger-head div {
            text-align: center;
            padding: 0 4px 8px 4px;
            position: relative;
        }

        .symbol-plus,
        .symbol-minus,
        .symbol-equals {
            position: absolute;
            right: -10px;
            top: 11px;
            font-size: 22px;
            font-weight: 800;
            line-height: 1;
        }

        .symbol-minus { right: -9px; }
        .symbol-equals { right: -9px; }

        .ledger-row {
            display: grid;
            grid-template-columns: 13% 12.4% 12.4% 12.4% 12.4% 12.4% 12.4% 12.6%;
            min-height: 31px;
        }

        .cell {
            border: 1px solid #222;
            border-right: 0;
            position: relative;
            height: 31px;
        }

        .cell:last-child {
            border-right: 1px solid #222;
        }

        .cell-input {
            width: 100%;
            height: 100%;
            border: 0;
            padding: 2px 6px;
            font-size: 13px;
            background: transparent;
            text-align: center;
        }

        .notes-row {
            display: grid;
            grid-template-columns: 13% 87%;
            margin-bottom: 16px;
        }

        .notes-label {
            font-size: 16px;
            font-weight: 700;
            text-align: right;
            padding: 7px 2px 0 0;
        }

        .notes-box {
            border: 1px solid #222;
            min-height: 34px;
            display: flex;
            align-items: stretch;
        }

        .notes-input {
            width: 100%;
            min-height: 34px;
            border: 0;
            resize: none;
            overflow: hidden;
            padding: 7px 8px 5px 8px;
            font-size: 13px;
            line-height: 1.2;
            background: transparent;
        }

        .bottom-area {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: end;
        }

        .bottom-left,
        .bottom-right {
            display: flex;
            align-items: end;
            gap: 8px;
        }

        .bottom-left {
            justify-content: flex-start;
        }

        .bottom-right {
            justify-content: flex-end;
        }

        .bottom-label {
            font-size: 16px;
            font-weight: 500;
            white-space: nowrap;
        }

        .stack-head {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 15px;
            text-align: center;
            min-width: 110px;
        }

        .mini-input {
            width: 120px;
            height: 28px;
            border: 1px solid #222;
            padding: 3px 6px;
            font-size: 14px;
            background: transparent;
            text-align: center;
        }

        .footer-note {
            margin-top: 16px;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: 2px solid rgba(0,0,0,.18);
            outline-offset: 0;
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
                box-shadow: none;
                margin: 0;
                max-width: none;
                padding: 18px 26px 16px 26px;
            }

            .house-name-fill,
            .line-input,
            .date-box,
            .cell-input,
            .notes-input,
            .mini-input {
                outline: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-group">
            <label for="historyMemberSearch">History Search by Member Name</label>
            <input type="text" id="historyMemberSearch" placeholder="Enter member name">
        </div>

        <div class="toolbar-group" style="min-width:260px;">
            <label for="historySelect">History Records</label>
            <select id="historySelect">
                <option value="">Select saved ledger...</option>
            </select>
        </div>

        <div class="toolbar-group" style="min-width:120px;">
            <label>&nbsp;</label>
            <button type="button" id="printBtn">Print</button>
        </div>

        <div class="status" id="saveStatus">Ready</div>
    </div>

    <div class="sheet">
        <form id="ledgerForm" class="form-page" autocomplete="off">
            <input type="hidden" name="action" value="autosave">
            <input type="hidden" name="record_id" id="record_id" value="0">

            <div class="header">
                <div class="logo-wrap">
                    <img src="../images/oxford_house_logo.png" alt="Oxford House Logo">
                </div>

                <div class="title-wrap">
                    <div class="house-line-row">
                        <div class="house-title">OXFORD HOUSE -</div>
                        <div class="house-line"></div>
                    </div>
                    <input type="text" class="house-name-fill" name="house_name" id="house_name" value="<?= h($form['house_name']) ?>">
                    <h1 class="main-title">EQUAL EXPENSE SHARE<br>MEMBER LEDGER</h1>
                    <div class="subtext">
                        <div>Equal Expense Share = EES</div>
                        <div>EES should be adjusted based on occupancy.</div>
                    </div>
                </div>
            </div>

            <div class="top-fields">
                <div class="line-field">
                    <span class="label">MEMBER NAME:</span>
                    <input type="text" class="line-input" name="member_name" id="member_name" value="<?= h($form['member_name']) ?>">
                </div>

                <div class="date-group">
                    <span>MOVE-IN DATE:</span>
                    <input type="text" class="date-box" name="move_in_month" id="move_in_month" value="<?= h($form['move_in_month']) ?>">
                    <span>/</span>
                    <input type="text" class="date-box" name="move_in_day" id="move_in_day" value="<?= h($form['move_in_day']) ?>">
                    <span>/</span>
                    <input type="text" class="date-box" name="move_in_year" id="move_in_year" value="<?= h($form['move_in_year']) ?>">
                </div>
            </div>

            <div class="ledger-head">
                <div>WEEK START</div>
                <div>PREVIOUS<br>BALANCE<span class="symbol-plus">+</span></div>
                <div>EES<br>DUE<span class="symbol-plus">+</span></div>
                <div>FINES/<br>OTHER<span class="symbol-minus">-</span></div>
                <div>APPROVED<br>RECEIPTS<span class="symbol-equals">=</span></div>
                <div>TOTAL</div>
                <div>AMOUNT<br>PAID</div>
                <div>ENDING<br>BALANCE</div>
            </div>

            <?php for ($i = 1; $i <= 10; $i++): ?>
                <div class="ledger-row">
                    <div class="cell"><input class="cell-input money-calc-trigger" type="text" name="week_start_<?= $i ?>" id="week_start_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input money-calc-trigger" type="text" name="previous_balance_<?= $i ?>" id="previous_balance_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input money-calc-trigger" type="text" name="ees_due_<?= $i ?>" id="ees_due_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input money-calc-trigger" type="text" name="fines_other_<?= $i ?>" id="fines_other_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input money-calc-trigger" type="text" name="approved_receipts_<?= $i ?>" id="approved_receipts_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input" type="text" name="total_<?= $i ?>" id="total_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input money-calc-trigger" type="text" name="amount_paid_<?= $i ?>" id="amount_paid_<?= $i ?>"></div>
                    <div class="cell"><input class="cell-input" type="text" name="ending_balance_<?= $i ?>" id="ending_balance_<?= $i ?>"></div>
                </div>
                <div class="notes-row">
                    <div class="notes-label">NOTES:</div>
                    <div class="notes-box">
                        <textarea class="notes-input autosize" name="notes_<?= $i ?>" id="notes_<?= $i ?>" rows="1"></textarea>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="bottom-area">
                <div class="bottom-left">
                    <div class="bottom-label">MOVE-IN FEE:</div>
                    <div class="stack-head">
                        <div>Amount</div>
                        <input type="text" class="mini-input" name="move_in_fee_amount" id="move_in_fee_amount" value="<?= h($form['move_in_fee_amount']) ?>">
                    </div>
                    <div class="stack-head">
                        <div>Date Paid</div>
                        <input type="text" class="mini-input" name="move_in_fee_date_paid" id="move_in_fee_date_paid" value="<?= h($form['move_in_fee_date_paid']) ?>">
                    </div>
                </div>

                <div class="bottom-right">
                    <div class="bottom-label">DEPARTURE:</div>
                    <div class="stack-head">
                        <div>Date</div>
                        <input type="text" class="mini-input" name="departure_date" id="departure_date" value="<?= h($form['departure_date']) ?>">
                    </div>
                    <div class="stack-head">
                        <div>Ending Bal</div>
                        <input type="text" class="mini-input" name="departure_ending_balance" id="departure_ending_balance" value="<?= h($form['departure_ending_balance']) ?>">
                    </div>
                </div>
            </div>

            <div class="footer-note">
                Upon departure, staple final ledger to member application and place in files.
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('ledgerForm');
        const saveStatus = document.getElementById('saveStatus');
        const historyMemberSearch = document.getElementById('historyMemberSearch');
        const historySelect = document.getElementById('historySelect');
        const recordIdInput = document.getElementById('record_id');
        const printBtn = document.getElementById('printBtn');

        let saveTimer = null;
        let historyTimer = null;
        let isSaving = false;

        function setStatus(message) {
            saveStatus.textContent = message;
        }

        function autosizeTextarea(el) {
            el.style.height = 'auto';
            el.style.height = Math.max(el.scrollHeight, 34) + 'px';
        }

        document.querySelectorAll('.autosize').forEach(el => {
            autosizeTextarea(el);
            el.addEventListener('input', () => autosizeTextarea(el));
        });

        function parseMoney(value) {
            const cleaned = String(value || '').replace(/[^0-9.-]/g, '');
            const num = parseFloat(cleaned);
            return Number.isFinite(num) ? num : 0;
        }

        function formatMoney(value) {
            if (value === '' || value === null || typeof value === 'undefined') return '';
            const num = Number(value);
            if (!Number.isFinite(num)) return '';
            return num.toFixed(2);
        }

        function recalcRow(i) {
            const previousBalance = parseMoney(document.getElementById(`previous_balance_${i}`).value);
            const eesDue = parseMoney(document.getElementById(`ees_due_${i}`).value);
            const finesOther = parseMoney(document.getElementById(`fines_other_${i}`).value);
            const approvedReceipts = parseMoney(document.getElementById(`approved_receipts_${i}`).value);
            const amountPaid = parseMoney(document.getElementById(`amount_paid_${i}`).value);

            const total = previousBalance + eesDue + finesOther - approvedReceipts;
            const endingBalance = total - amountPaid;

            document.getElementById(`total_${i}`).value = formatMoney(total);
            document.getElementById(`ending_balance_${i}`).value = formatMoney(endingBalance);
        }

        function recalcAllRows() {
            for (let i = 1; i <= 10; i++) {
                recalcRow(i);
            }
        }

        document.querySelectorAll('.money-calc-trigger').forEach(el => {
            el.addEventListener('input', () => {
                const parts = el.id.split('_');
                const rowNumber = parts[parts.length - 1];
                recalcRow(rowNumber);
            });
        });

        function serializeForm() {
            return new FormData(form);
        }

        async function saveForm() {
            if (isSaving) return;
            isSaving = true;
            setStatus('Saving...');

            try {
                recalcAllRows();
                const response = await fetch(window.location.pathname, {
                    method: 'POST',
                    body: serializeForm()
                });
                const data = await response.json();

                if (data.ok) {
                    if (data.record_id) {
                        recordIdInput.value = data.record_id;
                    }
                    setStatus('Saved');
                } else {
                    setStatus(data.message || 'Save failed');
                }
            } catch (error) {
                setStatus('Save error');
            } finally {
                isSaving = false;
            }
        }

        function queueSave() {
            setStatus('Pending changes...');
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveForm, 700);
        }

        form.querySelectorAll('input, textarea, select').forEach(el => {
            if (el.id !== 'record_id') {
                el.addEventListener('input', queueSave);
                el.addEventListener('change', queueSave);
            }
        });

        async function loadHistory(memberName) {
            if (!memberName.trim()) {
                historySelect.innerHTML = '<option value="">Select saved ledger...</option>';
                return;
            }

            historySelect.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`${window.location.pathname}?ajax=history&member_name=${encodeURIComponent(memberName)}`);
                const data = await response.json();

                if (!data.ok) {
                    historySelect.innerHTML = '<option value="">No records found</option>';
                    return;
                }

                let options = '<option value="">Select saved ledger...</option>';
                for (const item of data.items) {
                    const moveDate = [item.move_in_month, item.move_in_day, item.move_in_year].filter(Boolean).join('/');
                    const label = `${item.member_name || 'Unnamed'}${item.house_name ? ' - ' + item.house_name : ''}${moveDate ? ' - Move-in: ' + moveDate : ''} - ${item.updated_at}`;
                    options += `<option value="${item.id}">${label}</option>`;
                }

                historySelect.innerHTML = options;
            } catch (error) {
                historySelect.innerHTML = '<option value="">Error loading history</option>';
            }
        }

        historyMemberSearch.addEventListener('input', () => {
            clearTimeout(historyTimer);
            historyTimer = setTimeout(() => loadHistory(historyMemberSearch.value), 350);
        });

        function setValue(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = value ?? '';
            if (el.classList.contains('autosize')) {
                autosizeTextarea(el);
            }
        }

        async function loadRecord(id) {
            if (!id) return;
            setStatus('Loading...');

            try {
                const response = await fetch(`${window.location.pathname}?ajax=load&id=${encodeURIComponent(id)}`);
                const data = await response.json();

                if (!data.ok || !data.record) {
                    setStatus(data.message || 'Load failed');
                    return;
                }

                const r = data.record;
                setValue('record_id', r.id || 0);
                setValue('member_name', r.member_name || '');
                setValue('house_name', r.house_name || '');
                setValue('move_in_month', r.move_in_month || '');
                setValue('move_in_day', r.move_in_day || '');
                setValue('move_in_year', r.move_in_year || '');
                setValue('move_in_fee_amount', r.move_in_fee_amount || '');
                setValue('move_in_fee_date_paid', r.move_in_fee_date_paid || '');
                setValue('departure_date', r.departure_date || '');
                setValue('departure_ending_balance', r.departure_ending_balance || '');

                for (let i = 1; i <= 10; i++) {
                    const row = r[`row_${i}`] || {};
                    setValue(`week_start_${i}`, row.week_start || '');
                    setValue(`previous_balance_${i}`, row.previous_balance || '');
                    setValue(`ees_due_${i}`, row.ees_due || '');
                    setValue(`fines_other_${i}`, row.fines_other || '');
                    setValue(`approved_receipts_${i}`, row.approved_receipts || '');
                    setValue(`total_${i}`, row.total || '');
                    setValue(`amount_paid_${i}`, row.amount_paid || '');
                    setValue(`ending_balance_${i}`, row.ending_balance || '');
                    setValue(`notes_${i}`, row.notes || '');
                }

                recalcAllRows();
                setStatus('Loaded');
            } catch (error) {
                setStatus('Load error');
            }
        }

        historySelect.addEventListener('change', () => {
            if (historySelect.value) {
                loadRecord(historySelect.value);
            }
        });

        printBtn.addEventListener('click', () => {
            window.print();
        });

        recalcAllRows();
    </script>
</body>
</html>
