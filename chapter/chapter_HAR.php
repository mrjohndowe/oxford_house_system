<?php
declare(strict_types=1);

/**
 * Oxford House Activity Report
 * Single-file PHP app
 * - Fillable layout closely matching provided image
 * - Auto-save to MySQL
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

$rowCount = 14;
$logoPath = '../images/oxford_house_logo.png';

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
   TABLE
========================= */
$pdo->exec("
    CREATE TABLE IF NOT EXISTS chapter_house_activity_reports (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        report_date DATE NULL,
        chapter VARCHAR(100) NOT NULL DEFAULT '',
        rows_json LONGTEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function clean_text(mixed $value): string
{
    return trim((string)$value);
}

function clean_num(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = str_replace([',', '$', ' '], '', $value);
    return is_numeric($value) ? number_format((float)$value, 2, '.', '') : '';
}

function build_empty_rows(int $count): array
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'house_name' => '',
            'president' => '',
            'meeting_time' => '',
            'beds_total' => '',
            'beds_filled' => '',
            'beds_vacant' => '',
            'arrivals_apps' => '',
            'arrivals_accepted' => '',
            'departures_voluntary' => '',
            'departures_relapse' => '',
            'departures_other' => '',
            'departures_total' => '',
            'ws_don' => '',
        ];
    }
    return $rows;
}

/* =========================
   AJAX
========================= */
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'autosave') {
    $id = (int)($_POST['id'] ?? 0);
    $chapter = clean_text($_POST['chapter'] ?? '');
    $report_date = clean_text($_POST['report_date'] ?? '');

    $rows = [];
    for ($i = 0; $i < $rowCount; $i++) {
        $rows[] = [
            'house_name' => clean_text($_POST['house_name'][$i] ?? ''),
            'president' => clean_text($_POST['president'][$i] ?? ''),
            'meeting_time' => clean_text($_POST['meeting_time'][$i] ?? ''),
            'beds_total' => clean_num($_POST['beds_total'][$i] ?? ''),
            'beds_filled' => clean_num($_POST['beds_filled'][$i] ?? ''),
            'beds_vacant' => clean_num($_POST['beds_vacant'][$i] ?? ''),
            'arrivals_apps' => clean_num($_POST['arrivals_apps'][$i] ?? ''),
            'arrivals_accepted' => clean_num($_POST['arrivals_accepted'][$i] ?? ''),
            'departures_voluntary' => clean_num($_POST['departures_voluntary'][$i] ?? ''),
            'departures_relapse' => clean_num($_POST['departures_relapse'][$i] ?? ''),
            'departures_other' => clean_num($_POST['departures_other'][$i] ?? ''),
            'departures_total' => clean_num($_POST['departures_total'][$i] ?? ''),
            'ws_don' => clean_num($_POST['ws_don'][$i] ?? ''),
        ];
    }

    $hasData = $chapter !== '' || $report_date !== '';
    if (!$hasData) {
        foreach ($rows as $row) {
            foreach ($row as $v) {
                if ($v !== '') {
                    $hasData = true;
                    break 2;
                }
            }
        }
    }

    if (!$hasData) {
        json_response([
            'ok' => true,
            'id' => $id,
            'message' => 'Nothing to save yet.'
        ]);
    }

    $rowsJson = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE chapter_house_activity_reports
            SET chapter = :chapter,
                report_date = :report_date,
                rows_json = :rows_json
            WHERE id = :id
        ");
        $stmt->execute([
            ':chapter' => $chapter,
            ':report_date' => $report_date !== '' ? $report_date : null,
            ':rows_json' => $rowsJson,
            ':id' => $id,
        ]);

        json_response([
            'ok' => true,
            'id' => $id,
            'message' => 'Record updated.'
        ]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO chapter_house_activity_reports (chapter, report_date, rows_json)
        VALUES (:chapter, :report_date, :rows_json)
    ");
    $stmt->execute([
        ':chapter' => $chapter,
        ':report_date' => $report_date !== '' ? $report_date : null,
        ':rows_json' => $rowsJson,
    ]);

    json_response([
        'ok' => true,
        'id' => (int)$pdo->lastInsertId(),
        'message' => 'Record saved.'
    ]);
}

if ($action === 'history') {
    $stmt = $pdo->query("
        SELECT id, chapter, report_date, updated_at
        FROM chapter_house_activity_reports
        ORDER BY COALESCE(report_date, '1000-01-01') DESC, updated_at DESC, id DESC
        LIMIT 500
    ");
    $records = $stmt->fetchAll();
    json_response(['ok' => true, 'records' => $records]);
}

if ($action === 'load') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM chapter_house_activity_reports WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        json_response(['ok' => false, 'message' => 'Record not found.'], 404);
    }

    $rows = json_decode((string)$record['rows_json'], true);
    if (!is_array($rows)) {
        $rows = build_empty_rows($rowCount);
    }

    json_response([
        'ok' => true,
        'record' => [
            'id' => (int)$record['id'],
            'chapter' => (string)$record['chapter'],
            'report_date' => $record['report_date'] ?? '',
            'rows' => $rows,
        ]
    ]);
}

/* =========================
   DEFAULT DATA
========================= */
$form = [
    'id' => '',
    'chapter' => '',
    'report_date' => '',
    'rows' => build_empty_rows($rowCount),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Activity Report</title>
    <style>
        :root{
            --paper-w: 1000px;
            --line: #222;
            --text: #111;
            --bg: #e9e9e9;
            --paper: #fff;
        }

        *{
            box-sizing: border-box;
        }

        html, body{
            margin:0;
            padding:0;
            background:var(--bg);
            color:var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        body{
            padding:18px;
        }

        .toolbar{
            width: var(--paper-w);
            margin: 0 auto 12px auto;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            flex-wrap:wrap;
        }

        .toolbar-left,
        .toolbar-right{
            display:flex;
            align-items:center;
            gap:8px;
            flex-wrap:wrap;
        }

        .toolbar select,
        .toolbar button{
            height:36px;
            border:1px solid #888;
            background:#fff;
            padding:0 12px;
            border-radius:4px;
            font-size:14px;
        }

        .status{
            font-size:13px;
            color:#444;
            min-width:120px;
            text-align:right;
        }

        .page{
            width: var(--paper-w);
            margin: 0 auto;
            background: var(--paper);
            box-shadow: 0 0 10px rgba(0,0,0,.14);
            padding: 18px 34px 56px 34px;
        }

        .logo-wrap{
            text-align:center;
            margin-bottom:6px;
        }

        .logo-wrap img{
            width:74px;
            height:auto;
            display:inline-block;
        }

        .topline{
            display:grid;
            grid-template-columns: 1fr auto 1fr;
            align-items:end;
            column-gap:20px;
            margin-bottom:16px;
        }

        .topline .left,
        .topline .right{
            font-size:15px;
            white-space:nowrap;
        }

        .topline .center{
            text-align:center;
            font-size:16px;
            font-weight:700;
            letter-spacing:.2px;
        }

        .line-inline{
            display:inline-flex;
            align-items:flex-end;
            gap:6px;
        }

        .chapter-input{
            width:90px;
            border:0;
            border-bottom:1px solid var(--line);
            outline:0;
            font-size:15px;
            padding:0 2px;
            background:transparent;
        }

        .date-wrap{
            display:inline-flex;
            align-items:flex-end;
            gap:2px;
        }

        .date-part{
            width:34px;
            border:0;
            border-bottom:1px solid var(--line);
            outline:0;
            text-align:center;
            font-size:15px;
            padding:0 1px;
            background:transparent;
        }

        .date-year{
            width:44px;
        }

        table.report{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }

        table.report th,
        table.report td{
            border:1px solid var(--line);
            padding:0;
            vertical-align:middle;
        }

        table.report thead th{
            font-weight:400;
            font-size:12px;
            text-align:center;
            line-height:1.05;
        }

        .main-head{
            height:16px;
        }

        .sub-head{
            height:16px;
        }

        .col-house{ width: 18%; }
        .col-president{ width: 12%; }
        .col-meeting{ width: 12%; }
        .col-small{ width: 6%; }
        .col-ws{ width: 6%; }

        .body-row td{
            height:30px;
        }

        input.cell{
            width:100%;
            height:100%;
            border:0;
            outline:0;
            background:transparent;
            font-size:13px;
            padding:4px 5px;
        }

        input.center{
            text-align:center;
        }

        .money-wrap{
            display:grid;
            grid-template-columns: 14px 1fr;
            align-items:center;
            width:100%;
            height:100%;
        }

        .money-sign{
            text-align:center;
            font-size:18px;
            line-height:1;
        }

        .totals-row td{
            height:30px;
        }

        .totals-label{
            border:none !important;
            font-weight:700;
            font-size:13px;
            text-align:right;
            padding-right:24px !important;
        }

        .totals-blank{
            border:none !important;
        }

        .footnote{
            margin-top:14px;
            text-align:center;
            font-size:12px;
            font-weight:700;
        }

        @media print{
            @page{
                size: letter landscape;
                margin: 0.35in;
            }

            html, body{
                background:#fff;
                margin:0;
                padding:0;
            }

            .toolbar{
                display:none !important;
            }

            .page{
                width:100%;
                box-shadow:none;
                margin:0;
                padding:0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-left">
            <select id="historySelect">
                <option value="">Load prior record...</option>
            </select>
            <button type="button" id="printBtn">Print</button>
        </div>
        <div class="toolbar-right">
            <span class="status" id="saveStatus">Ready</span>
        </div>
    </div>

    <form id="reportForm" class="page" autocomplete="off">
        <input type="hidden" name="id" id="record_id" value="<?= h($form['id']) ?>">
        <input type="hidden" name="report_date" id="report_date" value="<?= h($form['report_date']) ?>">

        <div class="logo-wrap">
            <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
        </div>

        <div class="topline">
            <div class="left">
                <span class="line-inline">
                    <span>CHAPTER</span>
                    <input type="text" name="chapter" id="chapter" class="chapter-input" value="<?= h($form['chapter']) ?>">
                </span>
            </div>

            <div class="center">OXFORD HOUSE ACTIVITY REPORT</div>

            <div class="right" style="text-align:right;">
                <span class="line-inline">
                    <span>DATE</span>
                    <span class="date-wrap">
                        <input type="text" id="date_mm" class="date-part" maxlength="2" inputmode="numeric">
                        <span>/</span>
                        <input type="text" id="date_dd" class="date-part" maxlength="2" inputmode="numeric">
                        <span>/20</span>
                        <input type="text" id="date_yy" class="date-part date-year" maxlength="2" inputmode="numeric">
                    </span>
                </span>
            </div>
        </div>

        <table class="report">
            <colgroup>
                <col class="col-house">
                <col class="col-president">
                <col class="col-meeting">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-small">
                <col class="col-ws">
            </colgroup>
            <thead>
                <tr class="main-head">
                    <th>HOUSE NAME</th>
                    <th>PRESIDENT</th>
                    <th>MEETING TIME</th>
                    <th colspan="3">BEDS</th>
                    <th colspan="2">ARRIVALS</th>
                    <th colspan="4">DEPARTURES</th>
                    <th>WS DON</th>
                </tr>
                <tr class="sub-head">
                    <th style="border-top:none;"></th>
                    <th style="border-top:none;"></th>
                    <th style="border-top:none;"></th>
                    <th>Total</th>
                    <th>Filled</th>
                    <th>Vacant</th>
                    <th>Apps</th>
                    <th>Accepted</th>
                    <th>Voluntary</th>
                    <th>Relapse</th>
                    <th>Other</th>
                    <th>Total</th>
                    <th style="border-top:none;"></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $rowCount; $i++): ?>
                    <tr class="body-row">
                        <td><input class="cell" type="text" name="house_name[]" value="<?= h($form['rows'][$i]['house_name']) ?>"></td>
                        <td><input class="cell" type="text" name="president[]" value="<?= h($form['rows'][$i]['president']) ?>"></td>
                        <td><input class="cell center" type="text" name="meeting_time[]" value="<?= h($form['rows'][$i]['meeting_time']) ?>"></td>

                        <td><input class="cell center num" data-col="beds_total" type="text" name="beds_total[]" value="<?= h($form['rows'][$i]['beds_total']) ?>"></td>
                        <td><input class="cell center num" data-col="beds_filled" type="text" name="beds_filled[]" value="<?= h($form['rows'][$i]['beds_filled']) ?>"></td>
                        <td><input class="cell center num" data-col="beds_vacant" type="text" name="beds_vacant[]" value="<?= h($form['rows'][$i]['beds_vacant']) ?>"></td>

                        <td><input class="cell center num" data-col="arrivals_apps" type="text" name="arrivals_apps[]" value="<?= h($form['rows'][$i]['arrivals_apps']) ?>"></td>
                        <td><input class="cell center num" data-col="arrivals_accepted" type="text" name="arrivals_accepted[]" value="<?= h($form['rows'][$i]['arrivals_accepted']) ?>"></td>

                        <td><input class="cell center num dep-part" data-col="departures_voluntary" type="text" name="departures_voluntary[]" value="<?= h($form['rows'][$i]['departures_voluntary']) ?>"></td>
                        <td><input class="cell center num dep-part" data-col="departures_relapse" type="text" name="departures_relapse[]" value="<?= h($form['rows'][$i]['departures_relapse']) ?>"></td>
                        <td><input class="cell center num dep-part" data-col="departures_other" type="text" name="departures_other[]" value="<?= h($form['rows'][$i]['departures_other']) ?>"></td>
                        <td><input class="cell center num dep-total" data-col="departures_total" type="text" name="departures_total[]" value="<?= h($form['rows'][$i]['departures_total']) ?>" readonly></td>

                        <td>
                            <div class="money-wrap">
                                <div class="money-sign">$</div>
                                <input class="cell center num" data-col="ws_don" type="text" name="ws_don[]" value="<?= h($form['rows'][$i]['ws_don']) ?>">
                            </div>
                        </td>
                    </tr>
                <?php endfor; ?>

                <tr class="totals-row">
                    <td class="totals-blank"></td>
                    <td class="totals-blank"></td>
                    <td class="totals-label">TOTALS</td>

                    <td><input class="cell center" type="text" id="total_beds_total" readonly></td>
                    <td><input class="cell center" type="text" id="total_beds_filled" readonly></td>
                    <td><input class="cell center" type="text" id="total_beds_vacant" readonly></td>

                    <td><input class="cell center" type="text" id="total_arrivals_apps" readonly></td>
                    <td><input class="cell center" type="text" id="total_arrivals_accepted" readonly></td>

                    <td><input class="cell center" type="text" id="total_departures_voluntary" readonly></td>
                    <td><input class="cell center" type="text" id="total_departures_relapse" readonly></td>
                    <td><input class="cell center" type="text" id="total_departures_other" readonly></td>
                    <td><input class="cell center" type="text" id="total_departures_total" readonly></td>

                    <td>
                        <div class="money-wrap">
                            <div class="money-sign">$</div>
                            <input class="cell center" type="text" id="total_ws_don" readonly>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footnote">Completed forms need to go to outreach rep.</div>
    </form>

    <script>
        const form = document.getElementById('reportForm');
        const historySelect = document.getElementById('historySelect');
        const printBtn = document.getElementById('printBtn');
        const saveStatus = document.getElementById('saveStatus');
        const recordId = document.getElementById('record_id');

        const dateMM = document.getElementById('date_mm');
        const dateDD = document.getElementById('date_dd');
        const dateYY = document.getElementById('date_yy');
        const hiddenDate = document.getElementById('report_date');

        let saveTimer = null;
        let loadingRecord = false;

        function parseNum(value) {
            value = String(value ?? '').replace(/[^0-9.\-]/g, '').trim();
            if (value === '' || isNaN(Number(value))) {
                return 0;
            }
            return Number(value);
        }

        function formatNum(value) {
            if (value === '' || value === null || typeof value === 'undefined') {
                return '';
            }
            return Number(value).toFixed(2);
        }

        function setStatus(text) {
            saveStatus.textContent = text;
        }

        function updateHiddenDate() {
            const mm = dateMM.value.replace(/\D/g, '').slice(0, 2);
            const dd = dateDD.value.replace(/\D/g, '').slice(0, 2);
            const yy = dateYY.value.replace(/\D/g, '').slice(0, 2);

            if (mm.length === 2 && dd.length === 2 && yy.length === 2) {
                hiddenDate.value = `20${yy}-${mm}-${dd}`;
            } else {
                hiddenDate.value = '';
            }
        }

        function applyDateToSplit(dateValue) {
            if (!dateValue || !/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
                dateMM.value = '';
                dateDD.value = '';
                dateYY.value = '';
                return;
            }
            const [yyyy, mm, dd] = dateValue.split('-');
            dateMM.value = mm;
            dateDD.value = dd;
            dateYY.value = yyyy.slice(-2);
        }

        function updateDepartureRowTotals() {
            const bodyRows = document.querySelectorAll('tbody tr.body-row');
            bodyRows.forEach(row => {
                const voluntary = row.querySelector('input[name="departures_voluntary[]"]');
                const relapse = row.querySelector('input[name="departures_relapse[]"]');
                const other = row.querySelector('input[name="departures_other[]"]');
                const total = row.querySelector('input[name="departures_total[]"]');

                const sum = parseNum(voluntary.value) + parseNum(relapse.value) + parseNum(other.value);
                total.value = sum === 0 && voluntary.value === '' && relapse.value === '' && other.value === ''
                    ? ''
                    : formatNum(sum);
            });
        }

        function updateColumnTotals() {
            const cols = [
                'beds_total',
                'beds_filled',
                'beds_vacant',
                'arrivals_apps',
                'arrivals_accepted',
                'departures_voluntary',
                'departures_relapse',
                'departures_other',
                'departures_total',
                'ws_don'
            ];

            cols.forEach(col => {
                let sum = 0;
                const inputs = document.querySelectorAll(`input[data-col="${col}"]`);
                inputs.forEach(input => {
                    sum += parseNum(input.value);
                });

                const totalField = document.getElementById(`total_${col}`);
                if (totalField) {
                    totalField.value = sum === 0 ? '' : formatNum(sum);
                }
            });
        }

        function normalizeNumbers() {
            document.querySelectorAll('input.num').forEach(input => {
                if (input.readOnly) return;
                const raw = input.value.trim();
                if (raw === '') return;
                input.value = formatNum(parseNum(raw));
            });
        }

        async function loadHistory() {
            try {
                const res = await fetch('?action=history');
                const data = await res.json();

                historySelect.innerHTML = '<option value="">Load prior record...</option>';

                if (data.ok && Array.isArray(data.records)) {
                    data.records.forEach(row => {
                        const opt = document.createElement('option');
                        const dateText = row.report_date || 'No date';
                        const chapterText = row.chapter || 'No chapter';
                        opt.value = row.id;
                        opt.textContent = `${dateText} - Chapter ${chapterText}`;
                        historySelect.appendChild(opt);
                    });
                }
            } catch (e) {
                setStatus('History failed');
            }
        }

        async function loadRecord(id) {
            if (!id) return;

            loadingRecord = true;
            setStatus('Loading...');

            try {
                const res = await fetch(`?action=load&id=${encodeURIComponent(id)}`);
                const data = await res.json();

                if (!data.ok) {
                    setStatus('Load failed');
                    loadingRecord = false;
                    return;
                }

                const record = data.record;

                recordId.value = record.id || '';
                document.getElementById('chapter').value = record.chapter || '';
                hiddenDate.value = record.report_date || '';
                applyDateToSplit(record.report_date || '');

                const fieldMap = [
                    'house_name',
                    'president',
                    'meeting_time',
                    'beds_total',
                    'beds_filled',
                    'beds_vacant',
                    'arrivals_apps',
                    'arrivals_accepted',
                    'departures_voluntary',
                    'departures_relapse',
                    'departures_other',
                    'departures_total',
                    'ws_don'
                ];

                fieldMap.forEach(field => {
                    const inputs = document.querySelectorAll(`input[name="${field}[]"]`);
                    inputs.forEach((input, index) => {
                        input.value = record.rows[index] && record.rows[index][field] ? record.rows[index][field] : '';
                    });
                });

                updateDepartureRowTotals();
                updateColumnTotals();
                setStatus('Record loaded');
            } catch (e) {
                setStatus('Load failed');
            } finally {
                loadingRecord = false;
            }
        }

        async function autosave() {
            if (loadingRecord) return;

            updateHiddenDate();
            updateDepartureRowTotals();
            updateColumnTotals();

            const formData = new FormData(form);
            formData.append('action', 'autosave');

            setStatus('Saving...');

            try {
                const res = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.ok) {
                    if (data.id) {
                        recordId.value = data.id;
                    }
                    setStatus('Saved');
                    await loadHistory();
                } else {
                    setStatus('Save failed');
                }
            } catch (e) {
                setStatus('Save failed');
            }
        }

        function queueAutosave() {
            if (loadingRecord) return;

            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => {
                normalizeNumbers();
                updateDepartureRowTotals();
                updateColumnTotals();
                autosave();
            }, 700);
        }

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                if ([dateMM, dateDD, dateYY].includes(input)) {
                    input.value = input.value.replace(/\D/g, '').slice(0, 2);
                    updateHiddenDate();
                }

                if (input.classList.contains('dep-part')) {
                    updateDepartureRowTotals();
                }

                if (input.classList.contains('num')) {
                    updateColumnTotals();
                }

                queueAutosave();
            });

            input.addEventListener('blur', () => {
                if (input.classList.contains('num') && !input.readOnly) {
                    const raw = input.value.trim();
                    input.value = raw === '' ? '' : formatNum(parseNum(raw));
                    updateDepartureRowTotals();
                    updateColumnTotals();
                    queueAutosave();
                }
            });
        });

        [dateMM, dateDD, dateYY].forEach((el, idx, arr) => {
            el.addEventListener('input', () => {
                el.value = el.value.replace(/\D/g, '').slice(0, 2);
                if (el.value.length === 2 && idx < arr.length - 1) {
                    arr[idx + 1].focus();
                }
                updateHiddenDate();
            });
        });

        historySelect.addEventListener('change', function () {
            loadRecord(this.value);
        });

        printBtn.addEventListener('click', () => {
            window.print();
        });

        applyDateToSplit(hiddenDate.value);
        updateDepartureRowTotals();
        updateColumnTotals();
        loadHistory();
    </script>
</body>
</html>