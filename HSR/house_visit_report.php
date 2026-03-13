<?php
/**
 * Oxford House - HSC House Visit Report
 * Single-file PHP app
 * - Closely matches the uploaded House Visit Report sheet
 * - Auto-save to MySQL (debounced autosave + manual save)
 * - History by house name and saved date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals fields retained and numeric formatting helpers included
 */
declare(strict_types=1);

require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const FORM_TITLE = 'HSC HOUSE VISIT REPORT';

function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function moneyf(mixed $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function posted(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function jsonResponse(array $payload): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
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
} catch (PDOException $e) {
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec("CREATE TABLE IF NOT EXISTS house_visit_reports (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    phone VARCHAR(100) NOT NULL DEFAULT '',
    president VARCHAR(255) NOT NULL DEFAULT '',
    secretary VARCHAR(255) NOT NULL DEFAULT '',
    treasurer VARCHAR(255) NOT NULL DEFAULT '',
    comptroller VARCHAR(255) NOT NULL DEFAULT '',
    coordinator VARCHAR(255) NOT NULL DEFAULT '',
    hsc_rep VARCHAR(255) NOT NULL DEFAULT '',
    overall_appearance VARCHAR(10) NOT NULL DEFAULT '',
    overall_appearance_comments TEXT NULL,
    members_behind_ees DECIMAL(10,2) NULL DEFAULT NULL,
    total_amount_owed DECIMAL(12,2) NULL DEFAULT NULL,
    rent_paid_monthly DECIMAL(12,2) NULL DEFAULT NULL,
    ees_paid_weekly DECIMAL(12,2) NULL DEFAULT NULL,
    utilities_monthly DECIMAL(12,2) NULL DEFAULT NULL,
    house_business_meeting VARCHAR(10) NOT NULL DEFAULT '',
    house_business_comments TEXT NULL,
    rating_reading_traditions VARCHAR(10) NOT NULL DEFAULT '',
    rating_reading_minutes VARCHAR(10) NOT NULL DEFAULT '',
    rating_treasurer_report VARCHAR(10) NOT NULL DEFAULT '',
    rating_comptroller_report VARCHAR(10) NOT NULL DEFAULT '',
    rating_coordinator_report VARCHAR(10) NOT NULL DEFAULT '',
    rating_maintains_guidelines VARCHAR(10) NOT NULL DEFAULT '',
    rating_handling_business VARCHAR(10) NOT NULL DEFAULT '',
    rating_organization_order VARCHAR(10) NOT NULL DEFAULT '',
    financial_comments TEXT NULL,
    first_visit_date VARCHAR(50) NOT NULL DEFAULT '',
    narcan_present VARCHAR(10) NOT NULL DEFAULT '',
    narcan_trained VARCHAR(10) NOT NULL DEFAULT '',
    follow_up_visit_dates VARCHAR(255) NOT NULL DEFAULT '',
    hsc_rep_signature VARCHAR(255) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_house_name (house_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$fields = [
    'id' => '',
    'house_name' => '',
    'phone' => '',
    'president' => '',
    'secretary' => '',
    'treasurer' => '',
    'comptroller' => '',
    'coordinator' => '',
    'hsc_rep' => '',
    'overall_appearance' => '',
    'overall_appearance_comments' => '',
    'members_behind_ees' => '',
    'total_amount_owed' => '',
    'rent_paid_monthly' => '',
    'ees_paid_weekly' => '',
    'utilities_monthly' => '',
    'house_business_meeting' => '',
    'house_business_comments' => '',
    'rating_reading_traditions' => '',
    'rating_reading_minutes' => '',
    'rating_treasurer_report' => '',
    'rating_comptroller_report' => '',
    'rating_coordinator_report' => '',
    'rating_maintains_guidelines' => '',
    'rating_handling_business' => '',
    'rating_organization_order' => '',
    'financial_comments' => '',
    'first_visit_date' => '',
    'narcan_present' => '',
    'narcan_trained' => '',
    'follow_up_visit_dates' => '',
    'hsc_rep_signature' => '',
];

$numericFields = [
    'members_behind_ees', 'total_amount_owed', 'rent_paid_monthly', 'ees_paid_weekly', 'utilities_monthly'
];

function normalizePayload(array $source, array $fields, array $numericFields): array
{
    $payload = [];
    foreach ($fields as $key => $default) {
        $value = $source[$key] ?? $default;
        if (in_array($key, $numericFields, true)) {
            $trim = trim((string)$value);
            $payload[$key] = $trim === '' ? null : round((float)str_replace([',', '$'], '', $trim), 2);
        } else {
            $payload[$key] = is_string($value) ? trim($value) : $value;
        }
    }
    return $payload;
}

function saveReport(PDO $pdo, array $payload): int
{
    if (!empty($payload['id'])) {
        $sql = "UPDATE house_visit_reports SET
            house_name=:house_name,
            phone=:phone,
            president=:president,
            secretary=:secretary,
            treasurer=:treasurer,
            comptroller=:comptroller,
            coordinator=:coordinator,
            hsc_rep=:hsc_rep,
            overall_appearance=:overall_appearance,
            overall_appearance_comments=:overall_appearance_comments,
            members_behind_ees=:members_behind_ees,
            total_amount_owed=:total_amount_owed,
            rent_paid_monthly=:rent_paid_monthly,
            ees_paid_weekly=:ees_paid_weekly,
            utilities_monthly=:utilities_monthly,
            house_business_meeting=:house_business_meeting,
            house_business_comments=:house_business_comments,
            rating_reading_traditions=:rating_reading_traditions,
            rating_reading_minutes=:rating_reading_minutes,
            rating_treasurer_report=:rating_treasurer_report,
            rating_comptroller_report=:rating_comptroller_report,
            rating_coordinator_report=:rating_coordinator_report,
            rating_maintains_guidelines=:rating_maintains_guidelines,
            rating_handling_business=:rating_handling_business,
            rating_organization_order=:rating_organization_order,
            financial_comments=:financial_comments,
            first_visit_date=:first_visit_date,
            narcan_present=:narcan_present,
            narcan_trained=:narcan_trained,
            follow_up_visit_dates=:follow_up_visit_dates,
            hsc_rep_signature=:hsc_rep_signature
            WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($payload);
        return (int)$payload['id'];
    }

    unset($payload['id']);
    $sql = "INSERT INTO house_visit_reports (
        house_name, phone, president, secretary, treasurer, comptroller, coordinator, hsc_rep,
        overall_appearance, overall_appearance_comments, members_behind_ees, total_amount_owed,
        rent_paid_monthly, ees_paid_weekly, utilities_monthly, house_business_meeting, house_business_comments,
        rating_reading_traditions, rating_reading_minutes, rating_treasurer_report, rating_comptroller_report,
        rating_coordinator_report, rating_maintains_guidelines, rating_handling_business, rating_organization_order,
        financial_comments, first_visit_date, narcan_present, narcan_trained, follow_up_visit_dates, hsc_rep_signature
    ) VALUES (
        :house_name, :phone, :president, :secretary, :treasurer, :comptroller, :coordinator, :hsc_rep,
        :overall_appearance, :overall_appearance_comments, :members_behind_ees, :total_amount_owed,
        :rent_paid_monthly, :ees_paid_weekly, :utilities_monthly, :house_business_meeting, :house_business_comments,
        :rating_reading_traditions, :rating_reading_minutes, :rating_treasurer_report, :rating_comptroller_report,
        :rating_coordinator_report, :rating_maintains_guidelines, :rating_handling_business, :rating_organization_order,
        :financial_comments, :first_visit_date, :narcan_present, :narcan_trained, :follow_up_visit_dates, :hsc_rep_signature
    )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($payload);
    return (int)$pdo->lastInsertId();
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
    $houseName = trim((string)($_GET['house_name'] ?? ''));
    if ($houseName === '') {
        jsonResponse(['ok' => true, 'records' => []]);
    }
    $stmt = $pdo->prepare("SELECT id, house_name, created_at, updated_at, first_visit_date
        FROM house_visit_reports
        WHERE house_name = :house_name
        ORDER BY updated_at DESC, id DESC");
    $stmt->execute(['house_name' => $houseName]);
    jsonResponse(['ok' => true, 'records' => $stmt->fetchAll()]);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'load' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM house_visit_reports WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => (int)$_GET['id']]);
    $row = $stmt->fetch();
    jsonResponse(['ok' => (bool)$row, 'record' => $row ?: null]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'autosave' || ($_POST['action'] ?? '') === 'save')) {
    $payload = normalizePayload($_POST, $fields, $numericFields);
    $recordId = saveReport($pdo, $payload);
    $stmt = $pdo->prepare("SELECT updated_at, created_at FROM house_visit_reports WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $recordId]);
    $meta = $stmt->fetch() ?: ['updated_at' => '', 'created_at' => ''];
    jsonResponse([
        'ok' => true,
        'id' => $recordId,
        'saved_at' => $meta['updated_at'] ?: $meta['created_at'],
        'message' => ($_POST['action'] === 'autosave') ? 'Auto-saved' : 'Saved successfully'
    ]);
}

if (isset($_GET['load']) && ctype_digit((string)$_GET['load'])) {
    $stmt = $pdo->prepare("SELECT * FROM house_visit_reports WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => (int)$_GET['load']]);
    $row = $stmt->fetch();
    if ($row) {
        foreach ($fields as $key => $default) {
            $fields[$key] = $row[$key] ?? $default;
        }
    }
}

$houseOptions = $pdo->query("SELECT DISTINCT house_name FROM house_visit_reports WHERE house_name <> '' ORDER BY house_name ASC")->fetchAll(PDO::FETCH_COLUMN);
$initialHistory = [];
if ($fields['house_name'] !== '') {
    $stmt = $pdo->prepare("SELECT id, house_name, created_at, updated_at, first_visit_date
        FROM house_visit_reports WHERE house_name = :house_name ORDER BY updated_at DESC, id DESC");
    $stmt->execute(['house_name' => $fields['house_name']]);
    $initialHistory = $stmt->fetchAll();
}

function ratingOptions(string $selected): string
{
    $html = '<option value=""></option>';
    for ($i = 1; $i <= 5; $i++) {
        $sel = ((string)$selected === (string)$i) ? ' selected' : '';
        $html .= '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
    }
    return $html;
}

function yesNoOptions(string $selected): string
{
    $options = ['', 'YES', 'NO'];
    $html = '';
    foreach ($options as $option) {
        $sel = ($selected === $option) ? ' selected' : '';
        $html .= '<option value="' . h($option) . '"' . $sel . '>' . h($option) . '</option>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Visit Report</title>
    <style>
        :root {
            --border: #000;
            --light: #efefef;
            --panel: #f7f7f7;
            --text: #111;
            --accent: #1c4d8c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #d9dde3;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            padding: 18px;
        }
        .page {
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
            padding: 16px 18px 20px;
            border: 2px solid var(--border);
            box-shadow: 0 4px 18px rgba(0,0,0,.08);
        }
        .toolbar {
            max-width: 980px;
            margin: 0 auto 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .toolbar .group {
            background: #fff;
            border: 1px solid #b5bcc8;
            padding: 8px;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        button, select, input[type="text"], input[type="date"], input[type="number"] {
            font: inherit;
        }
        button {
            border: 1px solid #7b8798;
            background: #f6f7f9;
            padding: 7px 10px;
            cursor: pointer;
        }
        button:hover { background: #edf1f7; }
        .save-state {
            font-size: 12px;
            color: #334155;
            min-width: 160px;
        }
        .header-note {
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .scale-note {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .title-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .title-wrap img {
            height: 62px;
            width: auto;
            object-fit: contain;
        }
        h1 {
            margin: 0;
            font-size: 26px;
            letter-spacing: .5px;
            text-align: center;
        }
        .grid-2, .grid-3, .grid-4 {
            display: grid;
            gap: 0;
            border-left: 1px solid var(--border);
            border-top: 1px solid var(--border);
        }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .grid-3 { grid-template-columns: 1fr 1fr 1fr; }
        .grid-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
        .cell {
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            min-height: 38px;
            display: flex;
            align-items: stretch;
        }
        .label-cell {
            background: var(--light);
            font-weight: 700;
            font-size: 12px;
            padding: 6px 8px;
            align-items: center;
        }
        .field-cell {
            padding: 0;
        }
        .field-cell input,
        .field-cell select,
        .field-cell textarea {
            width: 100%;
            border: 0;
            padding: 7px 8px;
            min-height: 36px;
            font: inherit;
            background: #fff;
        }
        .field-cell textarea {
            min-height: 56px;
            resize: vertical;
        }
        .section-bar {
            margin-top: 10px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-bottom: 0;
            padding: 6px 8px;
            font-weight: 700;
            font-size: 13px;
        }
        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.report th,
        table.report td {
            border: 1px solid var(--border);
            padding: 6px 7px;
            font-size: 12px;
            vertical-align: middle;
        }
        table.report th {
            background: var(--light);
            text-align: left;
        }
        table.report td select,
        table.report td textarea,
        table.report td input {
            width: 100%;
            border: 0;
            padding: 4px;
            font: inherit;
            background: transparent;
        }
        .money {
            position: relative;
        }
        .money::before {
            content: '$';
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #444;
            font-size: 12px;
            pointer-events: none;
        }
        .money input {
            padding-left: 18px !important;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr .8fr;
            gap: 0;
            border-left: 1px solid var(--border);
            border-top: 1px solid var(--border);
            margin-top: 10px;
        }
        .footer-grid .cell { min-height: 42px; }
        .muted {
            color: #475569;
            font-size: 12px;
        }
        .print-only { display: none; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .page { border: 0; box-shadow: none; max-width: none; padding: 8mm; }
            .print-only { display: block; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="group">
            <label for="history_house_name"><strong>House History</strong></label>
            <select id="history_house_name">
                <option value="">Select House Name</option>
                <?php foreach ($houseOptions as $house): ?>
                    <option value="<?= h($house) ?>" <?= $fields['house_name'] === $house ? 'selected' : '' ?>><?= h($house) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="history_record_id">
                <option value="">Select Saved Date</option>
                <?php foreach ($initialHistory as $record): ?>
                    <option value="<?= (int)$record['id'] ?>">
                        <?= h(($record['first_visit_date'] ?: substr((string)$record['created_at'], 0, 10)) . ' | Saved ' . substr((string)$record['updated_at'], 0, 16)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="load_history_btn">Load Record</button>
        </div>
        <div class="group">
            <button type="button" id="save_btn">Save</button>
            <button type="button" onclick="window.print()">Print</button>
            <span class="save-state" id="save_state">Ready</span>
        </div>
    </div>

    <div class="page">
        <div class="header-note">THIS REPORT TO BE GIVEN AT MONTHLY HOUSING SERVICES COMMITTEE (HSC) MEETING</div>
        <div class="scale-note">1- Very Poor&nbsp;&nbsp;2 - Poor&nbsp;&nbsp;3 - Okay&nbsp;&nbsp;4 - Good&nbsp;&nbsp;5 - Excellent</div>
        <div class="title-wrap">
            <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo">
            <h1><?= h(FORM_TITLE) ?></h1>
        </div>

        <form id="reportForm">
            <input type="hidden" name="id" id="id" value="<?= h($fields['id']) ?>">

            <div class="grid-4">
                <div class="cell label-cell">HOUSE NAME:</div>
                <div class="cell field-cell"><input type="text" name="house_name" id="house_name" value="<?= h($fields['house_name']) ?>"></div>
                <div class="cell label-cell">PHONE #:</div>
                <div class="cell field-cell"><input type="text" name="phone" id="phone" value="<?= h($fields['phone']) ?>"></div>

                <div class="cell label-cell">PRESIDENT:</div>
                <div class="cell field-cell"><input type="text" name="president" value="<?= h($fields['president']) ?>"></div>
                <div class="cell label-cell">SECRETARY:</div>
                <div class="cell field-cell"><input type="text" name="secretary" value="<?= h($fields['secretary']) ?>"></div>

                <div class="cell label-cell">TREASURER:</div>
                <div class="cell field-cell"><input type="text" name="treasurer" value="<?= h($fields['treasurer']) ?>"></div>
                <div class="cell label-cell">COMPTROLLER:</div>
                <div class="cell field-cell"><input type="text" name="comptroller" value="<?= h($fields['comptroller']) ?>"></div>

                <div class="cell label-cell">COORDINATOR:</div>
                <div class="cell field-cell"><input type="text" name="coordinator" value="<?= h($fields['coordinator']) ?>"></div>
                <div class="cell label-cell">HSC REP:</div>
                <div class="cell field-cell"><input type="text" name="hsc_rep" value="<?= h($fields['hsc_rep']) ?>"></div>
            </div>

            <div class="section-bar">OVERALL APPEARANCE OF THE HOME:</div>
            <div class="grid-2">
                <div class="cell label-cell">IS HOUSE CLEAN, DUSTED, GENERALLY WELL TAKEN CARE OF.</div>
                <div class="cell field-cell">
                    <select name="overall_appearance">
                        <?= ratingOptions((string)$fields['overall_appearance']) ?>
                    </select>
                </div>
            </div>
            <div class="grid-2" style="border-top:0;">
                <div class="cell label-cell">COMMENTS:</div>
                <div class="cell field-cell"><textarea name="overall_appearance_comments"><?= h($fields['overall_appearance_comments']) ?></textarea></div>
            </div>

            <div class="section-bar">FINANCIAL INTEGRITY:</div>
            <div class="grid-2">
                <div class="cell label-cell">MEMBERS BEHIND IN EQUAL EXPENSE SHARE (EES):</div>
                <div class="cell field-cell"><input type="number" step="0.01" name="members_behind_ees" value="<?= h($fields['members_behind_ees']) ?>"></div>

                <div class="cell label-cell">TOTAL AMOUNT OWED HOUSE AT THIS TIME:</div>
                <div class="cell field-cell money"><input type="number" step="0.01" name="total_amount_owed" id="total_amount_owed" value="<?= h($fields['total_amount_owed']) ?>"></div>

                <div class="cell label-cell">AMOUNT OF RENT PAID TO LANDLORD PER MONTH:</div>
                <div class="cell field-cell money"><input type="number" step="0.01" name="rent_paid_monthly" value="<?= h($fields['rent_paid_monthly']) ?>"></div>

                <div class="cell label-cell">AMOUNT OF EES PAID BY HOUSE MEMBERS WEEKLY:</div>
                <div class="cell field-cell money"><input type="number" step="0.01" name="ees_paid_weekly" value="<?= h($fields['ees_paid_weekly']) ?>"></div>

                <div class="cell label-cell">ESTIMATED AMOUNT OF UTILITIES EACH MONTH:</div>
                <div class="cell field-cell money"><input type="number" step="0.01" name="utilities_monthly" value="<?= h($fields['utilities_monthly']) ?>"></div>

                <div class="cell label-cell">HOUSE BUSINESS MEETING:</div>
                <div class="cell field-cell">
                    <select name="house_business_meeting">
                        <?= ratingOptions((string)$fields['house_business_meeting']) ?>
                    </select>
                </div>
            </div>
            <div class="grid-2" style="border-top:0;">
                <div class="cell label-cell">COMMENTS:</div>
                <div class="cell field-cell"><textarea name="house_business_comments"><?= h($fields['house_business_comments']) ?></textarea></div>
            </div>

            <table class="report" aria-label="Rating table" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th style="width:72px;">Rating</th>
                        <th>Item</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><select name="rating_reading_traditions"><?= ratingOptions((string)$fields['rating_reading_traditions']) ?></select></td>
                        <td>1. READING OF TRADITIONS:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_reading_minutes"><?= ratingOptions((string)$fields['rating_reading_minutes']) ?></select></td>
                        <td>2. READING OF MINUTES:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_treasurer_report"><?= ratingOptions((string)$fields['rating_treasurer_report']) ?></select></td>
                        <td>3. PRESENTATION OF TREASURER REPORT:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_comptroller_report"><?= ratingOptions((string)$fields['rating_comptroller_report']) ?></select></td>
                        <td>4. PRESENTATION OF COMPTROLLER REPORT:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_coordinator_report"><?= ratingOptions((string)$fields['rating_coordinator_report']) ?></select></td>
                        <td>5. PRESENTATION OF COORDINATOR REPORT:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_maintains_guidelines"><?= ratingOptions((string)$fields['rating_maintains_guidelines']) ?></select></td>
                        <td>6. MAINTAINS GUIDELINES AND TRADITIONS:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_handling_business"><?= ratingOptions((string)$fields['rating_handling_business']) ?></select></td>
                        <td>7. HANDLING OF HOUSE BUSINESS/ISSUES:</td>
                    </tr>
                    <tr>
                        <td><select name="rating_organization_order"><?= ratingOptions((string)$fields['rating_organization_order']) ?></select></td>
                        <td>8. ORGANIZATION, ORDER, &amp; STRUCTURE:</td>
                    </tr>
                </tbody>
            </table>

            <div class="grid-2" style="margin-top:0; border-top:0;">
                <div class="cell label-cell">Comments</div>
                <div class="cell field-cell"><textarea name="financial_comments"><?= h($fields['financial_comments']) ?></textarea></div>
            </div>

            <div class="footer-grid">
                <div class="cell label-cell">DATE OF 1ST VISIT:</div>
                <div class="cell field-cell"><input type="text" name="first_visit_date" value="<?= h($fields['first_visit_date']) ?>"></div>
                <div class="cell label-cell muted">Use the same wording/date style as the paper form if needed.</div>

                <div class="cell label-cell">NARCAN PRESENT: YES / NO</div>
                <div class="cell field-cell"><select name="narcan_present"><?= yesNoOptions((string)$fields['narcan_present']) ?></select></div>
                <div class="cell label-cell">MEMBERS TRAINED ON NARCAN: YES / NO</div>

                <div class="cell label-cell">DATE OF FOLLOW UP VISIT(S):</div>
                <div class="cell field-cell"><input type="text" name="follow_up_visit_dates" value="<?= h($fields['follow_up_visit_dates']) ?>"></div>
                <div class="cell field-cell"><select name="narcan_trained"><?= yesNoOptions((string)$fields['narcan_trained']) ?></select></div>

                <div class="cell label-cell">HSC REP SIGNATURE:</div>
                <div class="cell field-cell" style="grid-column: span 2;"><input type="text" name="hsc_rep_signature" value="<?= h($fields['hsc_rep_signature']) ?>"></div>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('reportForm');
        const saveState = document.getElementById('save_state');
        const historyHouseName = document.getElementById('history_house_name');
        const historyRecordId = document.getElementById('history_record_id');
        const loadHistoryBtn = document.getElementById('load_history_btn');
        const saveBtn = document.getElementById('save_btn');
        const houseNameInput = document.getElementById('house_name');
        const idInput = document.getElementById('id');
        let autosaveTimer = null;
        let lastSerialized = new URLSearchParams(new FormData(form)).toString();

        function setSaveState(message) {
            saveState.textContent = message;
        }

        async function populateHistory(houseName, autoSelectCurrent = false) {
            historyRecordId.innerHTML = '<option value="">Select Saved Date</option>';
            if (!houseName) return;
            const res = await fetch(`?ajax=history&house_name=${encodeURIComponent(houseName)}`);
            const data = await res.json();
            if (!data.ok || !Array.isArray(data.records)) return;
            data.records.forEach(record => {
                const opt = document.createElement('option');
                const savedDate = (record.updated_at || '').slice(0, 16).replace('T', ' ');
                const visitDate = record.first_visit_date || (record.created_at || '').slice(0, 10);
                opt.value = record.id;
                opt.textContent = `${visitDate} | Saved ${savedDate}`;
                if (autoSelectCurrent && String(record.id) === String(idInput.value)) {
                    opt.selected = true;
                }
                historyRecordId.appendChild(opt);
            });
        }

        function fillForm(record) {
            Object.keys(record).forEach(key => {
                const field = form.elements.namedItem(key);
                if (!field) return;
                field.value = record[key] ?? '';
            });
            if (record.house_name) {
                historyHouseName.value = record.house_name;
            }
            lastSerialized = new URLSearchParams(new FormData(form)).toString();
            setSaveState('Record loaded');
            populateHistory(record.house_name || '', true);
        }

        async function saveForm(action = 'autosave') {
            const currentSerialized = new URLSearchParams(new FormData(form)).toString();
            if (action === 'autosave' && currentSerialized === lastSerialized) {
                return;
            }
            setSaveState(action === 'autosave' ? 'Auto-saving…' : 'Saving…');
            const data = new FormData(form);
            data.append('action', action);
            const res = await fetch('', { method: 'POST', body: data });
            const json = await res.json();
            if (json.ok) {
                if (json.id) idInput.value = json.id;
                lastSerialized = new URLSearchParams(new FormData(form)).toString();
                setSaveState(`${json.message} at ${json.saved_at}`);
                if (houseNameInput.value.trim()) {
                    historyHouseName.value = houseNameInput.value.trim();
                    populateHistory(houseNameInput.value.trim(), true);
                }
            } else {
                setSaveState('Save failed');
            }
        }

        form.addEventListener('input', () => {
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(() => saveForm('autosave'), 900);
        });

        saveBtn.addEventListener('click', () => saveForm('save'));

        houseNameInput.addEventListener('change', () => {
            historyHouseName.value = houseNameInput.value.trim();
            populateHistory(houseNameInput.value.trim());
        });

        historyHouseName.addEventListener('change', () => {
            populateHistory(historyHouseName.value);
        });

        loadHistoryBtn.addEventListener('click', async () => {
            if (!historyRecordId.value) return;
            const res = await fetch(`?ajax=load&id=${encodeURIComponent(historyRecordId.value)}`);
            const data = await res.json();
            if (data.ok && data.record) {
                fillForm(data.record);
            }
        });
    </script>
</body>
</html>
