<?php
declare(strict_types=1);

require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';
const UPLOAD_DIR = __DIR__ . '/uploads/safety_checklists';

$rows = [
    ['section' => 'Outside'],
    ['label' => 'Gutters in good working order.'],
    ['label' => 'Driveway in good order. Clear of debris.'],
    ['label' => 'Front of House clear of debris. Any furniture is appropriate outside'],
    ['label' => 'Trash can area clean and well maintained.'],

    ['section' => 'Inside'],
    ['label' => 'House is generally clean-including walls and rugs.'],
    ['label' => 'Baseboards, blinds and ceiling fans free of dust and dirt.'],
    ['label' => 'Bedrooms free of clutter, trash and generally well-maintained.'],
    ['label' => 'No water leaks in or around bathrooms or under cabinets.'],
    ['label' => 'No mold on walls or ceilings.'],
    ['label' => 'Stairwells in good order, railings secure.'],

    ['section' => 'Fire Safety'],
    ['label' => 'Fire escape plan posted.'],
    ['label' => 'Outlets not overloaded.'],
    ['label' => 'A smoke detector in each room of the home.'],
    ['label' => 'A fire extinguisher in or near the kitchen, halls and stairwells'],
    ['label' => 'Second story houses must have fire escape ladders.'],
    ['label' => 'Absolutely no candles or incense, other than emergency candles.'],
    ['label' => 'Carbon Monoxide Detectors if necessary.'],
    ['label' => 'Light fixtures in good repair.'],

    ['section' => 'House Operations'],
    ['label' => 'All notebooks and records in good order.'],
    ['label' => 'File cabinet in good working order - locked box for checkbook.'],
    ['label' => 'Central cork board for house business.'],

    ['section' => 'Finances'],
    ['label' => 'Bank Statements available for review.'],
    ['label' => 'Financial records in order-notebooks and audits'],
];

function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function ensureUploadDir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
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

function createTableIfMissing(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS safety_inspection_checklists (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            house_name VARCHAR(255) NOT NULL DEFAULT '',
            inspection_date DATE DEFAULT NULL,
            inspector_name VARCHAR(255) NOT NULL DEFAULT '',
            checklist_json LONGTEXT NOT NULL,
            satisfactory_total INT NOT NULL DEFAULT 0,
            unsatisfactory_total INT NOT NULL DEFAULT 0,
            completed_total INT NOT NULL DEFAULT 0,
            uploaded_copy VARCHAR(500) DEFAULT NULL,
            original_upload_name VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_inspection_date (inspection_date),
            KEY idx_house_name (house_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function getBlankChecklist(array $rows): array
{
    $data = [];
    foreach ($rows as $index => $row) {
        if (isset($row['section'])) {
            continue;
        }
        $data[(string)$index] = [
            'satisfactory' => '',
            'unsatisfactory' => '',
            'when_completed' => '',
            'notes' => '',
        ];
    }
    return $data;
}

function normalizeChecklist(array $rows, array $input): array
{
    $normalized = getBlankChecklist($rows);
    foreach ($normalized as $index => $defaults) {
        $item = $input[$index] ?? [];
        $normalized[$index] = [
            'satisfactory' => !empty($item['satisfactory']) ? '1' : '',
            'unsatisfactory' => !empty($item['unsatisfactory']) ? '1' : '',
            'when_completed' => trim((string)($item['when_completed'] ?? '')),
            'notes' => trim((string)($item['notes'] ?? '')),
        ];
    }
    return $normalized;
}

function computeTotals(array $checklist): array
{
    $sat = 0;
    $unsat = 0;
    $completed = 0;
    foreach ($checklist as $row) {
        if (!empty($row['satisfactory'])) {
            $sat++;
        }
        if (!empty($row['unsatisfactory'])) {
            $unsat++;
        }
        if (trim((string)($row['when_completed'] ?? '')) !== '') {
            $completed++;
        }
    }
    return [$sat, $unsat, $completed];
}

function fetchHistory(PDO $pdo): array
{
    return $pdo->query(
        "SELECT id, house_name, inspection_date, inspector_name, uploaded_copy, original_upload_name
         FROM safety_inspection_checklists
         ORDER BY inspection_date DESC, id DESC"
    )->fetchAll();
}

function fetchRecord(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM safety_inspection_checklists WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function saveUploadedFile(array $file): ?array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }

    ensureUploadDir();

    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only PDF, JPG, PNG, and WEBP files are allowed.');
    }

    $ext = $allowed[$mime];
    $base = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo((string)$file['name'], PATHINFO_FILENAME));
    $name = $base . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Unable to save uploaded file.');
    }

    return [
        'path' => 'uploads/safety_checklists/' . $name,
        'original' => (string)$file['name'],
    ];
}

$pdo = db();
createTableIfMissing($pdo);

$message = '';
$error = '';
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    try {
        if ($action === 'save') {
            $id = (int)($_POST['record_id'] ?? 0);
            $houseName = trim((string)($_POST['house_name'] ?? ''));
            $inspectionDate = trim((string)($_POST['inspection_date'] ?? ''));
            $inspectorName = trim((string)($_POST['inspector_name'] ?? ''));
            $checklist = normalizeChecklist($rows, $_POST['checklist'] ?? []);
            [$sat, $unsat, $completed] = computeTotals($checklist);

            if ($houseName === '' && $inspectionDate === '' && $inspectorName === '' && $sat === 0 && $unsat === 0 && $completed === 0) {
                throw new RuntimeException('Please enter at least one value before saving.');
            }

            $existing = $id > 0 ? fetchRecord($pdo, $id) : null;
            $uploadMeta = null;
            if (!empty($_FILES['uploaded_copy']['name'] ?? '')) {
                $uploadMeta = saveUploadedFile($_FILES['uploaded_copy']);
            }

            if ($existing) {
                $stmt = $pdo->prepare(
                    "UPDATE safety_inspection_checklists
                     SET house_name = ?, inspection_date = ?, inspector_name = ?, checklist_json = ?,
                         satisfactory_total = ?, unsatisfactory_total = ?, completed_total = ?,
                         uploaded_copy = ?, original_upload_name = ?
                     WHERE id = ?"
                );
                $stmt->execute([
                    $houseName,
                    $inspectionDate !== '' ? $inspectionDate : null,
                    $inspectorName,
                    json_encode($checklist, JSON_UNESCAPED_UNICODE),
                    $sat,
                    $unsat,
                    $completed,
                    $uploadMeta['path'] ?? $existing['uploaded_copy'],
                    $uploadMeta['original'] ?? $existing['original_upload_name'],
                    $id,
                ]);
                $selectedId = $id;
                $message = 'Record updated.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO safety_inspection_checklists
                    (house_name, inspection_date, inspector_name, checklist_json, satisfactory_total, unsatisfactory_total, completed_total, uploaded_copy, original_upload_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $houseName,
                    $inspectionDate !== '' ? $inspectionDate : null,
                    $inspectorName,
                    json_encode($checklist, JSON_UNESCAPED_UNICODE),
                    $sat,
                    $unsat,
                    $completed,
                    $uploadMeta['path'] ?? null,
                    $uploadMeta['original'] ?? null,
                ]);
                $selectedId = (int)$pdo->lastInsertId();
                $message = 'Record saved.';
            }

            if (isset($_POST['autosave']) && $_POST['autosave'] === '1') {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'id' => $selectedId,
                    'message' => $message,
                ]);
                exit;
            }
        }
    } catch (Throwable $e) {
        if (isset($_POST['autosave']) && $_POST['autosave'] === '1') {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $error = $e->getMessage();
    }
}

$history = fetchHistory($pdo);
$record = $selectedId > 0 ? fetchRecord($pdo, $selectedId) : null;
$checklistData = $record ? json_decode((string)$record['checklist_json'], true) ?: getBlankChecklist($rows) : getBlankChecklist($rows);
[$satTotal, $unsatTotal, $completedTotal] = computeTotals($checklistData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Safety Inspection Checklist</title>
<style>
    :root {
        --border: #666;
        --dark: #4b4b4b;
        --light: #e8e8e8;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        background: #f2f2f2;
        font-family: Arial, Helvetica, sans-serif;
        color: #111;
    }
    .toolbar {
        max-width: 1100px;
        margin: 18px auto 0;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .toolbar select, .toolbar button, .toolbar input[type="file"] {
        padding: 8px 10px;
        font-size: 14px;
    }
    .sheet {
        width: 850px;
        margin: 14px auto 28px;
        background: #fff;
        padding: 28px 34px 24px;
        box-shadow: 0 1px 8px rgba(0,0,0,.12);
    }
    .head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 8px;
    }
    .head img {
        width: 72px;
        height: auto;
        object-fit: contain;
    }
    .title-wrap h1 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
    }
    .title-wrap h2 {
        margin: 4px 0 0;
        font-size: 17px;
        font-weight: 600;
    }
    .top-fields {
        display: grid;
        grid-template-columns: 1fr 190px;
        gap: 22px;
        margin: 10px 0 8px;
        font-size: 14px;
    }
    .line-field {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .line-field label {
        font-weight: 600;
        white-space: nowrap;
    }
    .line-field input[type="text"],
    .line-field input[type="date"] {
        border: 0;
        border-bottom: 1px solid #222;
        outline: none;
        flex: 1;
        min-width: 0;
        padding: 4px 2px;
        font-size: 14px;
        background: transparent;
    }
    .inspection-line {
        margin: 3px 0 12px;
        font-size: 14px;
    }
    .inspection-line .line-field label {
        font-style: italic;
        font-weight: 500;
    }
    table.checklist {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 12px;
    }
    table.checklist th, table.checklist td {
        border: 1px solid var(--border);
        padding: 4px 5px;
        vertical-align: middle;
    }
    table.checklist .superhead th {
        background: var(--dark);
        color: #fff;
        text-align: left;
        font-weight: 700;
        padding: 7px 6px;
    }
    table.checklist .subhead th {
        background: #f4f4f4;
        font-weight: 700;
        text-align: center;
    }
    table.checklist .subhead th:first-child {
        text-align: left;
    }
    table.checklist .section td {
        background: var(--light);
        font-weight: 700;
        height: 28px;
    }
    .area-col { width: 43%; }
    .status-col { width: 9.5%; text-align: center; }
    .when-col { width: 15%; }
    .notes-col { width: 24%; }
    .checkbox-cell {
        text-align: center;
        padding: 0;
    }
    .checkbox-cell input {
        width: 16px;
        height: 16px;
        margin: 0;
    }
    .notes-input, .when-input {
        width: 100%;
        border: 0;
        outline: none;
        font-size: 12px;
        padding: 0;
        background: transparent;
    }
    .totals {
        display: flex;
        justify-content: flex-end;
        gap: 18px;
        margin-top: 10px;
        font-size: 13px;
        font-weight: 700;
    }
    .banner {
        max-width: 1100px;
        margin: 12px auto 0;
        font-size: 14px;
    }
    .msg { color: #0b6f2d; }
    .err { color: #b10000; }
    .scan-view {
        border: 1px solid #bbb;
        padding: 14px;
        text-align: center;
    }
    .scan-view img, .scan-view iframe {
        width: 100%;
        min-height: 900px;
        border: 0;
        background: #fff;
    }
    .scan-note {
        font-size: 13px;
        margin: 0 0 12px;
    }
    .print-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .status-note {
        font-size: 12px;
        color: #666;
        margin-left: auto;
    }
    @media print {
        body { background: #fff; }
        .toolbar, .banner { display: none !important; }
        .sheet { box-shadow: none; margin: 0; width: 100%; padding: 10px; }
    }
</style>
</head>
<body>
    <div class="banner">
        <?php if ($message !== ''): ?><div class="msg"><?= h($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="err"><?= h($error) ?></div><?php endif; ?>
    </div>

    <div class="toolbar">
        <form method="get" class="print-row">
            <strong>History by Date:</strong>
            <select name="id" onchange="this.form.submit()">
                <option value="">Select saved record</option>
                <?php foreach ($history as $item): ?>
                    <option value="<?= (int)$item['id'] ?>" <?= $selectedId === (int)$item['id'] ? 'selected' : '' ?>>
                        <?= h(($item['inspection_date'] ?: 'No Date') . ' - ' . ($item['house_name'] ?: 'No House Name') . (!empty($item['uploaded_copy']) ? ' [Uploaded Copy]' : '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <button type="button" onclick="window.print()">Print</button>
        <span class="status-note" id="saveStatus">Ready</span>
    </div>

    <div class="sheet">
        <?php if ($record && !empty($record['uploaded_copy'])): ?>
            <div class="scan-view">
                <p class="scan-note"><strong>Uploaded copy displayed for this saved record.</strong> <?= h((string)($record['original_upload_name'] ?? '')) ?></p>
                <?php $ext = strtolower(pathinfo((string)$record['uploaded_copy'], PATHINFO_EXTENSION)); ?>
                <?php if ($ext === 'pdf'): ?>
                    <iframe src="<?= h((string)$record['uploaded_copy']) ?>"></iframe>
                <?php else: ?>
                    <img src="<?= h((string)$record['uploaded_copy']) ?>" alt="Uploaded checklist copy">
                <?php endif; ?>
            </div>
        <?php else: ?>
        <form id="checklistForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="record_id" id="record_id" value="<?= (int)($record['id'] ?? 0) ?>">

            <div class="head">
                <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo">
                <div class="title-wrap">
                    <h1>Oxford Houses</h1>
                    <h2>Safety Checklist</h2>
                </div>
            </div>

            <div class="top-fields">
                <div class="line-field">
                    <label for="house_name">House Name:</label>
                    <input type="text" name="house_name" id="house_name" value="<?= h($record['house_name'] ?? '') ?>">
                </div>
                <div class="line-field">
                    <label for="inspection_date">Date:</label>
                    <input type="date" name="inspection_date" id="inspection_date" value="<?= h($record['inspection_date'] ?? '') ?>">
                </div>
            </div>

            <div class="inspection-line">
                <div class="line-field">
                    <label for="inspector_name">Person Completing Inspection:</label>
                    <input type="text" name="inspector_name" id="inspector_name" value="<?= h($record['inspector_name'] ?? '') ?>">
                </div>
            </div>

            <table class="checklist">
                <colgroup>
                    <col class="area-col">
                    <col class="status-col">
                    <col class="status-col">
                    <col class="when-col">
                    <col class="notes-col">
                </colgroup>
                <tr class="superhead">
                    <th>Safety Checklist</th>
                    <th></th>
                    <th></th>
                    <th colspan="1"></th>
                    <th></th>
                </tr>
                <tr class="subhead">
                    <th>Area</th>
                    <th>Satisfactory</th>
                    <th>Unsatisfactory</th>
                    <th>Status<br>When Completed</th>
                    <th>Notes</th>
                </tr>
                <?php foreach ($rows as $index => $row): ?>
                    <?php if (isset($row['section'])): ?>
                        <tr class="section">
                            <td><?= h($row['section']) ?></td>
                            <td></td><td></td><td></td><td></td>
                        </tr>
                    <?php else: ?>
                        <?php $item = $checklistData[(string)$index] ?? ['satisfactory' => '', 'unsatisfactory' => '', 'when_completed' => '', 'notes' => '']; ?>
                        <tr>
                            <td><?= h($row['label']) ?></td>
                            <td class="checkbox-cell"><input type="checkbox" name="checklist[<?= $index ?>][satisfactory]" value="1" <?= !empty($item['satisfactory']) ? 'checked' : '' ?>></td>
                            <td class="checkbox-cell"><input type="checkbox" name="checklist[<?= $index ?>][unsatisfactory]" value="1" <?= !empty($item['unsatisfactory']) ? 'checked' : '' ?>></td>
                            <td><input class="when-input" type="text" name="checklist[<?= $index ?>][when_completed]" value="<?= h($item['when_completed'] ?? '') ?>"></td>
                            <td><input class="notes-input" type="text" name="checklist[<?= $index ?>][notes]" value="<?= h($item['notes'] ?? '') ?>"></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>

            <div class="totals">
                <span>Satisfactory: <span id="satTotal"><?= (int)$satTotal ?></span></span>
                <span>Unsatisfactory: <span id="unsatTotal"><?= (int)$unsatTotal ?></span></span>
                <span>Completed: <span id="completedTotal"><?= (int)$completedTotal ?></span></span>
            </div>

            <div class="toolbar" style="max-width:100%; margin:16px 0 0; padding:0;">
                <input type="file" name="uploaded_copy" accept=".pdf,.jpg,.jpeg,.png,.webp">
                <button type="submit">Save Record</button>
                <?php if ($selectedId > 0): ?>
                    <a href="<?= h($_SERVER['PHP_SELF']) ?>" style="padding:8px 12px; border:1px solid #999; text-decoration:none; color:#111;">New Blank Form</a>
                <?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
    </div>

<script>
(function () {
    const form = document.getElementById('checklistForm');
    if (!form) return;

    const statusEl = document.getElementById('saveStatus');

    function updateTotals() {
        const sat = form.querySelectorAll('input[type="checkbox"][name*="[satisfactory]"]:checked').length;
        const unsat = form.querySelectorAll('input[type="checkbox"][name*="[unsatisfactory]"]:checked').length;
        let completed = 0;
        form.querySelectorAll('input[name*="[when_completed]"]').forEach((el) => {
            if (el.value.trim() !== '') completed++;
        });
        document.getElementById('satTotal').textContent = sat;
        document.getElementById('unsatTotal').textContent = unsat;
        document.getElementById('completedTotal').textContent = completed;
    }

    let timer = null;
    function queueSave() {
        updateTotals();
        clearTimeout(timer);
        statusEl.textContent = 'Saving...';
        timer = setTimeout(saveNow, 700);
    }

    async function saveNow() {
        const data = new FormData(form);
        data.set('autosave', '1');
        try {
            const res = await fetch(window.location.href, { method: 'POST', body: data });
            const json = await res.json();
            if (!res.ok || !json.ok) throw new Error(json.message || 'Save failed');
            document.getElementById('record_id').value = json.id;
            statusEl.textContent = 'Saved';
            const url = new URL(window.location.href);
            url.searchParams.set('id', json.id);
            window.history.replaceState({}, '', url.toString());
        } catch (err) {
            statusEl.textContent = err.message;
        }
    }

    form.addEventListener('input', queueSave);
    form.addEventListener('change', queueSave);
    updateTotals();
})();
</script>
</body>
</html>

<?php /*
CREATE TABLE `safety_inspection_checklists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int NOT NULL DEFAULT 0,
  `unsatisfactory_total` int NOT NULL DEFAULT 0,
  `completed_total` int NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inspection_date` (`inspection_date`),
  KEY `idx_house_name` (`house_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/ ?>