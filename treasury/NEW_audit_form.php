<?php
declare(strict_types=1);

/**
 * Oxford House Financial Audit
 * Single-file PHP app with:
 * - Layout based on the uploaded audit form
 * - Auto-save to MySQL
 * - History dropdown by house name
 * - Auto-calculated totals
 * - Print button
 * - Save PDF button (browser print-to-PDF helper)
 * - Scanned audit upload support
 * - Officer-name dropdowns that auto-fill signature lines
 *
 * Uploaded form reference: Audit Form.pdf
 * Expected logo path: ../images/oxford_house_logo.png
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

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
   PATH SETUP
========================= */
$uploadDirRelative = 'uploads/audit_scans';
$uploadDirAbsolute = __DIR__ . '/' . $uploadDirRelative;

if (!is_dir($uploadDirAbsolute)) {
    @mkdir($uploadDirAbsolute, 0775, true);
}

/* =========================
   TABLE SETUP
========================= */
/* =========================
   SQL TABLE
=========================
CREATE TABLE `oxford_house_financial_audits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_house_name` (`house_name`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

$pdo->exec("
CREATE TABLE IF NOT EXISTS oxford_house_financial_audits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    completed_month VARCHAR(10) NOT NULL DEFAULT '',
    completed_day VARCHAR(10) NOT NULL DEFAULT '',
    completed_year VARCHAR(10) NOT NULL DEFAULT '',

    bank_ending_month VARCHAR(10) NOT NULL DEFAULT '',
    bank_ending_day VARCHAR(10) NOT NULL DEFAULT '',
    bank_ending_year VARCHAR(10) NOT NULL DEFAULT '',

    past_due_bills VARCHAR(50) NOT NULL DEFAULT '',
    savings_balance VARCHAR(50) NOT NULL DEFAULT '',
    outstanding_ees VARCHAR(50) NOT NULL DEFAULT '',
    bank_statement_ending_balance VARCHAR(50) NOT NULL DEFAULT '',

    deposits_total VARCHAR(50) NOT NULL DEFAULT '',
    checks_total VARCHAR(50) NOT NULL DEFAULT '',
    balance_after_audit VARCHAR(50) NOT NULL DEFAULT '',

    treasurer_name VARCHAR(255) NOT NULL DEFAULT '',
    comptroller_name VARCHAR(255) NOT NULL DEFAULT '',
    president_name VARCHAR(255) NOT NULL DEFAULT '',

    treasurer_signature VARCHAR(255) NOT NULL DEFAULT '',
    comptroller_signature VARCHAR(255) NOT NULL DEFAULT '',
    president_signature VARCHAR(255) NOT NULL DEFAULT '',

    checks_rows LONGTEXT DEFAULT NULL,
    deposits_rows LONGTEXT DEFAULT NULL,

    scan_original_name VARCHAR(255) NOT NULL DEFAULT '',
    scan_stored_name VARCHAR(255) NOT NULL DEFAULT '',
    scan_path VARCHAR(255) NOT NULL DEFAULT '',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_house_name (house_name),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function posted(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function normalizeMoneyString(string $value): string
{
    $clean = preg_replace('/[^0-9.\-]/', '', $value);
    if ($clean === '' || $clean === '-' || $clean === '.' || $clean === '-.') {
        return '';
    }
    return $clean;
}

function rowDataFromRequest(string $prefix): array
{
    $rows = [];
    $dates = $_POST[$prefix . '_date'] ?? [];
    $amounts = $_POST[$prefix . '_amount'] ?? [];
    $checks = $_POST[$prefix . '_check_no'] ?? [];
    $purposes = $_POST[$prefix . '_purpose'] ?? [];

    $max = max(
        is_array($dates) ? count($dates) : 0,
        is_array($amounts) ? count($amounts) : 0,
        is_array($checks) ? count($checks) : 0,
        is_array($purposes) ? count($purposes) : 0
    );

    for ($i = 0; $i < $max; $i++) {
        if ($prefix === 'checks') {
            $row = [
                'check_no' => trim((string)($checks[$i] ?? '')),
                'purpose' => trim((string)($purposes[$i] ?? '')),
                'date' => trim((string)($dates[$i] ?? '')),
                'amount' => trim((string)($amounts[$i] ?? '')),
            ];
        } else {
            $row = [
                'date' => trim((string)($dates[$i] ?? '')),
                'amount' => trim((string)($amounts[$i] ?? '')),
            ];
        }

        $hasContent = false;
        foreach ($row as $value) {
            if ($value !== '') {
                $hasContent = true;
                break;
            }
        }

        if ($hasContent) {
            $rows[] = $row;
        }
    }

    return $rows;
}

function emptyRows(string $type): array
{
    $rows = [];
    $count = $type === 'checks' ? 8 : 6;

    for ($i = 0; $i < $count; $i++) {
        if ($type === 'checks') {
            $rows[] = ['check_no' => '', 'purpose' => '', 'date' => '', 'amount' => ''];
        } else {
            $rows[] = ['date' => '', 'amount' => ''];
        }
    }

    return $rows;
}

function normalizeRows(mixed $json, string $type): array
{
    if (is_string($json) && $json !== '') {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $targetCount = $type === 'checks' ? 8 : 6;
            while (count($decoded) < $targetCount) {
                $decoded[] = $type === 'checks'
                    ? ['check_no' => '', 'purpose' => '', 'date' => '', 'amount' => '']
                    : ['date' => '', 'amount' => ''];
            }
            return array_slice($decoded, 0, $targetCount);
        }
    }

    return emptyRows($type);
}

function uploadedScanUrl(?array $record): string
{
    if (!$record || empty($record['scan_path'])) {
        return '';
    }
    return (string)$record['scan_path'];
}

function saveUploadedScan(array $file, string $uploadDirAbsolute, string $uploadDirRelative): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'ok' => true,
            'original_name' => '',
            'stored_name' => '',
            'path' => '',
            'message' => 'No file uploaded',
        ];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [
            'ok' => false,
            'message' => 'Upload failed with error code ' . (int)$file['error'],
        ];
    }

    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $tmp = (string)($file['tmp_name'] ?? '');
    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string)finfo_file($finfo, $tmp);
            finfo_close($finfo);
        }
    }

    if (!isset($allowed[$mime])) {
        return [
            'ok' => false,
            'message' => 'Only PDF, JPG, PNG, and WEBP files are allowed.',
        ];
    }

    $ext = $allowed[$mime];
    $originalName = (string)($file['name'] ?? 'scan.' . $ext);
    $storedName = 'audit_scan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destination = rtrim($uploadDirAbsolute, '/') . '/' . $storedName;

    if (!move_uploaded_file($tmp, $destination)) {
        return [
            'ok' => false,
            'message' => 'Could not move uploaded file.',
        ];
    }

    return [
        'ok' => true,
        'original_name' => $originalName,
        'stored_name' => $storedName,
        'path' => rtrim($uploadDirRelative, '/') . '/' . $storedName,
        'message' => 'Upload saved',
    ];
}

/* =========================
   AJAX: HISTORY
========================= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
    header('Content-Type: application/json; charset=utf-8');

    $houseName = trim((string)($_GET['house_name'] ?? ''));
    if ($houseName === '') {
        echo json_encode(['ok' => true, 'items' => []]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id, house_name, completed_month, completed_day, completed_year, updated_at
        FROM oxford_house_financial_audits
        WHERE house_name LIKE :house_name
        ORDER BY updated_at DESC, id DESC
        LIMIT 100
    ");
    $stmt->execute([
        ':house_name' => '%' . $houseName . '%',
    ]);

    echo json_encode([
        'ok' => true,
        'items' => $stmt->fetchAll(),
    ]);
    exit;
}

/* =========================
   AJAX: LOAD RECORD
========================= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
    header('Content-Type: application/json; charset=utf-8');

    $id = (int)($_GET['id'] ?? 0);
    if ($id < 1) {
        echo json_encode(['ok' => false, 'message' => 'Invalid record ID.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM oxford_house_financial_audits WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        echo json_encode(['ok' => false, 'message' => 'Record not found.']);
        exit;
    }

    $record['checks_rows'] = normalizeRows($record['checks_rows'] ?? null, 'checks');
    $record['deposits_rows'] = normalizeRows($record['deposits_rows'] ?? null, 'deposits');
    $record['scan_url'] = uploadedScanUrl($record);

    echo json_encode(['ok' => true, 'record' => $record]);
    exit;
}

/* =========================
   AJAX: AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'autosave')) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $id = (int)($_POST['record_id'] ?? 0);

        $houseName = posted('house_name');
        $completedMonth = posted('completed_month');
        $completedDay = posted('completed_day');
        $completedYear = posted('completed_year');

        $bankEndingMonth = posted('bank_ending_month');
        $bankEndingDay = posted('bank_ending_day');
        $bankEndingYear = posted('bank_ending_year');

        $pastDueBills = posted('past_due_bills');
        $savingsBalance = posted('savings_balance');
        $outstandingEes = posted('outstanding_ees');
        $bankStatementEndingBalance = posted('bank_statement_ending_balance');
        $depositsTotal = posted('deposits_total');
        $checksTotal = posted('checks_total');
        $balanceAfterAudit = posted('balance_after_audit');

        $treasurerName = posted('treasurer_name');
        $comptrollerName = posted('comptroller_name');
        $presidentName = posted('president_name');

        $treasurerSignature = posted('treasurer_signature');
        $comptrollerSignature = posted('comptroller_signature');
        $presidentSignature = posted('president_signature');

        $checksRows = json_encode(rowDataFromRequest('checks'), JSON_UNESCAPED_UNICODE);
        $depositsRows = json_encode(rowDataFromRequest('deposits'), JSON_UNESCAPED_UNICODE);

        $existing = null;
        if ($id > 0) {
            $stmtExisting = $pdo->prepare("SELECT scan_original_name, scan_stored_name, scan_path FROM oxford_house_financial_audits WHERE id = :id LIMIT 1");
            $stmtExisting->execute([':id' => $id]);
            $existing = $stmtExisting->fetch() ?: null;
        }

        $scanOriginalName = $existing['scan_original_name'] ?? '';
        $scanStoredName = $existing['scan_stored_name'] ?? '';
        $scanPath = $existing['scan_path'] ?? '';

        if (isset($_FILES['scan_file']) && ($_FILES['scan_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = saveUploadedScan($_FILES['scan_file'], $uploadDirAbsolute, $uploadDirRelative);
            if (!$upload['ok']) {
                echo json_encode(['ok' => false, 'message' => $upload['message']]);
                exit;
            }

            if (!empty($scanPath)) {
                $oldAbsolute = __DIR__ . '/' . ltrim($scanPath, '/');
                if (is_file($oldAbsolute)) {
                    @unlink($oldAbsolute);
                }
            }

            $scanOriginalName = $upload['original_name'];
            $scanStoredName = $upload['stored_name'];
            $scanPath = $upload['path'];
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE oxford_house_financial_audits SET
                    house_name = :house_name,
                    completed_month = :completed_month,
                    completed_day = :completed_day,
                    completed_year = :completed_year,
                    bank_ending_month = :bank_ending_month,
                    bank_ending_day = :bank_ending_day,
                    bank_ending_year = :bank_ending_year,
                    past_due_bills = :past_due_bills,
                    savings_balance = :savings_balance,
                    outstanding_ees = :outstanding_ees,
                    bank_statement_ending_balance = :bank_statement_ending_balance,
                    deposits_total = :deposits_total,
                    checks_total = :checks_total,
                    balance_after_audit = :balance_after_audit,
                    treasurer_name = :treasurer_name,
                    comptroller_name = :comptroller_name,
                    president_name = :president_name,
                    treasurer_signature = :treasurer_signature,
                    comptroller_signature = :comptroller_signature,
                    president_signature = :president_signature,
                    checks_rows = :checks_rows,
                    deposits_rows = :deposits_rows,
                    scan_original_name = :scan_original_name,
                    scan_stored_name = :scan_stored_name,
                    scan_path = :scan_path
                WHERE id = :id
            ");
            $stmt->execute([
                ':house_name' => $houseName,
                ':completed_month' => $completedMonth,
                ':completed_day' => $completedDay,
                ':completed_year' => $completedYear,
                ':bank_ending_month' => $bankEndingMonth,
                ':bank_ending_day' => $bankEndingDay,
                ':bank_ending_year' => $bankEndingYear,
                ':past_due_bills' => $pastDueBills,
                ':savings_balance' => $savingsBalance,
                ':outstanding_ees' => $outstandingEes,
                ':bank_statement_ending_balance' => $bankStatementEndingBalance,
                ':deposits_total' => $depositsTotal,
                ':checks_total' => $checksTotal,
                ':balance_after_audit' => $balanceAfterAudit,
                ':treasurer_name' => $treasurerName,
                ':comptroller_name' => $comptrollerName,
                ':president_name' => $presidentName,
                ':treasurer_signature' => $treasurerSignature,
                ':comptroller_signature' => $comptrollerSignature,
                ':president_signature' => $presidentSignature,
                ':checks_rows' => $checksRows,
                ':deposits_rows' => $depositsRows,
                ':scan_original_name' => $scanOriginalName,
                ':scan_stored_name' => $scanStoredName,
                ':scan_path' => $scanPath,
                ':id' => $id,
            ]);

            echo json_encode([
                'ok' => true,
                'record_id' => $id,
                'message' => 'Updated',
                'scan_url' => $scanPath,
                'scan_original_name' => $scanOriginalName,
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO oxford_house_financial_audits (
                house_name,
                completed_month,
                completed_day,
                completed_year,
                bank_ending_month,
                bank_ending_day,
                bank_ending_year,
                past_due_bills,
                savings_balance,
                outstanding_ees,
                bank_statement_ending_balance,
                deposits_total,
                checks_total,
                balance_after_audit,
                treasurer_name,
                comptroller_name,
                president_name,
                treasurer_signature,
                comptroller_signature,
                president_signature,
                checks_rows,
                deposits_rows,
                scan_original_name,
                scan_stored_name,
                scan_path
            ) VALUES (
                :house_name,
                :completed_month,
                :completed_day,
                :completed_year,
                :bank_ending_month,
                :bank_ending_day,
                :bank_ending_year,
                :past_due_bills,
                :savings_balance,
                :outstanding_ees,
                :bank_statement_ending_balance,
                :deposits_total,
                :checks_total,
                :balance_after_audit,
                :treasurer_name,
                :comptroller_name,
                :president_name,
                :treasurer_signature,
                :comptroller_signature,
                :president_signature,
                :checks_rows,
                :deposits_rows,
                :scan_original_name,
                :scan_stored_name,
                :scan_path
            )
        ");
        $stmt->execute([
            ':house_name' => $houseName,
            ':completed_month' => $completedMonth,
            ':completed_day' => $completedDay,
            ':completed_year' => $completedYear,
            ':bank_ending_month' => $bankEndingMonth,
            ':bank_ending_day' => $bankEndingDay,
            ':bank_ending_year' => $bankEndingYear,
            ':past_due_bills' => $pastDueBills,
            ':savings_balance' => $savingsBalance,
            ':outstanding_ees' => $outstandingEes,
            ':bank_statement_ending_balance' => $bankStatementEndingBalance,
            ':deposits_total' => $depositsTotal,
            ':checks_total' => $checksTotal,
            ':balance_after_audit' => $balanceAfterAudit,
            ':treasurer_name' => $treasurerName,
            ':comptroller_name' => $comptrollerName,
            ':president_name' => $presidentName,
            ':treasurer_signature' => $treasurerSignature,
            ':comptroller_signature' => $comptrollerSignature,
            ':president_signature' => $presidentSignature,
            ':checks_rows' => $checksRows,
            ':deposits_rows' => $depositsRows,
            ':scan_original_name' => $scanOriginalName,
            ':scan_stored_name' => $scanStoredName,
            ':scan_path' => $scanPath,
        ]);

        echo json_encode([
            'ok' => true,
            'record_id' => (int)$pdo->lastInsertId(),
            'message' => 'Created',
            'scan_url' => $scanPath,
            'scan_original_name' => $scanOriginalName,
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
            'ok' => false,
            'message' => 'Save failed: ' . $e->getMessage(),
        ]);
        exit;
    }
}

$checksRows = emptyRows('checks');
$depositsRows = emptyRows('deposits');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Financial Audit</title>
    <style>
        @page {
            size: Letter;
            margin: 0.4in;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 18px;
            background: #f1f1f1;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
        }

        .toolbar {
            max-width: 1050px;
            margin: 0 auto 12px auto;
            background: #fff;
            border: 1px solid #d8d8d8;
            padding: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 14px;
            align-items: end;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .toolbar-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 180px;
        }

        .toolbar label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .toolbar input,
        .toolbar select,
        .toolbar button {
            height: 36px;
            border: 1px solid #999;
            background: #fff;
            padding: 6px 10px;
            font-size: 14px;
        }

        .toolbar button {
            cursor: pointer;
            font-weight: 700;
        }

        .status {
            font-size: 13px;
            font-weight: 700;
            color: #444;
            padding-bottom: 8px;
            min-width: 130px;
        }

        .page {
            max-width: 1050px;
            margin: 0 auto;
            background: #fff;
            padding: 22px 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        .header {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 14px;
            align-items: start;
            margin-bottom: 6px;
        }

        .logo-wrap {
            padding-top: 4px;
        }

        .logo-wrap img {
            width: 78px;
            height: auto;
            display: block;
        }

        .header-main {
            text-align: center;
        }

        .header-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 800;
            font-size: 28px;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .house-fill {
            display: inline-block;
            min-width: 240px;
            border-bottom: 2px solid #111;
            height: 28px;
            position: relative;
        }

        .house-fill input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            background: transparent;
            font-size: 20px;
            font-weight: 700;
            text-align: left;
            padding: 0 4px;
        }

        .subhead {
            margin-top: 6px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .date-completed {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 700;
            margin: 8px 0 12px 0;
        }

        .datebox {
            width: 46px;
            border: 0;
            border-bottom: 1px solid #111;
            text-align: center;
            height: 26px;
            font-size: 14px;
            background: transparent;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 22px;
            margin-bottom: 12px;
        }

        .field-block {
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: end;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .money-line,
        .text-line {
            border: 0;
            border-bottom: 1px solid #111;
            height: 26px;
            background: transparent;
            font-size: 14px;
            padding: 0 4px;
            width: 100%;
        }

        .bank-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .instruction-box {
            border: 1px solid #111;
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.35;
            margin: 10px 0 16px 0;
        }

        .instruction-box ol {
            margin: 0;
            padding-left: 18px;
        }

        .section-title {
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            margin: 12px 0 6px 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid #111;
            padding: 4px 5px;
            font-size: 13px;
            height: 30px;
        }

        th {
            font-weight: 800;
            text-align: center;
        }

        td input {
            width: 100%;
            border: 0;
            background: transparent;
            height: 20px;
            font-size: 13px;
            padding: 0 3px;
        }

        .totals-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
            font-weight: 700;
        }

        .small-money {
            width: 120px;
            border: 0;
            border-bottom: 1px solid #111;
            background: transparent;
            height: 24px;
            font-size: 14px;
            text-align: right;
            padding: 0 4px;
        }

        .math-box {
            display: grid;
            grid-template-columns: 1.5fr 30px 1.5fr 30px 1.5fr 30px 1.5fr;
            gap: 4px;
            align-items: end;
            margin: 18px 0 14px 0;
        }

        .math-col {
            text-align: center;
        }

        .math-col label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .math-col input {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #111;
            background: transparent;
            height: 26px;
            text-align: right;
            padding: 0 4px;
            font-size: 14px;
        }

        .math-symbol {
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            padding-bottom: 2px;
        }

        .officer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin: 6px 0 10px 0;
        }

        .officer-card {
            border: 1px solid #111;
            padding: 8px 8px 10px 8px;
        }

        .officer-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 8px;
        }

        .officer-card label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .officer-card select,
        .officer-card input {
            width: 100%;
            height: 32px;
            border: 1px solid #111;
            background: #fff;
            padding: 4px 8px;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 18px;
            margin-top: 10px;
        }

        .signature-block {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
        }

        .signature-block input {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #111;
            height: 28px;
            background: transparent;
            margin-bottom: 4px;
            font-size: 14px;
            text-align: center;
        }

        .scan-box {
            border: 1px solid #111;
            margin-top: 16px;
            padding: 10px;
        }

        .scan-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .scan-row {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 12px;
            align-items: start;
        }

        .scan-row input[type="file"] {
            width: 100%;
        }

        .scan-meta {
            font-size: 13px;
            line-height: 1.4;
        }

        .scan-meta a {
            color: #0a58ca;
            text-decoration: none;
            font-weight: 700;
        }

        .scan-preview {
            margin-top: 10px;
            border: 1px solid #ccc;
            background: #fafafa;
            padding: 8px;
            display: none;
        }

        .scan-preview img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .scan-preview iframe {
            width: 100%;
            height: 420px;
            border: 0;
            display: block;
        }

        input:focus,
        select:focus,
        button:focus {
            outline: 2px solid rgba(0,0,0,.15);
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar,
            .scan-box {
                display: none !important;
            }

            .page {
                box-shadow: none;
                max-width: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-group">
            <label for="historyHouseSearch">History Search by House Name</label>
            <input type="text" id="historyHouseSearch" placeholder="Enter house name">
        </div>

        <div class="toolbar-group" style="min-width:300px;">
            <label for="historySelect">History Records</label>
            <select id="historySelect">
                <option value="">Select saved audit...</option>
            </select>
        </div>

        <div class="toolbar-group" style="min-width:120px;">
            <label>&nbsp;</label>
            <button type="button" id="printBtn">Print</button>
        </div>

        <div class="toolbar-group" style="min-width:140px;">
            <label>&nbsp;</label>
            <button type="button" id="savePdfBtn">Save PDF</button>
        </div>

        <div class="status" id="saveStatus">Ready</div>
    </div>

    <div class="page">
        <form id="auditForm" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="action" value="autosave">
            <input type="hidden" name="record_id" id="record_id" value="0">

            <div class="header">
                <div class="logo-wrap">
                    <img src="../images/oxford_house_logo.png" alt="Oxford House Logo">
                </div>
                <div class="header-main">
                    <div class="header-line">
                        <span>OXFORD HOUSE -</span>
                        <span class="house-fill"><input type="text" name="house_name" id="house_name"></span>
                    </div>
                    <div class="subhead">FINANCIAL AUDIT</div>
                </div>
            </div>

            <div class="date-completed">
                <span>DATE COMPLETED:</span>
                <input type="text" class="datebox" name="completed_month" id="completed_month">
                <span>/</span>
                <input type="text" class="datebox" name="completed_day" id="completed_day">
                <span>/</span>
                <input type="text" class="datebox" name="completed_year" id="completed_year">
            </div>

            <div class="top-grid">
                <div class="field-block">
                    <span>Total of past due bills</span>
                    <input type="text" class="money-line calc-trigger" name="past_due_bills" id="past_due_bills">
                </div>

                <div class="field-block">
                    <span>BANK STATEMENT ENDING DATE</span>
                    <div class="bank-date">
                        <input type="text" class="datebox" name="bank_ending_month" id="bank_ending_month">
                        <span>/</span>
                        <input type="text" class="datebox" name="bank_ending_day" id="bank_ending_day">
                        <span>/</span>
                        <input type="text" class="datebox" name="bank_ending_year" id="bank_ending_year">
                    </div>
                </div>

                <div class="field-block">
                    <span>Savings Account balance</span>
                    <input type="text" class="money-line calc-trigger" name="savings_balance" id="savings_balance">
                </div>

                <div class="field-block">
                    <span>BANK STATEMENT ENDING BALANCE</span>
                    <input type="text" class="money-line calc-trigger" name="bank_statement_ending_balance" id="bank_statement_ending_balance">
                </div>

                <div class="field-block">
                    <span>Total outstanding EES</span>
                    <input type="text" class="money-line calc-trigger" name="outstanding_ees" id="outstanding_ees">
                </div>

                <div></div>
            </div>

            <div class="instruction-box">
                <ol>
                    <li>The Treasurer, Comptroller, &amp; President do the audit together.</li>
                    <li>Use the bank statement, checkbook, Financial Status Report, and Meeting Minutes for references.</li>
                    <li>Document all deposits and checks that are not listed on the most recent bank statement.</li>
                    <li>Highlight or circle the check numbers on the check stubs for all checks that are listed on the bank statement.</li>
                    <li>Count the checks in the checkbook, by check number, to ensure no checks are missing.</li>
                    <li>The final audited balance should match the ending balance on the check stub of the last check written.</li>
                    <li>If fraud, theft, or embezzlement has occured, immediately notify the house, OHI staff, the bank, and the police.</li>
                </ol>
            </div>

            <div class="section-title">CHECKS NOT ON STATEMENT</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:14%;">Check #</th>
                        <th style="width:46%;">To Whom / Purpose</th>
                        <th style="width:18%;">Date</th>
                        <th style="width:22%;">Amount $</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < 8; $i++): ?>
                        <tr>
                            <td><input type="text" name="checks_check_no[]"></td>
                            <td><input type="text" name="checks_purpose[]"></td>
                            <td><input type="text" name="checks_date[]"></td>
                            <td><input type="text" name="checks_amount[]" class="check-amount calc-trigger"></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div class="totals-row">
                <span>TOTAL: $</span>
                <input type="text" class="small-money" name="checks_total" id="checks_total" readonly>
            </div>

            <div class="section-title">DEPOSITS NOT ON STATEMENT</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:70%;">Date</th>
                        <th style="width:30%;">Amount $</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <tr>
                            <td><input type="text" name="deposits_date[]"></td>
                            <td><input type="text" name="deposits_amount[]" class="deposit-amount calc-trigger"></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div class="totals-row">
                <span>TOTAL: $</span>
                <input type="text" class="small-money" name="deposits_total" id="deposits_total" readonly>
            </div>

            <div class="math-box">
                <div class="math-col">
                    <label>Bank Statement Ending Balance</label>
                    <input type="text" id="math_bank_balance" readonly>
                </div>
                <div class="math-symbol">+</div>
                <div class="math-col">
                    <label>Deposits Total</label>
                    <input type="text" id="math_deposits_total" readonly>
                </div>
                <div class="math-symbol">-</div>
                <div class="math-col">
                    <label>Checks Total</label>
                    <input type="text" id="math_checks_total" readonly>
                </div>
                <div class="math-symbol">=</div>
                <div class="math-col">
                    <label>Balance After Audit</label>
                    <input type="text" name="balance_after_audit" id="balance_after_audit" readonly>
                </div>
            </div>

            <div class="officer-grid">
                <div class="officer-card">
                    <div class="officer-title">Treasurer</div>
                    <label for="treasurer_name">Officer Name</label>
                    <select name="treasurer_name" id="treasurer_name" class="signature-source">
                        <option value="">Select Treasurer</option>
                        <option value="">Custom</option>
                        <option value="John Doe">John Doe</option>
                        <option value="Jose Davila">Jose Davila</option>
                        <option value="Charles">Charles</option>
                        <option value="Frank">Frank</option>
                        <option value="Todd">Todd</option>
                    </select>
                    <label for="treasurer_signature">Signature Line</label>
                    <input type="text" name="treasurer_signature" id="treasurer_signature" placeholder="Auto-fills from officer name">
                </div>

                <div class="officer-card">
                    <div class="officer-title">Comptroller</div>
                    <label for="comptroller_name">Officer Name</label>
                    <select name="comptroller_name" id="comptroller_name" class="signature-source">
                        <option value="">Select Comptroller</option>
                        <option value="">Custom</option>
                        <option value="John Doe">John Doe</option>
                        <option value="Jose Davila">Jose Davila</option>
                        <option value="Charles">Charles</option>
                        <option value="Frank">Frank</option>
                        <option value="Todd">Todd</option>
                    </select>
                    <label for="comptroller_signature">Signature Line</label>
                    <input type="text" name="comptroller_signature" id="comptroller_signature" placeholder="Auto-fills from officer name">
                </div>

                <div class="officer-card">
                    <div class="officer-title">President</div>
                    <label for="president_name">Officer Name</label>
                    <select name="president_name" id="president_name" class="signature-source">
                        <option value="">Select President</option>
                        <option value="">Custom</option>
                        <option value="John Doe">John Doe</option>
                        <option value="Jose Davila">Jose Davila</option>
                        <option value="Charles">Charles</option>
                        <option value="Frank">Frank</option>
                        <option value="Todd">Todd</option>
                    </select>
                    <label for="president_signature">Signature Line</label>
                    <input type="text" name="president_signature" id="president_signature" placeholder="Auto-fills from officer name">
                </div>
            </div>

            <div class="signature-row">
                <div class="signature-block">
                    <input type="text" value="" readonly>
                    <div>Treasurer Signature</div>
                </div>
                <div class="signature-block">
                    <input type="text" value="" readonly>
                    <div>Comptroller Signature</div>
                </div>
                <div class="signature-block">
                    <input type="text" value="" readonly>
                    <div>President Signature</div>
                </div>
            </div>

            <div class="scan-box">
                <div class="scan-title">Scanned Audit Upload</div>
                <div class="scan-row">
                    <div>
                        <input type="file" name="scan_file" id="scan_file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="scan-meta">
                        <div id="scanFileStatus">No scan uploaded.</div>
                        <div id="scanFileLinkWrap" style="margin-top:6px;"></div>
                    </div>
                </div>
                <div class="scan-preview" id="scanPreview"></div>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('auditForm');
        const saveStatus = document.getElementById('saveStatus');
        const historyHouseSearch = document.getElementById('historyHouseSearch');
        const historySelect = document.getElementById('historySelect');
        const recordIdInput = document.getElementById('record_id');
        const printBtn = document.getElementById('printBtn');
        const savePdfBtn = document.getElementById('savePdfBtn');
        const scanFileInput = document.getElementById('scan_file');
        const scanFileStatus = document.getElementById('scanFileStatus');
        const scanFileLinkWrap = document.getElementById('scanFileLinkWrap');
        const scanPreview = document.getElementById('scanPreview');

        let saveTimer = null;
        let historyTimer = null;
        let isSaving = false;

        const officerMap = {
            treasurer_name: 'treasurer_signature',
            comptroller_name: 'comptroller_signature',
            president_name: 'president_signature'
        };

        function setStatus(message) {
            saveStatus.textContent = message;
        }

        function parseMoney(value) {
            const cleaned = String(value || '').replace(/[^0-9.-]/g, '');
            const num = parseFloat(cleaned);
            return Number.isFinite(num) ? num : 0;
        }

        function formatMoney(value) {
            const num = Number(value);
            return Number.isFinite(num) ? num.toFixed(2) : '0.00';
        }

        function calcTotals() {
            let checksTotal = 0;
            document.querySelectorAll('.check-amount').forEach(el => {
                checksTotal += parseMoney(el.value);
            });

            let depositsTotal = 0;
            document.querySelectorAll('.deposit-amount').forEach(el => {
                depositsTotal += parseMoney(el.value);
            });

            const bankBalance = parseMoney(document.getElementById('bank_statement_ending_balance').value);
            const balanceAfterAudit = bankBalance + depositsTotal - checksTotal;

            document.getElementById('checks_total').value = formatMoney(checksTotal);
            document.getElementById('deposits_total').value = formatMoney(depositsTotal);
            document.getElementById('balance_after_audit').value = formatMoney(balanceAfterAudit);
            document.getElementById('math_bank_balance').value = formatMoney(bankBalance);
            document.getElementById('math_deposits_total').value = formatMoney(depositsTotal);
            document.getElementById('math_checks_total').value = formatMoney(checksTotal);
        }

        function syncSignatureFromOfficer(selectId, inputId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);
            if (!select || !input) return;
            if (select.value && (!input.value || input.dataset.autofilled === '1')) {
                input.value = select.value;
                input.dataset.autofilled = '1';
            }
        }

        Object.entries(officerMap).forEach(([selectId, inputId]) => {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);
            if (select) {
                select.addEventListener('change', () => {
                    syncSignatureFromOfficer(selectId, inputId);
                    queueSave();
                });
            }
            if (input) {
                input.addEventListener('input', () => {
                    input.dataset.autofilled = '0';
                });
            }
        });

        function updateScanDisplay(url, originalName) {
            if (!url) {
                scanFileStatus.textContent = 'No scan uploaded.';
                scanFileLinkWrap.innerHTML = '';
                scanPreview.style.display = 'none';
                scanPreview.innerHTML = '';
                return;
            }

            scanFileStatus.textContent = originalName ? `Uploaded: ${originalName}` : 'Scan uploaded.';
            scanFileLinkWrap.innerHTML = `<a href="${url}" target="_blank" rel="noopener">Open uploaded scan</a>`;

            const lower = url.toLowerCase();
            scanPreview.style.display = 'block';
            if (lower.endsWith('.pdf')) {
                scanPreview.innerHTML = `<iframe src="${url}"></iframe>`;
            } else {
                scanPreview.innerHTML = `<img src="${url}" alt="Uploaded audit scan preview">`;
            }
        }

        async function saveForm() {
            if (isSaving) return;
            isSaving = true;
            setStatus('Saving...');

            try {
                calcTotals();
                const formData = new FormData(form);

                const response = await fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.ok) {
                    if (data.record_id) {
                        recordIdInput.value = data.record_id;
                    }
                    updateScanDisplay(data.scan_url || '', data.scan_original_name || '');
                    setStatus('Saved');
                } else {
                    setStatus(data.message || 'Save failed');
                }
            } catch (error) {
                setStatus('Save error');
            } finally {
                isSaving = false;
            }
        }

        function queueSave() {
            calcTotals();
            setStatus('Pending changes...');
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveForm, 700);
        }

        form.querySelectorAll('input, select').forEach(el => {
            if (el.id !== 'record_id' && el.type !== 'file') {
                el.addEventListener('input', queueSave);
                el.addEventListener('change', queueSave);
            }
        });

        scanFileInput.addEventListener('change', () => {
            const file = scanFileInput.files && scanFileInput.files[0] ? scanFileInput.files[0] : null;
            if (file) {
                scanFileStatus.textContent = `Selected: ${file.name}`;
            }
            queueSave();
        });

        async function loadHistory(houseName) {
            if (!houseName.trim()) {
                historySelect.innerHTML = '<option value="">Select saved audit...</option>';
                return;
            }

            historySelect.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`${window.location.pathname}?ajax=history&house_name=${encodeURIComponent(houseName)}`);
                const data = await response.json();

                if (!data.ok) {
                    historySelect.innerHTML = '<option value="">No records found</option>';
                    return;
                }

                let html = '<option value="">Select saved audit...</option>';
                for (const item of data.items) {
                    const completedDate = [item.completed_month, item.completed_day, item.completed_year].filter(Boolean).join('/');
                    const label = `${item.house_name || 'Unnamed House'}${completedDate ? ' - ' + completedDate : ''} - ${item.updated_at}`;
                    html += `<option value="${item.id}">${label}</option>`;
                }
                historySelect.innerHTML = html;
            } catch (error) {
                historySelect.innerHTML = '<option value="">Error loading history</option>';
            }
        }

        historyHouseSearch.addEventListener('input', () => {
            clearTimeout(historyTimer);
            historyTimer = setTimeout(() => loadHistory(historyHouseSearch.value), 350);
        });

        function setValue(id, value) {
            const el = document.getElementById(id);
            if (el) {
                el.value = value ?? '';
            }
        }

        async function loadRecord(id) {
            if (!id) return;
            setStatus('Loading...');

            try {
                const response = await fetch(`${window.location.pathname}?ajax=load&id=${encodeURIComponent(id)}`);
                const data = await response.json();

                if (!data.ok || !data.record) {
                    setStatus(data.message || 'Load failed');
                    return;
                }

                const r = data.record;
                setValue('record_id', r.id || 0);
                setValue('house_name', r.house_name || '');
                setValue('completed_month', r.completed_month || '');
                setValue('completed_day', r.completed_day || '');
                setValue('completed_year', r.completed_year || '');
                setValue('bank_ending_month', r.bank_ending_month || '');
                setValue('bank_ending_day', r.bank_ending_day || '');
                setValue('bank_ending_year', r.bank_ending_year || '');
                setValue('past_due_bills', r.past_due_bills || '');
                setValue('savings_balance', r.savings_balance || '');
                setValue('outstanding_ees', r.outstanding_ees || '');
                setValue('bank_statement_ending_balance', r.bank_statement_ending_balance || '');
                setValue('checks_total', r.checks_total || '');
                setValue('deposits_total', r.deposits_total || '');
                setValue('balance_after_audit', r.balance_after_audit || '');
                setValue('treasurer_name', r.treasurer_name || '');
                setValue('comptroller_name', r.comptroller_name || '');
                setValue('president_name', r.president_name || '');
                setValue('treasurer_signature', r.treasurer_signature || '');
                setValue('comptroller_signature', r.comptroller_signature || '');
                setValue('president_signature', r.president_signature || '');

                const checkNos = document.querySelectorAll('input[name="checks_check_no[]"]');
                const checkPurposes = document.querySelectorAll('input[name="checks_purpose[]"]');
                const checkDates = document.querySelectorAll('input[name="checks_date[]"]');
                const checkAmounts = document.querySelectorAll('input[name="checks_amount[]"]');

                for (let i = 0; i < checkNos.length; i++) {
                    const row = r.checks_rows[i] || {};
                    checkNos[i].value = row.check_no || '';
                    checkPurposes[i].value = row.purpose || '';
                    checkDates[i].value = row.date || '';
                    checkAmounts[i].value = row.amount || '';
                }

                const depositDates = document.querySelectorAll('input[name="deposits_date[]"]');
                const depositAmounts = document.querySelectorAll('input[name="deposits_amount[]"]');

                for (let i = 0; i < depositDates.length; i++) {
                    const row = r.deposits_rows[i] || {};
                    depositDates[i].value = row.date || '';
                    depositAmounts[i].value = row.amount || '';
                }

                updateScanDisplay(r.scan_url || '', r.scan_original_name || '');
                calcTotals();
                setStatus('Loaded');
            } catch (error) {
                setStatus('Load error');
            }
        }

        historySelect.addEventListener('change', () => {
            if (historySelect.value) {
                loadRecord(historySelect.value);
            }
        });

        printBtn.addEventListener('click', () => {
            window.print();
        });

        savePdfBtn.addEventListener('click', () => {
            alert('Your browser print window will open. Choose “Save as PDF” as the printer destination.');
            window.print();
        });

        calcTotals();
    </script>
</body>
</html>

