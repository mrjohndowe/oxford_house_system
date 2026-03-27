<?php
declare(strict_types=1);

/**
 * Oxford House Residency Form
 * - Single file PHP app
 * - Fillable form matching uploaded residency sheet
 * - Auto-save to MySQL
 * - History dropdown based on member name
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 * - Signature block supports mouse/touch drawing
 */

require_once __DIR__ . '/../extras/master_config.php';

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
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function posted(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function asMoney(string $value): float
{
    $clean = preg_replace('/[^0-9.\-]/', '', $value);
    return is_numeric($clean) ? (float)$clean : 0.00;
}

function fmtMoney(mixed $value): string
{
    if ($value === '' || $value === null) {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table
          AND COLUMN_NAME = :column
    ");
    $stmt->execute([
        ':table' => $table,
        ':column' => $column,
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

/* =========================
   CREATE TABLE IF NOT EXISTS
========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS oxford_residency_forms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    member_name VARCHAR(255) DEFAULT '',
    letter_date VARCHAR(20) DEFAULT '',
    house_name VARCHAR(255) DEFAULT '',
    accepted_date VARCHAR(20) DEFAULT '',
    address_line VARCHAR(255) DEFAULT '',
    city_state_zip VARCHAR(255) DEFAULT '',
    move_in_fee DECIMAL(10,2) DEFAULT 0.00,
    weekly_rent DECIMAL(10,2) DEFAULT 0.00,
    president_contact VARCHAR(255) DEFAULT '',
    president_name VARCHAR(255) DEFAULT '',
    president_signature LONGTEXT NULL,
    president_signature_date VARCHAR(20) DEFAULT '',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member_name (member_name),
    INDEX idx_house_name (house_name),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* =========================
   SAFE COLUMN UPDATES FOR EXISTING TABLES
========================= */
$tableName = 'oxford_residency_forms';

if (!columnExists($pdo, $tableName, 'president_signature_date')) {
    $pdo->exec("ALTER TABLE oxford_residency_forms ADD COLUMN president_signature_date VARCHAR(20) DEFAULT '' AFTER president_signature");
}

/* =========================
   AJAX AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'autosave') {
    header('Content-Type: application/json; charset=utf-8');

    $id                       = (int)($_POST['id'] ?? 0);
    $member_name              = posted('member_name');
    $letter_date              = posted('letter_date');
    $house_name               = posted('house_name');
    $accepted_date            = posted('accepted_date');
    $address_line             = posted('address_line');
    $city_state_zip           = posted('city_state_zip');
    $move_in_fee              = asMoney(posted('move_in_fee'));
    $weekly_rent              = asMoney(posted('weekly_rent'));
    $president_contact        = posted('president_contact');
    $president_name           = posted('president_name');
    $president_signature      = posted('president_signature');
    $president_signature_date = posted('president_signature_date');

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE oxford_residency_forms SET
                    member_name = :member_name,
                    letter_date = :letter_date,
                    house_name = :house_name,
                    accepted_date = :accepted_date,
                    address_line = :address_line,
                    city_state_zip = :city_state_zip,
                    move_in_fee = :move_in_fee,
                    weekly_rent = :weekly_rent,
                    president_contact = :president_contact,
                    president_name = :president_name,
                    president_signature = :president_signature,
                    president_signature_date = :president_signature_date
                WHERE id = :id
            ");
            $stmt->execute([
                ':member_name'              => $member_name,
                ':letter_date'              => $letter_date,
                ':house_name'               => $house_name,
                ':accepted_date'            => $accepted_date,
                ':address_line'             => $address_line,
                ':city_state_zip'           => $city_state_zip,
                ':move_in_fee'              => $move_in_fee,
                ':weekly_rent'              => $weekly_rent,
                ':president_contact'        => $president_contact,
                ':president_name'           => $president_name,
                ':president_signature'      => $president_signature,
                ':president_signature_date' => $president_signature_date,
                ':id'                       => $id,
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO oxford_residency_forms (
                    member_name,
                    letter_date,
                    house_name,
                    accepted_date,
                    address_line,
                    city_state_zip,
                    move_in_fee,
                    weekly_rent,
                    president_contact,
                    president_name,
                    president_signature,
                    president_signature_date
                ) VALUES (
                    :member_name,
                    :letter_date,
                    :house_name,
                    :accepted_date,
                    :address_line,
                    :city_state_zip,
                    :move_in_fee,
                    :weekly_rent,
                    :president_contact,
                    :president_name,
                    :president_signature,
                    :president_signature_date
                )
            ");
            $stmt->execute([
                ':member_name'              => $member_name,
                ':letter_date'              => $letter_date,
                ':house_name'               => $house_name,
                ':accepted_date'            => $accepted_date,
                ':address_line'             => $address_line,
                ':city_state_zip'           => $city_state_zip,
                ':move_in_fee'              => $move_in_fee,
                ':weekly_rent'              => $weekly_rent,
                ':president_contact'        => $president_contact,
                ':president_name'           => $president_name,
                ':president_signature'      => $president_signature,
                ':president_signature_date' => $president_signature_date,
            ]);
            $id = (int)$pdo->lastInsertId();
        }

        $savedStmt = $pdo->prepare("
            SELECT DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') AS saved_at
            FROM oxford_residency_forms
            WHERE id = :id
            LIMIT 1
        ");
        $savedStmt->execute([':id' => $id]);
        $savedAt = $savedStmt->fetchColumn();

        echo json_encode([
            'ok' => true,
            'id' => $id,
            'saved_at' => $savedAt,
        ]);
    } catch (Throwable $e) {
        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage(),
        ]);
    }
    exit;
}

/* =========================
   LOAD RECORD
========================= */
$current = [
    'id' => 0,
    'member_name' => '',
    'letter_date' => '',
    'house_name' => '',
    'accepted_date' => '',
    'address_line' => '',
    'city_state_zip' => '',
    'move_in_fee' => '',
    'weekly_rent' => '',
    'president_contact' => '',
    'president_name' => '',
    'president_signature' => '',
    'president_signature_date' => '',
];

if (isset($_GET['load_id']) && ctype_digit((string)$_GET['load_id'])) {
    $loadId = (int)$_GET['load_id'];
    $stmt = $pdo->prepare("SELECT * FROM oxford_residency_forms WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loadId]);
    $row = $stmt->fetch();
    if ($row) {
        $current = array_merge($current, $row);
    }
}

/* =========================
   HISTORY
========================= */
$historyRows = $pdo->query("
    SELECT id, member_name, house_name, accepted_date, updated_at
    FROM oxford_residency_forms
    ORDER BY member_name ASC, updated_at DESC
")->fetchAll();

$signatureDataUrl = (string)($current['president_signature'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Residency Form</title>
    <style>
        @page {
            size: letter;
            margin: 0.5in;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f5;
            font-family: "Times New Roman", Times, serif;
            color: #000;
        }

        .appbar {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #d7dbe2;
            padding: 12px 18px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .appbar-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-group label {
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: 700;
        }

        select, button {
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #bfc6d1;
            background: #fff;
        }

        button {
            cursor: pointer;
            font-weight: 700;
        }

        .status {
            margin-left: auto;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #2f5f2f;
            font-weight: 700;
        }

        .page-wrap {
            padding: 22px;
        }

        .sheet {
            width: 8.5in;
            min-height: 11in;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 8px 30px rgba(0,0,0,0.10);
            padding: 0.55in 0.65in 0.6in 0.65in;
        }

        .header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex: 0 0 72px;
        }

        .title {
            flex: 1;
            text-align: left;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.15;
            padding-top: 6px;
        }

        .date-line {
            text-align: right;
            margin-bottom: 18px;
            font-size: 16px;
        }

        .line-input {
            border: none;
            border-bottom: 1px solid #000;
            outline: none;
            background: transparent;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            line-height: 1.4;
            padding: 0 2px 1px 2px;
            border-radius: 0;
        }

        .line-input:focus {
            background: #fffde8;
        }

        .money-input {
            width: 95px;
            text-align: right;
        }

        .short-date {
            width: 86px;
            text-align: center;
        }

        .member-line { width: 300px; }
        .house-line { width: 165px; }
        .address-line { width: 100%; }
        .city-line { width: 190px; }
        .contact-line { width: 330px; }
        .president-line { width: 235px; }
        .signature-line { width: 260px; }

        .paragraph {
            font-size: 16px;
            line-height: 1.9;
            text-align: left;
            margin: 0 0 12px 0;
        }

        .indent {
            text-indent: 28px;
        }

        .bottom-space {
            margin-top: 18px;
        }

        .signature-block {
            margin-top: 28px;
            border-top: 1px solid #000;
            padding-top: 16px;
        }

        .signature-block-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 14px 0;
            text-align: left;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 130px;
            gap: 18px 24px;
            align-items: end;
            margin-bottom: 12px;
        }

        .signature-cell label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .signature-cell .line-input {
            width: 100%;
        }

        .signature-pad-wrap {
            border: 1px solid #000;
            padding: 8px 8px 6px;
            background: #fff;
        }

        .signature-pad {
            display: block;
            width: 100%;
            height: 130px;
            border-bottom: 1px solid #000;
            cursor: crosshair;
            touch-action: none;
            background: #fff;
        }

        .signature-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .signature-tools-left {
            color: #444;
        }

        .signature-tools button {
            padding: 6px 10px;
            font-size: 12px;
        }

        .signature-note {
            margin-top: 8px;
            font-size: 13px;
            font-style: italic;
        }

        .totals-box {
            margin-top: 24px;
            border: 1px solid #000;
            padding: 10px 12px;
            width: 340px;
            margin-left: auto;
            font-size: 15px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 2px 0;
        }

        .totals-row strong {
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff;
            }

            .appbar {
                display: none !important;
            }

            .page-wrap {
                padding: 0;
            }

            .sheet {
                box-shadow: none;
                margin: 0;
                width: auto;
                min-height: auto;
                padding: 0;
            }

            .line-input {
                background: transparent !important;
            }

            .signature-tools {
                display: none !important;
            }

            .signature-pad-wrap {
                border: none;
                padding: 0;
            }

            .signature-pad {
                border-bottom: 1px solid #000;
            }
        }
    </style>
</head>
<body>
    <div class="appbar">
        <div class="appbar-inner">
            <div class="control-group">
                <label for="history_id">History by Member Name:</label>
                <select id="history_id">
                    <option value="">Select saved record...</option>
                    <?php foreach ($historyRows as $history): ?>
                        <?php
                            $label = trim(($history['member_name'] ?: 'Unnamed Member') . ' — ' . ($history['house_name'] ?: 'No House'));
                            $saved = date('m/d/Y h:i A', strtotime((string)$history['updated_at']));
                        ?>
                        <option value="<?= (int)$history['id'] ?>" <?= ((int)$current['id'] === (int)$history['id']) ? 'selected' : '' ?>>
                            <?= h($label) ?> — Saved <?= h($saved) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" onclick="window.print()">Print</button>
            <div class="status" id="saveStatus">Ready</div>
        </div>
    </div>

    <div class="page-wrap">
        <div class="sheet">
            <form id="residencyForm" autocomplete="off">
                <input type="hidden" name="id" id="record_id" value="<?= (int)$current['id'] ?>">
                <input type="hidden" name="president_signature" id="president_signature_data" value="<?= h($signatureDataUrl) ?>">

                <div class="header">
                    <img class="logo" src="../images/oxford_house_logo.png" alt="Oxford House Logo">
                    <div class="title">Oxford Houses of Colorado</div>
                </div>

                <div class="date-line">
                    <input type="text" class="line-input short-date autosave" name="letter_date" value="<?= h($current['letter_date']) ?>" placeholder="__/__/__">
                </div>

                <p class="paragraph">To whom it may concern:</p>

                <p class="paragraph">
                    <input type="text" class="line-input member-line autosave" name="member_name" value="<?= h($current['member_name']) ?>">
                    was accepted into Oxford House
                    <input type="text" class="line-input house-line autosave" name="house_name" value="<?= h($current['house_name']) ?>">
                    on
                    <input type="text" class="line-input short-date autosave" name="accepted_date" value="<?= h($current['accepted_date']) ?>" placeholder="__/__/__">.
                    As a resident at Oxford House
                    <input type="text" class="line-input house-line autosave" name="house_name_dup" value="<?= h($current['house_name']) ?>" data-sync="house_name">
                    (Address
                    <input type="text" class="line-input address-line autosave" name="address_line" value="<?= h($current['address_line']) ?>">),
                    <input type="text" class="line-input city-line autosave" name="city_state_zip" value="<?= h($current['city_state_zip']) ?>">
                    is required to pay a $<input type="text" class="line-input money-input autosave calc" name="move_in_fee" value="<?= h(fmtMoney($current['move_in_fee'])) ?>"> non-refundable move in fee and $
                    <input type="text" class="line-input money-input autosave calc" name="weekly_rent" value="<?= h(fmtMoney($current['weekly_rent'])) ?>">
                    per week, for rent.
                    He/She is required to follow all of the rules and regulations of the house, including paying his/her rent on time, doing his/her weekly chore, and holding a house officer position.
                    He/She is also required to attend recovery meetings 3-5 times per week, as well as, seek and maintain employment.
                    He/She will also been drug screened on a regular basis.
                </p>

                <p class="paragraph indent">
                    Oxford Houses are self-run, self-supporting addiction recovery homes for individuals who have stopped using alcohol and drugs, hoping for a second chance in life. The first Oxford House opened in 1975 and has since grown to over 3000 houses in the US. All of our houses are gender specific, which includes men’s houses, women’s house’s and houses for women with children. Safe, structured, and supportive environments help to create an atmosphere of growth, responsibility, and self-efficacy.
                </p>

                <p class="paragraph bottom-space">
                    If you have any questions, please contact The House Secretary at:
                    <input type="text" class="line-input contact-line autosave" name="president_contact" value="<?= h($current['president_contact']) ?>">
                </p>

                <p class="paragraph">Thank You,</p>

                <p class="paragraph">
                    House Secretary Name
                    <input type="text" class="line-input president-line autosave" name="president_name" value="<?= h($current['president_name']) ?>">
                </p>

                <div class="signature-block">
                    <div class="signature-block-title">Signature Block</div>

                    <div class="signature-grid">
                        <div class="signature-cell">
                            <label for="signatureCanvas">House Secretary Signature</label>
                            <div class="signature-pad-wrap">
                                <canvas id="signatureCanvas" class="signature-pad"></canvas>
                                <div class="signature-tools">
                                    <div class="signature-tools-left">Use mouse or touch to sign.</div>
                                    <button type="button" id="clearSignatureBtn">Clear Signature</button>
                                </div>
                            </div>
                        </div>
                        <div class="signature-cell">
                            <label for="president_signature_date">Date</label>
                            <input
                                id="president_signature_date"
                                type="text"
                                class="line-input short-date autosave"
                                name="president_signature_date"
                                value="<?= h($current['president_signature_date']) ?>"
                                placeholder="__/__/__"
                            >
                        </div>
                    </div>

                    <div class="signature-note">
                        Signature acknowledges the residency information shown on this form.
                    </div>
                </div>

                <div class="totals-box">
                    <div class="totals-row">
                        <span>Move-In Fee:</span>
                        <strong>$<span id="displayMoveIn">0.00</span></strong>
                    </div>
                    <div class="totals-row">
                        <span>Weekly Rent:</span>
                        <strong>$<span id="displayWeekly">0.00</span></strong>
                    </div>
                    <div class="totals-row">
                        <span>Total Due at Move-In:</span>
                        <strong>$<span id="displayTotal">0.00</span></strong>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('residencyForm');
        const saveStatus = document.getElementById('saveStatus');
        const recordId = document.getElementById('record_id');
        const historySelect = document.getElementById('history_id');

        const displayMoveIn = document.getElementById('displayMoveIn');
        const displayWeekly = document.getElementById('displayWeekly');
        const displayTotal = document.getElementById('displayTotal');

        const signatureCanvas = document.getElementById('signatureCanvas');
        const signatureDataField = document.getElementById('president_signature_data');
        const clearSignatureBtn = document.getElementById('clearSignatureBtn');
        const signatureCtx = signatureCanvas.getContext('2d');

        let isDrawing = false;
        let hasDrawn = false;
        let saveTimer = null;

        function moneyValue(v) {
            const n = parseFloat(String(v).replace(/[^0-9.\-]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function formatMoney(v) {
            return Number(v).toFixed(2);
        }

        function updateTotals() {
            const moveIn = moneyValue(form.move_in_fee.value);
            const weekly = moneyValue(form.weekly_rent ? form.weekly_rent.value : 0);

            displayMoveIn.textContent = formatMoney(moveIn);
            displayWeekly.textContent = formatMoney(weekly);
            displayTotal.textContent = formatMoney(moveIn + weekly);
        }

        function syncDuplicateFields(changedEl) {
            const name = changedEl.name;

            document.querySelectorAll('[data-sync="' + name + '"]').forEach(el => {
                if (el !== changedEl) {
                    el.value = changedEl.value;
                }
            });

            if (changedEl.dataset.sync) {
                const target = form.querySelector('[name="' + changedEl.dataset.sync + '"]');
                if (target && target !== changedEl) {
                    target.value = changedEl.value;
                }
            }
        }

        function queueAutosave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosave, 500);
        }

        function autosave() {
            saveStatus.textContent = 'Saving...';

            const fd = new FormData();
            fd.append('ajax', 'autosave');
            fd.append('id', recordId.value || '0');
            fd.append('member_name', form.member_name.value || '');
            fd.append('letter_date', form.letter_date.value || '');
            fd.append('house_name', form.house_name.value || form.house_name_dup.value || '');
            fd.append('accepted_date', form.accepted_date.value || '');
            fd.append('address_line', form.address_line.value || '');
            fd.append('city_state_zip', form.city_state_zip.value || '');
            fd.append('move_in_fee', form.move_in_fee.value || '0');
            fd.append('weekly_rent', form.weekly_rent ? form.weekly_rent.value : '0');
            fd.append('president_contact', form.president_contact.value || '');
            fd.append('president_name', form.president_name.value || '');
            fd.append('president_signature', signatureDataField.value || '');
            fd.append('president_signature_date', form.president_signature_date.value || '');

            fetch(window.location.href, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    if (data.id) {
                        recordId.value = data.id;
                    }
                    saveStatus.textContent = 'Saved: ' + (data.saved_at || 'just now');
                } else {
                    saveStatus.textContent = 'Save failed';
                    console.error(data.error || 'Unknown save error');
                }
            })
            .catch(err => {
                saveStatus.textContent = 'Save failed';
                console.error(err);
            });
        }

        function resizeSignatureCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = signatureCanvas.getBoundingClientRect();
            const previousData = signatureDataField.value || '';

            signatureCanvas.width = Math.floor(rect.width * ratio);
            signatureCanvas.height = Math.floor(rect.height * ratio);
            signatureCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
            signatureCtx.lineCap = 'round';
            signatureCtx.lineJoin = 'round';
            signatureCtx.lineWidth = 2;
            signatureCtx.strokeStyle = '#000';

            signatureCtx.clearRect(0, 0, rect.width, rect.height);

            if (previousData) {
                loadSignatureFromData(previousData, false);
            }
        }

        function getPoint(event) {
            const rect = signatureCanvas.getBoundingClientRect();
            let clientX = 0;
            let clientY = 0;

            if (event.touches && event.touches.length > 0) {
                clientX = event.touches[0].clientX;
                clientY = event.touches[0].clientY;
            } else if (event.changedTouches && event.changedTouches.length > 0) {
                clientX = event.changedTouches[0].clientX;
                clientY = event.changedTouches[0].clientY;
            } else {
                clientX = event.clientX;
                clientY = event.clientY;
            }

            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(event) {
            event.preventDefault();
            const point = getPoint(event);
            isDrawing = true;
            hasDrawn = true;
            signatureCtx.beginPath();
            signatureCtx.moveTo(point.x, point.y);
        }

        function draw(event) {
            if (!isDrawing) return;
            event.preventDefault();
            const point = getPoint(event);
            signatureCtx.lineTo(point.x, point.y);
            signatureCtx.stroke();
        }

        function endDrawing(event) {
            if (!isDrawing) return;
            event.preventDefault();
            isDrawing = false;
            signatureCtx.closePath();
            saveSignatureToField();
            queueAutosave();
        }

        function clearSignature() {
            const rect = signatureCanvas.getBoundingClientRect();
            signatureCtx.clearRect(0, 0, rect.width, rect.height);
            signatureDataField.value = '';
            hasDrawn = false;
            queueAutosave();
        }

        function saveSignatureToField() {
            if (!hasDrawn) {
                signatureDataField.value = '';
                return;
            }
            signatureDataField.value = signatureCanvas.toDataURL('image/png');
        }

        function loadSignatureFromData(dataUrl, setDrawnState = true) {
            if (!dataUrl) return;

            const img = new Image();
            img.onload = function () {
                const rect = signatureCanvas.getBoundingClientRect();
                signatureCtx.clearRect(0, 0, rect.width, rect.height);
                signatureCtx.drawImage(img, 0, 0, rect.width, rect.height);
                if (setDrawnState) {
                    hasDrawn = true;
                }
            };
            img.src = dataUrl;
        }

        document.querySelectorAll('.autosave').forEach(el => {
            el.addEventListener('input', function () {
                syncDuplicateFields(this);
                updateTotals();
                queueAutosave();
            });

            el.addEventListener('change', function () {
                syncDuplicateFields(this);
                updateTotals();
                queueAutosave();
            });
        });

        historySelect.addEventListener('change', function () {
            if (this.value) {
                window.location.href = '?load_id=' + encodeURIComponent(this.value);
            }
        });

        signatureCanvas.addEventListener('mousedown', startDrawing);
        signatureCanvas.addEventListener('mousemove', draw);
        signatureCanvas.addEventListener('mouseup', endDrawing);
        signatureCanvas.addEventListener('mouseleave', endDrawing);

        signatureCanvas.addEventListener('touchstart', startDrawing, { passive: false });
        signatureCanvas.addEventListener('touchmove', draw, { passive: false });
        signatureCanvas.addEventListener('touchend', endDrawing, { passive: false });
        signatureCanvas.addEventListener('touchcancel', endDrawing, { passive: false });

        clearSignatureBtn.addEventListener('click', clearSignature);

        window.addEventListener('resize', resizeSignatureCanvas);

        resizeSignatureCanvas();

        if (signatureDataField.value) {
            loadSignatureFromData(signatureDataField.value);
        }

        updateTotals();
    </script>
</body>
</html>