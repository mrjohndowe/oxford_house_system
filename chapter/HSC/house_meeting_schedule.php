<?php
declare(strict_types=1);

/**
 * House Visit Rotation Scheduler
 * - Single file PHP app
 * - Auto-generates a rotation for a user-selected number of months
 * - Optional Repeat Cycle to keep rotating after the unique cycle ends
 * - Host step control: move host house down by X each month
 * - Attempts to auto-load the default step from the latest HSC meeting minutes
 * - Meeting days/times editable per house
 * - Autosaves house settings to MySQL
 * - Add / delete houses
 * - Save generated schedules
 * - History dropdown to reload saved schedules
 */

require_once __DIR__ . '/../../extras/master_config.php';

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
CREATE TABLE IF NOT EXISTS house_visit_houses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(150) NOT NULL UNIQUE,
    meeting_day VARCHAR(20) NOT NULL DEFAULT '',
    meeting_time VARCHAR(10) NOT NULL DEFAULT '',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS house_visit_schedules (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    schedule_label VARCHAR(150) NOT NULL,
    base_month VARCHAR(7) NOT NULL DEFAULT '',
    month_count INT NOT NULL DEFAULT 1,
    repeat_cycle TINYINT(1) NOT NULL DEFAULT 0,
    step_size INT NOT NULL DEFAULT 1,
    month_label VARCHAR(50) NOT NULL,
    month_index INT NOT NULL DEFAULT 1,
    visiting_house VARCHAR(150) NOT NULL,
    host_house VARCHAR(150) NOT NULL,
    host_meeting_day VARCHAR(20) NOT NULL DEFAULT '',
    host_meeting_time VARCHAR(10) NOT NULL DEFAULT '',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_schedule_label (schedule_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* Upgrade older installs */
$existingColumns = $pdo->query("SHOW COLUMNS FROM house_visit_schedules")->fetchAll(PDO::FETCH_COLUMN);
$requiredColumns = [
    'base_month'   => "ALTER TABLE house_visit_schedules ADD COLUMN base_month VARCHAR(7) NOT NULL DEFAULT '' AFTER schedule_label",
    'month_count'  => "ALTER TABLE house_visit_schedules ADD COLUMN month_count INT NOT NULL DEFAULT 1 AFTER base_month",
    'repeat_cycle' => "ALTER TABLE house_visit_schedules ADD COLUMN repeat_cycle TINYINT(1) NOT NULL DEFAULT 0 AFTER month_count",
    'step_size'    => "ALTER TABLE house_visit_schedules ADD COLUMN step_size INT NOT NULL DEFAULT 1 AFTER repeat_cycle",
];
foreach ($requiredColumns as $column => $sql) {
    if (!in_array($column, $existingColumns, true)) {
        $pdo->exec($sql);
    }
}

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fetchHouses(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT house_name, meeting_day, meeting_time
        FROM house_visit_houses
        ORDER BY house_name ASC
    ");
    return $stmt->fetchAll();
}

function monthLabelFromOffset(DateTimeImmutable $baseDate, int $offset): string
{
    return $baseDate->modify('first day of +' . $offset . ' month')->format('F Y');
}

function normalizeMonthCount(mixed $value, int $default = 2): int
{
    $months = (int)$value;
    if ($months < 1) {
        return $default;
    }
    if ($months > 24) {
        return 24;
    }
    return $months;
}

function normalizeStepSize(mixed $value, int $default = 1): int
{
    $step = (int)$value;
    if ($step < 1) {
        return $default;
    }
    if ($step > 99) {
        return 99;
    }
    return $step;
}

function isTruthy(mixed $value): bool
{
    return in_array((string)$value, ['1', 'true', 'on', 'yes'], true);
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = :table_name
    ");
    $stmt->execute([':table_name' => $tableName]);
    return (int)$stmt->fetchColumn() > 0;
}

function getTableColumns(PDO $pdo, string $tableName): array
{
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = :table_name
        ORDER BY ORDINAL_POSITION ASC
    ");
    $stmt->execute([':table_name' => $tableName]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function firstExistingColumn(array $columns, array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }
    return null;
}

function detectLatestOrderClause(array $columns): string
{
    $orderCandidates = ['meeting_date', 'minutes_date', 'date', 'created_at', 'updated_at', 'id'];
    $parts = [];

    foreach ($orderCandidates as $col) {
        if (in_array($col, $columns, true)) {
            $parts[] = "`{$col}` DESC";
        }
    }

    if (!$parts) {
        return '1';
    }

    return implode(', ', $parts);
}

function extractStepFromText(string $text): ?int
{
    $patterns = [
        '/(?:rotation\s*step|host\s*step|step\s*size|visit\s*step|house\s*visit\s*step)\s*[:=\-]?\s*(\d{1,2})/i',
        '/move\s+(?:down|host(?:\s+house)?)\s+by\s+(\d{1,2})/i',
        '/default\s*step\s*[:=\-]?\s*(\d{1,2})/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $m)) {
            $step = (int)$m[1];
            if ($step >= 1 && $step <= 99) {
                return $step;
            }
        }
    }

    return null;
}

/**
 * Best-effort HSC minutes default step lookup.
 *
 * Tries, in order:
 * 1) HSC-specific tables with numeric step columns
 * 2) Generic minutes tables with HSC discriminator + numeric step columns
 * 3) Text/note columns in those tables and regex extraction
 */
function findDefaultStepFromHscMeetingMinutes(PDO $pdo): array
{
    $result = [
        'step'   => 2,
        'source' => 'fallback',
        'label'  => 'Fallback default',
    ];

    $tableCandidates = [
        'hsc_meeting_minutes',
        'hsc_minutes',
        'meeting_minutes',
        'minutes',
        'house_meeting_minutes',
        'house_minutes',
    ];

    $stepColumnCandidates = [
        'visit_rotation_step',
        'rotation_step',
        'default_rotation_step',
        'host_step',
        'step_size',
        'visit_step',
        'house_visit_step',
    ];

    $typeColumnCandidates = [
        'meeting_type',
        'minutes_type',
        'category',
        'committee',
        'meeting_name',
        'type',
    ];

    $dateColumnCandidates = [
        'meeting_date',
        'minutes_date',
        'date',
        'created_at',
        'updated_at',
        'id',
    ];

    $textColumnCandidates = [
        'minutes_text',
        'notes',
        'content',
        'body',
        'minutes_body',
        'description',
        'details',
    ];

    foreach ($tableCandidates as $table) {
        if (!tableExists($pdo, $table)) {
            continue;
        }

        $columns = getTableColumns($pdo, $table);
        if (!$columns) {
            continue;
        }

        $stepColumn = firstExistingColumn($columns, $stepColumnCandidates);
        $typeColumn = firstExistingColumn($columns, $typeColumnCandidates);
        $dateOrder   = detectLatestOrderClause($columns);

        if ($stepColumn !== null) {
            if ($typeColumn !== null) {
                $sql = "
                    SELECT `{$stepColumn}` AS step_value
                    FROM `{$table}`
                    WHERE `{$typeColumn}` LIKE :meeting_type
                      AND `{$stepColumn}` IS NOT NULL
                      AND `{$stepColumn}` <> ''
                    ORDER BY {$dateOrder}
                    LIMIT 1
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':meeting_type' => '%HSC%']);
            } else {
                $sql = "
                    SELECT `{$stepColumn}` AS step_value
                    FROM `{$table}`
                    WHERE `{$stepColumn}` IS NOT NULL
                      AND `{$stepColumn}` <> ''
                    ORDER BY {$dateOrder}
                    LIMIT 1
                ";
                $stmt = $pdo->query($sql);
            }

            $row = $stmt->fetch();
            if ($row && isset($row['step_value'])) {
                $step = normalizeStepSize($row['step_value'], 1);
                if ($step >= 1) {
                    return [
                        'step'   => $step,
                        'source' => 'hsc_minutes_column',
                        'label'  => "HSC meeting minutes ({$table}.{$stepColumn})",
                    ];
                }
            }
        }

        $textColumn = firstExistingColumn($columns, $textColumnCandidates);
        if ($textColumn !== null) {
            if ($typeColumn !== null) {
                $sql = "
                    SELECT `{$textColumn}` AS minutes_text
                    FROM `{$table}`
                    WHERE `{$typeColumn}` LIKE :meeting_type
                      AND `{$textColumn}` IS NOT NULL
                      AND `{$textColumn}` <> ''
                    ORDER BY {$dateOrder}
                    LIMIT 8
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':meeting_type' => '%HSC%']);
            } else {
                $sql = "
                    SELECT `{$textColumn}` AS minutes_text
                    FROM `{$table}`
                    WHERE `{$textColumn}` IS NOT NULL
                      AND `{$textColumn}` <> ''
                    ORDER BY {$dateOrder}
                    LIMIT 8
                ";
                $stmt = $pdo->query($sql);
            }

            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $text = (string)($row['minutes_text'] ?? '');
                $step = extractStepFromText($text);
                if ($step !== null) {
                    return [
                        'step'   => $step,
                        'source' => 'hsc_minutes_text',
                        'label'  => "HSC meeting minutes text ({$table}.{$textColumn})",
                    ];
                }
            }
        }
    }

    return $result;
}

/**
 * Build ordered usable shifts for a given house count and step size.
 * Month 1 shift starts at 1.
 * Each following month adds step size.
 * Shift 0 is skipped to avoid self-visits.
 *
 * Example with 8 houses, step 2:
 * 1, 3, 5, 7
 */
function getUsableShiftList(int $houseCount, int $stepSize): array
{
    if ($houseCount < 2) {
        return [];
    }

    $usableShifts = [];
    $seen = [];

    for ($monthOffset = 0; $monthOffset < 1000; $monthOffset++) {
        $shift = ((($monthOffset + 1) * $stepSize)) % $houseCount;

        if ($shift === 0) {
            continue;
        }

        if (!isset($seen[$shift])) {
            $seen[$shift] = true;
            $usableShifts[] = $shift;
        }

        if (count($usableShifts) >= ($houseCount - 1)) {
            break;
        }
    }

    return $usableShifts;
}

/**
 * Builds a schedule using step control.
 *
 * Host movement:
 * Month 1 uses shift 1
 * Later months increase by step size
 *
 * If repeat cycle is OFF:
 * - only unique usable shifts are used once
 *
 * If repeat cycle is ON:
 * - usable shifts loop
 */
function buildRotation(
    array $houses,
    DateTimeImmutable $baseDate,
    int $months = 2,
    bool $repeatCycle = false,
    int $stepSize = 1
): array {
    $count = count($houses);
    $schedule = [];

    if ($count < 2) {
        return $schedule;
    }

    $usableShifts = getUsableShiftList($count, $stepSize);
    if (!$usableShifts) {
        return $schedule;
    }

    $uniqueCycleLength = count($usableShifts);
    $monthsToGenerate = $repeatCycle ? $months : min($months, $uniqueCycleLength);

    for ($monthOffset = 0; $monthOffset < $monthsToGenerate; $monthOffset++) {
        $monthLabel = monthLabelFromOffset($baseDate, $monthOffset);
        $rows = [];
        $shift = $usableShifts[$monthOffset % $uniqueCycleLength];

        for ($i = 0; $i < $count; $i++) {
            $visitor = $houses[$i];
            $hostIndex = ($i + $shift) % $count;
            $host = $houses[$hostIndex];

            $rows[] = [
                'month_label'       => $monthLabel,
                'month_index'       => $monthOffset + 1,
                // 'shift_used'        => $shift,
                'visiting_house'    => $visitor['house_name'],
                'host_house'        => $host['house_name'],
                'host_meeting_day'  => $host['meeting_day'],
                'host_meeting_time' => $host['meeting_time'],
            ];
        }

        $schedule[] = [
            'month_label' => $monthLabel,
            // 'shift_used'  => $shift,
            'rows'        => $rows,
        ];
    }

    return $schedule;
}

/* =========================
   DEFAULT DATA SEED
========================= */
$defaultHouses = [
    ['house_name' => 'Eagles Nest', 'meeting_day' => 'Sunday', 'meeting_time' => '15:30'],
    ['house_name' => 'Northmoor', 'meeting_day' => 'Monday', 'meeting_time' => '14:30'],
    ['house_name' => 'North Place', 'meeting_day' => 'Friday', 'meeting_time' => '18:30'],
    ['house_name' => 'Norwich', 'meeting_day' => 'Wednesday', 'meeting_time' => '17:00'],
    ['house_name' => 'Otro Dia', 'meeting_day' => 'Sunday', 'meeting_time' => '17:00'],
    ['house_name' => 'Red Creek', 'meeting_day' => 'Sunday', 'meeting_time' => '18:00'],
    ['house_name' => 'Starlight', 'meeting_day' => 'Sunday', 'meeting_time' => '10:00'],
    ['house_name' => 'Sunset Park', 'meeting_day' => 'Sunday', 'meeting_time' => '14:00'],
];

$existingCount = (int)$pdo->query("SELECT COUNT(*) FROM house_visit_houses")->fetchColumn();
if ($existingCount === 0) {
    $seedStmt = $pdo->prepare("
        INSERT INTO house_visit_houses (house_name, meeting_day, meeting_time)
        VALUES (:house_name, :meeting_day, :meeting_time)
    ");

    foreach ($defaultHouses as $house) {
        $seedStmt->execute([
            ':house_name'   => $house['house_name'],
            ':meeting_day'  => $house['meeting_day'],
            ':meeting_time' => $house['meeting_time'],
        ]);
    }
}

/* =========================
   HSC DEFAULT STEP
========================= */
$hscStepInfo = findDefaultStepFromHscMeetingMinutes($pdo);
$hscDefaultStep = normalizeStepSize($hscStepInfo['step'] ?? 1, 1);

/* =========================
   AJAX ACTIONS
========================= */
$action = $_POST['action'] ?? '';

if ($action === 'autosave_house') {
    $houseName = trim((string)($_POST['house_name'] ?? ''));
    $meetingDay = trim((string)($_POST['meeting_day'] ?? ''));
    $meetingTime = trim((string)($_POST['meeting_time'] ?? ''));

    if ($houseName === '') {
        jsonResponse(['ok' => false, 'message' => 'House name is required.'], 422);
    }

    $stmt = $pdo->prepare("
        INSERT INTO house_visit_houses (house_name, meeting_day, meeting_time)
        VALUES (:house_name, :meeting_day, :meeting_time)
        ON DUPLICATE KEY UPDATE
            meeting_day = VALUES(meeting_day),
            meeting_time = VALUES(meeting_time)
    ");
    $stmt->execute([
        ':house_name'   => $houseName,
        ':meeting_day'  => $meetingDay,
        ':meeting_time' => $meetingTime,
    ]);

    jsonResponse(['ok' => true, 'message' => 'Meeting info saved.']);
}

if ($action === 'add_house') {
    $houseName = trim((string)($_POST['house_name'] ?? ''));
    $meetingDay = trim((string)($_POST['meeting_day'] ?? ''));
    $meetingTime = trim((string)($_POST['meeting_time'] ?? ''));

    if ($houseName === '' || $meetingDay === '' || $meetingTime === '') {
        jsonResponse(['ok' => false, 'message' => 'House name, day, and time are required.'], 422);
    }

    $stmt = $pdo->prepare("
        INSERT INTO house_visit_houses (house_name, meeting_day, meeting_time)
        VALUES (:house_name, :meeting_day, :meeting_time)
        ON DUPLICATE KEY UPDATE
            meeting_day = VALUES(meeting_day),
            meeting_time = VALUES(meeting_time)
    ");
    $stmt->execute([
        ':house_name'   => $houseName,
        ':meeting_day'  => $meetingDay,
        ':meeting_time' => $meetingTime,
    ]);

    jsonResponse(['ok' => true, 'message' => 'House added/updated.']);
}

if ($action === 'delete_house') {
    $houseName = trim((string)($_POST['house_name'] ?? ''));

    if ($houseName === '') {
        jsonResponse(['ok' => false, 'message' => 'House name is required.'], 422);
    }

    $stmt = $pdo->prepare("DELETE FROM house_visit_houses WHERE house_name = :house_name");
    $stmt->execute([':house_name' => $houseName]);

    jsonResponse(['ok' => true, 'message' => 'House deleted.']);
}

if ($action === 'save_schedule') {
    $scheduleLabel = trim((string)($_POST['schedule_label'] ?? ''));
    $baseMonth = trim((string)($_POST['base_month'] ?? date('Y-m')));
    $monthCount = normalizeMonthCount($_POST['month_count'] ?? 2, 2);
    $repeatCycle = isTruthy($_POST['repeat_cycle'] ?? '0');
    $stepSize = normalizeStepSize($_POST['step_size'] ?? $hscDefaultStep, $hscDefaultStep);

    if ($scheduleLabel === '') {
        $scheduleLabel = 'Rotation ' . date('Y-m-d H:i:s');
    }

    try {
        $baseDate = new DateTimeImmutable($baseMonth . '-01');
    } catch (Throwable $e) {
        $baseMonth = date('Y-m');
        $baseDate = new DateTimeImmutable($baseMonth . '-01');
    }

    $houses = fetchHouses($pdo);
    if (count($houses) < 2) {
        jsonResponse(['ok' => false, 'message' => 'At least 2 houses are required.'], 422);
    }

    $rotation = buildRotation($houses, $baseDate, $monthCount, $repeatCycle, $stepSize);

    $pdo->prepare("DELETE FROM house_visit_schedules WHERE schedule_label = :schedule_label")
        ->execute([':schedule_label' => $scheduleLabel]);

    $insert = $pdo->prepare("
        INSERT INTO house_visit_schedules (
            schedule_label,
            base_month,
            month_count,
            repeat_cycle,
            step_size,
            month_label,
            month_index,
            visiting_house,
            host_house,
            host_meeting_day,
            host_meeting_time
        ) VALUES (
            :schedule_label,
            :base_month,
            :month_count,
            :repeat_cycle,
            :step_size,
            :month_label,
            :month_index,
            :visiting_house,
            :host_house,
            :host_meeting_day,
            :host_meeting_time
        )
    ");

    foreach ($rotation as $month) {
        foreach ($month['rows'] as $row) {
            $insert->execute([
                ':schedule_label'     => $scheduleLabel,
                ':base_month'         => $baseMonth,
                ':month_count'        => $monthCount,
                ':repeat_cycle'       => $repeatCycle ? 1 : 0,
                ':step_size'          => $stepSize,
                ':month_label'        => $row['month_label'],
                ':month_index'        => $row['month_index'],
                ':visiting_house'     => $row['visiting_house'],
                ':host_house'         => $row['host_house'],
                ':host_meeting_day'   => $row['host_meeting_day'],
                ':host_meeting_time'  => $row['host_meeting_time'],
            ]);
        }
    }

    jsonResponse([
        'ok' => true,
        'message' => count($rotation) . ' month schedule saved.'
    ]);
}

/* =========================
   PAGE DATA
========================= */
$houses = fetchHouses($pdo);

$baseMonth = $_GET['base_month'] ?? date('Y-m');
$monthCount = normalizeMonthCount($_GET['month_count'] ?? 2, 2);
$repeatCycle = isTruthy($_GET['repeat_cycle'] ?? '0');
$stepSize = normalizeStepSize($_GET['step_size'] ?? $hscDefaultStep, $hscDefaultStep);

try {
    $baseDate = new DateTimeImmutable($baseMonth . '-01');
} catch (Throwable $e) {
    $baseMonth = date('Y-m');
    $baseDate = new DateTimeImmutable($baseMonth . '-01');
}

$rotation = buildRotation($houses, $baseDate, $monthCount, $repeatCycle, $stepSize);

$savedSchedules = $pdo->query("
    SELECT
        schedule_label,
        MIN(created_at) AS created_at,
        COUNT(*) AS total_rows,
        MAX(base_month) AS base_month,
        MAX(month_count) AS month_count,
        MAX(repeat_cycle) AS repeat_cycle,
        MAX(step_size) AS step_size
    FROM house_visit_schedules
    GROUP BY schedule_label
    ORDER BY MAX(created_at) DESC
")->fetchAll();

$selectedScheduleLabel = trim((string)($_GET['schedule_label'] ?? ''));
$loadedSchedule = [];
$loadedMeta = [
    'base_month'   => '',
    'month_count'  => '',
    'repeat_cycle' => '',
    'step_size'    => '',
];

if ($selectedScheduleLabel !== '') {
    $loadStmt = $pdo->prepare("
        SELECT
            schedule_label,
            base_month,
            month_count,
            repeat_cycle,
            step_size,
            month_label,
            month_index,
            visiting_house,
            host_house,
            host_meeting_day,
            host_meeting_time
        FROM house_visit_schedules
        WHERE schedule_label = :schedule_label
        ORDER BY month_index ASC, visiting_house ASC
    ");
    $loadStmt->execute([
        ':schedule_label' => $selectedScheduleLabel
    ]);
    $loadedRows = $loadStmt->fetchAll();

    if ($loadedRows) {
        $loadedMeta = [
            'base_month'   => (string)($loadedRows[0]['base_month'] ?? ''),
            'month_count'  => (string)($loadedRows[0]['month_count'] ?? ''),
            'repeat_cycle' => (string)($loadedRows[0]['repeat_cycle'] ?? ''),
            'step_size'    => (string)($loadedRows[0]['step_size'] ?? ''),
        ];

        foreach ($loadedRows as $row) {
            $monthKey = $row['month_index'] . '|' . $row['month_label'];

            if (!isset($loadedSchedule[$monthKey])) {
                $loadedSchedule[$monthKey] = [
                    'month_label' => $row['month_label'],
                    'rows' => []
                ];
            }

            $loadedSchedule[$monthKey]['rows'][] = [
                'visiting_house'    => $row['visiting_house'],
                'host_house'        => $row['host_house'],
                'host_meeting_day'  => $row['host_meeting_day'],
                'host_meeting_time' => $row['host_meeting_time'],
            ];
        }

        $loadedSchedule = array_values($loadedSchedule);
    }
}

$displayRotation = !empty($loadedSchedule) ? $loadedSchedule : $rotation;
$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$monthOptions = [1, 2, 3, 4, 6, 12];
$usableShifts = getUsableShiftList(count($houses), $stepSize);
$uniqueCycleLength = count($usableShifts);
$truncatedWithoutRepeat = !$repeatCycle && $monthCount > $uniqueCycleLength && count($houses) >= 2;

$defaultScheduleName = 'Rotation ' . $baseDate->format('F Y') . ' - ' . $monthCount . ' Month' . ($monthCount > 1 ? 's' : '') . ' - Step ' . $stepSize . ($repeatCycle ? ' (Repeat Cycle)' : '');

if ($selectedScheduleLabel !== '' && !empty($loadedSchedule)) {
    if ($loadedMeta['base_month'] !== '') {
        $baseMonth = $loadedMeta['base_month'];
    }
    if ($loadedMeta['month_count'] !== '') {
        $monthCount = normalizeMonthCount($loadedMeta['month_count'], $monthCount);
    }
    if ($loadedMeta['step_size'] !== '') {
        $stepSize = normalizeStepSize($loadedMeta['step_size'], $stepSize);
    }
    if ($loadedMeta['repeat_cycle'] !== '') {
        $repeatCycle = isTruthy($loadedMeta['repeat_cycle']);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>House Visit Rotation Scheduler</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #fff;
            --border: #d9e1ea;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #1d4ed8;
            --primary-dark: #163fae;
            --success: #0f9d58;
            --danger: #c62828;
            --warning-bg: #fff8e6;
            --warning-text: #8a6100;
            --info-bg: #eef5ff;
            --info-text: #1d4ed8;
            --shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .wrap {
            max-width: 1500px;
            margin: 0 auto;
            padding: 24px;
        }

        .titlebar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 30px;
        }

        .subtitle {
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: 680px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .card h2,
        .card h3 {
            margin-top: 0;
        }

        .controls {
            display: grid;
            grid-template-columns: 180px 150px 120px max-content max-content;
            gap: 10px;
            align-items: end;
        }

        .schedule-name-row {
            margin-top: 12px;
            display: block;
        }

        .schedule-name-wrap {
            width: 100%;
            max-width: 420px;
        }

        .repeat-cycle-row {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .repeat-cycle-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: bold;
            color: var(--text);
            cursor: pointer;
        }

        .repeat-cycle-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin: 0;
        }

        .helper-note {
            font-size: 12px;
            color: var(--muted);
        }

        .house-setup-grid {
            display: grid;
            gap: 10px;
        }

        .house-row {
            display: grid;
            grid-template-columns: 220px 160px 120px 90px;
            gap: 10px;
            align-items: end;
            margin-bottom: 10px;
        }

        label.small {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        input[type="text"],
        input[type="time"],
        input[type="month"],
        input[type="number"] {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            background: #fff;
        }

        input[type="month"] { width: 180px; }
        input[type="number"] { width: 120px; }

        select {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            background: #fff;
        }

        #month_count { width: 150px; }
        #step_size { width: 120px; }

        #new_house_name,
        .house-name { width: 220px; }

        #new_meeting_day,
        .meeting-day { width: 160px; }

        #new_meeting_time,
        .meeting-time { width: 120px; }

        #schedule_label_input {
            width: 420px;
            max-width: 100%;
        }

        button,
        .btn {
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            width: fit-content;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-success {
            background: #e8f7ee;
            color: var(--success);
        }

        .btn-danger {
            background: #fdecec;
            color: var(--danger);
        }

        .btn-secondary {
            background: #eef2ff;
            color: #243b75;
        }

        .rotation-card {
            padding: 14px;
        }

        .rotation-month {
            margin-bottom: 14px;
        }

        .rotation-month h3 {
            margin-bottom: 8px;
            font-size: 18px;
        }

        .shift-badge {
            display: inline-block;
            margin-left: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            background: #eef2ff;
            color: #243b75;
            vertical-align: middle;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid var(--border);
            padding: 8px 9px;
            text-align: left;
            font-size: 13px;
            line-height: 1.25;
            word-wrap: break-word;
        }

        th {
            background: #f8fafc;
        }

        .muted {
            color: var(--muted);
        }

        .status {
            display: none;
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
        }

        .status.show { display: block; }
        .status.success { background: #e8f7ee; color: #17663f; }
        .status.error { background: #fdecec; color: #8d1e1e; }

        .notice {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            background: var(--warning-bg);
            color: var(--warning-text);
        }

        .info-box {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            background: var(--info-bg);
            color: var(--info-text);
        }

        .top-space {
            margin-top: 12px;
        }

        #history_schedule_label {
            width: 100%;
        }

        .inline-mono {
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
        }

        @media print {
            .no-print,
            .left-panel {
                display: none !important;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .card {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            body {
                background: #fff;
            }
        }

        @media (max-width: 1320px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .controls,
            .house-row {
                grid-template-columns: 1fr;
            }

            input[type="month"],
            input[type="number"],
            #month_count,
            #step_size,
            #new_house_name,
            .house-name,
            #new_meeting_day,
            .meeting-day,
            #new_meeting_time,
            .meeting-time,
            #schedule_label_input,
            .btn {
                width: 100%;
                max-width: 100%;
            }

            .repeat-cycle-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="titlebar">
        <div>
            <h1>House Visit Rotation Scheduler</h1>
            <div class="subtitle">Auto-generates a rotation for the number of months you choose, auto-saves meeting day/time changes, uses a step control for host movement, and tries to pull the default step from the latest HSC meeting minutes.</div>
        </div>
        <button type="button" class="btn btn-secondary no-print" onclick="window.print()">Print Rotation</button>
    </div>

    <div class="grid">
        <div class="left-panel">
            <div class="card">
                <h2>Rotation Controls</h2>
                <form method="get" id="rotationForm">
                    <div class="controls">
                        <div>
                            <label class="small" for="base_month">Starting Month</label>
                            <input type="month" id="base_month" name="base_month" value="<?= h($baseMonth) ?>">
                        </div>
                        <div>
                            <label class="small" for="month_count">Months To Display</label>
                            <select id="month_count" name="month_count">
                                <?php foreach ($monthOptions as $opt): ?>
                                    <option value="<?= h((string)$opt) ?>" <?= $monthCount === $opt ? 'selected' : '' ?>>
                                        <?= h((string)$opt) ?> Month<?= $opt > 1 ? 's' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="small" for="step_size">Step Size</label>
                            <input type="number" id="step_size" name="step_size" min="1" max="99" value="<?= h((string)$stepSize) ?>">
                        </div>
                        <br>
                        <div>
                            <button type="submit" class="btn btn-primary">Generate</button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success" id="saveScheduleBtn">Save Schedule</button>
                        </div>
                    </div>

                    <div class="repeat-cycle-row">
                        <label class="repeat-cycle-label" for="repeat_cycle">
                            <input type="checkbox" id="repeat_cycle" name="repeat_cycle" value="1" <?= $repeatCycle ? 'checked' : '' ?>>
                            Repeat Cycle
                        </label>
                        <span class="helper-note">When enabled, the rotation loops after the unique cycle ends. When disabled, it stops at <?= h((string)$uniqueCycleLength) ?> unique month<?= $uniqueCycleLength !== 1 ? 's' : '' ?> for this step size.</span>
                    </div>

                    <div class="schedule-name-row">
                        <div class="schedule-name-wrap">
                            <label class="small" for="schedule_label_input">Schedule Name</label>
                            <input type="text" id="schedule_label_input" value="<?= h($selectedScheduleLabel !== '' ? $selectedScheduleLabel : $defaultScheduleName) ?>">
                        </div>
                    </div>
                </form>

                <!-- <div class="info-box">
                    <strong>Default step source:</strong>
                    <?= h($hscStepInfo['label']) ?>
                    <br>
                    <span class="inline-mono">Current default: <?= h((string)$hscDefaultStep) ?></span>
                </div> -->

                <div id="scheduleStatus" class="status"></div>

                <?php if ($truncatedWithoutRepeat): ?>
                    <div class="notice">
                        Repeat Cycle is off, so the schedule is limited to <?= h((string)$uniqueCycleLength) ?> unique month<?= $uniqueCycleLength !== 1 ? 's' : '' ?> for step size <?= h((string)$stepSize) ?> to avoid repeats and self-visits.
                    </div>
                <?php endif; ?>

                <?php if ($selectedScheduleLabel !== ''): ?>
                    <div class="top-space">
                        <a href="?base_month=<?= urlencode($baseMonth) ?>&month_count=<?= urlencode((string)$monthCount) ?>&repeat_cycle=<?= $repeatCycle ? '1' : '0' ?>&step_size=<?= urlencode((string)$stepSize) ?>" class="btn btn-secondary no-print">
                            Back To Current Rotation
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top:20px;">
                <h2>House Meeting Setup</h2>
                <p class="muted">Edit a meeting day/time and it auto-saves to the database.</p>

                <div class="house-setup-grid">
                    <div class="house-row no-print" style="margin-bottom:16px;">
                        <div>
                            <label class="small" for="new_house_name">New House</label>
                            <input type="text" id="new_house_name" placeholder="House name">
                        </div>
                        <div>
                            <label class="small" for="new_meeting_day">Meeting Day</label>
                            <select id="new_meeting_day">
                                <option value="">Select day</option>
                                <?php foreach ($daysOfWeek as $day): ?>
                                    <option value="<?= h($day) ?>"><?= h($day) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="small" for="new_meeting_time">Time</label>
                            <input type="time" id="new_meeting_time">
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" id="addHouseBtn">Add</button>
                        </div>
                    </div>

                    <div id="houseStatus" class="status"></div>

                    <?php foreach ($houses as $house): ?>
                        <div class="house-row" data-house-row>
                            <div>
                                <label class="small">House Name</label>
                                <input type="text" class="house-name" value="<?= h($house['house_name']) ?>" readonly>
                            </div>
                            <div>
                                <label class="small">Meeting Day</label>
                                <select class="meeting-day">
                                    <?php foreach ($daysOfWeek as $day): ?>
                                        <option value="<?= h($day) ?>" <?= $house['meeting_day'] === $day ? 'selected' : '' ?>>
                                            <?= h($day) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="small">Meeting Time</label>
                                <input type="time" class="meeting-time" value="<?= h($house['meeting_time']) ?>">
                            </div>
                            <div class="no-print">
                                <button type="button" class="btn btn-danger delete-house">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" style="margin-top:20px;">
                <h2>Saved Schedules</h2>

                <?php if ($savedSchedules): ?>
                    <form method="get" class="no-print" style="display:grid; gap:10px;">
                        <div>
                            <label class="small" for="history_schedule_label">History Dropdown</label>
                            <select id="history_schedule_label" name="schedule_label" onchange="this.form.submit()">
                                <option value="">-- View current generated schedule --</option>
                                <?php foreach ($savedSchedules as $saved): ?>
                                    <option value="<?= h($saved['schedule_label']) ?>" <?= $selectedScheduleLabel === $saved['schedule_label'] ? 'selected' : '' ?>>
                                        <?= h($saved['schedule_label']) ?> | Step <?= h((string)$saved['step_size']) ?> | <?= h((string)$saved['created_at']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="base_month" value="<?= h($baseMonth) ?>">
                        <input type="hidden" name="month_count" value="<?= h((string)$monthCount) ?>">
                        <input type="hidden" name="repeat_cycle" value="<?= $repeatCycle ? '1' : '0' ?>">
                        <input type="hidden" name="step_size" value="<?= h((string)$stepSize) ?>">
                    </form>

                    <div style="margin-top:12px;">
                        <?php if ($selectedScheduleLabel !== ''): ?>
                            <div class="status show success">
                                Loaded saved schedule: <strong><?= h($selectedScheduleLabel) ?></strong>
                            </div>
                        <?php else: ?>
                            <p class="muted">Select a saved schedule from the dropdown to reload it.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="muted">No saved schedules yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card rotation-card">
                <?php if (count($houses) < 2): ?>
                    <p class="muted">Add at least two houses to generate a rotation.</p>
                <?php elseif (!$displayRotation): ?>
                    <p class="muted">No valid rotation could be generated for the current settings.</p>
                <?php else: ?>
                    <?php foreach ($displayRotation as $month): ?>
                        <div class="rotation-month">
                            <h3>
                                <?= h($month['month_label']) ?>
                                <?php if (isset($month['shift_used'])): ?>
                                    <span class="shift-badge">Shift <?= h((string)$month['shift_used']) ?></span>
                                <?php endif; ?>
                            </h3>
                            <table>
                                <thead>
                                <tr>
                                    <th style="width:28%;">Visiting House</th>
                                    <th style="width:28%;">Host House</th>
                                    <th style="width:22%;">Meeting Day</th>
                                    <th style="width:22%;">Meeting Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($month['rows'] as $row): ?>
                                    <tr>
                                        <td><?= h($row['visiting_house']) ?></td>
                                        <td><?= h($row['host_house']) ?></td>
                                        <td><?= h($row['host_meeting_day']) ?></td>
                                        <td><?= h($row['host_meeting_time']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const houseStatus = document.getElementById('houseStatus');
    const scheduleStatus = document.getElementById('scheduleStatus');
    const stepSizeInput = document.getElementById('step_size');
    const scheduleNameInput = document.getElementById('schedule_label_input');
    const baseMonthInput = document.getElementById('base_month');
    const monthCountInput = document.getElementById('month_count');
    const repeatCycleInput = document.getElementById('repeat_cycle');

    let saveTimer = null;

    function showStatus(el, message, type = 'success') {
        if (!el) return;
        el.textContent = message;
        el.className = 'status show ' + type;
        clearTimeout(el._hideTimer);
        el._hideTimer = setTimeout(() => {
            el.className = 'status';
            el.textContent = '';
        }, 2500);
    }

    async function postForm(data) {
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(data).toString()
        });

        return response.json();
    }

    function monthLabelText(ym) {
        if (!ym || ym.indexOf('-') === -1) return 'Rotation';
        const parts = ym.split('-');
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const d = new Date(year, month, 1);
        return d.toLocaleString('en-US', { month: 'long', year: 'numeric' });
    }

    function refreshScheduleName() {
        if (!scheduleNameInput) return;
        const baseMonth = baseMonthInput ? baseMonthInput.value : '';
        const monthCount = monthCountInput ? monthCountInput.value : '1';
        const stepSize = stepSizeInput ? stepSizeInput.value : '1';
        const repeat = repeatCycleInput && repeatCycleInput.checked;
        const label = 'Rotation ' + monthLabelText(baseMonth) + ' - ' + monthCount + ' Month' + (String(monthCount) === '1' ? '' : 's') + ' - Step ' + stepSize + (repeat ? ' (Repeat Cycle)' : '');
        scheduleNameInput.value = label;
    }

    function bindAutoSave(row) {
        const houseName = row.querySelector('.house-name');
        const meetingDay = row.querySelector('.meeting-day');
        const meetingTime = row.querySelector('.meeting-time');
        const deleteBtn = row.querySelector('.delete-house');

        async function saveRow() {
            try {
                const result = await postForm({
                    action: 'autosave_house',
                    house_name: houseName.value,
                    meeting_day: meetingDay.value,
                    meeting_time: meetingTime.value
                });

                if (result.ok) {
                    showStatus(houseStatus, result.message, 'success');
                } else {
                    showStatus(houseStatus, result.message || 'Unable to save.', 'error');
                }
            } catch (e) {
                showStatus(houseStatus, 'Autosave failed.', 'error');
            }
        }

        function debouncedSave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveRow, 400);
        }

        meetingDay.addEventListener('change', debouncedSave);
        meetingTime.addEventListener('input', debouncedSave);

        if (deleteBtn) {
            deleteBtn.addEventListener('click', async function () {
                if (!confirm('Delete ' + houseName.value + '?')) return;

                try {
                    const result = await postForm({
                        action: 'delete_house',
                        house_name: houseName.value
                    });

                    if (result.ok) {
                        row.remove();
                        showStatus(houseStatus, result.message, 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showStatus(houseStatus, result.message || 'Delete failed.', 'error');
                    }
                } catch (e) {
                    showStatus(houseStatus, 'Delete failed.', 'error');
                }
            });
        }
    }

    document.querySelectorAll('[data-house-row]').forEach(bindAutoSave);

    const addHouseBtn = document.getElementById('addHouseBtn');
    if (addHouseBtn) {
        addHouseBtn.addEventListener('click', async function () {
            const houseName = document.getElementById('new_house_name').value.trim();
            const meetingDay = document.getElementById('new_meeting_day').value;
            const meetingTime = document.getElementById('new_meeting_time').value;

            try {
                const result = await postForm({
                    action: 'add_house',
                    house_name: houseName,
                    meeting_day: meetingDay,
                    meeting_time: meetingTime
                });

                if (result.ok) {
                    showStatus(houseStatus, result.message, 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showStatus(houseStatus, result.message || 'Unable to add house.', 'error');
                }
            } catch (e) {
                showStatus(houseStatus, 'Unable to add house.', 'error');
            }
        });
    }

    const saveScheduleBtn = document.getElementById('saveScheduleBtn');
    if (saveScheduleBtn) {
        saveScheduleBtn.addEventListener('click', async function () {
            const label = document.getElementById('schedule_label_input').value.trim();
            const baseMonth = document.getElementById('base_month').value;
            const monthCount = document.getElementById('month_count').value;
            const repeatCycle = document.getElementById('repeat_cycle').checked ? '1' : '0';
            const stepSize = document.getElementById('step_size').value;

            try {
                const result = await postForm({
                    action: 'save_schedule',
                    schedule_label: label,
                    base_month: baseMonth,
                    month_count: monthCount,
                    repeat_cycle: repeatCycle,
                    step_size: stepSize
                });

                if (result.ok) {
                    showStatus(scheduleStatus, result.message, 'success');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showStatus(scheduleStatus, result.message || 'Unable to save schedule.', 'error');
                }
            } catch (e) {
                showStatus(scheduleStatus, 'Unable to save schedule.', 'error');
            }
        });
    }

    [baseMonthInput, monthCountInput, repeatCycleInput, stepSizeInput].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', refreshScheduleName);
        el.addEventListener('input', refreshScheduleName);
    });
})();
</script>
</body>
</html>