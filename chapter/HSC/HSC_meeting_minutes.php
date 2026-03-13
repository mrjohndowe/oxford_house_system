<?php
/**
 * HSC Meeting Minutes
 * - Single-file PHP form
 * - Saves the entire report body as JSON
 * - Search history by house and date range
 * - Load prior saved report into the form
 * - Auto-save + update existing record
 */

declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

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
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function posted(array $source, string $key, mixed $default = ''): mixed
{
    return $source[$key] ?? $default;
}

function arr_posted(array $source, string $key, int $count, mixed $default = ''): array
{
    $value = $source[$key] ?? [];
    $out = [];

    for ($i = 0; $i < $count; $i++) {
        $out[$i] = is_array($value) && array_key_exists($i, $value) ? $value[$i] : $default;
    }

    return $out;
}

function checkbox_value(array $source, string $key): int
{
    return isset($source[$key]) ? 1 : 0;
}

function radio_value(array $source, string $key, string $default = ''): string
{
    return isset($source[$key]) ? trim((string)$source[$key]) : $default;
}

function checkbox_checked(array $data, string $key): string
{
    return !empty($data[$key]) ? 'checked' : '';
}

function radio_checked(array $data, string $key, string $value): string
{
    return (($data[$key] ?? '') === $value) ? 'checked' : '';
}

function selected_attr(string $current, string $value): string
{
    return $current === $value ? 'selected' : '';
}

function array_get(array $array, string $key, mixed $default = ''): mixed
{
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

function vote_value(array $source, string $key): string
{
    $value = preg_replace('/\D/', '', (string)($source[$key] ?? '')) ?? '';
    if ($value === '') {
        return '';
    }
    return substr($value, 0, 2);
}

function money_value(mixed $value): string
{
    $clean = preg_replace('/[^0-9.\-]/', '', trim((string)$value)) ?? '';
    if ($clean === '' || $clean === '.' || $clean === '-' || $clean === '-.') {
        return '';
    }
    return $clean;
}

function money_sum(array $rows, string $key): string
{
    $sum = 0.0;
    foreach ($rows as $row) {
        $value = (string)($row[$key] ?? '');
        if ($value !== '' && is_numeric($value)) {
            $sum += (float)$value;
        }
    }
    return number_format($sum, 2, '.', '');
}

function normalize_position_row(mixed $row): array
{
    $row = is_array($row) ? $row : [];
    $present = trim((string)($row['present'] ?? ''));

    if (!in_array($present, ['Y', 'N', 'E'], true)) {
        $present = '';
    }

    return [
        'position_name' => trim((string)($row['position_name'] ?? '')),
        'member_name' => trim((string)($row['member_name'] ?? '')),
        'present' => $present,
    ];
}

function normalize_roll_call_row(mixed $row): array
{
    $row = is_array($row) ? $row : [];
    return [
        'house_name' => trim((string)($row['house_name'] ?? '')),
        'president_proxy' => trim((string)($row['president_proxy'] ?? '')),
        'meeting_day' => trim((string)($row['meeting_day'] ?? '')),
        'meeting_time' => trim((string)($row['meeting_time'] ?? '')),
        'hsr_completed' => in_array((string)($row['hsr_completed'] ?? ''), ['Y', 'N'], true) ? (string)$row['hsr_completed'] : '',
    ];
}

function normalize_checkin_row(mixed $row): array
{
    $row = is_array($row) ? $row : [];
    return [
        'house_name' => trim((string)($row['house_name'] ?? '')),
        'comments' => trim((string)($row['comments'] ?? '')),
    ];
}

function normalize_money_received_row(mixed $row): array
{
    $row = is_array($row) ? $row : [];
    return [
        'date' => (string)($row['date'] ?? ''),
        'purpose' => trim((string)($row['purpose'] ?? '')),
        'amount' => money_value($row['amount'] ?? ''),
    ];
}

function normalize_money_spent_row(mixed $row): array
{
    $row = is_array($row) ? $row : [];
    return [
        'date' => (string)($row['date'] ?? ''),
        'purpose' => trim((string)($row['purpose'] ?? '')),
        'check_no' => trim((string)($row['check_no'] ?? '')),
        'amount' => money_value($row['amount'] ?? ''),
    ];
}

function default_hsc_positions(): array
{
    return [
        'HSC Chair',
        'Chapter Vice Chair',
        'HSC Treasurer',
        'HSC Coordinator',
        'HSC Secretary',
        'Re-Entry',
        'Fundraiser',
        'Outreach',
        'State',
    ];
}

function normalize_report(array $raw): array
{
    $defaultPositions = default_hsc_positions();
    $positionRows = [];
    $rawPositions = (array)array_get($raw, 'hsc_position_rows', []);

    foreach ($defaultPositions as $index => $positionName) {
        $row = isset($rawPositions[$index]) && is_array($rawPositions[$index]) ? $rawPositions[$index] : [];
        $positionRows[] = normalize_position_row([
            'position_name' => $positionName,
            'member_name' => $row['member_name'] ?? '',
            'present' => $row['present'] ?? '',
        ]);
    }

    $rollCall = [];
    foreach ((array)array_get($raw, 'roll_call_rows', []) as $row) {
        $rollCall[] = normalize_roll_call_row($row);
    }
    $rollCall = array_values(array_pad($rollCall, 8, normalize_roll_call_row([])));

    $checkins = [];
    foreach ((array)array_get($raw, 'house_checkins', []) as $row) {
        $checkins[] = normalize_checkin_row($row);
    }
    $checkins = array_values(array_pad($checkins, 10, normalize_checkin_row([])));

    $moneyReceived = [];
    foreach ((array)array_get($raw, 'treasurer_money_received', []) as $row) {
        $moneyReceived[] = normalize_money_received_row($row);
    }
    $moneyReceived = array_values(array_pad($moneyReceived, 6, normalize_money_received_row([])));

    $moneySpent = [];
    foreach ((array)array_get($raw, 'treasurer_money_spent', []) as $row) {
        $moneySpent[] = normalize_money_spent_row($row);
    }
    $moneySpent = array_values(array_pad($moneySpent, 6, normalize_money_spent_row([])));

    $beginningBalance = money_value(array_get($raw, 'treasurer_beginning_balance'));
    $totalReceived = money_value(array_get($raw, 'treasurer_total_received'));
    if ($totalReceived === '') {
        $totalReceived = money_sum($moneyReceived, 'amount');
    }

    $totalSpent = money_value(array_get($raw, 'treasurer_total_spent'));
    if ($totalSpent === '') {
        $totalSpent = money_sum($moneySpent, 'amount');
    }

    $endingBalance = money_value(array_get($raw, 'treasurer_ending_balance'));
    if ($endingBalance === '' && $beginningBalance !== '') {
        $endingBalance = number_format(
            (float)$beginningBalance + (float)($totalReceived !== '' ? $totalReceived : 0) - (float)($totalSpent !== '' ? $totalSpent : 0),
            2,
            '.',
            ''
        );
    }

    return [
        'main_house_name' => trim((string)array_get($raw, 'main_house_name')),
        'meeting_date' => (string)array_get($raw, 'meeting_date'),
        'start_time' => (string)array_get($raw, 'start_time'),
        'end_time' => (string)array_get($raw, 'end_time'),

        'hsc_position_rows' => $positionRows,
        'roll_call_rows' => $rollCall,
        'mission_statement_read' => (string)array_get($raw, 'mission_statement_read'),

        'secretary_member_name' => trim((string)array_get($raw, 'secretary_member_name')),
        'secretary_comments' => trim((string)array_get($raw, 'secretary_comments')),
        'secretary_motion' => (int)array_get($raw, 'secretary_motion', 0),
        'secretary_yay' => trim((string)array_get($raw, 'secretary_yay')),
        'secretary_nay' => trim((string)array_get($raw, 'secretary_nay')),

        'treasurer_member_name' => trim((string)array_get($raw, 'treasurer_member_name')),
        'treasurer_beginning_balance' => $beginningBalance,
        'treasurer_money_received' => $moneyReceived,
        'treasurer_total_received' => $totalReceived,
        'treasurer_money_spent' => $moneySpent,
        'treasurer_total_spent' => $totalSpent,
        'treasurer_ending_balance' => $endingBalance,
        'treasurer_motion' => (int)array_get($raw, 'treasurer_motion', 0),
        'treasurer_yay' => trim((string)array_get($raw, 'treasurer_yay')),
        'treasurer_nay' => trim((string)array_get($raw, 'treasurer_nay')),

        'chair_member_name' => trim((string)array_get($raw, 'chair_member_name')),
        'chair_comments' => trim((string)array_get($raw, 'chair_comments')),
        'chair_motion' => (int)array_get($raw, 'chair_motion', 0),
        'chair_yay' => trim((string)array_get($raw, 'chair_yay')),
        'chair_nay' => trim((string)array_get($raw, 'chair_nay')),

        'vice_chair_member_name' => trim((string)array_get($raw, 'vice_chair_member_name')),
        'vice_chair_comments' => trim((string)array_get($raw, 'vice_chair_comments')),
        'vice_chair_motion' => (int)array_get($raw, 'vice_chair_motion', 0),
        'vice_chair_yay' => trim((string)array_get($raw, 'vice_chair_yay')),
        'vice_chair_nay' => trim((string)array_get($raw, 'vice_chair_nay')),

        'state_member_name' => trim((string)array_get($raw, 'state_member_name')),
        'state_comments' => trim((string)array_get($raw, 'state_comments')),
        'state_motion' => (int)array_get($raw, 'state_motion', 0),
        'state_yay' => trim((string)array_get($raw, 'state_yay')),
        'state_nay' => trim((string)array_get($raw, 'state_nay')),

        'reentry_member_name' => trim((string)array_get($raw, 'reentry_member_name')),
        'reentry_comments' => trim((string)array_get($raw, 'reentry_comments')),
        'reentry_motion' => (int)array_get($raw, 'reentry_motion', 0),
        'reentry_yay' => trim((string)array_get($raw, 'reentry_yay')),
        'reentry_nay' => trim((string)array_get($raw, 'reentry_nay')),

        'fundraiser_member_name' => trim((string)array_get($raw, 'fundraiser_member_name')),
        'fundraiser_comments' => trim((string)array_get($raw, 'fundraiser_comments')),
        'fundraiser_motion' => (int)array_get($raw, 'fundraiser_motion', 0),
        'fundraiser_yay' => trim((string)array_get($raw, 'fundraiser_yay')),
        'fundraiser_nay' => trim((string)array_get($raw, 'fundraiser_nay')),

        'outreach_member_name' => trim((string)array_get($raw, 'outreach_member_name')),
        'outreach_comments' => trim((string)array_get($raw, 'outreach_comments')),
        'outreach_motion' => (int)array_get($raw, 'outreach_motion', 0),
        'outreach_yay' => trim((string)array_get($raw, 'outreach_yay')),
        'outreach_nay' => trim((string)array_get($raw, 'outreach_nay')),

        'house_checkins' => $checkins,

        'unfinished_business_member_name' => trim((string)array_get($raw, 'unfinished_business_member_name')),
        'unfinished_business_comments' => trim((string)array_get($raw, 'unfinished_business_comments')),
        'unfinished_business_motion' => (int)array_get($raw, 'unfinished_business_motion', 0),
        'unfinished_business_yay' => trim((string)array_get($raw, 'unfinished_business_yay')),
        'unfinished_business_nay' => trim((string)array_get($raw, 'unfinished_business_nay')),

        'new_business_member_name' => trim((string)array_get($raw, 'new_business_member_name')),
        'new_business_comments' => trim((string)array_get($raw, 'new_business_comments')),
        'new_business_motion' => (int)array_get($raw, 'new_business_motion', 0),
        'new_business_yay' => trim((string)array_get($raw, 'new_business_yay')),
        'new_business_nay' => trim((string)array_get($raw, 'new_business_nay')),

        'secretary_name' => trim((string)array_get($raw, 'secretary_name')),
    ];
}

function collect_report_from_post(array $post): array
{
    $defaultPositions = default_hsc_positions();
    $positionRows = [];
    $positionNames = arr_posted($post, 'hsc_position_name', count($defaultPositions), '');
    $positionMembers = arr_posted($post, 'hsc_member_name', count($defaultPositions), '');

    for ($i = 0; $i < count($defaultPositions); $i++) {
        $positionRows[] = normalize_position_row([
            'position_name' => $positionNames[$i] ?: $defaultPositions[$i],
            'member_name' => $positionMembers[$i] ?? '',
            'present' => radio_value($post, 'hsc_present_' . $i),
        ]);
    }

    $rollCall = [];
    $houseNames = arr_posted($post, 'roll_house_name', 8, '');
    $presidentProxy = arr_posted($post, 'roll_president_proxy', 8, '');
    $meetingDays = arr_posted($post, 'roll_meeting_day', 8, '');
    $meetingTimes = arr_posted($post, 'roll_meeting_time', 8, '');
    for ($i = 0; $i < 8; $i++) {
        $rollCall[] = normalize_roll_call_row([
            'house_name' => $houseNames[$i] ?? '',
            'president_proxy' => $presidentProxy[$i] ?? '',
            'meeting_day' => $meetingDays[$i] ?? '',
            'meeting_time' => $meetingTimes[$i] ?? '',
            'hsr_completed' => radio_value($post, 'hsr_completed_' . $i),
        ]);
    }

    $checkins = [];
    $checkinHouses = arr_posted($post, 'checkin_house_name', 10, '');
    $checkinComments = arr_posted($post, 'checkin_comments', 10, '');
    for ($i = 0; $i < 10; $i++) {
        $checkins[] = normalize_checkin_row([
            'house_name' => $checkinHouses[$i] ?? '',
            'comments' => $checkinComments[$i] ?? '',
        ]);
    }

    $moneyReceived = [];
    $receivedDates = arr_posted($post, 'treasurer_received_date', 6, '');
    $receivedPurpose = arr_posted($post, 'treasurer_received_purpose', 6, '');
    $receivedAmount = arr_posted($post, 'treasurer_received_amount', 6, '');
    for ($i = 0; $i < 6; $i++) {
        $moneyReceived[] = normalize_money_received_row([
            'date' => $receivedDates[$i] ?? '',
            'purpose' => $receivedPurpose[$i] ?? '',
            'amount' => $receivedAmount[$i] ?? '',
        ]);
    }

    $moneySpent = [];
    $spentDates = arr_posted($post, 'treasurer_spent_date', 6, '');
    $spentPurpose = arr_posted($post, 'treasurer_spent_purpose', 6, '');
    $spentCheckNo = arr_posted($post, 'treasurer_spent_check_no', 6, '');
    $spentAmount = arr_posted($post, 'treasurer_spent_amount', 6, '');
    for ($i = 0; $i < 6; $i++) {
        $moneySpent[] = normalize_money_spent_row([
            'date' => $spentDates[$i] ?? '',
            'purpose' => $spentPurpose[$i] ?? '',
            'check_no' => $spentCheckNo[$i] ?? '',
            'amount' => $spentAmount[$i] ?? '',
        ]);
    }

    $totalReceived = money_sum($moneyReceived, 'amount');
    $totalSpent = money_sum($moneySpent, 'amount');
    $beginningBalance = money_value(posted($post, 'treasurer_beginning_balance'));
    $endingBalance = '';
    if ($beginningBalance !== '') {
        $endingBalance = number_format(
            (float)$beginningBalance + (float)($totalReceived !== '' ? $totalReceived : 0) - (float)($totalSpent !== '' ? $totalSpent : 0),
            2,
            '.',
            ''
        );
    }

    return normalize_report([
        'main_house_name' => trim((string)posted($post, 'main_house_name')),
        'meeting_date' => (string)posted($post, 'meeting_date'),
        'start_time' => (string)posted($post, 'start_time'),
        'end_time' => (string)posted($post, 'end_time'),

        'hsc_position_rows' => $positionRows,
        'roll_call_rows' => $rollCall,
        'mission_statement_read' => radio_value($post, 'mission_statement_read'),

        'secretary_member_name' => trim((string)posted($post, 'secretary_member_name')),
        'secretary_comments' => trim((string)posted($post, 'secretary_comments')),
        'secretary_motion' => checkbox_value($post, 'secretary_motion'),
        'secretary_yay' => vote_value($post, 'secretary_yay'),
        'secretary_nay' => vote_value($post, 'secretary_nay'),

        'treasurer_member_name' => trim((string)posted($post, 'treasurer_member_name')),
        'treasurer_beginning_balance' => $beginningBalance,
        'treasurer_money_received' => $moneyReceived,
        'treasurer_total_received' => $totalReceived,
        'treasurer_money_spent' => $moneySpent,
        'treasurer_total_spent' => $totalSpent,
        'treasurer_ending_balance' => $endingBalance,
        'treasurer_motion' => checkbox_value($post, 'treasurer_motion'),
        'treasurer_yay' => vote_value($post, 'treasurer_yay'),
        'treasurer_nay' => vote_value($post, 'treasurer_nay'),

        'chair_member_name' => trim((string)posted($post, 'chair_member_name')),
        'chair_comments' => trim((string)posted($post, 'chair_comments')),
        'chair_motion' => checkbox_value($post, 'chair_motion'),
        'chair_yay' => vote_value($post, 'chair_yay'),
        'chair_nay' => vote_value($post, 'chair_nay'),

        'vice_chair_member_name' => trim((string)posted($post, 'vice_chair_member_name')),
        'vice_chair_comments' => trim((string)posted($post, 'vice_chair_comments')),
        'vice_chair_motion' => checkbox_value($post, 'vice_chair_motion'),
        'vice_chair_yay' => vote_value($post, 'vice_chair_yay'),
        'vice_chair_nay' => vote_value($post, 'vice_chair_nay'),

        'state_member_name' => trim((string)posted($post, 'state_member_name')),
        'state_comments' => trim((string)posted($post, 'state_comments')),
        'state_motion' => checkbox_value($post, 'state_motion'),
        'state_yay' => vote_value($post, 'state_yay'),
        'state_nay' => vote_value($post, 'state_nay'),

        'reentry_member_name' => trim((string)posted($post, 'reentry_member_name')),
        'reentry_comments' => trim((string)posted($post, 'reentry_comments')),
        'reentry_motion' => checkbox_value($post, 'reentry_motion'),
        'reentry_yay' => vote_value($post, 'reentry_yay'),
        'reentry_nay' => vote_value($post, 'reentry_nay'),

        'fundraiser_member_name' => trim((string)posted($post, 'fundraiser_member_name')),
        'fundraiser_comments' => trim((string)posted($post, 'fundraiser_comments')),
        'fundraiser_motion' => checkbox_value($post, 'fundraiser_motion'),
        'fundraiser_yay' => vote_value($post, 'fundraiser_yay'),
        'fundraiser_nay' => vote_value($post, 'fundraiser_nay'),

        'outreach_member_name' => trim((string)posted($post, 'outreach_member_name')),
        'outreach_comments' => trim((string)posted($post, 'outreach_comments')),
        'outreach_motion' => checkbox_value($post, 'outreach_motion'),
        'outreach_yay' => vote_value($post, 'outreach_yay'),
        'outreach_nay' => vote_value($post, 'outreach_nay'),

        'house_checkins' => $checkins,

        'unfinished_business_member_name' => trim((string)posted($post, 'unfinished_business_member_name')),
        'unfinished_business_comments' => trim((string)posted($post, 'unfinished_business_comments')),
        'unfinished_business_motion' => checkbox_value($post, 'unfinished_business_motion'),
        'unfinished_business_yay' => vote_value($post, 'unfinished_business_yay'),
        'unfinished_business_nay' => vote_value($post, 'unfinished_business_nay'),

        'new_business_member_name' => trim((string)posted($post, 'new_business_member_name')),
        'new_business_comments' => trim((string)posted($post, 'new_business_comments')),
        'new_business_motion' => checkbox_value($post, 'new_business_motion'),
        'new_business_yay' => vote_value($post, 'new_business_yay'),
        'new_business_nay' => vote_value($post, 'new_business_nay'),

        'secretary_name' => trim((string)posted($post, 'secretary_name')),
    ]);
}

function save_report(PDO $pdo, array $report, int $currentRecordId): array
{
    $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        return [
            'success' => false,
            'record_id' => $currentRecordId,
            'message' => 'Unable to encode the report body as JSON.',
        ];
    }

    $houseName = $report['main_house_name'] !== '' ? $report['main_house_name'] : 'Draft';
    $meetingDate = $report['meeting_date'] !== '' ? $report['meeting_date'] : date('Y-m-d');

    if ($currentRecordId > 0) {
        $stmt = $pdo->prepare(
            'UPDATE hsc_meeting_minutes_json
             SET house_name = :house_name,
                 meeting_date = :meeting_date,
                 start_time = :start_time,
                 end_time = :end_time,
                 report_json = :report_json,
                 updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            ':house_name' => $houseName,
            ':meeting_date' => $meetingDate,
            ':start_time' => $report['start_time'] ?: null,
            ':end_time' => $report['end_time'] ?: null,
            ':report_json' => $json,
            ':id' => $currentRecordId,
        ]);

        return [
            'success' => true,
            'record_id' => $currentRecordId,
            'message' => 'Report updated successfully.',
        ];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO hsc_meeting_minutes_json
         (house_name, meeting_date, start_time, end_time, report_json, created_at, updated_at)
         VALUES (:house_name, :meeting_date, :start_time, :end_time, :report_json, NOW(), NOW())'
    );

    $stmt->execute([
        ':house_name' => $houseName,
        ':meeting_date' => $meetingDate,
        ':start_time' => $report['start_time'] ?: null,
        ':end_time' => $report['end_time'] ?: null,
        ':report_json' => $json,
    ]);

    return [
        'success' => true,
        'record_id' => (int)$pdo->lastInsertId(),
        'message' => 'Report saved successfully.',
    ];
}

/* =========================
   AUTO SAVE HANDLER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'autosave') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $report = collect_report_from_post($_POST);
        $currentRecordId = isset($_POST['record_id']) && ctype_digit((string)$_POST['record_id']) ? (int)$_POST['record_id'] : 0;

        $result = save_report($pdo, $report, $currentRecordId);

        echo json_encode([
            'success' => $result['success'],
            'record_id' => $result['record_id'],
            'message' => $result['message'],
            'saved_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'record_id' => 0,
            'message' => 'Autosave failed: ' . $e->getMessage(),
        ]);
    }
    exit;
}

/* =========================
   DEFAULT DATA
========================= */
$report = normalize_report([]);
$message = '';
$error = '';
$history = [];
$currentRecordId = 0;

/* =========================
   LOAD RECORD
========================= */
if (isset($_GET['load']) && ctype_digit((string)$_GET['load'])) {
    $stmt = $pdo->prepare('SELECT * FROM hsc_meeting_minutes_json WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['load']]);
    $loaded = $stmt->fetch();

    if ($loaded) {
        $decoded = json_decode((string)$loaded['report_json'], true);
        if (is_array($decoded)) {
            $report = normalize_report($decoded);
            $currentRecordId = (int)$loaded['id'];
            $message = 'Loaded saved report #' . $currentRecordId . '.';
        }
    }
}

/* =========================
   MANUAL SAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $report = collect_report_from_post($_POST);
    $currentRecordId = isset($_POST['record_id']) && ctype_digit((string)$_POST['record_id']) ? (int)$_POST['record_id'] : 0;

    try {
        $result = save_report($pdo, $report, $currentRecordId);
        $currentRecordId = (int)$result['record_id'];
        $message = $result['message'];
    } catch (Throwable $e) {
        $error = 'Save failed: ' . $e->getMessage();
    }
}

/* =========================
   HISTORY SEARCH
========================= */
$searchHouse = trim((string)($_GET['search_house'] ?? ''));
$searchFrom = (string)($_GET['search_from'] ?? '');
$searchTo = (string)($_GET['search_to'] ?? '');

$where = [];
$params = [];

if ($searchHouse !== '') {
    $where[] = 'house_name LIKE :house_name';
    $params[':house_name'] = '%' . $searchHouse . '%';
}
if ($searchFrom !== '') {
    $where[] = 'meeting_date >= :search_from';
    $params[':search_from'] = $searchFrom;
}
if ($searchTo !== '') {
    $where[] = 'meeting_date <= :search_to';
    $params[':search_to'] = $searchTo;
}

$sql = 'SELECT id, house_name, meeting_date, start_time, end_time, created_at, updated_at
        FROM hsc_meeting_minutes_json';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY meeting_date DESC, id DESC LIMIT 50';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$history = $stmt->fetchAll();

$logoPath = __DIR__ . '/../../images/oxford_house_logo.png';
$logoWeb = '../../images/oxford_house_logo.png';
$logoExists = is_file($logoPath);

function render_report_section(
    string $title,
    string $memberKey,
    string $commentsKey,
    string $motionKey,
    string $yayKey,
    string $nayKey,
    array $report,
    int $rows = 5,
    string $motionLabel = 'Motion Made, Seconded: Report Accepted'
): string {
    ob_start(); ?>
    <div class="report-section">
        <div class="section-header-row">
            <div class="section-title"><?= h($title) ?></div>
            <div class="member-name-box">
                <label>House Member Name</label>
                <input type="text" name="<?= h($memberKey) ?>" value="<?= h($report[$memberKey] ?? '') ?>">
            </div>
        </div>
        <div class="wide-lines">
            <textarea class="plain-textarea auto-resize" name="<?= h($commentsKey) ?>" rows="<?= $rows ?>"><?= h($report[$commentsKey] ?? '') ?></textarea>
        </div>
        <div class="motion-line">
            <label class="inline-check"><input type="checkbox" name="<?= h($motionKey) ?>" <?= !empty($report[$motionKey]) ? 'checked' : '' ?>> <span><?= h($motionLabel) ?></span></label>
            <span>Yay-<input class="vote-mini" type="number" name="<?= h($yayKey) ?>" value="<?= h($report[$yayKey] ?? '') ?>" min="0" max="99" step="1" inputmode="numeric"></span>
            <span>Nay-<input class="vote-mini" type="number" name="<?= h($nayKey) ?>" value="<?= h($report[$nayKey] ?? '') ?>" min="0" max="99" step="1" inputmode="numeric"></span>
        </div>
    </div>
    <?php
    return (string)ob_get_clean();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSC Meeting Minutes</title>
    <style>
        @page { size: Letter; margin: 0.35in; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e9ecef;
            color: #111;
            padding: 18px;
        }
        .toolbar {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto 18px auto;
            background: #fff;
            border: 1px solid #cfcfcf;
            padding: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
        }
        .toolbar h2, .toolbar h3 { margin: 0 0 10px; }
        .toolbar p { margin: 0 0 10px; }
        .message, .error, .save-status {
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .message {
            border: 1px solid #b7e1c1;
            background: #edf9f0;
        }
        .error {
            border: 1px solid #e3aaaa;
            background: #fff0f0;
        }
        .save-status {
            border: 1px solid #cfd7ff;
            background: #f4f7ff;
            font-weight: 700;
        }
        .top-actions, .search-grid {
            display: grid;
            gap: 10px;
        }
        .top-actions {
            grid-template-columns: 1fr auto auto;
            align-items: end;
            margin-bottom: 14px;
        }
        .search-grid {
            grid-template-columns: 2fr 1fr 1fr auto;
            align-items: end;
        }
        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        input[type="text"],
        input[type="date"],
        input[type="time"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 7px 8px;
            border: 1px solid #333;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            background: #fff;
        }
        button,
        .button-link {
            display: inline-block;
            border: 1px solid #111;
            background: #111;
            color: #fff;
            padding: 9px 14px;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }
        .button-link.alt,
        button.alt {
            background: #fff;
            color: #111;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }
        .history-table th,
        .history-table td {
            border: 1px solid #bbb;
            padding: 7px 8px;
            text-align: left;
        }
        .history-table th { background: #f4f4f4; }

        .paper {
            width: 8.5in;
            background: #fff;
            margin: 0 auto 16px auto;
            padding: 0.55in 0.48in 0.45in 0.48in;
            box-shadow: 0 2px 14px rgba(0,0,0,.10);
            position: relative;
            page-break-after: always;
            break-after: page;
        }
        .paper:last-of-type {
            page-break-after: auto;
            break-after: auto;
        }
        .page-inner {
            min-height: 9.75in;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 18px;
            align-items: center;
            margin-bottom: 8px;
        }
        .logo-box {
            width: 110px;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .logo-box img {
            max-width: 100%;
            max-height: 100%;
        }
        .logo-fallback {
            width: 110px;
            height: 110px;
            border: 2px solid #111;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
        }
        .title {
            text-align: center;
            font-weight: 900;
            letter-spacing: .5px;
            line-height: 1.02;
            font-size: 34px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1.8fr .9fr .9fr;
            gap: 10px;
            margin-top: 6px;
        }
        .field-block .field-label {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 0;
        }
        .line-input {
            border: 2px solid #222;
            height: 36px;
            padding: 6px 8px;
            font-size: 14px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
            margin-top: 12px;
        }
        .sub-title {
            font-size: 14px;
            font-weight: 700;
            margin-top: 4px;
        }
        .inline-check,
        .inline-radio {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
        }
        input[type="checkbox"],
        input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #111;
        }
        .roll-call-table,
        .history-section-table,
        .treasurer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .roll-call-table th,
        .roll-call-table td,
        .history-section-table th,
        .history-section-table td,
        .treasurer-table th,
        .treasurer-table td {
            border: 2px solid #222;
            padding: 4px;
            vertical-align: middle;
        }
        .roll-call-table th,
        .history-section-table th,
        .treasurer-table th {
            font-size: 11px;
            text-transform: uppercase;
            background: #f7f7f7;
            text-align: left;
        }
        .roll-call-table input[type="text"],
        .roll-call-table input[type="time"],
        .roll-call-table select,
        .history-section-table input[type="text"],
        .history-section-table textarea,
        .treasurer-table input[type="text"],
        .treasurer-table input[type="date"],
        .treasurer-table input[type="number"] {
            border: 0;
            width: 100%;
            padding: 6px;
            font-size: 12px;
            background: transparent;
        }
        .history-section-table textarea {
            min-height: 68px;
            resize: none;
            overflow: hidden;
            line-height: 1.5;
        }
        .yn-box {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            white-space: nowrap;
            flex-wrap: wrap;
        }
        .mission-row {
            border: 2px solid #222;
            padding: 10px 12px;
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            font-size: 13px;
            font-weight: 700;
        }
        .report-section {
            margin-top: 12px;
        }
        .section-header-row {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 14px;
            align-items: end;
            margin-bottom: 4px;
        }
        .member-name-box label {
            font-size: 11px;
            margin-bottom: 2px;
        }
        .member-name-box input {
            border: 2px solid #222;
            height: 34px;
        }
        .wide-lines {
            border: 2px solid #222;
            margin-top: 4px;
        }
        .plain-textarea {
            width: 100%;
            border: 0;
            padding: 8px;
            resize: none;
            overflow: hidden;
            background: transparent;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            min-height: 80px;
            display: block;
        }
        .motion-line {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            font-size: 12px;
            margin-top: 5px;
            flex-wrap: wrap;
        }
        .vote-mini {
            width: 38px;
            height: 26px;
            text-align: center;
            font-size: 12px;
            padding: 0;
            border: 2px solid #222;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            font-size: 11px;
            margin-top: 18px;
        }
        .footer-grid .center { font-weight: 700; }
        .footer-grid .right { text-align: right; }
        .signature-grid {
            display: grid;
            grid-template-columns: 1.5fr .8fr;
            gap: 14px;
            margin-top: 14px;
        }
        .page-spacer { flex: 1; }
        .screen-only { display: block; }
        .totals-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 8px;
        }
        .money-box label {
            font-size: 11px;
            margin-bottom: 2px;
        }
        .money-box input {
            border: 2px solid #222;
            height: 34px;
        }
        .money-box.readonly input {
            background: #f7f7f7;
            font-weight: 700;
        }
        .amount-field {
            position: relative;
        }
        .amount-field::before {
            content: "$";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            font-weight: 700;
            color: #111;
            pointer-events: none;
            z-index: 2;
        }
        .amount-field input[type="number"] {
            padding-left: 24px !important;
        }
        .business-half {
            display: block;
        }
        .business-half-bottom {
            margin-top: 14px;
        }

        @media print {
            @page {
                size: Letter;
                margin: 0.35in;
            }

            html, body {
                width: 100%;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            body {
                padding: 0;
            }

            .toolbar,
            .screen-only {
                display: none !important;
            }

            .paper {
                width: 8.5in !important;
                min-height: 10.3in !important;
                margin: 0 !important;
                padding: 0.45in 0.40in 0.35in 0.40in !important;
                box-shadow: none !important;
                background: #fff !important;
                page-break-before: auto;
                page-break-after: always;
                page-break-inside: avoid;
                break-before: auto;
                break-after: page;
                break-inside: avoid-page;
            }

            .paper:last-of-type {
                page-break-after: auto;
                break-after: auto;
            }

            .page-inner {
                min-height: 9.4in !important;
                display: flex;
                flex-direction: column;
                break-inside: avoid-page;
            }

            table,
            tr,
            td,
            th,
            .report-section,
            .section-header-row,
            .wide-lines,
            .history-section-table,
            .roll-call-table,
            .treasurer-table,
            .signature-grid,
            .footer-grid {
                break-inside: avoid-page;
                page-break-inside: avoid;
            }

            textarea {
                overflow: visible !important;
            }
        }
    </style>
</head>
<body>
<div class="toolbar screen-only">
    <h2>HSC Meeting Minutes</h2>

    <?php if ($message !== ''): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="search-box">
        <h3>Search History</h3>
        <form method="get" class="search-grid">
            <div>
                <label for="search_house">House Name</label>
                <input type="text" name="search_house" id="search_house" value="<?= h($searchHouse) ?>">
            </div>
            <div>
                <label for="search_from">From Date</label>
                <input type="date" name="search_from" id="search_from" value="<?= h($searchFrom) ?>">
            </div>
            <div>
                <label for="search_to">To Date</label>
                <input type="date" name="search_to" id="search_to" value="<?= h($searchTo) ?>">
            </div>
            <div>
                <button type="submit">Search</button>
            </div>
        </form>

        <table class="history-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>House</th>
                    <th>Meeting Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Updated</th>
                    <th>Load</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$history): ?>
                <tr><td colspan="7">No saved reports found.</td></tr>
            <?php else: ?>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= h($row['id']) ?></td>
                        <td><?= h($row['house_name']) ?></td>
                        <td><?= h($row['meeting_date']) ?></td>
                        <td><?= h((string)$row['start_time']) ?></td>
                        <td><?= h((string)$row['end_time']) ?></td>
                        <td><?= h((string)$row['updated_at']) ?></td>
                        <td><a class="button-link alt" href="?load=<?= (int)$row['id'] ?>&search_house=<?= urlencode($searchHouse) ?>&search_from=<?= urlencode($searchFrom) ?>&search_to=<?= urlencode($searchTo) ?>">Load</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="post" id="minutesForm">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="record_id" id="record_id" value="<?= (int)$currentRecordId ?>">

    <div class="toolbar screen-only" style="margin-bottom:16px;">
        <div class="top-actions">
            <div>
                <strong>Current record:</strong>
                <span id="currentRecordText"><?= $currentRecordId > 0 ? '#' . (int)$currentRecordId : 'New unsaved report' ?></span>
            </div>
            <button type="submit">Save Report</button>
            <button type="button" class="alt" onclick="window.print()">Print Form</button>
        </div>
        <div id="saveStatus" class="save-status">Autosave ready.</div>
    </div>

    <section class="paper">
        <div class="page-inner">
            <div class="header">
                <div class="logo-box">
                    <?php if ($logoExists): ?>
                        <img src="<?= h($logoWeb) ?>" alt="Oxford House Logo">
                    <?php else: ?>
                        <div class="logo-fallback">OH</div>
                    <?php endif; ?>
                </div>
                <div class="title">HSC<br>MEETING MINUTES</div>
            </div>

            <div class="meta-grid">
                <div class="field-block">
                    <div class="field-label">Main House / Chapter Name:</div>
                    <input class="line-input" type="text" name="main_house_name" value="<?= h($report['main_house_name']) ?>">
                </div>
                <div class="field-block">
                    <div class="field-label">Date of Meeting:</div>
                    <input class="line-input" type="date" name="meeting_date" value="<?= h($report['meeting_date']) ?>">
                </div>
                <div class="field-block">
                    <div class="field-label">Start Time:</div>
                    <input class="line-input" type="time" name="start_time" value="<?= h($report['start_time']) ?>">
                </div>
            </div>

            <div class="section-title">ROLL CALL FOR HSC POSITIONS</div>
            <table class="roll-call-table">
                <thead>
                    <tr>
                        <th style="width:28%;">Position Name</th>
                        <th style="width:28%;">Member Name</th>
                        <th style="width:44%;">Present</th>
                    </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($report['hsc_position_rows']); $i++): ?>
                    <tr>
                        <td>
                            <input type="text" name="hsc_position_name[]" value="<?= h($report['hsc_position_rows'][$i]['position_name']) ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="hsc_member_name[]" value="<?= h($report['hsc_position_rows'][$i]['member_name']) ?>">
                        </td>
                        <td>
                            <div class="yn-box" style="gap:14px;">
                                <label class="inline-radio">
                                    Yes
                                    <input type="radio" name="hsc_present_<?= $i ?>" value="Y" <?= (($report['hsc_position_rows'][$i]['present'] ?? '') === 'Y') ? 'checked' : '' ?>>
                                </label>
                                <label class="inline-radio">
                                    No
                                    <input type="radio" name="hsc_present_<?= $i ?>" value="N" <?= (($report['hsc_position_rows'][$i]['present'] ?? '') === 'N') ? 'checked' : '' ?>>
                                </label>
                                <label class="inline-radio">
                                    Excused
                                    <input type="radio" name="hsc_present_<?= $i ?>" value="E" <?= (($report['hsc_position_rows'][$i]['present'] ?? '') === 'E') ? 'checked' : '' ?>>
                                </label>
                            </div>
                        </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>

            <div class="section-title">ROLL CALL</div>
            <table class="roll-call-table">
                <thead>
                    <tr>
                        <th style="width:22%;">House Name</th>
                        <th style="width:20%;">President / Proxy</th>
                        <th style="width:16%;">House Meeting Day</th>
                        <th style="width:14%;">House Meeting Time</th>
                        <th style="width:28%;">HSR Reports Completed</th>
                    </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($report['roll_call_rows']); $i++): ?>
                    <tr>
                        <td><input type="text" name="roll_house_name[]" value="<?= h($report['roll_call_rows'][$i]['house_name']) ?>"></td>
                        <td><input type="text" name="roll_president_proxy[]" value="<?= h($report['roll_call_rows'][$i]['president_proxy']) ?>"></td>
                        <td>
                            <select name="roll_meeting_day[]">
                                <option value="">--</option>
                                <?php foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day): ?>
                                    <option value="<?= h($day) ?>" <?= selected_attr((string)($report['roll_call_rows'][$i]['meeting_day'] ?? ''), $day) ?>><?= h($day) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="time" name="roll_meeting_time[]" value="<?= h($report['roll_call_rows'][$i]['meeting_time']) ?>"></td>
                        <td>
                            <div class="yn-box">
                                <label class="inline-radio">Yes <input type="radio" name="hsr_completed_<?= $i ?>" value="Y" <?= ($report['roll_call_rows'][$i]['hsr_completed'] ?? '') === 'Y' ? 'checked' : '' ?>></label>
                                <label class="inline-radio">No <input type="radio" name="hsr_completed_<?= $i ?>" value="N" <?= ($report['roll_call_rows'][$i]['hsr_completed'] ?? '') === 'N' ? 'checked' : '' ?>></label>
                            </div>
                        </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>

            <div class="section-title">READING OF THE OXFORD HOUSE MISSION STATEMENT</div>
            <div class="mission-row">
                <span>Mission Statement Read</span>
                <div class="yn-box">
                    <label class="inline-radio">Yes <input type="radio" name="mission_statement_read" value="Y" <?= radio_checked($report, 'mission_statement_read', 'Y') ?>></label>
                    <label class="inline-radio">No <input type="radio" name="mission_statement_read" value="N" <?= radio_checked($report, 'mission_statement_read', 'N') ?>></label>
                </div>
            </div>

            <div class="report-section">
                <div class="section-header-row">
                    <div>
                        <div class="section-title">SECRETARY REPORT: READING OF THE LAST MEETING MINUTES</div>
                        <div class="sub-title">Additions or Corrections: (if applicable)</div>
                    </div>
                    <div class="member-name-box">
                        <label>House Member Name</label>
                        <input type="text" name="secretary_member_name" value="<?= h($report['secretary_member_name']) ?>">
                    </div>
                </div>
                <div class="wide-lines">
                    <textarea class="plain-textarea auto-resize" name="secretary_comments" rows="5"><?= h($report['secretary_comments']) ?></textarea>
                </div>
                <div class="motion-line">
                    <label class="inline-check"><input type="checkbox" name="secretary_motion" <?= checkbox_checked($report, 'secretary_motion') ?>> <span>Motion Made, Seconded: Accept Minutes</span></label>
                    <span>Yay-<input class="vote-mini" type="number" name="secretary_yay" value="<?= h($report['secretary_yay']) ?>" min="0" max="99" step="1" inputmode="numeric"></span>
                    <span>Nay-<input class="vote-mini" type="number" name="secretary_nay" value="<?= h($report['secretary_nay']) ?>" min="0" max="99" step="1" inputmode="numeric"></span>
                </div>
            </div>

            <div class="report-section">
                <div class="section-header-row">
                    <div class="section-title">TREASURER REPORT</div>
                    <div class="member-name-box">
                        <label>House Member Name</label>
                        <input type="text" name="treasurer_member_name" value="<?= h($report['treasurer_member_name']) ?>">
                    </div>
                </div>

                <div class="totals-grid">
                    <div class="money-box amount-field">
                        <label>Beginning Balance</label>
                        <input type="number" step="0.01" min="0" name="treasurer_beginning_balance" id="treasurer_beginning_balance" value="<?= h($report['treasurer_beginning_balance']) ?>">
                    </div>
                    <div class="money-box readonly amount-field">
                        <label>Total Received</label>
                        <input type="number" step="0.01" name="treasurer_total_received_display" id="treasurer_total_received" value="<?= h($report['treasurer_total_received']) ?>" readonly>
                    </div>
                    <div class="money-box readonly amount-field">
                        <label>Ending Balance</label>
                        <input type="number" step="0.01" name="treasurer_ending_balance_display" id="treasurer_ending_balance" value="<?= h($report['treasurer_ending_balance']) ?>" readonly>
                    </div>
                </div>

                <div class="sub-title" style="margin-top:10px;">Money Received</div>
                <table class="treasurer-table">
                    <thead>
                        <tr>
                            <th style="width:20%;">Date</th>
                            <th>Purpose</th>
                            <th style="width:22%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < count($report['treasurer_money_received']); $i++): ?>
                        <tr>
                            <td><input type="date" name="treasurer_received_date[]" value="<?= h($report['treasurer_money_received'][$i]['date']) ?>"></td>
                            <td><input type="text" name="treasurer_received_purpose[]" value="<?= h($report['treasurer_money_received'][$i]['purpose']) ?>"></td>
                            <td class="amount-field"><input class="treasurer-received-amount" type="number" step="0.01" min="0" name="treasurer_received_amount[]" value="<?= h($report['treasurer_money_received'][$i]['amount']) ?>"></td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>

                <div class="totals-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                    <div></div>
                    <div></div>
                    <div class="money-box readonly amount-field">
                        <label>Total Received</label>
                        <input type="number" step="0.01" name="treasurer_total_received_footer_display" id="treasurer_total_received_footer" value="<?= h($report['treasurer_total_received']) ?>" readonly>
                    </div>
                </div>

                <div class="sub-title" style="margin-top:10px;">Money Spent</div>
                <table class="treasurer-table">
                    <thead>
                        <tr>
                            <th style="width:18%;">Date</th>
                            <th>Purpose</th>
                            <th style="width:16%;">Check #</th>
                            <th style="width:18%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < count($report['treasurer_money_spent']); $i++): ?>
                        <tr>
                            <td><input type="date" name="treasurer_spent_date[]" value="<?= h($report['treasurer_money_spent'][$i]['date']) ?>"></td>
                            <td><input type="text" name="treasurer_spent_purpose[]" value="<?= h($report['treasurer_money_spent'][$i]['purpose']) ?>"></td>
                            <td><input type="text" name="treasurer_spent_check_no[]" value="<?= h($report['treasurer_money_spent'][$i]['check_no']) ?>"></td>
                            <td class="amount-field"><input class="treasurer-spent-amount" type="number" step="0.01" min="0" name="treasurer_spent_amount[]" value="<?= h($report['treasurer_money_spent'][$i]['amount']) ?>"></td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>

                <div class="totals-grid">
                    <div></div>
                    <div class="money-box readonly amount-field">
                        <label>Total Spent</label>
                        <input type="number" step="0.01" name="treasurer_total_spent_display" id="treasurer_total_spent" value="<?= h($report['treasurer_total_spent']) ?>" readonly>
                    </div>
                    <div class="money-box readonly amount-field">
                        <label>Ending Balance</label>
                        <input type="number" step="0.01" name="treasurer_ending_balance_footer_display" id="treasurer_ending_balance_footer" value="<?= h($report['treasurer_ending_balance']) ?>" readonly>
                    </div>
                </div>

                <div class="motion-line">
                    <label class="inline-check"><input type="checkbox" name="treasurer_motion" <?= checkbox_checked($report, 'treasurer_motion') ?>> <span>Motion Made, Seconded: Report Accepted</span></label>
                    <span>Yay-<input class="vote-mini" type="number" name="treasurer_yay" value="<?= h($report['treasurer_yay']) ?>" min="0" max="99" step="1" inputmode="numeric"></span>
                    <span>Nay-<input class="vote-mini" type="number" name="treasurer_nay" value="<?= h($report['treasurer_nay']) ?>" min="0" max="99" step="1" inputmode="numeric"></span>
                </div>
            </div>

            <?= render_report_section('CHAIR PERSON REPORT', 'chair_member_name', 'chair_comments', 'chair_motion', 'chair_yay', 'chair_nay', $report, 4) ?>

            <div class="page-spacer"></div>
            <div class="footer-grid">
                <div>&copy; Oxford House, Inc</div>
                <div class="center">Page 1 of 3</div>
                <div class="right">HSC Form</div>
            </div>
        </div>
    </section>

    <section class="paper">
        <div class="page-inner">
            <?= render_report_section('VICE CHAIR REPORT', 'vice_chair_member_name', 'vice_chair_comments', 'vice_chair_motion', 'vice_chair_yay', 'vice_chair_nay', $report, 4) ?>
            <?= render_report_section('OUTREACH REPORT', 'outreach_member_name', 'outreach_comments', 'outreach_motion', 'outreach_yay', 'outreach_nay', $report, 4) ?>
            <?= render_report_section('STATE REPORT', 'state_member_name', 'state_comments', 'state_motion', 'state_yay', 'state_nay', $report, 4) ?>
            <?= render_report_section('RE-ENTRY CHAIR REPORT', 'reentry_member_name', 'reentry_comments', 'reentry_motion', 'reentry_yay', 'reentry_nay', $report, 4) ?>
            <?= render_report_section('FUNDRAISER REPORT', 'fundraiser_member_name', 'fundraiser_comments', 'fundraiser_motion', 'fundraiser_yay', 'fundraiser_nay', $report, 4) ?>

            <div class="section-title">HOUSE CHECK-INS</div>
            <table class="history-section-table">
                <thead>
                    <tr>
                        <th style="width:26%;">House Name</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($report['house_checkins']); $i++): ?>
                    <tr>
                        <td><input type="text" name="checkin_house_name[]" value="<?= h($report['house_checkins'][$i]['house_name']) ?>"></td>
                        <td><textarea class="plain-textarea auto-resize" name="checkin_comments[]" rows="3"><?= h($report['house_checkins'][$i]['comments']) ?></textarea></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>

            <div class="page-spacer"></div>
            <div class="footer-grid">
                <div>&copy; Oxford House, Inc</div>
                <div class="center">Page 2 of 3</div>
                <div class="right">HSC Form</div>
            </div>
        </div>
    </section>

    <section class="paper">
        <div class="page-inner">
            <div class="business-half">
                <?= render_report_section('UNFINISHED BUSINESS', 'unfinished_business_member_name', 'unfinished_business_comments', 'unfinished_business_motion', 'unfinished_business_yay', 'unfinished_business_nay', $report, 8) ?>
            </div>

            <div class="business-half business-half-bottom">
                <?= render_report_section('NEW BUSINESS', 'new_business_member_name', 'new_business_comments', 'new_business_motion', 'new_business_yay', 'new_business_nay', $report, 8) ?>
            </div>

            <div class="signature-grid">
                <div>
                    <label>Secretary Name / Signature</label>
                    <input type="text" name="secretary_name" value="<?= h($report['secretary_name']) ?>">
                </div>
                <div>
                    <label>End Time</label>
                    <input type="time" name="end_time" value="<?= h($report['end_time']) ?>">
                </div>
            </div>

            <div class="page-spacer"></div>
            <div class="footer-grid">
                <div>&copy; Oxford House, Inc</div>
                <div class="center">Page 3 of 3</div>
                <div class="right">HSC Form</div>
            </div>
        </div>
    </section>
</form>

<script>
(function () {
    var form = document.getElementById('minutesForm');
    var recordIdInput = document.getElementById('record_id');
    var currentRecordText = document.getElementById('currentRecordText');
    var saveStatus = document.getElementById('saveStatus');
    var autosaveTimer = null;
    var autosaveInProgress = false;
    var queuedAutosave = false;
    var AUTOSAVE_DELAY = 1200;

    function parseMoney(value) {
        var num = parseFloat(value);
        return isNaN(num) ? 0 : num;
    }

    function sumInputs(selector) {
        var total = 0;
        document.querySelectorAll(selector).forEach(function (input) {
            total += parseMoney(input.value);
        });
        return total;
    }

    function formatMoney(num) {
        return num.toFixed(2);
    }

    function updateTreasurerTotals() {
        var beginningInput = document.getElementById('treasurer_beginning_balance');
        var beginning = parseMoney(beginningInput ? beginningInput.value : '0');
        var totalReceived = sumInputs('.treasurer-received-amount');
        var totalSpent = sumInputs('.treasurer-spent-amount');
        var ending = beginning + totalReceived - totalSpent;

        var totalReceivedTop = document.getElementById('treasurer_total_received');
        var totalReceivedBottom = document.getElementById('treasurer_total_received_footer');
        var totalSpentBox = document.getElementById('treasurer_total_spent');
        var endingTop = document.getElementById('treasurer_ending_balance');
        var endingBottom = document.getElementById('treasurer_ending_balance_footer');

        if (totalReceivedTop) totalReceivedTop.value = formatMoney(totalReceived);
        if (totalReceivedBottom) totalReceivedBottom.value = formatMoney(totalReceived);
        if (totalSpentBox) totalSpentBox.value = formatMoney(totalSpent);
        if (endingTop) endingTop.value = formatMoney(ending);
        if (endingBottom) endingBottom.value = formatMoney(ending);
    }

    function autoResizeTextarea(textarea) {
        if (!textarea) return;
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    function initAutoResize() {
        document.querySelectorAll('textarea').forEach(function (textarea) {
            autoResizeTextarea(textarea);
            textarea.addEventListener('input', function () {
                autoResizeTextarea(textarea);
            });
        });
    }

    function setStatus(text, isError) {
        if (!saveStatus) return;
        saveStatus.textContent = text;
        saveStatus.style.borderColor = isError ? '#e3aaaa' : '#cfd7ff';
        saveStatus.style.background = isError ? '#fff0f0' : '#f4f7ff';
    }

    function doAutosave() {
        if (autosaveInProgress) {
            queuedAutosave = true;
            return;
        }

        autosaveInProgress = true;
        queuedAutosave = false;
        setStatus('Saving...', false);

        var formData = new FormData(form);
        formData.set('action', 'autosave');
        formData.set('record_id', recordIdInput.value || '0');

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                throw new Error(data.message || 'Autosave failed.');
            }

            if (data.record_id) {
                recordIdInput.value = data.record_id;
                if (currentRecordText) {
                    currentRecordText.textContent = '#' + data.record_id;
                }
            }

            setStatus('Saved automatically at ' + (data.saved_at || ''), false);
        })
        .catch(function (error) {
            setStatus('Autosave error: ' + error.message, true);
        })
        .finally(function () {
            autosaveInProgress = false;
            if (queuedAutosave) {
                doAutosave();
            }
        });
    }

    function scheduleAutosave() {
        clearTimeout(autosaveTimer);
        setStatus('Changes detected...', false);
        autosaveTimer = setTimeout(doAutosave, AUTOSAVE_DELAY);
    }

    document.addEventListener('input', function (event) {
        if (
            event.target.matches('#treasurer_beginning_balance') ||
            event.target.matches('.treasurer-received-amount') ||
            event.target.matches('.treasurer-spent-amount')
        ) {
            updateTreasurerTotals();
        }

        if (event.target.matches('textarea')) {
            autoResizeTextarea(event.target);
        }

        if (
            event.target.matches('input') ||
            event.target.matches('textarea') ||
            event.target.matches('select')
        ) {
            scheduleAutosave();
        }
    });

    document.addEventListener('change', function (event) {
        if (
            event.target.matches('input[type="checkbox"]') ||
            event.target.matches('input[type="radio"]') ||
            event.target.matches('select') ||
            event.target.matches('input[type="date"]') ||
            event.target.matches('input[type="time"]')
        ) {
            scheduleAutosave();
        }
    });

    form.addEventListener('submit', function () {
        setStatus('Saving...', false);
    });

    window.addEventListener('beforeunload', function () {
        if (autosaveTimer) {
            clearTimeout(autosaveTimer);
        }
    });

    window.addEventListener('load', function () {
        initAutoResize();
        updateTreasurerTotals();
    });
})();
</script>
</body>
</html>