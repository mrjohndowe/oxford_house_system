<?php
declare(strict_types=1);

/**
 * Oxford House Red Creek - New Member Packet
 * Single-file PHP app
 * - Fillable form closely matching the uploaded packet
 * - Auto-save to MySQL
 * - History dropdown by member name
 * - Upload scanned copy; when present, history view shows uploaded copy instead of form
 * - Reload / edit prior records
 * - Print button
 */

require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const UPLOAD_DIR = __DIR__ . '/uploads/member_packets';
const HOUSE_NAME = 'Oxford House';

function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function strv(mixed $value): string
{
    return trim((string)($value ?? ''));
}

function money(mixed $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function upload_web_path(string $absolutePath): string
{
    $root = realpath(__DIR__);
    $real = realpath($absolutePath);
    if (!$root || !$real) {
        return '';
    }
    $relative = str_replace('\\', '/', ltrim(str_replace($root, '', $real), DIRECTORY_SEPARATOR));
    return './' . $relative;
}

function ensure_upload_dir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
}

function empty_record(): array
{
    return [
        'id' => '',
        'member_name' => '',
        'move_in_date' => '',
        'signature_date' => '',
        'refund_move_in_date' => '',
        'new_member_signature' => '',
        'new_member_signature_date' => '',
        'president_hsr_signature' => '',
        'president_hsr_signature_date' => '',
        'expectations_signature' => '',
        'expectations_signature_date' => '',
        'medication_signature' => '',
        'medication_signature_date' => '',
        'emergency_name' => '',
        'emergency_age' => '',
        'emergency_dob' => '',
        'blood_type' => '',
        'primary_physician' => '',
        'physician_phone' => '',
        'hospital_clinic' => '',
        'insurance' => '',
        'allergies' => '',
        'medications' => '',
        'medical_history' => '',
        'contact1_name' => '',
        'contact1_phone' => '',
        'contact2_name' => '',
        'contact2_phone' => '',
        'contact3_name' => '',
        'contact3_phone' => '',
        'property_owner_name' => '',
        'property_signature' => '',
        'property_witness_signature' => '',
        'property_date' => '',
        'property_removed_date' => '',
        'property_removed_witness_signature' => '',
        'ees_amount' => '',
        'move_in_fee' => '250.00',
        'other_charge' => '',
        'total_due' => '',
        'scan_path' => '',
        'scan_original_name' => '',
        'created_at' => '',
        'updated_at' => '',
    ];
}

function property_items_from_post(): array
{
    $items = $_POST['property_items'] ?? [];
    if (!is_array($items)) {
        $items = [];
    }
    $normalized = [];
    for ($i = 0; $i < 15; $i++) {
        $normalized[] = strv($items[$i] ?? '');
    }
    return $normalized;
}

function get_pdo(): PDO
{
    global $dbHost, $dbName, $dbUser, $dbPass;

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS oxford_red_creek_member_packets (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            member_name VARCHAR(255) NOT NULL DEFAULT '',
            move_in_date DATE NULL,
            signature_date DATE NULL,
            refund_move_in_date DATE NULL,
            new_member_signature VARCHAR(255) NOT NULL DEFAULT '',
            new_member_signature_date DATE NULL,
            president_hsr_signature VARCHAR(255) NOT NULL DEFAULT '',
            president_hsr_signature_date DATE NULL,
            expectations_signature VARCHAR(255) NOT NULL DEFAULT '',
            expectations_signature_date DATE NULL,
            medication_signature VARCHAR(255) NOT NULL DEFAULT '',
            medication_signature_date DATE NULL,
            emergency_name VARCHAR(255) NOT NULL DEFAULT '',
            emergency_age VARCHAR(50) NOT NULL DEFAULT '',
            emergency_dob VARCHAR(50) NOT NULL DEFAULT '',
            blood_type VARCHAR(50) NOT NULL DEFAULT '',
            primary_physician VARCHAR(255) NOT NULL DEFAULT '',
            physician_phone VARCHAR(100) NOT NULL DEFAULT '',
            hospital_clinic VARCHAR(255) NOT NULL DEFAULT '',
            insurance VARCHAR(255) NOT NULL DEFAULT '',
            allergies TEXT NULL,
            medications TEXT NULL,
            medical_history TEXT NULL,
            contact1_name VARCHAR(255) NOT NULL DEFAULT '',
            contact1_phone VARCHAR(100) NOT NULL DEFAULT '',
            contact2_name VARCHAR(255) NOT NULL DEFAULT '',
            contact2_phone VARCHAR(100) NOT NULL DEFAULT '',
            contact3_name VARCHAR(255) NOT NULL DEFAULT '',
            contact3_phone VARCHAR(100) NOT NULL DEFAULT '',
            property_items LONGTEXT NULL,
            property_owner_name VARCHAR(255) NOT NULL DEFAULT '',
            property_signature VARCHAR(255) NOT NULL DEFAULT '',
            property_witness_signature VARCHAR(255) NOT NULL DEFAULT '',
            property_date DATE NULL,
            property_removed_date DATE NULL,
            property_removed_witness_signature VARCHAR(255) NOT NULL DEFAULT '',
            ees_amount DECIMAL(10,2) NULL DEFAULT NULL,
            move_in_fee DECIMAL(10,2) NULL DEFAULT 250.00,
            other_charge DECIMAL(10,2) NULL DEFAULT 0.00,
            total_due DECIMAL(10,2) NULL DEFAULT NULL,
            scan_path VARCHAR(500) NOT NULL DEFAULT '',
            scan_original_name VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_member_name (member_name),
            KEY idx_updated_at (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    return $pdo;
}

function fetch_record(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM oxford_red_creek_member_packets WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $row['property_items'] = json_decode((string)($row['property_items'] ?? '[]'), true);
    if (!is_array($row['property_items'])) {
        $row['property_items'] = array_fill(0, 15, '');
    }
    $row['property_items'] = array_pad(array_slice($row['property_items'], 0, 15), 15, '');

    return $row;
}

function save_record(PDO $pdo, array $data, ?int $id = null): int
{
    $payload = [
        'member_name' => strv($data['member_name'] ?? ''),
        'move_in_date' => strv($data['move_in_date'] ?? '') ?: null,
        'signature_date' => strv($data['signature_date'] ?? '') ?: null,
        'refund_move_in_date' => strv($data['refund_move_in_date'] ?? '') ?: null,
        'new_member_signature' => strv($data['new_member_signature'] ?? ''),
        'new_member_signature_date' => strv($data['new_member_signature_date'] ?? '') ?: null,
        'president_hsr_signature' => strv($data['president_hsr_signature'] ?? ''),
        'president_hsr_signature_date' => strv($data['president_hsr_signature_date'] ?? '') ?: null,
        'expectations_signature' => strv($data['expectations_signature'] ?? ''),
        'expectations_signature_date' => strv($data['expectations_signature_date'] ?? '') ?: null,
        'medication_signature' => strv($data['medication_signature'] ?? ''),
        'medication_signature_date' => strv($data['medication_signature_date'] ?? '') ?: null,
        'emergency_name' => strv($data['emergency_name'] ?? ''),
        'emergency_age' => strv($data['emergency_age'] ?? ''),
        'emergency_dob' => strv($data['emergency_dob'] ?? ''),
        'blood_type' => strv($data['blood_type'] ?? ''),
        'primary_physician' => strv($data['primary_physician'] ?? ''),
        'physician_phone' => strv($data['physician_phone'] ?? ''),
        'hospital_clinic' => strv($data['hospital_clinic'] ?? ''),
        'insurance' => strv($data['insurance'] ?? ''),
        'allergies' => strv($data['allergies'] ?? ''),
        'medications' => strv($data['medications'] ?? ''),
        'medical_history' => strv($data['medical_history'] ?? ''),
        'contact1_name' => strv($data['contact1_name'] ?? ''),
        'contact1_phone' => strv($data['contact1_phone'] ?? ''),
        'contact2_name' => strv($data['contact2_name'] ?? ''),
        'contact2_phone' => strv($data['contact2_phone'] ?? ''),
        'contact3_name' => strv($data['contact3_name'] ?? ''),
        'contact3_phone' => strv($data['contact3_phone'] ?? ''),
        'property_items' => json_encode($data['property_items'] ?? array_fill(0, 15, ''), JSON_UNESCAPED_UNICODE),
        'property_owner_name' => strv($data['property_owner_name'] ?? ''),
        'property_signature' => strv($data['property_signature'] ?? ''),
        'property_witness_signature' => strv($data['property_witness_signature'] ?? ''),
        'property_date' => strv($data['property_date'] ?? '') ?: null,
        'property_removed_date' => strv($data['property_removed_date'] ?? '') ?: null,
        'property_removed_witness_signature' => strv($data['property_removed_witness_signature'] ?? ''),
        'ees_amount' => ($data['ees_amount'] ?? '') === '' ? null : (float)$data['ees_amount'],
        'move_in_fee' => ($data['move_in_fee'] ?? '') === '' ? null : (float)$data['move_in_fee'],
        'other_charge' => ($data['other_charge'] ?? '') === '' ? null : (float)$data['other_charge'],
        'total_due' => ($data['total_due'] ?? '') === '' ? null : (float)$data['total_due'],
        'scan_path' => strv($data['scan_path'] ?? ''),
        'scan_original_name' => strv($data['scan_original_name'] ?? ''),
    ];

    if ($id) {
        $sql = "UPDATE oxford_red_creek_member_packets SET
            member_name=:member_name,
            move_in_date=:move_in_date,
            signature_date=:signature_date,
            refund_move_in_date=:refund_move_in_date,
            new_member_signature=:new_member_signature,
            new_member_signature_date=:new_member_signature_date,
            president_hsr_signature=:president_hsr_signature,
            president_hsr_signature_date=:president_hsr_signature_date,
            expectations_signature=:expectations_signature,
            expectations_signature_date=:expectations_signature_date,
            medication_signature=:medication_signature,
            medication_signature_date=:medication_signature_date,
            emergency_name=:emergency_name,
            emergency_age=:emergency_age,
            emergency_dob=:emergency_dob,
            blood_type=:blood_type,
            primary_physician=:primary_physician,
            physician_phone=:physician_phone,
            hospital_clinic=:hospital_clinic,
            insurance=:insurance,
            allergies=:allergies,
            medications=:medications,
            medical_history=:medical_history,
            contact1_name=:contact1_name,
            contact1_phone=:contact1_phone,
            contact2_name=:contact2_name,
            contact2_phone=:contact2_phone,
            contact3_name=:contact3_name,
            contact3_phone=:contact3_phone,
            property_items=:property_items,
            property_owner_name=:property_owner_name,
            property_signature=:property_signature,
            property_witness_signature=:property_witness_signature,
            property_date=:property_date,
            property_removed_date=:property_removed_date,
            property_removed_witness_signature=:property_removed_witness_signature,
            ees_amount=:ees_amount,
            move_in_fee=:move_in_fee,
            other_charge=:other_charge,
            total_due=:total_due,
            scan_path=:scan_path,
            scan_original_name=:scan_original_name
            WHERE id=:id";
        $payload['id'] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($payload);
        return $id;
    }

    $sql = "INSERT INTO oxford_red_creek_member_packets (
        member_name, move_in_date, signature_date, refund_move_in_date,
        new_member_signature, new_member_signature_date,
        president_hsr_signature, president_hsr_signature_date,
        expectations_signature, expectations_signature_date,
        medication_signature, medication_signature_date,
        emergency_name, emergency_age, emergency_dob, blood_type,
        primary_physician, physician_phone, hospital_clinic, insurance,
        allergies, medications, medical_history,
        contact1_name, contact1_phone, contact2_name, contact2_phone, contact3_name, contact3_phone,
        property_items, property_owner_name, property_signature, property_witness_signature,
        property_date, property_removed_date, property_removed_witness_signature,
        ees_amount, move_in_fee, other_charge, total_due,
        scan_path, scan_original_name
    ) VALUES (
        :member_name, :move_in_date, :signature_date, :refund_move_in_date,
        :new_member_signature, :new_member_signature_date,
        :president_hsr_signature, :president_hsr_signature_date,
        :expectations_signature, :expectations_signature_date,
        :medication_signature, :medication_signature_date,
        :emergency_name, :emergency_age, :emergency_dob, :blood_type,
        :primary_physician, :physician_phone, :hospital_clinic, :insurance,
        :allergies, :medications, :medical_history,
        :contact1_name, :contact1_phone, :contact2_name, :contact2_phone, :contact3_name, :contact3_phone,
        :property_items, :property_owner_name, :property_signature, :property_witness_signature,
        :property_date, :property_removed_date, :property_removed_witness_signature,
        :ees_amount, :move_in_fee, :other_charge, :total_due,
        :scan_path, :scan_original_name
    )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($payload);
    return (int)$pdo->lastInsertId();
}

$pdo = get_pdo();
ensure_upload_dir();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'autosave') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id = isset($_POST['record_id']) && ctype_digit((string)$_POST['record_id']) ? (int)$_POST['record_id'] : null;
        $existing = $id ? fetch_record($pdo, $id) : null;

        $payload = $_POST;
        $payload['property_items'] = property_items_from_post();
        if ($existing) {
            $payload['scan_path'] = $existing['scan_path'] ?? '';
            $payload['scan_original_name'] = $existing['scan_original_name'] ?? '';
        }

        $savedId = save_record($pdo, $payload, $id);
        $saved = fetch_record($pdo, $savedId);

        echo json_encode([
            'ok' => true,
            'record_id' => $savedId,
            'updated_at' => $saved['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage(),
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_scan') {
    $recordId = isset($_POST['record_id']) && ctype_digit((string)$_POST['record_id']) ? (int)$_POST['record_id'] : 0;
    if ($recordId > 0 && isset($_FILES['scan_file']) && is_uploaded_file($_FILES['scan_file']['tmp_name'])) {
        $existing = fetch_record($pdo, $recordId);
        if ($existing) {
            $originalName = $_FILES['scan_file']['name'] ?? 'scan';
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $existing['member_name'] ?: 'member');
                $filename = $recordId . '_' . $safeBase . '_' . time() . '.' . $ext;
                $dest = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;
                if (move_uploaded_file($_FILES['scan_file']['tmp_name'], $dest)) {
                    $existing['scan_path'] = $dest;
                    $existing['scan_original_name'] = $originalName;
                    save_record($pdo, $existing, $recordId);
                }
            }
        }
    }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?load=' . (int)$recordId);
    exit;
}

$historyRows = $pdo->query("
    SELECT id, member_name, move_in_date, updated_at, scan_path, scan_original_name
    FROM oxford_red_creek_member_packets
    ORDER BY member_name ASC, updated_at DESC
")->fetchAll();

$memberNames = $pdo->query("
    SELECT member_name
    FROM oxford_red_creek_member_packets
    WHERE member_name <> ''
    GROUP BY member_name
    ORDER BY member_name ASC
")->fetchAll(PDO::FETCH_COLUMN);

$selectedMember = strv($_GET['member_name'] ?? '');
$loadId = isset($_GET['load']) && ctype_digit((string)$_GET['load']) ? (int)$_GET['load'] : 0;

if (!$loadId && $selectedMember !== '') {
    $stmt = $pdo->prepare("
        SELECT id
        FROM oxford_red_creek_member_packets
        WHERE member_name = ?
        ORDER BY updated_at DESC, id DESC
        LIMIT 1
    ");
    $stmt->execute([$selectedMember]);
    $loadId = (int)($stmt->fetchColumn() ?: 0);
}

$record = $loadId ? fetch_record($pdo, $loadId) : null;
$form = array_merge(empty_record(), $record ?: []);
$form['property_items'] = $form['property_items'] ?? array_fill(0, 15, '');

$recordsForSelectedMember = [];
if ($selectedMember !== '') {
    $stmt = $pdo->prepare("
        SELECT id, member_name, move_in_date, updated_at, scan_path, scan_original_name
        FROM oxford_red_creek_member_packets
        WHERE member_name = ?
        ORDER BY updated_at DESC, id DESC
    ");
    $stmt->execute([$selectedMember]);
    $recordsForSelectedMember = $stmt->fetchAll();
} elseif ($form['member_name'] !== '') {
    $stmt = $pdo->prepare("
        SELECT id, member_name, move_in_date, updated_at, scan_path, scan_original_name
        FROM oxford_red_creek_member_packets
        WHERE member_name = ?
        ORDER BY updated_at DESC, id DESC
    ");
    $stmt->execute([$form['member_name']]);
    $recordsForSelectedMember = $stmt->fetchAll();
    $selectedMember = $form['member_name'];
}

$scanWebPath = '';
$scanExists = false;
if (!empty($form['scan_path']) && file_exists((string)$form['scan_path'])) {
    $scanWebPath = upload_web_path((string)$form['scan_path']);
    $scanExists = $scanWebPath !== '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>red_creek_new_member.php</title>
    <style>
        :root{
            --ink:#111;
            --muted:#555;
            --line:#222;
            --soft:#e7e7e7;
            --panel:#fafafa;
            --accent:#8b0000;
        }
        *{box-sizing:border-box}
        html,body{margin:0;padding:0;background:#dcdcdc;color:var(--ink);font-family:"Times New Roman", Georgia, serif}
        body{padding:18px}
        .app{max-width:1180px;margin:0 auto}
        .toolbar{
            display:flex;flex-wrap:wrap;gap:10px;align-items:end;
            background:#fff;border:1px solid #cfcfcf;padding:14px 16px;margin-bottom:16px
        }
        .toolbar .group{display:flex;flex-direction:column;gap:5px;min-width:220px}
        .toolbar label{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em}
        .toolbar input[type="text"], .toolbar input[type="date"], .toolbar input[type="number"], .toolbar select, .toolbar input[type="file"]{
            width:100%;padding:8px 10px;border:1px solid #bdbdbd;background:#fff;font-size:14px
        }
        .toolbar .actions{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto}
        button,.btn{
            display:inline-flex;align-items:center;justify-content:center;gap:6px;
            padding:9px 14px;border:1px solid #333;background:#fff;color:#111;
            font-size:14px;text-decoration:none;cursor:pointer
        }
        button.primary,.btn.primary{background:#111;color:#fff}
        button:disabled{opacity:.55;cursor:not-allowed}
        .save-note{font-size:12px;color:var(--muted);min-width:170px}
        .sheet{
            width:8.5in;min-height:11in;margin:0 auto;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.12);
            padding:.55in .55in .6in .55in;border:1px solid #cfcfcf
        }
        .top-logo{text-align:center;margin-bottom:10px}
        .top-logo img{max-width:130px;max-height:85px;display:block;margin:0 auto 8px}
        .center{text-align:center}
        .caps{font-weight:bold;text-transform:uppercase}
        .subtitle{font-size:17px;font-weight:bold}
        .small{font-size:12px}
        .tiny{font-size:11px}
        p{margin:.12in 0;line-height:1.23}
        ol{margin:.06in 0 .14in .22in;padding:0}
        li{margin:0 0 .08in 0;line-height:1.2}
        .line-row{display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;margin:8px 0}
        .field{display:flex;align-items:flex-end;gap:8px;flex:1;min-width:200px}
        .field label{white-space:nowrap}
        .line-input, .line-textarea{
            border:none;border-bottom:1px solid var(--line);background:transparent;
            padding:2px 4px 1px 4px;font:inherit;color:inherit;outline:none;width:100%
        }
        .line-textarea{min-height:54px;resize:vertical;border:1px solid #888;padding:6px}
        .money-grid{
            display:grid;grid-template-columns:repeat(4,minmax(110px,1fr));gap:10px;
            border:1px solid #bbb;padding:12px;margin:14px 0;background:#fcfcfc
        }
        .money-grid label{display:block;font-size:12px;font-weight:bold;text-transform:uppercase;margin-bottom:4px}
        .money-grid input{width:100%;padding:7px 8px;border:1px solid #aaa;font-size:14px}
        .section-title{
            text-align:center;font-weight:bold;text-transform:uppercase;
            margin:24px 0 10px 0;font-size:20px;letter-spacing:.02em
        }
        .double-space{margin-top:18px}
        .signature-grid{display:grid;grid-template-columns:1fr 170px;gap:16px;align-items:end;margin:8px 0}
        .signature-grid .field{min-width:unset}
        .scan-view{
            width:8.5in;min-height:11in;margin:0 auto;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.12);
            padding:18px;border:1px solid #cfcfcf
        }
        .scan-box{text-align:center}
        .scan-box iframe{width:100%;height:9.4in;border:1px solid #aaa;background:#f8f8f8}
        .scan-box img{max-width:100%;max-height:9.4in;border:1px solid #aaa;background:#f8f8f8}
        .notice{padding:10px 12px;border:1px solid #d7d7d7;background:#fbfbfb;margin-bottom:12px}
        .property-list{margin-top:10px}
        .property-list .row{display:flex;align-items:flex-end;gap:8px;margin:4px 0}
        .property-list .num{width:28px}
        .property-list input{flex:1;border:none;border-bottom:1px solid #222;padding:2px 4px;font:inherit}
        .right{text-align:right}
        .muted{color:#666}
        .print-only{display:none}
        @media print{
            body{background:#fff;padding:0}
            .toolbar,.no-print{display:none !important}
            .sheet,.scan-view{box-shadow:none;border:none;margin:0;width:auto;min-height:auto;padding:.3in .35in}
            .line-input,.line-textarea{border-color:#000}
            .print-only{display:block}
        }
        @media (max-width: 980px){
            body{padding:8px}
            .sheet,.scan-view{width:100%;padding:16px}
            .money-grid{grid-template-columns:repeat(2,minmax(120px,1fr))}
            .signature-grid{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
<div class="app">
    <form class="toolbar no-print" method="get" action="">
        <div class="group">
            <label for="member_name_filter">History by member name</label>
            <select id="member_name_filter" name="member_name" onchange="this.form.submit()">
                <option value="">Select member</option>
                <?php foreach ($memberNames as $name): ?>
                    <option value="<?= h($name) ?>" <?= $selectedMember === $name ? 'selected' : '' ?>><?= h($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="group">
            <label for="record_loader">Saved records</label>
            <select id="record_loader" onchange="if(this.value){ window.location='?member_name=<?= urlencode($selectedMember) ?>&load='+this.value; }">
                <option value="">Select saved record</option>
                <?php foreach ($recordsForSelectedMember as $hist): ?>
                    <option value="<?= (int)$hist['id'] ?>" <?= (int)$form['id'] === (int)$hist['id'] ? 'selected' : '' ?>>
                        <?= h(($hist['move_in_date'] ?: 'No Move-In Date') . ' | Updated ' . $hist['updated_at'] . (!empty($hist['scan_path']) ? ' | Scan Uploaded' : '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="group">
            <label>Status</label>
            <div class="save-note" id="saveStatus">Ready.</div>
        </div>

        <div class="actions">
            <button type="button" onclick="window.print()">Print</button>
            <?php if ($scanExists): ?>
                <button type="button" id="toggleViewBtn" onclick="toggleView()">Show Form</button>
            <?php endif; ?>
            <a class="btn" href="<?= h(strtok($_SERVER['REQUEST_URI'], '?')) ?>">New Blank Form</a>
        </div>
    </form>

    <?php if ($scanExists): ?>
        <div id="scanView" class="scan-view">
            <div class="notice no-print">
                <strong>Uploaded scanned copy displayed.</strong>
                This record has an uploaded file, so history opens to the scan first.
                You can switch back to the editable form with <strong>Show Form</strong>.
            </div>
            <div class="scan-box">
                <?php $ext = strtolower(pathinfo((string)$form['scan_original_name'], PATHINFO_EXTENSION)); ?>
                <?php if ($ext === 'pdf'): ?>
                    <iframe src="<?= h($scanWebPath) ?>"></iframe>
                <?php else: ?>
                    <img src="<?= h($scanWebPath) ?>" alt="Uploaded scanned copy">
                <?php endif; ?>
                <div class="small muted" style="margin-top:8px;">
                    File: <?= h($form['scan_original_name']) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div id="formView" class="sheet" style="<?= $scanExists ? 'display:none;' : '' ?>">
        <div class="top-logo">
            <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House logo">
            <div class="caps subtitle">Welcome</div>
            <div class="caps subtitle">To</div>
            <div class="caps subtitle"><?= h(HOUSE_NAME) ?></div>
        </div>

        <form id="packetForm" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="">
            <input type="hidden" id="record_id" name="record_id" value="<?= h($form['id']) ?>">

            <div class="line-row">
                <div class="field">
                    <label>Member Name:</label>
                    <input class="line-input autosave" type="text" name="member_name" value="<?= h($form['member_name']) ?>">
                </div>
                <div class="field" style="max-width:280px;">
                    <label>Move-In Date:</label>
                    <input class="line-input autosave" type="date" name="move_in_date" value="<?= h($form['move_in_date']) ?>">
                </div>
            </div>

            <div class="money-grid">
                <div>
                    <label>EES Amount</label>
                    <input class="autosave money-field" type="number" step="0.01" min="0" name="ees_amount" value="<?= h(money($form['ees_amount'])) ?>">
                </div>
                <div>
                    <label>Move-In Fee</label>
                    <input class="autosave money-field" type="number" step="0.01" min="0" name="move_in_fee" value="<?= h(money($form['move_in_fee'])) ?>">
                </div>
                <div>
                    <label>Other Charge</label>
                    <input class="autosave money-field" type="number" step="0.01" min="0" name="other_charge" value="<?= h(money($form['other_charge'])) ?>">
                </div>
                <div>
                    <label>Total Due</label>
                    <input class="autosave" id="total_due" type="number" step="0.01" min="0" name="total_due" value="<?= h(money($form['total_due'])) ?>" readonly>
                </div>
            </div>

            <div class="section-title">Boundaries…and What Is Not Acceptable</div>

            <p class="caps center">“We have no bosses in Oxford House”.</p>
            <p class="caps">It is not O.K. to…</p>
            <ol>
                <li>Verbally attack another person, either by raising your voice or making sarcastic remarks toward that person.</li>
                <li>Put another person down, exhibit expression of inappropriate criticism toward a person’s moral behavior, appearance, clothes, friends, etc., is not acceptable.</li>
                <li>Take someone else’s inventory, unless you are concerned that person is exhibiting relapse behavior. Relapse concerns us all.</li>
                <li>Shame or frighten any of us by crossing the boundaries we have set for ourselves.</li>
                <li>Place blame on someone else for your behavior or feelings. We must take responsibility for our own actions and feelings.</li>
                <li>Throw objects, slam doors, stomp around, call names, or physically attack in anger. Raging out of control frightens and traumatizes everyone around you. It is not acceptable!</li>
                <li>Isolate.</li>
                <li>Hold resentments toward house members, which can cause communication breakdown and/or tension.</li>
                <li>To attempt to manipulate or control others to meet your expectations through gossip, aggressiveness, or self-justification.</li>
                <li>No spanking, hitting, or yelling at children. They are precious and valuable people too. They deserve our mutual respect.</li>
                <li>If a parent cannot control a disruptive child within 10 minutes, the parent must take the child to his/her respective room, or to a less populated area until the child has calmed down.</li>
            </ol>

            <p class="caps">It is O.K. to and, acceptable to…</p>
            <ol>
                <li>Be patient and tolerant. Each of us are at our own level of growth. Practice acceptance. “Live and let live.”</li>
                <li>To share positive attitudes and feelings towards others.</li>
                <li>To recognize achievements and growth in others.</li>
                <li>Share your program and listen to others.</li>
                <li>To have personal quiet times and privacy.</li>
                <li>To laugh at your mistakes.</li>
                <li>To do unto others as you would have them to do unto you.</li>
            </ol>

            <p class="center">“Oxford Houses Provide the addicted individual the opportunity to change their behaviors”<br>Paul Malloy – Founder, Oxford House Inc.</p>

            <div class="section-title">Oxford House of Colorado<br>Rules and Expectations</div>

            <p>We, the members of the Oxford House as recovering alcoholics and addicts, acknowledge the need to set the following rules and expectations to create guidance and stability for our members.</p>

            <p class="caps">Conduct</p>
            <ol>
                <li>Treat other members and guests as you would want to be treated. That means respect other member’s boundaries!</li>
                <li>Keep noise at a considerate level at all times. Not all members are on the same sleeping schedule.</li>
                <li>Any house decisions are made by a majority vote. No one person is allowed to make a decision that effects the house.</li>
                <li>All appliances are to be turned off when not in use, except for the computer hard drive and one living room light during the night.</li>
                <li>Any disruptive behavior is subject to a behavioral contract or the expulsion of the disruptive member. Our definition of disruptive behavior is anything that disrupts the harmony of the house, like missing of house meetings, violence or threatening behavior, illegal activity, theft, driving w/o license or insurance, probation violations, etc.</li>
                <li>If a relapse or disruptive behavior occurs, an emergency house meeting must be called as soon as two or more members are present. The party in question may be asked to leave until the meeting.</li>
                <li>After one week of occupancy, members must be moving towards work, school, treatment, or anything the house deems productive. By the fourth week, all members must be either working, enrolled in school, involved in treatment, or doing something productive in society. Note: If a member is not working, they then have to be out of the house by 9:00 am and be out till 3:00 pm Monday thru Friday actively looking for work.</li>
                <li>There is no set time given to members if they are asked to leave. It may range from 15 minutes to 24 hours, depending on whether the member’s behavior after is deemed disruptive. If they are cordial, they may be invited to stay up to 24 hours. Notice may not exceed 24 hours. If they are being dismissed for relapse, they will have 15-30 minutes to get what they need and leave the house. They can make arrangements to come back for the rest of their property within 30 days. Two members must be present when this happens. After 30 days your personal property will be donated or thrown away.</li>
                <li>Failure to comply with these rules/expectations may be subject to a vote of dismissal or contract of the offending member.</li>
                <li>If you are on Parole, Probation, or in Drug Court you will follow all the rules set down for you. If a member fails to do so, they could be asked to leave the house for disruptive behavior.</li>
                <li>You will have 30 days to find a job. We will review you every week to see that you are complying with this rule. After gaining employment you will have 90 days to be caught up in full. This will be a case-by-case situation voted by the house. You are expected to pay your own way after the 60-day new member contract has elapsed.</li>
                <li>If you are behind on your E.E.S you will remain on the New Member Contract until caught up in full.</li>
            </ol>

            <p class="caps">Equal Expense Shares</p>
            <ol>
                <li>EES is due on the 1st of each month no later than the 5th. A $10.00 fine will be issued daily after the 5th.</li>
                <li>All monies paid toward the house must be in the form of a money order. Only agency checks will be accepted.</li>
                <li>Room rates for EES range from $500.00 to $1100.00 a month for a single or a double room. Your EES amount for the month is $<input class="line-input autosave money-field" style="display:inline-block;width:120px;" type="number" step="0.01" min="0" name="ees_amount_inline" value="<?= h(money($form['ees_amount'])) ?>" data-sync-target="ees_amount">. (This is subject to change due to the financial stability of the house.)</li>
                <li>A non-refundable move in fee of $250.00 is required upon move in.</li>
            </ol>

            <p class="caps">House Meetings</p>
            <ol>
                <li>Attendance at house meetings, interviews, and orientations are mandatory. If a member is absent, a $25.00 fine will be issued unless the house approves the absence.</li>
                <li>If you miss a house meeting you must read the minutes of that meeting.</li>
                <li>Anyone can set up an interview, the steps are: a. Schedule it for one of our interview times. Give at least 24-hour notice for house members. b. Post interview on white board.</li>
            </ol>

            <p class="caps">Relapses</p>
            <ol>
                <li>Taking another member’s medication or failing to take your own prescribed medications will be considered a relapse and dismissal from the house will result.</li>
                <li>Withholding any information concerning a relapse is subject to dismissal of the member withholding the information.</li>
                <li>If you are on any agency or organizational funding and relapse, your EES will not be refunded back to you.</li>
                <li>Any UA’s that come back diluted will be considered positive.</li>
                <li>Poppy seed has caused many members to test positive and therefore it is highly discouraged to consume. You are responsible for what you put in your body.</li>
            </ol>

            <p class="caps">Fines</p>
            <ol>
                <li>Chores left undone or incomplete will be fined $25. If this becomes a problem, you may be subject to a behavioral contract.</li>
                <li>If warned or fined, you are required to complete the chore immediately-if you do not, the fine will be doubled and an emergency meeting will be called to find out if you still want to live here.</li>
                <li>Fines may be challenged at the Chore Coordinator’s report.</li>
                <li>Neglect or abuse of house officer positions is subject to a fine.</li>
            </ol>

            <p class="caps">New Members</p>
            <ol>
                <li>It is strongly encouraged for new members to interact with other members, attend regular meetings, give back to Oxford, or do anything to strive towards a higher level of recovery. In other words, we don’t pay to lay around the house…grow or go!</li>
                <li>For the first sixty days of occupancy, new members are not allowed to have overnights in or outside of the house. Overnights are considered anything after midnight.</li>
                <li>Overnights after sixty days are allowed as long as the house is given a number where you may be reached.</li>
                <li>Overnights (outside the house) are limited to no more than 3 nights per week and no more than 2 nights consecutively unless other arrangements have been made with the house. A member may have an overnight guest no more than 3 nights a week. If you are constantly staying out, you may want to consider moving out so room can be made for someone that needs and wants to be here.</li>
                <li>Must attend 3 Chapter meetings within the first 90 days after moving in.</li>
                <li>Must attend a minimum of 3 meetings a week for their recovery, i.e. AA/NA, therapy, celebrate recovery, church, the gym, etc. within their first 60 days after moving in.</li>
                <li>If you are behind on your E.E.S you will stay on New Member Probation until you’re caught up.</li>
            </ol>

            <p class="caps">Medications</p>
            <ol>
                <li>All prescribed medications must be listed upon acceptance into the house. Any medication prescribed during occupancy must also be reported at the next house meeting and must also be approved. If there is any change in your meds it will be reported to the house.</li>
                <li>Medications are to be kept out of sight of other members at all times.</li>
                <li>Health aids containing any alcohol content are prohibited (mouthwash, nyquil, cold medicine, etc.).</li>
            </ol>

            <p class="caps">Children</p>
            <ol>
                <li>You are responsible for your child’s behavior.</li>
                <li>No corporal punishment, yelling, or swearing at children.</li>
            </ol>

            <p>I have read and agree to these rules and expectations for residing within an Oxford House in the State of Colorado.</p>

            <div class="signature-grid">
                <div class="field">
                    <label>Signature</label>
                    <input class="line-input autosave" type="text" name="new_member_signature" value="<?= h($form['new_member_signature']) ?>">
                </div>
                <div class="field">
                    <label>Date</label>
                    <input class="line-input autosave" type="date" name="signature_date" value="<?= h($form['signature_date']) ?>">
                </div>
            </div>

            <div class="section-title">Refund Policy in Oxford House</div>
            <p><strong>Relapse</strong><br>When a member has relapsed, they will be required to leave the house immediately and the house will refund any unused EES remaining within 30 days.</p>
            <p><strong>Disruptive Behavior</strong><br>When a person is asked to leave a house because of disruptive behavior, all of the unused EES from the date of dismissal must be returned to the individual. Any monies owed to the house for fines, phone, cable, cleaning of room will be deducted. The house has up to 30 days to refund any money due to an individual if it creates a financial burden on the house.</p>
            <p><strong>Other Causes</strong><br>If a person decides to leave the house on their own, a two-week notice is required. The house may keep two weeks EES if the person failed to give notice or moved out before the two weeks were up.</p>
            <p><strong>Note</strong><br>If a person has resided within an Oxford House previously, and left owing an outstanding financial obligation to any Oxford Houses, then the individual is required to make some financial payment arrangements to the certain Oxford House owed either before moving in or upon entering another Oxford House. Failure to do so will result in termination of residency immediately.</p>

            <div class="line-row">
                <div class="field">
                    <label>Move-In Date</label>
                    <input class="line-input autosave" type="date" name="refund_move_in_date" value="<?= h($form['refund_move_in_date'] ?: $form['move_in_date']) ?>">
                </div>
            </div>

            <div class="signature-grid">
                <div class="field">
                    <label>New Member Signature</label>
                    <input class="line-input autosave" type="text" name="new_member_signature" value="<?= h($form['new_member_signature']) ?>">
                </div>
                <div class="field">
                    <label>Date</label>
                    <input class="line-input autosave" type="date" name="new_member_signature_date" value="<?= h($form['new_member_signature_date']) ?>">
                </div>
            </div>

            <div class="signature-grid">
                <div class="field">
                    <label>House President or HSR Signature</label>
                    <input class="line-input autosave" type="text" name="president_hsr_signature" value="<?= h($form['president_hsr_signature']) ?>">
                </div>
                <div class="field">
                    <label>Date</label>
                    <input class="line-input autosave" type="date" name="president_hsr_signature_date" value="<?= h($form['president_hsr_signature_date']) ?>">
                </div>
            </div>

            <div class="section-title">First 60-90 Days—Expectations</div>
            <p class="center caps small">(These expectations are put into effect so the newcomer will interact and adhere to the procedures that make Oxford Houses safe and comfortable)</p>
            <ol>
                <li>No overnight guests.</li>
                <li>No overnights outside the house (with the exception of Oxford House functions).</li>
                <li>11:00 P.M. curfew on weekdays and weekends for the first 60 days.</li>
                <li>Guests must be gone in accordance with curfew requirements (11:00 P.M.).</li>
                <li>Read and familiarize yourself with the Oxford House Manual.</li>
                <li>Interaction with house members is strongly encouraged (this way we can get to know you and you can get to know us).</li>
                <li>Must attend a minimum of 3 meetings a week, i.e, AA/NA, celebrate recovery, church, some sort of positive living based program for recovery.</li>
                <li>If you are behind on your EES you will stay on the New Member Status until you are caught up in full.</li>
                <li>All new member’s must account for 3 “outreach days” during the first 90 days. New members must attend the first 3 chapter meetings.</li>
                <li>New member’s will be probationarily voted in. They will be reevaluated and voted in officially after 30 days has elapsed.</li>
            </ol>

            <div class="signature-grid">
                <div class="field">
                    <label>Signature</label>
                    <input class="line-input autosave" type="text" name="expectations_signature" value="<?= h($form['expectations_signature']) ?>">
                </div>
                <div class="field">
                    <label>Date</label>
                    <input class="line-input autosave" type="date" name="expectations_signature_date" value="<?= h($form['expectations_signature_date']) ?>">
                </div>
            </div>

            <div class="section-title">Medication Issues in Oxford Homes</div>
            <p>The following information came from Oxford House World Services in 1998 and is still policy regarding narcotic medication in houses:</p>
            <p>“Any member prescribed narcotics by a physician must have a letter from his or her doctor acknowledging that they are aware their patient is a recovering alcoholic or drug addict and that, in their professional opinion, there is no alternative of suitable non-narcotic pain medication for the condition. The letter should also state the length of time the patient should be on their prescribed medicine.</p>
            <p>In addition, the group conscience of the House must determine if the presence of narcotics in the house might trigger or have a negative effect on their own recovery and vote accordingly, keeping in mind that we are a zero-tolerance program.”</p>
            <p>Medication issues seem to be on ongoing problem. Lately we’ve heard of several instances, in different houses, where members have been prescribed narcotics. We know the above policy has not been followed for a long time, but think it needs to be. It’s one of the guidelines that make us Oxford Houses and it is important.</p>
            <p>Several other absolutes about medication that need to be repeated are:</p>
            <ol>
                <li>All medication needs to be brought to the house’s attention, not just narcotics or pain medication. There are plenty of other medications that are abusable. Also, it’s a safety issue. In the event of a medical emergency, you need to know all medications a house member is taking in order for medical personnel to treat them. The information needs to be in writing somewhere accessible, preferably in the member’s file or in the minutes.</li>
                <li>Taking prescription medication in any manner or dosage other than as prescribed is a relapse. Taking over-the-counter medications at a higher dosage or more frequently than the manufacturer recommends may be considered a relapse.</li>
                <li>Sharing prescription medication or taking anyone else’s medication, with or without their permission, is not only a relapse; it is against the law.</li>
            </ol>
            <p>Houses are encouraged to decide within themselves how they want to handle other medication issues. For example:</p>
            <ol>
                <li>It is suggested that member who are prescribed psychiatric medication stop taking their medication ONLY with their healthcare professional’s approval.</li>
                <li>Because many over-the-counter medications can be abused, houses may adopt policies banning certain OTC drugs such as Nyquil. The use of any over-the counter “diet pills” is strongly discouraged!</li>
                <li>It is suggested that members keep their medications out of sight to avoid triggering other house members.</li>
            </ol>

            <div class="signature-grid">
                <div class="field">
                    <label>Signature</label>
                    <input class="line-input autosave" type="text" name="medication_signature" value="<?= h($form['medication_signature']) ?>">
                </div>
                <div class="field">
                    <label>Date</label>
                    <input class="line-input autosave" type="date" name="medication_signature_date" value="<?= h($form['medication_signature_date']) ?>">
                </div>
            </div>

            <div class="section-title">Emergency Medical Information Form</div>
            <p class="center">This form is used for emergency medical use only</p>

            <div class="line-row">
                <div class="field"><label>Name:</label><input class="line-input autosave" type="text" name="emergency_name" value="<?= h($form['emergency_name']) ?>"></div>
                <div class="field" style="max-width:180px;"><label>Age:</label><input class="line-input autosave" type="text" name="emergency_age" value="<?= h($form['emergency_age']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>Date of Birth:</label><input class="line-input autosave" type="text" name="emergency_dob" value="<?= h($form['emergency_dob']) ?>"></div>
                <div class="field" style="max-width:200px;"><label>Blood Type:</label><input class="line-input autosave" type="text" name="blood_type" value="<?= h($form['blood_type']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>Primary Physician:</label><input class="line-input autosave" type="text" name="primary_physician" value="<?= h($form['primary_physician']) ?>"></div>
                <div class="field" style="max-width:240px;"><label>Phone:</label><input class="line-input autosave" type="text" name="physician_phone" value="<?= h($form['physician_phone']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>Hospital or Clinic:</label><input class="line-input autosave" type="text" name="hospital_clinic" value="<?= h($form['hospital_clinic']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>Insurance:</label><input class="line-input autosave" type="text" name="insurance" value="<?= h($form['insurance']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field" style="display:block;">
                    <label>Allergies:</label>
                    <textarea class="line-textarea autosave" name="allergies"><?= h($form['allergies']) ?></textarea>
                </div>
            </div>
            <div class="line-row">
                <div class="field" style="display:block;">
                    <label>Medications:</label>
                    <textarea class="line-textarea autosave" name="medications"><?= h($form['medications']) ?></textarea>
                </div>
            </div>
            <div class="line-row">
                <div class="field" style="display:block;">
                    <label>Medical History (major surgeries, contracted diseases, hereditary health problems, etc.):</label>
                    <textarea class="line-textarea autosave" name="medical_history"><?= h($form['medical_history']) ?></textarea>
                </div>
            </div>

            <p>In case of medical emergency contact:</p>
            <div class="line-row">
                <div class="field"><label>1. Name</label><input class="line-input autosave" type="text" name="contact1_name" value="<?= h($form['contact1_name']) ?>"></div>
                <div class="field" style="max-width:260px;"><label>Phone</label><input class="line-input autosave" type="text" name="contact1_phone" value="<?= h($form['contact1_phone']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>2. Name</label><input class="line-input autosave" type="text" name="contact2_name" value="<?= h($form['contact2_name']) ?>"></div>
                <div class="field" style="max-width:260px;"><label>Phone</label><input class="line-input autosave" type="text" name="contact2_phone" value="<?= h($form['contact2_phone']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>3. Name</label><input class="line-input autosave" type="text" name="contact3_name" value="<?= h($form['contact3_name']) ?>"></div>
                <div class="field" style="max-width:260px;"><label>Phone</label><input class="line-input autosave" type="text" name="contact3_phone" value="<?= h($form['contact3_phone']) ?>"></div>
            </div>

            <div class="section-title">Oxford House Personal Property List</div>
            <p>Please list any items that belong to you, which you will take with you when you move, that will be used in any of the common areas.</p>
            <div class="property-list">
                <?php for ($i = 0; $i < 15; $i++): ?>
                    <div class="row">
                        <div class="num"><?= $i + 1 ?>.</div>
                        <input class="autosave" type="text" name="property_items[]" value="<?= h($form['property_items'][$i] ?? '') ?>">
                    </div>
                <?php endfor; ?>
            </div>

            <p class="small">Note: The Oxford House is not responsible for any personal property placed within the common areas of the House.</p>

            <div class="line-row">
                <div class="field"><label>These items belong to</label><input class="line-input autosave" type="text" name="property_owner_name" value="<?= h($form['property_owner_name']) ?>"></div>
            </div>
            <div class="signature-grid">
                <div class="field">
                    <label>Signature</label>
                    <input class="line-input autosave" type="text" name="property_signature" value="<?= h($form['property_signature']) ?>">
                </div>
                <div class="field">
                    <label>Date</label>
                    <input class="line-input autosave" type="date" name="property_date" value="<?= h($form['property_date']) ?>">
                </div>
            </div>
            <div class="line-row">
                <div class="field"><label>Witness Signature</label><input class="line-input autosave" type="text" name="property_witness_signature" value="<?= h($form['property_witness_signature']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>These items were removed from house on</label><input class="line-input autosave" type="date" name="property_removed_date" value="<?= h($form['property_removed_date']) ?>"></div>
            </div>
            <div class="line-row">
                <div class="field"><label>Witness Signature</label><input class="line-input autosave" type="text" name="property_removed_witness_signature" value="<?= h($form['property_removed_witness_signature']) ?>"></div>
            </div>

            <div class="section-title no-print">Upload Scanned Copy</div>
            <div class="notice no-print">
                Upload a PDF or image scan. Once uploaded, loading this history record will show the uploaded copy first instead of the form.
            </div>
            <div class="line-row no-print">
                <div class="field">
                    <label>Scanned Copy</label>
                    <input type="file" name="scan_file" id="scan_file" accept=".pdf,.png,.jpg,.jpeg,.webp">
                </div>
                <div class="field" style="max-width:220px;">
                    <button type="button" class="primary" onclick="uploadScan()">Upload Scan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const form = document.getElementById('packetForm');
const saveStatus = document.getElementById('saveStatus');
const recordIdInput = document.getElementById('record_id');
let saveTimer = null;
let inFlight = false;

function updateTotal() {
    const ees = parseFloat(document.querySelector('input[name="ees_amount"]')?.value || 0);
    const move = parseFloat(document.querySelector('input[name="move_in_fee"]')?.value || 0);
    const other = parseFloat(document.querySelector('input[name="other_charge"]')?.value || 0);
    const total = (isNaN(ees) ? 0 : ees) + (isNaN(move) ? 0 : move) + (isNaN(other) ? 0 : other);
    document.getElementById('total_due').value = total.toFixed(2);

    const inline = document.querySelector('input[name="ees_amount_inline"]');
    const main = document.querySelector('input[name="ees_amount"]');
    if (document.activeElement === main && inline) {
        inline.value = main.value;
    }
}

function syncLinkedMoneyInputs() {
    document.querySelectorAll('[data-sync-target]').forEach(el => {
        el.addEventListener('input', () => {
            const target = document.querySelector(`input[name="${el.dataset.syncTarget}"]`);
            if (target) {
                target.value = el.value;
                updateTotal();
                queueSave();
            }
        });
    });

    const mainEes = document.querySelector('input[name="ees_amount"]');
    const inline = document.querySelector('input[name="ees_amount_inline"]');
    if (mainEes && inline) {
        mainEes.addEventListener('input', () => {
            inline.value = mainEes.value;
            updateTotal();
        });
    }

    document.querySelectorAll('.money-field').forEach(el => {
        el.addEventListener('input', updateTotal);
    });

    updateTotal();
}

function queueSave() {
    saveStatus.textContent = 'Saving…';
    window.clearTimeout(saveTimer);
    saveTimer = window.setTimeout(autosave, 600);
}

async function autosave() {
    if (inFlight || !form) return;
    inFlight = true;
    updateTotal();

    const fd = new FormData(form);
    fd.set('action', 'autosave');

    try {
        const res = await fetch('', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!res.ok || !data.ok) {
            throw new Error(data.error || 'Save failed');
        }
        if (data.record_id) {
            recordIdInput.value = data.record_id;
        }
        saveStatus.textContent = 'Saved: ' + data.updated_at;
    } catch (err) {
        saveStatus.textContent = 'Save error: ' + err.message;
    } finally {
        inFlight = false;
    }
}

async function uploadScan() {
    const input = document.getElementById('scan_file');
    if (!input.files.length) {
        alert('Choose a PDF or image first.');
        return;
    }
    if (!recordIdInput.value) {
        await autosave();
    }
    if (!recordIdInput.value) {
        alert('Please enter at least the member name before uploading.');
        return;
    }

    const fd = new FormData();
    fd.append('action', 'upload_scan');
    fd.append('record_id', recordIdInput.value);
    fd.append('scan_file', input.files[0]);

    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.enctype = 'multipart/form-data';
    tempForm.style.display = 'none';

    for (const [key, value] of fd.entries()) {
        if (key === 'scan_file') continue;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = key;
        hidden.value = value;
        tempForm.appendChild(hidden);
    }

    const fileClone = input.cloneNode();
    tempForm.appendChild(fileClone);
    document.body.appendChild(tempForm);

    const dt = new DataTransfer();
    dt.items.add(input.files[0]);
    fileClone.files = dt.files;
    tempForm.submit();
}

function toggleView() {
    const scan = document.getElementById('scanView');
    const formView = document.getElementById('formView');
    const btn = document.getElementById('toggleViewBtn');
    if (!scan || !formView || !btn) return;

    const showingScan = scan.style.display !== 'none';
    if (showingScan) {
        scan.style.display = 'none';
        formView.style.display = '';
        btn.textContent = 'Show Scan';
    } else {
        scan.style.display = '';
        formView.style.display = 'none';
        btn.textContent = 'Show Form';
    }
}

document.querySelectorAll('.autosave').forEach(el => {
    el.addEventListener('input', queueSave);
    el.addEventListener('change', queueSave);
});

syncLinkedMoneyInputs();
</script>
</body>
</html>
