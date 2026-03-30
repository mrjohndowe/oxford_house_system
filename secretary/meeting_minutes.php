<?php
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

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
   HELPERS
========================= */
function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function posted(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? (string)$_POST[$key] : $default;
}

function isChecked(string $key): bool
{
    return posted($key) === '1';
}

function radioChecked(string $key, string $value): bool
{
    return posted($key) === $value;
}

function todayDisplayDate(): string
{
    return date('m/d/Y');
}

function loadRecordIntoPost(array $record): void
{
    foreach ($record as $key => $value) {
        if (in_array($key, ['id', 'created_at', 'updated_at'], true)) {
            continue;
        }
        $_POST[$key] = (string)$value;
    }

    if (!isset($_POST['meeting_type'])) {
        if (!empty($record['meeting_type_regular'])) {
            $_POST['meeting_type'] = 'regular';
        } elseif (!empty($record['meeting_type_emergency'])) {
            $_POST['meeting_type'] = 'emergency';
        } elseif (!empty($record['meeting_type_interview'])) {
            $_POST['meeting_type'] = 'interview';
        }
    }

    $pairs = [
        'amendment_made'   => ['amend_yes' => 'yes', 'amend_no' => 'no'],
        'petty_receipts'   => ['petty_receipts_yes' => 'yes', 'petty_receipts_no' => 'no'],
        'narcan_kit'       => ['narcan_kit_y' => 'y', 'narcan_kit_n' => 'n'],
        'narcan_use'       => ['narcan_use_y' => 'y', 'narcan_use_n' => 'n'],
        'vacancy_updated'  => ['vacancy_updated_y' => 'y', 'vacancy_updated_n' => 'n'],
        'email_checked'    => ['email_checked_y' => 'y', 'email_checked_n' => 'n'],
        'voicemail_checked'=> ['voicemail_checked_y' => 'y', 'voicemail_checked_n' => 'n'],
        'checked_in_daily' => ['checked_in_daily_y' => 'y', 'checked_in_daily_n' => 'n'],
    ];

    foreach ($pairs as $groupName => $map) {
        if (isset($_POST[$groupName])) {
            continue;
        }
        foreach ($map as $column => $radioValue) {
            if (!empty($record[$column])) {
                $_POST[$groupName] = $radioValue;
                break;
            }
        }
    }

    for ($i = 1; $i <= 20; $i++) {
        $group = 'roll_' . $i . '_present';
        if (!isset($_POST[$group])) {
            if (!empty($record['roll_y_' . $i])) {
                $_POST[$group] = 'y';
            } elseif (!empty($record['roll_n_' . $i])) {
                $_POST[$group] = 'n';
            }
        }
    }
}

/* =========================
   FIELD LIST
========================= */
$textFields = [
    'house_name',
    'meeting_date',
    'start_time',
    'tradition_number',
    'treasurer_comments',
    'comptroller_comments',
    'ees_plan',
    'coordinator_report',
    'housing_services_report',
    'unfinished_business',
    'new_business',
    'adjourn_hour',
    'adjourn_min',
    'secretary_name',
    'secretary_signature',
    'secretary_signed_date',
];

$checkboxFields = [
    'minutes_accepted',
    'comptroller_mmsp',
    'coordinator_mmsp',
    'hsr_mmsp',
    'old_business_mmsp',    
    'new_business_mmsp',
    'treasurer_mmsp',
    'meeting_type_regular',
    'meeting_type_emergency',
    'meeting_type_interview',
    'amend_yes',
    'amend_no',
    'petty_receipts_yes',
    'petty_receipts_no',
    'narcan_kit_y',
    'narcan_kit_n',
    'narcan_use_y',
    'narcan_use_n',
    'vacancy_updated_y',
    'vacancy_updated_n',
    'email_checked_y',
    'email_checked_n',
    'voicemail_checked_y',
    'voicemail_checked_n',
    'checked_in_daily_y',
    'checked_in_daily_n',
];

for ($i = 1; $i <= 20; $i++) {
    $textFields[] = "roll_name_$i";
    $textFields[] = "comp_name_$i";
    $textFields[] = "comp_bal_$i";
    $checkboxFields[] = "roll_y_$i";
    $checkboxFields[] = "roll_n_$i";
}

for ($i = 0; $i <= 3; $i++) {
    $textFields[] = "checking_$i";
}
for ($i = 0; $i <= 4; $i++) {
    $textFields[] = "savings_$i";
}
for ($i = 0; $i <= 3; $i++) {
    $textFields[] = "petty_$i";
}

/* =========================
   SAVE FUNCTION
========================= */
function collectMinutesData(array $textFields, array $checkboxFields): array
{
    $data = [];

    foreach ($textFields as $field) {
        $data[$field] = trim(isset($_POST[$field]) ? (string)$_POST[$field] : '');
    }

    if ($data['secretary_signed_date'] === '') {
        $data['secretary_signed_date'] = todayDisplayDate();
    }

    if ($data['secretary_signature'] === '' && $data['secretary_name'] !== '') {
        $data['secretary_signature'] = $data['secretary_name'];
    }

    foreach ($checkboxFields as $field) {
        $data[$field] = 0;
    }

    $data['minutes_accepted'] = posted('minutes_accepted') === '1' ? 1 : 0;
    $data['treasurer_mmsp'] = posted('treasurer_mmsp') === '1' ? 1 : 0;
    $data['comptroller_mmsp'] = posted('comptroller_mmsp') === '1' ? 1 : 0;
    $data['coordinator_mmsp'] = posted('coordinator_mmsp') === '1' ? 1 : 0;
    $data['hsr_mmsp'] = posted('hsr_mmsp') === '1' ? 1 : 0;
    $data['old_business_mmsp'] = posted('old_business_mmsp') === '1' ? 1 : 0;
    $data['new_business_mmsp'] = posted('new_business_mmsp') === '1' ? 1 : 0;

    $meetingType = posted('meeting_type');
    $data['meeting_type_regular'] = $meetingType === 'regular' ? 1 : 0;
    $data['meeting_type_emergency'] = $meetingType === 'emergency' ? 1 : 0;
    $data['meeting_type_interview'] = $meetingType === 'interview' ? 1 : 0;

    $amendmentMade = posted('amendment_made');
    $data['amend_yes'] = $amendmentMade === 'yes' ? 1 : 0;
    $data['amend_no'] = $amendmentMade === 'no' ? 1 : 0;

    $pettyReceipts = posted('petty_receipts');
    $data['petty_receipts_yes'] = $pettyReceipts === 'yes' ? 1 : 0;
    $data['petty_receipts_no'] = $pettyReceipts === 'no' ? 1 : 0;

    $narcanKit = posted('narcan_kit');
    $data['narcan_kit_y'] = $narcanKit === 'y' ? 1 : 0;
    $data['narcan_kit_n'] = $narcanKit === 'n' ? 1 : 0;

    $narcanUse = posted('narcan_use');
    $data['narcan_use_y'] = $narcanUse === 'y' ? 1 : 0;
    $data['narcan_use_n'] = $narcanUse === 'n' ? 1 : 0;

    $vacancyUpdated = posted('vacancy_updated');
    $data['vacancy_updated_y'] = $vacancyUpdated === 'y' ? 1 : 0;
    $data['vacancy_updated_n'] = $vacancyUpdated === 'n' ? 1 : 0;

    $emailChecked = posted('email_checked');
    $data['email_checked_y'] = $emailChecked === 'y' ? 1 : 0;
    $data['email_checked_n'] = $emailChecked === 'n' ? 1 : 0;

    $voicemailChecked = posted('voicemail_checked');
    $data['voicemail_checked_y'] = $voicemailChecked === 'y' ? 1 : 0;
    $data['voicemail_checked_n'] = $voicemailChecked === 'n' ? 1 : 0;

    $checkedInDaily = posted('checked_in_daily');
    $data['checked_in_daily_y'] = $checkedInDaily === 'y' ? 1 : 0;
    $data['checked_in_daily_n'] = $checkedInDaily === 'n' ? 1 : 0;

    for ($i = 1; $i <= 20; $i++) {
        $rollPresent = posted('roll_' . $i . '_present');
        $data['roll_y_' . $i] = $rollPresent === 'y' ? 1 : 0;
        $data['roll_n_' . $i] = $rollPresent === 'n' ? 1 : 0;
    }

    return [$data, [
        'meeting_type' => $meetingType,
        'amendment_made' => $amendmentMade,
        'petty_receipts' => $pettyReceipts,
        'narcan_kit' => $narcanKit,
        'narcan_use' => $narcanUse,
        'vacancy_updated' => $vacancyUpdated,
        'email_checked' => $emailChecked,
        'voicemail_checked' => $voicemailChecked,
        'checked_in_daily' => $checkedInDaily,
    ]];
}

function saveMinutesRecord(PDO $pdo, array $data, int $recordId): int
{
    if ($recordId > 0) {
        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "`$field` = :$field";
        }

        $sql = "UPDATE oxford_house_minutes
                SET " . implode(', ', $setParts) . ", updated_at = NOW()
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        foreach ($data as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        $stmt->bindValue(':id', $recordId, PDO::PARAM_INT);
        $stmt->execute();

        return $recordId;
    }

    $columns = array_keys($data);
    $placeholders = array_map(static fn($f) => ':' . $f, $columns);

    $sql = "INSERT INTO oxford_house_minutes (`" . implode('`,`', $columns) . "`)
            VALUES (" . implode(',', $placeholders) . ")";

    $stmt = $pdo->prepare($sql);

    foreach ($data as $field => $value) {
        $stmt->bindValue(":$field", $value);
    }

    $stmt->execute();
    return (int)$pdo->lastInsertId();
}

/* =========================
   AJAX AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['autosave']) && $_POST['autosave'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $recordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
        [$data] = collectMinutesData($textFields, $checkboxFields);

        // Allow autosave even without meeting_date so drafts can save
        $recordId = saveMinutesRecord($pdo, $data, $recordId);

        echo json_encode([
            'ok' => true,
            'record_id' => $recordId,
            'message' => 'Auto-saved',
            'saved_at' => date('m/d/Y h:i:s A'),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => 'Auto-save failed: ' . $e->getMessage(),
        ]);
    }
    exit;
}

/* =========================
   PAGE SAVE / LOAD
========================= */
$message = '';
$currentRecordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;

if (isset($_GET['history_id']) && ctype_digit($_GET['history_id'])) {
    $historyId = (int)$_GET['history_id'];
    $stmt = $pdo->prepare("SELECT * FROM oxford_house_minutes WHERE id = :id");
    $stmt->execute([':id' => $historyId]);
    $record = $stmt->fetch();

    if ($record) {
        loadRecordIntoPost($record);
        $_POST['record_id'] = (string)$record['id'];
        $currentRecordId = (int)$record['id'];
        $message = 'Historical record loaded.';
    } else {
        $message = 'Requested historical record was not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_form'])) {
    try {
        $currentRecordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
        [$data, $state] = collectMinutesData($textFields, $checkboxFields);

        if ($data['meeting_date'] === '') {
            $message = 'Meeting date is required.';
        } else {
            $currentRecordId = saveMinutesRecord($pdo, $data, $currentRecordId);
            $_POST['record_id'] = (string)$currentRecordId;
            $message = 'Record saved successfully.';
        }

        foreach ($data as $field => $value) {
            $_POST[$field] = (string)$value;
        }

        foreach ($state as $field => $value) {
            $_POST[$field] = $value;
        }

        for ($i = 1; $i <= 20; $i++) {
            $_POST['roll_' . $i . '_present'] = posted('roll_' . $i . '_present');
        }
    } catch (PDOException $e) {
        die('Save failed: ' . h($e->getMessage()));
    }
}

$historyRows = $pdo->query("
    SELECT id, meeting_date, house_name, created_at
    FROM oxford_house_minutes
    ORDER BY
        CASE
            WHEN STR_TO_DATE(meeting_date, '%m/%d/%Y') IS NULL THEN 1
            ELSE 0
        END,
        STR_TO_DATE(meeting_date, '%m/%d/%Y') DESC,
        id DESC
")->fetchAll();

$selfUrl = htmlspecialchars($_SERVER['PHP_SELF'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oxford House Meeting Minutes</title>
<style>
    :root {
        --page-w: 8.5in;
        --page-h: 11in;
        --print-margin-top: 0.5in;
        --print-margin-right: 0.5in;
        --print-margin-bottom: 0.5in;
        --print-margin-left: 0.5in;
        --ink: #111;
        --line: #222;
        --font: Arial, Helvetica, sans-serif;
        --ok: #0a7d2f;
        --warn: #a15c00;
        --err: #b00020;
    }

    * { box-sizing: border-box; }

    html, body {
        margin: 0;
        padding: 0;
        background: #e9e9e9;
        color: var(--ink);
        font-family: var(--font);
    }

    body { padding: 18px 0; }

    form {
        margin: 0 auto;
        width: var(--page-w);
    }

    .toolbar {
        width: var(--page-w);
        margin: 0 auto 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .toolbar-left,
    .toolbar-right {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .toolbar button,
    .toolbar select {
        border: 1px solid #222;
        background: #fff;
        padding: 8px 12px;
        font-size: 13px;
        font-family: var(--font);
    }

    .toolbar button { cursor: pointer; }

    .autosave-status {
        font-size: 12px;
        font-weight: 700;
        padding: 6px 10px;
        border: 1px solid #222;
        background: #fff;
        min-width: 140px;
        text-align: center;
    }

    .autosave-status.saving { color: var(--warn); }
    .autosave-status.saved { color: var(--ok); }
    .autosave-status.error { color: var(--err); }

    .status-message {
        width: var(--page-w);
        margin: 0 auto 12px;
        font-size: 13px;
        font-weight: 700;
    }

    .page {
        width: var(--page-w);
        height: var(--page-h);
        min-height: var(--page-h);
        max-height: var(--page-h);
        background: #fff;
        margin: 0 auto 18px;
        padding:
            var(--print-margin-top)
            var(--print-margin-right)
            var(--print-margin-bottom)
            var(--print-margin-left);
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 0 1px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.08);
        page-break-after: always;
        break-after: page;
    }

    .page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .page.page-3 { padding-bottom: 0.38in; }
    .page.page-3 .section-title { margin-top: 10px; }
    .page.page-3 .comments-3 { height: 52px; }
    .page.page-3 .comments-5 { height: 88px; }
    .page.page-3 .mmtp-line { margin-top: 1px; font-size: 11px; }
    .page.page-3 .status-table th,
    .page.page-3 .status-table td { height: 20px; font-size: 10px; padding: 0 3px; }
    .page.page-3 .signature-block { margin-top: 8px; padding: 8px 10px; }
    .page.page-3 .signature-block-title { font-size: 14px; margin-bottom: 8px; }
    .page.page-3 .signature-box { height: 66px; }
    .page.page-3 .signature-box input[type="text"] { font-size: 24px; padding: 12px 8px 4px; }
    .page.page-3 .signature-line-row { gap: 10px; }
    .page.page-3 .signature-line-field span { font-size: 12px; }
    .page.page-3 .footer-page { bottom: 0.12in; }

    .header {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .logo {
        width: 76px;
        height: 76px;
        flex: 0 0 76px;
        object-fit: contain;
    }

    .title-wrap { flex: 1; }

    .title-line {
        font-weight: 800;
        font-size: 23px;
        line-height: 1;
        letter-spacing: .2px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 2px;
    }

    .title-line.second { margin-top: 2px; }

    .meta-row, .row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .meta-row {
        margin-top: 8px;
        justify-content: space-between;
    }

    .left-group, .right-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .label {
        font-size: 13px;
        font-weight: 700;
    }

    .small {
        font-size: 11px;
        font-weight: 400;
    }

    .checkbox-group,
    .radio-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    input[type="text"],
    textarea {
        border: none;
        outline: none;
        background: transparent;
        font: 13px var(--font);
        color: #111;
        width: 100%;
    }

    .line-input {
        display: inline-flex;
        align-items: center;
        border-bottom: 2px solid #222;
        min-height: 20px;
        padding: 0 3px;
    }

    .w-40{width:40px}.w-55{width:55px}.w-65{width:65px}.w-70{width:70px}.w-80{width:80px}.w-90{width:90px}.w-110{width:110px}.w-130{width:130px}.w-150{width:150px}.w-170{width:170px}.w-200{width:200px}.w-240{width:240px}.w-260{width:260px}

    .section-title {
        margin-top: 8px;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.05;
    }

    .section-title .small { font-size: 10px; }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    .rollcall { margin-top: 4px; }

    .rollcall th,
    .rollcall td {
        border: 1px solid #222;
        height: 22px;
        font-size: 11px;
        padding: 0 3px;
        vertical-align: middle;
    }

    .rollcall thead th {
        border: none;
        height: auto;
        padding: 0 3px 1px;
        font-weight: 400;
        text-align: center;
    }

    .yn { width: 34px; text-align: center; }

    .name-cell input { height: 20px; }

    input[type="checkbox"],
    input[type="radio"] {
        width: 14px;
        height: 14px;
        margin: 0;
        accent-color: #111;
        vertical-align: middle;
    }

    .checkbox-group input[type="checkbox"],
    .rollcall input[type="checkbox"],
    .radio-group input[type="radio"],
    .rollcall input[type="radio"] {
        transform: translateY(-1px);
    }

    .box-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border: 2px solid #222;
        vertical-align: middle;
        margin-left: 6px;
        position: relative;
        overflow: hidden;
    }

    .box-check input[type="checkbox"] {
        width: 100%;
        height: 100%;
        appearance: none;
        -webkit-appearance: none;
        margin: 0;
        cursor: pointer;
        background: #fff;
    }

    .box-check input[type="checkbox"]:checked::after {
        content: "✕";
        position: absolute;
        inset: -1px 0 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
    }

    .report-box {
        border: 2px solid #222;
        margin-top: 8px;
        padding: 3px 4px 6px;
    }

    .report-heading {
        font-size: 17px;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 5px;
    }

    .money-table th,
    .money-table td,
    .comptroller-table th,
    .comptroller-table td {
        border: 1px solid #222;
        font-size: 11px;
        padding: 0 3px;
        height: 22px;
    }

    .money-table th,
    .comptroller-table th {
        font-weight: 400;
        text-align: center;
    }

    .money-table .caption {
        border: none;
        font-weight: 700;
        font-size: 14px;
        height: auto;
    }

    .subnote { font-size: 11px; }

    .lined-block {
        border: 1px solid #222;
        position: relative;
        overflow: hidden;
        background: #fff;
    }

    .lined-block textarea {
        width: 100%;
        height: 100%;
        min-height: 100%;
        resize: none;
        padding: 6px;
        line-height: 1.35;
        background: transparent;
        overflow: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap;
    }

    .lined-block textarea::-webkit-scrollbar { display: none; }

    .comments-5 { height: 106px; }
    .comments-3 { height: 64px; }

    .autofit-comment { font-size: 13px; }

    .mmtp-line {
        text-align: right;
        font-size: 12px;
        margin-top: 3px;
    }

    .footer-page {
        position: absolute;
        bottom: 0.22in;
        left: 0;
        right: 0;
        text-align: center;
        font-weight: 700;
        font-size: 12px;
    }

    .full-rect {
        border: 2px solid #222;
        height: calc(11in - 0.5in - 0.5in - 1.15in);
        margin-top: 6px;
        padding: 2px;
        overflow: hidden;
    }

    .full-rect textarea { height: 100%; }

    .status-table th,
    .status-table td {
        border: 1px solid #222;
        font-size: 11px;
        padding: 0 4px;
        height: 22px;
        text-align: center;
    }

    .status-table th { font-weight: 700; }

    .signature-block {
        margin-top: 16px;
        border: 2px solid #222;
        padding: 12px 14px;
    }

    .signature-block-title {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 18px;
        align-items: end;
    }

    .signature-box {
        border: 1px solid #222;
        height: 90px;
        position: relative;
        background: #fff;
    }

    .signature-box input[type="text"] {
        width: 100%;
        height: 100%;
        border: none;
        outline: none;
        background: transparent;
        font-family: "Brush Script MT", "Segoe Script", cursive;
        font-size: 30px;
        padding: 18px 10px 6px;
    }

    .signature-label {
        font-size: 12px;
        font-weight: 700;
        margin-top: 4px;
    }

    .signature-line-row {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .signature-line-field {
        display: flex;
        align-items: end;
        gap: 8px;
    }

    .signature-line-field span {
        white-space: nowrap;
        font-size: 14px;
        font-weight: 700;
    }

    .signature-line {
        flex: 1;
        border-bottom: 2px solid #222;
        min-height: 24px;
        display: flex;
        align-items: center;
        padding: 0 4px 2px;
    }

    .signature-line input[type="text"] {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        font: 13px Arial, Helvetica, sans-serif;
    }

    .no-print { display: block; }

    @page {
        size: Letter portrait;
        margin: 0;
    }

    @media print {
        html, body {
            width: 8.5in;
            height: auto;
            margin: 0;
            padding: 0;
            background: #fff !important;
        }

        body { padding: 0; }

        form {
            width: 100%;
            margin: 0;
        }

        .toolbar,
        .status-message,
        .no-print {
            display: none !important;
        }

        .page {
            width: 8.5in;
            height: 11in;
            min-height: 11in;
            max-height: 11in;
            margin: 0 !important;
            padding:
                var(--print-margin-top)
                var(--print-margin-right)
                var(--print-margin-bottom)
                var(--print-margin-left);
            box-shadow: none !important;
            overflow: hidden;
            page-break-after: always;
            break-after: page;
        }

        .page:last-child {
            page-break-after: auto;
            break-after: auto;
        }
    }

    .back-to-top {
        position: fixed;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 9999;
        display: none;
        width: 64px;
        height: 64px;
        border: 2px solid #222;
        background: rgba(255,255,255,.96);
        color: #111;
        font: 700 12px Arial, Helvetica, sans-serif;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(0,0,0,.18);
        border-radius: 16px;
    }

    .back-to-top:hover {
        background: #f3f3f3;
    }

    .back-to-top.show {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    @media print {
        .back-to-top {
            display: none !important;
        }
    }
</style>
</head>
<body>

<?php if ($message !== ''): ?>
    <div class="status-message"><?= h($message) ?></div>
<?php endif; ?>

<form method="post" id="minutesForm" autocomplete="off">
    <input type="hidden" name="record_id" id="record_id" value="<?= h((string)$currentRecordId) ?>">

    <div class="toolbar no-print">
        <div class="toolbar-left">
            <button type="submit" name="save_form" value="1">Save Entries</button>
            <button type="button" onclick="window.location='<?= $selfUrl ?>'">New Form</button>
            <button type="button" onclick="clearForm()">Clear Form</button>
            <button type="button" onclick="window.print()">Print Form</button>
            <div id="autosaveStatus" class="autosave-status">Auto-save ready</div>
        </div>

        <div class="toolbar-right">
            <label for="history_id"><strong>History by Date:</strong></label>
            <select id="history_id" onchange="if(this.value){ window.location='?history_id=' + this.value; }">
                <option value="">Select saved meeting</option>
                <?php foreach ($historyRows as $row): ?>
                    <option value="<?= (int)$row['id'] ?>" <?= ((int)$currentRecordId === (int)$row['id']) ? 'selected' : '' ?>>
                        <?= h($row['meeting_date']) ?><?= !empty($row['house_name']) ? ' - ' . h($row['house_name']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <section class="page">
        <div class="header">
            <img class="logo" src="../images/oxford_house_logo.png" alt="Oxford House Logo">
            <div class="title-wrap">
                <div class="title-line">
                    <span>OXFORD HOUSE</span><span>-</span>
                    <span class="line-input w-240" style="margin:0 6px 0 4px; min-height:24px; transform:translateY(1px);">
                        <input type="text" name="house_name" value="<?= h(posted('house_name')) ?>" required>
                    </span>
                </div>
                <div class="title-line second">
                    <span style="margin-left:115px;">MEETING MINUTES</span>
                </div>
            </div>
        </div>

        <div class="meta-row">
            <div class="left-group">
                <span class="label">MEETING TYPE:</span>

                <label class="radio-group">
                    <span>Regular</span>
                    <input type="radio" name="meeting_type" value="regular" <?= radioChecked('meeting_type', 'regular') ? 'checked' : '' ?>>
                </label>

                <label class="radio-group">
                    <span>Emergency</span>
                    <input type="radio" name="meeting_type" value="emergency" <?= radioChecked('meeting_type', 'emergency') ? 'checked' : '' ?>>
                </label>

                <label class="radio-group">
                    <span>Interview</span>
                    <input type="radio" name="meeting_type" value="interview" <?= radioChecked('meeting_type', 'interview') ? 'checked' : '' ?>>
                </label>
            </div>

            <div class="right-group">
                <span class="label">DATE:</span>
                <span class="line-input w-130">
                    <input type="text" name="meeting_date" value="<?= h(posted('meeting_date')) ?>" required>
                </span>
            </div>
        </div>

        <div class="meta-row" style="margin-top:4px; align-items:flex-end;">
            <div class="section-title" style="margin-top:0; font-size:17px;">
                ROLL CALL <span class="small">(List all members and guests. Discuss all non-excused absences)</span>
            </div>
            <div class="right-group">
                <span class="label">START TIME:</span>
                <span class="line-input w-90"><input type="text" name="start_time" value="<?= h(posted('start_time')) ?>"></span>
                <span>am / pm</span>
            </div>
        </div>

        <table class="rollcall">
            <thead>
                <tr>
                    <th style="width:31%">Name</th><th colspan="2">Present</th>
                    <th style="width:31%">Name</th><th colspan="2">Present</th>
                    <th style="width:31%">Name</th><th colspan="2">Present</th>
                    <th style="width:31%">Name</th><th colspan="2">Present</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($r = 1; $r <= 5; $r++): ?>
                <tr>
                    <?php for ($c = 1; $c <= 4; $c++): $idx = (($c - 1) * 5) + $r; ?>
                        <td class="name-cell">
                            <input type="text" name="roll_name_<?= $idx ?>" value="<?= h(posted('roll_name_' . $idx)) ?>">
                        </td>
                        <td class="yn">
                            <label>Y <input type="radio" name="roll_<?= $idx ?>_present" value="y" <?= radioChecked('roll_' . $idx . '_present', 'y') ? 'checked' : '' ?>></label>
                        </td>
                        <td class="yn">
                            <label>N <input type="radio" name="roll_<?= $idx ?>_present" value="n" <?= radioChecked('roll_' . $idx . '_present', 'n') ? 'checked' : '' ?>></label>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div class="row" style="margin-top:8px; justify-content:space-between;">
            <div>
                <span class="section-title" style="font-size:18px; display:inline;">TRADITION #</span>
                <span class="line-input w-55"><input type="text" name="tradition_number" value="<?= h(posted('tradition_number')) ?>"></span>
                <span class="small">(Using the Oxford House Manual, read the entire page for one of the Traditions. Each resident reads a paragraph.)</span>
            </div>
        </div>

        <div class="row" style="margin-top:6px; justify-content:space-between;">
            <div class="section-title" style="font-size:18px; margin:0;">
                READ MINUTES OF LAST MEETING <span class="small">(Read the entire minutes from the last meeting.)</span>
            </div>
            <div>
                Minutes accepted as read/amended
                <label class="box-check">
                    <input type="checkbox" value="1" name="minutes_accepted" <?= isChecked('minutes_accepted') ? 'checked' : '' ?>>
                </label>
            </div>
        </div>

        <div class="row" style="margin-top:3px;">
            <span>Amendments made:</span>
            <label class="radio-group"><span>Yes</span><input type="radio" name="amendment_made" value="yes" <?= radioChecked('amendment_made', 'yes') ? 'checked' : '' ?>></label>
            <label class="radio-group"><span>No</span><input type="radio" name="amendment_made" value="no" <?= radioChecked('amendment_made', 'no') ? 'checked' : '' ?>></label>
        </div>

        <div class="report-box">
            <div class="report-heading">TREASURER REPORT</div>

            <div class="grid-3">
                <?php
                $moneySections = [
                    'checking' => ['CHECKING', ['Beginning Bal', 'Total Received', 'Total Spent', 'Ending Bal']],
                    'savings'  => ['SAVINGS', ['Beginning Bal', 'Deposits', 'Withdrawals', 'Interest', 'Ending Bal']],
                    'petty'    => ['PETTY CASH', ['Beginning Cash', 'Cash Spent', 'Cash Replinished', 'Ending Cash', 'Receipts Viewed']],
                ];

                foreach ($moneySections as $key => [$title, $rows]):
                ?>
                    <table class="money-table">
                        <tr><th colspan="2" class="caption"><?= h($title) ?></th></tr>
                        <?php foreach ($rows as $i => $row): ?>
                            <tr>
                                <td style="border-left:0; border-right:0; width:42%; font-size:11px;"><?= h($row) ?></td>
                                <td style="width:58%;">
                                    <?php if ($key === 'petty' && $row === 'Receipts Viewed'): ?>
                                        <div style="display:flex; justify-content:space-evenly; align-items:center; height:20px;">
                                            <label>Yes <input type="radio" name="petty_receipts" value="yes" <?= radioChecked('petty_receipts', 'yes') ? 'checked' : '' ?>></label>
                                            <label>No <input type="radio" name="petty_receipts" value="no" <?= radioChecked('petty_receipts', 'no') ? 'checked' : '' ?>></label>
                                        </div>
                                    <?php else: ?>
                                        <div style="display:flex; align-items:center; gap:2px;">
                                            <span>$</span>
                                            <input type="text" name="<?= h($key . '_' . $i) ?>" value="<?= h(posted($key . '_' . $i)) ?>">
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:4px; font-size:11px;">
                <strong>TREASURER REPORT COMMENTS</strong>
                <span class="subnote">(Bills to be paid, checks written, etc.)</span>
            </div>
            <div class="lined-block comments-5">
                <textarea class="autofit-comment" data-max-font="13" data-min-font="8" name="treasurer_comments"><?= h(posted('treasurer_comments')) ?></textarea>
            </div>
            <div class="mmtp-line">
                Motion Made, Seconded, and Passed (MMSP) to accept the Treasurer Report
                <label class="box-check">
                    <input type="checkbox" value="1" name="treasurer_mmsp" <?= isChecked('treasurer_mmsp') ? 'checked' : '' ?>>
                </label>
            </div>
        </div>

        <div class="report-box" style="margin-top:10px;">
            <div class="report-heading">COMPTROLLER REPORT</div>

            <table class="comptroller-table">
                <tr>
                    <?php for ($g = 1; $g <= 4; $g++): ?>
                        <th>Name</th><th>Balance</th>
                    <?php endfor; ?>
                </tr>
                <?php for ($r = 1; $r <= 5; $r++): ?>
                    <tr>
                        <?php for ($g = 1; $g <= 4; $g++): $i = (($g - 1) * 5) + $r; ?>
                            <td><input type="text" name="comp_name_<?= $i ?>" value="<?= h(posted('comp_name_' . $i)) ?>"></td>
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <span>$</span>
                                    <input type="text" name="comp_bal_<?= $i ?>" value="<?= h(posted('comp_bal_' . $i)) ?>">
                                </div>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </table>

            <div style="margin-top:4px; font-size:11px;">
                <strong>COMPTROLLER REPORT COMMENTS</strong>
                <span class="subnote">(Record any warnings, contracts, and/or fines)</span>
            </div>
            <div class="lined-block comments-3">
                <textarea class="autofit-comment" data-max-font="13" data-min-font="8" name="comptroller_comments"><?= h(posted('comptroller_comments')) ?></textarea>
            </div>
            <div class="mmtp-line">
                MMSP to accept the Comptroller report
                <label class="box-check">
                    <input type="checkbox" value="1" name="comptroller_mmsp" <?= isChecked('comptroller_mmsp') ? 'checked' : '' ?>>
                </label>
            </div>
        </div>

        <div class="footer-page">PAGE 1 of 3</div>
    </section>

    <section class="page">
        <div class="section-title" style="font-size:19px;">
            MEMBERS EES PLAN OF ACTION
            <span class="small" style="font-size:12px;">(Record members plan to be able to pay towards EES payments)</span>
        </div>
        <div class="full-rect lined-block">
            <textarea name="ees_plan"><?= h(posted('ees_plan')) ?></textarea>
        </div>
        <div class="footer-page">PAGE 2 of 3</div>
    </section>

    <section class="page page-3">
        <div class="section-title" style="font-size:18px;">
            COORDINATOR REPORT <span class="small">(notes on chores, cleaning, safety, and repairs)</span>
        </div>
        <div class="lined-block comments-3">
            <textarea class="autofit-comment" data-max-font="13" data-min-font="8" name="coordinator_report"><?= h(posted('coordinator_report')) ?></textarea>
        </div>
        <div class="mmtp-line">
            MMSP to accept the Coordinator report
            <label class="box-check">
                <input type="checkbox" value="1" name="coordinator_mmsp" <?= isChecked('coordinator_mmsp') ? 'checked' : '' ?>>
            </label>
        </div>

        <div class="section-title" style="font-size:18px; margin-top:12px;">
            HOUSING SERVICES REPORT
            <span class="small">(updates on chapter, HSC, presentations, events, and new member interviews)</span>
        </div>
        <div class="lined-block comments-5">
            <textarea class="autofit-comment" data-max-font="13" data-min-font="8" name="housing_services_report"><?= h(posted('housing_services_report')) ?></textarea>
        </div>
        <div class="mmtp-line">
            MMSP to accept the HSR report
            <label class="box-check">
                <input type="checkbox" value="1" name="hsr_mmsp" <?= isChecked('hsr_mmsp') ? 'checked' : '' ?>>
            </label>
        </div>

        <div class="section-title" style="font-size:18px; margin-top:12px;">
            UNFINISHED BUSINESS <span class="small">(Discussion of any prior business that has not been resolved)</span>
        </div>

        <table class="status-table" style="margin-top:4px;">
            <tr>
                <th>All members know the location of the Narcan Kit:</th>
                <th style="width:35px;">Y</th><th style="width:35px;">N</th>
                <th>All members know how to use Narcan:</th>
                <th style="width:35px;">Y</th><th style="width:35px;">N</th>
            </tr>
            <tr>
                <td></td>
                <td><input type="radio" name="narcan_kit" value="y" <?= radioChecked('narcan_kit', 'y') ? 'checked' : '' ?>></td>
                <td><input type="radio" name="narcan_kit" value="n" <?= radioChecked('narcan_kit', 'n') ? 'checked' : '' ?>></td>
                <td></td>
                <td><input type="radio" name="narcan_use" value="y" <?= radioChecked('narcan_use', 'y') ? 'checked' : '' ?>></td>
                <td><input type="radio" name="narcan_use" value="n" <?= radioChecked('narcan_use', 'n') ? 'checked' : '' ?>></td>
            </tr>
            <tr>
                <td colspan="6" style="padding:0;">
                    <div class="lined-block" style="height:88px; border:none;">
                        <textarea class="autofit-comment" data-max-font="13" data-min-font="8" name="unfinished_business" style="padding:2px 4px;"><?= h(posted('unfinished_business')) ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
        <div class="mmtp-line">
            MMSP to accept the Old Business report
            <label class="box-check">
                <input type="checkbox" value="1" name="old_business_mmsp" <?= isChecked('old_business_mmsp') ? 'checked' : '' ?>>
            </label>
        </div>

        <div class="section-title" style="font-size:18px; margin-top:12px;">
            NEW BUSINESS <span class="small">(Discussion of all new business topics.)</span>
        </div>

        <table class="status-table" style="margin-top:4px;">
            <tr>
                <th>Vacancy Site Updated:</th><th style="width:35px;">Y</th><th style="width:35px;">N</th>
                <th>House email checked:</th><th style="width:35px;">Y</th><th style="width:35px;">N</th>
                <th>Voicemail Checked Daily:</th><th style="width:35px;">Y</th><th style="width:35px;">N</th>
            </tr>
            <tr>
                <td></td>
                <td><input type="radio" name="vacancy_updated" value="y" <?= radioChecked('vacancy_updated', 'y') ? 'checked' : '' ?>></td>
                <td><input type="radio" name="vacancy_updated" value="n" <?= radioChecked('vacancy_updated', 'n') ? 'checked' : '' ?>></td>
                <td></td>
                <td><input type="radio" name="email_checked" value="y" <?= radioChecked('email_checked', 'y') ? 'checked' : '' ?>></td>
                <td><input type="radio" name="email_checked" value="n" <?= radioChecked('email_checked', 'n') ? 'checked' : '' ?>></td>
                <td></td>
                <td><input type="radio" name="voicemail_checked" value="y" <?= radioChecked('voicemail_checked', 'y') ? 'checked' : '' ?>></td>
                <td><input type="radio" name="voicemail_checked" value="n" <?= radioChecked('voicemail_checked', 'n') ? 'checked' : '' ?>></td>
            </tr>
            <tr>
                <th colspan="2" style="border-right:none;">All members have checked in everyday:</th>
                <th style="width:35px; border-left:none;">Y</th><th style="width:35px;">N</th>
                <td colspan="5"></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td><input type="radio" name="checked_in_daily" value="y" <?= radioChecked('checked_in_daily', 'y') ? 'checked' : '' ?>></td>
                <td><input type="radio" name="checked_in_daily" value="n" <?= radioChecked('checked_in_daily', 'n') ? 'checked' : '' ?>></td>
                <td colspan="5"></td>
            </tr>
            <tr>
                <td colspan="9" style="padding:0;">
                    <div class="lined-block" style="height:250px; border:none;">
                        <textarea name="new_business" style="padding:2px 4px;"><?= h(posted('new_business')) ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
        <div class="mmtp-line">
            MMSP to accept the New Business report
            <label class="box-check">
                <input type="checkbox" value="1" name="new_business_mmsp" <?= isChecked('new_business_mmsp') ? 'checked' : '' ?>>
            </label>
        </div>

        <div style="margin-top:10px; font-size:17px; font-weight:800;">
            ADJOURN MEETING AT
            <span class="line-input w-55" style="margin-left:8px;"><input type="text" name="adjourn_hour" value="<?= h(posted('adjourn_hour')) ?>"></span>
            :
            <span class="line-input w-55"><input type="text" name="adjourn_min" value="<?= h(posted('adjourn_min')) ?>"></span>
            <span style="font-size:14px; font-weight:400; margin-left:8px;">am / pm</span>
        </div>

        <div class="signature-block">
            <div class="signature-block-title">SECRETARY CERTIFICATION</div>

            <div class="signature-grid">
                <div>
                    <div class="signature-box">
                        <input
                            type="text"
                            id="secretary_signature"
                            name="secretary_signature"
                            value="<?= h(posted('secretary_signature')) ?>"
                        >
                    </div>
                    <div class="signature-label">Secretary Signature</div>
                </div>

                <div class="signature-line-row">
                    <div class="signature-line-field">
                        <span>Secretary Name:</span>
                        <div class="signature-line">
                            <input
                                type="text"
                                id="secretary_name"
                                name="secretary_name"
                                value="<?= h(posted('secretary_name')) ?>"
                            >
                        </div>
                    </div>

                    <div class="signature-line-field">
                        <span>Date Signed:</span>
                        <div class="signature-line">
                            <input
                                type="text"
                                id="secretary_signed_date"
                                name="secretary_signed_date"
                                value="<?= h(posted('secretary_signed_date', todayDisplayDate())) ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-page">PAGE 3 of 3</div>
    </section>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('minutesForm');
    const recordIdInput = document.getElementById('record_id');
    const autosaveStatus = document.getElementById('autosaveStatus');

    let autosaveTimer = null;
    let isAutosaving = false;
    let pendingAutosave = false;

    function setAutosaveStatus(text, state) {
        if (!autosaveStatus) return;
        autosaveStatus.textContent = text;
        autosaveStatus.classList.remove('saving', 'saved', 'error');
        if (state) autosaveStatus.classList.add(state);
    }

    function fitCommentTextarea(textarea) {
        const maxFont = parseFloat(textarea.dataset.maxFont || 13);
        const minFont = parseFloat(textarea.dataset.minFont || 8);

        textarea.style.overflow = 'hidden';
        textarea.style.fontSize = maxFont + 'px';
        textarea.style.lineHeight = (maxFont * 1.35) + 'px';

        let current = maxFont;

        while (
            current > minFont &&
            (textarea.scrollHeight > textarea.clientHeight || textarea.scrollWidth > textarea.clientWidth)
        ) {
            current -= 0.25;
            textarea.style.fontSize = current + 'px';
            textarea.style.lineHeight = (current * 1.35) + 'px';
        }

        if (textarea.scrollHeight > textarea.clientHeight || textarea.scrollWidth > textarea.clientWidth) {
            textarea.style.fontSize = minFont + 'px';
            textarea.style.lineHeight = (minFont * 1.35) + 'px';
        }
    }

    function fitAllCommentTextareas() {
        document.querySelectorAll('.autofit-comment').forEach(function (textarea) {
            fitCommentTextarea(textarea);
        });
    }

    function serializeFormForAutosave() {
        const formData = new FormData(form);
        formData.delete('save_form');
        formData.append('autosave', '1');
        return formData;
    }

    async function doAutosave() {
        if (!form) return;

        if (isAutosaving) {
            pendingAutosave = true;
            return;
        }

        isAutosaving = true;
        pendingAutosave = false;
        setAutosaveStatus('Saving...', 'saving');

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: serializeFormForAutosave(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.ok) {
                throw new Error(result.message || 'Auto-save failed');
            }

            if (recordIdInput && result.record_id) {
                recordIdInput.value = result.record_id;
            }

            setAutosaveStatus('Saved ' + (result.saved_at || ''), 'saved');
        } catch (error) {
            setAutosaveStatus('Auto-save failed', 'error');
            console.error(error);
        } finally {
            isAutosaving = false;

            if (pendingAutosave) {
                doAutosave();
            }
        }
    }

    function queueAutosave() {
        if (!form) return;
        clearTimeout(autosaveTimer);
        setAutosaveStatus('Changes detected...', 'saving');
        autosaveTimer = setTimeout(doAutosave, 900);
    }

    document.querySelectorAll('.autofit-comment').forEach(function (textarea) {
        textarea.addEventListener('input', function () {
            fitCommentTextarea(textarea);
        });

        textarea.addEventListener('change', function () {
            fitCommentTextarea(textarea);
        });

        textarea.addEventListener('paste', function () {
            requestAnimationFrame(function () {
                fitCommentTextarea(textarea);
            });
        });
    });

    if (form) {
        form.querySelectorAll('input[type="text"], textarea').forEach(function (el) {
            el.addEventListener('input', queueAutosave);
            el.addEventListener('change', queueAutosave);
            el.addEventListener('blur', queueAutosave);
        });

        form.querySelectorAll('input[type="checkbox"], input[type="radio"], select').forEach(function (el) {
            el.addEventListener('change', queueAutosave);
        });
    }

    const secretaryNameInput = document.querySelector('#secretary_name');
    const secretarySignatureInput = document.querySelector('#secretary_signature');
    const secretaryDateInput = document.querySelector('#secretary_signed_date');

    let signatureManuallyEdited = secretarySignatureInput && secretarySignatureInput.value.trim() !== '';

    if (secretarySignatureInput) {
        secretarySignatureInput.addEventListener('input', function () {
            const nameValue = secretaryNameInput ? secretaryNameInput.value.trim() : '';
            const sigValue = this.value.trim();
            signatureManuallyEdited = sigValue !== '' && sigValue !== nameValue;
            queueAutosave();
        });
    }

    if (secretaryNameInput && secretarySignatureInput) {
        const syncSignatureFromName = function () {
            if (!signatureManuallyEdited || secretarySignatureInput.value.trim() === '') {
                secretarySignatureInput.value = secretaryNameInput.value;
            }
        };

        secretaryNameInput.addEventListener('input', function () {
            syncSignatureFromName();
            queueAutosave();
        });

        syncSignatureFromName();
    }

    if (secretaryDateInput && secretaryDateInput.value.trim() === '') {
        const now = new Date();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const yyyy = now.getFullYear();
        secretaryDateInput.value = `${mm}/${dd}/${yyyy}`;
    }

    window.addEventListener('resize', fitAllCommentTextareas);
    fitAllCommentTextareas();
    setAutosaveStatus('Auto-save ready', 'saved');
});

function clearForm() {
    const form = document.querySelector('#minutesForm');
    if (!form) return;
    if (!window.confirm('Clear all entries on this form?')) return;

    form.reset();

    form.querySelectorAll('input[type="text"], textarea').forEach(function (el) {
        if (el.name !== 'record_id') {
            el.value = '';
        }
    });

    form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (el) {
        el.checked = false;
    });

    const secretarySignatureInput = form.querySelector('#secretary_signature');
    const secretaryDateInput = form.querySelector('#secretary_signed_date');
    const autosaveStatus = document.getElementById('autosaveStatus');

    if (secretarySignatureInput) {
        secretarySignatureInput.value = '';
    }

    if (secretaryDateInput) {
        const now = new Date();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const yyyy = now.getFullYear();
        secretaryDateInput.value = `${mm}/${dd}/${yyyy}`;
    }

    const recordId = form.querySelector('input[name="record_id"]');
    if (recordId) {
        recordId.value = '';
    }

    if (autosaveStatus) {
        autosaveStatus.textContent = 'Cleared - new draft';
        autosaveStatus.classList.remove('saving', 'error');
        autosaveStatus.classList.add('saved');
    }

    requestAnimationFrame(function () {
        const secretaryNameInput = form.querySelector('#secretary_name');
        const signatureInput = form.querySelector('#secretary_signature');

        if (secretaryNameInput && signatureInput && signatureInput.value.trim() === '') {
            secretaryNameInput.addEventListener('input', function () {
                if (signatureInput.value.trim() === '') {
                    signatureInput.value = secretaryNameInput.value;
                }
            });
        }

        document.querySelectorAll('.autofit-comment').forEach(function (textarea) {
            const maxFont = parseFloat(textarea.dataset.maxFont || 13);
            textarea.style.fontSize = maxFont + 'px';
            textarea.style.lineHeight = (maxFont * 1.35) + 'px';
        });
    });
}

const backToTopBtn = document.getElementById('backToTopBtn');

function toggleBackToTopButton() {
    if (!backToTopBtn) return;

    if (window.scrollY > 250) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
}

if (backToTopBtn) {
    backToTopBtn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

window.addEventListener('scroll', toggleBackToTopButton);
toggleBackToTopButton();
</script>

<button type="button" id="backToTopBtn" class="back-to-top no-print" aria-label="Back to top">
    ↑ Top
</button>
</body>
</html>