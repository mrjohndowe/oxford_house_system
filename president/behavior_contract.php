<?php
/**
 * Oxford House Member Contract For Disruptive/Relapse Behavior
 * Single-file PHP app
 * - Layout closely matches uploaded form
 * - Auto-save to MySQL
 * - History dropdown by member name
 * - Reload/edit prior records
 * - Optional signed/scanned upload
 * - Uploaded signed copy replaces form view and locks editing
 * - Locked edit/unlock requires contract password
 * - Red stamp support: CONTRACT FULFILLED / VOIDED using contract password
 * - Separate left-side control block
 * - Print layout tightened for single page
 */
declare(strict_types=1);

session_start();

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

/* =========================
   APP CONFIG
========================= */
const LOGO_PATH = '../images/oxford_house_logo.png';
const UPLOAD_DIR = __DIR__ . '/uploads/disruptive_contracts';
const UPLOAD_URL = 'uploads/disruptive_contracts';
const MAX_UPLOAD_SIZE = 15 * 1024 * 1024; // 15MB

function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function ensureUploadDir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
}

function sanitizeFilename(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?? 'upload';
    $name = trim($name, '_');
    return $name !== '' ? $name : 'upload';
}

function buildPdo(string $host, string $db, string $user, string $pass): PDO
{
    return new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
    $stmt->execute(['column' => $column]);
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$column} {$definition}");
    }
}

function blankRecord(): array
{
    return [
        'id' => 0,
        'member_name' => '',
        'house_name' => 'House Name',
        'contract_subject' => '',
        'contract_date' => '',
        'contract_length' => '',
        'behavior_1' => '',
        'behavior_2' => '',
        'behavior_3' => '',
        'term_1' => '',
        'term_2' => '',
        'term_3' => '',
        'term_4' => '',
        'term_5' => '',
        'acknowledgment_name' => '',
        'signature_date' => '',
        'president_name' => '',
        'secretary_name' => '',
        'treasurer_name' => '',
        'comptroller_name' => '',
        'coordinator_name' => '',
        'hs_representative_name' => '',
        'member_1_name' => '',
        'member_2_name' => '',
        'member_3_name' => '',
        'member_4_name' => '',
        'uploaded_original_name' => '',
        'uploaded_stored_name' => '',
        'uploaded_mime' => '',
        'uploaded_size' => 0,
        'contract_password_hash' => '',
        'is_locked' => 0,
        'stamp_status' => '',
        'stamp_applied_at' => null,
    ];
}

function recordById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM oxford_disruptive_contracts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function latestHistory(PDO $pdo, string $memberName = ''): array
{
    if ($memberName !== '') {
        $stmt = $pdo->prepare(
            'SELECT id, member_name, house_name, contract_date, updated_at, uploaded_original_name, is_locked, stamp_status
             FROM oxford_disruptive_contracts
             WHERE member_name = :member_name
             ORDER BY updated_at DESC, id DESC
             LIMIT 100'
        );
        $stmt->execute(['member_name' => $memberName]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->query(
        'SELECT id, member_name, house_name, contract_date, updated_at, uploaded_original_name, is_locked, stamp_status
         FROM oxford_disruptive_contracts
         ORDER BY updated_at DESC, id DESC
         LIMIT 100'
    );
    return $stmt->fetchAll();
}

function distinctMemberNames(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT DISTINCT member_name
         FROM oxford_disruptive_contracts
         WHERE member_name <> ''
         ORDER BY member_name ASC"
    );
    return array_map(static fn(array $row): string => (string)$row['member_name'], $stmt->fetchAll());
}

function isUnlockedForSession(int $id): bool
{
    return !empty($_SESSION['disruptive_contract_unlocks'][$id]);
}

function requireValidPassword(array $record, string $password): bool
{
    $hash = (string)($record['contract_password_hash'] ?? '');
    if ($hash === '' || $password === '') {
        return false;
    }
    return password_verify($password, $hash);
}

$pdo = buildPdo($dbHost, $dbName, $dbUser, $dbPass);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS oxford_disruptive_contracts (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        member_name VARCHAR(255) NOT NULL DEFAULT '',
        house_name VARCHAR(255) NOT NULL DEFAULT '',
        contract_subject VARCHAR(255) NOT NULL DEFAULT '',
        contract_date VARCHAR(50) NOT NULL DEFAULT '',
        contract_length VARCHAR(255) NOT NULL DEFAULT '',
        behavior_1 TEXT NULL,
        behavior_2 TEXT NULL,
        behavior_3 TEXT NULL,
        term_1 TEXT NULL,
        term_2 TEXT NULL,
        term_3 TEXT NULL,
        term_4 TEXT NULL,
        term_5 TEXT NULL,
        acknowledgment_name VARCHAR(255) NOT NULL DEFAULT '',
        signature_date VARCHAR(50) NOT NULL DEFAULT '',
        president_name VARCHAR(255) NOT NULL DEFAULT '',
        secretary_name VARCHAR(255) NOT NULL DEFAULT '',
        treasurer_name VARCHAR(255) NOT NULL DEFAULT '',
        comptroller_name VARCHAR(255) NOT NULL DEFAULT '',
        coordinator_name VARCHAR(255) NOT NULL DEFAULT '',
        hs_representative_name VARCHAR(255) NOT NULL DEFAULT '',
        member_1_name VARCHAR(255) NOT NULL DEFAULT '',
        member_2_name VARCHAR(255) NOT NULL DEFAULT '',
        member_3_name VARCHAR(255) NOT NULL DEFAULT '',
        member_4_name VARCHAR(255) NOT NULL DEFAULT '',
        uploaded_original_name VARCHAR(255) NOT NULL DEFAULT '',
        uploaded_stored_name VARCHAR(255) NOT NULL DEFAULT '',
        uploaded_mime VARCHAR(100) NOT NULL DEFAULT '',
        uploaded_size INT UNSIGNED NOT NULL DEFAULT 0,
        contract_password_hash VARCHAR(255) NOT NULL DEFAULT '',
        is_locked TINYINT(1) NOT NULL DEFAULT 0,
        stamp_status VARCHAR(30) NOT NULL DEFAULT '',
        stamp_applied_at DATETIME NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_member_name (member_name),
        INDEX idx_house_name (house_name),
        INDEX idx_contract_date (contract_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

ensureColumn($pdo, 'oxford_disruptive_contracts', 'term_5', 'TEXT NULL AFTER term_4');
ensureColumn($pdo, 'oxford_disruptive_contracts', 'contract_password_hash', "VARCHAR(255) NOT NULL DEFAULT '' AFTER uploaded_size");
ensureColumn($pdo, 'oxford_disruptive_contracts', 'is_locked', "TINYINT(1) NOT NULL DEFAULT 0 AFTER contract_password_hash");
ensureColumn($pdo, 'oxford_disruptive_contracts', 'stamp_status', "VARCHAR(30) NOT NULL DEFAULT '' AFTER is_locked");
ensureColumn($pdo, 'oxford_disruptive_contracts', 'stamp_applied_at', "DATETIME NULL DEFAULT NULL AFTER stamp_status");

if (isset($_GET['action'])) {
    $action = (string)$_GET['action'];

    if ($action === 'history') {
        $memberName = trim((string)($_GET['member_name'] ?? ''));
        jsonResponse([
            'ok' => true,
            'items' => latestHistory($pdo, $memberName),
        ]);
    }

    if ($action === 'load') {
        $id = (int)($_GET['id'] ?? 0);
        $record = $id > 0 ? recordById($pdo, $id) : null;

        if (!$record) {
            jsonResponse(['ok' => false, 'message' => 'Record not found.'], 404);
        }

        jsonResponse([
            'ok' => true,
            'record' => $record,
            'has_upload' => !empty($record['uploaded_stored_name']),
            'upload_url' => !empty($record['uploaded_stored_name']) ? (UPLOAD_URL . '/' . $record['uploaded_stored_name']) : '',
            'is_locked' => (int)($record['is_locked'] ?? 0) === 1,
            'is_unlocked' => isUnlockedForSession((int)$record['id']),
        ]);
    }

    if ($action === 'autosave' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = $_POST;
        $id = (int)($input['id'] ?? 0);
        $existing = $id > 0 ? recordById($pdo, $id) : null;

        if ($existing && (int)$existing['is_locked'] === 1 && !isUnlockedForSession($id)) {
            jsonResponse(['ok' => false, 'message' => 'This contract is locked. Enter the contract password to edit it.'], 403);
        }

        $password = trim((string)($input['contract_password'] ?? ''));

        $data = [
            'member_name' => trim((string)($input['member_name'] ?? '')),
            'house_name' => trim((string)($input['house_name'] ?? '')),
            'contract_subject' => trim((string)($input['contract_subject'] ?? '')),
            'contract_date' => trim((string)($input['contract_date'] ?? '')),
            'contract_length' => trim((string)($input['contract_length'] ?? '')),
            'behavior_1' => trim((string)($input['behavior_1'] ?? '')),
            'behavior_2' => trim((string)($input['behavior_2'] ?? '')),
            'behavior_3' => trim((string)($input['behavior_3'] ?? '')),
            'term_1' => trim((string)($input['term_1'] ?? '')),
            'term_2' => trim((string)($input['term_2'] ?? '')),
            'term_3' => trim((string)($input['term_3'] ?? '')),
            'term_4' => trim((string)($input['term_4'] ?? '')),
            'term_5' => trim((string)($input['term_5'] ?? '')),
            'acknowledgment_name' => trim((string)($input['acknowledgment_name'] ?? '')),
            'signature_date' => trim((string)($input['signature_date'] ?? '')),
            'president_name' => trim((string)($input['president_name'] ?? '')),
            'secretary_name' => trim((string)($input['secretary_name'] ?? '')),
            'treasurer_name' => trim((string)($input['treasurer_name'] ?? '')),
            'comptroller_name' => trim((string)($input['comptroller_name'] ?? '')),
            'coordinator_name' => trim((string)($input['coordinator_name'] ?? '')),
            'hs_representative_name' => trim((string)($input['hs_representative_name'] ?? '')),
            'member_1_name' => trim((string)($input['member_1_name'] ?? '')),
            'member_2_name' => trim((string)($input['member_2_name'] ?? '')),
            'member_3_name' => trim((string)($input['member_3_name'] ?? '')),
            'member_4_name' => trim((string)($input['member_4_name'] ?? '')),
        ];

        if ($existing) {
            $hashSql = '';
            $params = $data + ['id' => $id];

            if ($password !== '' && (string)$existing['contract_password_hash'] === '') {
                $hashSql = ', contract_password_hash = :contract_password_hash';
                $params['contract_password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql = "UPDATE oxford_disruptive_contracts SET
                        member_name = :member_name,
                        house_name = :house_name,
                        contract_subject = :contract_subject,
                        contract_date = :contract_date,
                        contract_length = :contract_length,
                        behavior_1 = :behavior_1,
                        behavior_2 = :behavior_2,
                        behavior_3 = :behavior_3,
                        term_1 = :term_1,
                        term_2 = :term_2,
                        term_3 = :term_3,
                        term_4 = :term_4,
                        term_5 = :term_5,
                        acknowledgment_name = :acknowledgment_name,
                        signature_date = :signature_date,
                        president_name = :president_name,
                        secretary_name = :secretary_name,
                        treasurer_name = :treasurer_name,
                        comptroller_name = :comptroller_name,
                        coordinator_name = :coordinator_name,
                        hs_representative_name = :hs_representative_name,
                        member_1_name = :member_1_name,
                        member_2_name = :member_2_name,
                        member_3_name = :member_3_name,
                        member_4_name = :member_4_name
                        {$hashSql}
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '';

            $sql = "INSERT INTO oxford_disruptive_contracts (
                        member_name, house_name, contract_subject, contract_date, contract_length,
                        behavior_1, behavior_2, behavior_3,
                        term_1, term_2, term_3, term_4, term_5,
                        acknowledgment_name, signature_date,
                        president_name, secretary_name, treasurer_name, comptroller_name,
                        coordinator_name, hs_representative_name,
                        member_1_name, member_2_name, member_3_name, member_4_name,
                        contract_password_hash
                    ) VALUES (
                        :member_name, :house_name, :contract_subject, :contract_date, :contract_length,
                        :behavior_1, :behavior_2, :behavior_3,
                        :term_1, :term_2, :term_3, :term_4, :term_5,
                        :acknowledgment_name, :signature_date,
                        :president_name, :secretary_name, :treasurer_name, :comptroller_name,
                        :coordinator_name, :hs_representative_name,
                        :member_1_name, :member_2_name, :member_3_name, :member_4_name,
                        :contract_password_hash
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data + ['contract_password_hash' => $hash]);
            $id = (int)$pdo->lastInsertId();
        }

        $saved = recordById($pdo, $id);

        jsonResponse([
            'ok' => true,
            'id' => $id,
            'record' => $saved,
            'history' => latestHistory($pdo, $data['member_name']),
            'is_locked' => (int)($saved['is_locked'] ?? 0) === 1,
            'is_unlocked' => isUnlockedForSession($id),
        ]);
    }

    if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $record = $id > 0 ? recordById($pdo, $id) : null;

        if (!$record) {
            jsonResponse(['ok' => false, 'message' => 'Please save the form first before uploading a copy.'], 400);
        }

        if (trim((string)$record['contract_password_hash']) === '') {
            jsonResponse(['ok' => false, 'message' => 'Please save a contract password before uploading the signed contract.'], 400);
        }

        if (!isset($_FILES['uploaded_copy']) || !is_array($_FILES['uploaded_copy'])) {
            jsonResponse(['ok' => false, 'message' => 'No file was uploaded.'], 400);
        }

        $file = $_FILES['uploaded_copy'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            jsonResponse(['ok' => false, 'message' => 'Upload failed.'], 400);
        }

        if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
            jsonResponse(['ok' => false, 'message' => 'File is too large. Max 15MB allowed.'], 400);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            jsonResponse(['ok' => false, 'message' => 'Only PDF, JPG, PNG, and WEBP files are allowed.'], 400);
        }

        ensureUploadDir();
        $originalName = sanitizeFilename((string)($file['name'] ?? 'upload.' . $allowed[$mime]));
        $storedName = 'contract_' . $id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $destination = UPLOAD_DIR . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            jsonResponse(['ok' => false, 'message' => 'Could not move uploaded file.'], 500);
        }

        $stmt = $pdo->prepare(
            'UPDATE oxford_disruptive_contracts
             SET uploaded_original_name = :uploaded_original_name,
                 uploaded_stored_name = :uploaded_stored_name,
                 uploaded_mime = :uploaded_mime,
                 uploaded_size = :uploaded_size,
                 is_locked = 1
             WHERE id = :id'
        );
        $stmt->execute([
            'uploaded_original_name' => $originalName,
            'uploaded_stored_name' => $storedName,
            'uploaded_mime' => $mime,
            'uploaded_size' => (int)($file['size'] ?? 0),
            'id' => $id,
        ]);

        unset($_SESSION['disruptive_contract_unlocks'][$id]);

        $updated = recordById($pdo, $id);
        jsonResponse([
            'ok' => true,
            'message' => 'Uploaded signed copy saved and contract locked.',
            'record' => $updated,
            'upload_url' => UPLOAD_URL . '/' . $storedName,
            'is_locked' => true,
            'is_unlocked' => false,
        ]);
    }

    if ($action === 'unlock' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $password = trim((string)($_POST['password'] ?? ''));
        $record = $id > 0 ? recordById($pdo, $id) : null;

        if (!$record) {
            jsonResponse(['ok' => false, 'message' => 'Record not found.'], 404);
        }

        if (!requireValidPassword($record, $password)) {
            jsonResponse(['ok' => false, 'message' => 'Incorrect contract password.'], 403);
        }

        $_SESSION['disruptive_contract_unlocks'][$id] = true;

        jsonResponse([
            'ok' => true,
            'message' => 'Contract unlocked for editing.',
            'is_locked' => (int)$record['is_locked'] === 1,
            'is_unlocked' => true,
        ]);
    }

    if ($action === 'lock' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        unset($_SESSION['disruptive_contract_unlocks'][$id]);

        jsonResponse([
            'ok' => true,
            'message' => 'Editing lock restored.',
            'is_unlocked' => false,
        ]);
    }

    if ($action === 'stamp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $password = trim((string)($_POST['password'] ?? ''));
        $stamp = trim((string)($_POST['stamp'] ?? ''));
        $allowedStamps = ['CONTRACT FULFILLED', 'VOIDED'];

        $record = $id > 0 ? recordById($pdo, $id) : null;
        if (!$record) {
            jsonResponse(['ok' => false, 'message' => 'Record not found.'], 404);
        }

        if (!in_array($stamp, $allowedStamps, true)) {
            jsonResponse(['ok' => false, 'message' => 'Invalid stamp selection.'], 400);
        }

        if (!requireValidPassword($record, $password)) {
            jsonResponse(['ok' => false, 'message' => 'Incorrect contract password.'], 403);
        }

        $stmt = $pdo->prepare(
            'UPDATE oxford_disruptive_contracts
             SET stamp_status = :stamp_status,
                 stamp_applied_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'stamp_status' => $stamp,
            'id' => $id,
        ]);

        $updated = recordById($pdo, $id);

        jsonResponse([
            'ok' => true,
            'message' => $stamp . ' stamp applied.',
            'record' => $updated,
        ]);
    }

    jsonResponse(['ok' => false, 'message' => 'Invalid action.'], 400);
}

$memberNames = distinctMemberNames($pdo);
$initialRecord = blankRecord();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Member Contract</title>
    <style>
        :root {
            --viewer-width: 1180px;
            --sidebar-width: 300px;
            --content-width: calc(var(--viewer-width) - var(--sidebar-width) - 16px);
            --border: #000;
            --muted: #555;
            --bg: #f3f4f6;
            --panel-bg: #fff;
            --accent: #9b111e;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        body {
            padding: 16px;
        }

        .app-shell {
            max-width: var(--viewer-width);
            margin: 0 auto;
        }

        .statusbar {
            margin: 0 0 10px 0;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 12px;
            color: var(--muted);
        }

        .workspace {
            display: grid;
            grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }

        .sidebar {
            background: var(--panel-bg);
            border: 1px solid #d6d6d6;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.06);
            position: sticky;
            top: 16px;
        }

        .side-section {
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #ececec;
        }

        .side-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .side-title {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 10px;
        }

        .field:last-child {
            margin-bottom: 0;
        }

        .field label {
            font-size: 12px;
            font-weight: 700;
            color: #222;
        }

        .field input,
        .field select,
        .field button {
            width: 100%;
            min-height: 36px;
            border: 1px solid #cfcfcf;
            padding: 8px 10px;
            font-size: 14px;
            border-radius: 6px;
            background: #fff;
        }

        .field input[type="file"] {
            padding: 6px;
        }

        .field button {
            cursor: pointer;
            font-weight: 700;
        }

        .btn-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .btn-primary {
            background: #f7f7f7;
        }

        .btn-danger {
            border-color: #d0a0a0 !important;
            color: #7b1113;
        }

        .btn-success {
            border-color: #9fb79f !important;
            color: #1f5d1f;
        }

        .mini-note {
            font-size: 11px;
            color: #666;
            line-height: 1.35;
            margin-top: 6px;
        }

        .lock-badge,
        .stamp-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            padding: 6px 8px;
            border-radius: 999px;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .lock-badge {
            background: #f5e4a5;
            color: #5e4700;
            border: 1px solid #ddc874;
        }

        .stamp-badge {
            background: #f8d6d6;
            color: #8b1111;
            border: 1px solid #d8a2a2;
        }

        .page-panel {
            background: #fff;
            border: 1px solid var(--border);
            box-shadow: 0 1px 5px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .page {
            width: 100%;
            min-height: auto;
            padding: 18px 22px 16px;
            position: relative;
        }

        .logo-wrap {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo-wrap img {
            max-height: 58px;
            width: auto;
            display: inline-block;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 1px;
            line-height: 1.1;
        }

        .subtitle {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.1;
        }

        .line-row {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 6px;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .line-label {
            white-space: nowrap;
            font-size: 14px;
        }

        .line-input {
            flex: 1 1 auto;
            min-width: 110px;
            border: none;
            border-bottom: 1px solid #000;
            outline: none;
            padding: 2px 4px 1px;
            min-height: 22px;
            font-size: 14px;
            background: transparent;
        }

        .line-input.short { max-width: 150px; }
        .line-input.medium { max-width: 235px; }
        .line-input.center { text-align: center; }

        p {
            margin: 8px 0;
            font-size: 14px;
            line-height: 1.3;
        }

        .numbered-block {
            margin: 6px 0 10px;
        }

        .numbered-row {
            display: grid;
            grid-template-columns: 20px 1fr;
            gap: 6px;
            align-items: start;
            margin-bottom: 6px;
        }

        .num {
            font-size: 14px;
            line-height: 24px;
        }

        textarea.line-area {
            width: 100%;
            min-height: 26px;
            border: none;
            border-bottom: 1px solid #000;
            resize: none;
            outline: none;
            padding: 3px 4px 2px;
            font-size: 14px;
            font-family: Arial, Helvetica, sans-serif;
            background: transparent;
            overflow: hidden;
            line-height: 1.25;
        }

        .acknowledgement {
            margin-top: 8px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 44px 1fr 58px 1fr;
            gap: 8px;
            align-items: end;
            margin: 10px 0 8px;
        }

        .signature-label {
            font-size: 14px;
        }

        .people-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 18px;
            margin-top: 8px;
        }

        .person-line {
            display: flex;
            align-items: end;
            gap: 6px;
        }

        .person-line .role {
            white-space: nowrap;
            min-width: 108px;
            font-size: 14px;
        }

        .person-line .line-input {
            min-width: 0;
        }

        .viewer {
            display: none;
            position: relative;
            background: #fff;
            min-height: 900px;
        }

        .viewer.active {
            display: block;
        }

        .viewer-frame {
            position: relative;
            min-height: 900px;
            background: #fff;
        }

        .viewer iframe,
        .viewer img,
        .viewer embed {
            width: 100%;
            min-height: 900px;
            border: none;
            display: block;
            object-fit: contain;
            background: #fff;
        }

        .hidden {
            display: none !important;
        }

        .form-locked .line-input,
        .form-locked .line-area,
        .form-locked input,
        .form-locked textarea,
        .form-locked select {
            pointer-events: none;
            opacity: 0.9;
        }

        .red-stamp {
            position: absolute;
            top: 300px;
            right: 28px;
            z-index: 10;
            border: 5px solid rgba(150, 0, 0, 0.88);
            color: rgba(150, 0, 0, 0.88);
            font-weight: 900;
            font-size: 32px;
            letter-spacing: 2px;
            padding: 10px 18px;
            transform: rotate(-14deg);
            text-transform: uppercase;
            background: rgba(255,255,255,0.10);
            pointer-events: none;
            box-shadow: 0 0 0 2px rgba(255,255,255,0.15) inset;
        }

        .red-stamp.form-stamp {
            top: 110px;
            right: 34px;
        }

        .print-note {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }

        @media print {
            html, body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .statusbar,
            .sidebar,
            .print-note {
                display: none !important;
            }

            .app-shell {
                max-width: 100%;
                margin: 0;
            }

            .workspace {
                display: block;
            }

            .page-panel {
                border: none;
                box-shadow: none;
                overflow: visible;
            }

            .page {
                padding: 12px 16px 10px;
            }

            .viewer,
            .viewer-frame,
            .viewer iframe,
            .viewer img,
            .viewer embed {
                min-height: auto !important;
                height: auto !important;
                max-height: none !important;
            }

            .red-stamp {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        @media (max-width: 1100px) {
            .workspace {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
        }

        @media (max-width: 760px) {
            body {
                padding: 10px;
            }

            .signature-row,
            .people-grid {
                grid-template-columns: 1fr;
            }

            .person-line .role {
                min-width: 120px;
            }

            .red-stamp {
                font-size: 22px;
                top: 18px;
                right: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <div class="statusbar">
            <div id="save_status">Ready.</div>
            <div id="record_status">No record loaded.</div>
        </div>

        <div class="workspace">
            <aside class="sidebar">
                <div class="side-section">
                    <div class="side-title">History</div>

                    <div class="field">
                        <label for="member_filter">History by Member Name</label>
                        <input list="member_list" id="member_filter" placeholder="Start typing member name...">
                        <datalist id="member_list">
                            <?php foreach ($memberNames as $memberName): ?>
                                <option value="<?= h($memberName) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="field">
                        <label for="history_select">Saved Records</label>
                        <select id="history_select">
                            <option value="">Select a saved record...</option>
                        </select>
                    </div>
                </div>

                <div class="side-section">
                    <div class="side-title">Contract Security</div>

                    <div id="record_flags"></div>

                    <div class="field">
                        <label for="contract_password">Contract Password</label>
                        <input type="password" id="contract_password" name="contract_password" placeholder="Set or enter password">
                    </div>

                    <div class="btn-row">
                        <button type="button" id="unlock_btn" class="btn-primary">Unlock Edit</button>
                        <button type="button" id="relock_btn" class="btn-primary">Restore Lock</button>
                    </div>

                    <div class="mini-note">
                        Uploading a signed contract locks the record. The same contract password is required to unlock edits or apply a red stamp.
                    </div>
                </div>

                <div class="side-section">
                    <div class="side-title">Signed Copy</div>

                    <div class="field">
                        <label for="uploaded_copy">Upload Signed/Scanned Copy</label>
                        <input type="file" id="uploaded_copy" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="btn-row">
                        <button type="button" id="upload_btn" class="btn-primary">Upload Signed Copy</button>
                    </div>
                </div>

                <div class="side-section">
                    <div class="side-title">Red Stamp</div>

                    <div class="btn-row">
                        <button type="button" id="fulfilled_btn" class="btn-success">Stamp CONTRACT FULFILLED</button>
                        <button type="button" id="voided_btn" class="btn-danger">Stamp VOIDED</button>
                        <button type="button" id="dismissed_btn" class="btn-danger">Stamp Dismissed</button>
                        <button type="button" id="moved_out_btn" class="btn-info">Stamp Moved Out</button>
                    </div>
                </div>

                <div class="side-section">
                    <div class="side-title">Actions</div>

                    <div class="btn-row">
                        <button type="button" id="new_record_btn">New Record</button>
                        <button type="button" id="print_btn">Print</button>
                    </div>
                </div>
            </aside>

            <div class="page-panel">
                <div id="form_wrap">
                    <div class="page" id="form_page">
                        <div id="form_stamp_wrap"></div>

                        <form id="contract_form" autocomplete="off">
                            <input type="hidden" name="id" id="record_id" value="0">

                            <div class="logo-wrap">
                                <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo">
                            </div>
                            <div class="title">Oxford House Member Contract</div>
                            <div class="subtitle">For Disruptive/Relapse Behavior</div>

                            <div class="line-row">
                                <span class="line-label">House Name:</span>
                                <input class="line-input" type="text" name="house_name" id="house_name" placeholder="<?= h($initialRecord['house_name']) ?>">
                                <span class="line-label">Member Under Contract:</span>
                                <input class="line-input medium" type="text" name="member_name" id="member_name" placeholder="<?= h($initialRecord['member_name']) ?>">
                            </div>

                            <div class="line-row">
                                <span class="line-label">Date:</span>
                                <input class="line-input short" type="text" name="contract_date" id="contract_date" value="<?= h($initialRecord['contract_date']) ?>">
                                <span class="line-label">Length of Contract (Indicate # of days and start/end dates):</span>
                                <input class="line-input" type="text" name="contract_length" id="contract_length" value="<?= h($initialRecord['contract_length']) ?>">
                            </div>

                            <p>
                                This contract represents our concern and responsibility as Oxford House members. It is designed to help you help
                                yourself. We have observed the following behaviors and/or patterns that are disrupting this Oxford House or show
                                signs of relapse.
                            </p>

                            <div class="numbered-block">
                                <div class="numbered-row">
                                    <div class="num">1.</div>
                                    <textarea class="line-area autosize" name="behavior_1" id="behavior_1"><?= h($initialRecord['behavior_1']) ?></textarea>
                                </div>
                                <div class="numbered-row">
                                    <div class="num">2.</div>
                                    <textarea class="line-area autosize" name="behavior_2" id="behavior_2"><?= h($initialRecord['behavior_2']) ?></textarea>
                                </div>
                                <div class="numbered-row">
                                    <div class="num">3.</div>
                                    <textarea class="line-area autosize" name="behavior_3" id="behavior_3"><?= h($initialRecord['behavior_3']) ?></textarea>
                                </div>
                            </div>

                            <p>
                                The house is asking that you modify your behavior in the following ways in order to best facilitate your recovery
                                and the recovery of everyone in the house. The terms of this contract are the following (be specific):
                            </p>

                            <div class="numbered-block">
                                <div class="numbered-row">
                                    <div class="num">1.</div>
                                    <textarea class="line-area autosize" name="term_1" id="term_1"><?= h($initialRecord['term_1']) ?></textarea>
                                </div>
                                <div class="numbered-row">
                                    <div class="num">2.</div>
                                    <textarea class="line-area autosize" name="term_2" id="term_2"><?= h($initialRecord['term_2']) ?></textarea>
                                </div>
                                <div class="numbered-row">
                                    <div class="num">3.</div>
                                    <textarea class="line-area autosize" name="term_3" id="term_3"><?= h($initialRecord['term_3']) ?></textarea>
                                </div>
                                <div class="numbered-row">
                                    <div class="num">4.</div>
                                    <textarea class="line-area autosize" name="term_4" id="term_4"><?= h($initialRecord['term_4']) ?></textarea>
                                </div>
                                <div class="numbered-row">
                                    <div class="num">5.</div>
                                    <textarea class="line-area autosize" name="term_5" id="term_5"><?= h($initialRecord['term_5']) ?></textarea>
                                </div>
                            </div>

                            <div class="acknowledgement">
                                <p>
                                    I,
                                    <input class="line-input medium" type="text" name="acknowledgment_name" id="acknowledgment_name" value="<?= h($initialRecord['acknowledgment_name']) ?>">
                                    , acknowledge and understand my house’s expectations as outlined in this contract. I am fully aware that if I fail to comply with these terms, any time during the length of this contract, I am subject to immediate expulsion from this Oxford House.
                                </p>
                            </div>

                            <div class="signature-row">
                                <div class="signature-label">Date</div>
                                <input class="line-input" type="text" name="signature_date" id="signature_date" value="<?= h($initialRecord['signature_date']) ?>">
                                <div class="signature-label">Signature</div>
                                <input class="line-input" type="text" name="contract_subject" id="contract_subject" value="<?= h($initialRecord['contract_subject']) ?>">
                            </div>

                            <p>
                                We, your fellow Oxford House members, in an effort to support your recovery and uphold the principles of Oxford House, acknowledge the observed behaviors and contract terms above. During the course of your disruptive/relapse behavior contract, our hope is that you will comply with the terms of this contract, as we are prepared to vote to expel you from our house if you fail to do so. By complying with this contract you are demonstrating that you are willing to live according to Oxford House principles and remain a member of our house.
                            </p>

                            <div class="people-grid">
                                <div class="person-line">
                                    <span class="role">President:</span>
                                    <input class="line-input" type="text" name="president_name" id="president_name" value="<?= h($initialRecord['president_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Secretary:</span>
                                    <input class="line-input" type="text" name="secretary_name" id="secretary_name" value="<?= h($initialRecord['secretary_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Treasurer:</span>
                                    <input class="line-input" type="text" name="treasurer_name" id="treasurer_name" value="<?= h($initialRecord['treasurer_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Comptroller:</span>
                                    <input class="line-input" type="text" name="comptroller_name" id="comptroller_name" value="<?= h($initialRecord['comptroller_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Coordinator:</span>
                                    <input class="line-input" type="text" name="coordinator_name" id="coordinator_name" value="<?= h($initialRecord['coordinator_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">HS Representative:</span>
                                    <input class="line-input" type="text" name="hs_representative_name" id="hs_representative_name" value="<?= h($initialRecord['hs_representative_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Member:</span>
                                    <input class="line-input" type="text" name="member_1_name" id="member_1_name" value="<?= h($initialRecord['member_1_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Member:</span>
                                    <input class="line-input" type="text" name="member_2_name" id="member_2_name" value="<?= h($initialRecord['member_2_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Member:</span>
                                    <input class="line-input" type="text" name="member_3_name" id="member_3_name" value="<?= h($initialRecord['member_3_name']) ?>">
                                </div>
                                <div class="person-line">
                                    <span class="role">Member:</span>
                                    <input class="line-input" type="text" name="member_4_name" id="member_4_name" value="<?= h($initialRecord['member_4_name']) ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="upload_viewer" class="viewer">
                    <div id="viewer_stamp_wrap"></div>
                    <div class="viewer-frame" id="viewer_frame"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('contract_form');
        const formWrap = document.getElementById('form_wrap');
        const formPage = document.getElementById('form_page');
        const viewer = document.getElementById('upload_viewer');
        const viewerFrame = document.getElementById('viewer_frame');
        const memberFilter = document.getElementById('member_filter');
        const historySelect = document.getElementById('history_select');
        const saveStatus = document.getElementById('save_status');
        const recordStatus = document.getElementById('record_status');
        const uploadInput = document.getElementById('uploaded_copy');
        const uploadBtn = document.getElementById('upload_btn');
        const printBtn = document.getElementById('print_btn');
        const newRecordBtn = document.getElementById('new_record_btn');
        const recordIdField = document.getElementById('record_id');
        const memberNameField = document.getElementById('member_name');
        const contractPasswordField = document.getElementById('contract_password');
        const unlockBtn = document.getElementById('unlock_btn');
        const relockBtn = document.getElementById('relock_btn');
        const fulfilledBtn = document.getElementById('fulfilled_btn');
        const voidedBtn = document.getElementById('voided_btn');
        const movedOutBtn = document.getElementById('moved_out_btn');
        const dismissedBtn = document.getElementById('dismissed_btn');
        const textareas = document.querySelectorAll('.autosize');
        const recordFlags = document.getElementById('record_flags');
        const formStampWrap = document.getElementById('form_stamp_wrap');
        const viewerStampWrap = document.getElementById('viewer_stamp_wrap');

        let saveTimer = null;
        let isSaving = false;
        let lastLoadedUploadUrl = '';
        let currentRecord = null;
        let currentLocked = false;
        let currentUnlocked = false;
        let currentStamp = '';

        function autosize(el) {
            el.style.height = 'auto';
            el.style.height = Math.max(26, el.scrollHeight) + 'px';
        }

        textareas.forEach((ta) => {
            autosize(ta);
            ta.addEventListener('input', () => autosize(ta));
        });

        function setSaveStatus(text) {
            saveStatus.textContent = text;
        }

        function setRecordStatus(text) {
            recordStatus.textContent = text;
        }

        function currentId() {
            return parseInt(recordIdField.value || '0', 10) || 0;
        }

        function formDataForSave() {
            const fd = new FormData(form);
            fd.set('contract_password', contractPasswordField.value || '');
            return fd;
        }

        function refreshFlags() {
            const flags = [];

            if (currentLocked) {
                flags.push('<span class="lock-badge">LOCKED</span>');
            }
            if (currentUnlocked) {
                flags.push('<span class="lock-badge">EDIT UNLOCKED</span>');
            }
            if (currentStamp) {
                flags.push('<span class="stamp-badge">' + escapeHtml(currentStamp) + '</span>');
            }

            recordFlags.innerHTML = flags.join('');
        }

        function renderStamp() {
            formStampWrap.innerHTML = '';
            viewerStampWrap.innerHTML = '';

            if (!currentStamp) return;

            const formStamp = document.createElement('div');
            formStamp.className = 'red-stamp form-stamp';
            formStamp.textContent = currentStamp;
            formStampWrap.appendChild(formStamp);

            const viewerStamp = document.createElement('div');
            viewerStamp.className = 'red-stamp';
            viewerStamp.textContent = currentStamp;
            viewerStampWrap.appendChild(viewerStamp);
        }

        function applyEditState() {
            const lockedForEditing = currentLocked && !currentUnlocked;

            if (lockedForEditing) {
                formPage.classList.add('form-locked');
            } else {
                formPage.classList.remove('form-locked');
            }

            Array.from(form.elements).forEach((field) => {
                if (field.id === 'record_id') return;
                if (lockedForEditing) {
                    field.setAttribute('disabled', 'disabled');
                } else {
                    field.removeAttribute('disabled');
                }
            });

            recordIdField.removeAttribute('disabled');
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function refreshHistory(memberName = '') {
            const url = new URL(window.location.href);
            url.searchParams.set('action', 'history');
            if (memberName.trim() !== '') {
                url.searchParams.set('member_name', memberName.trim());
            }

            const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'fetch' } });
            const data = await res.json();

            historySelect.innerHTML = '<option value="">Select a saved record...</option>';

            if (data.items && Array.isArray(data.items)) {
                data.items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);

                    const uploadLabel = item.uploaded_original_name ? ' [Uploaded Copy]' : '';
                    const lockLabel = parseInt(item.is_locked || 0, 10) === 1 ? ' [Locked]' : '';
                    const stampLabel = item.stamp_status ? ' [' + item.stamp_status + ']' : '';

                    option.textContent = `${item.member_name || '(No Member Name)'} - ${item.contract_date || 'No Date'} - ${item.house_name || 'Oxford House'}${uploadLabel}${lockLabel}${stampLabel}`;
                    historySelect.appendChild(option);
                });
            }
        }

        function fillForm(record) {
            for (const [key, value] of Object.entries(record)) {
                const field = form.elements.namedItem(key);
                if (field) {
                    field.value = value ?? '';
                    if (field.tagName === 'TEXTAREA') autosize(field);
                }
            }

            if (record.member_name) {
                memberFilter.value = record.member_name;
            }

            setRecordStatus(`Loaded record #${record.id || 0}`);
        }

        function showUpload(url, mime, originalName = '') {
            lastLoadedUploadUrl = url || '';
            viewerFrame.innerHTML = '';

            if (!url) {
                viewer.classList.remove('active');
                formWrap.classList.remove('hidden');
                return;
            }

            const lowerMime = (mime || '').toLowerCase();
            let node;

            if (lowerMime.includes('pdf') || url.toLowerCase().endsWith('.pdf')) {
                node = document.createElement('embed');
                node.src = url;
                node.type = 'application/pdf';
            } else {
                node = document.createElement('img');
                node.src = url;
                node.alt = originalName || 'Uploaded copy';
            }

            viewerFrame.appendChild(node);
            viewer.classList.add('active');
            formWrap.classList.add('hidden');

            setRecordStatus(`Showing uploaded copy${originalName ? ': ' + originalName : ''}`);
        }

        function showForm() {
            lastLoadedUploadUrl = '';
            viewerFrame.innerHTML = '';
            viewer.classList.remove('active');
            formWrap.classList.remove('hidden');
        }

        function syncRecordState(record, isLocked = false, isUnlocked = false) {
            currentRecord = record || null;
            currentLocked = !!isLocked;
            currentUnlocked = !!isUnlocked;
            currentStamp = record && record.stamp_status ? record.stamp_status : '';
            refreshFlags();
            renderStamp();
            applyEditState();
        }

        async function loadRecord(id) {
            if (!id) return;

            const url = new URL(window.location.href);
            url.searchParams.set('action', 'load');
            url.searchParams.set('id', String(id));

            const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'fetch' } });
            const data = await res.json();

            if (!data.ok) {
                alert(data.message || 'Could not load record.');
                return;
            }

            fillForm(data.record);
            syncRecordState(data.record, data.is_locked, data.is_unlocked);

            if (data.has_upload && data.upload_url) {
                showUpload(data.upload_url, data.record.uploaded_mime || '', data.record.uploaded_original_name || '');
            } else {
                showForm();
            }
        }

        async function autosave() {
            if (isSaving) return;
            if (currentLocked && !currentUnlocked) {
                setSaveStatus('Locked. Enter password to edit.');
                return;
            }

            isSaving = true;
            setSaveStatus('Saving...');

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('action', 'autosave');

                const res = await fetch(url.toString(), {
                    method: 'POST',
                    body: formDataForSave(),
                    headers: { 'X-Requested-With': 'fetch' }
                });

                const data = await res.json();

                if (!data.ok) {
                    setSaveStatus(data.message || 'Save failed.');
                    return;
                }

                if (data.id) {
                    recordIdField.value = data.id;
                    setRecordStatus(`Saved record #${data.id}`);
                }

                syncRecordState(data.record, data.is_locked, data.is_unlocked);
                await refreshHistory(memberFilter.value || memberNameField.value || '');
                setSaveStatus('Saved.');
            } catch (error) {
                setSaveStatus('Save failed.');
            } finally {
                isSaving = false;
            }
        }

        function queueSave() {
            if (currentLocked && !currentUnlocked) {
                setSaveStatus('Locked. Enter password to edit.');
                return;
            }

            setSaveStatus('Changes pending...');
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosave, 700);
        }

        async function unlockRecord() {
            if (!currentId()) {
                alert('Load or save a record first.');
                return;
            }

            const password = contractPasswordField.value.trim();
            if (!password) {
                alert('Enter the contract password.');
                return;
            }

            const fd = new FormData();
            fd.append('id', String(currentId()));
            fd.append('password', password);

            const url = new URL(window.location.href);
            url.searchParams.set('action', 'unlock');

            const res = await fetch(url.toString(), {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'fetch' }
            });

            const data = await res.json();

            if (!data.ok) {
                alert(data.message || 'Unlock failed.');
                return;
            }

            currentUnlocked = true;
            refreshFlags();
            applyEditState();
            setSaveStatus(data.message || 'Unlocked.');
        }

        async function relockRecord() {
            if (!currentId()) {
                return;
            }

            const fd = new FormData();
            fd.append('id', String(currentId()));

            const url = new URL(window.location.href);
            url.searchParams.set('action', 'lock');

            const res = await fetch(url.toString(), {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'fetch' }
            });

            const data = await res.json();

            if (!data.ok) {
                alert(data.message || 'Could not restore lock.');
                return;
            }

            currentUnlocked = false;
            refreshFlags();
            applyEditState();
            setSaveStatus(data.message || 'Lock restored.');
        }

        async function applyStamp(stamp) {
            if (!currentId()) {
                alert('Load or save a record first.');
                return;
            }

            const password = contractPasswordField.value.trim();
            if (!password) {
                alert('Enter the contract password to apply a stamp.');
                return;
            }

            const fd = new FormData();
            fd.append('id', String(currentId()));
            fd.append('password', password);
            fd.append('stamp', stamp);

            const url = new URL(window.location.href);
            url.searchParams.set('action', 'stamp');

            const res = await fetch(url.toString(), {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'fetch' }
            });

            const data = await res.json();

            if (!data.ok) {
                alert(data.message || 'Stamp failed.');
                return;
            }

            if (data.record) {
                syncRecordState(data.record, currentLocked, currentUnlocked);
            }

            await refreshHistory(memberFilter.value || memberNameField.value || '');
            setSaveStatus(data.message || 'Stamp applied.');
        }

        form.addEventListener('input', (e) => {
            const target = e.target;

            if (target && target.id === 'record_id') return;
            if (currentLocked && !currentUnlocked) return;

            queueSave();
        });

        memberFilter.addEventListener('change', async () => {
            await refreshHistory(memberFilter.value);
        });

        memberNameField.addEventListener('change', async () => {
            memberFilter.value = memberNameField.value;
            await refreshHistory(memberFilter.value);
        });

        historySelect.addEventListener('change', async () => {
            const id = historySelect.value;
            if (id) {
                await loadRecord(id);
            }
        });

        uploadBtn.addEventListener('click', async () => {
            if (!currentId()) {
                await autosave();
            }

            if (!currentId()) {
                alert('Please save the form first.');
                return;
            }

            const file = uploadInput.files[0];
            if (!file) {
                alert('Please choose a file to upload.');
                return;
            }

            const password = contractPasswordField.value.trim();
            if (!password && !(currentRecord && currentRecord.contract_password_hash)) {
                alert('Please set and save the contract password first.');
                return;
            }

            if (!currentRecord || !currentRecord.contract_password_hash) {
                await autosave();
            }

            const fd = new FormData();
            fd.append('id', String(currentId()));
            fd.append('uploaded_copy', file);

            setSaveStatus('Uploading signed copy...');

            const url = new URL(window.location.href);
            url.searchParams.set('action', 'upload');

            const res = await fetch(url.toString(), {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'fetch' }
            });

            const data = await res.json();

            if (!data.ok) {
                setSaveStatus(data.message || 'Upload failed.');
                alert(data.message || 'Upload failed.');
                return;
            }

            syncRecordState(data.record, data.is_locked, data.is_unlocked);
            setSaveStatus('Signed copy uploaded. Contract locked.');
            await refreshHistory(memberFilter.value || memberNameField.value || '');
            showUpload(data.upload_url, data.record.uploaded_mime || '', data.record.uploaded_original_name || '');
        });

        unlockBtn.addEventListener('click', unlockRecord);
        relockBtn.addEventListener('click', relockRecord);
        fulfilledBtn.addEventListener('click', () => applyStamp('CONTRACT FULFILLED'));
        voidedBtn.addEventListener('click', () => applyStamp('VOIDED'));

        newRecordBtn.addEventListener('click', () => {
            form.reset();
            recordIdField.value = '0';
            document.getElementById('house_name').value = '';
            memberFilter.value = '';
            historySelect.value = '';
            uploadInput.value = '';
            contractPasswordField.value = '';
            showForm();
            textareas.forEach((ta) => autosize(ta));
            currentRecord = null;
            currentLocked = false;
            currentUnlocked = false;
            currentStamp = '';
            refreshFlags();
            renderStamp();
            applyEditState();
            setSaveStatus('Ready for new record.');
            setRecordStatus('New unsaved record.');
        });

        printBtn.addEventListener('click', () => window.print());

        refreshHistory('');
        refreshFlags();
        renderStamp();
        applyEditState();
    </script>
</body>
</html>