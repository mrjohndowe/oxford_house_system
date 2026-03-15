<?php
declare(strict_types=1);

/**
 * House Summary Report
 * - Single-file PHP/MySQL app
 * - Auto-save via AJAX
 * - History dropdown by house name + report date
 * - Reload/edit prior records
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function field(array $data, string $key, string $default = ''): string
{
    return h($data[$key] ?? $default);
}

function checkedValue(array $data, string $key, string $value): string
{
    return (($data[$key] ?? '') === $value) ? 'checked' : '';
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

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
} catch (Throwable $e) {
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec("
    CREATE TABLE IF NOT EXISTS house_summary_reports (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        report_date DATE NOT NULL,
        house_name VARCHAR(255) NOT NULL DEFAULT '',
        chapter VARCHAR(255) NOT NULL DEFAULT '',
        meeting_day_time VARCHAR(255) NOT NULL DEFAULT '',
        house_president VARCHAR(255) NOT NULL DEFAULT '',
        capacity VARCHAR(50) NOT NULL DEFAULT '',
        occupied_beds VARCHAR(50) NOT NULL DEFAULT '',
        vacant_beds VARCHAR(50) NOT NULL DEFAULT '',
        applications_received VARCHAR(50) NOT NULL DEFAULT '',
        new_members VARCHAR(255) NOT NULL DEFAULT '',
        voluntary_departures VARCHAR(50) NOT NULL DEFAULT '',
        relapse_departurers VARCHAR(50) NOT NULL DEFAULT '',
        other_departures VARCHAR(50) NOT NULL DEFAULT '',
        members_attending_3_meets VARCHAR(50) NOT NULL DEFAULT '',
        members_on_contract VARCHAR(50) NOT NULL DEFAULT '',
        owing_line_1 VARCHAR(255) NOT NULL DEFAULT '',
        owing_line_2 VARCHAR(255) NOT NULL DEFAULT '',
        owing_line_3 VARCHAR(255) NOT NULL DEFAULT '',
        amount_checking VARCHAR(50) NOT NULL DEFAULT '',
        amount_saving VARCHAR(50) NOT NULL DEFAULT '',
        number_of_members VARCHAR(50) NOT NULL DEFAULT '',
        number_of_members_behind VARCHAR(50) NOT NULL DEFAULT '',
        total_behind VARCHAR(50) NOT NULL DEFAULT '',
        bills_caught_up VARCHAR(10) NOT NULL DEFAULT '',
        bills_current VARCHAR(10) NOT NULL DEFAULT '',
        members_behind VARCHAR(50) NOT NULL DEFAULT '',
        answering_machine_checked VARCHAR(50) NOT NULL DEFAULT '',
        ohi_donation VARCHAR(10) NOT NULL DEFAULT '',
        email_checked_daily VARCHAR(10) NOT NULL DEFAULT '',
        house_audit_done VARCHAR(10) NOT NULL DEFAULT '',
        bank_statement_attached VARCHAR(10) NOT NULL DEFAULT '',
        presentation_done_date VARCHAR(255) NOT NULL DEFAULT '',
        members_with_jobs VARCHAR(50) NOT NULL DEFAULT '',
        members_from_jail_prison_30_days VARCHAR(50) NOT NULL DEFAULT '',
        reentry_members_arrested VARCHAR(50) NOT NULL DEFAULT '',
        members_abused_opioids VARCHAR(50) NOT NULL DEFAULT '',
        members_on_mat VARCHAR(50) NOT NULL DEFAULT '',
        concerns_1 VARCHAR(255) NOT NULL DEFAULT '',
        concerns_2 VARCHAR(255) NOT NULL DEFAULT '',
        concerns_3 VARCHAR(255) NOT NULL DEFAULT '',
        concerns_4 VARCHAR(255) NOT NULL DEFAULT '',
        concerns_5 VARCHAR(255) NOT NULL DEFAULT '',
        concerns_6 VARCHAR(255) NOT NULL DEFAULT '',
        chapter_requests_1 VARCHAR(255) NOT NULL DEFAULT '',
        chapter_requests_2 VARCHAR(255) NOT NULL DEFAULT '',
        chapter_requests_3 VARCHAR(255) NOT NULL DEFAULT '',
        chapter_requests_4 VARCHAR(255) NOT NULL DEFAULT '',
        chapter_requests_5 VARCHAR(255) NOT NULL DEFAULT '',
        chapter_requests_6 VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_house_date (house_name, report_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$fields = [
    'report_date',
    'house_name',
    'chapter',
    'meeting_day_time',
    'house_president',
    'capacity',
    'occupied_beds',
    'vacant_beds',
    'applications_received',
    'new_members',
    'voluntary_departures',
    'relapse_departurers',
    'other_departures',
    'members_attending_3_meets',
    'members_on_contract',
    'owing_line_1',
    'owing_line_2',
    'owing_line_3',
    'amount_checking',
    'amount_saving',
    'number_of_members',
    'number_of_members_behind',
    'total_behind',
    'bills_caught_up',
    'bills_current',
    'members_behind',
    'answering_machine_checked',
    'ohi_donation',
    'email_checked_daily',
    'house_audit_done',
    'bank_statement_attached',
    'presentation_done_date',
    'members_with_jobs',
    'members_from_jail_prison_30_days',
    'reentry_members_arrested',
    'members_abused_opioids',
    'members_on_mat',
    'concerns_1',
    'concerns_2',
    'concerns_3',
    'concerns_4',
    'concerns_5',
    'concerns_6',
    'chapter_requests_1',
    'chapter_requests_2',
    'chapter_requests_3',
    'chapter_requests_4',
    'chapter_requests_5',
    'chapter_requests_6',
];

if (($_GET['action'] ?? '') === 'history') {
    $stmt = $pdo->query("
        SELECT id, house_name, report_date
        FROM house_summary_reports
        ORDER BY house_name ASC, report_date DESC, id DESC
    ");
    jsonResponse([
        'success' => true,
        'records' => $stmt->fetchAll(),
    ]);
}

if (($_GET['action'] ?? '') === 'load' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM house_summary_reports WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();

    if (!$row) {
        jsonResponse(['success' => false, 'message' => 'Record not found.'], 404);
    }

    jsonResponse(['success' => true, 'record' => $row]);
}

if (($_GET['action'] ?? '') === 'autosave' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $data = [];
    foreach ($fields as $fieldName) {
        $data[$fieldName] = trim((string)($_POST[$fieldName] ?? ''));
    }

    if ($data['report_date'] === '') {
        $data['report_date'] = date('Y-m-d');
    }

    if ($data['house_name'] === '') {
        jsonResponse([
            'success' => false,
            'message' => 'House name is required before auto-save.'
        ], 422);
    }

    $sql = "
        INSERT INTO house_summary_reports (
            report_date, house_name, chapter, meeting_day_time, house_president,
            capacity, occupied_beds, vacant_beds, applications_received, new_members,
            voluntary_departures, relapse_departurers, other_departures,
            members_attending_3_meets, members_on_contract,
            owing_line_1, owing_line_2, owing_line_3,
            amount_checking, amount_saving,
            number_of_members, number_of_members_behind, total_behind,
            bills_caught_up, bills_current, members_behind, answering_machine_checked,
            ohi_donation, email_checked_daily, house_audit_done, bank_statement_attached,
            presentation_done_date, members_with_jobs, members_from_jail_prison_30_days,
            reentry_members_arrested, members_abused_opioids, members_on_mat,
            concerns_1, concerns_2, concerns_3, concerns_4, concerns_5, concerns_6,
            chapter_requests_1, chapter_requests_2, chapter_requests_3,
            chapter_requests_4, chapter_requests_5, chapter_requests_6
        ) VALUES (
            :report_date, :house_name, :chapter, :meeting_day_time, :house_president,
            :capacity, :occupied_beds, :vacant_beds, :applications_received, :new_members,
            :voluntary_departures, :relapse_departurers, :other_departures,
            :members_attending_3_meets, :members_on_contract,
            :owing_line_1, :owing_line_2, :owing_line_3,
            :amount_checking, :amount_saving,
            :number_of_members, :number_of_members_behind, :total_behind,
            :bills_caught_up, :bills_current, :members_behind, :answering_machine_checked,
            :ohi_donation, :email_checked_daily, :house_audit_done, :bank_statement_attached,
            :presentation_done_date, :members_with_jobs, :members_from_jail_prison_30_days,
            :reentry_members_arrested, :members_abused_opioids, :members_on_mat,
            :concerns_1, :concerns_2, :concerns_3, :concerns_4, :concerns_5, :concerns_6,
            :chapter_requests_1, :chapter_requests_2, :chapter_requests_3,
            :chapter_requests_4, :chapter_requests_5, :chapter_requests_6
        )
        ON DUPLICATE KEY UPDATE
            chapter = VALUES(chapter),
            meeting_day_time = VALUES(meeting_day_time),
            house_president = VALUES(house_president),
            capacity = VALUES(capacity),
            occupied_beds = VALUES(occupied_beds),
            vacant_beds = VALUES(vacant_beds),
            applications_received = VALUES(applications_received),
            new_members = VALUES(new_members),
            voluntary_departures = VALUES(voluntary_departures),
            relapse_departurers = VALUES(relapse_departurers),
            other_departures = VALUES(other_departures),
            members_attending_3_meets = VALUES(members_attending_3_meets),
            members_on_contract = VALUES(members_on_contract),
            owing_line_1 = VALUES(owing_line_1),
            owing_line_2 = VALUES(owing_line_2),
            owing_line_3 = VALUES(owing_line_3),
            amount_checking = VALUES(amount_checking),
            amount_saving = VALUES(amount_saving),
            number_of_members = VALUES(number_of_members),
            number_of_members_behind = VALUES(number_of_members_behind),
            total_behind = VALUES(total_behind),
            bills_caught_up = VALUES(bills_caught_up),
            bills_current = VALUES(bills_current),
            members_behind = VALUES(members_behind),
            answering_machine_checked = VALUES(answering_machine_checked),
            ohi_donation = VALUES(ohi_donation),
            email_checked_daily = VALUES(email_checked_daily),
            house_audit_done = VALUES(house_audit_done),
            bank_statement_attached = VALUES(bank_statement_attached),
            presentation_done_date = VALUES(presentation_done_date),
            members_with_jobs = VALUES(members_with_jobs),
            members_from_jail_prison_30_days = VALUES(members_from_jail_prison_30_days),
            reentry_members_arrested = VALUES(reentry_members_arrested),
            members_abused_opioids = VALUES(members_abused_opioids),
            members_on_mat = VALUES(members_on_mat),
            concerns_1 = VALUES(concerns_1),
            concerns_2 = VALUES(concerns_2),
            concerns_3 = VALUES(concerns_3),
            concerns_4 = VALUES(concerns_4),
            concerns_5 = VALUES(concerns_5),
            concerns_6 = VALUES(concerns_6),
            chapter_requests_1 = VALUES(chapter_requests_1),
            chapter_requests_2 = VALUES(chapter_requests_2),
            chapter_requests_3 = VALUES(chapter_requests_3),
            chapter_requests_4 = VALUES(chapter_requests_4),
            chapter_requests_5 = VALUES(chapter_requests_5),
            chapter_requests_6 = VALUES(chapter_requests_6),
            updated_at = CURRENT_TIMESTAMP
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    $idStmt = $pdo->prepare("SELECT id FROM house_summary_reports WHERE house_name = ? AND report_date = ? LIMIT 1");
    $idStmt->execute([$data['house_name'], $data['report_date']]);
    $saved = $idStmt->fetch();

    jsonResponse([
        'success' => true,
        'message' => 'Auto-saved',
        'id' => $saved['id'] ?? null,
        'label' => $data['house_name'] . ' - ' . $data['report_date'],
    ]);
}

$formData = [];
foreach ($fields as $fieldName) {
    $formData[$fieldName] = '';
}
$formData['report_date'] = date('Y-m-d');

$historyStmt = $pdo->query("
    SELECT id, house_name, report_date
    FROM house_summary_reports
    ORDER BY house_name ASC, report_date DESC, id DESC
");
$historyRows = $historyStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Summary Report</title>
    <style>
        :root {
            --page-width: 8.5in;
            --page-height: 11in;
            --text: #111;
            --border: #111;
            --accent: #1f4d8f;
            --success: #177245;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #dcdcdc;
            color: var(--text);
            font-family: "Arial Narrow", Arial, Helvetica, sans-serif;
        }

        body {
            padding: 18px;
        }

        .topbar {
            width: var(--page-width);
            margin: 0 auto 14px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .actions button,
        .history-bar select,
        .history-bar input {
            border: 1px solid #333;
            background: #fff;
            padding: 8px 12px;
            font-size: 14px;
        }

        .actions button {
            cursor: pointer;
        }

        .history-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .status {
            font-size: 13px;
            color: var(--success);
            min-width: 140px;
        }

        .page {
            width: var(--page-width);
            min-height: var(--page-height);
            margin: 0 auto 18px auto;
            background: #fff;
            position: relative;
            padding: 12px 14px 52px 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .banner {
            width: 100%;
            display: block;
            margin: 0 0 30px 0;
        }

        .title {
            font-size: 18px;
            font-weight: 400;
            margin: 8px 0 24px 0;
        }

        .line-row,
        .triple-row,
        .double-row {
            width: 100%;
            margin: 0 0 20px 0;
            white-space: nowrap;
            font-size: 15px;
            line-height: 1.35;
        }

        .double-row {
            display: flex;
            gap: 18px;
            align-items: baseline;
            flex-wrap: nowrap;
        }

        .triple-row {
            display: flex;
            gap: 12px;
            align-items: baseline;
            flex-wrap: nowrap;
        }

        .field-wrap {
            display: inline-flex;
            align-items: baseline;
            min-width: 0;
            flex: 1 1 auto;
        }

        .label {
            white-space: nowrap;
        }

        .line-input,
        .small-input,
        .medium-input,
        .large-input,
        .money-input {
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            font: inherit;
            color: inherit;
            height: 24px;
            padding: 0 3px;
            border-radius: 0;
            min-width: 24px;
            max-width: 100%;
        }

        .line-input { width: 100%; }
        .small-input { width: 90px; }
        .medium-input { width: 145px; }
        .large-input { width: 220px; }
        .money-input { width: 120px; }

        .money-wrap {
            display: inline-flex;
            align-items: baseline;
            gap: 2px;
        }

        .money-sign {
            font-size: 15px;
            display: inline-block;
            min-width: 10px;
        }

        .yn-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 4px;
        }

        .yn-group label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 15px;
        }

        input[type="radio"] {
            width: 14px;
            height: 14px;
            margin: 0;
            vertical-align: middle;
        }

        .owed-lines {
            margin: 14px 0 22px 0;
        }

        .owed-line {
            width: 100%;
            max-width: 100%;
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            font: inherit;
            display: block;
            height: 28px;
            margin-bottom: 16px;
        }

        .concern-label {
            margin: 22px 0 10px 0;
            font-size: 15px;
        }

        .concern-line {
            width: 100%;
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            font: inherit;
            display: block;
            height: 28px;
            margin-bottom: 12px;
        }

        .page-number {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 16px;
            text-align: center;
            font-size: 18px;
        }

        .section-spacer {
            height: 8px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .topbar {
                display: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
                break-after: page;
            }

            .page:last-of-type {
                break-after: auto;
            }
        }
    </style>
</head>
<body>
<form id="reportForm" method="post" autocomplete="off">
    <input type="hidden" name="report_date" id="report_date" value="<?= field($formData, 'report_date') ?>">

    <div class="topbar">
        <div class="actions">
            <button type="button" id="saveNowBtn">Save Now</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>

        <div class="history-bar">
            <label for="historySelect">History</label>
            <select id="historySelect">
                <option value="">Select saved record</option>
                <?php foreach ($historyRows as $row): ?>
                    <option value="<?= (int)$row['id'] ?>">
                        <?= h($row['house_name']) ?> - <?= h($row['report_date']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="status" id="saveStatus">Ready</div>
        </div>
    </div>

    <div class="page">
        <img class="banner" src="../images/oxford_banner.png" alt="Oxford House Banner">

        <div class="title">House Summary Report</div>

        <div class="line-row">
            <span class="label">House Name </span>
            <input class="line-input autosave" type="text" name="house_name" value="<?= field($formData, 'house_name') ?>">
        </div>

        <div class="line-row">
            <span class="label">Report Date </span>
            <input class="line-input autosave" type="date" name="report_date_display" id="report_date_display" value="<?= field($formData, 'report_date') ?>">
        </div>

        <div class="line-row">
            <span class="label">What Chapter is your house in </span>
            <input class="line-input autosave" type="text" name="chapter" value="<?= field($formData, 'chapter') ?>">
        </div>

        <div class="line-row">
            <span class="label">House Meeting day and time </span>
            <input class="line-input autosave" type="text" name="meeting_day_time" value="<?= field($formData, 'meeting_day_time') ?>">
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">House President </span>
                <input class="medium-input autosave" type="text" name="house_president" value="<?= field($formData, 'house_president') ?>">
            </span>
        </div>

        <div class="triple-row">
            <span class="field-wrap">
                <span class="label">Capacity </span>
                <input class="small-input autosave" type="text" name="capacity" value="<?= field($formData, 'capacity') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Occupied Beds </span>
                <input class="small-input autosave" type="text" name="occupied_beds" value="<?= field($formData, 'occupied_beds') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Vacant Beds </span>
                <input class="small-input autosave" type="text" name="vacant_beds" value="<?= field($formData, 'vacant_beds') ?>">
            </span>
        </div>

        <div class="triple-row">
            <span class="field-wrap">
                <span class="label">Applications Recieved </span>
                <input class="small-input autosave" type="text" name="applications_received" value="<?= field($formData, 'applications_received') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">New Members </span>
                <input class="medium-input autosave" type="text" name="new_members" value="<?= field($formData, 'new_members') ?>">
            </span>
        </div>

        <div class="triple-row">
            <span class="field-wrap">
                <span class="label">Voluntary departures </span>
                <input class="small-input autosave" type="text" name="voluntary_departures" value="<?= field($formData, 'voluntary_departures') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Relapse Departurers </span>
                <input class="small-input autosave" type="text" name="relapse_departurers" value="<?= field($formData, 'relapse_departurers') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Other </span>
                <input class="small-input autosave" type="text" name="other_departures" value="<?= field($formData, 'other_departures') ?>">
            </span>
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Number of members attending 3 meets or more </span>
                <input class="small-input autosave" type="text" name="members_attending_3_meets" value="<?= field($formData, 'members_attending_3_meets') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Members on Contract </span>
                <input class="small-input autosave" type="text" name="members_on_contract" value="<?= field($formData, 'members_on_contract') ?>">
            </span>
        </div>

        <div class="line-row">
            <span class="label">Names Of memebers left owing Money: and How much?</span>
        </div>

        <div class="owed-lines">
            <input class="owed-line autosave" type="text" name="owing_line_1" value="<?= field($formData, 'owing_line_1') ?>">
            <input class="owed-line autosave" type="text" name="owing_line_2" value="<?= field($formData, 'owing_line_2') ?>">
            <input class="owed-line autosave" type="text" name="owing_line_3" value="<?= field($formData, 'owing_line_3') ?>">
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Amount in Checking </span>
                <span class="money-wrap">
                    <span class="money-sign">$</span>
                    <input class="money-input autosave" type="text" name="amount_checking" value="<?= field($formData, 'amount_checking') ?>">
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Amount in Saving </span>
                <span class="money-wrap">
                    <span class="money-sign">$</span>
                    <input class="money-input autosave" type="text" name="amount_saving" value="<?= field($formData, 'amount_saving') ?>">
                </span>
            </span>
        </div>

        <div class="section-spacer"></div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Number of Members </span>
                <input class="small-input autosave" type="text" name="number_of_members" value="<?= field($formData, 'number_of_members') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Number of Members behind </span>
                <input class="small-input autosave" type="text" name="number_of_members_behind" value="<?= field($formData, 'number_of_members_behind') ?>">
            </span>
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Total Behind </span>
                <span class="money-wrap">
                    <span class="money-sign">$</span>
                    <input class="money-input autosave" type="text" name="total_behind" value="<?= field($formData, 'total_behind') ?>">
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Are your bills caught up?</span>
                <span class="yn-group">
                    <label><input class="autosave" type="radio" name="bills_caught_up" value="Yes" <?= checkedValue($formData, 'bills_caught_up', 'Yes') ?>> Yes</label>
                    <label><input class="autosave" type="radio" name="bills_caught_up" value="No" <?= checkedValue($formData, 'bills_caught_up', 'No') ?>> No</label>
                </span>
            </span>
        </div>

        <div class="page-number">1</div>
    </div>

    <div class="page">
        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Are all bills Current</span>
                <span class="yn-group">
                    <label><input class="autosave" type="radio" name="bills_current" value="Y" <?= checkedValue($formData, 'bills_current', 'Y') ?>> Y</label>
                    <label><input class="autosave" type="radio" name="bills_current" value="N" <?= checkedValue($formData, 'bills_current', 'N') ?>> N</label>
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Members behind </span>
                <input class="small-input autosave" type="text" name="members_behind" value="<?= field($formData, 'members_behind') ?>">
            </span>
        </div>

        <div class="line-row">
            <span class="label">Answering machine checked </span>
            <input class="small-input autosave" type="text" name="answering_machine_checked" value="<?= field($formData, 'answering_machine_checked') ?>">
        </div>

        <div class="line-row">
            <span class="label">OHI Donation</span>
            <span class="yn-group">
                <label><input class="autosave" type="radio" name="ohi_donation" value="Y" <?= checkedValue($formData, 'ohi_donation', 'Y') ?>> Y</label>
                <label><input class="autosave" type="radio" name="ohi_donation" value="N" <?= checkedValue($formData, 'ohi_donation', 'N') ?>> N</label>
            </span>
        </div>

        <div class="line-row">
            <span class="label">Email Checked Daily</span>
            <span class="yn-group">
                <label><input class="autosave" type="radio" name="email_checked_daily" value="Y" <?= checkedValue($formData, 'email_checked_daily', 'Y') ?>> Y</label>
                <label><input class="autosave" type="radio" name="email_checked_daily" value="N" <?= checkedValue($formData, 'email_checked_daily', 'N') ?>> N</label>
            </span>
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">House audit done</span>
                <span class="yn-group">
                    <label><input class="autosave" type="radio" name="house_audit_done" value="Y" <?= checkedValue($formData, 'house_audit_done', 'Y') ?>> Y</label>
                    <label><input class="autosave" type="radio" name="house_audit_done" value="N" <?= checkedValue($formData, 'house_audit_done', 'N') ?>> N</label>
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Bank Statement attached</span>
                <span class="yn-group">
                    <label><input class="autosave" type="radio" name="bank_statement_attached" value="Y" <?= checkedValue($formData, 'bank_statement_attached', 'Y') ?>> Y</label>
                    <label><input class="autosave" type="radio" name="bank_statement_attached" value="N" <?= checkedValue($formData, 'bank_statement_attached', 'N') ?>> N</label>
                </span>
            </span>
        </div>

        <div class="line-row">
            <span class="label">Presentation Done and Date </span>
            <input class="medium-input autosave" type="text" name="presentation_done_date" value="<?= field($formData, 'presentation_done_date') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of Members with Jobs </span>
            <input class="medium-input autosave" type="text" name="members_with_jobs" value="<?= field($formData, 'members_with_jobs') ?>">
        </div>

        <div class="line-row">
            <span class="label">Members directly from jail prison in last 30 days </span>
            <input class="small-input autosave" type="text" name="members_from_jail_prison_30_days" value="<?= field($formData, 'members_from_jail_prison_30_days') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of re-entry members arrested while at Oxford </span>
            <input class="small-input autosave" type="text" name="reentry_members_arrested" value="<?= field($formData, 'reentry_members_arrested') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of member that abused Opioids </span>
            <input class="small-input autosave" type="text" name="members_abused_opioids" value="<?= field($formData, 'members_abused_opioids') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of members on MAT </span>
            <input class="small-input autosave" type="text" name="members_on_mat" value="<?= field($formData, 'members_on_mat') ?>">
        </div>

        <div class="concern-label">How is your house doing? Any Concerns?</div>
        <input class="concern-line autosave" type="text" name="concerns_1" value="<?= field($formData, 'concerns_1') ?>">
        <input class="concern-line autosave" type="text" name="concerns_2" value="<?= field($formData, 'concerns_2') ?>">
        <input class="concern-line autosave" type="text" name="concerns_3" value="<?= field($formData, 'concerns_3') ?>">
        <input class="concern-line autosave" type="text" name="concerns_4" value="<?= field($formData, 'concerns_4') ?>">
        <input class="concern-line autosave" type="text" name="concerns_5" value="<?= field($formData, 'concerns_5') ?>">
        <input class="concern-line autosave" type="text" name="concerns_6" value="<?= field($formData, 'concerns_6') ?>">

        <div class="concern-label">Is there anything you would like from chapter?</div>
        <input class="concern-line autosave" type="text" name="chapter_requests_1" value="<?= field($formData, 'chapter_requests_1') ?>">
        <input class="concern-line autosave" type="text" name="chapter_requests_2" value="<?= field($formData, 'chapter_requests_2') ?>">
        <input class="concern-line autosave" type="text" name="chapter_requests_3" value="<?= field($formData, 'chapter_requests_3') ?>">
        <input class="concern-line autosave" type="text" name="chapter_requests_4" value="<?= field($formData, 'chapter_requests_4') ?>">
        <input class="concern-line autosave" type="text" name="chapter_requests_5" value="<?= field($formData, 'chapter_requests_5') ?>">
        <input class="concern-line autosave" type="text" name="chapter_requests_6" value="<?= field($formData, 'chapter_requests_6') ?>">

        <div class="page-number">2</div>
    </div>
</form>

<script>
(() => {
    const form = document.getElementById('reportForm');
    const saveStatus = document.getElementById('saveStatus');
    const historySelect = document.getElementById('historySelect');
    const saveNowBtn = document.getElementById('saveNowBtn');
    const reportDateDisplay = document.getElementById('report_date_display');
    const reportDateHidden = document.getElementById('report_date');
    let saveTimer = null;
    let isSaving = false;

    function setStatus(text, isError = false) {
        saveStatus.textContent = text;
        saveStatus.style.color = isError ? '#b42318' : '#177245';
    }

    function syncDateField() {
        reportDateHidden.value = reportDateDisplay.value || '';
    }

    function fillForm(record) {
        Array.from(form.elements).forEach(el => {
            if (!el.name) return;

            if (el.type === 'radio') {
                el.checked = record[el.name] === el.value;
            } else if (el.name === 'report_date_display') {
                el.value = record['report_date'] || '';
            } else if (el.name in record) {
                el.value = record[el.name] ?? '';
            }
        });

        reportDateHidden.value = record.report_date || '';
    }

    async function refreshHistory(selectedId = '') {
        const res = await fetch('?action=history', { cache: 'no-store' });
        const data = await res.json();

        if (!data.success) return;

        historySelect.innerHTML = '<option value="">Select saved record</option>';
        data.records.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = `${row.house_name} - ${row.report_date}`;
            if (String(selectedId) === String(row.id)) {
                opt.selected = true;
            }
            historySelect.appendChild(opt);
        });
    }

    async function saveForm() {
        if (isSaving) return;
        syncDateField();

        const houseName = (form.querySelector('[name="house_name"]').value || '').trim();
        if (!houseName) {
            setStatus('Enter house name to save', true);
            return;
        }

        isSaving = true;
        setStatus('Saving...');

        try {
            const fd = new FormData(form);
            const res = await fetch('?action=autosave', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();

            if (!data.success) {
                setStatus(data.message || 'Save failed', true);
                isSaving = false;
                return;
            }

            setStatus(`Saved: ${data.label}`);
            await refreshHistory(data.id || '');
        } catch (err) {
            setStatus('Save failed', true);
        } finally {
            isSaving = false;
        }
    }

    function queueSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveForm, 700);
    }

    form.querySelectorAll('.autosave').forEach(el => {
        el.addEventListener('input', queueSave);
        el.addEventListener('change', queueSave);
    });

    reportDateDisplay.addEventListener('change', () => {
        syncDateField();
        queueSave();
    });

    saveNowBtn.addEventListener('click', saveForm);

    historySelect.addEventListener('change', async () => {
        if (!historySelect.value) return;

        setStatus('Loading...');
        try {
            const res = await fetch(`?action=load&id=${encodeURIComponent(historySelect.value)}`, { cache: 'no-store' });
            const data = await res.json();

            if (!data.success) {
                setStatus(data.message || 'Load failed', true);
                return;
            }

            fillForm(data.record);
            setStatus(`Loaded: ${data.record.house_name} - ${data.record.report_date}`);
        } catch (err) {
            setStatus('Load failed', true);
        }
    });

    syncDateField();
})();
</script>
</body>
</html>