<?php
/**
 * Oxford House Member Contract For Disruptive/Relapse Behavior
 * Single-file PHP app
 * - Layout closely matches uploaded form
 * - Auto-save to MySQL
 * - History dropdown by member name
 * - Reload/edit prior records
 * - Optional scanned upload; when a saved record has an uploaded copy, history displays the uploaded copy instead of the form
 * - Centered Oxford House logo
 * - Print button
 */
declare(strict_types=1);

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

function normalizeDate(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    return $value;
}

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
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
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_member_name (member_name),
        INDEX idx_house_name (house_name),
        INDEX idx_contract_date (contract_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

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
            'SELECT id, member_name, house_name, contract_date, updated_at, uploaded_original_name
             FROM oxford_disruptive_contracts
             WHERE member_name = :member_name
             ORDER BY updated_at DESC, id DESC
             LIMIT 100'
        );
        $stmt->execute(['member_name' => $memberName]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->query(
        'SELECT id, member_name, house_name, contract_date, updated_at, uploaded_original_name
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
        ]);
    }

    if ($action === 'autosave' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = $_POST;
        $id = (int)($input['id'] ?? 0);

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

        if ($id > 0 && recordById($pdo, $id)) {
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
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data + ['id' => $id]);
        } else {
            $sql = "INSERT INTO oxford_disruptive_contracts (
                        member_name, house_name, contract_subject, contract_date, contract_length,
                        behavior_1, behavior_2, behavior_3,
                        term_1, term_2, term_3, term_4,
                        acknowledgment_name, signature_date,
                        president_name, secretary_name, treasurer_name, comptroller_name,
                        coordinator_name, hs_representative_name,
                        member_1_name, member_2_name, member_3_name, member_4_name
                    ) VALUES (
                        :member_name, :house_name, :contract_subject, :contract_date, :contract_length,
                        :behavior_1, :behavior_2, :behavior_3,
                        :term_1, :term_2, :term_3, :term_4,
                        :acknowledgment_name, :signature_date,
                        :president_name, :secretary_name, :treasurer_name, :comptroller_name,
                        :coordinator_name, :hs_representative_name,
                        :member_1_name, :member_2_name, :member_3_name, :member_4_name
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }

        $saved = recordById($pdo, $id);
        jsonResponse([
            'ok' => true,
            'id' => $id,
            'record' => $saved,
            'history' => latestHistory($pdo, $data['member_name']),
        ]);
    }

    if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $record = $id > 0 ? recordById($pdo, $id) : null;

        if (!$record) {
            jsonResponse(['ok' => false, 'message' => 'Please save the form first before uploading a copy.'], 400);
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
                 uploaded_size = :uploaded_size
             WHERE id = :id'
        );
        $stmt->execute([
            'uploaded_original_name' => $originalName,
            'uploaded_stored_name' => $storedName,
            'uploaded_mime' => $mime,
            'uploaded_size' => (int)($file['size'] ?? 0),
            'id' => $id,
        ]);

        $updated = recordById($pdo, $id);
        jsonResponse([
            'ok' => true,
            'message' => 'Uploaded copy saved.',
            'record' => $updated,
            'upload_url' => UPLOAD_URL . '/' . $storedName,
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
            --page-width: 850px;
            --border: #000;
            --muted: #555;
            --bg: #f3f4f6;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 18px;
            background: var(--bg);
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }
        .toolbar {
            max-width: var(--page-width);
            margin: 0 auto 16px auto;
            background: #fff;
            border: 1px solid #d9d9d9;
            padding: 12px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            border-radius: 8px;
        }
        .toolbar .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .toolbar label {
            font-size: 12px;
            font-weight: 700;
            color: #222;
        }
        .toolbar input,
        .toolbar select,
        .toolbar button {
            width: 100%;
            min-height: 38px;
            border: 1px solid #cfcfcf;
            padding: 8px 10px;
            font-size: 14px;
            border-radius: 6px;
            background: #fff;
        }
        .toolbar .actions {
            display: flex;
            gap: 8px;
            align-items: end;
        }
        .toolbar button {
            cursor: pointer;
            font-weight: 700;
        }
        .statusbar {
            max-width: var(--page-width);
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 12px;
            color: var(--muted);
        }
        .page {
            max-width: var(--page-width);
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            padding: 26px 34px 24px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.08);
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 6px;
        }
        .logo-wrap img {
            max-height: 72px;
            width: auto;
            display: inline-block;
        }
        .title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .subtitle {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .line-row {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 8px;
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .line-label {
            white-space: nowrap;
            font-size: 15px;
        }
        .line-input {
            flex: 1 1 auto;
            min-width: 120px;
            border: none;
            border-bottom: 1px solid #000;
            outline: none;
            padding: 2px 4px 1px;
            min-height: 24px;
            font-size: 15px;
            background: transparent;
        }
        .line-input.short { max-width: 180px; }
        .line-input.medium { max-width: 260px; }
        .line-input.center { text-align: center; }
        p {
            margin: 10px 0;
            font-size: 15px;
            line-height: 1.45;
        }
        .numbered-block {
            margin: 8px 0 12px;
        }
        .numbered-row {
            display: grid;
            grid-template-columns: 22px 1fr;
            gap: 8px;
            align-items: start;
            margin-bottom: 8px;
        }
        .num {
            font-size: 15px;
            line-height: 28px;
        }
        textarea.line-area {
            width: 100%;
            min-height: 32px;
            border: none;
            border-bottom: 1px solid #000;
            resize: vertical;
            outline: none;
            padding: 4px 4px 2px;
            font-size: 15px;
            font-family: Arial, Helvetica, sans-serif;
            background: transparent;
            overflow: hidden;
        }
        .acknowledgement {
            margin-top: 10px;
        }
        .signature-row {
            display: grid;
            grid-template-columns: 70px 1fr 60px 1fr;
            gap: 8px;
            align-items: end;
            margin: 14px 0 12px;
        }
        .signature-label {
            font-size: 15px;
        }
        .people-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
            margin-top: 12px;
        }
        .person-line {
            display: flex;
            align-items: end;
            gap: 8px;
        }
        .person-line .role {
            white-space: nowrap;
            min-width: 118px;
            font-size: 15px;
        }
        .person-line .line-input {
            min-width: 0;
        }
        .viewer {
            display: none;
            margin-top: 10px;
            border: 1px solid #000;
            min-height: 850px;
        }
        .viewer.active { display: block; }
        .viewer iframe,
        .viewer img,
        .viewer embed {
            width: 100%;
            min-height: 850px;
            border: none;
            display: block;
            object-fit: contain;
            background: #fff;
        }
        .hidden { display: none !important; }
        .print-note {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar, .statusbar, .print-note {
                display: none !important;
            }
            .page {
                border: none;
                box-shadow: none;
                max-width: 100%;
                padding: 18px 22px;
            }
            .viewer {
                border: none;
                min-height: auto;
            }
            .viewer iframe,
            .viewer embed,
            .viewer img {
                min-height: auto;
                max-height: none;
            }
        }
        @media (max-width: 860px) {
            .toolbar {
                grid-template-columns: 1fr;
            }
            .signature-row,
            .people-grid {
                grid-template-columns: 1fr;
            }
            .person-line .role {
                min-width: 132px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
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
        <div class="field">
            <label for="uploaded_copy">Upload Signed/Scanned Copy</label>
            <input type="file" id="uploaded_copy" accept=".pdf,.jpg,.jpeg,.png,.webp">
        </div>
        <div class="actions">
            <button type="button" id="new_record_btn">New Record</button>
            <button type="button" id="upload_btn">Upload Copy</button>
            <button type="button" id="print_btn">Print</button>
        </div>
    </div>

    <div class="statusbar">
        <div id="save_status">Ready.</div>
        <div id="record_status">No record loaded.</div>
    </div>

    <div class="page">
        <div id="form_wrap">
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
                    <input class="line-input medium" type="text" name="member_name" id="member_name" value="<?= h($initialRecord['member_name']) ?>">
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

        <div id="upload_viewer" class="viewer"></div>
        <!-- <div class="print-note">When a saved history record has an uploaded signed/scanned copy, that uploaded copy is shown instead of the fillable form.</div> -->
    </div>

    <script>
        const form = document.getElementById('contract_form');
        const formWrap = document.getElementById('form_wrap');
        const viewer = document.getElementById('upload_viewer');
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
        const textareas = document.querySelectorAll('.autosize');

        let saveTimer = null;
        let isSaving = false;
        let lastLoadedUploadUrl = '';

        function autosize(el) {
            el.style.height = 'auto';
            el.style.height = Math.max(32, el.scrollHeight) + 'px';
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
            return new FormData(form);
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
                    option.textContent = `${item.member_name || '(No Member Name)'} - ${item.contract_date || 'No Date'} - ${item.house_name || 'Oxford House'}${uploadLabel}`;
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
            viewer.innerHTML = '';
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

            viewer.appendChild(node);
            viewer.classList.add('active');
            formWrap.classList.add('hidden');
            setRecordStatus(`Showing uploaded copy${originalName ? ': ' + originalName : ''}`);
        }

        function showForm() {
            lastLoadedUploadUrl = '';
            viewer.innerHTML = '';
            viewer.classList.remove('active');
            formWrap.classList.remove('hidden');
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
            if (data.has_upload && data.upload_url) {
                showUpload(data.upload_url, data.record.uploaded_mime || '', data.record.uploaded_original_name || '');
            } else {
                showForm();
            }
        }

        async function autosave() {
            if (isSaving) return;
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
                await refreshHistory(memberFilter.value || memberNameField.value || '');
                setSaveStatus('Saved.');
            } catch (error) {
                setSaveStatus('Save failed.');
            } finally {
                isSaving = false;
            }
        }

        function queueSave() {
            setSaveStatus('Changes pending...');
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosave, 900);
        }

        form.addEventListener('input', () => {
            if (lastLoadedUploadUrl) {
                showForm();
            }
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
            const fd = new FormData();
            fd.append('id', String(currentId()));
            fd.append('uploaded_copy', file);

            setSaveStatus('Uploading copy...');
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
            setSaveStatus('Upload saved.');
            await refreshHistory(memberFilter.value || memberNameField.value || '');
            showUpload(data.upload_url, data.record.uploaded_mime || '', data.record.uploaded_original_name || '');
        });

        newRecordBtn.addEventListener('click', () => {
            form.reset();
            recordIdField.value = '0';
            document.getElementById('house_name').value = 'Oxford House';
            memberFilter.value = '';
            historySelect.value = '';
            uploadInput.value = '';
            showForm();
            textareas.forEach((ta) => autosize(ta));
            setSaveStatus('Ready for new record.');
            setRecordStatus('New unsaved record.');
        });

        printBtn.addEventListener('click', () => window.print());

        refreshHistory('');
    </script>
</body>
</html>
