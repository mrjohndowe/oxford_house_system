<?php
/**
 * Oxford House Chore List Coordinator
 * Single-file PHP app
 * - Closely matches uploaded chore list sheet
 * - Auto-saves to MySQL while typing
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals by week and grand total
 */
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const ROW_COUNT = 10;
const WEEK_COUNT = 8;

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

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS oxford_chore_lists (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        house_name VARCHAR(255) NOT NULL DEFAULT '',
        title VARCHAR(255) NOT NULL DEFAULT 'CHORE LIST',
        week1_start VARCHAR(20) NOT NULL DEFAULT '',
        week2_start VARCHAR(20) NOT NULL DEFAULT '',
        week3_start VARCHAR(20) NOT NULL DEFAULT '',
        week4_start VARCHAR(20) NOT NULL DEFAULT '',
        week5_start VARCHAR(20) NOT NULL DEFAULT '',
        week6_start VARCHAR(20) NOT NULL DEFAULT '',
        week7_start VARCHAR(20) NOT NULL DEFAULT '',
        week8_start VARCHAR(20) NOT NULL DEFAULT '',
        form_json LONGTEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_house_name (house_name),
        INDEX idx_updated_at (updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function emptyFormData(): array
{
    $data = [
        'house_name' => '',
        'title' => 'CHORE LIST',
        'weeks' => [],
    ];

    for ($w = 1; $w <= WEEK_COUNT; $w++) {
        $week = [
            'start_date' => '',
            'rows' => [],
        ];

        for ($r = 0; $r < ROW_COUNT; $r++) {
            $week['rows'][] = [
                'activity' => '',
                'name' => '',
                'member_initials' => '',
                'coordinator_initials' => '',
            ];
        }

        $data['weeks'][$w] = $week;
    }

    return $data;
}

function normalizePostedForm(array $source): array
{
    $data = emptyFormData();
    $data['house_name'] = trim((string)($source['house_name'] ?? ''));
    $data['title'] = trim((string)($source['title'] ?? 'CHORE LIST')) ?: 'CHORE LIST';

    for ($w = 1; $w <= WEEK_COUNT; $w++) {
        $weekPosted = $source['weeks'][$w] ?? [];
        $data['weeks'][$w]['start_date'] = trim((string)($weekPosted['start_date'] ?? ''));

        for ($r = 0; $r < ROW_COUNT; $r++) {
            $rowPosted = $weekPosted['rows'][$r] ?? [];
            $data['weeks'][$w]['rows'][$r] = [
                'activity' => trim((string)($rowPosted['activity'] ?? '')),
                'name' => trim((string)($rowPosted['name'] ?? '')),
                'member_initials' => strtoupper(trim((string)($rowPosted['member_initials'] ?? ''))),
                'coordinator_initials' => strtoupper(trim((string)($rowPosted['coordinator_initials'] ?? ''))),
            ];
        }
    }

    return $data;
}

function decodeRecord(array $row): array
{
    $data = emptyFormData();
    $json = json_decode((string)$row['form_json'], true);
    if (is_array($json)) {
        $data = array_replace_recursive($data, $json);
    }

    $data['house_name'] = (string)($row['house_name'] ?? $data['house_name']);
    $data['title'] = (string)($row['title'] ?? $data['title']);

    for ($w = 1; $w <= WEEK_COUNT; $w++) {
        $col = 'week' . $w . '_start';
        if (isset($row[$col])) {
            $data['weeks'][$w]['start_date'] = (string)$row[$col];
        }
    }

    return $data;
}

function getWeekCompletedCount(array $week): int
{
    $count = 0;
    foreach ($week['rows'] as $row) {
        $hasActivity = trim((string)($row['activity'] ?? '')) !== '';
        $hasName = trim((string)($row['name'] ?? '')) !== '';
        $hasMemberInitials = trim((string)($row['member_initials'] ?? '')) !== '';
        $hasCoordInitials = trim((string)($row['coordinator_initials'] ?? '')) !== '';

        if ($hasActivity && $hasName && $hasMemberInitials && $hasCoordInitials) {
            $count++;
        }
    }
    return $count;
}

function getGrandCompletedTotal(array $data): int
{
    $total = 0;
    for ($w = 1; $w <= WEEK_COUNT; $w++) {
        $total += getWeekCompletedCount($data['weeks'][$w]);
    }
    return $total;
}

function saveRecord(PDO $pdo, ?int $id, array $data): int
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Failed to encode form data.');
    }

    $params = [
        ':house_name' => $data['house_name'],
        ':title' => $data['title'],
        ':week1_start' => $data['weeks'][1]['start_date'],
        ':week2_start' => $data['weeks'][2]['start_date'],
        ':week3_start' => $data['weeks'][3]['start_date'],
        ':week4_start' => $data['weeks'][4]['start_date'],
        ':week5_start' => $data['weeks'][5]['start_date'],
        ':week6_start' => $data['weeks'][6]['start_date'],
        ':week7_start' => $data['weeks'][7]['start_date'],
        ':week8_start' => $data['weeks'][8]['start_date'],
        ':form_json' => $json,
    ];

    if ($id !== null && $id > 0) {
        $params[':id'] = $id;
        $stmt = $pdo->prepare(
            "UPDATE oxford_chore_lists SET
                house_name = :house_name,
                title = :title,
                week1_start = :week1_start,
                week2_start = :week2_start,
                week3_start = :week3_start,
                week4_start = :week4_start,
                week5_start = :week5_start,
                week6_start = :week6_start,
                week7_start = :week7_start,
                week8_start = :week8_start,
                form_json = :form_json
             WHERE id = :id"
        );
        $stmt->execute($params);
        return $id;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO oxford_chore_lists (
            house_name, title,
            week1_start, week2_start, week3_start, week4_start,
            week5_start, week6_start, week7_start, week8_start,
            form_json
        ) VALUES (
            :house_name, :title,
            :week1_start, :week2_start, :week3_start, :week4_start,
            :week5_start, :week6_start, :week7_start, :week8_start,
            :form_json
        )"
    );
    $stmt->execute($params);
    return (int)$pdo->lastInsertId();
}

/* =========================
   AJAX AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'autosave') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $recordId = isset($_POST['record_id']) && $_POST['record_id'] !== '' ? (int)$_POST['record_id'] : null;
        $formData = normalizePostedForm($_POST);
        $savedId = saveRecord($pdo, $recordId, $formData);

        $weekTotals = [];
        for ($w = 1; $w <= WEEK_COUNT; $w++) {
            $weekTotals[$w] = getWeekCompletedCount($formData['weeks'][$w]);
        }

        echo json_encode([
            'ok' => true,
            'record_id' => $savedId,
            'updated_at' => date('Y-m-d H:i:s'),
            'week_totals' => $weekTotals,
            'grand_total' => getGrandCompletedTotal($formData),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => $e->getMessage(),
        ]);
    }
    exit;
}

/* =========================
   LOAD SELECTED RECORD
========================= */
$currentId = isset($_GET['load']) ? (int)$_GET['load'] : 0;
$formData = emptyFormData();
$statusMessage = '';

if ($currentId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM oxford_chore_lists WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $currentId]);
    $row = $stmt->fetch();
    if ($row) {
        $formData = decodeRecord($row);
        $statusMessage = 'Loaded saved record #' . $currentId . '.';
    }
}

$historyRows = $pdo->query(
    'SELECT id, house_name, updated_at, week1_start, week2_start, week3_start, week4_start, week5_start, week6_start, week7_start, week8_start
     FROM oxford_chore_lists
     ORDER BY updated_at DESC, id DESC'
)->fetchAll();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chore List Coordinator</title>
    <style>
        :root {
            --border: #333;
            --thin: 1px;
            --thick: 2px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #efefef;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
        }
        .toolbar {
            width: 100%;
            max-width: 1200px;
            margin: 14px auto 0;
            padding: 10px 14px;
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
            font-size: 13px;
            font-weight: 700;
        }
        select, button {
            height: 36px;
            border: 1px solid #999;
            background: #fff;
            padding: 0 10px;
            font-size: 14px;
        }
        button {
            cursor: pointer;
            font-weight: 700;
        }
        .status {
            font-size: 13px;
            font-weight: 700;
            color: #1e5e2f;
        }
        .sheet-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 10px 14px 30px;
        }
        .sheet {
            width: 100%;
            max-width: 1050px;
            background: #fff;
            padding: 18px 18px 16px;
            box-shadow: 0 1px 10px rgba(0,0,0,.12);
        }
        .header {
            display: grid;
            grid-template-columns: 96px 1fr;
            gap: 12px;
            align-items: start;
            margin-bottom: 6px;
        }
        .logo-box {
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .title-wrap {
            padding-top: 4px;
        }
        .oxford-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }
        .oxford-line span {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: .4px;
        }
        .house-input-line {
            flex: 1;
            border-bottom: 2px solid #555;
            min-height: 25px;
            display: flex;
            align-items: flex-end;
        }
        .house-input-line input {
            width: 100%;
            border: 0;
            outline: none;
            font-size: 22px;
            font-weight: 700;
            padding: 0 3px 1px;
            background: transparent;
        }
        .main-title {
            text-align: center;
            font-size: 28px;
            font-weight: 900;
            margin: 0;
            line-height: 1;
        }
        .weeks-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 10px;
        }
        .week-box {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .week-head {
            text-align: center;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.05;
            min-height: 28px;
        }
        .week-head small {
            display: block;
            font-size: 11px;
            font-weight: 900;
            margin-top: 2px;
        }
        table.week-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: var(--thick) solid var(--border);
        }
        .week-table th,
        .week-table td {
            border: var(--thin) solid var(--border);
            padding: 0;
            height: 26px;
            vertical-align: middle;
        }
        .week-table th {
            height: 24px;
            font-size: 11px;
            font-weight: 900;
            background: #fff;
        }
        .activity-col { width: 48%; }
        .name-col { width: 30%; }
        .init-col { width: 11%; }
        .week-table input {
            width: 100%;
            height: 100%;
            border: 0;
            outline: none;
            padding: 4px 5px;
            font-size: 12px;
            background: transparent;
            text-transform: uppercase;
        }
        .week-table input.activity-field,
        .week-table input.name-field {
            text-transform: none;
        }
        .start-line {
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 8px;
            margin: 7px 0 10px;
            min-height: 26px;
        }
        .start-line label {
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }
        .date-inputs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .date-underline {
            border-bottom: 2px solid #555;
            min-height: 24px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .date-underline input {
            width: 100%;
            border: 0;
            outline: none;
            text-align: center;
            font-size: 13px;
            padding: 0 2px 1px;
            background: transparent;
        }
        .rules {
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.2;
            padding-left: 18px;
        }
        .rules li { margin: 3px 0; }
        .totals-bar {
            margin-top: 8px;
            border-top: 2px solid #333;
            padding-top: 8px;
            display: grid;
            grid-template-columns: repeat(8, 1fr) 1.3fr;
            gap: 8px;
            align-items: center;
        }
        .total-card {
            border: 1px solid #444;
            min-height: 48px;
            padding: 4px 6px;
            text-align: center;
        }
        .total-card .label {
            font-size: 11px;
            font-weight: 900;
        }
        .total-card .value {
            font-size: 20px;
            font-weight: 900;
            margin-top: 2px;
        }
        .print-only { display: none; }

        @media print {
            body {
                background: #fff;
            }
            .toolbar {
                display: none !important;
            }
            .sheet-wrap {
                padding: 0;
            }
            .sheet {
                box-shadow: none;
                max-width: none;
                width: 100%;
                padding: 12px;
            }
            .week-table input,
            .date-underline input,
            .house-input-line input {
                color: #000;
            }
            .print-only { display: block; }
        }

        @media (max-width: 980px) {
            .weeks-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .date-inputs {
                grid-template-columns: repeat(2, 1fr);
            }
            .totals-bar {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-left">
            <label for="history_id">Saved Records</label>
            <select id="history_id" onchange="if(this.value){ window.location='?load='+this.value; }">
                <option value="">Select saved record...</option>
                <?php foreach ($historyRows as $item): ?>
                    <option value="<?= (int)$item['id'] ?>" <?= $currentId === (int)$item['id'] ? 'selected' : '' ?>>
                        #<?= (int)$item['id'] ?> - <?= h($item['house_name'] ?: 'No House Name') ?> - <?= h($item['updated_at']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="newSheet()">New Sheet</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
        <div class="toolbar-right">
            <span class="status" id="saveStatus"><?= h($statusMessage ?: 'Ready') ?></span>
        </div>
    </div>

    <div class="sheet-wrap">
        <form id="choreForm" class="sheet" autocomplete="off">
            <input type="hidden" name="action" value="autosave">
            <input type="hidden" name="record_id" id="record_id" value="<?= $currentId > 0 ? $currentId : '' ?>">
            <input type="hidden" name="title" value="<?= h($formData['title']) ?>">

            <div class="header">
                <div class="logo-box">
                    <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo">
                </div>
                <div class="title-wrap">
                    <div class="oxford-line">
                        <span>OXFORD HOUSE -</span>
                        <div class="house-input-line">
                            <input type="text" name="house_name" value="<?= h($formData['house_name']) ?>">
                        </div>
                    </div>
                    <h1 class="main-title">CHORE LIST</h1>
                </div>
            </div>

            <?php for ($section = 0; $section < 2; $section++): ?>
                <div class="weeks-grid">
                    <?php for ($offset = 1; $offset <= 4; $offset++):
                        $weekNum = ($section * 4) + $offset;
                        $week = $formData['weeks'][$weekNum];
                    ?>
                        <div class="week-box">
                            <div class="week-head">
                                WEEK #<?= $weekNum ?>
                                <small><?= $weekNum === 1 || $weekNum === 5 ? 'NAME / INITIALS' : 'NAME / INITIALS' ?></small>
                            </div>
                            <table class="week-table">
                                <colgroup>
                                    <col class="activity-col">
                                    <col class="name-col">
                                    <col class="init-col">
                                    <col class="init-col">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>ACTIVITY</th>
                                        <th>NAME</th>
                                        <th colspan="2">INITIALS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($week['rows'] as $rowIndex => $row): ?>
                                    <tr>
                                        <td><input class="activity-field" type="text" name="weeks[<?= $weekNum ?>][rows][<?= $rowIndex ?>][activity]" value="<?= h($row['activity']) ?>"></td>
                                        <td><input class="name-field" type="text" name="weeks[<?= $weekNum ?>][rows][<?= $rowIndex ?>][name]" value="<?= h($row['name']) ?>"></td>
                                        <td><input maxlength="4" type="text" name="weeks[<?= $weekNum ?>][rows][<?= $rowIndex ?>][member_initials]" value="<?= h($row['member_initials']) ?>"></td>
                                        <td><input maxlength="4" type="text" name="weeks[<?= $weekNum ?>][rows][<?= $rowIndex ?>][coordinator_initials]" value="<?= h($row['coordinator_initials']) ?>"></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="start-line">
                    <label>WEEK START DATE:</label>
                    <div class="date-inputs">
                        <?php for ($offset = 1; $offset <= 4; $offset++):
                            $weekNum = ($section * 4) + $offset;
                        ?>
                            <div class="date-underline">
                                <input type="text" name="weeks[<?= $weekNum ?>][start_date]" value="<?= h($formData['weeks'][$weekNum]['start_date']) ?>" placeholder="____ / ____ / ____">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endfor; ?>

            <ul class="rules">
                <li>Chores are divided equally and rotate weekly.</li>
                <li>Members are required to keep their assigned area clean all week.</li>
                <li>Completed chores require the initials of the assigned Member and the Coordinator.</li>
                <li>Incomplete and/or neglected chores may result in a fine or penalty.</li>
                <li>All fines or penalties must be approved by a majority vote in a house meeting.</li>
                <li>All approved fines need to be paid at the next House Meeting.</li>
                <li>All Members are responsible to keep their own room clean.</li>
                <li>Fines or penalties may be given for dirty rooms.</li>
                <li>All members are required to check-in daily with the person assigned to do check-ins.</li>
            </ul>

            <div class="totals-bar">
                <?php for ($w = 1; $w <= WEEK_COUNT; $w++): ?>
                    <div class="total-card">
                        <div class="label">WEEK #<?= $w ?></div>
                        <div class="value" id="weekTotal<?= $w ?>"><?= getWeekCompletedCount($formData['weeks'][$w]) ?></div>
                    </div>
                <?php endfor; ?>
                <div class="total-card">
                    <div class="label">GRAND TOTAL</div>
                    <div class="value" id="grandTotal"><?= getGrandCompletedTotal($formData) ?></div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('choreForm');
        const saveStatus = document.getElementById('saveStatus');
        const recordIdField = document.getElementById('record_id');
        let autosaveTimer = null;
        let saving = false;

        function setStatus(text, isError = false) {
            saveStatus.textContent = text;
            saveStatus.style.color = isError ? '#a32020' : '#1e5e2f';
        }

        function computeTotalsFromDom() {
            let grand = 0;
            for (let week = 1; week <= 8; week++) {
                let total = 0;
                for (let row = 0; row < <?= ROW_COUNT ?>; row++) {
                    const a = form.querySelector(`[name="weeks[${week}][rows][${row}][activity]"]`)?.value.trim() || '';
                    const n = form.querySelector(`[name="weeks[${week}][rows][${row}][name]"]`)?.value.trim() || '';
                    const mi = form.querySelector(`[name="weeks[${week}][rows][${row}][member_initials]"]`)?.value.trim() || '';
                    const ci = form.querySelector(`[name="weeks[${week}][rows][${row}][coordinator_initials]"]`)?.value.trim() || '';
                    if (a && n && mi && ci) total++;
                }
                grand += total;
                const target = document.getElementById('weekTotal' + week);
                if (target) target.textContent = String(total);
            }
            const grandEl = document.getElementById('grandTotal');
            if (grandEl) grandEl.textContent = String(grand);
        }

        async function autosave() {
            if (saving) return;
            saving = true;
            setStatus('Saving...');

            try {
                const fd = new FormData(form);
                const response = await fetch('', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Save failed.');
                }
                if (data.record_id) {
                    recordIdField.value = data.record_id;
                }
                if (data.week_totals) {
                    for (const week in data.week_totals) {
                        const el = document.getElementById('weekTotal' + week);
                        if (el) el.textContent = data.week_totals[week];
                    }
                }
                if (typeof data.grand_total !== 'undefined') {
                    const grandEl = document.getElementById('grandTotal');
                    if (grandEl) grandEl.textContent = data.grand_total;
                }
                setStatus('Saved ' + data.updated_at);
            } catch (error) {
                setStatus(error.message || 'Save failed.', true);
            } finally {
                saving = false;
            }
        }

        function queueAutosave() {
            computeTotalsFromDom();
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(autosave, 500);
        }

        function newSheet() {
            window.location = window.location.pathname;
        }

        form.addEventListener('input', queueAutosave);
        form.addEventListener('change', queueAutosave);

        computeTotalsFromDom();
    </script>
</body>
</html>
