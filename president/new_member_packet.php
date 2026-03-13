<?php
/**
 * Oxford House New Member Packet
 * Single-file PHP app
 * - Fillable packet closely matching the uploaded PDF
 * - Auto-save to MySQL
 * - History dropdown filtered by member name
 * - Reload/edit prior records
 * - Upload scanned copy/PDF/image per record
 * - When a saved record has an uploaded copy, the history view shows the uploaded copy instead of the form
 * - Print button
 * - Auto-calculated totals on checklist page
 *
 * Uploaded source reference:
 * [NEW] New Member Packet.pdf
 */
declare(strict_types=1);

require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const UPLOAD_DIR = __DIR__ . '/uploads/new_member_packet';
const UPLOAD_WEB_DIR = 'uploads/new_member_packet';
const MAX_NOTIFY_ROWS = 4;
const MAX_PROPERTY_ROWS = 18;

function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function moneyFormat(mixed $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function emptyPacket(): array
{
    return [
        'id' => 0,
        'house_name' => '',
        'member_name' => '',
        'move_in_date' => '',
        'packet_date' => '',
        'check_membership_application' => 0,
        'check_house_manual' => 0,
        'check_house_guidelines' => 0,
        'check_membership_agreement' => 0,
        'check_plan_for_recovery' => 0,
        'check_relapse_contingency' => 0,
        'check_medical_release' => 0,
        'check_property_list' => 0,
        'member_initials_1' => '',
        'president_initials_1' => '',
        'member_initials_2' => '',
        'president_initials_2' => '',
        'member_initials_3' => '',
        'president_initials_3' => '',
        'member_initials_4' => '',
        'president_initials_4' => '',
        'member_initials_5' => '',
        'president_initials_5' => '',
        'member_initials_6' => '',
        'president_initials_6' => '',
        'member_initials_7' => '',
        'president_initials_7' => '',
        'member_initials_8' => '',
        'president_initials_8' => '',
        'member_signature_1' => '',
        'member_signature_date_1' => '',
        'president_signature_1' => '',
        'president_signature_date_1' => '',
        'membership_agreement_text' => '',
        'agreement_member_name' => '',
        'agreement_member_signature' => '',
        'agreement_member_date' => '',
        'agreement_president_name' => '',
        'agreement_president_signature' => '',
        'agreement_president_date' => '',
        'plan_name' => '',
        'plan_text' => '',
        'aftercare_program' => '',
        'has_sponsor' => '',
        'sponsor_by_date' => '',
        'meetings_per_week' => '',
        'meeting_types' => '',
        'plan_signature' => '',
        'plan_signature_date' => '',
        'plan_president_signature' => '',
        'plan_president_date' => '',
        'relapse_name' => '',
        'relapse_family' => 0,
        'relapse_friend' => 0,
        'relapse_detox' => 0,
        'relapse_other' => 0,
        'relapse_other_text' => '',
        'relapse_details' => '',
        'notify_rows_json' => '[]',
        'pickup_rows_json' => '[]',
        'relapse_member_signature' => '',
        'relapse_member_date' => '',
        'relapse_president_signature' => '',
        'relapse_president_date' => '',
        'relapse_witness_signature' => '',
        'relapse_witness_date' => '',
        'medical_name' => '',
        'physician_name' => '',
        'physician_phone' => '',
        'hospital_clinic' => '',
        'insurance_info' => '',
        'allergies' => '',
        'medications' => '',
        'medical_history' => '',
        'dob' => '',
        'blood_type' => '',
        'medical_contacts_json' => '[]',
        'medical_signature' => '',
        'medical_date' => '',
        'property_name' => '',
        'property_move_in_date' => '',
        'property_rows_json' => '[]',
        'uploaded_copy_name' => '',
        'uploaded_copy_path' => '',
        'uploaded_copy_mime' => '',
        'updated_at' => '',
    ];
}

function normalizeRows(?string $json, int $count, array $keys): array
{
    $rows = json_decode((string)$json, true);
    if (!is_array($rows)) {
        $rows = [];
    }
    $normalized = [];
    for ($i = 0; $i < $count; $i++) {
        $src = is_array($rows[$i] ?? null) ? $rows[$i] : [];
        $row = [];
        foreach ($keys as $key) {
            $row[$key] = (string)($src[$key] ?? '');
        }
        $normalized[] = $row;
    }
    return $normalized;
}

function cleanDate(?string $value): string
{
    $value = trim((string)$value);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
}

function createUploadDir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
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
    http_response_code(500);
    die('Database connection failed: ' . h($e->getMessage()));
}

createUploadDir();

$pdo->exec("CREATE TABLE IF NOT EXISTS oxford_new_member_packets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    member_name VARCHAR(255) NOT NULL DEFAULT '',
    move_in_date DATE NULL,
    packet_date DATE NULL,

    check_membership_application TINYINT(1) NOT NULL DEFAULT 0,
    check_house_manual TINYINT(1) NOT NULL DEFAULT 0,
    check_house_guidelines TINYINT(1) NOT NULL DEFAULT 0,
    check_membership_agreement TINYINT(1) NOT NULL DEFAULT 0,
    check_plan_for_recovery TINYINT(1) NOT NULL DEFAULT 0,
    check_relapse_contingency TINYINT(1) NOT NULL DEFAULT 0,
    check_medical_release TINYINT(1) NOT NULL DEFAULT 0,
    check_property_list TINYINT(1) NOT NULL DEFAULT 0,

    member_initials_1 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_1 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_2 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_2 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_3 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_3 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_4 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_4 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_5 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_5 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_6 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_6 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_7 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_7 VARCHAR(20) NOT NULL DEFAULT '',
    member_initials_8 VARCHAR(20) NOT NULL DEFAULT '',
    president_initials_8 VARCHAR(20) NOT NULL DEFAULT '',

    member_signature_1 VARCHAR(255) NOT NULL DEFAULT '',
    member_signature_date_1 DATE NULL,
    president_signature_1 VARCHAR(255) NOT NULL DEFAULT '',
    president_signature_date_1 DATE NULL,

    membership_agreement_text LONGTEXT NULL,
    agreement_member_name VARCHAR(255) NOT NULL DEFAULT '',
    agreement_member_signature VARCHAR(255) NOT NULL DEFAULT '',
    agreement_member_date DATE NULL,
    agreement_president_name VARCHAR(255) NOT NULL DEFAULT '',
    agreement_president_signature VARCHAR(255) NOT NULL DEFAULT '',
    agreement_president_date DATE NULL,

    plan_name VARCHAR(255) NOT NULL DEFAULT '',
    plan_text LONGTEXT NULL,
    aftercare_program LONGTEXT NULL,
    has_sponsor VARCHAR(10) NOT NULL DEFAULT '',
    sponsor_by_date DATE NULL,
    meetings_per_week VARCHAR(50) NOT NULL DEFAULT '',
    meeting_types VARCHAR(255) NOT NULL DEFAULT '',
    plan_signature VARCHAR(255) NOT NULL DEFAULT '',
    plan_signature_date DATE NULL,
    plan_president_signature VARCHAR(255) NOT NULL DEFAULT '',
    plan_president_date DATE NULL,

    relapse_name VARCHAR(255) NOT NULL DEFAULT '',
    relapse_family TINYINT(1) NOT NULL DEFAULT 0,
    relapse_friend TINYINT(1) NOT NULL DEFAULT 0,
    relapse_detox TINYINT(1) NOT NULL DEFAULT 0,
    relapse_other TINYINT(1) NOT NULL DEFAULT 0,
    relapse_other_text VARCHAR(255) NOT NULL DEFAULT '',
    relapse_details LONGTEXT NULL,
    notify_rows_json LONGTEXT NULL,
    pickup_rows_json LONGTEXT NULL,
    relapse_member_signature VARCHAR(255) NOT NULL DEFAULT '',
    relapse_member_date DATE NULL,
    relapse_president_signature VARCHAR(255) NOT NULL DEFAULT '',
    relapse_president_date DATE NULL,
    relapse_witness_signature VARCHAR(255) NOT NULL DEFAULT '',
    relapse_witness_date DATE NULL,

    medical_name VARCHAR(255) NOT NULL DEFAULT '',
    physician_name VARCHAR(255) NOT NULL DEFAULT '',
    physician_phone VARCHAR(50) NOT NULL DEFAULT '',
    hospital_clinic VARCHAR(255) NOT NULL DEFAULT '',
    insurance_info VARCHAR(255) NOT NULL DEFAULT '',
    allergies LONGTEXT NULL,
    medications LONGTEXT NULL,
    medical_history LONGTEXT NULL,
    dob DATE NULL,
    blood_type VARCHAR(20) NOT NULL DEFAULT '',
    medical_contacts_json LONGTEXT NULL,
    medical_signature VARCHAR(255) NOT NULL DEFAULT '',
    medical_date DATE NULL,

    property_name VARCHAR(255) NOT NULL DEFAULT '',
    property_move_in_date DATE NULL,
    property_rows_json LONGTEXT NULL,

    uploaded_copy_name VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_copy_path VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_copy_mime VARCHAR(100) NOT NULL DEFAULT '',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member_name (member_name),
    INDEX idx_house_member (house_name, member_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

function findPacket(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM oxford_new_member_packets WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function packetFromPost(): array
{
    $data = emptyPacket();
    $data['id'] = (int)($_POST['id'] ?? 0);
    $fields = [
        'house_name','member_name','packet_date','move_in_date',
        'member_initials_1','president_initials_1','member_initials_2','president_initials_2','member_initials_3','president_initials_3',
        'member_initials_4','president_initials_4','member_initials_5','president_initials_5','member_initials_6','president_initials_6',
        'member_initials_7','president_initials_7','member_initials_8','president_initials_8',
        'member_signature_1','member_signature_date_1','president_signature_1','president_signature_date_1',
        'membership_agreement_text','agreement_member_name','agreement_member_signature','agreement_member_date',
        'agreement_president_name','agreement_president_signature','agreement_president_date',
        'plan_name','plan_text','aftercare_program','has_sponsor','sponsor_by_date','meetings_per_week','meeting_types',
        'plan_signature','plan_signature_date','plan_president_signature','plan_president_date',
        'relapse_name','relapse_other_text','relapse_details','relapse_member_signature','relapse_member_date',
        'relapse_president_signature','relapse_president_date','relapse_witness_signature','relapse_witness_date',
        'medical_name','physician_name','physician_phone','hospital_clinic','insurance_info','allergies','medications',
        'medical_history','dob','blood_type','medical_signature','medical_date',
        'property_name','property_move_in_date'
    ];
    foreach ($fields as $field) {
        $data[$field] = trim((string)($_POST[$field] ?? ''));
    }

    foreach (['check_membership_application','check_house_manual','check_house_guidelines','check_membership_agreement','check_plan_for_recovery','check_relapse_contingency','check_medical_release','check_property_list','relapse_family','relapse_friend','relapse_detox','relapse_other'] as $flag) {
        $data[$flag] = isset($_POST[$flag]) ? 1 : 0;
    }

    $notifyRows = [];
    for ($i = 0; $i < MAX_NOTIFY_ROWS; $i++) {
        $notifyRows[] = [
            'name' => trim((string)($_POST['notify_name'][$i] ?? '')),
            'phone' => trim((string)($_POST['notify_phone'][$i] ?? '')),
            'relationship' => trim((string)($_POST['notify_relationship'][$i] ?? '')),
        ];
    }
    $pickupRows = [];
    for ($i = 0; $i < MAX_NOTIFY_ROWS; $i++) {
        $pickupRows[] = [
            'name' => trim((string)($_POST['pickup_name'][$i] ?? '')),
            'phone' => trim((string)($_POST['pickup_phone'][$i] ?? '')),
            'relationship' => trim((string)($_POST['pickup_relationship'][$i] ?? '')),
        ];
    }
    $medicalRows = [];
    for ($i = 0; $i < 3; $i++) {
        $medicalRows[] = [
            'name' => trim((string)($_POST['medical_contact_name'][$i] ?? '')),
            'phone' => trim((string)($_POST['medical_contact_phone'][$i] ?? '')),
            'relationship' => trim((string)($_POST['medical_contact_relationship'][$i] ?? '')),
        ];
    }
    $propertyRows = [];
    for ($i = 0; $i < MAX_PROPERTY_ROWS; $i++) {
        $propertyRows[] = [
            'date' => trim((string)($_POST['property_date'][$i] ?? '')),
            'description' => trim((string)($_POST['property_description'][$i] ?? '')),
            'initials' => trim((string)($_POST['property_initials'][$i] ?? '')),
        ];
    }

    $data['notify_rows_json'] = json_encode($notifyRows, JSON_UNESCAPED_UNICODE);
    $data['pickup_rows_json'] = json_encode($pickupRows, JSON_UNESCAPED_UNICODE);
    $data['medical_contacts_json'] = json_encode($medicalRows, JSON_UNESCAPED_UNICODE);
    $data['property_rows_json'] = json_encode($propertyRows, JSON_UNESCAPED_UNICODE);
    return $data;
}

function savePacket(PDO $pdo, array $data, ?array $existing = null): int
{
    $payload = [
        trim($data['house_name']),
        trim($data['member_name']),
        cleanDate($data['move_in_date']) ?: null,
        cleanDate($data['packet_date']) ?: null,
        (int)$data['check_membership_application'],
        (int)$data['check_house_manual'],
        (int)$data['check_house_guidelines'],
        (int)$data['check_membership_agreement'],
        (int)$data['check_plan_for_recovery'],
        (int)$data['check_relapse_contingency'],
        (int)$data['check_medical_release'],
        (int)$data['check_property_list'],
        trim($data['member_initials_1']), trim($data['president_initials_1']),
        trim($data['member_initials_2']), trim($data['president_initials_2']),
        trim($data['member_initials_3']), trim($data['president_initials_3']),
        trim($data['member_initials_4']), trim($data['president_initials_4']),
        trim($data['member_initials_5']), trim($data['president_initials_5']),
        trim($data['member_initials_6']), trim($data['president_initials_6']),
        trim($data['member_initials_7']), trim($data['president_initials_7']),
        trim($data['member_initials_8']), trim($data['president_initials_8']),
        trim($data['member_signature_1']), cleanDate($data['member_signature_date_1']) ?: null,
        trim($data['president_signature_1']), cleanDate($data['president_signature_date_1']) ?: null,
        trim($data['membership_agreement_text']),
        trim($data['agreement_member_name']), trim($data['agreement_member_signature']), cleanDate($data['agreement_member_date']) ?: null,
        trim($data['agreement_president_name']), trim($data['agreement_president_signature']), cleanDate($data['agreement_president_date']) ?: null,
        trim($data['plan_name']), trim($data['plan_text']), trim($data['aftercare_program']), trim($data['has_sponsor']),
        cleanDate($data['sponsor_by_date']) ?: null, trim($data['meetings_per_week']), trim($data['meeting_types']),
        trim($data['plan_signature']), cleanDate($data['plan_signature_date']) ?: null,
        trim($data['plan_president_signature']), cleanDate($data['plan_president_date']) ?: null,
        trim($data['relapse_name']), (int)$data['relapse_family'], (int)$data['relapse_friend'], (int)$data['relapse_detox'], (int)$data['relapse_other'], trim($data['relapse_other_text']), trim($data['relapse_details']),
        $data['notify_rows_json'], $data['pickup_rows_json'],
        trim($data['relapse_member_signature']), cleanDate($data['relapse_member_date']) ?: null,
        trim($data['relapse_president_signature']), cleanDate($data['relapse_president_date']) ?: null,
        trim($data['relapse_witness_signature']), cleanDate($data['relapse_witness_date']) ?: null,
        trim($data['medical_name']), trim($data['physician_name']), trim($data['physician_phone']), trim($data['hospital_clinic']), trim($data['insurance_info']), trim($data['allergies']), trim($data['medications']), trim($data['medical_history']),
        cleanDate($data['dob']) ?: null, trim($data['blood_type']), $data['medical_contacts_json'], trim($data['medical_signature']), cleanDate($data['medical_date']) ?: null,
        trim($data['property_name']), cleanDate($data['property_move_in_date']) ?: null, $data['property_rows_json'],
        $existing['uploaded_copy_name'] ?? '', $existing['uploaded_copy_path'] ?? '', $existing['uploaded_copy_mime'] ?? ''
    ];

    if ($existing) {
        $sql = "UPDATE oxford_new_member_packets SET
            house_name=?, member_name=?, move_in_date=?, packet_date=?,
            check_membership_application=?, check_house_manual=?, check_house_guidelines=?, check_membership_agreement=?,
            check_plan_for_recovery=?, check_relapse_contingency=?, check_medical_release=?, check_property_list=?,
            member_initials_1=?, president_initials_1=?, member_initials_2=?, president_initials_2=?, member_initials_3=?, president_initials_3=?,
            member_initials_4=?, president_initials_4=?, member_initials_5=?, president_initials_5=?, member_initials_6=?, president_initials_6=?,
            member_initials_7=?, president_initials_7=?, member_initials_8=?, president_initials_8=?,
            member_signature_1=?, member_signature_date_1=?, president_signature_1=?, president_signature_date_1=?,
            membership_agreement_text=?, agreement_member_name=?, agreement_member_signature=?, agreement_member_date=?,
            agreement_president_name=?, agreement_president_signature=?, agreement_president_date=?,
            plan_name=?, plan_text=?, aftercare_program=?, has_sponsor=?, sponsor_by_date=?, meetings_per_week=?, meeting_types=?,
            plan_signature=?, plan_signature_date=?, plan_president_signature=?, plan_president_date=?,
            relapse_name=?, relapse_family=?, relapse_friend=?, relapse_detox=?, relapse_other=?, relapse_other_text=?, relapse_details=?,
            notify_rows_json=?, pickup_rows_json=?,
            relapse_member_signature=?, relapse_member_date=?, relapse_president_signature=?, relapse_president_date=?, relapse_witness_signature=?, relapse_witness_date=?,
            medical_name=?, physician_name=?, physician_phone=?, hospital_clinic=?, insurance_info=?, allergies=?, medications=?, medical_history=?, dob=?, blood_type=?, medical_contacts_json=?, medical_signature=?, medical_date=?,
            property_name=?, property_move_in_date=?, property_rows_json=?,
            uploaded_copy_name=?, uploaded_copy_path=?, uploaded_copy_mime=?
            WHERE id=? LIMIT 1";
        $payload[] = (int)$existing['id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($payload);
        return (int)$existing['id'];
    }

    $sql = "INSERT INTO oxford_new_member_packets (
        house_name, member_name, move_in_date, packet_date,
        check_membership_application, check_house_manual, check_house_guidelines, check_membership_agreement,
        check_plan_for_recovery, check_relapse_contingency, check_medical_release, check_property_list,
        member_initials_1, president_initials_1, member_initials_2, president_initials_2, member_initials_3, president_initials_3,
        member_initials_4, president_initials_4, member_initials_5, president_initials_5, member_initials_6, president_initials_6,
        member_initials_7, president_initials_7, member_initials_8, president_initials_8,
        member_signature_1, member_signature_date_1, president_signature_1, president_signature_date_1,
        membership_agreement_text, agreement_member_name, agreement_member_signature, agreement_member_date,
        agreement_president_name, agreement_president_signature, agreement_president_date,
        plan_name, plan_text, aftercare_program, has_sponsor, sponsor_by_date, meetings_per_week, meeting_types,
        plan_signature, plan_signature_date, plan_president_signature, plan_president_date,
        relapse_name, relapse_family, relapse_friend, relapse_detox, relapse_other, relapse_other_text, relapse_details,
        notify_rows_json, pickup_rows_json,
        relapse_member_signature, relapse_member_date, relapse_president_signature, relapse_president_date, relapse_witness_signature, relapse_witness_date,
        medical_name, physician_name, physician_phone, hospital_clinic, insurance_info, allergies, medications, medical_history, dob, blood_type, medical_contacts_json, medical_signature, medical_date,
        property_name, property_move_in_date, property_rows_json,
        uploaded_copy_name, uploaded_copy_path, uploaded_copy_mime
    ) VALUES (" . rtrim(str_repeat('?,', count($payload)), ',') . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($payload);
    return (int)$pdo->lastInsertId();
}

function saveUpload(PDO $pdo, int $id, array $existing): ?string
{
    if (!isset($_FILES['uploaded_copy']) || !is_array($_FILES['uploaded_copy'])) {
        return null;
    }
    $file = $_FILES['uploaded_copy'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Upload failed.';
    }
    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    if (!isset($allowed[$mime])) {
        return 'Only PDF, JPG, PNG, and WEBP files are allowed.';
    }
    $ext = $allowed[$mime];
    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '_', pathinfo((string)$file['name'], PATHINFO_FILENAME));
    $filename = 'packet_' . $id . '_' . date('Ymd_His') . '_' . ($base ?: 'upload') . '.' . $ext;
    $target = UPLOAD_DIR . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return 'Unable to save uploaded file.';
    }
    if (!empty($existing['uploaded_copy_path'])) {
        $old = __DIR__ . '/' . ltrim((string)$existing['uploaded_copy_path'], '/');
        if (is_file($old)) {
            @unlink($old);
        }
    }
    $webPath = UPLOAD_WEB_DIR . '/' . $filename;
    $stmt = $pdo->prepare('UPDATE oxford_new_member_packets SET uploaded_copy_name=?, uploaded_copy_path=?, uploaded_copy_mime=? WHERE id=? LIMIT 1');
    $stmt->execute([(string)$file['name'], $webPath, $mime, $id]);
    return null;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'history') {
    header('Content-Type: application/json; charset=utf-8');
    $member = trim((string)($_GET['member_name'] ?? ''));
    if ($member === '') {
        echo json_encode(['ok' => true, 'items' => []]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT id, member_name, house_name, packet_date, move_in_date, uploaded_copy_name, updated_at FROM oxford_new_member_packets WHERE member_name LIKE ? ORDER BY updated_at DESC LIMIT 100');
    $stmt->execute(["%{$member}%"]);
    echo json_encode(['ok' => true, 'items' => $stmt->fetchAll()]);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_GET['id'] ?? 0);
    $packet = $id > 0 ? findPacket($pdo, $id) : null;
    echo json_encode(['ok' => (bool)$packet, 'packet' => $packet]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? 'save');
    $incoming = packetFromPost();
    $existing = $incoming['id'] > 0 ? findPacket($pdo, $incoming['id']) : null;
    $packetId = savePacket($pdo, $incoming, $existing);
    $saved = findPacket($pdo, $packetId) ?: emptyPacket();

    if ($action === 'save_upload' || (isset($_FILES['uploaded_copy']) && ($_FILES['uploaded_copy']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)) {
        $uploadError = saveUpload($pdo, $packetId, $saved);
        if ($uploadError !== null) {
            $saved['flash_error'] = $uploadError;
        }
        $saved = findPacket($pdo, $packetId) ?: $saved;
    }

    if (isset($_POST['ajax_save']) && $_POST['ajax_save'] === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'id' => $packetId,
            'updated_at' => $saved['updated_at'] ?? date('Y-m-d H:i:s'),
            'has_upload' => !empty($saved['uploaded_copy_path']),
            'upload_path' => $saved['uploaded_copy_path'] ?? ''
        ]);
        exit;
    }

    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?id=' . $packetId . '&saved=1');
    exit;
}

$current = emptyPacket();
if (isset($_GET['id'])) {
    $loaded = findPacket($pdo, (int)$_GET['id']);
    if ($loaded) {
        $current = array_merge($current, $loaded);
    }
}

$notifyRows = normalizeRows($current['notify_rows_json'], MAX_NOTIFY_ROWS, ['name','phone','relationship']);
$pickupRows = normalizeRows($current['pickup_rows_json'], MAX_NOTIFY_ROWS, ['name','phone','relationship']);
$medicalRows = normalizeRows($current['medical_contacts_json'], 3, ['name','phone','relationship']);
$propertyRows = normalizeRows($current['property_rows_json'], MAX_PROPERTY_ROWS, ['date','description','initials']);
$checkTotal = (int)$current['check_membership_application'] + (int)$current['check_house_manual'] + (int)$current['check_house_guidelines'] + (int)$current['check_membership_agreement'] + (int)$current['check_plan_for_recovery'] + (int)$current['check_relapse_contingency'] + (int)$current['check_medical_release'] + (int)$current['check_property_list'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Member Packet</title>
<style>
    :root{
        --ink:#111;
        --line:#222;
        --muted:#666;
        --bg:#f3f3f3;
        --white:#fff;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#e9ecef;color:var(--ink)}
    .topbar{position:sticky;top:0;z-index:50;background:#1d1f23;color:#fff;padding:12px 18px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;box-shadow:0 2px 10px rgba(0,0,0,.18)}
    .topbar .title{font-weight:700;font-size:18px;margin-right:auto}
    .topbar input,.topbar select,.topbar button{padding:9px 10px;border-radius:6px;border:1px solid #c9c9c9;font-size:14px}
    .topbar button{background:#fff;cursor:pointer}
    .topbar .status{font-size:13px;opacity:.9}
    .container{max-width:980px;margin:24px auto;padding:0 12px 36px}
    .page{background:var(--bg);border:1px solid #cfcfcf;box-shadow:0 3px 14px rgba(0,0,0,.08);width:100%;min-height:1270px;margin:0 auto 24px;padding:20px 22px 28px;position:relative}
    .doc-header{text-align:center;margin-bottom:8px}
    .logo{height:56px;object-fit:contain;display:block;margin:0 auto 8px}
    .oxford-line{font-weight:700;font-size:26px;letter-spacing:.3px}
    .subhead{font-weight:700;font-size:30px;line-height:1.1;text-transform:uppercase}
    .copyright{font-size:13px;margin-top:4px}
    .house-line{display:inline-block;min-width:260px;border-bottom:2px solid var(--ink);padding:0 8px 2px;text-align:left}
    .field-row{display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;margin:8px 0}
    .field{display:flex;align-items:flex-end;gap:8px;flex:1;min-width:220px}
    .field label{font-weight:700;white-space:nowrap}
    .line-input,.line-textarea,.line-select{width:100%;border:none;border-bottom:2px solid var(--ink);background:transparent;padding:4px 2px 3px;font-size:16px;outline:none}
    .line-textarea{resize:none;min-height:64px;border:1px solid #bdbdbd;background:#f9f9f9;padding:8px}
    .center{text-align:center}
    table{width:100%;border-collapse:collapse}
    .checklist-table th,.checklist-table td,.grid-table th,.grid-table td{border:1px solid var(--line);padding:6px 8px;vertical-align:middle}
    .checklist-table th,.grid-table th{background:#ededed}
    .checklist-table .itemcol{width:62%}
    .checkbox{width:18px;height:18px;vertical-align:middle}
    .small-input{width:100%;border:none;background:transparent;font-size:15px;outline:none;text-align:center}
    .legal{font-size:18px;line-height:1.45;margin-top:14px}
    .legal p{margin:0 0 12px}
    .legal .roman{margin:0 0 0 18px;padding:0}
    .legal .roman li{margin-bottom:8px}
    .lined-block{border-bottom:2px solid #666;min-height:32px;padding:4px 0}
    .multi-lines{background:repeating-linear-gradient(to bottom, transparent 0, transparent 30px, #555 30px, #555 32px);min-height:240px;padding:4px}
    .subtle{font-size:14px;color:var(--muted)}
    .tiny{font-size:12px}
    .sig-row{display:grid;grid-template-columns:1fr 190px;gap:16px;margin-top:18px;align-items:end}
    .sig-pair{display:grid;grid-template-columns:1fr 200px;gap:16px;align-items:end;margin-top:14px}
    .footer-house{position:absolute;bottom:10px;left:22px;font-weight:700;letter-spacing:.7px}
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .viewer{background:#fff;border:1px solid #cfcfcf;padding:18px;box-shadow:0 3px 12px rgba(0,0,0,.08)}
    .viewer iframe,.viewer img{width:100%;min-height:900px;border:none;background:#fff}
    .viewer h2{margin-top:0}
    .totals-box{margin-top:10px;display:flex;justify-content:flex-end;gap:24px;font-weight:700}
    .print-hide{display:block}
    @media print{
        body{background:#fff}
        .topbar,.print-hide{display:none !important}
        .container{max-width:none;margin:0;padding:0}
        .page{box-shadow:none;border:none;page-break-after:always;margin:0 auto;padding:14px 18px;min-height:0}
    }
</style>
</head>
<body>
<div class="topbar print-hide">
    <div class="title">New Member Packet</div>
    <label>Member Name</label>
    <input type="text" id="historyMemberName" value="<?= h($current['member_name']) ?>" placeholder="Search history by member name">
    <select id="historySelect">
        <option value="">History records</option>
    </select>
    <button type="button" id="loadHistoryBtn">Load</button>
    <button type="button" onclick="window.print()">Print</button>
    <span class="status" id="saveStatus"><?= isset($_GET['saved']) ? 'Saved.' : 'Ready.' ?></span>
</div>

<div class="container">
<form id="packetForm" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" id="recordId" value="<?= (int)$current['id'] ?>">
    <input type="hidden" name="action" id="formAction" value="save">

    <?php if (!empty($current['uploaded_copy_path'])): ?>
        <div class="viewer page print-hide" style="min-height:auto;">
            <h2>Uploaded Copy on File</h2>
            <p><strong>Showing uploaded copy instead of the form for this saved record.</strong></p>
            <p>File: <?= h($current['uploaded_copy_name']) ?></p>
            <?php if (str_starts_with((string)$current['uploaded_copy_mime'], 'image/')): ?>
                <img src="<?= h($current['uploaded_copy_path']) ?>" alt="Uploaded copy">
            <?php else: ?>
                <iframe src="<?= h($current['uploaded_copy_path']) ?>"></iframe>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div id="formPages" style="<?= !empty($current['uploaded_copy_path']) ? 'display:none;' : '' ?>">
        <section class="page">
            <div class="doc-header">
                <img class="logo" src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo" onerror="this.style.display='none'">
                <div class="oxford-line">OXFORD HOUSE - <span class="house-line"><input class="line-input" name="house_name" value="<?= h($current['house_name']) ?>"></span></div>
                <div class="subhead">NEW MEMBER PACKET</div>
                <div class="copyright">© Oxford House, Inc V1.0 2023</div>
            </div>

            <div class="field-row">
                <div class="field"><label>Name:</label><input class="line-input autosave member-name-source" name="member_name" value="<?= h($current['member_name']) ?>"></div>
            </div>

            <div class="field-row">
                <div class="field"><label>Date of Move-in:</label><input type="date" class="line-input autosave" name="move_in_date" value="<?= h($current['move_in_date']) ?>"></div>
                <div class="field"><label>Packet Date:</label><input type="date" class="line-input autosave" name="packet_date" value="<?= h($current['packet_date']) ?>"></div>
            </div>

            <div class="center" style="margin:16px 0 10px;font-weight:700;font-size:22px;">CHECKLIST</div>
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th class="itemcol">Item</th>
                        <th>Done</th>
                        <th>Member Initials</th>
                        <th>President Initials</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $items = [
                        ['Membership Application completed and signed', 'check_membership_application', 1],
                        ['House Manual provided and reviewed', 'check_house_manual', 2],
                        ['House Guidelines provided and reviewed', 'check_house_guidelines', 3],
                        ['Membership Agreement read and signed', 'check_membership_agreement', 4],
                        ['Plan for Recovery completed and signed', 'check_plan_for_recovery', 5],
                        ['Relapse Contingency completed and signed', 'check_relapse_contingency', 6],
                        ['Emergency Medical Release completed and signed', 'check_medical_release', 7],
                        ['Property list completed and signed', 'check_property_list', 8],
                    ];
                    foreach ($items as [$label,$flag,$idx]):
                    ?>
                    <tr>
                        <td><?= h($label) ?></td>
                        <td class="center"><input type="checkbox" class="checkbox autosave calc-check" name="<?= h($flag) ?>" <?= !empty($current[$flag]) ? 'checked' : '' ?>></td>
                        <td><input class="small-input autosave" name="member_initials_<?= $idx ?>" value="<?= h($current['member_initials_' . $idx]) ?>"></td>
                        <td><input class="small-input autosave" name="president_initials_<?= $idx ?>" value="<?= h($current['president_initials_' . $idx]) ?>"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="totals-box"><div>Total Completed: <span id="checkTotal"><?= $checkTotal ?></span> / 8</div></div>

            <div class="sig-pair">
                <div class="field"><label>Member Signature:</label><input class="line-input autosave" name="member_signature_1" value="<?= h($current['member_signature_1']) ?>"></div>
                <div class="field"><label>Date:</label><input type="date" class="line-input autosave" name="member_signature_date_1" value="<?= h($current['member_signature_date_1']) ?>"></div>
            </div>
            <div class="sig-pair">
                <div class="field"><label>President Signature:</label><input class="line-input autosave" name="president_signature_1" value="<?= h($current['president_signature_1']) ?>"></div>
                <div class="field"><label>Date:</label><input type="date" class="line-input autosave" name="president_signature_date_1" value="<?= h($current['president_signature_date_1']) ?>"></div>
            </div>
            <div class="footer-house">OXFORD HOUSE</div>
        </section>

        <section class="page">
            <div class="doc-header">
                <div class="oxford-line">OXFORD HOUSE - <span class="house-line"><input class="line-input autosave" name="house_name" value="<?= h($current['house_name']) ?>"></span></div>
                <div class="subhead">MEMBERSHIP AGREEMENT</div>
                <div class="copyright">© Oxford House, Inc V1.0 2023</div>
            </div>
            <div class="legal" style="font-size:17px;">
                <p>I, <input class="line-input autosave" style="display:inline-block;width:320px" name="agreement_member_name" value="<?= h($current['agreement_member_name']) ?>">, as a member of this Oxford House, agree to abide by the Oxford House Model and System of Operations, the Lease, and the guidelines for this House. I understand that if the House determines I have had a recurrence of use, I will be expelled from the house, effective immediately.</p>
                <p>A recurrence of use will be determined by a majority vote of the House members. A urinalysis/breath analyzer is not required, although refusal to submit to one, if asked by the House or Chapter, will be considered an admission of a recurrence of use. Absense from the house for longer than three days that is not pre-arranged may be considered a recurrence of use, and if done so, the house may vote that a recurrence of use has occured and expel me in my absence. I understand that otherwise, I have a right to be present at any house meeting addressing my possible recurrence of use and I have the right to participate in the vote.</p>
                <p>I understand that criminal activity, physical violence, threats of physical violence, allowing a guest in the house who is under the influence of drugs or alcohol, and failure to bring a house member’s recurrence of use to the attention of the house will cause me to be expelled for disruptive behavior effective immediately. I understand that if I am placed on a disruptive behavior contract (including for non-payment of Equal Expense Share (EES)) and violate the terms of that contract, I may be expelled for disruptive behavior effective immediately.</p>
                <p>In case of expulsion, or if I move out without notice, any unused portion of my EES will be returned to me as soon as is reasonably possible, but no later than 30 days of my departure. If any portion of my EES has been paid by a third party, I understand that the unused EES will be returned to that organization or individual.</p>
                <p>I also agree to the following terms as to the disposition of my personal belongings if I am expelled or voluntarily move out of the house without removing my possessions.</p>
                <p>I understand and accept the above procedures as a guideline of this Oxford House.</p>
                <ol class="roman">
                    <li>I am expected to remove my property from the house within 72 hours. During this time the House will not do anything with my property except in case of emergency. If unable, I may authorize a third party to remove my belongings. A signed, written authorization must be given to the house membership prior to a third-party taking possession of my property.</li>
                    <li>After 72 hours, the House members will pack up and store my belongings up to 30 days from my departure.</li>
                    <li>If I have not removed my property within 30 days or made other arrangements satisfactory to the majority of the House membership, my possessions will be disposed of and/or donated to a charitable organization.</li>
                </ol>
                <p>I realize that the Oxford House in which I reside has been established in compliance with the conditions of § 2036 of the Federal Anti-Drug Abuse Act of 1988, P.L. 100-690, as amended, which provides that federal money loaned to start the house requires the house residents to: prohibit all residents from using any alcohol or illegal drugs; expel any resident who violates such prohibition; equally share household expenses including the monthly lease payment, among all residents; and utilize democratic decision making within the group including inclusion in and expulsion from the group. In accepting these terms, the member excludes himself or herself from the normal due process afforded by local landlord-tenant laws.</p>
            </div>
            <div class="sig-pair"><div class="field"><label>House Member Name</label><input class="line-input autosave" name="agreement_member_name" value="<?= h($current['agreement_member_name']) ?>"></div><div class="field"><label>Date</label><input type="date" class="line-input autosave" name="agreement_member_date" value="<?= h($current['agreement_member_date']) ?>"></div></div>
            <div class="sig-pair"><div class="field"><label>House Member Signature</label><input class="line-input autosave" name="agreement_member_signature" value="<?= h($current['agreement_member_signature']) ?>"></div><div></div></div>
            <div class="sig-pair"><div class="field"><label>House President Name</label><input class="line-input autosave" name="agreement_president_name" value="<?= h($current['agreement_president_name']) ?>"></div><div class="field"><label>Date</label><input type="date" class="line-input autosave" name="agreement_president_date" value="<?= h($current['agreement_president_date']) ?>"></div></div>
            <div class="sig-pair"><div class="field"><label>House President Signature</label><input class="line-input autosave" name="agreement_president_signature" value="<?= h($current['agreement_president_signature']) ?>"></div><div></div></div>
            <div class="footer-house">OXFORD HOUSE</div>
        </section>

        <section class="page">
            <div class="doc-header">
                <div class="subhead" style="font-size:34px">PLAN FOR RECOVERY</div>
                <div class="oxford-line" style="font-size:22px">OXFORD HOUSE - <span class="house-line"><input class="line-input autosave" name="house_name" value="<?= h($current['house_name']) ?>"></span></div>
            </div>
            <div class="field-row"><div class="field"><label>Name</label><input class="line-input autosave" name="plan_name" value="<?= h($current['plan_name'] ?: $current['member_name']) ?>"></div></div>
            <div style="margin:16px 0 8px;font-weight:700">My plan for recovery:</div>
            <textarea class="line-textarea autosave" name="plan_text" rows="8"><?= h($current['plan_text']) ?></textarea>
            <div style="margin:16px 0 8px;font-weight:700">If enrolled in an aftercare/court program, my attendance includes:</div>
            <textarea class="line-textarea autosave" name="aftercare_program" rows="4"><?= h($current['aftercare_program']) ?></textarea>
            <div class="field-row">
                <div class="field" style="max-width:280px"><label><input type="radio" class="autosave" name="has_sponsor" value="yes" <?= $current['has_sponsor'] === 'yes' ? 'checked' : '' ?>> I do have a sponsor/mentor.</label></div>
                <div class="field" style="max-width:440px"><label><input type="radio" class="autosave" name="has_sponsor" value="no" <?= $current['has_sponsor'] === 'no' ? 'checked' : '' ?>> I do not have a sponsor/mentor. I plan to have one by date:</label><input type="date" class="line-input autosave" name="sponsor_by_date" value="<?= h($current['sponsor_by_date']) ?>"></div>
            </div>
            <div class="field-row">
                <div class="field"><label>I plan to attend</label><input class="line-input autosave" name="meetings_per_week" value="<?= h($current['meetings_per_week']) ?>"><label>recovery meetings per week.</label></div>
            </div>
            <div class="field-row"><div class="field"><label>The type of meetings I will attend:</label><input class="line-input autosave" name="meeting_types" value="<?= h($current['meeting_types']) ?>"></div></div>
            <p style="margin-top:20px;font-size:21px;line-height:1.35">I understand if I have a drug use recurrence (including alcohol), I will be immediately expelled from this Oxford House.</p>
            <div class="sig-pair"><div class="field"><label>Signature</label><input class="line-input autosave" name="plan_signature" value="<?= h($current['plan_signature']) ?>"></div><div class="field"><label>Date</label><input type="date" class="line-input autosave" name="plan_signature_date" value="<?= h($current['plan_signature_date']) ?>"></div></div>
            <div class="sig-pair"><div class="field"><label>President</label><input class="line-input autosave" name="plan_president_signature" value="<?= h($current['plan_president_signature']) ?>"></div><div class="field"><label>Date</label><input type="date" class="line-input autosave" name="plan_president_date" value="<?= h($current['plan_president_date']) ?>"></div></div>
            <div class="footer-house">OXFORD HOUSE</div>
        </section>

        <section class="page">
            <div class="doc-header">
                <div class="subhead" style="font-size:28px">RELAPSE CONTINGENCY PLAN</div>
            </div>
            <p style="font-size:18px;line-height:1.5">I, (print name) <input class="line-input autosave" style="display:inline-block;width:330px" name="relapse_name" value="<?= h($current['relapse_name'] ?: $current['member_name']) ?>"> understand that per the Oxford House Charter, if I have a recurrence of use I will be immediately expelled from this Oxford House. If this should happen, I would like the following actions to be taken:</p>
            <div class="center" style="font-weight:700;margin:18px 0 14px">Check all that apply</div>
            <div class="field-row" style="justify-content:center;gap:24px;font-size:20px">
                <label><input type="checkbox" class="autosave" name="relapse_family" <?= !empty($current['relapse_family']) ? 'checked' : '' ?>> Family</label>
                <label><input type="checkbox" class="autosave" name="relapse_friend" <?= !empty($current['relapse_friend']) ? 'checked' : '' ?>> Friend</label>
                <label><input type="checkbox" class="autosave" name="relapse_detox" <?= !empty($current['relapse_detox']) ? 'checked' : '' ?>> Detox / Treatment</label>
                <label><input type="checkbox" class="autosave" name="relapse_other" <?= !empty($current['relapse_other']) ? 'checked' : '' ?>> Other</label>
                <input class="line-input autosave" style="max-width:160px" name="relapse_other_text" value="<?= h($current['relapse_other_text']) ?>">
            </div>
            <div style="font-size:18px;margin:12px 0 6px">Describe details: including names, phone numbers, and addresses:</div>
            <textarea class="line-textarea autosave" name="relapse_details" rows="8"><?= h($current['relapse_details']) ?></textarea>
            <div class="center" style="font-weight:700;font-size:28px;margin:18px 0 8px">People to Notify:</div>
            <table class="grid-table">
                <thead><tr><th>Name</th><th>Phone Number</th><th>Relationship</th></tr></thead>
                <tbody>
                <?php foreach ($notifyRows as $i => $row): ?>
                    <tr>
                        <td><input class="small-input autosave" name="notify_name[]" value="<?= h($row['name']) ?>"></td>
                        <td><input class="small-input autosave" name="notify_phone[]" value="<?= h($row['phone']) ?>"></td>
                        <td><input class="small-input autosave" name="notify_relationship[]" value="<?= h($row['relationship']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p style="font-size:17px;line-height:1.6;text-align:center;margin:20px 20px 10px">I understand I have 30 days to remove all of my personal belongings from this Oxford House and that any items left behind after 30 days will be thrown away or donated to a local charitable organization.</p>
            <p style="font-size:17px;line-height:1.6;text-align:center;margin:8px 20px 10px">I understand that 72 hours after being expelled, any of my personal items I have not removed from the property will be safely removed from the bedroom and relocated to a storage area.</p>
            <p style="font-size:17px;line-height:1.6;text-align:center;margin:8px 20px 14px">If I am unable to remove my personal belongings from this Oxford House, I give the following people permission to remove them for me:</p>
            <table class="grid-table">
                <thead><tr><th>Name</th><th>Phone Number</th><th>Relationship</th></tr></thead>
                <tbody>
                <?php foreach ($pickupRows as $row): ?>
                    <tr>
                        <td><input class="small-input autosave" name="pickup_name[]" value="<?= h($row['name']) ?>"></td>
                        <td><input class="small-input autosave" name="pickup_phone[]" value="<?= h($row['phone']) ?>"></td>
                        <td><input class="small-input autosave" name="pickup_relationship[]" value="<?= h($row['relationship']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="sig-pair"><div class="field"><label>Member Signature:</label><input class="line-input autosave" name="relapse_member_signature" value="<?= h($current['relapse_member_signature']) ?>"></div><div class="field"><label>Date:</label><input type="date" class="line-input autosave" name="relapse_member_date" value="<?= h($current['relapse_member_date']) ?>"></div></div>
            <div class="sig-pair"><div class="field"><label>President Signature:</label><input class="line-input autosave" name="relapse_president_signature" value="<?= h($current['relapse_president_signature']) ?>"></div><div class="field"><label>Date:</label><input type="date" class="line-input autosave" name="relapse_president_date" value="<?= h($current['relapse_president_date']) ?>"></div></div>
            <div class="sig-pair"><div class="field"><label>Witness Signature:</label><input class="line-input autosave" name="relapse_witness_signature" value="<?= h($current['relapse_witness_signature']) ?>"></div><div class="field"><label>Date:</label><input type="date" class="line-input autosave" name="relapse_witness_date" value="<?= h($current['relapse_witness_date']) ?>"></div></div>
        </section>

        <section class="page">
            <div class="doc-header">
                <div class="subhead" style="font-size:26px">EMERGENCY MEDICAL INFORMATION</div>
                <div class="subhead" style="font-size:26px">RELEASE FORM</div>
                <div class="oxford-line" style="font-size:22px">OXFORD HOUSE - <span class="house-line"><input class="line-input autosave" name="house_name" value="<?= h($current['house_name']) ?>"></span></div>
                <div class="copyright">© Oxford House, Inc V1.0 2023</div>
            </div>
            <div class="two-col">
                <div class="field"><label>Name</label><input class="line-input autosave" name="medical_name" value="<?= h($current['medical_name'] ?: $current['member_name']) ?>"></div>
                <div class="field"><label>D.O.B.</label><input type="date" class="line-input autosave" name="dob" value="<?= h($current['dob']) ?>"></div>
            </div>
            <div class="two-col">
                <div class="field"><label>Physician Name</label><input class="line-input autosave" name="physician_name" value="<?= h($current['physician_name']) ?>"></div>
                <div class="field"><label>Physician Phone</label><input class="line-input autosave" name="physician_phone" value="<?= h($current['physician_phone']) ?>"></div>
            </div>
            <div class="field-row"><div class="field"><label>Hospital or Clinic</label><input class="line-input autosave" name="hospital_clinic" value="<?= h($current['hospital_clinic']) ?>"></div></div>
            <div class="two-col">
                <div class="field"><label>Insurance Info</label><input class="line-input autosave" name="insurance_info" value="<?= h($current['insurance_info']) ?>"></div>
                <div class="field"><label>Blood Type</label><input class="line-input autosave" name="blood_type" value="<?= h($current['blood_type']) ?>"></div>
            </div>
            <div style="margin-top:10px"><label style="font-weight:700">Allergies</label><textarea class="line-textarea autosave" name="allergies" rows="3"><?= h($current['allergies']) ?></textarea></div>
            <div style="margin-top:10px"><label style="font-weight:700">Medications</label><textarea class="line-textarea autosave" name="medications" rows="3"><?= h($current['medications']) ?></textarea></div>
            <div style="margin-top:10px"><label style="font-weight:700">Medical History</label><textarea class="line-textarea autosave" name="medical_history" rows="4"><?= h($current['medical_history']) ?></textarea></div>
            <div style="margin:16px 0 8px;font-weight:700">Emergency Contacts</div>
            <table class="grid-table">
                <thead><tr><th>Name</th><th>Phone</th><th>Relationship</th></tr></thead>
                <tbody>
                <?php foreach ($medicalRows as $row): ?>
                    <tr>
                        <td><input class="small-input autosave" name="medical_contact_name[]" value="<?= h($row['name']) ?>"></td>
                        <td><input class="small-input autosave" name="medical_contact_phone[]" value="<?= h($row['phone']) ?>"></td>
                        <td><input class="small-input autosave" name="medical_contact_relationship[]" value="<?= h($row['relationship']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p style="font-size:18px;margin-top:16px">I hereby give consent for emergency medical treatment</p>
            <div class="sig-pair"><div class="field"><label>Signature</label><input class="line-input autosave" name="medical_signature" value="<?= h($current['medical_signature']) ?>"></div><div class="field"><label>Date</label><input type="date" class="line-input autosave" name="medical_date" value="<?= h($current['medical_date']) ?>"></div></div>
            <div class="footer-house">OXFORD HOUSE</div>
        </section>

        <section class="page">
            <div class="doc-header">
                <div class="subhead" style="font-size:30px">PROPERTY LIST</div>
                <div class="oxford-line" style="font-size:22px">OXFORD HOUSE - <span class="house-line"><input class="line-input autosave" name="house_name" value="<?= h($current['house_name']) ?>"></span></div>
            </div>
            <div class="two-col">
                <div class="field"><label>Name</label><input class="line-input autosave" name="property_name" value="<?= h($current['property_name'] ?: $current['member_name']) ?>"></div>
                <div class="field"><label>Move-in Date</label><input type="date" class="line-input autosave" name="property_move_in_date" value="<?= h($current['property_move_in_date'] ?: $current['move_in_date']) ?>"></div>
            </div>
            <table class="grid-table" style="margin-top:14px">
                <thead><tr><th style="width:18%">Date</th><th>Property Item &amp; Description</th><th style="width:18%">President Initials</th></tr></thead>
                <tbody>
                <?php foreach ($propertyRows as $row): ?>
                    <tr>
                        <td><input class="small-input autosave" name="property_date[]" value="<?= h($row['date']) ?>"></td>
                        <td><input class="small-input autosave" name="property_description[]" value="<?= h($row['description']) ?>"></td>
                        <td><input class="small-input autosave" name="property_initials[]" value="<?= h($row['initials']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="footer-house">OXFORD HOUSE &nbsp;&nbsp;&nbsp; <span class="tiny">COMMON AREA ONLY</span></div>
        </section>
    </div>

    <div class="page print-hide" style="min-height:auto;">
        <div class="doc-header"><div class="subhead" style="font-size:24px">UPLOAD COPY</div></div>
        <p>Upload a scanned copy, PDF, or image. When a record has an uploaded copy, loading that history entry will show the uploaded copy instead of the fillable form.</p>
        <input type="file" name="uploaded_copy" accept=".pdf,image/jpeg,image/png,image/webp">
        <button type="submit" onclick="document.getElementById('formAction').value='save_upload'">Save Form + Upload Copy</button>
        <?php if (!empty($current['flash_error'])): ?><p style="color:#b00020"><?= h($current['flash_error']) ?></p><?php endif; ?>
    </div>
</form>
</div>

<script>
const form = document.getElementById('packetForm');
const saveStatus = document.getElementById('saveStatus');
const recordId = document.getElementById('recordId');
const historyMemberName = document.getElementById('historyMemberName');
const historySelect = document.getElementById('historySelect');
const formPages = document.getElementById('formPages');
let saveTimer = null;

function updateChecklistTotal() {
    const total = Array.from(document.querySelectorAll('.calc-check')).filter(cb => cb.checked).length;
    const el = document.getElementById('checkTotal');
    if (el) el.textContent = total;
}
updateChecklistTotal();
document.querySelectorAll('.calc-check').forEach(cb => cb.addEventListener('change', updateChecklistTotal));

document.querySelectorAll('.member-name-source').forEach(el => {
    el.addEventListener('input', () => {
        historyMemberName.value = el.value;
        queueHistoryFetch();
    });
});

async function autosave() {
    saveStatus.textContent = 'Saving...';
    const fd = new FormData(form);
    fd.set('ajax_save', '1');
    fd.set('action', 'save');
    try {
        const res = await fetch(window.location.pathname, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.ok) {
            if (json.id) recordId.value = json.id;
            saveStatus.textContent = 'Saved ' + (json.updated_at || '');
        } else {
            saveStatus.textContent = 'Save failed';
        }
    } catch (e) {
        saveStatus.textContent = 'Save failed';
    }
}

function queueAutosave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autosave, 700);
}

document.querySelectorAll('.autosave').forEach(el => {
    el.addEventListener('input', queueAutosave);
    el.addEventListener('change', queueAutosave);
});

let historyTimer = null;
function queueHistoryFetch() {
    clearTimeout(historyTimer);
    historyTimer = setTimeout(loadHistoryList, 350);
}

async function loadHistoryList() {
    const member = historyMemberName.value.trim();
    historySelect.innerHTML = '<option value="">History records</option>';
    if (!member) return;
    try {
        const res = await fetch(window.location.pathname + '?ajax=history&member_name=' + encodeURIComponent(member));
        const json = await res.json();
        if (!json.ok) return;
        json.items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            const packetDate = item.packet_date || 'No packet date';
            const house = item.house_name || 'No house';
            const uploaded = item.uploaded_copy_name ? ' | Uploaded Copy' : '';
            opt.textContent = `${item.member_name} | ${house} | ${packetDate}${uploaded}`;
            historySelect.appendChild(opt);
        });
    } catch (e) {}
}

async function loadRecord(id) {
    if (!id) return;
    window.location.href = window.location.pathname + '?id=' + encodeURIComponent(id);
}

document.getElementById('loadHistoryBtn').addEventListener('click', () => loadRecord(historySelect.value));
historyMemberName.addEventListener('input', queueHistoryFetch);
window.addEventListener('load', loadHistoryList);
</script>
</body>
</html>
