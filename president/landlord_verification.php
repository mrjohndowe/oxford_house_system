<?php
/**
 * Catholic Charities of Southern Colorado - Landlord Verification Form
 * Single-file PHP app
 *
 * Features:
 * - Fillable form laid out to closely match the uploaded PDF
 * - Auto-save to MySQL on change / blur
 * - Load and edit prior records
 * - History dropdown filtered by Tenant Name
 * - Print button
 * - Auto-calculated totals / helper values
 */
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

const APP_TITLE = 'Catholic Charities of Southern Colorado';
const APP_SUBTITLE = 'Emergency Assistance Application';
const FORM_TITLE = 'Landlord Verification Form';
const TABLE_NAME = 'landlord_verification_forms';

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

function ynToInt(mixed $value): int
{
    return in_array((string)$value, ['1', 'yes', 'true', 'on'], true) ? 1 : 0;
}

function moneyString(mixed $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function moneyFloat(mixed $value): float
{
    if ($value === null || $value === '') {
        return 0.0;
    }
    return (float)preg_replace('/[^\d.-]/', '', (string)$value);
}

function normalizeDate(mixed $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $formats = ['Y-m-d', 'm/d/Y', 'n/j/Y', 'm/d/y', 'n/j/y'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }

    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : null;
}

function displayDate(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('m/d/Y', $ts) : $value;
}

function emptyForm(): array
{
    return [
        'id' => '',
        'property_owner_name' => '',
        'landlord_manager_name' => '',
        'manager_street' => '',
        'manager_city' => '',
        'manager_state' => '',
        'manager_zip' => '',
        'manager_county' => '',
        'manager_phone' => '',
        'manager_email' => '',
        'tenant_name' => '',
        'rental_street' => '',
        'rental_apt_lot' => '',
        'rental_city' => '',
        'rental_state' => '',
        'rental_zip' => '',
        'rental_county' => '',
        'bedrooms' => '',
        'lease_start_date' => '',
        'lease_end_date' => '',
        'monthly_rent_amount' => '',
        'next_payment_due_date' => '',
        'last_payment_amount' => '',
        'last_payment_date' => '',
        'tenant_in_arrears' => '1',
        'amount_owed' => '',
        'arrears_period_from' => '',
        'arrears_period_to' => '',
        'receiving_other_assistance' => '0',
        'other_assistance_amount' => '',
        'other_assistance_period' => '',
        'payment_method' => 'check',
        'check_payable_to' => '',
        'send_to_landlord_address' => '1',
        'alternative_address' => '',
        'cert_name' => '',
        'cert_title' => '',
        'cert_signature' => '',
        'cert_date' => '',
        'calc_balance_remaining' => '',
        'calc_total_assistance_gap' => '',
    ];
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
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS `" . TABLE_NAME . "` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `tenant_name` VARCHAR(255) NOT NULL DEFAULT '',
        `property_owner_name` VARCHAR(255) NOT NULL DEFAULT '',
        `landlord_manager_name` VARCHAR(255) NOT NULL DEFAULT '',
        `manager_street` VARCHAR(255) NOT NULL DEFAULT '',
        `manager_city` VARCHAR(150) NOT NULL DEFAULT '',
        `manager_state` VARCHAR(50) NOT NULL DEFAULT '',
        `manager_zip` VARCHAR(25) NOT NULL DEFAULT '',
        `manager_county` VARCHAR(150) NOT NULL DEFAULT '',
        `manager_phone` VARCHAR(50) NOT NULL DEFAULT '',
        `manager_email` VARCHAR(255) NOT NULL DEFAULT '',
        `rental_street` VARCHAR(255) NOT NULL DEFAULT '',
        `rental_apt_lot` VARCHAR(100) NOT NULL DEFAULT '',
        `rental_city` VARCHAR(150) NOT NULL DEFAULT '',
        `rental_state` VARCHAR(50) NOT NULL DEFAULT '',
        `rental_zip` VARCHAR(25) NOT NULL DEFAULT '',
        `rental_county` VARCHAR(150) NOT NULL DEFAULT '',
        `bedrooms` VARCHAR(20) NOT NULL DEFAULT '',
        `lease_start_date` DATE NULL,
        `lease_end_date` DATE NULL,
        `monthly_rent_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `next_payment_due_date` DATE NULL,
        `last_payment_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `last_payment_date` DATE NULL,
        `tenant_in_arrears` TINYINT(1) NOT NULL DEFAULT 0,
        `amount_owed` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `arrears_period_from` DATE NULL,
        `arrears_period_to` DATE NULL,
        `receiving_other_assistance` TINYINT(1) NOT NULL DEFAULT 0,
        `other_assistance_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `other_assistance_period` VARCHAR(100) NOT NULL DEFAULT '',
        `payment_method` VARCHAR(20) NOT NULL DEFAULT 'check',
        `check_payable_to` VARCHAR(255) NOT NULL DEFAULT '',
        `send_to_landlord_address` TINYINT(1) NOT NULL DEFAULT 1,
        `alternative_address` TEXT NULL,
        `cert_name` VARCHAR(255) NOT NULL DEFAULT '',
        `cert_title` VARCHAR(255) NOT NULL DEFAULT '',
        `cert_signature` VARCHAR(255) NOT NULL DEFAULT '',
        `cert_date` DATE NULL,
        `calc_balance_remaining` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `calc_total_assistance_gap` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `tenant_name_idx` (`tenant_name`),
        KEY `updated_at_idx` (`updated_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

function collectFormData(): array
{
    $monthlyRent = moneyFloat(posted('monthly_rent_amount'));
    $lastPayment = moneyFloat(posted('last_payment_amount'));
    $amountOwed = moneyFloat(posted('amount_owed'));
    $otherAssist = moneyFloat(posted('other_assistance_amount'));

    $balanceRemaining = max($monthlyRent - $lastPayment, 0);
    $assistanceGap = max($amountOwed - $otherAssist, 0);

    return [
        'id' => (int)posted('id', 0),
        'tenant_name' => trim((string)posted('tenant_name')),
        'property_owner_name' => trim((string)posted('property_owner_name')),
        'landlord_manager_name' => trim((string)posted('landlord_manager_name')),
        'manager_street' => trim((string)posted('manager_street')),
        'manager_city' => trim((string)posted('manager_city')),
        'manager_state' => trim((string)posted('manager_state')),
        'manager_zip' => trim((string)posted('manager_zip')),
        'manager_county' => trim((string)posted('manager_county')),
        'manager_phone' => trim((string)posted('manager_phone')),
        'manager_email' => trim((string)posted('manager_email')),
        'rental_street' => trim((string)posted('rental_street')),
        'rental_apt_lot' => trim((string)posted('rental_apt_lot')),
        'rental_city' => trim((string)posted('rental_city')),
        'rental_state' => trim((string)posted('rental_state')),
        'rental_zip' => trim((string)posted('rental_zip')),
        'rental_county' => trim((string)posted('rental_county')),
        'bedrooms' => trim((string)posted('bedrooms')),
        'lease_start_date' => normalizeDate(posted('lease_start_date')),
        'lease_end_date' => normalizeDate(posted('lease_end_date')),
        'monthly_rent_amount' => $monthlyRent,
        'next_payment_due_date' => normalizeDate(posted('next_payment_due_date')),
        'last_payment_amount' => $lastPayment,
        'last_payment_date' => normalizeDate(posted('last_payment_date')),
        'tenant_in_arrears' => ynToInt(posted('tenant_in_arrears')),
        'amount_owed' => $amountOwed,
        'arrears_period_from' => normalizeDate(posted('arrears_period_from')),
        'arrears_period_to' => normalizeDate(posted('arrears_period_to')),
        'receiving_other_assistance' => ynToInt(posted('receiving_other_assistance')),
        'other_assistance_amount' => $otherAssist,
        'other_assistance_period' => trim((string)posted('other_assistance_period')),
        'payment_method' => in_array(posted('payment_method'), ['eft', 'check'], true) ? (string)posted('payment_method') : 'check',
        'check_payable_to' => trim((string)posted('check_payable_to')),
        'send_to_landlord_address' => ynToInt(posted('send_to_landlord_address')),
        'alternative_address' => trim((string)posted('alternative_address')),
        'cert_name' => trim((string)posted('cert_name')),
        'cert_title' => trim((string)posted('cert_title')),
        'cert_signature' => trim((string)posted('cert_signature')),
        'cert_date' => normalizeDate(posted('cert_date')),
        'calc_balance_remaining' => $balanceRemaining,
        'calc_total_assistance_gap' => $assistanceGap,
    ];
}

function saveRecord(PDO $pdo, array $data): int
{
    $columns = [
        'tenant_name', 'property_owner_name', 'landlord_manager_name', 'manager_street', 'manager_city', 'manager_state', 'manager_zip',
        'manager_county', 'manager_phone', 'manager_email', 'rental_street', 'rental_apt_lot', 'rental_city', 'rental_state',
        'rental_zip', 'rental_county', 'bedrooms', 'lease_start_date', 'lease_end_date', 'monthly_rent_amount', 'next_payment_due_date',
        'last_payment_amount', 'last_payment_date', 'tenant_in_arrears', 'amount_owed', 'arrears_period_from', 'arrears_period_to',
        'receiving_other_assistance', 'other_assistance_amount', 'other_assistance_period', 'payment_method', 'check_payable_to',
        'send_to_landlord_address', 'alternative_address', 'cert_name', 'cert_title', 'cert_signature', 'cert_date',
        'calc_balance_remaining', 'calc_total_assistance_gap'
    ];

    if (!empty($data['id'])) {
        $set = implode(', ', array_map(static fn($col) => "`{$col}` = :{$col}", $columns));
        $sql = "UPDATE `" . TABLE_NAME . "` SET {$set} WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        foreach ($columns as $col) {
            $stmt->bindValue(':' . $col, $data[$col]);
        }
        $stmt->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);
        $stmt->execute();
        return (int)$data['id'];
    }

    $fields = '`' . implode('`, `', $columns) . '`';
    $placeholders = ':' . implode(', :', $columns);
    $sql = "INSERT INTO `" . TABLE_NAME . "` ({$fields}) VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    foreach ($columns as $col) {
        $stmt->bindValue(':' . $col, $data[$col]);
    }
    $stmt->execute();
    return (int)$pdo->lastInsertId();
}

function fetchRecord(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM `" . TABLE_NAME . "` WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    foreach (['lease_start_date', 'lease_end_date', 'next_payment_due_date', 'last_payment_date', 'arrears_period_from', 'arrears_period_to', 'cert_date'] as $dateField) {
        $row[$dateField] = displayDate($row[$dateField]);
    }
    foreach (['monthly_rent_amount', 'last_payment_amount', 'amount_owed', 'other_assistance_amount', 'calc_balance_remaining', 'calc_total_assistance_gap'] as $moneyField) {
        $row[$moneyField] = moneyString($row[$moneyField]);
    }
    foreach (['tenant_in_arrears', 'receiving_other_assistance', 'send_to_landlord_address'] as $boolField) {
        $row[$boolField] = (string)((int)$row[$boolField]);
    }
    return $row;
}

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        if ($_GET['ajax'] === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = collectFormData();
            $id = saveRecord($pdo, $data);

            $record = fetchRecord($pdo, $id) ?? [];
            echo json_encode([
                'ok' => true,
                'id' => $id,
                'message' => 'Auto-saved',
                'calc_balance_remaining' => $record['calc_balance_remaining'] ?? '0.00',
                'calc_total_assistance_gap' => $record['calc_total_assistance_gap'] ?? '0.00',
                'updated_at' => date('m/d/Y h:i:s A'),
            ]);
            exit;
        }

        if ($_GET['ajax'] === 'history') {
            $tenant = trim((string)($_GET['tenant_name'] ?? ''));
            if ($tenant === '') {
                $stmt = $pdo->query("SELECT id, tenant_name, rental_street, updated_at FROM `" . TABLE_NAME . "` ORDER BY updated_at DESC LIMIT 50");
            } else {
                $stmt = $pdo->prepare(
                    "SELECT id, tenant_name, rental_street, updated_at
                     FROM `" . TABLE_NAME . "`
                     WHERE tenant_name LIKE :tenant
                     ORDER BY updated_at DESC
                     LIMIT 50"
                );
                $stmt->execute([':tenant' => '%' . $tenant . '%']);
            }
            echo json_encode(['ok' => true, 'records' => $stmt->fetchAll()]);
            exit;
        }

        if ($_GET['ajax'] === 'load' && isset($_GET['id'])) {
            $record = fetchRecord($pdo, (int)$_GET['id']);
            echo json_encode(['ok' => $record !== null, 'record' => $record]);
            exit;
        }

        echo json_encode(['ok' => false, 'message' => 'Invalid request']);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$form = emptyForm();
if (isset($_GET['load_id']) && ctype_digit((string)$_GET['load_id'])) {
    $loaded = fetchRecord($pdo, (int)$_GET['load_id']);
    if ($loaded) {
        $form = array_merge($form, $loaded);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>joseph ponce landlord verification</title>
    <style>
        :root {
            --page-width: 8.5in;
            --text: #111;
            --line: #222;
            --muted: #666;
            --bg: #f2f2f2;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: "Times New Roman", Times, serif;
        }
        .topbar {
            max-width: calc(var(--page-width) + 40px);
            margin: 14px auto 0;
            padding: 0 10px;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .toolbar, .historybar {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .toolbar button, .historybar button, .historybar select, .historybar input {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            padding: 7px 10px;
            border: 1px solid #b9b9b9;
            background: #fff;
            border-radius: 4px;
        }
        .toolbar button { cursor: pointer; }
        .page {
            width: var(--page-width);
            min-height: 11in;
            background: #fff;
            margin: 10px auto 20px;
            padding: 0.48in 0.7in 0.45in;
            box-shadow: 0 2px 14px rgba(0,0,0,.12);
            position: relative;
        }
        h1, h2, h3, p { margin: 0; }
        .center { text-align: center; }
        .title-1 { font-weight: 700; font-size: 17px; }
        .title-2 { font-weight: 700; font-size: 17px; margin-top: 2px; }
        .title-3 { font-weight: 700; font-size: 16px; margin-top: 16px; margin-bottom: 18px; }
        .status {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #0a6b2b;
            min-height: 18px;
        }
        .section { margin-top: 6px; }
        .label-row, .inline-row, .triple-row, .double-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            width: 100%;
        }
        .label-row { margin-bottom: 12px; }
        .double-row { margin-bottom: 12px; }
        .triple-row { margin-bottom: 12px; }
        .field {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            min-width: 0;
        }
        .field.block {
            display: block;
        }
        .label {
            font-size: 15px;
            line-height: 1.15;
            white-space: nowrap;
        }
        .line-input, .line-display, .line-textarea {
            border: 0;
            border-bottom: 1px solid var(--line);
            outline: 0;
            background: transparent;
            font-family: "Times New Roman", Times, serif;
            font-size: 15px;
            line-height: 1.2;
            padding: 0 2px 1px;
            min-height: 22px;
            width: 100%;
        }
        .line-textarea {
            resize: none;
            height: 24px;
            overflow: hidden;
        }
        .w-40 { width: 40px; }
        .w-55 { width: 55px; }
        .w-70 { width: 70px; }
        .w-90 { width: 90px; }
        .w-110 { width: 110px; }
        .w-130 { width: 130px; }
        .w-150 { width: 150px; }
        .w-180 { width: 180px; }
        .w-220 { width: 220px; }
        .grow { flex: 1 1 auto; }
        .indent { padding-left: 38px; }
        .indent-2 { padding-left: 58px; }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 18px;
            margin: 8px 0 4px 38px;
            font-size: 15px;
        }
        .checkbox-row label, .sub-checks label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        input[type="checkbox"], input[type="radio"] {
            width: 14px;
            height: 14px;
            margin: 0;
            accent-color: #000;
        }
        .paragraphs {
            margin-top: 26px;
            font-size: 15px;
            line-height: 1.35;
        }
        .paragraphs p { margin-bottom: 18px; }
        .signature-grid {
            margin-top: 34px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 28px;
        }
        .sig-field .line-input { min-height: 24px; }
        .sig-caption {
            margin-top: 2px;
            font-size: 15px;
        }
        .page-num {
            position: absolute;
            right: 18px;
            bottom: 10px;
            font-size: 14px;
        }
        .helper-box {
            margin: 14px 0 10px;
            padding: 10px 12px;
            border: 1px solid #d7d7d7;
            background: #fafafa;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            display: grid;
            grid-template-columns: repeat(2, minmax(160px, 1fr));
            gap: 8px 16px;
        }
        .helper-box strong { font-size: 12px; }
        @media print {
            body { background: #fff; }
            .topbar { display: none !important; }
            .page {
                box-shadow: none;
                margin: 0 auto;
                padding-top: 0.45in;
                page-break-after: always;
            }
            .page:last-of-type { page-break-after: auto; }
        }
    </style>
</head>
<body>
<div class="topbar">
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="newRecord()">New Record</button>
        <div class="status" id="saveStatus">Ready</div>
    </div>
    <div class="historybar">
        <label for="historyTenant" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;">Tenant Name</label>
        <input type="text" id="historyTenant" placeholder="Search tenant name">
        <button type="button" onclick="loadHistory()">History</button>
        <select id="historySelect">
            <option value="">Select prior record...</option>
        </select>
        <button type="button" onclick="loadSelectedRecord()">Load</button>
    </div>
</div>

<form id="landlordForm" autocomplete="off">
    <input type="hidden" name="id" id="record_id" value="<?= h($form['id']) ?>">

    <div class="page">
        <div class="center">
            <div class="title-1"><?= h(APP_TITLE) ?></div>
            <div class="title-2"><?= h(APP_SUBTITLE) ?></div>
            <div class="title-3"><?= h(FORM_TITLE) ?></div>
        </div>

        <div class="section">
            <div class="label-row">
                <div class="field grow">
                    <div class="label">Property Owner Name:</div>
                    <input class="line-input" name="property_owner_name" value="<?= h($form['property_owner_name']) ?>">
                </div>
            </div>

            <div class="label-row">
                <div class="field grow">
                    <div class="label">Landlord/Property Manager Name <em>(if different from Property Owner)</em>:</div>
                    <input class="line-input" name="landlord_manager_name" value="<?= h($form['landlord_manager_name']) ?>">
                </div>
            </div>

            <div class="label-row" style="margin-bottom:8px;">
                <div class="label">Landlord/Property Manager's Address and Contact Info:</div>
            </div>

            <div class="double-row indent">
                <div class="field grow">
                    <div class="label">Street:</div>
                    <input class="line-input" name="manager_street" value="<?= h($form['manager_street']) ?>">
                </div>
            </div>

            <div class="triple-row indent">
                <div class="field grow">
                    <div class="label">City:</div>
                    <input class="line-input" name="manager_city" value="<?= h($form['manager_city']) ?>">
                </div>
                <div class="field w-130">
                    <div class="label">State:</div>
                    <input class="line-input" name="manager_state" value="<?= h($form['manager_state']) ?>">
                </div>
                <div class="field w-150">
                    <div class="label">Zip Code:</div>
                    <input class="line-input" name="manager_zip" value="<?= h($form['manager_zip']) ?>">
                </div>
            </div>

            <div class="triple-row indent">
                <div class="field grow">
                    <div class="label">County:</div>
                    <input class="line-input" name="manager_county" value="<?= h($form['manager_county']) ?>">
                </div>
            </div>

            <div class="triple-row indent">
                <div class="field grow">
                    <div class="label">Phone #:</div>
                    <input class="line-input" name="manager_phone" value="<?= h($form['manager_phone']) ?>">
                </div>
                <div class="field grow">
                    <div class="label">Email:</div>
                    <input class="line-input" name="manager_email" value="<?= h($form['manager_email']) ?>">
                </div>
            </div>

            <div class="label-row" style="margin-top:20px;">
                <div class="field grow">
                    <div class="label">Tenant's Name:</div>
                    <input class="line-input autosave-watch" name="tenant_name" value="<?= h($form['tenant_name']) ?>">
                </div>
            </div>

            <div class="label-row" style="margin-bottom:8px;">
                <div class="label">Address of Rental Unit:</div>
            </div>

            <div class="double-row indent">
                <div class="field grow">
                    <div class="label">Street:</div>
                    <input class="line-input" name="rental_street" value="<?= h($form['rental_street']) ?>">
                </div>
                <div class="field w-180">
                    <div class="label">Apt/Lot #:</div>
                    <input class="line-input" name="rental_apt_lot" value="<?= h($form['rental_apt_lot']) ?>">
                </div>
            </div>

            <div class="triple-row indent">
                <div class="field grow">
                    <div class="label">City:</div>
                    <input class="line-input" name="rental_city" value="<?= h($form['rental_city']) ?>">
                </div>
                <div class="field w-130">
                    <div class="label">State:</div>
                    <input class="line-input" name="rental_state" value="<?= h($form['rental_state']) ?>">
                </div>
                <div class="field w-150">
                    <div class="label">Zip Code:</div>
                    <input class="line-input" name="rental_zip" value="<?= h($form['rental_zip']) ?>">
                </div>
            </div>

            <div class="triple-row indent">
                <div class="field grow">
                    <div class="label">County:</div>
                    <input class="line-input" name="rental_county" value="<?= h($form['rental_county']) ?>">
                </div>
            </div>

            <div class="label-row" style="margin-top:10px;">
                <div class="field grow">
                    <div class="label">Number of Bedrooms in Rental Unit Listed Above:</div>
                    <input class="line-input w-55" name="bedrooms" value="<?= h($form['bedrooms']) ?>">
                </div>
            </div>

            <div class="double-row">
                <div class="field grow">
                    <div class="label">Lease Start Date:</div>
                    <input class="line-input w-130" name="lease_start_date" value="<?= h($form['lease_start_date']) ?>">
                </div>
                <div class="field grow">
                    <div class="label">Lease End Date:</div>
                    <input class="line-input w-130" name="lease_end_date" value="<?= h($form['lease_end_date']) ?>">
                </div>
            </div>

            <div class="double-row">
                <div class="field grow">
                    <div class="label">Monthly Rent Amount: $</div>
                    <input class="line-input w-130 money-field" name="monthly_rent_amount" value="<?= h($form['monthly_rent_amount']) ?>">
                </div>
            </div>

            <div class="double-row">
                <div class="field grow">
                    <div class="label">Date Next Payment Due:</div>
                    <input class="line-input w-130" name="next_payment_due_date" value="<?= h($form['next_payment_due_date']) ?>">
                </div>
            </div>

            <div class="double-row">
                <div class="field grow">
                    <div class="label">Amount of Last Payment Received: $</div>
                    <input class="line-input w-130 money-field" name="last_payment_amount" value="<?= h($form['last_payment_amount']) ?>">
                </div>
                <div class="field grow">
                    <div class="label">Date of Last Payment:</div>
                    <input class="line-input w-130" name="last_payment_date" value="<?= h($form['last_payment_date']) ?>">
                </div>
            </div>

            <div class="label-row" style="margin-top:10px;margin-bottom:4px;">
                <div class="label">Is the tenant in arrears?</div>
            </div>
            <div class="checkbox-row">
                <label><input type="radio" name="tenant_in_arrears" value="1" <?= ((string)$form['tenant_in_arrears'] === '1') ? 'checked' : '' ?>> Yes</label>
                <label><input type="radio" name="tenant_in_arrears" value="0" <?= ((string)$form['tenant_in_arrears'] === '0') ? 'checked' : '' ?>> No</label>
            </div>

            <div class="double-row indent">
                <div class="field grow">
                    <div class="label">If yes, how much does the tenant owe? $</div>
                    <input class="line-input w-130 money-field" name="amount_owed" value="<?= h($form['amount_owed']) ?>">
                </div>
            </div>

            <div class="double-row indent">
                <div class="field grow">
                    <div class="label">For what period?</div>
                    <input class="line-input w-110" name="arrears_period_from" value="<?= h($form['arrears_period_from']) ?>">
                </div>
                <div class="field grow" style="max-width:170px;">
                    <div class="label">to</div>
                    <input class="line-input w-110" name="arrears_period_to" value="<?= h($form['arrears_period_to']) ?>">
                </div>
            </div>

            <div class="label-row" style="margin-top:12px;margin-bottom:4px;">
                <div class="label">Are you currently receiving any other form of rental assistance for this household?</div>
            </div>
            <div class="checkbox-row">
                <label><input type="radio" name="receiving_other_assistance" value="1" <?= ((string)$form['receiving_other_assistance'] === '1') ? 'checked' : '' ?>> Yes</label>
                <label><input type="radio" name="receiving_other_assistance" value="0" <?= ((string)$form['receiving_other_assistance'] === '0') ? 'checked' : '' ?>> No</label>
            </div>

            <div class="double-row indent">
                <div class="field grow">
                    <div class="label">If yes, how much have you received? $</div>
                    <input class="line-input w-110 money-field" name="other_assistance_amount" value="<?= h($form['other_assistance_amount']) ?>">
                </div>
                <div class="field grow">
                    <div class="label">per</div>
                    <input class="line-input w-130" name="other_assistance_period" value="<?= h($form['other_assistance_period']) ?>">
                </div>
            </div>
        </div>

        <div class="helper-box">
            <div><strong>Auto Balance Remaining:</strong> $<span id="calc_balance_remaining"><?= h($form['calc_balance_remaining']) ?></span></div>
            <div><strong>Auto Assistance Gap:</strong> $<span id="calc_total_assistance_gap"><?= h($form['calc_total_assistance_gap']) ?></span></div>
        </div>

        <div class="page-num">1</div>
    </div>

    <div class="page">
        <div class="center">
            <div class="title-1"><?= h(APP_TITLE) ?></div>
            <div class="title-2"><?= h(APP_SUBTITLE) ?></div>
        </div>

        <div class="section" style="margin-top:38px;">
            <div class="label-row" style="margin-bottom:12px;">
                <div class="label">How do you wish to receive payment?</div>
            </div>

            <div class="checkbox-row" style="margin-left:44px; margin-bottom:8px;">
                <label><input type="radio" name="payment_method" value="eft" <?= ((string)$form['payment_method'] === 'eft') ? 'checked' : '' ?>> Electronic Funds Transfer (complete attached ACH form)</label>
            </div>
            <div class="checkbox-row" style="margin-left:44px; margin-bottom:6px;">
                <label><input type="radio" name="payment_method" value="check" <?= ((string)$form['payment_method'] === 'check') ? 'checked' : '' ?>> Check made to</label>
                <input class="line-input grow" style="max-width:280px;" name="check_payable_to" value="<?= h($form['check_payable_to']) ?>">
                <span>and sent to:</span>
            </div>

            <div class="sub-checks" style="margin-left:66px; font-size:15px;">
                <div style="margin-bottom:6px; display:flex; align-items:center; gap:10px;">
                    <label><input type="radio" name="send_to_landlord_address" value="1" <?= ((string)$form['send_to_landlord_address'] === '1') ? 'checked' : '' ?>> The Landlord/Property Manager's address listed above or</label>
                </div>
                <div style="display:flex; align-items:flex-end; gap:10px;">
                    <label><input type="radio" name="send_to_landlord_address" value="0" <?= ((string)$form['send_to_landlord_address'] === '0') ? 'checked' : '' ?>> The following alternative address:</label>
                </div>
                <div style="margin:6px 0 0 30px;">
                    <textarea class="line-textarea" name="alternative_address"><?= h($form['alternative_address']) ?></textarea>
                </div>
                <div style="margin:14px 0 0 30px;">
                    <div class="line-display" style="min-height:24px;"></div>
                </div>
            </div>

            <div class="paragraphs">
                <p>I, the undersigned, certify that to the best of my knowledge the rental unit referenced above contains no health or safety violations that threatens the health or safety of the tenant.</p>
                <p>I certify that I have not received rent payments from Catholic Charities or any other source to cover the unpaid rent listed above.</p>
                <p>I agree that I will not evict the tenant, provide the tenant with a five-day notice, or in any way ask the tenant to leave for the duration of this assistance.</p>
                <p>I agree that if the tenant is facing eviction, I will only accept payment arrears if the eviction will be avoided.</p>
                <p>I confirm that the above information is true and accurate to the best of my knowledge and that providing false representations herein constitutes an act of fraud.</p>
            </div>

            <div class="signature-grid">
                <div class="sig-field">
                    <input class="line-input" name="cert_name" value="<?= h($form['cert_name']) ?>">
                    <div class="sig-caption">Name</div>
                </div>
                <div class="sig-field">
                    <input class="line-input" name="cert_title" value="<?= h($form['cert_title']) ?>">
                    <div class="sig-caption">Title</div>
                </div>
                <div class="sig-field">
                    <input class="line-input" name="cert_signature" value="<?= h($form['cert_signature']) ?>">
                    <div class="sig-caption">Signature</div>
                </div>
                <div class="sig-field">
                    <input class="line-input" name="cert_date" value="<?= h($form['cert_date']) ?>">
                    <div class="sig-caption">Date</div>
                </div>
            </div>
        </div>

        <div class="page-num">2</div>
    </div>
</form>

<script>
const form = document.getElementById('landlordForm');
const saveStatus = document.getElementById('saveStatus');
const historySelect = document.getElementById('historySelect');
const historyTenant = document.getElementById('historyTenant');
let saveTimer = null;
let isLoading = false;

function setStatus(message, isError = false) {
    saveStatus.textContent = message;
    saveStatus.style.color = isError ? '#a30000' : '#0a6b2b';
}

function autoResizeTextareas() {
    document.querySelectorAll('.line-textarea').forEach(el => {
        el.style.height = '24px';
        el.style.height = Math.max(24, el.scrollHeight) + 'px';
    });
}

function serializeForm() {
    const fd = new FormData(form);
    return fd;
}

function queueSave() {
    if (isLoading) return;
    setStatus('Saving...');
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveForm, 450);
}

async function saveForm() {
    if (isLoading) return;
    try {
        const res = await fetch('?ajax=save', {
            method: 'POST',
            body: serializeForm()
        });
        const json = await res.json();
        if (!json.ok) throw new Error(json.message || 'Save failed');

        document.getElementById('record_id').value = json.id;
        document.getElementById('calc_balance_remaining').textContent = json.calc_balance_remaining;
        document.getElementById('calc_total_assistance_gap').textContent = json.calc_total_assistance_gap;
        setStatus('Auto-saved ' + json.updated_at);
        loadHistory(false);
    } catch (err) {
        setStatus(err.message || 'Save failed', true);
    }
}

async function loadHistory(focusDropdown = true) {
    try {
        const q = encodeURIComponent(historyTenant.value.trim());
        const res = await fetch('?ajax=history&tenant_name=' + q);
        const json = await res.json();
        if (!json.ok) throw new Error(json.message || 'History load failed');

        historySelect.innerHTML = '<option value="">Select prior record...</option>';
        json.records.forEach(row => {
            const opt = document.createElement('option');
            const stamp = row.updated_at ? row.updated_at : '';
            opt.value = row.id;
            opt.textContent = `${row.tenant_name || '(No tenant)'} - ${row.rental_street || 'No address'} - ${stamp}`;
            historySelect.appendChild(opt);
        });
        if (focusDropdown) historySelect.focus();
    } catch (err) {
        setStatus(err.message || 'Could not load history', true);
    }
}

async function loadSelectedRecord() {
    const id = historySelect.value;
    if (!id) return;
    try {
        isLoading = true;
        const res = await fetch('?ajax=load&id=' + encodeURIComponent(id));
        const json = await res.json();
        if (!json.ok || !json.record) throw new Error('Record not found');

        for (const [key, value] of Object.entries(json.record)) {
            const el = form.elements.namedItem(key);
            if (!el) continue;

            if (el instanceof RadioNodeList) {
                [...el].forEach(r => {
                    r.checked = (r.value === String(value));
                });
            } else {
                el.value = value ?? '';
            }
        }
        document.getElementById('record_id').value = json.record.id || '';
        document.getElementById('calc_balance_remaining').textContent = json.record.calc_balance_remaining || '0.00';
        document.getElementById('calc_total_assistance_gap').textContent = json.record.calc_total_assistance_gap || '0.00';
        autoResizeTextareas();
        setStatus('Record loaded');
    } catch (err) {
        setStatus(err.message || 'Load failed', true);
    } finally {
        isLoading = false;
    }
}

function newRecord() {
    window.location.href = window.location.pathname;
}

form.querySelectorAll('input, textarea, select').forEach(el => {
    el.addEventListener('input', () => {
        autoResizeTextareas();
        queueSave();
    });
    el.addEventListener('change', queueSave);
    el.addEventListener('blur', queueSave);
});

historyTenant.addEventListener('input', () => {
    clearTimeout(window.historyTimer);
    window.historyTimer = setTimeout(() => loadHistory(false), 350);
});

autoResizeTextareas();
loadHistory(false);
</script>
</body>
</html>
