<?php
/**
 * New House Tour Form
 * Single-file PHP application
 * - Closely matches the uploaded Oxford House Tour Form layout
 * - Auto-saves to MySQL
 * - History dropdown by house name + date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated section totals and grand total
 */
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';

const SECTIONS = [
    'exterior' => [
        'title' => 'EXTERIOR',
        'rows' => ['Yard', 'Parking', 'Paint/Gutters', 'Porches', 'Garage', 'Overall'],
    ],
    'common_area' => [
        'title' => 'COMMON AREA',
        'rows' => ['Living Room(s)', 'Kitchen(s)', 'Dining Room', 'Bathrooms', 'Hallways', 'Office Area', 'Carpet', 'Walls', 'Overall'],
    ],
    'bedrooms' => [
        'title' => 'BEDROOMS',
        'rows' => ['Cleanliness', 'Carpet', 'Walls', 'Overall'],
    ],
    'office_area' => [
        'title' => 'OFFICE AREA',
        'rows' => ['Officer Binders', 'Filing System', 'Organization', 'Overall'],
    ],
    'safety' => [
        'title' => 'SAFETY',
        'rows' => ['Smoke Detectors', 'CO2 Detectors', 'Fire Extinguisher', 'Rope Ladder', 'Room Egress', 'First Aid Kit'],
    ],
];

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function posted(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function normalizeScore(mixed $value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }
    $int = (int)$value;
    if ($int < 1) {
        $int = 1;
    }
    if ($int > 5) {
        $int = 5;
    }
    return $int;
}

function makeEmptyForm(): array
{
    $data = [
        'id' => '',
        'house_name' => '',
        'tour_date' => '',
        'tour_time' => '',
        'smoking_area' => '',
        'notes' => '',
        'inspected_by' => '',
        'inspector_name' => '',
        'signature' => '',
        'items' => [],
    ];

    foreach (SECTIONS as $sectionKey => $section) {
        foreach ($section['rows'] as $label) {
            $itemKey = itemKey($sectionKey, $label);
            $data['items'][$itemKey] = [
                'section' => $sectionKey,
                'label' => $label,
                'score' => '',
                'comment' => '',
            ];
        }
    }

    return $data;
}

function itemKey(string $section, string $label): string
{
    $normalized = strtolower($label);
    $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized ?? '');
    $normalized = trim((string)$normalized, '_');
    return $section . '__' . $normalized;
}

function sectionTotal(array $form, string $sectionKey): int
{
    $sum = 0;
    foreach ($form['items'] as $item) {
        if (($item['section'] ?? '') !== $sectionKey) {
            continue;
        }
        $score = (int)($item['score'] ?? 0);
        if ($score >= 1 && $score <= 5) {
            $sum += $score;
        }
    }
    return $sum;
}

function grandTotal(array $form): int
{
    $sum = 0;
    foreach (array_keys(SECTIONS) as $sectionKey) {
        $sum += sectionTotal($form, $sectionKey);
    }
    return $sum;
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

$pdo->exec("CREATE TABLE IF NOT EXISTS house_tour_forms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    tour_date DATE NULL,
    tour_time VARCHAR(20) NOT NULL DEFAULT '',
    smoking_area VARCHAR(10) NOT NULL DEFAULT '',
    notes TEXT NULL,
    inspected_by VARCHAR(255) NOT NULL DEFAULT '',
    inspector_name VARCHAR(255) NOT NULL DEFAULT '',
    signature VARCHAR(255) NOT NULL DEFAULT '',
    items_json LONGTEXT NULL,
    section_totals_json TEXT NULL,
    grand_total INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_house_name (house_name),
    INDEX idx_tour_date (tour_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$form = makeEmptyForm();
$statusMessage = '';
$statusType = 'success';
$currentId = isset($_GET['load']) ? (int)$_GET['load'] : 0;

/* =========================
   LOAD EXISTING RECORD
========================= */
if ($currentId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM house_tour_forms WHERE id = ? LIMIT 1');
    $stmt->execute([$currentId]);
    $record = $stmt->fetch();
    if ($record) {
        $form['id'] = (string)$record['id'];
        $form['house_name'] = (string)$record['house_name'];
        $form['tour_date'] = !empty($record['tour_date']) ? (string)$record['tour_date'] : '';
        $form['tour_time'] = (string)$record['tour_time'];
        $form['smoking_area'] = (string)$record['smoking_area'];
        $form['notes'] = (string)$record['notes'];
        $form['inspected_by'] = (string)$record['inspected_by'];
        $form['inspector_name'] = (string)$record['inspector_name'];
        $form['signature'] = (string)$record['signature'];

        $items = json_decode((string)$record['items_json'], true);
        if (is_array($items)) {
            foreach ($items as $itemKey => $item) {
                if (isset($form['items'][$itemKey]) && is_array($item)) {
                    $form['items'][$itemKey]['score'] = (string)($item['score'] ?? '');
                    $form['items'][$itemKey]['comment'] = (string)($item['comment'] ?? '');
                }
            }
        }
    }
}

/* =========================
   SAVE / AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['id'] = (string)posted('id', '');
    $form['house_name'] = trim((string)posted('house_name', ''));
    $form['tour_date'] = trim((string)posted('tour_date', ''));
    $form['tour_time'] = trim((string)posted('tour_time', ''));
    $form['smoking_area'] = trim((string)posted('smoking_area', ''));
    $form['notes'] = trim((string)posted('notes', ''));
    $form['inspected_by'] = trim((string)posted('inspected_by', ''));
    $form['inspector_name'] = trim((string)posted('inspector_name', ''));
    $form['signature'] = trim((string)posted('signature', ''));

    $postedItems = $_POST['items'] ?? [];
    foreach (SECTIONS as $sectionKey => $section) {
        foreach ($section['rows'] as $label) {
            $itemKey = itemKey($sectionKey, $label);
            $score = normalizeScore($postedItems[$itemKey]['score'] ?? '');
            $comment = trim((string)($postedItems[$itemKey]['comment'] ?? ''));
            $form['items'][$itemKey] = [
                'section' => $sectionKey,
                'label' => $label,
                'score' => $score === null ? '' : (string)$score,
                'comment' => $comment,
            ];
        }
    }

    $sectionTotals = [];
    foreach (array_keys(SECTIONS) as $sectionKey) {
        $sectionTotals[$sectionKey] = sectionTotal($form, $sectionKey);
    }
    $grand = grandTotal($form);

    $id = (int)$form['id'];
    $itemsJson = json_encode($form['items'], JSON_UNESCAPED_UNICODE);
    $sectionTotalsJson = json_encode($sectionTotals, JSON_UNESCAPED_UNICODE);

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE house_tour_forms SET
            house_name = ?,
            tour_date = NULLIF(?, ''),
            tour_time = ?,
            smoking_area = ?,
            notes = ?,
            inspected_by = ?,
            inspector_name = ?,
            signature = ?,
            items_json = ?,
            section_totals_json = ?,
            grand_total = ?
            WHERE id = ?");
        $stmt->execute([
            $form['house_name'],
            $form['tour_date'],
            $form['tour_time'],
            $form['smoking_area'],
            $form['notes'],
            $form['inspected_by'],
            $form['inspector_name'],
            $form['signature'],
            $itemsJson,
            $sectionTotalsJson,
            $grand,
            $id,
        ]);
        $currentId = $id;
        $statusMessage = 'Record updated.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO house_tour_forms (
            house_name,
            tour_date,
            tour_time,
            smoking_area,
            notes,
            inspected_by,
            inspector_name,
            signature,
            items_json,
            section_totals_json,
            grand_total
        ) VALUES (?, NULLIF(?, ''), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $form['house_name'],
            $form['tour_date'],
            $form['tour_time'],
            $form['smoking_area'],
            $form['notes'],
            $form['inspected_by'],
            $form['inspector_name'],
            $form['signature'],
            $itemsJson,
            $sectionTotalsJson,
            $grand,
        ]);
        $currentId = (int)$pdo->lastInsertId();
        $form['id'] = (string)$currentId;
        $statusMessage = 'Record saved.';
    }

    if ((string)posted('autosave', '') === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'id' => $currentId,
            'message' => $statusMessage,
            'sectionTotals' => $sectionTotals,
            'grandTotal' => $grand,
        ]);
        exit;
    }
}

/* =========================
   HISTORY LISTS
========================= */
$houseOptions = $pdo->query("SELECT DISTINCT house_name FROM house_tour_forms WHERE house_name <> '' ORDER BY house_name ASC")?->fetchAll() ?? [];

$historyHouse = trim((string)($_GET['history_house'] ?? ''));
$historyDate = trim((string)($_GET['history_date'] ?? ''));
$where = [];
$params = [];

if ($historyHouse !== '') {
    $where[] = 'house_name = ?';
    $params[] = $historyHouse;
}
if ($historyDate !== '') {
    $where[] = 'tour_date = ?';
    $params[] = $historyDate;
}

$sqlHistory = 'SELECT id, house_name, tour_date, tour_time, updated_at, grand_total FROM house_tour_forms';
if ($where) {
    $sqlHistory .= ' WHERE ' . implode(' AND ', $where);
}
$sqlHistory .= ' ORDER BY COALESCE(tour_date, DATE(updated_at)) DESC, id DESC LIMIT 100';
$stmtHistory = $pdo->prepare($sqlHistory);
$stmtHistory->execute($params);
$historyRows = $stmtHistory->fetchAll();

function renderSection(string $sectionKey, array $section, array $form): string
{
    $rowsHtml = '';
    foreach ($section['rows'] as $label) {
        $itemKey = itemKey($sectionKey, $label);
        $score = $form['items'][$itemKey]['score'] ?? '';
        $comment = $form['items'][$itemKey]['comment'] ?? '';
        $rowsHtml .= '<tr>';
        $rowsHtml .= '<td class="label-cell">' . h($label) . '</td>';
        $rowsHtml .= '<td class="score-cell"><input type="number" min="1" max="5" inputmode="numeric" name="items[' . h($itemKey) . '][score]" value="' . h($score) . '" class="score-input calc-score"></td>';
        $rowsHtml .= '<td class="comment-cell"><input type="text" name="items[' . h($itemKey) . '][comment]" value="' . h($comment) . '" class="comment-input"></td>';
        $rowsHtml .= '</tr>';
    }

    $sectionId = 'section-total-' . $sectionKey;

    return '
    <table class="tour-section">
        <tr>
            <th class="section-title">' . h($section['title']) . '</th>
            <th class="score-head">Score</th>
            <th class="comments-head">Comments</th>
        </tr>
        ' . $rowsHtml . '
        <tr class="totals-row">
            <td class="label-cell total-label">Section Total</td>
            <td class="score-cell"><input type="text" id="' . h($sectionId) . '" class="score-input total-input" readonly value="' . h((string)sectionTotal($form, $sectionKey)) . '"></td>
            <td class="comment-cell"></td>
        </tr>
    </table>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New House Tour Form</title>
    <style>
        :root {
            --border: #000;
            --bg: #efefef;
            --white: #fff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            background: #d9d9d9;
            color: #000;
        }
        .page {
            width: 768px;
            margin: 18px auto;
            background: var(--bg);
            border: 1px solid #bbb;
            padding: 12px 14px 18px;
        }
        .topbar {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .controls-left, .controls-right {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn, .select, .filter-input {
            font-family: inherit;
            font-size: 14px;
            padding: 6px 10px;
            border: 1px solid #333;
            background: #fff;
        }
        .status {
            font-size: 13px;
            padding: 4px 8px;
            border: 1px solid #999;
            background: #fff;
        }
        .status.success { border-color: #1f7a1f; }
        .status.error { border-color: #a11; }
        .print-only { display: none; }

        .doc {
            position: relative;
        }
        .logo-wrap {
            position: absolute;
            left: 0;
            top: 0;
            width: 90px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-wrap img {
            max-width: 78px;
            max-height: 58px;
            object-fit: contain;
        }
        .title {
            text-align: center;
            font-weight: 700;
            font-size: 22px;
            line-height: 1.05;
            margin: 0 0 18px;
            text-transform: uppercase;
        }
        .header-grid {
            display: grid;
            grid-template-columns: 1.4fr 0.6fr 0.6fr;
            gap: 24px;
            align-items: end;
            margin-bottom: 8px;
        }
        .field-line {
            display: flex;
            align-items: end;
            gap: 8px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
        }
        .field-line label {
            white-space: nowrap;
        }
        .line-input {
            flex: 1;
            border: 0;
            border-bottom: 2px solid #000;
            background: transparent;
            min-height: 26px;
            font-family: inherit;
            font-size: 16px;
            padding: 2px 4px;
            outline: none;
        }
        .rating-scale {
            display: grid;
            grid-template-columns: 170px repeat(5, 1fr);
            align-items: end;
            margin: 6px 0 8px;
            font-size: 16px;
        }
        .rating-scale .scale-label {
            font-weight: 700;
            text-transform: uppercase;
        }
        .rating-box {
            text-align: center;
            line-height: 1.1;
        }
        .rating-box .num {
            font-weight: 700;
            font-size: 18px;
        }
        .tour-section {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px;
            border: 3px solid #000;
            table-layout: fixed;
            background: var(--bg);
        }
        .tour-section th,
        .tour-section td {
            border: 1px solid #000;
            padding: 0;
            vertical-align: middle;
        }
        .tour-section .section-title {
            width: 160px;
            font-size: 18px;
            font-weight: 700;
            text-decoration: underline;
            text-align: center;
            background: var(--bg);
        }
        .tour-section .score-head,
        .tour-section .comments-head {
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            background: var(--bg);
        }
        .tour-section .score-head { width: 84px; }
        .tour-section .comments-head { width: auto; }
        .label-cell {
            width: 160px;
            text-align: center;
            font-size: 17px;
            height: 28px;
            background: var(--bg);
            border-right: 1px solid #000;
        }
        .score-cell {
            width: 84px;
            background: var(--bg);
        }
        .comment-cell {
            background: var(--bg);
        }
        .score-input,
        .comment-input {
            width: 100%;
            height: 27px;
            border: 0;
            background: transparent;
            font-family: inherit;
            font-size: 16px;
            padding: 2px 6px;
            outline: none;
        }
        .score-input {
            text-align: center;
        }
        .total-label {
            font-weight: 700;
        }
        .bottom-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            border-left: 3px solid #000;
            border-right: 3px solid #000;
            border-bottom: 3px solid #000;
            margin-top: -10px;
        }
        .bottom-left,
        .bottom-right {
            background: var(--bg);
        }
        .bottom-left {
            border-right: 1px solid #000;
            padding: 8px 0 0;
        }
        .bottom-right {
            display: flex;
            align-items: stretch;
        }
        .smoking-row,
        .notes-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            align-items: center;
            min-height: 31px;
        }
        .notes-row {
            min-height: 63px;
        }
        .bottom-label {
            font-size: 17px;
            text-align: center;
            text-decoration: underline;
            padding: 0 6px;
        }
        .bottom-input,
        .notes-box {
            width: 100%;
            border: 1px solid #000;
            border-left: 0;
            min-height: 31px;
            background: transparent;
            font-family: inherit;
            font-size: 16px;
            padding: 4px 6px;
            outline: none;
            resize: none;
        }
        .notes-box {
            min-height: 63px;
            border-bottom: 0;
        }
        .warning-box {
            width: 100%;
            border-top: 1px solid #000;
            padding: 8px 10px;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.15;
            display: flex;
            align-items: center;
        }
        .sign-row {
            display: grid;
            grid-template-columns: 140px 1fr 100px 1fr;
            gap: 10px;
            align-items: end;
            margin-top: 14px;
            font-size: 18px;
        }
        .plain-line {
            border: 0;
            border-bottom: 2px solid #000;
            background: transparent;
            min-height: 28px;
            font-family: inherit;
            font-size: 17px;
            padding: 2px 4px;
            outline: none;
        }
        .summary-bar {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin: 10px 0 14px;
        }
        .summary-box {
            border: 1px solid #000;
            background: #fff;
            padding: 6px;
            text-align: center;
            font-size: 13px;
        }
        .summary-box strong {
            display: block;
            font-size: 18px;
        }
        .history-panel {
            margin-top: 16px;
            border-top: 2px solid #000;
            padding-top: 12px;
        }
        .history-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .history-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .history-table th,
        .history-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 14px;
            text-align: left;
        }
        .history-table th {
            background: #efefef;
        }
        .load-link {
            color: #000;
            text-decoration: underline;
        }

        @media print {
            body { background: #fff; }
            .page {
                width: 100%;
                margin: 0;
                border: 0;
                padding: 0;
                background: #efefef;
            }
            .topbar,
            .history-panel {
                display: none !important;
            }
            .print-only { display: block; }
            .doc { margin-top: 0; }
            input, textarea, select {
                color: #000 !important;
                -webkit-text-fill-color: #000 !important;
                opacity: 1 !important;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <form id="tourForm" method="post" action="">
        <input type="hidden" name="id" id="record_id" value="<?= h($form['id']) ?>">

        <div class="topbar">
            <div class="controls-left">
                <button type="submit" class="btn">Save Sheet</button>
                <button type="button" class="btn" onclick="window.print()">Print</button>
                <a class="btn" href="<?= h(basename(__FILE__)) ?>" style="text-decoration:none; display:inline-block;">New Sheet</a>
                <span id="saveStatus" class="status <?= h($statusType) ?>"><?= h($statusMessage !== '' ? $statusMessage : 'Ready') ?></span>
            </div>
            <div class="controls-right">
                <label for="quickLoad"><strong>History:</strong></label>
                <select id="quickLoad" class="select" onchange="if(this.value){window.location='?load='+this.value;}">
                    <option value="">Load saved record...</option>
                    <?php foreach ($historyRows as $row): ?>
                        <option value="<?= (int)$row['id'] ?>" <?= ((int)$row['id'] === $currentId) ? 'selected' : '' ?>>
                            <?= h(($row['house_name'] ?: 'No House') . ' - ' . (($row['tour_date'] ?: '') ?: substr((string)$row['updated_at'], 0, 10))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="doc">
            <div class="logo-wrap">
                <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo">
            </div>

            <h1 class="title">OXFORD HOUSE<br>TOUR FORM</h1>

            <div class="header-grid">
                <div class="field-line">
                    <label for="house_name">HOUSE NAME:</label>
                    <input class="line-input autosave" type="text" name="house_name" id="house_name" value="<?= h($form['house_name']) ?>">
                </div>
                <div class="field-line">
                    <label for="tour_date">DATE:</label>
                    <input class="line-input autosave" type="date" name="tour_date" id="tour_date" value="<?= h($form['tour_date']) ?>">
                </div>
                <div class="field-line">
                    <label for="tour_time">TIME:</label>
                    <input class="line-input autosave" type="time" name="tour_time" id="tour_time" value="<?= h($form['tour_time']) ?>">
                </div>
            </div>

            <div class="rating-scale">
                <div class="scale-label">RATING SCALE:</div>
                <div class="rating-box"><div class="num">1</div><div>Very Poor</div></div>
                <div class="rating-box"><div class="num">2</div><div>Poor</div></div>
                <div class="rating-box"><div class="num">3</div><div>Okay</div></div>
                <div class="rating-box"><div class="num">4</div><div>Good</div></div>
                <div class="rating-box"><div class="num">5</div><div>Excellent</div></div>
            </div>

            <?= renderSection('exterior', SECTIONS['exterior'], $form) ?>
            <?= renderSection('common_area', SECTIONS['common_area'], $form) ?>
            <?= renderSection('bedrooms', SECTIONS['bedrooms'], $form) ?>
            <?= renderSection('office_area', SECTIONS['office_area'], $form) ?>
            <?= renderSection('safety', SECTIONS['safety'], $form) ?>

            <div class="bottom-grid">
                <div class="bottom-left">
                    <div class="smoking-row">
                        <div class="bottom-label">Smoking Area:</div>
                        <input class="bottom-input autosave" type="text" name="smoking_area" value="<?= h($form['smoking_area']) ?>">
                    </div>
                    <div class="notes-row">
                        <div class="bottom-label">NOTES:</div>
                        <textarea class="notes-box autosave" name="notes"><?= h($form['notes']) ?></textarea>
                    </div>
                </div>
                <div class="bottom-right">
                    <div class="warning-box">** Please check for potential fire hazards; make sure there is a secure ash tray that cigarettes can be fully put out in case of weather (WIND) or being knocked over.</div>
                </div>
            </div>

            <div class="sign-row">
                <div>Inspected By:</div>
                <input class="plain-line autosave" type="text" name="inspected_by" value="<?= h($form['inspected_by']) ?>">
                <div>Inspector House Name:</div>
                <input class="plain-line autosave" type="text" name="inspector_name" value="<?= h($form['inspector_name']) ?>">
            </div>
            <div class="sign-row" style="grid-template-columns: 140px 1fr 100px 1fr;">
                <div></div>
                <div></div>
                <div>Signature:</div>
                <input class="plain-line autosave" type="text" name="signature" value="<?= h($form['signature']) ?>">
            </div>

            <div class="summary-bar">
                <div class="summary-box">Exterior<strong id="sum-exterior"><?= sectionTotal($form, 'exterior') ?></strong></div>
                <div class="summary-box">Common Area<strong id="sum-common_area"><?= sectionTotal($form, 'common_area') ?></strong></div>
                <div class="summary-box">Bedrooms<strong id="sum-bedrooms"><?= sectionTotal($form, 'bedrooms') ?></strong></div>
                <div class="summary-box">Office Area<strong id="sum-office_area"><?= sectionTotal($form, 'office_area') ?></strong></div>
                <div class="summary-box">Safety<strong id="sum-safety"><?= sectionTotal($form, 'safety') ?></strong></div>
                <div class="summary-box">Grand Total<strong id="sum-grand"><?= grandTotal($form) ?></strong></div>
            </div>
        </div>
    </form>

    <div class="history-panel">
        <div class="history-title">Saved History</div>
        <form method="get" class="history-filters">
            <select name="history_house" class="select">
                <option value="">All house names</option>
                <?php foreach ($houseOptions as $opt): ?>
                    <option value="<?= h($opt['house_name']) ?>" <?= $historyHouse === (string)$opt['house_name'] ? 'selected' : '' ?>><?= h($opt['house_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="history_date" class="filter-input" value="<?= h($historyDate) ?>">
            <button type="submit" class="btn">Filter</button>
            <a href="<?= h(basename(__FILE__)) ?>" class="btn" style="text-decoration:none; display:inline-block;">Clear</a>
        </form>

        <table class="history-table">
            <thead>
                <tr>
                    <th>House Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Grand Total</th>
                    <th>Last Updated</th>
                    <th>Load</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$historyRows): ?>
                <tr>
                    <td colspan="6">No saved history found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($historyRows as $row): ?>
                    <tr>
                        <td><?= h($row['house_name']) ?></td>
                        <td><?= h((string)($row['tour_date'] ?: '')) ?></td>
                        <td><?= h((string)$row['tour_time']) ?></td>
                        <td><?= (int)$row['grand_total'] ?></td>
                        <td><?= h((string)$row['updated_at']) ?></td>
                        <td><a class="load-link" href="?load=<?= (int)$row['id'] ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('tourForm');
    const status = document.getElementById('saveStatus');
    const recordId = document.getElementById('record_id');
    let autosaveTimer = null;
    let saving = false;

    const sectionMap = {
        exterior: 'section-total-exterior',
        common_area: 'section-total-common_area',
        bedrooms: 'section-total-bedrooms',
        office_area: 'section-total-office_area',
        safety: 'section-total-safety'
    };

    function updateTotals() {
        const sums = {
            exterior: 0,
            common_area: 0,
            bedrooms: 0,
            office_area: 0,
            safety: 0,
            grand: 0
        };

        document.querySelectorAll('input[name^="items["][name$="[score]"]');
        document.querySelectorAll('input[name^="items["]').forEach(function () {});
        document.querySelectorAll('.calc-score').forEach(function (input) {
            let val = parseInt(input.value || '0', 10);
            if (isNaN(val) || val < 1 || val > 5) {
                val = 0;
            }
            const name = input.getAttribute('name') || '';
            const match = name.match(/^items\[([^\]]+)\]\[score\]$/);
            if (!match) return;
            const key = match[1];
            const section = key.split('__')[0] || '';
            if (typeof sums[section] !== 'undefined') {
                sums[section] += val;
                sums.grand += val;
            }
        });

        Object.keys(sectionMap).forEach(function (section) {
            const totalField = document.getElementById(sectionMap[section]);
            const summaryField = document.getElementById('sum-' + section);
            if (totalField) totalField.value = String(sums[section]);
            if (summaryField) summaryField.textContent = String(sums[section]);
        });

        const grandField = document.getElementById('sum-grand');
        if (grandField) grandField.textContent = String(sums.grand);
    }

    function setStatus(message, isError) {
        status.textContent = message;
        status.className = 'status ' + (isError ? 'error' : 'success');
    }

    function autosave() {
        if (saving) return;
        saving = true;
        setStatus('Saving...', false);

        const fd = new FormData(form);
        fd.append('autosave', '1');

        fetch(window.location.href, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            saving = false;
            if (!data || !data.ok) {
                setStatus('Auto-save failed.', true);
                return;
            }
            if (data.id) {
                recordId.value = data.id;
            }
            setStatus(data.message || 'Saved.', false);
            updateTotals();
        })
        .catch(function () {
            saving = false;
            setStatus('Auto-save failed.', true);
        });
    }

    function queueAutosave() {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(autosave, 700);
    }

    form.querySelectorAll('input, textarea, select').forEach(function (el) {
        el.addEventListener('input', function () {
            updateTotals();
            queueAutosave();
        });
        el.addEventListener('change', function () {
            updateTotals();
            queueAutosave();
        });
    });

    updateTotals();
})();
</script>
</body>
</html>
