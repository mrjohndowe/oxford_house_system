<?php
declare(strict_types=1);

/**
 * Oxford House Newcomer Contract
 * - Single-file PHP app
 * - Auto-save to MySQL only after Contract Information is filled out
 * - Reload/edit prior records
 * - Print button
 * - Oxford House logo support
 * - Locked scanned upload once uploaded
 * - After upload, only contract disposition can be edited
 */

require_once __DIR__ . '/../extras/master_config.php';

$logoPath = '../images/oxford_house_logo.png';
$tableName = 'oxford_newcomer_contracts';
$uploadDir = __DIR__ . '/../uploads/newcomer_contracts';
$uploadWebPath = '../uploads/newcomer_contracts';

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_money(mixed $value): string
{
    $raw = trim((string)$value);
    if ($raw === '') {
        return '';
    }
    $raw = str_replace([',', '$', ' '], '', $raw);
    if (!is_numeric($raw)) {
        return '';
    }
    return number_format((float)$raw, 2, '.', '');
}

function normalize_date(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : '';
}

function safe_filename(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?? 'file';
    $name = trim($name, '._-');
    return $name !== '' ? $name : 'file';
}

function allowed_upload_extension(string $filename): string
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    return in_array($ext, $allowed, true) ? $ext : '';
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

$pdo->exec("
CREATE TABLE IF NOT EXISTS `{$tableName}` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    member_name VARCHAR(255) NOT NULL DEFAULT '',
    date_issued DATE DEFAULT NULL,
    effective_date DATE DEFAULT NULL,
    weekly_ees DECIMAL(10,2) NOT NULL DEFAULT 150.00,
    contract_total DECIMAL(10,2) NOT NULL DEFAULT 330.00,
    purpose_text LONGTEXT NULL,
    newcomer_terms LONGTEXT NULL,
    financial_terms LONGTEXT NULL,
    performance_terms LONGTEXT NULL,
    limitations_terms LONGTEXT NULL,
    relationship_terms LONGTEXT NULL,
    consequences_text LONGTEXT NULL,
    acknowledgement_text LONGTEXT NULL,
    uploaded_file_name VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_file_path VARCHAR(500) NOT NULL DEFAULT '',
    uploaded_at DATETIME DEFAULT NULL,
    is_upload_locked TINYINT(1) NOT NULL DEFAULT 0,
    contract_disposition ENUM('pending','fulfilled','dismissed','voided') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member_name (member_name),
    INDEX idx_house_name (house_name),
    INDEX idx_date_issued (date_issued)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$existingColumns = $pdo->query("SHOW COLUMNS FROM `{$tableName}`")->fetchAll(PDO::FETCH_COLUMN, 0);
$alterMap = [
    'uploaded_file_name' => "ALTER TABLE `{$tableName}` ADD COLUMN uploaded_file_name VARCHAR(255) NOT NULL DEFAULT '' AFTER acknowledgement_text",
    'uploaded_file_path' => "ALTER TABLE `{$tableName}` ADD COLUMN uploaded_file_path VARCHAR(500) NOT NULL DEFAULT '' AFTER uploaded_file_name",
    'uploaded_at' => "ALTER TABLE `{$tableName}` ADD COLUMN uploaded_at DATETIME DEFAULT NULL AFTER uploaded_file_path",
    'is_upload_locked' => "ALTER TABLE `{$tableName}` ADD COLUMN is_upload_locked TINYINT(1) NOT NULL DEFAULT 0 AFTER uploaded_at",
    'contract_disposition' => "ALTER TABLE `{$tableName}` ADD COLUMN contract_disposition ENUM('pending','fulfilled','dismissed','voided') NOT NULL DEFAULT 'pending' AFTER is_upload_locked",
];
foreach ($alterMap as $col => $sql) {
    if (!in_array($col, $existingColumns, true)) {
        $pdo->exec($sql);
    }
}

$defaultPurpose = "This contract is established to re-establish accountability, structure, and financial responsibility within the Oxford House due to:\n\n- Missed and/or late EES payments\n- Recent contract violations\n- Need to demonstrate consistent adherence to house standards";
$defaultNewcomerTerms = "- The member will be treated as a new member of the house\n- The member must re-earn full member standing\n- The member acknowledges reduced trust and increased accountability expectations";
$defaultFinancialTerms = "- Weekly EES amount: $150.00\n- Contract obligation: Two (2) weeks EES + 10% = $330.00 total\n- Make all payments on time and in full\n- Communicate prior to the weekly house meeting if unable to meet payment requirements\n- Actively work toward maintaining a current or ahead balance";
$defaultPerformanceTerms = "- Consistent and timely EES payments\n- No further contract violations\n- Reliability in all house responsibilities\n- Active participation in house expectations\n- Removal from newcomer status will be determined by house vote based on demonstrated consistency";
$defaultLimitationsTerms = "- Reduced credibility in house decisions and discussions\n- Increased scrutiny regarding chores, meeting attendance, and behavior\n- Expectation to demonstrate financial responsibility, program consistency, and accountability to the house";
$defaultRelationshipTerms = "Behavioral Contract (Conduct-Based): Any future violation involving paraphernalia or similar behavior may result in immediate dismissal.\n\nNewcomer Contract (Accountability-Based): The member must demonstrate the ability to maintain payments, follow house structure, and remain consistent and accountable.";
$defaultConsequences = "Failure to comply with the terms of this contract may result in additional house sanctions, extended newcomer status, or review for dismissal from the house.";
$defaultAcknowledgement = "I understand that I am being given the opportunity to remain in the house under structured conditions. I acknowledge that I am in a high-risk but recoverable position, that the house is providing grace, structure, and clear expectations, and that my continued residency depends on my ability to meet these expectations consistently.";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $action = (string)$_POST['ajax'];

    if ($action === 'autosave') {
        $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id > 0) {
            $lockStmt = $pdo->prepare("SELECT is_upload_locked FROM `{$tableName}` WHERE id = :id LIMIT 1");
            $lockStmt->execute(['id' => $id]);
            $lockedRow = $lockStmt->fetch();
            if ($lockedRow && (int)$lockedRow['is_upload_locked'] === 1) {
                json_response([
                    'ok' => false,
                    'message' => 'This record is locked because a scanned contract has been uploaded. Only the disposition can be changed.'
                ], 423);
            }
        }

        $data = [
            'house_name' => trim((string)($_POST['house_name'] ?? '')),
            'member_name' => trim((string)($_POST['member_name'] ?? '')),
            'date_issued' => normalize_date($_POST['date_issued'] ?? ''),
            'effective_date' => normalize_date($_POST['effective_date'] ?? ''),
            'weekly_ees' => normalize_money($_POST['weekly_ees'] ?? '') ?: '150.00',
            'contract_total' => normalize_money($_POST['contract_total'] ?? '') ?: '330.00',
            'purpose_text' => trim((string)($_POST['purpose_text'] ?? '')),
            'newcomer_terms' => trim((string)($_POST['newcomer_terms'] ?? '')),
            'financial_terms' => trim((string)($_POST['financial_terms'] ?? '')),
            'performance_terms' => trim((string)($_POST['performance_terms'] ?? '')),
            'limitations_terms' => trim((string)($_POST['limitations_terms'] ?? '')),
            'relationship_terms' => trim((string)($_POST['relationship_terms'] ?? '')),
            'consequences_text' => trim((string)($_POST['consequences_text'] ?? '')),
            'acknowledgement_text' => trim((string)($_POST['acknowledgement_text'] ?? '')),
        ];

        if ($id > 0) {
            $sql = "UPDATE `{$tableName}` SET
                house_name = :house_name,
                member_name = :member_name,
                date_issued = :date_issued,
                effective_date = :effective_date,
                weekly_ees = :weekly_ees,
                contract_total = :contract_total,
                purpose_text = :purpose_text,
                newcomer_terms = :newcomer_terms,
                financial_terms = :financial_terms,
                performance_terms = :performance_terms,
                limitations_terms = :limitations_terms,
                relationship_terms = :relationship_terms,
                consequences_text = :consequences_text,
                acknowledgement_text = :acknowledgement_text
                WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $data['id'] = $id;
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO `{$tableName}` (
                house_name, member_name, date_issued, effective_date, weekly_ees, contract_total,
                purpose_text, newcomer_terms, financial_terms, performance_terms, limitations_terms,
                relationship_terms, consequences_text, acknowledgement_text
            ) VALUES (
                :house_name, :member_name, :date_issued, :effective_date, :weekly_ees, :contract_total,
                :purpose_text, :newcomer_terms, :financial_terms, :performance_terms, :limitations_terms,
                :relationship_terms, :consequences_text, :acknowledgement_text
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
        }

        json_response([
            'ok' => true,
            'id' => $id,
            'message' => 'Auto-saved',
            'saved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    if ($action === 'upload_scan') {
        $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            json_response(['ok' => false, 'message' => 'Save the contract before uploading the scanned copy.'], 400);
        }

        $stmt = $pdo->prepare("SELECT id, member_name, uploaded_file_path, is_upload_locked FROM `{$tableName}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        if (!$record) {
            json_response(['ok' => false, 'message' => 'Record not found'], 404);
        }
        if ((int)$record['is_upload_locked'] === 1 || trim((string)$record['uploaded_file_path']) !== '') {
            json_response(['ok' => false, 'message' => 'A scanned contract has already been uploaded and locked.'], 423);
        }
        if (!isset($_FILES['contract_scan']) || !is_array($_FILES['contract_scan'])) {
            json_response(['ok' => false, 'message' => 'No file was uploaded.'], 400);
        }
        if ((int)$_FILES['contract_scan']['error'] !== UPLOAD_ERR_OK) {
            json_response(['ok' => false, 'message' => 'Upload failed.'], 400);
        }

        $originalName = (string)$_FILES['contract_scan']['name'];
        $ext = allowed_upload_extension($originalName);
        if ($ext === '') {
            json_response(['ok' => false, 'message' => 'Allowed file types: PDF, JPG, JPEG, PNG, WEBP.'], 400);
        }

        $safeMember = safe_filename((string)$record['member_name']);
        $storedName = 'newcomer_contract_' . $id . '_' . $safeMember . '_' . date('Ymd_His') . '.' . $ext;
        $absolutePath = $uploadDir . '/' . $storedName;
        $relativePath = $uploadWebPath . '/' . $storedName;

        if (!move_uploaded_file($_FILES['contract_scan']['tmp_name'], $absolutePath)) {
            json_response(['ok' => false, 'message' => 'Failed to move uploaded file.'], 500);
        }

        $update = $pdo->prepare("UPDATE `{$tableName}` SET
            uploaded_file_name = :uploaded_file_name,
            uploaded_file_path = :uploaded_file_path,
            uploaded_at = NOW(),
            is_upload_locked = 1
            WHERE id = :id
        ");
        $update->execute([
            'uploaded_file_name' => $originalName,
            'uploaded_file_path' => $relativePath,
            'id' => $id,
        ]);

        json_response([
            'ok' => true,
            'message' => 'Scanned contract uploaded and record locked.',
            'file_name' => $originalName,
            'file_path' => $relativePath,
            'locked' => true,
        ]);
    }

    if ($action === 'update_disposition') {
        $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
        $allowed = ['pending', 'fulfilled', 'dismissed', 'voided'];
        $disposition = strtolower(trim((string)($_POST['contract_disposition'] ?? 'pending')));
        if ($id <= 0) {
            json_response(['ok' => false, 'message' => 'Invalid record ID.'], 400);
        }
        if (!in_array($disposition, $allowed, true)) {
            json_response(['ok' => false, 'message' => 'Invalid disposition.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE `{$tableName}` SET contract_disposition = :contract_disposition WHERE id = :id");
        $stmt->execute([
            'contract_disposition' => $disposition,
            'id' => $id,
        ]);

        json_response([
            'ok' => true,
            'message' => 'Disposition updated.',
            'contract_disposition' => $disposition,
        ]);
    }

    if ($action === 'load' && isset($_POST['id']) && ctype_digit((string)$_POST['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM `{$tableName}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$_POST['id']]);
        $row = $stmt->fetch();
        if (!$row) {
            json_response(['ok' => false, 'message' => 'Record not found'], 404);
        }
        json_response(['ok' => true, 'record' => $row]);
    }
}

$historyStmt = $pdo->query("SELECT id, member_name, house_name, date_issued, contract_disposition, is_upload_locked, updated_at FROM `{$tableName}` ORDER BY updated_at DESC, id DESC");
$historyRows = $historyStmt->fetchAll();

$prefill = [
    'id' => '',
    'house_name' => '',
    'member_name' => '',
    'date_issued' => '',
    'effective_date' => '',
    'weekly_ees' => '',
    'contract_total' => '',
    'purpose_text' => '',
    'newcomer_terms' => '',
    'financial_terms' => '',
    'performance_terms' => '',
    'limitations_terms' => '',
    'relationship_terms' => '',
    'consequences_text' => '',
    'acknowledgement_text' => '',
    'uploaded_file_name' => '',
    'uploaded_file_path' => '',
    'uploaded_at' => '',
    'is_upload_locked' => 0,
    'contract_disposition' => 'pending',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Oxford House Newcomer Contract</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --border: #111;
            --light: #f4f4f4;
            --text: #111;
            --accent: #1d4f91;
            --success: #0f6b2d;
            --warn: #8a5a00;
            --danger: #8d1111;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: #e9edf2;
        }
        body { padding: 18px; }
        .page {
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #cfd6df;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
            padding: 14px 14px 20px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: flex-end;
            margin-bottom: 8px;
            flex-wrap: nowrap;
        }
        .top-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: nowrap;
        }
        .logo-wrap { text-align: center; margin-bottom: 6px; }
        .logo-wrap img { max-width: 88px; max-height: 88px; display: inline-block; }
        .title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: .5px;
            margin: 2px 0 10px;
            text-transform: uppercase;
        }
        .subtle { color: #444; font-size: 12px; }
        .history-select, input[type="text"], input[type="date"], input[type="number"], input[type="file"], textarea, select {
            width: 100%;
            border: 1px solid var(--border);
            padding: 6px 8px;
            background: #fff;
            border-radius: 0;
            font: inherit;
        }
        textarea {
            min-height: 92px;
            resize: none;
            line-height: 1.35;
            white-space: pre-wrap;
            overflow: hidden;
        }
        button {
            border: 1px solid var(--border);
            background: #fff;
            padding: 6px 10px;
            cursor: pointer;
            line-height: 1.2;
            font: inherit;
        }
        button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        button[disabled], input[disabled], textarea[disabled], select[disabled] {
            opacity: .75;
            cursor: not-allowed;
            background: #f3f3f3;
        }
        .grid-2, .grid-4 {
            display: grid;
            gap: 8px;
        }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .field { margin-bottom: 8px; }
        .label {
            display: block;
            font-weight: 700;
            margin-bottom: 3px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .section {
            border: 1px solid var(--border);
            margin-top: 8px;
        }
        .section-head {
            background: var(--light);
            border-bottom: 1px solid var(--border);
            padding: 6px 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .35px;
            font-size: 13px;
        }
        .section-body { padding: 8px; }
        .money-note {
            font-size: 11px;
            color: #444;
            margin-top: 4px;
        }
        .status-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .autosave-status {
            font-size: 12px;
            color: #333;
            min-height: 16px;
        }
        .sig-lines-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 18px;
            margin-top: 4px;
        }
        .sig-line-block { min-height: 64px; }
        .sig-line {
            border-bottom: 1px solid #111;
            height: 34px;
            margin-bottom: 6px;
        }
        .sig-caption {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
        }
        .sig-caption span:last-child {
            min-width: 110px;
            text-align: right;
        }
        .upload-box {
            border: 1px dashed #555;
            padding: 10px;
            background: #fbfbfb;
        }
        .lock-banner {
            margin-top: 8px;
            padding: 8px 10px;
            border: 1px solid #b91c1c;
            background: #fff3f3;
            color: #7f1d1d;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
        }
        .pill {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #111;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            background: #fff;
        }
        .pill.pending { color: var(--warn); border-color: var(--warn); }
        .pill.fulfilled { color: var(--success); border-color: var(--success); }
        .pill.dismissed, .pill.voided { color: var(--danger); border-color: var(--danger); }
        .file-link {
            display: inline-block;
            margin-top: 6px;
            word-break: break-word;
        }

        @media (max-width: 860px) {
            .topbar { flex-wrap: wrap; }
            .grid-2, .grid-4, .sig-lines-grid { grid-template-columns: 1fr; }
            .contract-info-row { grid-template-columns: 1fr !important; }
            body { padding: 8px; }
            .page { padding: 10px; }
        }

        @page {
            size: Letter;
            margin: 0.35in;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .page { max-width: none; border: 0; box-shadow: none; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            textarea, input, select {
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .section-head {
                background: #efefef !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar no-print">
        <div style="min-width: 0; flex: 1 1 auto; max-width: 640px;">
            <label class="label" for="history_id" style="margin-bottom:2px;">History</label>
            <select id="history_id" class="history-select">
                <option value="">-- New Contract --</option>
                <?php foreach ($historyRows as $row): ?>
                    <option value="<?= (int)$row['id'] ?>">
                        <?= h(($row['member_name'] ?: 'Unnamed Member') . ' | ' . ($row['house_name'] ?: 'No House') . ' | ' . ($row['date_issued'] ?: 'No Date') . ' | ' . strtoupper((string)$row['contract_disposition']) . ' | ' . ((int)$row['is_upload_locked'] === 1 ? 'LOCKED' : 'OPEN') . ' | Updated ' . $row['updated_at']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="loadSelectedContract()" style="margin-top:6px;">Load Selected</button>
        </div>
        <div class="top-actions">
            <button type="button" onclick="newRecord()">New</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="logo-wrap">
        <img src="<?= h($logoPath) ?>" alt="Oxford House Logo" onerror="this.style.display='none'">
    </div>
    <div class="title">Oxford House Newcomer Contract</div>

    <form id="contractForm" autocomplete="off">
        <input type="hidden" name="id" id="id" value="<?= h($prefill['id']) ?>">
        <input type="hidden" name="is_upload_locked" id="is_upload_locked" value="0">
        <input type="hidden" name="uploaded_file_path" id="uploaded_file_path" value="">
        <input type="hidden" name="uploaded_file_name" id="uploaded_file_name" value="">

        <div class="status-row no-print">
            <div class="autosave-status" id="autosaveStatus">Auto-save will start once Contract Information is fully filled out.</div>
            <div class="subtle">After scanned upload, the contract locks and only disposition can be changed.</div>
        </div>

        <div class="section no-print">
            <div class="section-head">Scanned Contract Upload / Final Disposition</div>
            <div class="section-body">
                <div class="grid-2">
                    <div class="upload-box">
                        <label class="label" for="contract_scan">Upload Signed Contract</label>
                        <input type="file" id="contract_scan" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <div class="money-note">Allowed file types: PDF, JPG, JPEG, PNG, WEBP. Once uploaded, the record is locked and cannot be edited.</div>
                        <div style="margin-top:8px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <button type="button" class="primary" id="uploadBtn" onclick="uploadScan()">Upload & Lock</button>
                            <span id="uploadedFileStatus" class="subtle">No scanned contract uploaded yet.</span>
                        </div>
                        <div id="uploadedFileLinkWrap"></div>
                    </div>
                    <div>
                        <div class="field">
                            <label class="label" for="contract_disposition">Contract Disposition</label>
                            <select id="contract_disposition" name="contract_disposition">
                                <option value="pending">Pending</option>
                                <option value="fulfilled">Contract Fulfilled</option>
                                <option value="dismissed">Member Dismissed</option>
                                <option value="voided">Voided</option>
                            </select>
                        </div>
                        <div>
                            <span class="pill pending" id="dispositionBadge">Pending</span>
                        </div>
                        <div class="money-note" style="margin-top:8px;">This is the only field that remains editable after upload lock.</div>
                    </div>
                </div>
                <div id="lockBanner" class="lock-banner" style="display:none;">Record is locked. Form content can no longer be edited because a scanned contract has been uploaded.</div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Contract Information</div>
            <div class="section-body">
                <div class="grid-4 contract-info-row" style="grid-template-columns: 1.15fr 1.15fr .85fr .85fr .75fr .75fr; gap:8px;">
                    <div class="field" style="margin-bottom:0;">
                        <label class="label" for="house_name">House Name</label>
                        <input type="text" name="house_name" id="house_name" value="<?= h($prefill['house_name']) ?>" data-lockable="1">
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label class="label" for="member_name">Member Name</label>
                        <input type="text" name="member_name" id="member_name" value="<?= h($prefill['member_name']) ?>" data-lockable="1">
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label class="label" for="date_issued">Date Issued</label>
                        <input type="date" name="date_issued" id="date_issued" value="<?= h($prefill['date_issued']) ?>" data-lockable="1">
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label class="label" for="effective_date">Effective Date</label>
                        <input type="date" name="effective_date" id="effective_date" value="<?= h($prefill['effective_date']) ?>" data-lockable="1">
                    </div>
                </div>
                <div class="money-note">Default contract total example is 2 weeks EES + 10%.</div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Purpose <code>This contract is established to <b>re-establish accountability, structure, and financial responsibility</b> within the Oxford House due to:</code></div>
            <div class="section-body">
                <textarea name="purpose_text" id="purpose_text" placeholder="<?= h($defaultPurpose) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Terms of Newcomer Status <code>By agreement of the house, the above-named member is hereby placed on <b>Newcomer Status</b>, which includes the following conditions:</code></div>
            <div class="section-body">
                <textarea name="newcomer_terms" id="newcomer_terms" placeholder="<?= h($defaultNewcomerTerms) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Financial Requirements (EES) <code>The member agrees to the following financial terms:</code></div>
            <div class="section-body">
                <textarea name="financial_terms" id="financial_terms" placeholder="<?= h($defaultFinancialTerms) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Performance Requirements <code>To be removed from Newcomer Status, the member must demonstrate:</code></div>
            <div class="section-body">
                <textarea name="performance_terms" id="performance_terms" placeholder="<?= h($defaultPerformanceTerms) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Limitations During Newcomer Status <code>While on Newcomer Status, the member acknowledges:</code></div>
            <div class="section-body">
                <textarea name="limitations_terms" id="limitations_terms" placeholder="<?= h($defaultLimitationsTerms) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Relationship to Behavioral Contract <code>The member acknowledges that they are currently under both:</code></div>
            <div class="section-body">
                <textarea name="relationship_terms" id="relationship_terms" placeholder="<?= h($defaultRelationshipTerms) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Consequences <code>Failure to comply with the terms of this contract may result in:</code></div>
            <div class="section-body">
                <textarea name="consequences_text" id="consequences_text" placeholder="<?= h($defaultConsequences) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Acknowledgment <code>I understand that I am being given the opportunity to remain in the house under structured conditions. I acknowledge that:</code></div>
            <div class="section-body">
                <textarea name="acknowledgement_text" id="acknowledgement_text" placeholder="<?= h($defaultAcknowledgement) ?>" data-lockable="1"></textarea>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Signatures</div>
            <div class="section-body">
                <div class="sig-lines-grid">
                    <div class="sig-line-block">
                        <div class="sig-line"></div>
                        <div class="sig-caption"><span>Member Signature</span><span>Date</span></div>
                    </div>
                    <div class="sig-line-block">
                        <div class="sig-line"></div>
                        <div class="sig-caption"><span>President Signature</span><span>Date</span></div>
                    </div>
                    <div class="sig-line-block">
                        <div class="sig-line"></div>
                        <div class="sig-caption"><span>Treasurer Signature</span><span>Date</span></div>
                    </div>
                    <div class="sig-line-block">
                        <div class="sig-line"></div>
                        <div class="sig-caption"><span>Witness (Member) Signature</span><span>Date</span></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const form = document.getElementById('contractForm');
const autosaveStatus = document.getElementById('autosaveStatus');
const historySelect = document.getElementById('history_id');
const dispositionSelect = document.getElementById('contract_disposition');
const dispositionBadge = document.getElementById('dispositionBadge');
const uploadedFileStatus = document.getElementById('uploadedFileStatus');
const uploadedFileLinkWrap = document.getElementById('uploadedFileLinkWrap');
const uploadBtn = document.getElementById('uploadBtn');
const uploadInput = document.getElementById('contract_scan');
const lockBanner = document.getElementById('lockBanner');
let autosaveTimer = null;
let currentSaveRequest = null;

function setStatus(message) {
    autosaveStatus.textContent = message;
}

function contractInfoComplete() {
    const requiredIds = ['house_name', 'member_name', 'date_issued', 'effective_date'];
    return requiredIds.every((id) => {
        const el = document.getElementById(id);
        return el && String(el.value || '').trim() !== '';
    });
}

function isLocked() {
    return String(document.getElementById('is_upload_locked').value) === '1';
}

function formDataFromForm() {
    const fd = new FormData(form);
    fd.append('ajax', 'autosave');
    return fd;
}

function queueAutosave() {
    if (isLocked()) {
        setStatus('Record locked. Only disposition may be changed.');
        window.clearTimeout(autosaveTimer);
        return;
    }
    if (!contractInfoComplete()) {
        setStatus('Auto-save will start once Contract Information is fully filled out.');
        window.clearTimeout(autosaveTimer);
        return;
    }
    setStatus('Saving...');
    window.clearTimeout(autosaveTimer);
    autosaveTimer = window.setTimeout(saveForm, 450);
}

async function saveForm() {
    if (isLocked()) {
        setStatus('Record locked. Only disposition may be changed.');
        return;
    }
    if (!contractInfoComplete()) {
        setStatus('Auto-save will start once Contract Information is fully filled out.');
        return;
    }

    if (currentSaveRequest) {
        currentSaveRequest.abort();
    }
    currentSaveRequest = new AbortController();

    try {
        const response = await fetch(location.href, {
            method: 'POST',
            body: formDataFromForm(),
            signal: currentSaveRequest.signal,
        });
        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Save failed');
        }
        if (data.id) {
            document.getElementById('id').value = data.id;
        }
        setStatus('Auto-saved: ' + data.saved_at);
        refreshHistoryOption(data.id);
    } catch (err) {
        if (err.name !== 'AbortError') {
            setStatus('Save error: ' + err.message);
        }
    }
}

function refreshHistoryOption(id) {
    if (!id) return;
    const member = document.getElementById('member_name').value || 'Unnamed Member';
    const house = document.getElementById('house_name').value || 'No House';
    const issued = document.getElementById('date_issued').value || 'No Date';
    const disposition = dispositionSelect.value || 'pending';
    const lockedText = isLocked() ? 'LOCKED' : 'OPEN';
    const label = `${member} | ${house} | ${issued} | ${disposition.toUpperCase()} | ${lockedText} | Updated just now`;

    let option = Array.from(historySelect.options).find((opt) => String(opt.value) === String(id));
    if (!option) {
        option = document.createElement('option');
        option.value = String(id);
        historySelect.appendChild(option);
    }
    option.textContent = label;
    historySelect.value = String(id);
}

function setDispositionBadge(value) {
    const map = {
        pending: 'Pending',
        fulfilled: 'Contract Fulfilled',
        dismissed: 'Member Dismissed',
        voided: 'Voided',
    };
    dispositionBadge.className = 'pill ' + value;
    dispositionBadge.textContent = map[value] || value;
}

function renderUploadedFile(filePath, fileName) {
    uploadedFileLinkWrap.innerHTML = '';
    if (!filePath) {
        uploadedFileStatus.textContent = 'No scanned contract uploaded yet.';
        return;
    }
    uploadedFileStatus.textContent = 'Scanned contract uploaded and locked.';
    const link = document.createElement('a');
    link.href = filePath;
    link.target = '_blank';
    link.rel = 'noopener';
    link.className = 'file-link';
    link.textContent = fileName || 'Open uploaded contract';
    uploadedFileLinkWrap.appendChild(link);
}

function applyLockState() {
    const locked = isLocked();
    document.querySelectorAll('[data-lockable="1"]').forEach((el) => {
        el.disabled = locked;
    });
    uploadInput.disabled = locked;
    uploadBtn.disabled = locked;
    lockBanner.style.display = locked ? 'block' : 'none';
    if (locked) {
        setStatus('Record locked. Only disposition may be changed.');
    }
}

async function uploadScan() {
    const id = document.getElementById('id').value;
    if (!id) {
        setStatus('Save the contract first before uploading the scanned copy.');
        return;
    }
    if (isLocked()) {
        setStatus('This record is already locked.');
        return;
    }
    if (!uploadInput.files || !uploadInput.files[0]) {
        setStatus('Choose a file to upload.');
        return;
    }

    const fd = new FormData();
    fd.append('ajax', 'upload_scan');
    fd.append('id', id);
    fd.append('contract_scan', uploadInput.files[0]);

    try {
        setStatus('Uploading scanned contract...');
        const response = await fetch(location.href, { method: 'POST', body: fd });
        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Upload failed');
        }
        document.getElementById('is_upload_locked').value = '1';
        document.getElementById('uploaded_file_path').value = data.file_path || '';
        document.getElementById('uploaded_file_name').value = data.file_name || '';
        renderUploadedFile(data.file_path || '', data.file_name || 'Uploaded contract');
        applyLockState();
        refreshHistoryOption(id);
        setStatus(data.message || 'Scanned contract uploaded and record locked.');
    } catch (err) {
        setStatus('Upload error: ' + err.message);
    }
}

async function updateDisposition() {
    const id = document.getElementById('id').value;
    if (!id) {
        setDispositionBadge(dispositionSelect.value);
        return;
    }
    const fd = new FormData();
    fd.append('ajax', 'update_disposition');
    fd.append('id', id);
    fd.append('contract_disposition', dispositionSelect.value);

    try {
        const response = await fetch(location.href, { method: 'POST', body: fd });
        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Failed to update disposition');
        }
        setDispositionBadge(data.contract_disposition || dispositionSelect.value);
        refreshHistoryOption(id);
        setStatus('Disposition updated.');
    } catch (err) {
        setStatus('Disposition update error: ' + err.message);
    }
}

async function loadRecord(id) {
    const fd = new FormData();
    fd.append('ajax', 'load');
    fd.append('id', id);

    const response = await fetch(location.href, { method: 'POST', body: fd });
    const data = await response.json();
    if (!response.ok || !data.ok) {
        throw new Error(data.message || 'Failed to load record');
    }

    const record = data.record;
    Object.keys(record).forEach((key) => {
        const input = document.getElementById(key);
        if (input) {
            input.value = record[key] ?? '';
        }
    });

    setDispositionBadge(record.contract_disposition || 'pending');
    renderUploadedFile(record.uploaded_file_path || '', record.uploaded_file_name || 'Uploaded contract');
    applyLockState();
    autoResizeAll();
    setStatus(isLocked() ? 'Loaded locked record #' + id : (contractInfoComplete() ? 'Loaded record #' + id : 'Auto-save will start once Contract Information is fully filled out.'));
}

function newRecord() {
    form.reset();
    document.getElementById('id').value = '';
    document.getElementById('purpose_text').value = '';
    document.getElementById('newcomer_terms').value = '';
    document.getElementById('financial_terms').value = '';
    document.getElementById('performance_terms').value = '';
    document.getElementById('limitations_terms').value = '';
    document.getElementById('relationship_terms').value = '';
    document.getElementById('consequences_text').value = '';
    document.getElementById('acknowledgement_text').value = '';
    document.getElementById('is_upload_locked').value = '0';
    document.getElementById('uploaded_file_path').value = '';
    document.getElementById('uploaded_file_name').value = '';
    dispositionSelect.value = 'pending';
    historySelect.value = '';
    uploadInput.value = '';
    renderUploadedFile('', '');
    setDispositionBadge('pending');
    applyLockState();
    autoResizeAll();
    setStatus('Auto-save will start once Contract Information is fully filled out.');
}

function autoResizeAll() {
    document.querySelectorAll('textarea').forEach((ta) => {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
    });
}

form.querySelectorAll('input[data-lockable="1"], textarea[data-lockable="1"]').forEach((el) => {
    el.addEventListener('input', queueAutosave);
    el.addEventListener('change', queueAutosave);
});

document.querySelectorAll('textarea').forEach((ta) => {
    const resize = () => {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
    };
    ta.addEventListener('input', resize);
    ta.addEventListener('change', resize);
    resize();
});

dispositionSelect.addEventListener('change', updateDisposition);

function loadSelectedContract() {
    const id = historySelect.value;
    if (!id) {
        setStatus('Select a contract to load.');
        return;
    }
    loadRecord(id).catch(err => setStatus('Load error: ' + err.message));
}

// Optional: keep auto-load disabled to force button use
historySelect.addEventListener('change', function () {
    // Intentionally empty - user must click Load Selected button
});

setDispositionBadge(dispositionSelect.value || 'pending');
renderUploadedFile('', '');
applyLockState();
autoResizeAll();
</script>
</body>
</html>
