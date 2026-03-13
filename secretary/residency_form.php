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
    president_signature VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member_name (member_name),
    INDEX idx_house_name (house_name),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

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

/* =========================
   AJAX AUTOSAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'autosave') {
    header('Content-Type: application/json; charset=utf-8');

    $id                 = (int)($_POST['id'] ?? 0);
    $member_name        = posted('member_name');
    $letter_date        = posted('letter_date');
    $house_name         = posted('house_name');
    $accepted_date      = posted('accepted_date');
    $address_line       = posted('address_line');
    $city_state_zip     = posted('city_state_zip');
    $move_in_fee        = asMoney(posted('move_in_fee'));
    $weekly_rent        = asMoney(posted('weekly_rent'));
    $president_contact  = posted('president_contact');
    $president_name     = posted('president_name');
    $president_signature= posted('president_signature');

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
                    president_signature = :president_signature
                WHERE id = :id
            ");
            $stmt->execute([
                ':member_name'         => $member_name,
                ':letter_date'         => $letter_date,
                ':house_name'          => $house_name,
                ':accepted_date'       => $accepted_date,
                ':address_line'        => $address_line,
                ':city_state_zip'      => $city_state_zip,
                ':move_in_fee'         => $move_in_fee,
                ':weekly_rent'         => $weekly_rent,
                ':president_contact'   => $president_contact,
                ':president_name'      => $president_name,
                ':president_signature' => $president_signature,
                ':id'                  => $id,
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO oxford_residency_forms (
                    member_name, letter_date, house_name, accepted_date, address_line,
                    city_state_zip, move_in_fee, weekly_rent, president_contact,
                    president_name, president_signature
                ) VALUES (
                    :member_name, :letter_date, :house_name, :accepted_date, :address_line,
                    :city_state_zip, :move_in_fee, :weekly_rent, :president_contact,
                    :president_name, :president_signature
                )
            ");
            $stmt->execute([
                ':member_name'         => $member_name,
                ':letter_date'         => $letter_date,
                ':house_name'          => $house_name,
                ':accepted_date'       => $accepted_date,
                ':address_line'        => $address_line,
                ':city_state_zip'      => $city_state_zip,
                ':move_in_fee'         => $move_in_fee,
                ':weekly_rent'         => $weekly_rent,
                ':president_contact'   => $president_contact,
                ':president_name'      => $president_name,
                ':president_signature' => $president_signature,
            ]);
            $id = (int)$pdo->lastInsertId();
        }

        $savedAt = $pdo->query("SELECT DATE_FORMAT(updated_at, '%m/%d/%Y %h:%i %p') AS saved_at FROM oxford_residency_forms WHERE id = {$id}")->fetchColumn();

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
];

if (isset($_GET['load_id']) && ctype_digit((string)$_GET['load_id'])) {
    $loadId = (int)$_GET['load_id'];
    $stmt = $pdo->prepare("SELECT * FROM oxford_residency_forms WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loadId]);
    $row = $stmt->fetch();
    if ($row) {
        $current = $row;
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

function fmtMoney(mixed $value): string
{
    if ($value === '' || $value === null) {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}
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

        .totals-box {
            margin-top: 24px;
            border: 1px solid #000;
            padding: 10px 12px;
            width: 340px;
            margin-left: auto;
            font-size: 15px;
        }

        .totals-box h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
            text-align: center;
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

        .note {
            margin-top: 8px;
            font-size: 12px;
            font-style: italic;
            font-family: Arial, sans-serif;
        }

        @media print {
            body {
                background: #fff;
            }

            .appbar,
            .note {
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
                    is required to pay a non-refundable move in fee and $
                    <input type="text" class="line-input money-input autosave calc" name="move_in_fee" value="<?= h(fmtMoney($current['move_in_fee'])) ?>">
                    per week, for rent.
                    He/She is required to follow all of the rules and regulations of the house, including paying his/her rent on time, doing his/her weekly chore, and holding a house officer position.
                    He/She is also required to attend recovery meetings 3-5 times per week, as well as, seek and maintain employment.
                    He/She will also been drug screened on a regular basis.
                </p>

                <p class="paragraph indent">
                    Oxford Houses are self-run, self-supporting addiction recovery homes for individuals who have stopped using alcohol and drugs, hoping for a second chance in life. The first Oxford House opened in 1975 and has since grown to over 3000 houses in the US. All of our houses are gender specific, which includes men’s houses, women’s house’s and houses for women with children. Safe, structured, and supportive environments help to create an atmosphere of growth, responsibility, and self-efficacy.
                </p>

                <p class="paragraph bottom-space">
                    If you have any questions, please contact The House President at:
                    <input type="text" class="line-input contact-line autosave" name="president_contact" value="<?= h($current['president_contact']) ?>">
                </p>

                <p class="paragraph">Thank You,</p>

                <p class="paragraph">
                    House President Name
                    <input type="text" class="line-input president-line autosave" name="president_name" value="<?= h($current['president_name']) ?>">
                </p>

                <p class="paragraph">
                    House President Signature
                    <input type="text" class="line-input signature-line autosave" name="president_signature" value="<?= h($current['president_signature']) ?>">
                </p>

                <div class="totals-box">
                    <!-- <h4>Auto-Calculated Totals</h4> -->
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

                <!-- <div class="note">
                    This layout was built from the uploaded residency form and keeps the same wording and structure as closely as possible while making it fillable and database-backed.
                </div> -->
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

        let saveTimer = null;

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
            fd.append('president_signature', form.president_signature.value || '');

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

        function queueAutosave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(autosave, 500);
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

        updateTotals();
    </script>
</body>
</html>