<?php
/**
 * Nightly Kitchen Schedule (Optional)
 * Single-file PHP app
 * - Closely matches uploaded Oxford House sheet
 * - Auto-saves to MySQL
 * - Reload/edit prior records
 * - Print button
 */
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const BLOCK_COUNT = 9;
const DAYS = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $payload): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function postedJson(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function normalizeBlock(array $input): array
{
    $rows = [];
    for ($r = 0; $r < 7; $r++) {
        $row = $input['rows'][$r] ?? [];
        $rows[] = [
            'name' => trim((string)($row['name'] ?? '')),
            'initials_1' => strtoupper(substr(trim((string)($row['initials_1'] ?? '')), 0, 4)),
            'initials_2' => strtoupper(substr(trim((string)($row['initials_2'] ?? '')), 0, 4)),
        ];
    }

    return [
        'week_of' => trim((string)($input['week_of'] ?? '')),
        'rows' => $rows,
    ];
}

function defaultFormData(): array
{
    $blocks = [];
    for ($i = 0; $i < BLOCK_COUNT; $i++) {
        $rows = [];
        for ($r = 0; $r < 7; $r++) {
            $rows[] = ['name' => '', 'initials_1' => '', 'initials_2' => ''];
        }
        $blocks[] = ['week_of' => '', 'rows' => $rows];
    }

    return [
        'id' => 0,
        'house_name' => '',
        'trash_to_curb_on' => '',
        'duties' => array_fill(0, 8, ''),
        'blocks' => $blocks,
    ];
}

function normalizeFormData(array $input): array
{
    $data = defaultFormData();
    $data['id'] = (int)($input['id'] ?? 0);
    $data['house_name'] = trim((string)($input['house_name'] ?? ''));
    $data['trash_to_curb_on'] = trim((string)($input['trash_to_curb_on'] ?? ''));

    $duties = $input['duties'] ?? [];
    for ($i = 0; $i < 8; $i++) {
        $data['duties'][$i] = trim((string)($duties[$i] ?? ''));
    }

    $blocks = $input['blocks'] ?? [];
    for ($i = 0; $i < BLOCK_COUNT; $i++) {
        $data['blocks'][$i] = normalizeBlock($blocks[$i] ?? []);
    }

    return $data;
}

function calculateTotals(array $data): array
{
    $weekly = [];
    $grandTotal = 0;

    foreach ($data['blocks'] as $index => $block) {
        $count = 0;
        foreach ($block['rows'] as $row) {
            if ($row['initials_1'] !== '') {
                $count++;
            }
            if ($row['initials_2'] !== '') {
                $count++;
            }
        }
        $weekly[$index] = $count;
        $grandTotal += $count;
    }

    return [
        'weekly' => $weekly,
        'grand_total' => $grandTotal,
    ];
}

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
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS nightly_kitchen_schedules (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        house_name VARCHAR(255) NOT NULL DEFAULT '',
        trash_to_curb_on VARCHAR(255) NOT NULL DEFAULT '',
        schedule_data LONGTEXT NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_house_name (house_name),
        INDEX idx_updated_at (updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

/* =========================
   AJAX ACTIONS
========================= */
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'autosave') {
    $payload = postedJson();
    $data = normalizeFormData($payload);

    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        jsonResponse(['ok' => false, 'message' => 'Unable to encode form data.']);
    }

    if ($data['id'] > 0) {
        $stmt = $pdo->prepare(
            'UPDATE nightly_kitchen_schedules
             SET house_name = :house_name,
                 trash_to_curb_on = :trash_to_curb_on,
                 schedule_data = :schedule_data
             WHERE id = :id'
        );
        $stmt->execute([
            ':house_name' => $data['house_name'],
            ':trash_to_curb_on' => $data['trash_to_curb_on'],
            ':schedule_data' => $json,
            ':id' => $data['id'],
        ]);
        $id = $data['id'];
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO nightly_kitchen_schedules (house_name, trash_to_curb_on, schedule_data)
             VALUES (:house_name, :trash_to_curb_on, :schedule_data)'
        );
        $stmt->execute([
            ':house_name' => $data['house_name'],
            ':trash_to_curb_on' => $data['trash_to_curb_on'],
            ':schedule_data' => $json,
        ]);
        $id = (int)$pdo->lastInsertId();
    }

    $data['id'] = $id;
    jsonResponse([
        'ok' => true,
        'id' => $id,
        'totals' => calculateTotals($data),
        'saved_at' => date('Y-m-d H:i:s'),
    ]);
}

if ($action === 'load') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['ok' => false, 'message' => 'Invalid record ID.']);
    }

    $stmt = $pdo->prepare('SELECT * FROM nightly_kitchen_schedules WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        jsonResponse(['ok' => false, 'message' => 'Record not found.']);
    }

    $data = json_decode((string)$record['schedule_data'], true);
    $data = is_array($data) ? normalizeFormData($data) : defaultFormData();
    $data['id'] = (int)$record['id'];
    $data['house_name'] = (string)$record['house_name'];
    $data['trash_to_curb_on'] = (string)$record['trash_to_curb_on'];

    jsonResponse([
        'ok' => true,
        'record' => $data,
        'totals' => calculateTotals($data),
    ]);
}

/* =========================
   INITIAL PAGE DATA
========================= */
$formData = defaultFormData();
$totals = calculateTotals($formData);

$history = $pdo->query(
    'SELECT id, house_name, trash_to_curb_on, updated_at
     FROM nightly_kitchen_schedules
     ORDER BY updated_at DESC, id DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nightly Kitchen Schedule (Optional)</title>
    <style>
        :root {
            --paper-width: 816px;
            --ink: #222;
            --line: #444;
            --muted: #666;
            --bg: #ececec;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #dcdcdc;
            color: var(--ink);
        }

        .screen-toolbar {
            max-width: calc(var(--paper-width) + 40px);
            margin: 18px auto 0;
            padding: 0 20px;
        }

        .toolbar-card {
            background: #fff;
            border: 1px solid #d5d5d5;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 6px 20px rgba(0,0,0,.06);
        }

        .toolbar-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .toolbar-card select,
        .toolbar-card button {
            height: 38px;
            border-radius: 8px;
            border: 1px solid #b9b9b9;
            padding: 0 12px;
            font-size: 14px;
        }

        .toolbar-card button {
            background: #1c1c1c;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
        }

        .toolbar-card button.secondary {
            background: #f4f4f4;
            color: #222;
        }

        .save-state {
            font-size: 13px;
            color: var(--muted);
            min-width: 190px;
            text-align: right;
        }

        .paper {
            width: var(--paper-width);
            margin: 18px auto 24px;
            background: var(--bg);
            padding: 18px 26px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,.10);
        }

        .header {
            display: grid;
            grid-template-columns: 98px 1fr;
            align-items: start;
            column-gap: 14px;
        }

        .logo {
            width: 92px;
            height: auto;
            display: block;
        }

        .title-wrap {
            padding-top: 2px;
        }

        .oxford-line {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 24px;
            line-height: 1;
            letter-spacing: .2px;
        }

        .oxford-line .fill-line {
            flex: 1;
            border-bottom: 2px solid var(--line);
            height: 19px;
            position: relative;
        }

        .house-input {
            position: absolute;
            inset: -2px 0 0 0;
            width: 100%;
            border: 0;
            background: transparent;
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            padding: 0 2px;
            outline: none;
        }

        .main-title {
            text-align: left;
            font-weight: 900;
            font-size: 30px;
            letter-spacing: .4px;
            margin: 4px 0 0;
        }

        .trash-line {
            margin: 22px 0 18px;
            text-align: center;
            font-size: 18px;
            font-weight: 800;
        }

        .trash-input {
            width: 305px;
            max-width: 100%;
            border: 0;
            border-bottom: 2px solid var(--line);
            background: transparent;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            outline: none;
            padding: 0 6px 2px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 26px 20px;
            margin-top: 6px;
        }

        .week-block {
            position: relative;
        }

        .week-label {
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 2px;
            letter-spacing: .2px;
        }

        .week-inline {
            display: inline-flex;
            align-items: end;
            justify-content: center;
            gap: 3px;
        }

        .week-input {
            width: 120px;
            border: 0;
            border-bottom: 2px solid var(--line);
            background: transparent;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            outline: none;
            padding: 0 2px 1px;
        }

        .week-header {
            display: grid;
            grid-template-columns: 42px 1fr 56px;
            align-items: end;
            margin-bottom: 2px;
        }

        .day-gap { height: 1px; }

        .header-name,
        .header-initials {
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
        }

        .header-initials {
            grid-column: 3;
        }

        .week-table {
            display: grid;
            grid-template-columns: 42px 1fr;
            align-items: stretch;
        }

        .days {
            display: grid;
            grid-template-rows: repeat(7, 31px);
            align-items: center;
            padding-right: 6px;
            font-size: 14px;
            font-weight: 800;
        }

        .days div {
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .table-box {
            border: 2px solid var(--line);
            background: transparent;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 28px 28px;
            min-height: 31px;
        }

        .row + .row { border-top: 2px solid var(--line); }
        .cell + .cell { border-left: 2px solid var(--line); }

        .cell input {
            width: 100%;
            height: 29px;
            border: 0;
            background: transparent;
            outline: none;
            padding: 4px 6px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .cell.name input {
            text-transform: none;
            font-weight: 600;
        }

        .cell.initials input {
            text-align: center;
            padding: 4px 1px;
            font-size: 12px;
        }

        .week-total {
            position: absolute;
            right: 2px;
            bottom: -18px;
            font-size: 11px;
            color: var(--muted);
            font-weight: 700;
        }

        .duties-title {
            margin: 18px 0 8px;
            text-align: center;
            font-weight: 900;
            font-size: 20px;
            letter-spacing: .4px;
        }

        .duties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 28px;
            padding: 0 28px;
        }

        .duty-row {
            display: grid;
            grid-template-columns: 24px 1fr;
            align-items: center;
            column-gap: 5px;
            font-size: 15px;
            font-weight: 700;
        }

        .duty-row span {
            text-align: right;
        }

        .duty-row input {
            border: 0;
            border-bottom: 2px solid var(--line);
            background: transparent;
            height: 26px;
            outline: none;
            font-size: 14px;
            padding: 2px 4px;
            font-weight: 600;
        }

        .totals-bar {
            margin-top: 14px;
            text-align: center;
            font-size: 14px;
            font-weight: 800;
        }

        @media print {
            body {
                background: #fff;
            }
            .screen-toolbar {
                display: none !important;
            }
            .paper {
                margin: 0 auto;
                box-shadow: none;
                width: 100%;
                max-width: 816px;
                background: #ececec;
            }
            @page {
                size: letter portrait;
                margin: 0.3in;
            }
        }
    </style>
</head>
<body>
    <div class="screen-toolbar">
        <div class="toolbar-card">
            <div class="toolbar-group">
                <select id="historySelect">
                    <option value="">Load saved record...</option>
                    <?php foreach ($history as $item): ?>
                        <option value="<?= (int)$item['id'] ?>">
                            <?= h($item['house_name'] !== '' ? $item['house_name'] : 'Untitled House') ?>
                            — <?= h((string)$item['updated_at']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="secondary" id="newRecordBtn">New Sheet</button>
                <button type="button" id="printBtn">Print</button>
            </div>
            <div class="save-state" id="saveState">Ready</div>
        </div>
    </div>

    <div class="paper">
        <form id="scheduleForm" autocomplete="off">
            <input type="hidden" name="id" id="record_id" value="0">

            <div class="header">
                <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo" class="logo">
                <div class="title-wrap">
                    <div class="oxford-line">
                        <span>OXFORD HOUSE -</span>
                        <span class="fill-line">
                            <input type="text" id="house_name" name="house_name" class="house-input" value="<?= h($formData['house_name']) ?>">
                        </span>
                    </div>
                    <div class="main-title">NIGHTLY KITCHEN SCHEDULE</div>
                </div>
            </div>

            <div class="trash-line">
                TRASH TO CURB ON
                <input type="text" id="trash_to_curb_on" name="trash_to_curb_on" class="trash-input" value="<?= h($formData['trash_to_curb_on']) ?>">
            </div>

            <div class="grid">
                <?php for ($b = 0; $b < BLOCK_COUNT; $b++): ?>
                    <div class="week-block" data-week-index="<?= $b ?>">
                        <div class="week-label">
                            <span class="week-inline">WEEK OF <input type="text" class="week-input autosave-field" name="blocks[<?= $b ?>][week_of]" value=""></span>
                        </div>
                        <div class="week-header">
                            <div class="day-gap"></div>
                            <div class="header-name">NAME</div>
                            <div class="header-initials">INITIALS</div>
                        </div>
                        <div class="week-table">
                            <div class="days">
                                <?php foreach (DAYS as $day): ?>
                                    <div><?= h($day) ?></div>
                                <?php endforeach; ?>
                            </div>
                            <div class="table-box">
                                <?php for ($r = 0; $r < 7; $r++): ?>
                                    <div class="row">
                                        <div class="cell name">
                                            <input type="text" class="autosave-field" name="blocks[<?= $b ?>][rows][<?= $r ?>][name]" value="">
                                        </div>
                                        <div class="cell initials">
                                            <input type="text" maxlength="4" class="autosave-field initials-field" name="blocks[<?= $b ?>][rows][<?= $r ?>][initials_1]" value="">
                                        </div>
                                        <div class="cell initials">
                                            <input type="text" maxlength="4" class="autosave-field initials-field" name="blocks[<?= $b ?>][rows][<?= $r ?>][initials_2]" value="">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="week-total">Total initials: <span class="week-total-value">0</span></div>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="duties-title">DUTIES</div>
            <div class="duties-grid">
                <?php for ($i = 0; $i < 8; $i++): ?>
                    <label class="duty-row">
                        <span><?= $i + 1 ?> -</span>
                        <input type="text" class="autosave-field" name="duties[<?= $i ?>]" value="">
                    </label>
                <?php endfor; ?>
            </div>

            <div class="totals-bar">TOTAL INITIALS RECORDED: <span id="grandTotal">0</span></div>
        </form>
    </div>

    <script>
        const form = document.getElementById('scheduleForm');
        const saveState = document.getElementById('saveState');
        const historySelect = document.getElementById('historySelect');
        const newRecordBtn = document.getElementById('newRecordBtn');
        const printBtn = document.getElementById('printBtn');
        const recordIdField = document.getElementById('record_id');
        const grandTotal = document.getElementById('grandTotal');
        let autosaveTimer = null;
        let isLoading = false;

        function setSaveState(text) {
            saveState.textContent = text;
        }

        function getFormData() {
            const data = {
                id: parseInt(recordIdField.value || '0', 10) || 0,
                house_name: document.getElementById('house_name').value || '',
                trash_to_curb_on: document.getElementById('trash_to_curb_on').value || '',
                duties: [],
                blocks: []
            };

            for (let i = 0; i < 8; i++) {
                const duty = form.querySelector(`[name="duties[${i}]"]`);
                data.duties.push(duty ? duty.value : '');
            }

            for (let b = 0; b < 9; b++) {
                const block = {
                    week_of: (form.querySelector(`[name="blocks[${b}][week_of]"]`) || {}).value || '',
                    rows: []
                };

                for (let r = 0; r < 7; r++) {
                    block.rows.push({
                        name: (form.querySelector(`[name="blocks[${b}][rows][${r}][name]"]`) || {}).value || '',
                        initials_1: (form.querySelector(`[name="blocks[${b}][rows][${r}][initials_1]"]`) || {}).value || '',
                        initials_2: (form.querySelector(`[name="blocks[${b}][rows][${r}][initials_2]"]`) || {}).value || ''
                    });
                }

                data.blocks.push(block);
            }

            return data;
        }

        function fillForm(data) {
            isLoading = true;
            recordIdField.value = data.id || 0;
            document.getElementById('house_name').value = data.house_name || '';
            document.getElementById('trash_to_curb_on').value = data.trash_to_curb_on || '';

            for (let i = 0; i < 8; i++) {
                const duty = form.querySelector(`[name="duties[${i}]"]`);
                if (duty) duty.value = (data.duties && data.duties[i]) ? data.duties[i] : '';
            }

            for (let b = 0; b < 9; b++) {
                const block = (data.blocks && data.blocks[b]) ? data.blocks[b] : {week_of: '', rows: []};
                const week = form.querySelector(`[name="blocks[${b}][week_of]"]`);
                if (week) week.value = block.week_of || '';

                for (let r = 0; r < 7; r++) {
                    const row = (block.rows && block.rows[r]) ? block.rows[r] : {};
                    const name = form.querySelector(`[name="blocks[${b}][rows][${r}][name]"]`);
                    const i1 = form.querySelector(`[name="blocks[${b}][rows][${r}][initials_1]"]`);
                    const i2 = form.querySelector(`[name="blocks[${b}][rows][${r}][initials_2]"]`);
                    if (name) name.value = row.name || '';
                    if (i1) i1.value = row.initials_1 || '';
                    if (i2) i2.value = row.initials_2 || '';
                }
            }

            updateLocalTotals();
            isLoading = false;
        }

        function updateLocalTotals(serverTotals = null) {
            let grand = 0;
            document.querySelectorAll('.week-block').forEach((block) => {
                const idx = parseInt(block.getAttribute('data-week-index') || '0', 10);
                let weekCount = 0;
                block.querySelectorAll('.initials-field').forEach((input) => {
                    if ((input.value || '').trim() !== '') weekCount++;
                });
                if (serverTotals && serverTotals.weekly && typeof serverTotals.weekly[idx] !== 'undefined') {
                    weekCount = parseInt(serverTotals.weekly[idx], 10) || 0;
                }
                block.querySelector('.week-total-value').textContent = weekCount;
                grand += weekCount;
            });
            if (serverTotals && typeof serverTotals.grand_total !== 'undefined') {
                grand = parseInt(serverTotals.grand_total, 10) || 0;
            }
            grandTotal.textContent = grand;
        }

        async function autosave() {
            if (isLoading) return;
            const payload = getFormData();
            setSaveState('Saving...');

            try {
                const response = await fetch('?action=autosave', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (!result.ok) {
                    setSaveState(result.message || 'Save failed');
                    return;
                }

                if (result.id) {
                    recordIdField.value = result.id;
                    if (![...historySelect.options].some(opt => opt.value === String(result.id))) {
                        const option = document.createElement('option');
                        option.value = String(result.id);
                        option.textContent = `${payload.house_name || 'Untitled House'} — ${result.saved_at || 'Saved'}`;
                        historySelect.insertBefore(option, historySelect.options[1] || null);
                    }
                    historySelect.value = String(result.id);
                }

                updateLocalTotals(result.totals || null);
                setSaveState('Saved ' + (result.saved_at || ''));
            } catch (error) {
                console.error(error);
                setSaveState('Save failed');
            }
        }

        function queueAutosave() {
            if (isLoading) return;
            updateLocalTotals();
            setSaveState('Unsaved changes...');
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(autosave, 500);
        }

        async function loadRecord(id) {
            if (!id) return;
            setSaveState('Loading...');
            try {
                const response = await fetch(`?action=load&id=${encodeURIComponent(id)}`);
                const result = await response.json();
                if (!result.ok) {
                    setSaveState(result.message || 'Load failed');
                    return;
                }
                fillForm(result.record);
                updateLocalTotals(result.totals || null);
                setSaveState('Record loaded');
            } catch (error) {
                console.error(error);
                setSaveState('Load failed');
            }
        }

        document.querySelectorAll('.autosave-field, #house_name, #trash_to_curb_on').forEach((field) => {
            field.addEventListener('input', queueAutosave);
            field.addEventListener('change', queueAutosave);
        });

        document.querySelectorAll('.initials-field').forEach((field) => {
            field.addEventListener('input', () => {
                field.value = field.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 4);
            });
        });

        historySelect.addEventListener('change', () => {
            if (historySelect.value) {
                loadRecord(historySelect.value);
            }
        });

        newRecordBtn.addEventListener('click', () => {
            fillForm({
                id: 0,
                house_name: '',
                trash_to_curb_on: '',
                duties: Array(8).fill(''),
                blocks: Array.from({ length: 9 }, () => ({
                    week_of: '',
                    rows: Array.from({ length: 7 }, () => ({ name: '', initials_1: '', initials_2: '' }))
                }))
            });
            historySelect.value = '';
            setSaveState('New blank sheet');
        });

        printBtn.addEventListener('click', () => window.print());

        updateLocalTotals();
    </script>
</body>
</html>
