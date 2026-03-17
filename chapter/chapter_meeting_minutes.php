<?php
/**
 * Oxford House Colorado Chapter 14 Meeting Minutes
 * Fillable + MySQL save/load by meeting date
 * Manual save + auto-save + update existing record
 * Includes revised Comptroller Report with automatic math
 * Tighter print layout to reduce total printed pages without cutting content off
 */
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

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
    CREATE TABLE IF NOT EXISTS chapter_meeting_minutes (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        meeting_date VARCHAR(50) NOT NULL,
        form_data LONGTEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_meeting_date (meeting_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function checked(array $source, string $name, string $value): string
{
    return (($source[$name] ?? '') === $value) ? 'checked' : '';
}

function is_checked(array $source, string $name): string
{
    return !empty($source[$name]) ? 'checked' : '';
}

function request_value(array $source, string $key, string $default = ''): string
{
    return h($source[$key] ?? $default);
}

function save_form(PDO $pdo, array $payload): array
{
    $meetingDate = trim((string)($payload['meeting_date'] ?? ''));
    if ($meetingDate === '') {
        return [
            'ok' => false,
            'message' => 'Please enter a meeting date before saving.',
        ];
    }

    $checkboxFields = [
        'minutes_accept_checked',
        'treasurer_accept_checked',
        'chairperson_report_accept_checked',
        'vicechair_report_accept_checked',
        'housing_report_accept_checked',
        'outreach_report_accept_checked',
        'comptroller_accept_checked',
        'reentry_report_accept_checked',
        'fundraising_report_accept_checked',
        'alumni_accept_checked',
        'old_business_accept_checked',
        'new_business_accept_checked',
        'adjourn_meeting_checked',
    ];

    for ($i = 0; $i < 10; $i++) {
        $checkboxFields[] = 'comp_warning_' . $i;
        $checkboxFields[] = 'comp_contract_' . $i;
    }

    foreach ($checkboxFields as $field) {
        $payload[$field] = isset($payload[$field]) ? '1' : '0';
    }

    unset($payload['action'], $payload['history_date'], $payload['autosave']);

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return [
            'ok' => false,
            'message' => 'Unable to encode form data.',
        ];
    }

    $stmt = $pdo->prepare("
        INSERT INTO chapter_meeting_minutes (meeting_date, form_data)
        VALUES (:meeting_date, :form_data)
        ON DUPLICATE KEY UPDATE form_data = VALUES(form_data)
    ");
    $stmt->execute([
        ':meeting_date' => $meetingDate,
        ':form_data' => $json,
    ]);

    $readStmt = $pdo->prepare("
        SELECT id, updated_at
        FROM chapter_meeting_minutes
        WHERE meeting_date = :meeting_date
        LIMIT 1
    ");
    $readStmt->execute([':meeting_date' => $meetingDate]);
    $savedRow = $readStmt->fetch();

    return [
        'ok' => true,
        'message' => 'Meeting minutes saved successfully.',
        'meeting_date' => $meetingDate,
        'id' => $savedRow['id'] ?? null,
        'updated_at' => $savedRow['updated_at'] ?? null,
    ];
}

/* =========================
   STATIC DATA
========================= */
$officerRows = [
    'Chair',
    'Vice-Chair',
    'Secretary',
    'Treasurer',
    'Housing Serv. Chair',
    'Outreach Chair',
    'Re-Entry Chair',
    'Fundraising Chair',
    'Alumni Coordinator',
];

$reportSectionsPage2 = [
    'Chairperson Report' => 'chairperson_report',
    'Vice-Chair Report' => 'vicechair_report',
    'Housing Services Chair Report' => 'housing_report',
    'Outreach Report' => 'outreach_report',
    'Re-Entry Chair Report' => 'reentry_report',
    'Fundraising Chair Report' => 'fundraising_report',
];

/* =========================
   AJAX AUTO SAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'autosave')) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $result = save_form($pdo, $_POST);
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => 'Auto-save failed: ' . $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

/* =========================
   NORMAL PAGE REQUEST
========================= */
$message = '';
$formData = [];
$selectedDate = trim((string)($_GET['history_date'] ?? $_POST['history_date'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'save')) {
    try {
        $result = save_form($pdo, $_POST);
        $message = $result['message'];
        $selectedDate = trim((string)($_POST['meeting_date'] ?? ''));
        $formData = $_POST;
        unset($formData['action']);

        if (!($result['ok'] ?? false)) {
            $formData = $_POST;
        }
    } catch (Throwable $e) {
        $message = 'Save failed: ' . $e->getMessage();
        $formData = $_POST;
    }
}

if ($selectedDate !== '') {
    $stmt = $pdo->prepare("
        SELECT form_data
        FROM chapter_meeting_minutes
        WHERE meeting_date = :meeting_date
        LIMIT 1
    ");
    $stmt->execute([':meeting_date' => $selectedDate]);
    $row = $stmt->fetch();

    if ($row) {
        $decoded = json_decode((string)$row['form_data'], true);
        if (is_array($decoded)) {
            $formData = $decoded;
        }
    }
}

$historyRows = $pdo->query("
    SELECT meeting_date
    FROM chapter_meeting_minutes
    ORDER BY STR_TO_DATE(meeting_date, '%m/%d/%Y') DESC, updated_at DESC, meeting_date DESC
")->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oxford House Colorado Chapter 14 Meeting Minutes</title>
    <style>
        :root {
            --page-w: 8.5in;
            --page-h: 11in;
            --page-pad: 0.12in;
            --border: 2px solid #222;
            --thin: 1px solid #222;
            --text: #111;
            --font-main: "Arial Narrow", Arial, Helvetica, sans-serif;
            --ok: #1c6b2a;
            --warn: #8a5a00;
            --err: #9a1f1f;
            --comp-green: #157a2c;
            --comp-red: #b11717;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #d9d9d9;
            color: var(--text);
            font-family: var(--font-main);
        }

        body {
            line-height: 1.06;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f2f2f2;
            border-bottom: 1px solid #bbb;
            padding: 12px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn,
        .history-select {
            border: 1px solid #111;
            background: #fff;
            color: #111;
            padding: 10px 16px;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .btn { cursor: pointer; }

        .history-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
        }

        .history-select {
            min-width: 220px;
            cursor: pointer;
        }

        .status {
            width: 100%;
            text-align: center;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
        }

        .status.ok { color: var(--ok); }
        .status.warn { color: var(--warn); }
        .status.err { color: var(--err); }

        .wrapper {
            padding: 10px 0 20px;
        }

        .page {
            width: var(--page-w);
            min-height: var(--page-h);
            margin: 0 auto 8px;
            background: #efefef;
            padding: var(--page-pad);
            box-shadow: 0 0 0 1px #c8c8c8;
            position: relative;
            overflow: hidden;
        }

        .page:last-child {
            margin-bottom: 0;
        }

        .title {
            text-align: center;
            font-weight: 900;
            font-size: 20px;
            letter-spacing: .2px;
            margin: 0 0 6px;
            text-transform: uppercase;
        }

        .topline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 20px;
            margin: 2px 4px 8px;
        }

        .label-line {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
        }

        .line-input {
            flex: 1;
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            min-height: 22px;
            font: 700 15px var(--font-main);
            color: #111;
            padding: 0 4px;
            outline: none;
            text-transform: uppercase;
        }

        .box {
            border: var(--border);
            margin-bottom: 8px;
            background: rgba(255,255,255,.08);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .section-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 2px 6px;
            border-bottom: var(--thin);
            text-align: center;
            letter-spacing: .1px;
        }

        .section-title.left { text-align: left; }

        .section-title small {
            font-size: 11px;
            font-weight: 700;
        }

        .roll-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            padding: 8px 8px 4px;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.grid th,
        table.grid td {
            border: var(--thin);
            padding: 0;
            height: 28px;
            vertical-align: middle;
            background: transparent;
        }

        table.grid th {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            padding: 3px 4px;
        }

        table.grid .rowlabel {
            width: 50%;
            padding: 3px 5px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .cell-input {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            font: 700 13px var(--font-main);
            padding: 3px 5px;
            outline: none;
            text-transform: uppercase;
        }

        .plain-lines { padding: 6px 10px 8px; }

        .plain-line {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 7px 0;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
        }

        .yn-row {
            margin: 4px 4px 8px;
            font-weight: 900;
            font-size: 15px;
            text-transform: uppercase;
        }

        .radio-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .radio-group label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }

        input[type="radio"] {
            width: 15px;
            height: 15px;
            accent-color: #111;
        }

        .mmsp-check {
            width: 15px;
            height: 15px;
            accent-color: #111;
            margin: 0;
        }

        .minutes-box { padding: 6px 6px 0; }

        .block-label {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .single-line {
            width: 100%;
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            padding: 3px 5px;
            outline: none;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
            min-height: 24px;
        }

        textarea {
            width: 100%;
            border: none;
            background: transparent;
            resize: none;
            outline: none;
            font: 700 14px/1.1 var(--font-main);
            padding: 4px 6px;
            text-transform: uppercase;
            overflow: hidden;
        }

        .short-notes { min-height: 34px; }
        .comment-area { min-height: 82px; }
        .report-area-lg { min-height: 92px; }
        .report-area-md { min-height: 64px; }
        .report-area-sm { min-height: 34px; }
        .business-old { min-height: 115px; }
        .business-new { min-height: 150px; }

        .accept-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 6px 5px;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 14px;
        }

        .accept-row .lefttxt { flex: 1; }

        .mmsp {
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .mmsp input[type="text"] {
            width: 82px;
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            font: 700 14px var(--font-main);
            outline: none;
            text-transform: uppercase;
        }

        .treasurer-wrap { padding: 0 0 3px; }

        .account-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            padding: 0 8px;
        }

        .moneyline {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 10px 5px;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .moneyline .moneyfill,
        .moneyline .blankfill {
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            outline: none;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
            height: 22px;
        }

        .moneyline .moneyfill { width: 100px; }
        .moneyline .blankfill { width: 58px; }

        .comments-head {
            padding: 6px 10px 2px;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .comments-head small {
            font-size: 11px;
            font-weight: 700;
        }

        .report-box {
            border: var(--border);
            margin-bottom: 8px;
            background: rgba(255,255,255,.08);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .report-box .section-title { padding: 2px 6px; }
        .report-content { min-height: 50px; }

        .footer-lines {
            margin-top: 6px;
            padding: 0 2px;
        }

        .footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .footer-row .fill {
            flex: 0 0 100px;
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            outline: none;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
        }

        .sigline {
            flex: 1;
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            outline: none;
            font: 700 14px var(--font-main);
            text-transform: uppercase;
        }

        .attach-note {
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 0;
        }

        .comptroller-sheet {
            border: 1px solid #c9c9c9;
            padding: 5px 5px 4px;
            margin-bottom: 8px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .comptroller-head {
            display: grid;
            grid-template-columns: 60px 1fr auto;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .comptroller-heading {
            font-size: 17px;
            font-weight: 700;
            line-height: 1;
        }

        .comptroller-rate {
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .comptroller-rate input {
            width: 56px;
            border: none;
            border-bottom: 1px solid #111;
            background: transparent;
            outline: none;
            font: 700 14px var(--font-main);
            text-align: center;
        }

        .comptroller-ledger {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .comptroller-ledger th,
        .comptroller-ledger td {
            border: 1px solid #d7d7d7;
            padding: 0;
        }

        .comptroller-ledger th {
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            padding: 4px 2px;
            line-height: 1.05;
        }

        .comptroller-ledger td { height: 24px; }

        .comp-input {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            outline: none;
            font: 700 12px var(--font-main);
            padding: 2px 4px;
            text-transform: uppercase;
        }

        .comp-center { text-align: center; }
        .comp-money { text-align: center; }

        .comp-negative {
            color: var(--comp-green) !important;
            font-weight: 900;
        }

        .comp-positive {
            color: var(--comp-red) !important;
            font-weight: 900;
        }

        .comp-zero {
            color: inherit !important;
            font-weight: 700;
        }

        .comptroller-summary-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            padding: 5px 2px 2px;
        }

        .comptroller-summary-item {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }

        .comptroller-summary-item input {
            flex: 1;
            border: none;
            border-bottom: 1px solid #111;
            background: transparent;
            outline: none;
            font: 700 12px var(--font-main);
            text-align: right;
        }

        .comptroller-comments-title {
            padding: 3px 2px 2px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .comptroller-comments-box {
            min-height: 52px;
            border-top: 1px solid #d7d7d7;
        }

        input[readonly] {
            color: #111;
        }

        @page {
            size: Letter portrait;
            margin: 0;
        }

        @media print {
            :root {
                --page-pad: 0.08in;
            }

            html, body {
                width: 8.5in;
                min-width: 8.5in;
                max-width: 8.5in;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body {
                overflow: visible !important;
            }

            .toolbar {
                display: none !important;
            }

            .wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }

            .page {
                width: 8.5in !important;
                min-height: 11in !important;
                height: auto !important;
                margin: 0 !important;
                padding: var(--page-pad) !important;
                box-shadow: none !important;
                border: none !important;
                background: #fff !important;
                page-break-after: always;
                break-after: page;
                page-break-inside: avoid;
                break-inside: avoid;
                overflow: visible !important;
            }

            .page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            .title { font-size: 18px !important; margin-bottom: 4px !important; }
            .topline { margin-bottom: 6px !important; }
            .box, .report-box, .comptroller-sheet { margin-bottom: 6px !important; }

            .section-title {
                font-size: 14px !important;
                padding: 2px 5px !important;
            }

            .label-line,
            .plain-line,
            .yn-row,
            .moneyline,
            .comments-head,
            .footer-row,
            .accept-row {
                font-size: 12px !important;
            }

            .line-input,
            .single-line,
            .mmsp input[type="text"],
            .footer-row .fill,
            .sigline,
            .moneyline .moneyfill,
            .moneyline .blankfill,
            .cell-input,
            textarea {
                font-size: 12px !important;
            }

            table.grid th { font-size: 11px !important; }
            table.grid .rowlabel { font-size: 10px !important; }
            table.grid th,
            table.grid td { height: 24px !important; }

            .roll-grid { gap: 10px !important; padding: 6px 6px 3px !important; }

            .short-notes { min-height: 28px !important; }
            .comment-area { min-height: 60px !important; }
            .report-area-md { min-height: 48px !important; }
            .report-area-sm { min-height: 26px !important; }
            .business-old { min-height: 86px !important; }
            .business-new { min-height: 110px !important; }

            .comptroller-ledger th { font-size: 8px !important; }
            .comptroller-ledger td { height: 21px !important; }
            .comp-input,
            .comptroller-summary-item input { font-size: 10px !important; }

            .comptroller-comments-box { min-height: 40px !important; }
            .attach-note { font-size: 10px !important; }

            .box,
            .report-box,
            .comptroller-sheet,
            .footer-lines {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            input,
            textarea,
            select {
                color: #000 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            textarea {
                overflow: hidden !important;
            }

            a,
            button {
                color: inherit !important;
                text-decoration: none !important;
            }
        }
    </style>
</head>
<body>
<form method="post" id="minutesForm" autocomplete="off">
    <input type="hidden" name="action" id="formAction" value="save">

    <div class="toolbar">
        <button class="btn" type="submit">Save Form</button>
        <button class="btn" type="button" onclick="window.print()">Print Form</button>
        <button class="btn" type="button" onclick="clearFormData()">Clear Form</button>

        <div class="history-wrap">
            <label for="history_date">History By Date</label>
            <select class="history-select" id="history_date" name="history_date" onchange="loadHistory(this.value)">
                <option value="">Select Saved Date</option>
                <?php foreach ($historyRows as $historyRow): ?>
                    <option value="<?= h($historyRow['meeting_date']) ?>" <?= $selectedDate === $historyRow['meeting_date'] ? 'selected' : '' ?>>
                        <?= h($historyRow['meeting_date']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="status <?= $message !== '' ? 'ok' : 'warn' ?>" id="saveStatus">
            <?= $message !== '' ? h($message) : 'Auto-save ready. Enter meeting date to enable auto-save.' ?>
        </div>
    </div>

    <div class="wrapper">
        <section class="page">
            <div class="title">Oxford House Colorado Chapter 14 Meeting Minutes</div>

            <div class="topline">
                <div class="label-line">
                    Date:
                    <input class="line-input" name="meeting_date" id="meeting_date" value="<?= request_value($formData, 'meeting_date') ?>">
                </div>
                <div class="label-line">
                    Start Time:
                    <input class="line-input" name="start_time" value="<?= request_value($formData, 'start_time') ?>">
                </div>
            </div>

            <div class="box">
                <div class="section-title left">Roll Call: <small>(By Secretary)</small></div>

                <div class="roll-grid">
                    <table class="grid">
                        <tr><th colspan="2">Chapter Officers</th></tr>
                        <?php foreach ($officerRows as $i => $row): ?>
                            <tr>
                                <td class="rowlabel"><?= h($row) ?></td>
                                <td><input class="cell-input" name="officer_<?= $i ?>" value="<?= request_value($formData, 'officer_' . $i) ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <table class="grid">
                        <tr><th colspan="2">Houses</th></tr>
                        <?php for ($i = 0; $i < 8; $i++): ?>
                            <tr>
                                <td><input class="cell-input" name="house_<?= $i ?>" value="<?= request_value($formData, 'house_' . $i) ?>"></td>
                                <td><input class="cell-input" name="house_status_<?= $i ?>" value="<?= request_value($formData, 'house_status_' . $i) ?>"></td>
                            </tr>
                        <?php endfor; ?>
                    </table>
                </div>

                <div class="plain-lines">
                    <div class="plain-line">
                        Absent:
                        <input class="line-input" name="absent" value="<?= request_value($formData, 'absent') ?>">
                    </div>
                    <div class="plain-line">
                        Guests:
                        <input class="line-input" name="guests" value="<?= request_value($formData, 'guests') ?>">
                    </div>
                </div>
            </div>

            <div class="yn-row">
                Principles Read:
                <span class="radio-group">
                    <label><input type="radio" name="principles_read" value="Y" <?= checked($formData, 'principles_read', 'Y') ?>> Y</label>
                    <label><input type="radio" name="principles_read" value="N" <?= checked($formData, 'principles_read', 'N') ?>> N</label>
                </span>
            </div>

            <div class="box">
                <div class="section-title left">Reading of Previous Minutes: <small>(By Secretary)</small></div>

                <div class="minutes-box">
                    <div class="block-label">Corrections:</div>
                    <textarea class="short-notes" name="corrections" oninput="autoGrow(this)"><?= request_value($formData, 'corrections') ?></textarea>
                    <input class="single-line" name="minutes_line" value="<?= request_value($formData, 'minutes_line') ?>">
                </div>

                <div class="accept-row">
                    <div class="lefttxt">Accept Minutes as Read or Corrected:</div>
                    <div class="mmsp">
                        <label><input class="mmsp-check" type="checkbox" name="minutes_accept_checked" value="1" <?= is_checked($formData, 'minutes_accept_checked') ?>></label>
                        MM/S/P
                        <input type="text" name="minutes_accept" value="<?= request_value($formData, 'minutes_accept') ?>">
                    </div>
                </div>
            </div>

            <div class="box treasurer-wrap">
                <div class="section-title">Treasurer's Report</div>

                <div class="account-grid">
                    <table class="grid">
                        <tr><th colspan="2">Checking Account</th></tr>
                        <tr>
                            <td class="rowlabel">Beginning Balance</td>
                            <td><input class="cell-input calc-money" id="checking_beginning" name="checking_beginning" value="<?= request_value($formData, 'checking_beginning') ?>"></td>
                        </tr>
                        <tr>
                            <td class="rowlabel">Last Month Deposit $</td>
                            <td><input class="cell-input calc-money" id="checking_deposit" name="checking_deposit" value="<?= request_value($formData, 'checking_deposit') ?>"></td>
                        </tr>
                        <tr>
                            <td class="rowlabel">Last Month Spent $</td>
                            <td><input class="cell-input calc-money" id="checking_spent" name="checking_spent" value="<?= request_value($formData, 'checking_spent') ?>"></td>
                        </tr>
                        <tr>
                            <td class="rowlabel">Current Balance $</td>
                            <td><input class="cell-input" id="checking_current" name="checking_current" value="<?= request_value($formData, 'checking_current') ?>" readonly></td>
                        </tr>
                    </table>

                    <table class="grid">
                        <tr><th colspan="2">Savings Account</th></tr>
                        <tr>
                            <td class="rowlabel">Beginning Balance $</td>
                            <td><input class="cell-input calc-money" id="savings_beginning" name="savings_beginning" value="<?= request_value($formData, 'savings_beginning') ?>"></td>
                        </tr>
                        <tr>
                            <td class="rowlabel">Deposits / Interest $</td>
                            <td><input class="cell-input calc-money" id="savings_deposits" name="savings_deposits" value="<?= request_value($formData, 'savings_deposits') ?>"></td>
                        </tr>
                        <tr>
                            <td class="rowlabel">Withdrawels $</td>
                            <td><input class="cell-input calc-money" id="savings_withdrawals" name="savings_withdrawals" value="<?= request_value($formData, 'savings_withdrawals') ?>"></td>
                        </tr>
                        <tr>
                            <td class="rowlabel">Current Balance $</td>
                            <td><input class="cell-input" id="savings_current" name="savings_current" value="<?= request_value($formData, 'savings_current') ?>" readonly></td>
                        </tr>
                    </table>
                </div>

                <div class="moneyline">
                    Total Money Collected to Deposit: $
                    <input class="moneyfill" name="money_collected" value="<?= request_value($formData, 'money_collected') ?>">
                    .
                    <input class="blankfill" name="money_collected_suffix" value="<?= request_value($formData, 'money_collected_suffix') ?>">
                </div>

                <div class="comments-head">Comments : <small>(Expenditures with check number and "MM/S/P")</small></div>
                <textarea class="comment-area" name="treasurer_comments" oninput="autoGrow(this)"><?= request_value($formData, 'treasurer_comments') ?></textarea>

                <div class="accept-row">
                    <div class="lefttxt">Accept Treasurer's Report</div>
                    <div class="mmsp">
                        <label><input class="mmsp-check" type="checkbox" name="treasurer_accept_checked" value="1" <?= is_checked($formData, 'treasurer_accept_checked') ?>></label>
                        MM/S/P
                        <input type="text" name="treasurer_accept" value="<?= request_value($formData, 'treasurer_accept') ?>">
                    </div>
                </div>
            </div>
        </section>

        <section class="page">
            <?php
            $heights = [
                'Chairperson Report' => 'report-area-md',
                'Vice-Chair Report' => 'report-area-md',
                'Housing Services Chair Report' => 'report-area-md',
                'Outreach Report' => 'report-area-sm',
                'Re-Entry Chair Report' => 'report-area-sm',
                'Fundraising Chair Report' => 'report-area-sm',
            ];
            ?>

            <?php foreach ($reportSectionsPage2 as $title => $name): ?>
                <div class="report-box">
                    <div class="section-title"><?= h($title) ?></div>

                    <div class="report-content">
                        <textarea class="<?= h($heights[$title]) ?>" name="<?= h($name) ?>" oninput="autoGrow(this)"><?= request_value($formData, $name) ?></textarea>
                    </div>

                    <div class="accept-row">
                        <div class="lefttxt">Accept Report</div>
                        <div class="mmsp">
                            <label><input class="mmsp-check" type="checkbox" name="<?= h($name) ?>_accept_checked" value="1" <?= is_checked($formData, $name . '_accept_checked') ?>></label>
                            MM/S/P
                            <input type="text" name="<?= h($name) ?>_accept" value="<?= request_value($formData, $name . '_accept') ?>">
                        </div>
                    </div>
                </div>

                <?php if ($name === 'outreach_report'): ?>
                    <div class="comptroller-sheet">
                        <div class="section-title">Comptroller's Report</div>

                        <div class="comptroller-head">
                            <div class="comptroller-heading"></div>
                            <div></div>
                            <div class="comptroller-rate">
                                Base Dues $
                                <input type="text" id="comptroller_rate" name="comptroller_rate" value="<?= request_value($formData, 'comptroller_rate', '35') ?>">
                            </div>
                        </div>

                        <table class="comptroller-ledger">
                            <colgroup>
                                <col style="width:24%">
                                <col style="width:8%">
                                <col style="width:12%">
                                <col style="width:12%">
                                <col style="width:10%">
                                <col style="width:12%">
                                <col style="width:10%">
                                <col style="width:12%">
                            </colgroup>
                            <thead>
                            <tr>
                                <th>House</th>
                                <th>Beds</th>
                                <th>Past Due</th>
                                <th>Current</th>
                                <th>Fines</th>
                                <th>Total Owed</th>
                                <th>Paid</th>
                                <th>End Bal.</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php for ($i = 0; $i < 10; $i++): ?>
                                <tr>
                                    <td>
                                        <input class="comp-input" name="comp_house_<?= $i ?>" value="<?= request_value($formData, 'comp_house_' . $i) ?>">
                                    </td>
                                    <td>
                                        <input class="comp-input comp-calc comp-center" name="comp_beds_<?= $i ?>" data-row="<?= $i ?>" value="<?= request_value($formData, 'comp_beds_' . $i) ?>">
                                    </td>
                                    <td>
                                        <input class="comp-input comp-calc comp-money" name="comp_prev_bal_<?= $i ?>" data-row="<?= $i ?>" value="<?= request_value($formData, 'comp_prev_bal_' . $i) ?>">
                                    </td>
                                    <td>
                                        <input class="comp-input comp-money" id="comp_current_<?= $i ?>" name="comp_current_<?= $i ?>" value="<?= request_value($formData, 'comp_current_' . $i) ?>" readonly>
                                    </td>
                                    <td>
                                        <input class="comp-input comp-calc comp-money" name="comp_fines_<?= $i ?>" data-row="<?= $i ?>" value="<?= request_value($formData, 'comp_fines_' . $i) ?>">
                                    </td>
                                    <td>
                                        <input class="comp-input comp-money" id="comp_total_<?= $i ?>" name="comp_total_<?= $i ?>" value="<?= request_value($formData, 'comp_total_' . $i) ?>" readonly>
                                    </td>
                                    <td>
                                        <input class="comp-input comp-calc comp-money" name="comp_paid_<?= $i ?>" data-row="<?= $i ?>" value="<?= request_value($formData, 'comp_paid_' . $i) ?>">
                                    </td>
                                    <td>
                                        <input class="comp-input comp-money" id="comp_end_bal_<?= $i ?>" name="comp_end_bal_<?= $i ?>" value="<?= request_value($formData, 'comp_end_bal_' . $i) ?>" readonly>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>

                        <div class="comptroller-summary-row">
                            <div class="comptroller-summary-item">
                                Total Current $
                                <input type="text" id="comp_total_current" name="comp_total_current" value="<?= request_value($formData, 'comp_total_current') ?>" readonly>
                            </div>
                            <div class="comptroller-summary-item">
                                Total Owed $
                                <input type="text" id="comp_total_owed" name="comp_total_owed" value="<?= request_value($formData, 'comp_total_owed') ?>" readonly>
                            </div>
                            <div class="comptroller-summary-item">
                                Total Paid $
                                <input type="text" id="comp_total_paid" name="comp_total_paid" value="<?= request_value($formData, 'comp_total_paid') ?>" readonly>
                            </div>
                            <div class="comptroller-summary-item">
                                Ending Balance $
                                <input type="text" id="comp_total_end" name="comp_total_end" value="<?= request_value($formData, 'comp_total_end') ?>" readonly>
                            </div>
                        </div>

                        <div class="comptroller-comments-title">Comments / Actions</div>
                        <textarea class="comptroller-comments-box" name="comptroller_comments" oninput="autoGrow(this)"><?= request_value($formData, 'comptroller_comments') ?></textarea>

                        <div class="accept-row">
                            <div class="lefttxt">Accept Comptroller Report</div>
                            <div class="mmsp">
                                <label><input class="mmsp-check" type="checkbox" name="comptroller_accept_checked" value="1" <?= is_checked($formData, 'comptroller_accept_checked') ?>></label>
                                MM/S/P
                                <input type="text" name="comptroller_accept" value="<?= request_value($formData, 'comptroller_accept') ?>">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <section class="page">
            <div class="report-box">
                <div class="section-title">Alumni Coordinator Report</div>
                <div class="report-content">
                    <textarea class="report-area-sm" name="alumni_report" oninput="autoGrow(this)"><?= request_value($formData, 'alumni_report') ?></textarea>
                </div>
                <div class="accept-row">
                    <div class="lefttxt">Accept Report</div>
                    <div class="mmsp">
                        <label><input class="mmsp-check" type="checkbox" name="alumni_accept_checked" value="1" <?= is_checked($formData, 'alumni_accept_checked') ?>></label>
                        MM/S/P
                        <input type="text" name="alumni_accept" value="<?= request_value($formData, 'alumni_accept') ?>">
                    </div>
                </div>
            </div>

            <div class="report-box">
                <div class="section-title">Old Business</div>
                <div class="report-content">
                    <textarea class="business-old" name="old_business" oninput="autoGrow(this)"><?= request_value($formData, 'old_business') ?></textarea>
                </div>
                <div class="accept-row">
                    <div class="lefttxt">Accept Old Business</div>
                    <div class="mmsp">
                        <label><input class="mmsp-check" type="checkbox" name="old_business_accept_checked" value="1" <?= is_checked($formData, 'old_business_accept_checked') ?>></label>
                        MM/S/P
                        <input type="text" name="old_business_accept" value="<?= request_value($formData, 'old_business_accept') ?>">
                    </div>
                </div>
            </div>

            <div class="report-box">
                <div class="section-title">New Business</div>
                <div class="report-content">
                    <textarea class="business-new" name="new_business" oninput="autoGrow(this)"><?= request_value($formData, 'new_business') ?></textarea>
                </div>
                <div class="accept-row">
                    <div class="lefttxt">Accept New Business</div>
                    <div class="mmsp">
                        <label><input class="mmsp-check" type="checkbox" name="new_business_accept_checked" value="1" <?= is_checked($formData, 'new_business_accept_checked') ?>></label>
                        MM/S/P
                        <input type="text" name="new_business_accept" value="<?= request_value($formData, 'new_business_accept') ?>">
                    </div>
                </div>
            </div>

            <div class="footer-lines">
                <div class="footer-row">
                    <span>Adjourn Meeting</span>
                    <span class="mmsp">
                        <label><input class="mmsp-check" type="checkbox" name="adjourn_meeting_checked" value="1" <?= is_checked($formData, 'adjourn_meeting_checked') ?>></label>
                        MM/S/P
                        <input class="fill" type="text" name="adjourn_meeting" value="<?= request_value($formData, 'adjourn_meeting') ?>">
                    </span>
                </div>

                <div class="footer-row">
                    <span>Adjourn Time</span>
                    <input class="fill" name="adjourn_time" value="<?= request_value($formData, 'adjourn_time') ?>">
                </div>

                <div class="footer-row">
                    <span>Secretary Signature:</span>
                    <input class="sigline" name="secretary_signature" value="<?= request_value($formData, 'secretary_signature') ?>">
                </div>

                <div class="attach-note">**Attach House Summary Reports**</div>
            </div>
        </section>
    </div>
</form>

<script>
const form = document.getElementById('minutesForm');
const saveStatus = document.getElementById('saveStatus');
const meetingDateInput = document.getElementById('meeting_date');
const historySelect = document.getElementById('history_date');

let autoSaveTimer = null;
let isSaving = false;
let lastSerialized = '';

function autoGrow(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

function setStatus(message, type = 'warn') {
    saveStatus.textContent = message;
    saveStatus.className = 'status ' + type;
}

function parseMoney(value) {
    if (value === null || value === undefined) return 0;
    const cleaned = String(value).replace(/[^0-9.\-]/g, '').trim();
    const num = parseFloat(cleaned);
    return isNaN(num) ? 0 : num;
}

function formatMoney(value) {
    return Number(value || 0).toFixed(2);
}

function formatCompMoney(value) {
    const num = Number(value || 0);
    return num === 0 ? '0.00' : num.toFixed(2);
}

function formatEndBalanceDisplay(value) {
    const num = Number(value || 0);

    if (num < 0) {
        return '[' + formatCompMoney(Math.abs(num)) + ']';
    }

    if (num > 0) {
        return '-' + formatCompMoney(num);
    }

    return '0.00';
}

function applyBalanceColor(el, value) {
    if (!el) return;

    el.classList.remove('comp-negative', 'comp-positive', 'comp-zero');

    const num = Number(value || 0);

    if (num < 0) {
        el.classList.add('comp-negative');
    } else if (num > 0) {
        el.classList.add('comp-positive');
    } else {
        el.classList.add('comp-zero');
    }
}

function calculateBalances() {
    const checkingBeginning = parseMoney(document.getElementById('checking_beginning').value);
    const checkingDeposit = parseMoney(document.getElementById('checking_deposit').value);
    const checkingSpent = parseMoney(document.getElementById('checking_spent').value);
    const checkingCurrent = checkingBeginning + checkingDeposit - checkingSpent;
    document.getElementById('checking_current').value = formatMoney(checkingCurrent);

    const savingsBeginning = parseMoney(document.getElementById('savings_beginning').value);
    const savingsDeposits = parseMoney(document.getElementById('savings_deposits').value);
    const savingsWithdrawals = parseMoney(document.getElementById('savings_withdrawals').value);
    const savingsCurrent = savingsBeginning + savingsDeposits - savingsWithdrawals;
    document.getElementById('savings_current').value = formatMoney(savingsCurrent);
}

function calculateComptrollerSection() {
    const rate = parseMoney(document.getElementById('comptroller_rate')?.value || 0);

    let totalCurrent = 0;
    let totalOwed = 0;
    let totalPaid = 0;
    let totalEnd = 0;

    for (let row = 0; row < 10; row++) {
        const beds = parseMoney(document.querySelector(`[name="comp_beds_${row}"]`)?.value || 0);
        const prev = parseMoney(document.querySelector(`[name="comp_prev_bal_${row}"]`)?.value || 0);
        const fines = parseMoney(document.querySelector(`[name="comp_fines_${row}"]`)?.value || 0);
        const paid = parseMoney(document.querySelector(`[name="comp_paid_${row}"]`)?.value || 0);

        const current = beds * rate;
        const owed = prev + current + fines;
        const endBal = owed - paid;

        const currentEl = document.getElementById(`comp_current_${row}`);
        const totalEl = document.getElementById(`comp_total_${row}`);
        const endEl = document.getElementById(`comp_end_bal_${row}`);

        if (currentEl) currentEl.value = formatCompMoney(current);
        if (totalEl) totalEl.value = formatCompMoney(owed);

        if (endEl) {
            endEl.value = formatEndBalanceDisplay(endBal);
            applyBalanceColor(endEl, endBal);
        }

        totalCurrent += current;
        totalOwed += owed;
        totalPaid += paid;
        totalEnd += endBal;
    }

    const totalCurrentEl = document.getElementById('comp_total_current');
    const totalOwedEl = document.getElementById('comp_total_owed');
    const totalPaidEl = document.getElementById('comp_total_paid');
    const totalEndEl = document.getElementById('comp_total_end');

    if (totalCurrentEl) totalCurrentEl.value = formatCompMoney(totalCurrent);
    if (totalOwedEl) totalOwedEl.value = formatCompMoney(totalOwed);
    if (totalPaidEl) totalPaidEl.value = formatCompMoney(totalPaid);

    if (totalEndEl) {
        totalEndEl.value = formatEndBalanceDisplay(totalEnd);
        applyBalanceColor(totalEndEl, totalEnd);
    }
}

function serializeForm() {
    calculateBalances();
    calculateComptrollerSection();
    const fd = new FormData(form);
    fd.set('action', 'autosave');
    return fd;
}

function formSnapshot(fd) {
    return new URLSearchParams(fd).toString();
}

async function doAutoSave() {
    const meetingDate = meetingDateInput.value.trim();
    if (!meetingDate) {
        setStatus('Enter meeting date to enable auto-save.', 'warn');
        return;
    }

    if (isSaving) return;

    calculateBalances();
    calculateComptrollerSection();

    const fd = serializeForm();
    const snapshot = formSnapshot(fd);

    if (snapshot === lastSerialized) {
        return;
    }

    isSaving = true;
    setStatus('Saving...', 'warn');

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (result.ok) {
            lastSerialized = snapshot;
            setStatus('Auto-saved ' + (result.updated_at ? '(' + result.updated_at + ')' : ''), 'ok');

            let exists = false;
            for (let i = 0; i < historySelect.options.length; i++) {
                if (historySelect.options[i].value === meetingDate) {
                    exists = true;
                    historySelect.value = meetingDate;
                    break;
                }
            }

            if (!exists) {
                const option = document.createElement('option');
                option.value = meetingDate;
                option.textContent = meetingDate;
                option.selected = true;
                historySelect.appendChild(option);
            }
        } else {
            setStatus(result.message || 'Auto-save failed.', 'err');
        }
    } catch (error) {
        setStatus('Auto-save failed. Check connection.', 'err');
    } finally {
        isSaving = false;
    }
}

function queueAutoSave() {
    calculateBalances();
    calculateComptrollerSection();
    setStatus('Changes not saved yet...', 'warn');
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(doAutoSave, 800);
}

function loadHistory(value) {
    if (!value) return;
    const url = new URL(window.location.href);
    url.searchParams.set('history_date', value);
    window.location.href = url.toString();
}

function clearFormData() {
    if (!confirm('Clear the current form fields?')) {
        return;
    }

    form.reset();

    document.querySelectorAll('textarea').forEach(el => {
        el.value = '';
        autoGrow(el);
    });

    calculateBalances();
    calculateComptrollerSection();
    setStatus('Form cleared. Enter meeting date to auto-save again.', 'warn');
    lastSerialized = '';
}

document.querySelectorAll('textarea').forEach(autoGrow);

document.querySelectorAll('.calc-money').forEach(el => {
    el.addEventListener('input', () => {
        calculateBalances();
        queueAutoSave();
    });
    el.addEventListener('change', () => {
        calculateBalances();
        queueAutoSave();
    });
});

document.querySelectorAll('.comp-calc, #comptroller_rate').forEach(el => {
    el.addEventListener('input', () => {
        calculateComptrollerSection();
        queueAutoSave();
    });
    el.addEventListener('change', () => {
        calculateComptrollerSection();
        queueAutoSave();
    });
});

document.querySelectorAll('#minutesForm input, #minutesForm textarea, #minutesForm select').forEach(el => {
    if (el.name === 'history_date') return;
    if (el.classList.contains('calc-money')) return;
    if (el.classList.contains('comp-calc')) return;
    if (el.id === 'comptroller_rate') return;
    if (el.type === 'button' || el.type === 'submit' || el.type === 'reset' || el.type === 'hidden') return;

    const evt = (el.tagName === 'SELECT' || el.type === 'radio' || el.type === 'checkbox') ? 'change' : 'input';

    el.addEventListener(evt, () => {
        if (el.tagName === 'TEXTAREA') autoGrow(el);
        queueAutoSave();
    });

    if (evt !== 'change') {
        el.addEventListener('change', queueAutoSave);
    }
});

calculateBalances();
calculateComptrollerSection();

window.addEventListener('beforeprint', () => {
    document.querySelectorAll('textarea').forEach(autoGrow);
    calculateBalances();
    calculateComptrollerSection();
});

window.addEventListener('beforeunload', function () {
    if (autoSaveTimer) {
        clearTimeout(autoSaveTimer);
    }
});
</script>
</body>
</html>