<?php
declare(strict_types=1);

/**
 * Oxford House State Association Meeting Minutes
 * Single-file PHP form app
 * - MySQL auto-save
 * - History dropdown by meeting date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 * - Working yes/no radios and checkbox fields
 * - Editable State Officers and Chapters sections
 * - Keeps current officer labels and chapter labels as defaults
 */

require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';

$defaultOfficerLabels = [
    'state_chair_label' => 'State Chair',
    'vice_chair_label' => 'Vice-Chair',
    'secretary_label' => 'Secretary',
    'treasurer_label' => 'Treasurer',
    'housing_serv_east_label' => 'Housing Serv. East',
    'housing_serv_west_label' => 'Housing Serv. West',
    'fora_label' => 'FORA',
    'alumni_coordinator_label' => 'Alumni Coordinator',
    'world_council_mem_label' => 'World Council Mem.',
];

$defaultChapterLabels = [
    'chapter_1_label' => '1',
    'chapter_2_label' => '2',
    'chapter_3_label' => '3',
    'chapter_4_label' => '4',
    'chapter_5_label' => '5',
    'chapter_6_label' => '6',
    'chapter_7_label' => '7',
    'chapter_8_label' => '8',
    'chapter_9_label' => '9',
    'chapter_10_label' => '10',
];

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_money($value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $value = str_replace([',', '$', ' '], '', $value);
    return is_numeric($value) ? number_format((float)$value, 2, '.', '') : null;
}

function post_value(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function field(array $row, string $key, $default = '')
{
    return array_key_exists($key, $row) ? $row[$key] : $default;
}

function ensure_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS state_association_meeting_minutes (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            meeting_date DATE DEFAULT NULL,
            start_time VARCHAR(20) DEFAULT '',
            start_ampm VARCHAR(5) DEFAULT 'AM',

            state_chair_label VARCHAR(255) DEFAULT 'State Chair',
            vice_chair_label VARCHAR(255) DEFAULT 'Vice-Chair',
            secretary_label VARCHAR(255) DEFAULT 'Secretary',
            treasurer_label VARCHAR(255) DEFAULT 'Treasurer',
            housing_serv_east_label VARCHAR(255) DEFAULT 'Housing Serv. East',
            housing_serv_west_label VARCHAR(255) DEFAULT 'Housing Serv. West',
            fora_label VARCHAR(255) DEFAULT 'FORA',
            alumni_coordinator_label VARCHAR(255) DEFAULT 'Alumni Coordinator',
            world_council_mem_label VARCHAR(255) DEFAULT 'World Council Mem.',

            state_chair VARCHAR(255) DEFAULT '',
            vice_chair VARCHAR(255) DEFAULT '',
            secretary VARCHAR(255) DEFAULT '',
            treasurer VARCHAR(255) DEFAULT '',
            housing_serv_east VARCHAR(255) DEFAULT '',
            housing_serv_west VARCHAR(255) DEFAULT '',
            fora VARCHAR(255) DEFAULT '',
            alumni_coordinator VARCHAR(255) DEFAULT '',
            world_council_mem VARCHAR(255) DEFAULT '',

            chapter_1_label VARCHAR(255) DEFAULT '1',
            chapter_2_label VARCHAR(255) DEFAULT '2',
            chapter_3_label VARCHAR(255) DEFAULT '3',
            chapter_4_label VARCHAR(255) DEFAULT '4',
            chapter_5_label VARCHAR(255) DEFAULT '5',
            chapter_6_label VARCHAR(255) DEFAULT '6',
            chapter_7_label VARCHAR(255) DEFAULT '7',
            chapter_8_label VARCHAR(255) DEFAULT '8',
            chapter_9_label VARCHAR(255) DEFAULT '9',
            chapter_10_label VARCHAR(255) DEFAULT '10',

            chapter_1 VARCHAR(255) DEFAULT '',
            chapter_2 VARCHAR(255) DEFAULT '',
            chapter_3 VARCHAR(255) DEFAULT '',
            chapter_4 VARCHAR(255) DEFAULT '',
            chapter_5 VARCHAR(255) DEFAULT '',
            chapter_6 VARCHAR(255) DEFAULT '',
            chapter_7 VARCHAR(255) DEFAULT '',
            chapter_8 VARCHAR(255) DEFAULT '',
            chapter_9 VARCHAR(255) DEFAULT '',
            chapter_10 VARCHAR(255) DEFAULT '',

            absent_text TEXT DEFAULT NULL,
            guests_text TEXT DEFAULT NULL,

            house_traditions_read VARCHAR(3) DEFAULT '',
            chapter_principles_read VARCHAR(3) DEFAULT '',

            previous_minutes_text TEXT DEFAULT NULL,
            corrections_text TEXT DEFAULT NULL,
            accept_minutes_mmsp VARCHAR(255) DEFAULT '',

            checking_beginning_balance DECIMAL(12,2) DEFAULT NULL,
            checking_last_month_deposit DECIMAL(12,2) DEFAULT NULL,
            checking_last_month_spent DECIMAL(12,2) DEFAULT NULL,
            checking_current_balance DECIMAL(12,2) DEFAULT NULL,

            savings_beginning_balance DECIMAL(12,2) DEFAULT NULL,
            savings_deposits_interest DECIMAL(12,2) DEFAULT NULL,
            savings_withdrawals DECIMAL(12,2) DEFAULT NULL,
            savings_current_balance DECIMAL(12,2) DEFAULT NULL,

            total_money_collected DECIMAL(12,2) DEFAULT NULL,
            comments_text TEXT DEFAULT NULL,
            accept_treasurer_report_mmsp VARCHAR(255) DEFAULT '',

            alumni_report_text LONGTEXT DEFAULT NULL,
            accept_alumni_report_mmsp VARCHAR(255) DEFAULT '',
            housing_east_report_text LONGTEXT DEFAULT NULL,
            accept_housing_east_report_mmsp VARCHAR(255) DEFAULT '',
            housing_west_report_text LONGTEXT DEFAULT NULL,
            accept_housing_west_report_mmsp VARCHAR(255) DEFAULT '',
            reentry_report_text LONGTEXT DEFAULT NULL,
            accept_reentry_report_mmsp VARCHAR(255) DEFAULT '',
            world_council_report_text LONGTEXT DEFAULT NULL,
            accept_world_council_report_mmsp VARCHAR(255) DEFAULT '',

            chapter_report_1 LONGTEXT DEFAULT NULL,
            chapter_report_2 LONGTEXT DEFAULT NULL,
            chapter_report_3 LONGTEXT DEFAULT NULL,
            chapter_report_4 LONGTEXT DEFAULT NULL,
            chapter_report_5 LONGTEXT DEFAULT NULL,
            chapter_report_6 LONGTEXT DEFAULT NULL,
            chapter_report_7 LONGTEXT DEFAULT NULL,
            chapter_report_8 LONGTEXT DEFAULT NULL,
            chapter_report_9 LONGTEXT DEFAULT NULL,
            chapter_report_10 LONGTEXT DEFAULT NULL,

            fora_report_text LONGTEXT DEFAULT NULL,
            accept_fora_report_mmsp VARCHAR(255) DEFAULT '',
            old_business_text LONGTEXT DEFAULT NULL,
            accept_old_business_mmsp VARCHAR(255) DEFAULT '',
            new_business_text LONGTEXT DEFAULT NULL,
            accept_new_business_mmsp VARCHAR(255) DEFAULT '',
            adjourn_meeting_mmsp VARCHAR(255) DEFAULT '',
            adjourn_time VARCHAR(20) DEFAULT '',
            adjourn_ampm VARCHAR(5) DEFAULT 'PM',
            secretary_signature VARCHAR(255) DEFAULT '',

            reviewed_checkbox TINYINT(1) NOT NULL DEFAULT 0,
            approved_checkbox TINYINT(1) NOT NULL DEFAULT 0,

            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_meeting_date (meeting_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $alterStatements = [
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN state_chair_label VARCHAR(255) DEFAULT 'State Chair' AFTER start_ampm",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN vice_chair_label VARCHAR(255) DEFAULT 'Vice-Chair' AFTER state_chair_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN secretary_label VARCHAR(255) DEFAULT 'Secretary' AFTER vice_chair_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN treasurer_label VARCHAR(255) DEFAULT 'Treasurer' AFTER secretary_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN housing_serv_east_label VARCHAR(255) DEFAULT 'Housing Serv. East' AFTER treasurer_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN housing_serv_west_label VARCHAR(255) DEFAULT 'Housing Serv. West' AFTER housing_serv_east_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN fora_label VARCHAR(255) DEFAULT 'FORA' AFTER housing_serv_west_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN alumni_coordinator_label VARCHAR(255) DEFAULT 'Alumni Coordinator' AFTER fora_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN world_council_mem_label VARCHAR(255) DEFAULT 'World Council Mem.' AFTER alumni_coordinator_label",

        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_1_label VARCHAR(255) DEFAULT '1' AFTER world_council_mem",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_2_label VARCHAR(255) DEFAULT '2' AFTER chapter_1_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_3_label VARCHAR(255) DEFAULT '3' AFTER chapter_2_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_4_label VARCHAR(255) DEFAULT '4' AFTER chapter_3_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_5_label VARCHAR(255) DEFAULT '5' AFTER chapter_4_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_6_label VARCHAR(255) DEFAULT '6' AFTER chapter_5_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_7_label VARCHAR(255) DEFAULT '7' AFTER chapter_6_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_8_label VARCHAR(255) DEFAULT '8' AFTER chapter_7_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_9_label VARCHAR(255) DEFAULT '9' AFTER chapter_8_label",
        "ALTER TABLE state_association_meeting_minutes ADD COLUMN chapter_10_label VARCHAR(255) DEFAULT '10' AFTER chapter_9_label",
    ];

    foreach ($alterStatements as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Throwable $e) {
        }
    }
}

function fetch_history(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT id, meeting_date, updated_at
        FROM state_association_meeting_minutes
        ORDER BY meeting_date DESC, id DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetch_record(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM state_association_meeting_minutes
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function with_defaults(array $row, array $defaults): array
{
    foreach ($defaults as $key => $value) {
        if (!isset($row[$key]) || trim((string)$row[$key]) === '') {
            $row[$key] = $value;
        }
    }
    return $row;
}

$pdo = null;
$dbError = '';
$message = '';
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current = [];
$history = [];

$officerMap = [
    'state_chair' => 'state_chair_label',
    'vice_chair' => 'vice_chair_label',
    'secretary' => 'secretary_label',
    'treasurer' => 'treasurer_label',
    'housing_serv_east' => 'housing_serv_east_label',
    'housing_serv_west' => 'housing_serv_west_label',
    'fora' => 'fora_label',
    'alumni_coordinator' => 'alumni_coordinator_label',
    'world_council_mem' => 'world_council_mem_label',
];

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

    ensure_schema($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $recordId = (int)($_POST['record_id'] ?? 0);
        $action = (string)($_POST['action'] ?? 'save');

        if ($action === 'load' && !empty($_POST['history_id'])) {
            $loadId = (int)$_POST['history_id'];
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?id=' . $loadId);
            exit;
        }

        $fields = [
            'meeting_date' => trim((string)post_value('meeting_date')) ?: null,
            'start_time' => trim((string)post_value('start_time')),
            'start_ampm' => trim((string)post_value('start_ampm', 'AM')),

            'state_chair_label' => trim((string)post_value('state_chair_label', 'State Chair')),
            'vice_chair_label' => trim((string)post_value('vice_chair_label', 'Vice-Chair')),
            'secretary_label' => trim((string)post_value('secretary_label', 'Secretary')),
            'treasurer_label' => trim((string)post_value('treasurer_label', 'Treasurer')),
            'housing_serv_east_label' => trim((string)post_value('housing_serv_east_label', 'Housing Serv. East')),
            'housing_serv_west_label' => trim((string)post_value('housing_serv_west_label', 'Housing Serv. West')),
            'fora_label' => trim((string)post_value('fora_label', 'FORA')),
            'alumni_coordinator_label' => trim((string)post_value('alumni_coordinator_label', 'Alumni Coordinator')),
            'world_council_mem_label' => trim((string)post_value('world_council_mem_label', 'World Council Mem.')),

            'state_chair' => trim((string)post_value('state_chair')),
            'vice_chair' => trim((string)post_value('vice_chair')),
            'secretary' => trim((string)post_value('secretary')),
            'treasurer' => trim((string)post_value('treasurer')),
            'housing_serv_east' => trim((string)post_value('housing_serv_east')),
            'housing_serv_west' => trim((string)post_value('housing_serv_west')),
            'fora' => trim((string)post_value('fora')),
            'alumni_coordinator' => trim((string)post_value('alumni_coordinator')),
            'world_council_mem' => trim((string)post_value('world_council_mem')),

            'chapter_1_label' => trim((string)post_value('chapter_1_label', '1')),
            'chapter_2_label' => trim((string)post_value('chapter_2_label', '2')),
            'chapter_3_label' => trim((string)post_value('chapter_3_label', '3')),
            'chapter_4_label' => trim((string)post_value('chapter_4_label', '4')),
            'chapter_5_label' => trim((string)post_value('chapter_5_label', '5')),
            'chapter_6_label' => trim((string)post_value('chapter_6_label', '6')),
            'chapter_7_label' => trim((string)post_value('chapter_7_label', '7')),
            'chapter_8_label' => trim((string)post_value('chapter_8_label', '8')),
            'chapter_9_label' => trim((string)post_value('chapter_9_label', '9')),
            'chapter_10_label' => trim((string)post_value('chapter_10_label', '10')),

            'chapter_1' => trim((string)post_value('chapter_1')),
            'chapter_2' => trim((string)post_value('chapter_2')),
            'chapter_3' => trim((string)post_value('chapter_3')),
            'chapter_4' => trim((string)post_value('chapter_4')),
            'chapter_5' => trim((string)post_value('chapter_5')),
            'chapter_6' => trim((string)post_value('chapter_6')),
            'chapter_7' => trim((string)post_value('chapter_7')),
            'chapter_8' => trim((string)post_value('chapter_8')),
            'chapter_9' => trim((string)post_value('chapter_9')),
            'chapter_10' => trim((string)post_value('chapter_10')),

            'absent_text' => trim((string)post_value('absent_text')),
            'guests_text' => trim((string)post_value('guests_text')),

            'house_traditions_read' => trim((string)post_value('house_traditions_read')),
            'chapter_principles_read' => trim((string)post_value('chapter_principles_read')),

            'previous_minutes_text' => trim((string)post_value('previous_minutes_text')),
            'corrections_text' => trim((string)post_value('corrections_text')),
            'accept_minutes_mmsp' => trim((string)post_value('accept_minutes_mmsp')),

            'checking_beginning_balance' => normalize_money(post_value('checking_beginning_balance')),
            'checking_last_month_deposit' => normalize_money(post_value('checking_last_month_deposit')),
            'checking_last_month_spent' => normalize_money(post_value('checking_last_month_spent')),
            'checking_current_balance' => normalize_money(post_value('checking_current_balance')),

            'savings_beginning_balance' => normalize_money(post_value('savings_beginning_balance')),
            'savings_deposits_interest' => normalize_money(post_value('savings_deposits_interest')),
            'savings_withdrawals' => normalize_money(post_value('savings_withdrawals')),
            'savings_current_balance' => normalize_money(post_value('savings_current_balance')),

            'total_money_collected' => normalize_money(post_value('total_money_collected')),
            'comments_text' => trim((string)post_value('comments_text')),
            'accept_treasurer_report_mmsp' => trim((string)post_value('accept_treasurer_report_mmsp')),

            'alumni_report_text' => trim((string)post_value('alumni_report_text')),
            'accept_alumni_report_mmsp' => trim((string)post_value('accept_alumni_report_mmsp')),
            'housing_east_report_text' => trim((string)post_value('housing_east_report_text')),
            'accept_housing_east_report_mmsp' => trim((string)post_value('accept_housing_east_report_mmsp')),
            'housing_west_report_text' => trim((string)post_value('housing_west_report_text')),
            'accept_housing_west_report_mmsp' => trim((string)post_value('accept_housing_west_report_mmsp')),
            'reentry_report_text' => trim((string)post_value('reentry_report_text')),
            'accept_reentry_report_mmsp' => trim((string)post_value('accept_reentry_report_mmsp')),
            'world_council_report_text' => trim((string)post_value('world_council_report_text')),
            'accept_world_council_report_mmsp' => trim((string)post_value('accept_world_council_report_mmsp')),

            'chapter_report_1' => trim((string)post_value('chapter_report_1')),
            'chapter_report_2' => trim((string)post_value('chapter_report_2')),
            'chapter_report_3' => trim((string)post_value('chapter_report_3')),
            'chapter_report_4' => trim((string)post_value('chapter_report_4')),
            'chapter_report_5' => trim((string)post_value('chapter_report_5')),
            'chapter_report_6' => trim((string)post_value('chapter_report_6')),
            'chapter_report_7' => trim((string)post_value('chapter_report_7')),
            'chapter_report_8' => trim((string)post_value('chapter_report_8')),
            'chapter_report_9' => trim((string)post_value('chapter_report_9')),
            'chapter_report_10' => trim((string)post_value('chapter_report_10')),

            'fora_report_text' => trim((string)post_value('fora_report_text')),
            'accept_fora_report_mmsp' => trim((string)post_value('accept_fora_report_mmsp')),
            'old_business_text' => trim((string)post_value('old_business_text')),
            'accept_old_business_mmsp' => trim((string)post_value('accept_old_business_mmsp')),
            'new_business_text' => trim((string)post_value('new_business_text')),
            'accept_new_business_mmsp' => trim((string)post_value('accept_new_business_mmsp')),

            'adjourn_meeting_mmsp' => trim((string)post_value('adjourn_meeting_mmsp')),
            'adjourn_time' => trim((string)post_value('adjourn_time')),
            'adjourn_ampm' => trim((string)post_value('adjourn_ampm', 'PM')),
            'secretary_signature' => trim((string)post_value('secretary_signature')),

            'reviewed_checkbox' => isset($_POST['reviewed_checkbox']) ? 1 : 0,
            'approved_checkbox' => isset($_POST['approved_checkbox']) ? 1 : 0,
        ];

        foreach ($fields as $key => $value) {
            if (str_ends_with($key, '_label') && trim((string)$value) === '') {
                if (isset($defaultOfficerLabels[$key])) {
                    $fields[$key] = $defaultOfficerLabels[$key];
                }
                if (isset($defaultChapterLabels[$key])) {
                    $fields[$key] = $defaultChapterLabels[$key];
                }
            }
        }

        if ($recordId > 0) {
            $setSql = [];
            foreach (array_keys($fields) as $fieldName) {
                $setSql[] = "{$fieldName} = :{$fieldName}";
            }

            $sql = "UPDATE state_association_meeting_minutes SET " . implode(', ', $setSql) . " WHERE id = :id";
            $fields['id'] = $recordId;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($fields);
            $message = 'Record updated successfully.';
        } else {
            $columns = implode(', ', array_keys($fields));
            $placeholders = ':' . implode(', :', array_keys($fields));
            $sql = "INSERT INTO state_association_meeting_minutes ({$columns}) VALUES ({$placeholders})";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($fields);
            $recordId = (int)$pdo->lastInsertId();
            $message = 'Record saved successfully.';
        }

        if (isset($_POST['autosave']) && $_POST['autosave'] === '1') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => true,
                'message' => $message,
                'id' => $recordId,
            ]);
            exit;
        }

        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?id=' . $recordId . '&saved=1');
        exit;
    }

    if (isset($_GET['saved'])) {
        $message = 'Record saved successfully.';
    }

    $history = fetch_history($pdo);

    if ($recordId > 0) {
        $current = fetch_record($pdo, $recordId) ?? [];
    }

    $current = with_defaults($current, $defaultOfficerLabels);
    $current = with_defaults($current, $defaultChapterLabels);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>state-association-meeting-minutes-2</title>
    <style>
        :root {
            --ink: #111;
            --line: #111;
            --paper: #f2f2f2;
            --white: #fff;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            padding: 18px;
            background: #d5d5d5;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        form {
            margin: 0;
        }

        .toolbar {
            max-width: 980px;
            margin: 0 auto 14px auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .btn,
        select,
        input[type="date"] {
            border: 1px solid #222;
            background: #fff;
            padding: 8px 10px;
            font-size: 13px;
            font-family: inherit;
        }

        .btn {
            cursor: pointer;
            font-weight: 700;
        }

        .status {
            max-width: 980px;
            margin: 0 auto 12px auto;
            padding: 8px 12px;
            border: 1px solid #111;
            background: #eef8ee;
            font-size: 13px;
        }

        .status.error {
            background: #fff0f0;
            color: #7a0000;
        }

        .paper {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            background: var(--paper);
        }

        .page {
            background: var(--paper);
            border: 2px solid var(--line);
            margin: 0 auto 16px auto;
            padding: 12px;
            page-break-after: always;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: transparent;
        }

        .main-title {
            font-weight: 900;
            font-size: 20px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .top-line {
            display: grid;
            grid-template-columns: 100px 1fr 120px 1fr;
            gap: 6px 8px;
            align-items: center;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .line-input,
        textarea {
            width: 100%;
            border: none;
            border-bottom: 2px solid var(--line);
            background: transparent;
            min-height: 28px;
            font-size: 15px;
            padding: 2px 4px;
            font-family: inherit;
        }

        textarea {
            border: 1px solid var(--line);
            min-height: 110px;
            resize: vertical;
            background: rgba(255,255,255,0.35);
            padding: 6px 8px;
        }

        .small-textarea { min-height: 72px; }
        .large-textarea { min-height: 220px; }

        .section {
            border: 2px solid var(--line);
            margin-bottom: 12px;
            background: transparent;
        }

        .section-title {
            font-weight: 900;
            text-transform: uppercase;
            font-size: 16px;
            padding: 3px 6px;
            border-bottom: 2px solid var(--line);
        }

        .section-inner {
            padding: 8px;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .grid-table th,
        .grid-table td {
            border: 2px solid var(--line);
            padding: 0;
            vertical-align: middle;
        }

        .grid-table th {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            background: rgba(255,255,255,0.2);
            padding: 3px 6px;
            text-align: center;
        }

        .editable-label-cell {
            background: rgba(255,255,255,0.12);
            width: 48%;
        }

        .editable-value-cell {
            background: rgba(255,255,255,0.18);
            width: 52%;
        }

        .editable-label-input,
        .editable-value-input,
        .chapter-edit-input,
        .chapter-number-input {
            width: 100%;
            height: 100%;
            min-height: 30px;
            border: none;
            outline: none;
            background: transparent;
            padding: 4px 6px;
            font-size: 14px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .editable-label-input {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
        }

        .editable-label-input:focus,
        .editable-value-input:focus,
        .chapter-edit-input:focus,
        .chapter-number-input:focus {
            background: rgba(255,255,255,0.35);
        }

        .attendance-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .lined-row {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 8px;
            align-items: center;
            margin: 6px 0;
            font-weight: 900;
            text-transform: uppercase;
        }

        .inline-block {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .radios {
            display: inline-flex;
            gap: 12px;
            align-items: center;
            font-weight: 700;
        }

        .radios label,
        .checks label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        input[type="radio"],
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #111;
            cursor: pointer;
        }

        .accept-line {
            display: grid;
            grid-template-columns: auto 180px;
            gap: 10px;
            align-items: center;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .finance-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .summary-line {
            display: grid;
            grid-template-columns: auto 120px auto 1fr;
            gap: 8px;
            align-items: start;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .summary-line .comments-label {
            padding-top: 6px;
        }

        .report-block {
            margin-bottom: 14px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .chapter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .chapter-item {
            border: 2px solid var(--line);
            padding: 6px;
        }

        .chapter-item h4 {
            margin: 0 0 6px 0;
            font-size: 18px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: center;
            margin-top: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .checks {
            display: flex;
            gap: 16px;
            margin-top: 8px;
            font-weight: 700;
            flex-wrap: wrap;
        }

        .muted {
            font-size: 12px;
            font-weight: 700;
        }

        .autosave-note {
            font-size: 12px;
            color: #333;
        }

        .chapter-rollcall-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .chapter-rollcall-table th,
        .chapter-rollcall-table td {
            border: 1px solid var(--line);
            padding: 0;
            vertical-align: middle;
        }

        .chapter-rollcall-table th {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            background: rgba(255,255,255,0.15);
            padding: 4px 6px;
            text-align: center;
        }

        .chapter-number-cell {
            width: 52px;
            background: rgba(255,255,255,0.08);
        }

        .chapter-edit-cell {
            height: 30px;
            background: rgba(255,255,255,0.18);
        }

        .chapter-number-input {
            text-align: left;
            font-weight: 900;
            font-size: 24px;
            line-height: 1;
            padding: 0 6px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar,
            .status {
                display: none !important;
            }

            .paper {
                max-width: none;
            }

            .page {
                margin: 0;
                border: 2px solid #111;
            }
        }

        @media (max-width: 760px) {
            .attendance-wrap,
            .two-col,
            .finance-grid {
                grid-template-columns: 1fr;
            }

            .top-line,
            .summary-line,
            .accept-line,
            .signature-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php if ($dbError !== ''): ?>
    <div class="status error">Database connection failed: <?= h($dbError) ?></div>
<?php else: ?>
    <?php if ($message !== ''): ?>
        <div class="status"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div class="toolbar-left">
            <button type="submit" form="minutesForm" class="btn">Save</button>
            <button type="button" class="btn" onclick="window.print()">Print</button>
            <span class="autosave-note" id="autosaveStatus">Autosave ready.</span>
        </div>

        <div class="toolbar-right">
            <form method="post" style="display:flex; gap:10px; align-items:center; margin:0;">
                <input type="hidden" name="action" value="load">
                <select name="history_id" required>
                    <option value="">History by date</option>
                    <?php foreach ($history as $item): ?>
                        <option value="<?= (int)$item['id'] ?>" <?= ((int)$recordId === (int)$item['id']) ? 'selected' : '' ?>>
                            <?= h(($item['meeting_date'] ?: 'No Date') . ' - Record #' . $item['id']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">Load</button>
            </form>
        </div>
    </div>

    <div class="paper">
        <form method="post" id="minutesForm" autocomplete="off">
            <input type="hidden" name="record_id" id="record_id" value="<?= (int)$recordId ?>">

            <div class="page">
                <div class="header-title">
                    <img src="<?= h($logoPath) ?>" alt="Oxford House Logo" class="logo" onerror="this.style.display='none'">
                    <div class="main-title">Oxford House State Association Meeting Minutes</div>
                </div>

                <div class="top-line">
                    <div>DATE:</div>
                    <input class="line-input autosave" type="date" name="meeting_date" value="<?= h((string)field($current, 'meeting_date')) ?>">

                    <div>START TIME:</div>
                    <div class="inline-block">
                        <input class="line-input autosave" style="max-width:120px;" type="text" name="start_time" value="<?= h((string)field($current, 'start_time')) ?>">
                        <select name="start_ampm" class="autosave">
                            <option value="AM" <?= field($current, 'start_ampm', 'AM') === 'AM' ? 'selected' : '' ?>>AM</option>
                            <option value="PM" <?= field($current, 'start_ampm', 'AM') === 'PM' ? 'selected' : '' ?>>PM</option>
                        </select>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Roll Call: <span style="font-weight:700; text-transform:none;">(By Secretary)</span></div>
                    <div class="section-inner">
                        <div class="attendance-wrap">
                            <table class="grid-table">
                                <tr>
                                    <th colspan="2">State Officers</th>
                                </tr>
                                <?php foreach ($officerMap as $valueField => $labelField): ?>
                                    <tr>
                                        <td class="editable-label-cell">
                                            <input
                                                class="editable-label-input autosave"
                                                type="text"
                                                name="<?= h($labelField) ?>"
                                                value="<?= h((string)field($current, $labelField, '')) ?>"
                                            >
                                        </td>
                                        <td class="editable-value-cell">
                                            <input
                                                class="editable-value-input autosave"
                                                type="text"
                                                name="<?= h($valueField) ?>"
                                                value="<?= h((string)field($current, $valueField)) ?>"
                                            >
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>

                            <table class="chapter-rollcall-table">
                                <tr>
                                    <th colspan="2">Chapters</th>
                                </tr>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <?php $labelKey = 'chapter_' . $i . '_label'; ?>
                                    <?php $valueKey = 'chapter_' . $i; ?>
                                    <tr>
                                        <td class="chapter-number-cell">
                                            <input
                                                class="chapter-number-input autosave"
                                                type="text"
                                                name="<?= h($labelKey) ?>"
                                                value="<?= h((string)field($current, $labelKey, (string)$i)) ?>"
                                            >
                                        </td>
                                        <td class="chapter-edit-cell">
                                            <input
                                                class="chapter-edit-input autosave"
                                                type="text"
                                                name="<?= h($valueKey) ?>"
                                                value="<?= h((string)field($current, $valueKey)) ?>"
                                            >
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </table>
                        </div>

                        <div class="lined-row">
                            <div>Absent:</div>
                            <input class="line-input autosave" type="text" name="absent_text" value="<?= h((string)field($current, 'absent_text')) ?>">
                        </div>

                        <div class="lined-row">
                            <div>Guests:</div>
                            <input class="line-input autosave" type="text" name="guests_text" value="<?= h((string)field($current, 'guests_text')) ?>">
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-inner">
                        <div class="two-col" style="margin-bottom:8px;">
                            <div class="inline-block" style="font-weight:900; text-transform:uppercase;">
                                <span>House Traditions Read:</span>
                                <span class="radios">
                                    <label><input class="autosave" type="radio" name="house_traditions_read" value="Y" <?= field($current, 'house_traditions_read') === 'Y' ? 'checked' : '' ?>> Y</label>
                                    <label><input class="autosave" type="radio" name="house_traditions_read" value="N" <?= field($current, 'house_traditions_read') === 'N' ? 'checked' : '' ?>> N</label>
                                </span>
                            </div>

                            <div class="inline-block" style="font-weight:900; text-transform:uppercase;">
                                <span>Chapter Principles Read:</span>
                                <span class="radios">
                                    <label><input class="autosave" type="radio" name="chapter_principles_read" value="Y" <?= field($current, 'chapter_principles_read') === 'Y' ? 'checked' : '' ?>> Y</label>
                                    <label><input class="autosave" type="radio" name="chapter_principles_read" value="N" <?= field($current, 'chapter_principles_read') === 'N' ? 'checked' : '' ?>> N</label>
                                </span>
                            </div>
                        </div>

                        <div class="report-title">Reading of Previous Minutes: <span style="font-size:16px; font-weight:700; text-transform:none;">(By Secretary)</span></div>
                        <textarea class="small-textarea autosave" name="previous_minutes_text"><?= h((string)field($current, 'previous_minutes_text')) ?></textarea>

                        <div class="report-title" style="margin-top:10px;">Corrections:</div>
                        <textarea class="small-textarea autosave" name="corrections_text"><?= h((string)field($current, 'corrections_text')) ?></textarea>

                        <div class="accept-line">
                            <div>Accept Minutes as Read or Corrected:</div>
                            <input class="line-input autosave" type="text" name="accept_minutes_mmsp" value="<?= h((string)field($current, 'accept_minutes_mmsp')) ?>" placeholder="MM/S/P">
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Treasurer's Report</div>
                    <div class="section-inner">
                        <div class="finance-grid">
                            <table class="grid-table">
                                <tr><th colspan="3">Checking Account</th></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Beginning Balance</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave" type="text" name="checking_beginning_balance" value="<?= h((string)field($current, 'checking_beginning_balance')) ?>"></td></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Last Month Deposit</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave" type="text" name="checking_last_month_deposit" value="<?= h((string)field($current, 'checking_last_month_deposit')) ?>"></td></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Last Month Spent</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave" type="text" name="checking_last_month_spent" value="<?= h((string)field($current, 'checking_last_month_spent')) ?>"></td></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Current Balance</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave calc-current" type="text" name="checking_current_balance" value="<?= h((string)field($current, 'checking_current_balance')) ?>"></td></tr>
                            </table>

                            <table class="grid-table">
                                <tr><th colspan="3">Savings Account</th></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Beginning Balance</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave" type="text" name="savings_beginning_balance" value="<?= h((string)field($current, 'savings_beginning_balance')) ?>"></td></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Deposits/ Interest</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave" type="text" name="savings_deposits_interest" value="<?= h((string)field($current, 'savings_deposits_interest')) ?>"></td></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Withdrawals</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave" type="text" name="savings_withdrawals" value="<?= h((string)field($current, 'savings_withdrawals')) ?>"></td></tr>
                                <tr><td style="padding:4px 6px; font-weight:900; text-transform:uppercase; font-size:13px;">Current Balance</td><td style="padding:4px 6px; font-weight:900;">$</td><td><input class="editable-value-input money autosave calc-current" type="text" name="savings_current_balance" value="<?= h((string)field($current, 'savings_current_balance')) ?>"></td></tr>
                            </table>
                        </div>

                        <div class="summary-line">
                            <div>Total Money Collected to Deposit:</div>
                            <input class="line-input money autosave" type="text" name="total_money_collected" value="<?= h((string)field($current, 'total_money_collected')) ?>">
                            <div class="comments-label">Comments:</div>
                            <textarea class="autosave" name="comments_text" style="min-height:180px;"><?= h((string)field($current, 'comments_text')) ?></textarea>
                        </div>

                        <div class="muted">(Expenditures with check number and "MM/S/P")</div>

                        <div class="accept-line">
                            <div>Accept Treasurer's Report</div>
                            <input class="line-input autosave" type="text" name="accept_treasurer_report_mmsp" value="<?= h((string)field($current, 'accept_treasurer_report_mmsp')) ?>" placeholder="MM/S/P">
                        </div>
                    </div>
                </div>
            </div>

            <div class="page">
                <?php
                $reportsPage2 = [
                    ['ALUMNI REPORT', 'alumni_report_text', 'accept_alumni_report_mmsp'],
                    ['HOUSING SERVICES REPORT - EAST', 'housing_east_report_text', 'accept_housing_east_report_mmsp'],
                    ['HOUSING SERVICES REPORT - WEST', 'housing_west_report_text', 'accept_housing_west_report_mmsp'],
                    ['RE-ENTRY REPORT', 'reentry_report_text', 'accept_reentry_report_mmsp'],
                    ['WORLD COUNCIL REPORT', 'world_council_report_text', 'accept_world_council_report_mmsp'],
                ];
                foreach ($reportsPage2 as [$title, $fieldName, $acceptField]): ?>
                    <div class="report-block">
                        <div class="report-title"><?= h($title) ?></div>
                        <textarea class="large-textarea autosave" name="<?= h($fieldName) ?>"><?= h((string)field($current, $fieldName)) ?></textarea>
                        <div class="accept-line">
                            <div>Accept Report</div>
                            <input class="line-input autosave" type="text" name="<?= h($acceptField) ?>" value="<?= h((string)field($current, $acceptField)) ?>" placeholder="MM/S/P">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="page">
                <div class="report-title">Chapter Reports</div>
                <div class="chapter-grid">
                    <?php for ($i = 1; $i <= 10; $i++): $key = 'chapter_report_' . $i; ?>
                        <div class="chapter-item">
                            <h4><?= h((string)field($current, 'chapter_' . $i . '_label', (string)$i)) ?></h4>
                            <textarea class="small-textarea autosave" name="<?= h($key) ?>"><?= h((string)field($current, $key)) ?></textarea>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="page">
                <?php
                $reportsPage4 = [
                    ['FORA REPORT', 'fora_report_text', 'accept_fora_report_mmsp'],
                    ['OLD BUSINESS', 'old_business_text', 'accept_old_business_mmsp'],
                    ['NEW BUSINESS', 'new_business_text', 'accept_new_business_mmsp'],
                ];
                foreach ($reportsPage4 as [$title, $fieldName, $acceptField]): ?>
                    <div class="report-block">
                        <div class="report-title"><?= h($title) ?></div>
                        <textarea class="large-textarea autosave" name="<?= h($fieldName) ?>"><?= h((string)field($current, $fieldName)) ?></textarea>
                        <div class="accept-line">
                            <div>Accept Report</div>
                            <input class="line-input autosave" type="text" name="<?= h($acceptField) ?>" value="<?= h((string)field($current, $acceptField)) ?>" placeholder="MM/S/P">
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="section">
                    <div class="section-inner">
                        <div class="accept-line">
                            <div>Adjourn Meeting</div>
                            <input class="line-input autosave" type="text" name="adjourn_meeting_mmsp" value="<?= h((string)field($current, 'adjourn_meeting_mmsp')) ?>" placeholder="MM/S/P">
                        </div>

                        <div class="top-line" style="margin-top:10px;">
                            <div>ADJOURN TIME:</div>
                            <div class="inline-block">
                                <input class="line-input autosave" style="max-width:120px;" type="text" name="adjourn_time" value="<?= h((string)field($current, 'adjourn_time')) ?>">
                                <select name="adjourn_ampm" class="autosave">
                                    <option value="AM" <?= field($current, 'adjourn_ampm', 'PM') === 'AM' ? 'selected' : '' ?>>AM</option>
                                    <option value="PM" <?= field($current, 'adjourn_ampm', 'PM') === 'PM' ? 'selected' : '' ?>>PM</option>
                                </select>
                            </div>
                            <div></div>
                            <div></div>
                        </div>

                        <div class="signature-row">
                            <div>Secretary Signature:</div>
                            <input class="line-input autosave" type="text" name="secretary_signature" value="<?= h((string)field($current, 'secretary_signature')) ?>">
                        </div>

                        <div class="checks">
                            <label><input class="autosave" type="checkbox" name="reviewed_checkbox" value="1" <?= (int)field($current, 'reviewed_checkbox', 0) === 1 ? 'checked' : '' ?>> Reviewed</label>
                            <label><input class="autosave" type="checkbox" name="approved_checkbox" value="1" <?= (int)field($current, 'approved_checkbox', 0) === 1 ? 'checked' : '' ?>> Approved</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const form = document.getElementById('minutesForm');
            const autosaveStatus = document.getElementById('autosaveStatus');
            const recordIdField = document.getElementById('record_id');
            let autosaveTimer = null;
            let lastPayload = '';

            function parseMoney(value) {
                const cleaned = String(value || '').replace(/[$,\s]/g, '');
                const num = parseFloat(cleaned);
                return Number.isFinite(num) ? num : 0;
            }

            function formatMoney(value) {
                return Number.isFinite(value) ? value.toFixed(2) : '';
            }

            function calcBalances() {
                const cb = form.elements['checking_beginning_balance'];
                const cd = form.elements['checking_last_month_deposit'];
                const cs = form.elements['checking_last_month_spent'];
                const cc = form.elements['checking_current_balance'];

                const sb = form.elements['savings_beginning_balance'];
                const sd = form.elements['savings_deposits_interest'];
                const sw = form.elements['savings_withdrawals'];
                const sc = form.elements['savings_current_balance'];

                if (cb && cd && cs && cc) {
                    cc.value = formatMoney(parseMoney(cb.value) + parseMoney(cd.value) - parseMoney(cs.value));
                }

                if (sb && sd && sw && sc) {
                    sc.value = formatMoney(parseMoney(sb.value) + parseMoney(sd.value) - parseMoney(sw.value));
                }
            }

            function setStatus(text) {
                autosaveStatus.textContent = text;
            }

            function getFormData() {
                calcBalances();
                return new FormData(form);
            }

            async function autosave() {
                const data = getFormData();
                data.append('autosave', '1');
                data.append('action', 'save');

                const serialized = new URLSearchParams(data).toString();

                if (serialized === lastPayload) {
                    setStatus('No changes to save.');
                    return;
                }

                setStatus('Saving...');

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: data
                    });

                    const result = await response.json();

                    if (result && result.ok) {
                        lastPayload = serialized;

                        if (result.id) {
                            recordIdField.value = result.id;
                            const url = new URL(window.location.href);
                            url.searchParams.set('id', result.id);
                            window.history.replaceState({}, '', url.toString());
                        }

                        setStatus('Autosaved at ' + new Date().toLocaleTimeString());
                    } else {
                        setStatus('Autosave failed.');
                    }
                } catch (error) {
                    setStatus('Autosave failed.');
                }
            }

            function queueAutosave() {
                clearTimeout(autosaveTimer);
                autosaveTimer = setTimeout(autosave, 700);
            }

            form.querySelectorAll('.autosave').forEach((el) => {
                el.addEventListener('input', queueAutosave);
                el.addEventListener('change', queueAutosave);
            });

            form.addEventListener('submit', () => {
                calcBalances();
                setStatus('Saving...');
            });

            form.querySelectorAll('.money').forEach((input) => {
                input.addEventListener('blur', () => {
                    const raw = parseMoney(input.value);
                    if (String(input.value).trim() !== '') {
                        input.value = formatMoney(raw);
                    }
                    calcBalances();
                });
            });

            calcBalances();
            lastPayload = new URLSearchParams(new FormData(form)).toString();
        })();
    </script>
<?php endif; ?>
</body>
</html>