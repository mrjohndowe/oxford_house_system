<?php
declare(strict_types=1);

/**
 * Vice-Chair - Chapter Visit
 * Single-file PHP/MySQL app
 * - Fillable form closely matching uploaded sheet
 * - Auto-save to MySQL
 * - History dropdown by date
 * - Reload/edit prior records
 * - Print button
 * - Oxford House logo path fixed
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';

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

function get_pdo(string $dbHost, string $dbName, string $dbUser, string $dbPass): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function ensure_tables(PDO $pdo): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS vice_chair_chapter_visit_reports (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    report_date DATE DEFAULT NULL,
    chapter_name VARCHAR(255) DEFAULT '',
    location_name VARCHAR(255) DEFAULT '',
    chair_name VARCHAR(255) DEFAULT '',
    secretary_name VARCHAR(255) DEFAULT '',
    treasurer_name VARCHAR(255) DEFAULT '',
    hsc_chair_name VARCHAR(255) DEFAULT '',
    other_name VARCHAR(255) DEFAULT '',
    overall_grade TINYINT UNSIGNED DEFAULT NULL,
    amount_bank_account DECIMAL(12,2) DEFAULT 0.00,
    amount_dues_owed DECIMAL(12,2) DEFAULT 0.00,
    amount_out_loans DECIMAL(12,2) DEFAULT 0.00,
    chapter_meeting TEXT,
    comments_top TEXT,
    rating_1 TINYINT UNSIGNED DEFAULT NULL,
    rating_2 TINYINT UNSIGNED DEFAULT NULL,
    rating_3 TINYINT UNSIGNED DEFAULT NULL,
    rating_4 TINYINT UNSIGNED DEFAULT NULL,
    rating_5 TINYINT UNSIGNED DEFAULT NULL,
    rating_6 TINYINT UNSIGNED DEFAULT NULL,
    rating_7 TINYINT UNSIGNED DEFAULT NULL,
    rating_8 TINYINT UNSIGNED DEFAULT NULL,
    comments_1 TEXT,
    comments_2 TEXT,
    comments_3 TEXT,
    comments_4 TEXT,
    comments_5 TEXT,
    comments_6 TEXT,
    comments_7 TEXT,
    comments_8 TEXT,
    first_visit_date DATE DEFAULT NULL,
    follow_up_visit_dates VARCHAR(255) DEFAULT '',
    signature_name VARCHAR(255) DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_report_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

    $pdo->exec($sql);
}

function normalize_date(?string $date): ?string
{
    $date = trim((string)$date);
    if ($date === '') {
        return null;
    }

    $ts = strtotime($date);
    if ($ts === false) {
        return null;
    }

    return date('Y-m-d', $ts);
}

function normalize_rating(mixed $value): ?int
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $int = (int)$value;
    if ($int < 1 || $int > 5) {
        return null;
    }

    return $int;
}

function normalize_money(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '0.00';
    }

    $value = preg_replace('/[^0-9.\-]/', '', $value);
    if ($value === '' || !is_numeric($value)) {
        return '0.00';
    }

    return number_format((float)$value, 2, '.', '');
}

function get_payload(): array
{
    $src = $_POST;

    return [
        'report_date' => normalize_date($src['report_date'] ?? ''),
        'chapter_name' => trim((string)($src['chapter_name'] ?? '')),
        'location_name' => trim((string)($src['location_name'] ?? '')),
        'chair_name' => trim((string)($src['chair_name'] ?? '')),
        'secretary_name' => trim((string)($src['secretary_name'] ?? '')),
        'treasurer_name' => trim((string)($src['treasurer_name'] ?? '')),
        'hsc_chair_name' => trim((string)($src['hsc_chair_name'] ?? '')),
        'other_name' => trim((string)($src['other_name'] ?? '')),
        'overall_grade' => normalize_rating($src['overall_grade'] ?? ''),
        'amount_bank_account' => normalize_money($src['amount_bank_account'] ?? ''),
        'amount_dues_owed' => normalize_money($src['amount_dues_owed'] ?? ''),
        'amount_out_loans' => normalize_money($src['amount_out_loans'] ?? ''),
        'chapter_meeting' => trim((string)($src['chapter_meeting'] ?? '')),
        'comments_top' => trim((string)($src['comments_top'] ?? '')),
        'rating_1' => normalize_rating($src['rating_1'] ?? ''),
        'rating_2' => normalize_rating($src['rating_2'] ?? ''),
        'rating_3' => normalize_rating($src['rating_3'] ?? ''),
        'rating_4' => normalize_rating($src['rating_4'] ?? ''),
        'rating_5' => normalize_rating($src['rating_5'] ?? ''),
        'rating_6' => normalize_rating($src['rating_6'] ?? ''),
        'rating_7' => normalize_rating($src['rating_7'] ?? ''),
        'rating_8' => normalize_rating($src['rating_8'] ?? ''),
        'comments_1' => trim((string)($src['comments_1'] ?? '')),
        'comments_2' => trim((string)($src['comments_2'] ?? '')),
        'comments_3' => trim((string)($src['comments_3'] ?? '')),
        'comments_4' => trim((string)($src['comments_4'] ?? '')),
        'comments_5' => trim((string)($src['comments_5'] ?? '')),
        'comments_6' => trim((string)($src['comments_6'] ?? '')),
        'comments_7' => trim((string)($src['comments_7'] ?? '')),
        'comments_8' => trim((string)($src['comments_8'] ?? '')),
        'first_visit_date' => normalize_date($src['first_visit_date'] ?? ''),
        'follow_up_visit_dates' => trim((string)($src['follow_up_visit_dates'] ?? '')),
        'signature_name' => trim((string)($src['signature_name'] ?? '')),
    ];
}

function save_report(PDO $pdo, array $data): int
{
    if (empty($data['report_date'])) {
        throw new RuntimeException('Report date is required to save history.');
    }

    $existingId = null;
    $check = $pdo->prepare("SELECT id FROM vice_chair_chapter_visit_reports WHERE report_date = :report_date LIMIT 1");
    $check->execute(['report_date' => $data['report_date']]);
    $existingId = $check->fetchColumn();

    if ($existingId) {
        $updateData = $data;
        $updateData['id'] = (int)$existingId;

        $sql = <<<SQL
UPDATE vice_chair_chapter_visit_reports SET
    report_date = :report_date,
    chapter_name = :chapter_name,
    location_name = :location_name,
    chair_name = :chair_name,
    secretary_name = :secretary_name,
    treasurer_name = :treasurer_name,
    hsc_chair_name = :hsc_chair_name,
    other_name = :other_name,
    overall_grade = :overall_grade,
    amount_bank_account = :amount_bank_account,
    amount_dues_owed = :amount_dues_owed,
    amount_out_loans = :amount_out_loans,
    chapter_meeting = :chapter_meeting,
    comments_top = :comments_top,
    rating_1 = :rating_1,
    rating_2 = :rating_2,
    rating_3 = :rating_3,
    rating_4 = :rating_4,
    rating_5 = :rating_5,
    rating_6 = :rating_6,
    rating_7 = :rating_7,
    rating_8 = :rating_8,
    comments_1 = :comments_1,
    comments_2 = :comments_2,
    comments_3 = :comments_3,
    comments_4 = :comments_4,
    comments_5 = :comments_5,
    comments_6 = :comments_6,
    comments_7 = :comments_7,
    comments_8 = :comments_8,
    first_visit_date = :first_visit_date,
    follow_up_visit_dates = :follow_up_visit_dates,
    signature_name = :signature_name
WHERE id = :id
SQL;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateData);
        return (int)$existingId;
    }

    $sql = <<<SQL
INSERT INTO vice_chair_chapter_visit_reports (
    report_date,
    chapter_name,
    location_name,
    chair_name,
    secretary_name,
    treasurer_name,
    hsc_chair_name,
    other_name,
    overall_grade,
    amount_bank_account,
    amount_dues_owed,
    amount_out_loans,
    chapter_meeting,
    comments_top,
    rating_1,
    rating_2,
    rating_3,
    rating_4,
    rating_5,
    rating_6,
    rating_7,
    rating_8,
    comments_1,
    comments_2,
    comments_3,
    comments_4,
    comments_5,
    comments_6,
    comments_7,
    comments_8,
    first_visit_date,
    follow_up_visit_dates,
    signature_name
) VALUES (
    :report_date,
    :chapter_name,
    :location_name,
    :chair_name,
    :secretary_name,
    :treasurer_name,
    :hsc_chair_name,
    :other_name,
    :overall_grade,
    :amount_bank_account,
    :amount_dues_owed,
    :amount_out_loans,
    :chapter_meeting,
    :comments_top,
    :rating_1,
    :rating_2,
    :rating_3,
    :rating_4,
    :rating_5,
    :rating_6,
    :rating_7,
    :rating_8,
    :comments_1,
    :comments_2,
    :comments_3,
    :comments_4,
    :comments_5,
    :comments_6,
    :comments_7,
    :comments_8,
    :first_visit_date,
    :follow_up_visit_dates,
    :signature_name
)
SQL;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    return (int)$pdo->lastInsertId();
}

function get_history(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT id, report_date, chapter_name, location_name, updated_at
        FROM vice_chair_chapter_visit_reports
        ORDER BY report_date DESC, id DESC
    ");

    return $stmt->fetchAll();
}

function get_report(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM vice_chair_chapter_visit_reports WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

try {
    $pdo = get_pdo($dbHost, $dbName, $dbUser, $dbPass);
    ensure_tables($pdo);

    if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
        json_response([
            'ok' => true,
            'history' => get_history($pdo),
        ]);
    }

    if (isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) {
            json_response(['ok' => false, 'message' => 'Invalid history record.'], 422);
        }

        $report = get_report($pdo, $id);
        if (!$report) {
            json_response(['ok' => false, 'message' => 'Record not found.'], 404);
        }

        json_response(['ok' => true, 'report' => $report]);
    }

    if (isset($_GET['ajax']) && $_GET['ajax'] === 'autosave' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $payload = get_payload();
        $id = save_report($pdo, $payload);
        json_response([
            'ok' => true,
            'id' => $id,
            'message' => 'Saved',
            'saved_at' => date('Y-m-d H:i:s'),
        ]);
    }
} catch (Throwable $e) {
    if (isset($_GET['ajax'])) {
        json_response([
            'ok' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
    $fatalError = $e->getMessage();
}

$form = [
    'report_date' => date('Y-m-d'),
    'chapter_name' => '',
    'location_name' => '',
    'chair_name' => '',
    'secretary_name' => '',
    'treasurer_name' => '',
    'hsc_chair_name' => '',
    'other_name' => '',
    'overall_grade' => '',
    'amount_bank_account' => '',
    'amount_dues_owed' => '',
    'amount_out_loans' => '',
    'chapter_meeting' => '',
    'comments_top' => '',
    'rating_1' => '',
    'rating_2' => '',
    'rating_3' => '',
    'rating_4' => '',
    'rating_5' => '',
    'rating_6' => '',
    'rating_7' => '',
    'rating_8' => '',
    'comments_1' => '',
    'comments_2' => '',
    'comments_3' => '',
    'comments_4' => '',
    'comments_5' => '',
    'comments_6' => '',
    'comments_7' => '',
    'comments_8' => '',
    'first_visit_date' => '',
    'follow_up_visit_dates' => '',
    'signature_name' => '',
];

if (!empty($_GET['id']) && empty($fatalError ?? null)) {
    try {
        $loaded = get_report($pdo, (int)$_GET['id']);
        if ($loaded) {
            $form = array_merge($form, $loaded);
        }
    } catch (Throwable $e) {
        $fatalError = $e->getMessage();
    }
}

$history = [];
if (empty($fatalError ?? null)) {
    try {
        $history = get_history($pdo);
    } catch (Throwable $e) {
        $fatalError = $e->getMessage();
    }
}

$questions = [
    1 => 'READING OF 3 CHAPTER PRINCIPLES:',
    2 => 'READING OF MINUTES:',
    3 => 'PRESENTATION OF TREASURER REPORT:',
    4 => 'PRESENTATION OF CHAIR REPORT:',
    5 => 'PRESENTATION OF HSC REPORT:',
    6 => 'MAINTAINS GUIDELINES AND TRADITIONS:',
    7 => 'HANDLING OF CHAPTER BUSINESS/ISSUES:',
    8 => 'ORGANIZATION, ORDER, & STRUCTURE:',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vice-Chair - Chapter Visit</title>
    <style>
        :root {
            --page-w: 8.5in;
            --page-h: 11in;
            --ink: #111;
            --line: #111;
            --muted: #666;
            --bg: #e6e6e6;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            padding: 18px;
        }

        .toolbar {
            width: var(--page-w);
            margin: 0 auto 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
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
            padding: 0 10px;
            font-size: 13px;
        }

        .status {
            font-size: 12px;
            color: var(--muted);
            min-width: 120px;
            text-align: right;
        }

        .page {
            width: var(--page-w);
            min-height: var(--page-h);
            margin: 0 auto;
            background: #fff;
            padding: .33in .36in .30in;
            box-shadow: 0 2px 16px rgba(0,0,0,.18);
        }

        .error-box {
            margin-bottom: 10px;
            border: 1px solid #900;
            background: #fee;
            padding: 8px 10px;
            font-size: 13px;
        }

        .top-note {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
            margin-bottom: 6px;
        }

        .score-note {
            text-align: center;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .title-row {
            display: grid;
            grid-template-columns: 84px 1fr 1fr;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .logo-wrap {
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-wrap img {
            max-width: 78px;
            max-height: 56px;
            object-fit: contain;
        }

        .main-title {
            grid-column: 2 / 4;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: .5px;
            line-height: 1;
        }

        .line-grid-2,
        .line-grid-3 {
            display: grid;
            gap: 8px 12px;
            margin-bottom: 7px;
        }

        .line-grid-2 { grid-template-columns: 1fr 1fr; }
        .line-grid-3 { grid-template-columns: 1fr 1fr 1fr; }

        .line-field {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            min-height: 22px;
        }

        .line-field .label {
            white-space: nowrap;
            font-size: 13px;
            font-weight: 700;
        }

        .line-input,
        .line-textarea,
        .line-select {
            width: 100%;
            border: 0;
            border-bottom: 1px solid var(--line);
            outline: none;
            font-size: 13px;
            padding: 1px 2px 2px;
            background: transparent;
            min-height: 18px;
        }

        .line-select {
            appearance: none;
            -webkit-appearance: none;
            background: transparent;
        }

        .financial-section {
            display: block;
            margin-bottom: 8px;
        }

        .financial-field {
            margin-bottom: 10px;
        }

        .financial-field label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 3px;
            letter-spacing: .15px;
        }

        .financial-input {
            width: 100%;
            border: 0;
            border-bottom: 2px solid var(--line);
            outline: none;
            font-size: 14px;
            padding: 4px 2px 3px;
            background: transparent;
            min-height: 24px;
        }

        .financial-input:focus {
            border-bottom-width: 2px;
        }

        .comments-block {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 8px;
            align-items: stretch;
            margin: 3px 0 8px;
        }

        .comments-block .label {
            font-size: 13px;
            font-weight: 700;
            align-self: start;
            padding-top: 4px;
        }

        .line-textarea {
            resize: none;
            min-height: 28px;
            border: 1px solid var(--line);
            padding: 4px 6px;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 2px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid var(--line);
            padding: 4px 5px;
            vertical-align: middle;
            font-size: 12px;
        }

        .report-table thead th {
            text-align: center;
            font-weight: 700;
        }

        .report-table .col-item { width: 52%; }
        .report-table .col-rating { width: 14%; }
        .report-table .col-comments { width: 34%; }

        .question-label {
            font-weight: 700;
        }

        .rating-select {
            width: 100%;
            border: 0;
            background: transparent;
            font-size: 12px;
            text-align: center;
            outline: none;
            padding: 0;
            appearance: none;
            -webkit-appearance: none;
        }

        .cell-textarea {
            width: 100%;
            border: 0;
            resize: none;
            min-height: 24px;
            outline: none;
            font-size: 12px;
            padding: 0;
            background: transparent;
            overflow: hidden;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 8px;
        }

        .signature-row {
            margin-top: 6px;
        }

        .small {
            font-size: 12px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: .30in .33in .25in;
            }

            .line-input,
            .line-textarea,
            .line-select,
            .rating-select,
            .cell-textarea {
                color: #000 !important;
                -webkit-text-fill-color: #000;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-left">
            <label for="historySelect">History by date:</label>
            <select id="historySelect">
                <option value="">Current / New Form</option>
                <?php foreach ($history as $row): ?>
                    <option value="<?= (int)$row['id'] ?>">
                        <?= h($row['report_date']) ?><?= $row['chapter_name'] ? ' - ' . h($row['chapter_name']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="printBtn">Print</button>
        </div>
        <div class="toolbar-right">
            <div class="status" id="saveStatus"><?= !empty($fatalError ?? null) ? 'Database error' : 'Ready' ?></div>
        </div>
    </div>

    <div class="page">
        <?php if (!empty($fatalError ?? null)): ?>
            <div class="error-box">
                <strong>Database connection failed:</strong> <?= h($fatalError) ?>
            </div>
        <?php endif; ?>

        <div class="top-note">THIS REPORT TO BE GIVEN AT NEXT ASSOCIATION MEETING</div>
        <div class="score-note">1- Very Poor&nbsp;&nbsp; 2 - Poor&nbsp;&nbsp; 3 - Okay&nbsp;&nbsp; 4 - Good&nbsp;&nbsp; 5 - Excellent</div>

        <div class="title-row">
            <div class="logo-wrap">
                <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
            </div>
            <div class="main-title">CHAPTER VISIT REPORT</div>
        </div>

        <form id="reportForm" autocomplete="off">
            <div class="line-grid-2">
                <div class="line-field">
                    <span class="label">CHAPTER:</span>
                    <input class="line-input" type="text" name="chapter_name" value="<?= h($form['chapter_name']) ?>">
                </div>
                <div class="line-field">
                    <span class="label">LOCATION:</span>
                    <input class="line-input" type="text" name="location_name" value="<?= h($form['location_name']) ?>">
                </div>
            </div>

            <div class="line-grid-3">
                <div class="line-field">
                    <span class="label">CHAIR:</span>
                    <input class="line-input" type="text" name="chair_name" value="<?= h($form['chair_name']) ?>">
                </div>
                <div class="line-field">
                    <span class="label">SECRETARY:</span>
                    <input class="line-input" type="text" name="secretary_name" value="<?= h($form['secretary_name']) ?>">
                </div>
                <div class="line-field">
                    <span class="label">TREASURER:</span>
                    <input class="line-input" type="text" name="treasurer_name" value="<?= h($form['treasurer_name']) ?>">
                </div>
            </div>

            <div class="line-grid-2">
                <div class="line-field">
                    <span class="label">HSC CHAIR:</span>
                    <input class="line-input" type="text" name="hsc_chair_name" value="<?= h($form['hsc_chair_name']) ?>">
                </div>
                <div class="line-field">
                    <span class="label">OTHER:</span>
                    <input class="line-input" type="text" name="other_name" value="<?= h($form['other_name']) ?>">
                </div>
            </div>

            <div class="financial-section">
                <div class="financial-field">
                    <label for="overall_grade">OVERALL GRADE OF CHAPTER MEETING:</label>
                    <select class="financial-input" id="overall_grade" name="overall_grade">
                        <option value=""></option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= (string)$form['overall_grade'] === (string)$i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="financial-field">
                    <label for="amount_bank_account">AMOUNT IN BANK ACCOUNT:</label>
                    <input class="financial-input money-field" id="amount_bank_account" type="text" name="amount_bank_account" value="<?= h($form['amount_bank_account']) ?>">
                </div>

                <div class="financial-field">
                    <label for="amount_dues_owed">AMOUNT OF DUES OWED TO CHAPTER:</label>
                    <input class="financial-input money-field" id="amount_dues_owed" type="text" name="amount_dues_owed" value="<?= h($form['amount_dues_owed']) ?>">
                </div>
            </div>

            <div class="line-grid-2" style="margin-top: 4px;">
                <div class="line-field">
                    <span class="label">AMOUNT OUT IN LOANS:</span>
                    <input class="line-input money-field" type="text" name="amount_out_loans" value="<?= h($form['amount_out_loans']) ?>">
                </div>
                <div class="line-field">
                    <span class="label">DATE:</span>
                    <input class="line-input" type="date" name="report_date" value="<?= h($form['report_date']) ?>">
                </div>
            </div>

            <div class="comments-block">
                <div class="label">CHAPTER MEETING:</div>
                <textarea class="line-textarea" name="chapter_meeting" rows="2"><?= h($form['chapter_meeting']) ?></textarea>
            </div>

            <div class="comments-block" style="margin-top:0;">
                <div class="label">COMMENTS:</div>
                <textarea class="line-textarea" name="comments_top" rows="2"><?= h($form['comments_top']) ?></textarea>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th class="col-item">FINANCIAL INTEGRITY:</th>
                        <th class="col-rating">Rating</th>
                        <th class="col-comments">Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $n => $label): ?>
                        <tr>
                            <td><span class="question-label"><?= h($n . '. ' . $label) ?></span></td>
                            <td>
                                <select class="rating-select" name="rating_<?= $n ?>">
                                    <option value=""></option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= (string)$form['rating_' . $n] === (string)$i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td>
                                <textarea class="cell-textarea autosize" name="comments_<?= $n ?>" rows="2"><?= h($form['comments_' . $n]) ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="bottom-grid">
                <div class="line-field">
                    <span class="label">DATE OF 1ST VISIT:</span>
                    <input class="line-input" type="date" name="first_visit_date" value="<?= h($form['first_visit_date']) ?>">
                </div>
                <div class="line-field">
                    <span class="label">DATE OF FOLLOW UP VISIT(S):</span>
                    <input class="line-input" type="text" name="follow_up_visit_dates" value="<?= h($form['follow_up_visit_dates']) ?>">
                </div>
            </div>

            <div class="signature-row">
                <div class="line-field">
                    <span class="label">SIGNATURE:</span>
                    <input class="line-input" type="text" name="signature_name" value="<?= h($form['signature_name']) ?>">
                </div>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('reportForm');
        const historySelect = document.getElementById('historySelect');
        const saveStatus = document.getElementById('saveStatus');
        const printBtn = document.getElementById('printBtn');
        let saveTimer = null;
        let saving = false;

        function setStatus(text) {
            saveStatus.textContent = text;
        }

        function autosize(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.max(el.scrollHeight, 24) + 'px';
        }

        document.querySelectorAll('.autosize, .line-textarea').forEach(autosize);

        document.addEventListener('input', (e) => {
            if (e.target.matches('.autosize, .line-textarea')) {
                autosize(e.target);
            }
        });

        function serializeForm() {
            const fd = new FormData(form);
            return fd;
        }

        async function refreshHistory() {
            try {
                const res = await fetch('?ajax=history', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                const currentValue = historySelect.value;
                historySelect.innerHTML = '<option value="">Current / New Form</option>';

                data.history.forEach(row => {
                    const option = document.createElement('option');
                    option.value = row.id;
                    option.textContent = row.report_date + (row.chapter_name ? ' - ' + row.chapter_name : '');
                    historySelect.appendChild(option);
                });

                if ([...historySelect.options].some(o => o.value === currentValue)) {
                    historySelect.value = currentValue;
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function autosaveNow() {
            if (saving) return;
            const dateField = form.querySelector('[name="report_date"]');
            if (!dateField.value) {
                setStatus('Enter a date to save');
                return;
            }

            saving = true;
            setStatus('Saving...');

            try {
                const res = await fetch('?ajax=autosave', {
                    method: 'POST',
                    body: serializeForm()
                });

                const data = await res.json();
                if (!data.ok) {
                    setStatus(data.message || 'Save failed');
                    saving = false;
                    return;
                }

                setStatus('Saved ' + data.saved_at);
                await refreshHistory();
            } catch (err) {
                console.error(err);
                setStatus('Save failed');
            }

            saving = false;
        }

        function queueSave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosaveNow, 700);
        }

        form.addEventListener('input', queueSave);
        form.addEventListener('change', queueSave);

        historySelect.addEventListener('change', async function() {
            const id = this.value;
            if (!id) {
                window.location.href = window.location.pathname;
                return;
            }

            setStatus('Loading...');
            try {
                const res = await fetch('?ajax=load&id=' + encodeURIComponent(id), { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) {
                    setStatus(data.message || 'Load failed');
                    return;
                }

                Object.entries(data.report).forEach(([key, value]) => {
                    const field = form.elements.namedItem(key);
                    if (!field) return;
                    field.value = value ?? '';
                    if (field.matches('.autosize, .line-textarea')) {
                        autosize(field);
                    }
                });

                setStatus('Loaded ' + (data.report.report_date || 'record'));
            } catch (err) {
                console.error(err);
                setStatus('Load failed');
            }
        });

        printBtn.addEventListener('click', () => window.print());

        document.querySelectorAll('.money-field').forEach((field) => {
            field.addEventListener('blur', () => {
                const cleaned = String(field.value).replace(/[^0-9.\-]/g, '');
                if (cleaned === '' || isNaN(cleaned)) {
                    field.value = '';
                    queueSave();
                    return;
                }
                field.value = Number(cleaned).toFixed(2);
                queueSave();
            });
        });
    </script>
</body>
</html>
