<?php
declare(strict_types=1);

/**
 * Oxford House Member Financial Contract
 * - Single-file PHP app
 * - MySQL save/update
 * - Autosave
 * - History uses a true dropdown select
 * - Reload and edit old sheets
 * - Upload scanned contract copies
 * - If a saved history record has an uploaded copy, it shows the uploaded copy instead of the form
 * - Uploaded scanned copy cannot be replaced or deleted once one exists
 * - Scanned copy can be stamped as CONTRACT FULFILLED or VOIDED
 * - Stamp action is password protected
 * - Stamp text is shown in red over the contract preview
 */

require_once __DIR__ . '/../extras/master_config.php';

$uploadDir = __DIR__ . '/uploads/contracts';
$uploadWebPath = 'uploads/contracts';
$logoPath = '../images/oxford_house_logo.png';

/**
 * Change this password to whatever you want.
 * This password is required before a red stamp can be applied.
 */
$stampPassword = oxford_get_contract_stamp_default_password();

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
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
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$pdo->exec("
    CREATE TABLE IF NOT EXISTS oxford_member_financial_contracts (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        member_name VARCHAR(255) NOT NULL DEFAULT '',
        house_name VARCHAR(255) NOT NULL DEFAULT '',
        contract_date VARCHAR(100) NOT NULL DEFAULT '',
        contract_length TEXT NULL,
        total_amount_owed DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        term_1 TEXT NULL,
        term_2 TEXT NULL,
        term_3 TEXT NULL,
        term_4 TEXT NULL,
        acknowledgement_name VARCHAR(255) NOT NULL DEFAULT '',
        signature_name VARCHAR(255) NOT NULL DEFAULT '',
        signature_date VARCHAR(100) NOT NULL DEFAULT '',
        president_name VARCHAR(255) NOT NULL DEFAULT '',
        treasurer_name VARCHAR(255) NOT NULL DEFAULT '',
        coordinator_name VARCHAR(255) NOT NULL DEFAULT '',
        member_1_name VARCHAR(255) NOT NULL DEFAULT '',
        member_2_name VARCHAR(255) NOT NULL DEFAULT '',
        secretary_name VARCHAR(255) NOT NULL DEFAULT '',
        comptroller_name VARCHAR(255) NOT NULL DEFAULT '',
        hs_representative_name VARCHAR(255) NOT NULL DEFAULT '',
        member_3_name VARCHAR(255) NOT NULL DEFAULT '',
        member_4_name VARCHAR(255) NOT NULL DEFAULT '',
        scanned_contract VARCHAR(500) NOT NULL DEFAULT '',
        contract_stamp VARCHAR(50) NOT NULL DEFAULT '',
        contract_stamp_at DATETIME NULL DEFAULT NULL,
        contract_stamp_by_ip VARCHAR(64) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_member_name (member_name),
        KEY idx_house_name (house_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function moneyToFloat(string $value): float
{
    $clean = preg_replace('/[^0-9.\-]/', '', $value);
    return is_numeric($clean) ? (float)$clean : 0.00;
}

function moneyForInput(mixed $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function isPreviewableImage(string $path): bool
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
}

function isPdfFile(string $path): bool
{
    return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
}

function getClientIpAddress(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $value = trim((string)$_SERVER[$key]);
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = explode(',', $value);
                return trim((string)$parts[0]);
            }
            return $value;
        }
    }

    return '';
}

function saveContract(PDO $pdo, array $data, ?array $file, string $uploadDir, string $uploadWebPath): array
{
    $id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : 0;

    $memberName = trim((string)($data['member_name'] ?? ''));
    $houseName = trim((string)($data['house_name'] ?? ''));
    $contractDate = trim((string)($data['contract_date'] ?? ''));

    $existingFile = trim((string)($data['existing_scanned_contract'] ?? ''));
    $existingStamp = trim((string)($data['existing_contract_stamp'] ?? ''));
    $scannedContract = $existingFile;

    /**
     * Once a scanned contract exists, it cannot be replaced or deleted.
     * A new upload is only accepted when there is no existing scanned copy yet.
     */
    if ($existingFile === '' && $file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name']) && (int)$file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid uploaded file type. Allowed: pdf, jpg, jpeg, png, webp'];
        }

        $safeMember = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $memberName ?: 'member');
        $safeDate = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $contractDate ?: date('Y-m-d'));
        $filename = $safeMember . '_' . $safeDate . '_' . time() . '.' . $ext;
        $destination = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to upload scanned contract file.'];
        }

        $scannedContract = rtrim($uploadWebPath, '/\\') . '/' . $filename;
    }

    $payload = [
        'member_name' => $memberName,
        'house_name' => $houseName,
        'contract_date' => $contractDate,
        'contract_length' => trim((string)($data['contract_length'] ?? '')),
        'total_amount_owed' => moneyToFloat((string)($data['total_amount_owed'] ?? '0')),
        'term_1' => trim((string)($data['term_1'] ?? '')),
        'term_2' => trim((string)($data['term_2'] ?? '')),
        'term_3' => trim((string)($data['term_3'] ?? '')),
        'term_4' => trim((string)($data['term_4'] ?? '')),
        'acknowledgement_name' => trim((string)($data['acknowledgement_name'] ?? '')),
        'signature_name' => trim((string)($data['signature_name'] ?? '')),
        'signature_date' => trim((string)($data['signature_date'] ?? '')),
        'president_name' => trim((string)($data['president_name'] ?? '')),
        'treasurer_name' => trim((string)($data['treasurer_name'] ?? '')),
        'coordinator_name' => trim((string)($data['coordinator_name'] ?? '')),
        'member_1_name' => trim((string)($data['member_1_name'] ?? '')),
        'member_2_name' => trim((string)($data['member_2_name'] ?? '')),
        'secretary_name' => trim((string)($data['secretary_name'] ?? '')),
        'comptroller_name' => trim((string)($data['comptroller_name'] ?? '')),
        'hs_representative_name' => trim((string)($data['hs_representative_name'] ?? '')),
        'member_3_name' => trim((string)($data['member_3_name'] ?? '')),
        'member_4_name' => trim((string)($data['member_4_name'] ?? '')),
        'scanned_contract' => $scannedContract,
        'contract_stamp' => $existingStamp,
    ];

    if ($id > 0) {
        $sql = "UPDATE oxford_member_financial_contracts SET
            member_name = :member_name,
            house_name = :house_name,
            contract_date = :contract_date,
            contract_length = :contract_length,
            total_amount_owed = :total_amount_owed,
            term_1 = :term_1,
            term_2 = :term_2,
            term_3 = :term_3,
            term_4 = :term_4,
            acknowledgement_name = :acknowledgement_name,
            signature_name = :signature_name,
            signature_date = :signature_date,
            president_name = :president_name,
            treasurer_name = :treasurer_name,
            coordinator_name = :coordinator_name,
            member_1_name = :member_1_name,
            member_2_name = :member_2_name,
            secretary_name = :secretary_name,
            comptroller_name = :comptroller_name,
            hs_representative_name = :hs_representative_name,
            member_3_name = :member_3_name,
            member_4_name = :member_4_name,
            scanned_contract = :scanned_contract,
            contract_stamp = :contract_stamp
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $payload['id'] = $id;
        $stmt->execute($payload);
        return ['success' => true, 'message' => 'Sheet updated successfully.', 'id' => $id];
    }

    $checkStmt = $pdo->prepare("
        SELECT id, scanned_contract, contract_stamp
        FROM oxford_member_financial_contracts
        WHERE member_name = :member_name
          AND contract_date = :contract_date
          AND house_name = :house_name
        LIMIT 1
    ");
    $checkStmt->execute([
        'member_name' => $payload['member_name'],
        'contract_date' => $payload['contract_date'],
        'house_name' => $payload['house_name'],
    ]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        $id = (int)$existing['id'];

        if (!empty($existing['scanned_contract'])) {
            $payload['scanned_contract'] = (string)$existing['scanned_contract'];
        }

        if (!empty($existing['contract_stamp'])) {
            $payload['contract_stamp'] = (string)$existing['contract_stamp'];
        }

        $sql = "UPDATE oxford_member_financial_contracts SET
            contract_length = :contract_length,
            total_amount_owed = :total_amount_owed,
            term_1 = :term_1,
            term_2 = :term_2,
            term_3 = :term_3,
            term_4 = :term_4,
            acknowledgement_name = :acknowledgement_name,
            signature_name = :signature_name,
            signature_date = :signature_date,
            president_name = :president_name,
            treasurer_name = :treasurer_name,
            coordinator_name = :coordinator_name,
            member_1_name = :member_1_name,
            member_2_name = :member_2_name,
            secretary_name = :secretary_name,
            comptroller_name = :comptroller_name,
            hs_representative_name = :hs_representative_name,
            member_3_name = :member_3_name,
            member_4_name = :member_4_name,
            scanned_contract = :scanned_contract,
            contract_stamp = :contract_stamp
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $updatePayload = $payload;
        unset($updatePayload['member_name'], $updatePayload['contract_date'], $updatePayload['house_name']);
        $updatePayload['id'] = $id;
        $stmt->execute($updatePayload);
        return ['success' => true, 'message' => 'Existing sheet found and updated.', 'id' => $id];
    }

    $sql = "INSERT INTO oxford_member_financial_contracts (
        member_name, house_name, contract_date, contract_length, total_amount_owed,
        term_1, term_2, term_3, term_4,
        acknowledgement_name, signature_name, signature_date,
        president_name, treasurer_name, coordinator_name, member_1_name, member_2_name,
        secretary_name, comptroller_name, hs_representative_name, member_3_name, member_4_name,
        scanned_contract, contract_stamp
    ) VALUES (
        :member_name, :house_name, :contract_date, :contract_length, :total_amount_owed,
        :term_1, :term_2, :term_3, :term_4,
        :acknowledgement_name, :signature_name, :signature_date,
        :president_name, :treasurer_name, :coordinator_name, :member_1_name, :member_2_name,
        :secretary_name, :comptroller_name, :hs_representative_name, :member_3_name, :member_4_name,
        :scanned_contract, :contract_stamp
    )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($payload);

    return ['success' => true, 'message' => 'Sheet saved successfully.', 'id' => (int)$pdo->lastInsertId()];
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_autosave']) && $_POST['ajax_autosave'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    $result = saveContract($pdo, $_POST, null, $uploadDir, $uploadWebPath);
    echo json_encode($result);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sheet'])) {
    $result = saveContract($pdo, $_POST, $_FILES['scanned_contract_file'] ?? null, $uploadDir, $uploadWebPath);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';

    if ($result['success'] && isset($result['id'])) {
        $_GET['load_id'] = (string)$result['id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_stamp'])) {
    $stampLoadId = isset($_POST['stamp_load_id']) ? (int)$_POST['stamp_load_id'] : 0;
    $stampValue = trim((string)($_POST['stamp_value'] ?? ''));
    $stampPasswordSubmitted = (string)($_POST['stamp_password'] ?? '');

    if ($stampLoadId <= 0) {
        $message = 'No history record selected for stamping.';
        $messageType = 'error';
    } elseif (!in_array($stampValue, ['CONTRACT FULFILLED', 'VOIDED'], true)) {
        $message = 'Invalid stamp selection.';
        $messageType = 'error';
    } elseif (!oxford_verify_contract_stamp_password($masterPdo, (int)$currentHouseId, $stampPasswordSubmitted)) {
        $message = 'Invalid stamp password.';
        $messageType = 'error';
        $_GET['load_id'] = (string)$stampLoadId;
    } else {
        $stmt = $pdo->prepare("SELECT id, scanned_contract, member_name FROM oxford_member_financial_contracts WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $stampLoadId]);
        $row = $stmt->fetch();

        if (!$row) {
            $message = 'Selected record was not found.';
            $messageType = 'error';
        } elseif (trim((string)$row['scanned_contract']) === '') {
            $message = 'This record does not have an uploaded scanned contract to stamp.';
            $messageType = 'error';
            $_GET['load_id'] = (string)$stampLoadId;
        } else {
            $update = $pdo->prepare("
                UPDATE oxford_member_financial_contracts
                SET contract_stamp = :contract_stamp,
                    contract_stamp_at = NOW(),
                    contract_stamp_by_ip = :contract_stamp_by_ip
                WHERE id = :id
            ");
            $update->execute([
                'contract_stamp' => $stampValue,
                'contract_stamp_by_ip' => getClientIpAddress(),
                'id' => $stampLoadId,
            ]);

            oxford_log_audit($masterPdo, [
                'house_id' => $currentHouseId,
                'user_id' => (int)($oxfordUser['id'] ?? 0),
                'action_name' => 'contract_stamp_applied',
                'page_name' => 'contracts.php',
                'target_table' => 'oxford_member_financial_contracts',
                'target_id' => (string)$stampLoadId,
                'details' => ['stamp' => $stampValue, 'member_name' => (string)($row['member_name'] ?? '')],
            ]);
            oxford_log_activity($masterPdo, $currentHouseId, 'contracts.php', 'contract_stamp_applied', ['stamp' => $stampValue], (int)($oxfordUser['id'] ?? 0));
            $message = 'Stamp applied successfully.';
            $messageType = 'success';
            $_GET['load_id'] = (string)$stampLoadId;
        }
    }
}

$historyMemberList = [];
$historySearch = isset($_GET['history_member_name']) ? trim((string)$_GET['history_member_name']) : '';

$stmtMembers = $pdo->query("
    SELECT member_name, COUNT(*) AS total_records, MAX(updated_at) AS latest_updated
    FROM oxford_member_financial_contracts
    WHERE member_name <> ''
    GROUP BY member_name
    ORDER BY member_name ASC
");
$historyMemberList = $stmtMembers->fetchAll();

$historyRecordOptions = [];
if ($historySearch !== '') {
    $stmt = $pdo->prepare("
        SELECT id, member_name, house_name, contract_date, updated_at, scanned_contract, contract_stamp
        FROM oxford_member_financial_contracts
        WHERE member_name = :member_name
        ORDER BY updated_at DESC, id DESC
        LIMIT 200
    ");
    $stmt->execute([
        'member_name' => $historySearch
    ]);
    $historyRecordOptions = $stmt->fetchAll();
}

$record = [
    'id' => '',
    'member_name' => '',
    'house_name' => '',
    'contract_date' => '',
    'contract_length' => '',
    'total_amount_owed' => '',
    'term_1' => '',
    'term_2' => '',
    'term_3' => '',
    'term_4' => '',
    'acknowledgement_name' => '',
    'signature_name' => '',
    'signature_date' => '',
    'president_name' => '',
    'treasurer_name' => '',
    'coordinator_name' => '',
    'member_1_name' => '',
    'member_2_name' => '',
    'secretary_name' => '',
    'comptroller_name' => '',
    'hs_representative_name' => '',
    'member_3_name' => '',
    'member_4_name' => '',
    'scanned_contract' => '',
    'contract_stamp' => '',
    'contract_stamp_at' => '',
];

$showUploadedCopy = false;
$forceFormView = isset($_GET['view']) && $_GET['view'] === 'form';

if (isset($_GET['load_id']) && ctype_digit((string)$_GET['load_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM oxford_member_financial_contracts WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => (int)$_GET['load_id']]);
    $loaded = $stmt->fetch();

    if ($loaded) {
        $record = array_merge($record, $loaded);
        $record['total_amount_owed'] = moneyForInput($record['total_amount_owed']);

        if ($historySearch === '' && trim((string)$record['member_name']) !== '') {
            $historySearch = (string)$record['member_name'];

            $stmt = $pdo->prepare("
                SELECT id, member_name, house_name, contract_date, updated_at, scanned_contract, contract_stamp
                FROM oxford_member_financial_contracts
                WHERE member_name = :member_name
                ORDER BY updated_at DESC, id DESC
                LIMIT 200
            ");
            $stmt->execute(['member_name' => $historySearch]);
            $historyRecordOptions = $stmt->fetchAll();
        }

        if (!$forceFormView && !empty($record['scanned_contract'])) {
            $showUploadedCopy = true;
        }

        if ($message === '') {
            $message = $showUploadedCopy
                ? 'Loaded saved record. Uploaded copy is shown because this history item has a scanned contract.'
                : 'Loaded saved sheet for editing.';
            $messageType = 'success';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Oxford House Member Financial Contract</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    @page { size: Letter; margin: 0.5in; }
    * { box-sizing: border-box; }

    body {
        margin: 0;
        background: #eef1f5;
        font-family: Arial, Helvetica, sans-serif;
        color: #111;
    }

    .app {
        max-width: 1450px;
        margin: 20px auto;
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 20px;
        padding: 0 16px 30px;
    }

    .panel, .sheet-wrap {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(0,0,0,0.08);
    }

    .panel {
        padding: 18px;
        position: sticky;
        top: 20px;
        align-self: start;
    }

    .panel h2 {
        margin: 0 0 12px;
        font-size: 20px;
    }

    .panel h3 {
        margin: 20px 0 10px;
        font-size: 16px;
    }

    .panel label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .panel input[type="text"],
    .panel input[type="password"],
    .panel input[type="date"],
    .panel input[type="search"],
    .panel input[type="file"],
    .panel select,
    .panel textarea {
        width: 100%;
        border: 1px solid #cfd6dd;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
        margin-bottom: 10px;
        background: #fff;
    }

    .btn {
        width: 100%;
        border: 0;
        border-radius: 6px;
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        margin-bottom: 10px;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-primary {
        background: #1f5fbf;
        color: #fff;
    }

    .btn-secondary {
        background: #eceff3;
        color: #111;
    }

    .btn-danger {
        background: #a11717;
        color: #fff;
    }

    .btn-link {
        display: inline-block;
        text-decoration: none;
        background: #eceff3;
        color: #111;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 13px;
        margin-top: 6px;
    }

    .helper-box {
        padding: 12px;
        border: 1px solid #dde3e8;
        border-radius: 8px;
        background: #fafbfd;
        font-size: 13px;
        line-height: 1.6;
    }

    .sheet-wrap {
        padding: 30px;
    }

    .status-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .status-message {
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 14px;
    }

    .status-success { background: #e7f7ea; color: #136c2e; }
    .status-error { background: #fdecec; color: #9d1d1d; }

    .autosave-indicator {
        font-size: 13px;
        color: #4a5865;
        font-weight: 700;
    }

    .sheet {
        width: 100%;
        max-width: 850px;
        margin: 0 auto;
        background: #fff;
        min-height: 1100px;
        color: #111;
    }

    .logo {
        text-align: center;
        margin-bottom: 12px;
    }

    .logo img {
        max-height: 90px;
        max-width: 260px;
        object-fit: contain;
    }

    .title {
        text-align: center;
        font-size: 28px;
        line-height: 1.35;
        margin-top: 8px;
        margin-bottom: 42px;
        letter-spacing: 0.3px;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
        align-items: flex-end;
    }

    .field-inline {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        flex: 1 1 auto;
        min-width: 240px;
    }

    .field-inline label {
        white-space: nowrap;
        font-size: 17px;
    }

    .line-input, .line-textarea {
        border: 0;
        border-bottom: 1px solid #222;
        background: transparent;
        width: 100%;
        font-size: 17px;
        padding: 2px 2px 4px 2px;
        outline: none;
        border-radius: 0;
    }

    .line-textarea {
        resize: none;
        overflow: hidden;
        min-height: 28px;
        line-height: 1.45;
    }

    .paragraph {
        font-size: 17px;
        line-height: 1.6;
        margin: 26px 0;
    }

    .terms {
        margin: 18px 0 28px;
    }

    .term-row {
        display: grid;
        grid-template-columns: 40px 1fr;
        gap: 14px;
        align-items: start;
        margin-bottom: 18px;
    }

    .term-num {
        font-size: 17px;
        padding-top: 4px;
    }

    .ack-block {
        margin-top: 22px;
        font-size: 16px;
        line-height: 1.55;
    }

    .signature-row {
        display: flex;
        gap: 40px;
        margin: 30px 0 26px;
        flex-wrap: wrap;
    }

    .signature-col {
        width: 280px;
        max-width: 100%;
    }

    .signature-label {
        font-size: 15px;
        margin-top: 6px;
    }

    .lower-paragraph {
        font-size: 15px;
        line-height: 1.6;
        margin: 24px 0 22px;
        text-align: left;
    }

    .officials-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 36px;
        margin-top: 18px;
    }

    .official-item {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .official-item label {
        min-width: 140px;
        font-size: 16px;
    }

    .money-wrap {
        position: relative;
        width: 100%;
    }

    .money-wrap .dollar {
        position: absolute;
        left: 2px;
        bottom: 5px;
        font-size: 17px;
    }

    .money-wrap input {
        padding-left: 16px;
    }

    .viewer-card {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #dde3e8;
        border-radius: 12px;
        overflow: hidden;
    }

    .viewer-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e7edf2;
        background: #f8fafc;
    }

    .viewer-header h2 {
        margin: 0 0 8px;
        font-size: 24px;
    }

    .viewer-meta {
        font-size: 14px;
        line-height: 1.7;
        color: #44515e;
    }

    .viewer-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 16px 20px;
        border-bottom: 1px solid #e7edf2;
        background: #fff;
    }

    .viewer-body {
        padding: 20px;
        background: #f4f7fb;
    }

    .viewer-stage {
        position: relative;
        width: 100%;
    }

    .viewer-frame {
        width: 100%;
        min-height: 950px;
        border: 1px solid #d6dee7;
        border-radius: 8px;
        background: #fff;
    }

    .viewer-image {
        display: block;
        width: 100%;
        height: auto;
        border: 1px solid #d6dee7;
        border-radius: 8px;
        background: #fff;
    }

    .stamp-overlay {
        position: absolute;
        top: 8%;
        left: 50%;
        transform: translateX(-50%) rotate(-14deg);
        z-index: 20;
        font-size: 64px;
        font-weight: 900;
        letter-spacing: 3px;
        color: rgba(200, 0, 0, 0.72);
        border: 8px solid rgba(200, 0, 0, 0.72);
        padding: 18px 34px;
        text-transform: uppercase;
        border-radius: 12px;
        pointer-events: none;
        text-align: center;
        white-space: nowrap;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.4) inset;
    }

    .stamp-info {
        display: inline-block;
        margin-top: 8px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #fee7e7;
        color: #a11717;
    }

    @media (max-width: 1100px) {
        .app {
            grid-template-columns: 1fr;
        }

        .panel {
            position: static;
        }

        .stamp-overlay {
            font-size: 42px;
            padding: 12px 20px;
        }
    }

    @media print {
        body {
            background: #fff;
        }

        .panel, .status-bar {
            display: none !important;
        }

        .sheet-wrap {
            box-shadow: none;
            padding: 0;
        }

        .sheet {
            max-width: none;
        }

        .viewer-actions .btn {
            display: none !important;
        }

        .stamp-overlay {
            color: rgba(200, 0, 0, 0.8) !important;
            border-color: rgba(200, 0, 0, 0.8) !important;
        }
    }
</style>
</head>
<body>
<div class="app">

    <aside class="panel">
        <h2>Contract Tools</h2>

        <form method="get" action="">
            <h3>History Dropdown</h3>

            <label for="history_member_name">Select Member Name</label>
            <select id="history_member_name" name="history_member_name" onchange="this.form.submit()">
                <option value="">-- Select Member --</option>
                <?php foreach ($historyMemberList as $memberItem): ?>
                    <option value="<?= h($memberItem['member_name']) ?>" <?= $historySearch === $memberItem['member_name'] ? 'selected' : '' ?>>
                        <?= h($memberItem['member_name']) ?> (<?= (int)$memberItem['total_records'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($historySearch !== ''): ?>
                <label for="load_id">Select Saved Record</label>
                <select id="load_id" name="load_id">
                    <option value="">-- Select Record --</option>
                    <?php foreach ($historyRecordOptions as $item): ?>
                        <option
                            value="<?= (int)$item['id'] ?>"
                            <?= isset($_GET['load_id']) && (int)$_GET['load_id'] === (int)$item['id'] ? 'selected' : '' ?>
                        >
                            <?= h($item['contract_date']) ?>
                            | House: <?= h($item['house_name']) ?>
                            | Updated: <?= h($item['updated_at']) ?>
                            <?= !empty($item['scanned_contract']) ? ' | Scan' : '' ?>
                            <?= !empty($item['contract_stamp']) ? ' | ' . h($item['contract_stamp']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-secondary" type="submit">Load Selected Record</button>
            <?php endif; ?>
        </form>

        <h3>Instructions</h3>
        <div class="helper-box">
            Choose a member from the dropdown, then choose a saved record from the second dropdown.<br><br>
            If that record has an uploaded scanned contract, the uploaded copy is shown instead of the editable form.<br><br>
            Once a scanned copy has been uploaded, it cannot be replaced or deleted from this page.<br><br>
            You can apply a red stamp of <strong>CONTRACT FULFILLED</strong> or <strong>VOIDED</strong> using the password section below.
        </div>

        <?php if (!empty($record['scanned_contract'])): ?>
            <h3>Current Scanned Contract</h3>
            <a class="btn-link" href="<?= h($record['scanned_contract']) ?>" target="_blank">Open Uploaded Scan</a>
            <?php if (!empty($record['contract_stamp'])): ?>
                <div class="stamp-info">
                    Stamp: <?= h($record['contract_stamp']) ?>
                    <?php if (!empty($record['contract_stamp_at'])): ?>
                        | <?= h($record['contract_stamp_at']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($record['id']) && !empty($record['scanned_contract'])): ?>
            <h3>Apply Red Stamp</h3>
            <form method="post" action="">
                <input type="hidden" name="stamp_load_id" value="<?= (int)$record['id'] ?>">

                <label for="stamp_value">Stamp Text</label>
                <select id="stamp_value" name="stamp_value" required>
                    <option value="">-- Select Stamp --</option>
                    <option value="CONTRACT FULFILLED">CONTRACT FULFILLED</option>
                    <option value="VOIDED">VOIDED</option>
                </select>

                <label for="stamp_password">Password</label>
                <input type="password" id="stamp_password" name="stamp_password" required placeholder="Enter stamp password">

                <button class="btn btn-danger" type="submit" name="apply_stamp" value="1">Apply Red Stamp</button>
            </form>
        <?php endif; ?>
    </aside>

    <main class="sheet-wrap">
        <div class="status-bar">
            <div>
                <?php if ($message !== ''): ?>
                    <div class="status-message <?= $messageType === 'success' ? 'status-success' : 'status-error' ?>">
                        <?= h($message) ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$showUploadedCopy): ?>
                <div class="autosave-indicator" id="autosaveStatus">Autosave: Ready</div>
            <?php endif; ?>
        </div>

        <?php if ($showUploadedCopy): ?>
            <div class="viewer-card">
                <div class="viewer-header">
                    <h2>Uploaded Contract Copy</h2>
                    <div class="viewer-meta">
                        <strong>Member:</strong> <?= h($record['member_name']) ?><br>
                        <strong>House:</strong> Oxford House - <?= h($record['house_name']) ?><br>
                        <strong>Date:</strong> <?= h($record['contract_date']) ?>
                    </div>
                </div>

                <div class="viewer-actions">
                    <a class="btn btn-primary" style="width:auto; min-width:190px;" href="?load_id=<?= (int)$record['id'] ?>&view=form&history_member_name=<?= urlencode((string)$historySearch) ?>">Edit Form Instead</a>
                    <a class="btn btn-secondary" style="width:auto; min-width:190px;" href="<?= h($record['scanned_contract']) ?>" target="_blank">Open Uploaded Copy</a>
                    <button class="btn btn-secondary" style="width:auto; min-width:190px;" type="button" onclick="window.print()">Print Copy</button>
                </div>

                <div class="viewer-body">
                    <div class="viewer-stage">
                        <?php if (!empty($record['contract_stamp'])): ?>
                            <div class="stamp-overlay"><?= h($record['contract_stamp']) ?></div>
                        <?php endif; ?>

                        <?php if (isPreviewableImage((string)$record['scanned_contract'])): ?>
                            <img class="viewer-image" src="<?= h($record['scanned_contract']) ?>" alt="Uploaded scanned contract">
                        <?php elseif (isPdfFile((string)$record['scanned_contract'])): ?>
                            <iframe class="viewer-frame" src="<?= h($record['scanned_contract']) ?>"></iframe>
                        <?php else: ?>
                            <div style="padding:24px; background:#fff; border:1px solid #d6dee7; border-radius:8px;">
                                This uploaded file type cannot be previewed inline.
                                <div style="margin-top:12px;">
                                    <a class="btn btn-primary" style="width:auto; min-width:190px;" href="<?= h($record['scanned_contract']) ?>" target="_blank">Open Uploaded File</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <form id="contractForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="id" value="<?= h($record['id']) ?>">
                <input type="hidden" name="existing_scanned_contract" value="<?= h($record['scanned_contract']) ?>">
                <input type="hidden" name="existing_contract_stamp" value="<?= h($record['contract_stamp']) ?>">

                <div class="sheet">
                    <div class="logo">
                        <?php if (is_file(__DIR__ . '/' . $logoPath)): ?>
                            <img src="<?= h($logoPath) ?>" alt="Oxford House Logo">
                        <?php endif; ?>
                    </div>

                    <div class="title">
                        Oxford House Member<br>
                        Financial Contract
                    </div>

                    <div class="form-row">
                        <div class="field-inline">
                            <label>House Name: Oxford House -</label>
                            <input class="line-input autosave-field" type="text" name="house_name" value="<?= h($record['house_name']) ?>">
                        </div>
                        <div class="field-inline">
                            <label>Member Under Contract:</label>
                            <input class="line-input autosave-field" type="text" name="member_name" value="<?= h($record['member_name']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field-inline" style="max-width: 280px;">
                            <label>Date:</label>
                            <input class="line-input autosave-field" type="text" name="contract_date" value="<?= h($record['contract_date']) ?>">
                        </div>
                        <div class="field-inline">
                            <label>Length of Contract (Indicate # of days and start/end dates):</label>
                            <input class="line-input autosave-field" type="text" name="contract_length" value="<?= h($record['contract_length']) ?>">
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 28px;">
                        <div class="field-inline" style="max-width: 420px;">
                            <label>Total Amount Owed by Member</label>
                            <div class="money-wrap">
                                <span class="dollar">$</span>
                                <input class="line-input autosave-field" type="text" name="total_amount_owed" value="<?= h($record['total_amount_owed']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="paragraph">
                        This contract represents our concern and responsibility as Oxford House members. It is designed to help you live up to your responsibility and so the house can be financially self-supported.
                    </div>

                    <div class="paragraph">
                        The house is asking that you become current on your share of expenses in the following ways in order to best facilitate your recovery and your responsibility to the house. The terms of this contract are the following (be specific):
                    </div>

                    <div class="terms">
                        <div class="term-row">
                            <div class="term-num">1.</div>
                            <textarea class="line-textarea autosave-field" name="term_1" rows="1"><?= h($record['term_1']) ?></textarea>
                        </div>
                        <div class="term-row">
                            <div class="term-num">2.</div>
                            <textarea class="line-textarea autosave-field" name="term_2" rows="1"><?= h($record['term_2']) ?></textarea>
                        </div>
                        <div class="term-row">
                            <div class="term-num">3.</div>
                            <textarea class="line-textarea autosave-field" name="term_3" rows="1"><?= h($record['term_3']) ?></textarea>
                        </div>
                        <div class="term-row">
                            <div class="term-num">4.</div>
                            <textarea class="line-textarea autosave-field" name="term_4" rows="1"><?= h($record['term_4']) ?></textarea>
                        </div>
                    </div>

                    <div class="ack-block">
                        I
                        <input class="line-input autosave-field" style="display:inline-block; width:320px;" type="text" name="acknowledgement_name" value="<?= h($record['acknowledgement_name']) ?>">
                        acknowledge and understand my house’s expectations as outlined in this contract. I am fully aware that if I fail to comply with these terms, any time during the length of this contract, I am subject to immediate expulsion from this Oxford House.
                    </div>

                    <div class="signature-row">
                        <div class="signature-col">
                            <input class="line-input autosave-field" type="text" name="signature_date" value="<?= h($record['signature_date']) ?>">
                            <div class="signature-label">Date</div>
                        </div>
                        <div class="signature-col" style="width:420px;">
                            <input class="line-input autosave-field" type="text" name="signature_name" id="signature_name" value="<?= h($record['signature_name']) ?>">
                            <div class="signature-label">Signature</div>
                        </div>
                    </div>

                    <div class="lower-paragraph">
                        We, your fellow Oxford House members, in an effort to support your recovery and uphold the principles of Oxford House, acknowledge lack of payment of EES and contract terms above. During the course of your financial contract, our hope is that you will comply with the terms of this contract, as we are prepared to vote to expel you from our house if you fail to do so. By complying with this contract you are demonstrating that you are willing to live according to Oxford House principles and remain a member of our house.
                    </div>

                    <div class="officials-grid">
                        <div class="official-item">
                            <label>President:</label>
                            <input class="line-input autosave-field" type="text" name="president_name" value="<?= h($record['president_name']) ?>">
                        </div>
                        <div class="official-item">
                            <label>Secretary:</label>
                            <input class="line-input autosave-field" type="text" name="secretary_name" value="<?= h($record['secretary_name']) ?>">
                        </div>

                        <div class="official-item">
                            <label>Treasurer:</label>
                            <input class="line-input autosave-field" type="text" name="treasurer_name" value="<?= h($record['treasurer_name']) ?>">
                        </div>
                        <div class="official-item">
                            <label>Comptroller:</label>
                            <input class="line-input autosave-field" type="text" name="comptroller_name" value="<?= h($record['comptroller_name']) ?>">
                        </div>

                        <div class="official-item">
                            <label>Coordinator:</label>
                            <input class="line-input autosave-field" type="text" name="coordinator_name" value="<?= h($record['coordinator_name']) ?>">
                        </div>
                        <div class="official-item">
                            <label>HS Representative:</label>
                            <input class="line-input autosave-field" type="text" name="hs_representative_name" value="<?= h($record['hs_representative_name']) ?>">
                        </div>

                        <div class="official-item">
                            <label>Member:</label>
                            <input class="line-input autosave-field" type="text" name="member_1_name" value="<?= h($record['member_1_name']) ?>">
                        </div>
                        <div class="official-item">
                            <label>Member:</label>
                            <input class="line-input autosave-field" type="text" name="member_3_name" value="<?= h($record['member_3_name']) ?>">
                        </div>

                        <div class="official-item">
                            <label>Member:</label>
                            <input class="line-input autosave-field" type="text" name="member_2_name" value="<?= h($record['member_2_name']) ?>">
                        </div>
                        <div class="official-item">
                            <label>Member:</label>
                            <input class="line-input autosave-field" type="text" name="member_4_name" value="<?= h($record['member_4_name']) ?>">
                        </div>
                    </div>

                    <div style="margin-top:34px;">
                        <label style="display:block; font-size:16px; margin-bottom:8px; font-weight:700;">Upload Scanned Version of Contract for Review</label>

                        <?php if (empty($record['scanned_contract'])): ?>
                            <input type="file" name="scanned_contract_file" accept=".pdf,.jpg,.jpeg,.png,.webp" style="font-size:15px;">
                        <?php else: ?>
                            <div class="helper-box">
                                A scanned contract has already been uploaded for this record and cannot be replaced or deleted here.
                                <div style="margin-top:10px;">
                                    <a class="btn-link" href="<?= h($record['scanned_contract']) ?>" target="_blank">Open Existing Uploaded Copy</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top:28px; display:flex; gap:12px; flex-wrap:wrap;">
                        <button class="btn btn-primary" type="submit" name="save_sheet" value="1" style="width:auto; min-width:170px;">Save Sheet</button>
                        <button class="btn btn-secondary" type="button" onclick="window.print()" style="width:auto; min-width:170px;">Print Sheet</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
</div>

<?php if (!$showUploadedCopy): ?>
<script>
(function () {
    const form = document.getElementById('contractForm');
    const autosaveStatus = document.getElementById('autosaveStatus');
    const fields = form ? form.querySelectorAll('.autosave-field') : [];
    const memberNameField = form ? form.querySelector('input[name="member_name"]') : null;
    const ackField = form ? form.querySelector('input[name="acknowledgement_name"]') : null;
    const signatureField = document.getElementById('signature_name');

    let autosaveTimer = null;
    let isSaving = false;

    function setStatus(text) {
        if (autosaveStatus) {
            autosaveStatus.textContent = 'Autosave: ' + text;
        }
    }

    function autoGrow(el) {
        if (!el || el.tagName !== 'TEXTAREA') return;
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
    }

    document.querySelectorAll('textarea.line-textarea').forEach(autoGrow);

    if (!form) {
        return;
    }

    form.addEventListener('input', function (e) {
        if (e.target.matches('textarea.line-textarea')) {
            autoGrow(e.target);
        }

        if (e.target === memberNameField) {
            if (ackField && !ackField.value.trim()) {
                ackField.value = memberNameField.value;
            }
            if (signatureField && !signatureField.value.trim()) {
                signatureField.value = memberNameField.value;
            }
        }

        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(runAutosave, 1200);
        setStatus('Pending...');
    });

    async function runAutosave() {
        if (isSaving) return;
        isSaving = true;
        setStatus('Saving...');

        try {
            const data = new FormData(form);
            data.append('ajax_autosave', '1');

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: data
            });

            const result = await response.json();

            if (result.success) {
                if (result.id) {
                    const idField = document.getElementById('id');
                    if (idField) {
                        idField.value = result.id;
                    }
                }
                setStatus('Saved');
            } else {
                setStatus('Error');
                console.error(result.message || 'Autosave failed');
            }
        } catch (err) {
            setStatus('Error');
            console.error(err);
        } finally {
            isSaving = false;
        }
    }

    fields.forEach(function (field) {
        field.addEventListener('blur', function () {
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(runAutosave, 200);
        });
    });
})();
</script>
<?php endif; ?>
</body>
</html>