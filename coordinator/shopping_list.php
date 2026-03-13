<?php
/**
 * Oxford House Shopping List
 * Single-file PHP app
 * - Layout rebuilt to closely match the uploaded sheet
 * - Real checkboxes plus quantity lines for numbers
 * - Auto-save to MySQL
 * - History dropdown by date
 * - Reload/edit prior records
 * - Optional uploaded scanned copy; history shows upload instead of the form when present
 * - Print button
 */
declare(strict_types=1);

require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const UPLOAD_DIR = __DIR__ . '/uploads/shopping_list';
const MAX_UPLOAD_BYTES = 10 * 1024 * 1024;

$sections = [
    [
        'key' => 'cleaning_supplies',
        'title' => 'CLEANING SUPPLIES',
        'items' => [
            ['key' => 'all_purpose_cleaner', 'label' => 'All-purpose Cleaner'],
            ['key' => 'glass_cleaner', 'label' => 'Glass Cleaner'],
            ['key' => 'floor_cleaner', 'label' => 'Floor Cleaner'],
            ['key' => 'kitchen_cleaner', 'label' => 'Kitchen Cleaner'],
            ['key' => 'bathroom_cleaner', 'label' => 'Bathroom Cleaner'],
            ['key' => 'toilet_bowl_cleaner', 'label' => 'Toilet Bowl Cleaner'],
            ['key' => 'carpet_powder', 'label' => 'Carpet Powder'],
            ['key' => 'wood_polish', 'label' => 'Wood Polish'],
            ['key' => 'gloves', 'label' => 'Gloves'],
        ],
    ],
    [
        'key' => 'laundry',
        'title' => 'LAUNDRY',
        'items' => [
            ['key' => 'laundry_soap', 'label' => 'Laundry Soap'],
            ['key' => 'fabric_softener', 'label' => 'Fabric Softener'],
            ['key' => 'dryer_sheets', 'label' => 'Dryer Sheets'],
            ['key' => 'bleach', 'label' => 'Bleach'],
            ['key' => 'stain_remover', 'label' => 'Stain Remover'],
            ['key' => 'starch', 'label' => 'Starch'],
        ],
    ],
    [
        'key' => 'paper_products',
        'title' => 'PAPER PRODUCTS',
        'items' => [
            ['key' => 'toilet_paper', 'label' => 'Toilet Paper'],
            ['key' => 'paper_towels', 'label' => 'Paper Towels'],
            ['key' => 'kleenex', 'label' => 'Kleenex'],
            ['key' => 'napkins', 'label' => 'Napkins'],
            ['key' => 'paper_plates', 'label' => 'Paper Plates'],
            ['key' => 'plastic_ware', 'label' => 'Plastic Ware'],
        ],
    ],
    [
        'key' => 'bags_wrap',
        'title' => 'BAGS & WRAP',
        'items' => [
            ['key' => 'large_trash_bags', 'label' => 'Large Trash Bags'],
            ['key' => 'small_trash_bags', 'label' => 'Small Trash Bags'],
            ['key' => 'sandwich_bags', 'label' => 'Sandwich Bags'],
            ['key' => 'freezer_bags', 'label' => 'Freezer Bags'],
            ['key' => 'aluminum_foil', 'label' => 'Aluminum Foil'],
            ['key' => 'plastic_wrap', 'label' => 'Plastic Wrap'],
        ],
    ],
    [
        'key' => 'miscellaneous',
        'title' => 'MISCELLANEOUS',
        'items' => [
            ['key' => 'aspirin_advil', 'label' => 'Aspirin/Advil'],
            ['key' => 'band_aids', 'label' => 'Band-aids'],
            ['key' => 'light_bulbs', 'label' => 'Light Bulbs'],
            ['key' => 'salt', 'label' => 'Salt'],
            ['key' => 'pepper', 'label' => 'Pepper'],
            ['key' => 'non_stick_spray', 'label' => 'Non-stick Spray'],
            ['key' => 'cooking_oil', 'label' => 'Cooking Oil'],
            ['key' => 'air_freshener', 'label' => 'Air Freshener'],
        ],
    ],
    [
        'key' => 'coffee',
        'title' => 'COFFEE',
        'items' => [
            ['key' => 'coffee', 'label' => 'Coffee'],
            ['key' => 'filters', 'label' => 'Filters'],
            ['key' => 'sugar', 'label' => 'Sugar'],
            ['key' => 'sweetener', 'label' => 'Sweetener'],
            ['key' => 'creamer', 'label' => 'Creamer'],
        ],
    ],
    [
        'key' => 'accessories',
        'title' => 'ACCESSORIES',
        'items' => [
            ['key' => 'rags', 'label' => 'Rags'],
            ['key' => 'sponges', 'label' => 'Sponges'],
            ['key' => 'scrub_pads', 'label' => 'Scrub Pads'],
            ['key' => 'vacuum_bag_filter', 'label' => 'Vacuum Bag/Filter'],
            ['key' => 'ac_filter', 'label' => 'A/C Filter'],
        ],
    ],
    [
        'key' => 'soap',
        'title' => 'SOAP',
        'items' => [
            ['key' => 'hand_soap', 'label' => 'Hand Soap'],
            ['key' => 'dish_soap', 'label' => 'Dish Soap'],
            ['key' => 'dishwasher_soap', 'label' => 'Dishwasher Soap'],
        ],
    ],
    [
        'key' => 'other',
        'title' => 'OTHER',
        'items' => [
            ['key' => 'other_1', 'label' => ''],
            ['key' => 'other_2', 'label' => ''],
            ['key' => 'other_3', 'label' => ''],
            ['key' => 'other_4', 'label' => ''],
            ['key' => 'other_5', 'label' => ''],
            ['key' => 'other_6', 'label' => ''],
        ],
    ],
];

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
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : null;
}

function db(): PDO
{
    global $dbHost, $dbName, $dbUser, $dbPass;
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    return $pdo;
}

function ensureStorage(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
}

function ensureTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS oxford_shopping_lists (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shopping_date DATE DEFAULT NULL,
            title VARCHAR(255) NOT NULL DEFAULT 'Oxford House Shopping List',
            items_json LONGTEXT NOT NULL,
            checked_json LONGTEXT NOT NULL,
            total_checked INT NOT NULL DEFAULT 0,
            total_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            uploaded_copy_path VARCHAR(500) DEFAULT NULL,
            uploaded_copy_name VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_shopping_date (shopping_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $columns = $pdo->query("SHOW COLUMNS FROM oxford_shopping_lists")->fetchAll(PDO::FETCH_COLUMN, 0);
    if (!in_array('checked_json', $columns, true)) {
        $pdo->exec("ALTER TABLE oxford_shopping_lists ADD COLUMN checked_json LONGTEXT NOT NULL AFTER items_json");
    }
    if (!in_array('total_checked', $columns, true)) {
        $pdo->exec("ALTER TABLE oxford_shopping_lists ADD COLUMN total_checked INT NOT NULL DEFAULT 0 AFTER checked_json");
    }
    if (!in_array('total_quantity', $columns, true)) {
        $pdo->exec("ALTER TABLE oxford_shopping_lists ADD COLUMN total_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_checked");
    }
}

function blankQuantities(array $sections): array
{
    $data = [];
    foreach ($sections as $section) {
        foreach ($section['items'] as $item) {
            $data[$item['key']] = '';
        }
    }
    return $data;
}

function blankChecks(array $sections): array
{
    $data = [];
    foreach ($sections as $section) {
        foreach ($section['items'] as $item) {
            $data[$item['key']] = 0;
        }
    }
    return $data;
}

function collectQuantities(array $sections, array $source): array
{
    $data = blankQuantities($sections);
    foreach ($data as $key => $_) {
        $value = trim((string)($source['qty'][$key] ?? ''));
        $data[$key] = $value;
    }
    return $data;
}

function collectChecks(array $sections, array $source): array
{
    $data = blankChecks($sections);
    foreach ($data as $key => $_) {
        $data[$key] = isset($source['checked'][$key]) ? 1 : 0;
    }
    return $data;
}

function countChecked(array $checked): int
{
    return array_sum(array_map('intval', $checked));
}

function totalQuantity(array $quantities): float
{
    $total = 0.0;
    foreach ($quantities as $value) {
        $value = trim((string)$value);
        if ($value === '') {
            continue;
        }
        $num = preg_replace('/[^0-9.\-]/', '', $value);
        if ($num !== '' && is_numeric($num)) {
            $total += (float)$num;
        }
    }
    return $total;
}

function fetchHistory(PDO $pdo): array
{
    return $pdo->query(
        "SELECT id, shopping_date, uploaded_copy_name, updated_at
         FROM oxford_shopping_lists
         ORDER BY shopping_date DESC, id DESC"
    )->fetchAll();
}

function fetchRecord(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM oxford_shopping_lists WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function upsertRecord(PDO $pdo, ?int $id, ?string $shoppingDate, array $quantities, array $checked, ?string $uploadPath = null, ?string $uploadName = null): int
{
    $quantitiesJson = json_encode($quantities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $checkedJson = json_encode($checked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $totalChecked = countChecked($checked);
    $totalQty = totalQuantity($quantities);

    if ($id !== null) {
        $stmt = $pdo->prepare(
            "UPDATE oxford_shopping_lists
             SET shopping_date = :shopping_date,
                 items_json = :items_json,
                 checked_json = :checked_json,
                 total_checked = :total_checked,
                 total_quantity = :total_quantity,
                 uploaded_copy_path = COALESCE(:uploaded_copy_path, uploaded_copy_path),
                 uploaded_copy_name = COALESCE(:uploaded_copy_name, uploaded_copy_name)
             WHERE id = :id"
        );
        $stmt->execute([
            ':shopping_date' => $shoppingDate,
            ':items_json' => $quantitiesJson,
            ':checked_json' => $checkedJson,
            ':total_checked' => $totalChecked,
            ':total_quantity' => $totalQty,
            ':uploaded_copy_path' => $uploadPath,
            ':uploaded_copy_name' => $uploadName,
            ':id' => $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO oxford_shopping_lists (
            shopping_date, title, items_json, checked_json, total_checked, total_quantity, uploaded_copy_path, uploaded_copy_name
        ) VALUES (
            :shopping_date, :title, :items_json, :checked_json, :total_checked, :total_quantity, :uploaded_copy_path, :uploaded_copy_name
        )
        ON DUPLICATE KEY UPDATE
            items_json = VALUES(items_json),
            checked_json = VALUES(checked_json),
            total_checked = VALUES(total_checked),
            total_quantity = VALUES(total_quantity),
            uploaded_copy_path = COALESCE(VALUES(uploaded_copy_path), uploaded_copy_path),
            uploaded_copy_name = COALESCE(VALUES(uploaded_copy_name), uploaded_copy_name),
            updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([
        ':shopping_date' => $shoppingDate,
        ':title' => 'Oxford House Shopping List',
        ':items_json' => $quantitiesJson,
        ':checked_json' => $checkedJson,
        ':total_checked' => $totalChecked,
        ':total_quantity' => $totalQty,
        ':uploaded_copy_path' => $uploadPath,
        ':uploaded_copy_name' => $uploadName,
    ]);

    $stmt = $pdo->prepare("SELECT id FROM oxford_shopping_lists WHERE shopping_date <=> :shopping_date LIMIT 1");
    $stmt->execute([':shopping_date' => $shoppingDate]);
    $row = $stmt->fetch();
    return (int)($row['id'] ?? $pdo->lastInsertId());
}

function handleUpload(string $field): array
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
        return [null, null];
    }
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Uploaded file is too large.');
    }

    $originalName = (string)($file['name'] ?? 'upload');
    $tmpName = (string)($file['tmp_name'] ?? '');
    $ext = strtolower((string)pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Allowed upload types: PDF, PNG, JPG, JPEG, WEBP.');
    }

    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $filename = $base . '_' . date('Ymd_His') . '.' . $ext;
    $target = UPLOAD_DIR . '/' . $filename;
    if (!move_uploaded_file($tmpName, $target)) {
        throw new RuntimeException('Unable to save uploaded file.');
    }

    return ['uploads/shopping_list/' . $filename, $originalName];
}

$pdo = db();
ensureStorage();
ensureTable($pdo);

$form = [
    'id' => null,
    'shopping_date' => date('Y-m-d'),
    'quantities' => blankQuantities($sections),
    'checked' => blankChecks($sections),
    'uploaded_copy_path' => null,
    'uploaded_copy_name' => null,
    'total_checked' => 0,
    'total_quantity' => 0.0,
];
$messages = [];
$errors = [];
$selectedId = isset($_GET['history_id']) ? (int)$_GET['history_id'] : null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postedId = isset($_POST['record_id']) && $_POST['record_id'] !== '' ? (int)$_POST['record_id'] : null;
        $shoppingDate = normalizeDate($_POST['shopping_date'] ?? '') ?? date('Y-m-d');
        $quantities = collectQuantities($sections, $_POST);
        $checked = collectChecks($sections, $_POST);

        $uploadPath = null;
        $uploadName = null;
        if (!empty($_FILES['uploaded_copy']['name'] ?? '')) {
            [$uploadPath, $uploadName] = handleUpload('uploaded_copy');
        }

        $savedId = upsertRecord($pdo, $postedId, $shoppingDate, $quantities, $checked, $uploadPath, $uploadName);
        $selectedId = $savedId;
        $messages[] = 'Shopping list saved successfully.';
    }

    if ($selectedId) {
        $record = fetchRecord($pdo, $selectedId);
        if ($record) {
            $form['id'] = (int)$record['id'];
            $form['shopping_date'] = (string)$record['shopping_date'];
            $form['quantities'] = array_replace(blankQuantities($sections), json_decode((string)$record['items_json'], true) ?: []);
            $form['checked'] = array_replace(blankChecks($sections), json_decode((string)$record['checked_json'], true) ?: []);
            $form['uploaded_copy_path'] = $record['uploaded_copy_path'];
            $form['uploaded_copy_name'] = $record['uploaded_copy_name'];
            $form['total_checked'] = (int)$record['total_checked'];
            $form['total_quantity'] = (float)$record['total_quantity'];
        }
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

$history = fetchHistory($pdo);
$showUploadedCopy = !empty($form['uploaded_copy_path']);

$layoutRows = [
    ['cleaning_supplies', 'laundry', 'paper_products'],
    ['bags_wrap', 'miscellaneous', 'coffee'],
    ['accessories', 'soap', 'other'],
];

$sectionsByKey = [];
foreach ($sections as $section) {
    $sectionsByKey[$section['key']] = $section;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Shopping List</title>
    <style>
        :root {
            --paper-width: 8.5in;
            --paper-min-height: 11in;
            --ink: #111;
            --bg: #efefef;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 18px;
            background: var(--bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
        }
        .app { max-width: 1100px; margin: 0 auto; }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .toolbar-left, .toolbar-right {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        select, input, button {
            font: inherit;
        }
        button {
            padding: 8px 14px;
            border: 1px solid #777;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        .notice {
            margin-bottom: 12px;
            padding: 10px 12px;
            border-radius: 6px;
        }
        .success { background: #eaf7ea; border: 1px solid #7db67d; }
        .error { background: #fff0f0; border: 1px solid #d58b8b; }
        .sheet-wrap {
            background: #fff;
            padding: 18px;
            box-shadow: 0 10px 28px rgba(0,0,0,.14);
        }
        .meta-row {
            display: grid;
            grid-template-columns: 160px 1fr auto;
            gap: 12px;
            margin-bottom: 12px;
            align-items: center;
        }
        .meta-row label { font-weight: 700; }
        .sheet {
            width: var(--paper-width);
            min-height: var(--paper-min-height);
            margin: 0 auto;
            background: white;
            padding: 0.18in 0.35in 0.35in 0.35in;
        }
        .header {
            display: grid;
            grid-template-columns: 1.35in 1fr 2.2in;
            align-items: start;
            column-gap: 0.22in;
            margin-bottom: 0.24in;
        }
        .logo-box {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 0.02in;
        }
        .logo-box img {
            width: 1.08in;
            height: auto;
            display: block;
        }
        .heading-box {
            text-align: left;
            line-height: 1.02;
            padding-top: 0.01in;
        }
        .heading-box .main {
            font-size: 0.50in;
            font-weight: 800;
            letter-spacing: 0.01in;
        }
        .heading-box .sub {
            margin-top: 0.05in;
            margin-left: 0.46in;
            font-size: 0.11in;
            line-height: 1.15;
        }
        .date-box {
            text-align: left;
            padding-top: 0.29in;
            white-space: nowrap;
        }
        .date-box .date-label {
            font-size: 0.18in;
            font-weight: 800;
            vertical-align: middle;
        }
        .date-box .date-line {
            display: inline-block;
            vertical-align: middle;
            width: 1.7in;
            border-bottom: 0.03in solid #111;
            margin-left: 0.06in;
            height: 0.19in;
            position: relative;
        }
        .date-box .date-line input {
            position: absolute;
            inset: -0.03in 0 0 0;
            width: 100%;
            height: 0.21in;
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.16in;
            text-align: center;
        }
        .layout-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            column-gap: 0.38in;
            margin-bottom: 0.34in;
            align-items: start;
        }
        .section-title {
            font-size: 0.22in;
            font-weight: 800;
            line-height: 1;
            margin: 0 0 0.08in;
            white-space: nowrap;
        }
        .item-list {
            display: flex;
            flex-direction: column;
            gap: 0.085in;
        }
        .item-row {
            display: flex;
            align-items: center;
            min-height: 0.23in;
            white-space: nowrap;
        }
        .box {
            width: 0.16in;
            height: 0.16in;
            margin-right: 0.05in;
            accent-color: #111;
            flex: 0 0 auto;
        }
        .item-label {
            font-size: 0.12in;
            line-height: 1;
            flex: 0 1 auto;
        }
        .line-wrap {
            flex: 1 1 auto;
            min-width: 0.55in;
            margin-left: 0.04in;
            position: relative;
            height: 0.18in;
        }
        .line-wrap.short { max-width: 0.90in; }
        .line-wrap.medium { max-width: 1.00in; }
        .line-wrap.long { max-width: 1.15in; }
        .line-wrap.xlong { max-width: 1.30in; }
        .line {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.01in;
            border-bottom: 0.022in solid #111;
        }
        .qty-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: transparent;
            text-align: center;
            font-size: 0.11in;
            padding: 0 0.02in;
        }
        .other .item-row { min-height: 0.235in; }
        .other .line-wrap {
            max-width: none;
            margin-left: 0.06in;
        }
        .summary-bar {
            margin-top: 0.18in;
            display: flex;
            justify-content: flex-end;
            gap: 0.35in;
            font-size: 0.13in;
            font-weight: 700;
        }
        .uploaded-preview {
            text-align: center;
            margin-top: 8px;
        }
        .uploaded-preview iframe,
        .uploaded-preview img {
            width: 100%;
            max-width: 850px;
            border: 1px solid #bbb;
            background: #fff;
        }
        .uploaded-preview iframe { min-height: 1000px; }
        .uploaded-preview img { min-height: auto; }
        @media screen and (max-width: 980px) {
            .sheet { width: 100%; min-height: auto; padding: 16px; }
            .header { grid-template-columns: 100px 1fr; row-gap: 10px; }
            .date-box { grid-column: 1 / -1; padding-top: 0; }
            .layout-row { grid-template-columns: 1fr; row-gap: 20px; }
            .heading-box .main { font-size: 42px; }
            .heading-box .sub { margin-left: 0; font-size: 14px; }
            .section-title { font-size: 26px; }
            .item-label { font-size: 20px; }
            .qty-input { font-size: 16px; }
            .meta-row { grid-template-columns: 1fr; }
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar, .notice, .meta-row { display: none !important; }
            .sheet-wrap { box-shadow: none; padding: 0; }
            .sheet { width: 8.5in; min-height: 11in; }
        }
    </style>
</head>
<body>
<div class="app">
    <form method="get" class="toolbar">
        <div class="toolbar-left">
            <label for="history_id"><strong>History by Date:</strong></label>
            <select name="history_id" id="history_id" onchange="this.form.submit()">
                <option value="">Select saved date...</option>
                <?php foreach ($history as $row): ?>
                    <option value="<?= (int)$row['id'] ?>" <?= ((int)$form['id'] === (int)$row['id']) ? 'selected' : '' ?>>
                        <?= h($row['shopping_date'] ?: 'No Date') ?><?= !empty($row['uploaded_copy_name']) ? ' — Uploaded Copy' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="toolbar-right">
            <button type="button" onclick="window.location.href = window.location.pathname;">New Form</button>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </form>

    <?php foreach ($messages as $message): ?>
        <div class="notice success"><?= h($message) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $error): ?>
        <div class="notice error"><?= h($error) ?></div>
    <?php endforeach; ?>

    <div class="sheet-wrap">
        <form method="post" enctype="multipart/form-data" id="shoppingForm">
            <input type="hidden" name="record_id" value="<?= h($form['id']) ?>">

            <div class="meta-row">
                <div>
                    <label for="shopping_date_top">Record Date</label>
                    <input type="date" name="shopping_date" id="shopping_date_top" value="<?= h($form['shopping_date']) ?>">
                </div>
                <div>
                    <label for="uploaded_copy">Upload Copy</label>
                    <input type="file" name="uploaded_copy" id="uploaded_copy" accept=".pdf,.png,.jpg,.jpeg,.webp">
                </div>
                <div>
                    <button type="submit">Save</button>
                </div>
            </div>

            <?php if ($showUploadedCopy): ?>
                <div class="uploaded-preview">
                    <p><strong>Uploaded Copy:</strong> <?= h((string)$form['uploaded_copy_name']) ?></p>
                    <?php $ext = strtolower((string)pathinfo((string)$form['uploaded_copy_path'], PATHINFO_EXTENSION)); ?>
                    <?php if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)): ?>
                        <img src="<?= h((string)$form['uploaded_copy_path']) ?>" alt="Uploaded copy preview">
                    <?php else: ?>
                        <iframe src="<?= h((string)$form['uploaded_copy_path']) ?>"></iframe>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="sheet">
                    <div class="header">
                        <div class="logo-box">
                            <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo">
                        </div>
                        <div class="heading-box">
                            <div class="main">OXFORD HOUSE</div>
                            <div class="main">SHOPPING LIST</div>
                            <div class="sub">Inventory house supplies every week.<br>Bulk shopping saves time and money.</div>
                        </div>
                        <div class="date-box">
                            <span class="date-label">DATE:</span><span class="date-line"><input type="date" value="<?= h($form['shopping_date']) ?>" onchange="document.getElementById('shopping_date_top').value=this.value;"></span>
                        </div>
                    </div>

                    <?php
                    $sizeClasses = [
                        'all_purpose_cleaner' => 'short', 'glass_cleaner' => 'medium', 'floor_cleaner' => 'short', 'kitchen_cleaner' => 'short',
                        'bathroom_cleaner' => 'medium', 'toilet_bowl_cleaner' => 'medium', 'carpet_powder' => 'short', 'wood_polish' => 'short', 'gloves' => 'short',
                        'laundry_soap' => 'short', 'fabric_softener' => 'medium', 'dryer_sheets' => 'medium', 'bleach' => 'short', 'stain_remover' => 'medium', 'starch' => 'medium',
                        'toilet_paper' => 'short', 'paper_towels' => 'medium', 'kleenex' => 'short', 'napkins' => 'medium', 'paper_plates' => 'medium', 'plastic_ware' => 'medium',
                        'large_trash_bags' => 'short', 'small_trash_bags' => 'short', 'sandwich_bags' => 'short', 'freezer_bags' => 'medium', 'aluminum_foil' => 'short', 'plastic_wrap' => 'short',
                        'aspirin_advil' => 'medium', 'band_aids' => 'short', 'light_bulbs' => 'medium', 'salt' => 'short', 'pepper' => 'short', 'non_stick_spray' => 'medium', 'cooking_oil' => 'short', 'air_freshener' => 'short',
                        'coffee' => 'short', 'filters' => 'short', 'sugar' => 'medium', 'sweetener' => 'medium', 'creamer' => 'medium',
                        'rags' => 'short', 'sponges' => 'short', 'scrub_pads' => 'short', 'vacuum_bag_filter' => 'xlong', 'ac_filter' => 'short',
                        'hand_soap' => 'medium', 'dish_soap' => 'medium', 'dishwasher_soap' => 'xlong',
                    ];
                    ?>

                    <?php foreach ($layoutRows as $row): ?>
                        <div class="layout-row">
                            <?php foreach ($row as $sectionKey): $section = $sectionsByKey[$sectionKey]; ?>
                                <div class="section <?= $sectionKey === 'other' ? 'other' : '' ?>">
                                    <div class="section-title"><?= h($section['title']) ?></div>
                                    <div class="item-list">
                                        <?php foreach ($section['items'] as $item):
                                            $itemKey = $item['key'];
                                            $lineClass = $sizeClasses[$itemKey] ?? 'long';
                                        ?>
                                            <label class="item-row">
                                                <input class="box autosave" type="checkbox" name="checked[<?= h($itemKey) ?>]" value="1" <?= !empty($form['checked'][$itemKey]) ? 'checked' : '' ?>>
                                                <?php if ($item['label'] !== ''): ?>
                                                    <span class="item-label"><?= h($item['label']) ?></span>
                                                <?php endif; ?>
                                                <span class="line-wrap <?= h($lineClass) ?>">
                                                    <span class="line"></span>
                                                    <input class="qty-input autosave" type="text" inputmode="decimal" name="qty[<?= h($itemKey) ?>]" value="<?= h($form['quantities'][$itemKey] ?? '') ?>">
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-bar">
                        <span>Checked: <span id="checkedCount"><?= (int)$form['total_checked'] ?></span></span>
                        <span>Total Numbers: <span id="qtyTotal"><?= number_format((float)$form['total_quantity'], 2) ?></span></span>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('shoppingForm');
    const uploadInput = document.getElementById('uploaded_copy');
    const checkedCount = document.getElementById('checkedCount');
    const qtyTotal = document.getElementById('qtyTotal');
    let timer = null;

    function updateStats() {
        if (checkedCount) {
            checkedCount.textContent = String(document.querySelectorAll('input[name^="checked["]:checked').length);
        }
        if (qtyTotal) {
            let total = 0;
            document.querySelectorAll('input[name^="qty["]').forEach(input => {
                const cleaned = input.value.replace(/[^0-9.\-]/g, '');
                if (cleaned !== '' && !isNaN(cleaned)) {
                    total += parseFloat(cleaned);
                }
            });
            qtyTotal.textContent = total.toFixed(2);
        }
    }

    function autosave() {
        updateStats();
        if (uploadInput && uploadInput.files && uploadInput.files.length > 0) {
            return;
        }
        clearTimeout(timer);
        timer = setTimeout(() => {
            const data = new FormData(form);
            fetch(window.location.href, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(() => {});
        }, 700);
    }

    form.querySelectorAll('.autosave, #shopping_date_top').forEach(el => {
        el.addEventListener('input', autosave);
        el.addEventListener('change', autosave);
    });

    updateStats();
})();
</script>
</body>
</html>
