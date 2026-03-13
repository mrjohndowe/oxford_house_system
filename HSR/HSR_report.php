<?php
/**
 * Housing Service Representative Report
 * Single-file PHP app
 * - Closely matches uploaded HSR report layout
 * - Auto-save to MySQL
 * - History dropdown by week date
 * - Reload/edit prior records
 * - Print button
 * - Oxford House logo at top
 */

declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

const LOGO_PATH = '../images/oxford_house_logo.png';

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function posted(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function checkboxPosted(string $key): string
{
    return isset($_POST[$key]) && $_POST[$key] === '1' ? '1' : '0';
}

function formatWeekLabel(array $row): string
{
    $from = trim((string)($row['week_from'] ?? ''));
    $to   = trim((string)($row['week_to'] ?? ''));
    $date = trim((string)($row['report_date'] ?? ''));
    $id   = (int)($row['id'] ?? 0);

    $week = ($from || $to) ? ($from . ' to ' . $to) : 'No week entered';
    $dateText = $date !== '' ? ' | Date: ' . $date : '';

    return '#' . $id . ' | Week: ' . $week . $dateText;
}

function defaultFormData(): array
{
    return [
        'id' => '',
        'week_from' => '',
        'week_to' => '',
        'house_visit' => '',
        'audit_done_yes' => '0',
        'audit_done_no' => '0',
        'summary_done_yes' => '0',
        'summary_done_no' => '0',
        'next_chapter_meeting' => '',
        'address' => '',
        'next_state_meeting' => '',
        'number_of_applications' => '',
        'number_contacted_plan' => '',
        'new_members' => '',
        'interviews_setup' => '',
        'chapter_news' => '',
        'chapter_meeting_recap' => '',
        'upcoming_unity' => '',
        'upcoming_presentations' => '',
        'hsr_name' => '',
        'report_date' => '',
    ];
}

/* =========================
   DB CONNECTION
========================= */
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

/* =========================
   TABLE SETUP
========================= */
$pdo->exec("
    CREATE TABLE IF NOT EXISTS housing_service_representative_reports (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        week_from VARCHAR(100) NOT NULL DEFAULT '',
        week_to VARCHAR(100) NOT NULL DEFAULT '',
        house_visit TEXT NULL,
        audit_done_yes TINYINT(1) NOT NULL DEFAULT 0,
        audit_done_no TINYINT(1) NOT NULL DEFAULT 0,
        summary_done_yes TINYINT(1) NOT NULL DEFAULT 0,
        summary_done_no TINYINT(1) NOT NULL DEFAULT 0,
        next_chapter_meeting TEXT NULL,
        address TEXT NULL,
        next_state_meeting TEXT NULL,
        number_of_applications TEXT NULL,
        number_contacted_plan LONGTEXT NULL,
        new_members LONGTEXT NULL,
        interviews_setup LONGTEXT NULL,
        chapter_news LONGTEXT NULL,
        chapter_meeting_recap LONGTEXT NULL,
        upcoming_unity LONGTEXT NULL,
        upcoming_presentations LONGTEXT NULL,
        hsr_name VARCHAR(255) NOT NULL DEFAULT '',
        report_date VARCHAR(100) NOT NULL DEFAULT '',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

/* =========================
   LOAD HISTORY LIST
========================= */
$historyStmt = $pdo->query("
    SELECT id, week_from, week_to, report_date
    FROM housing_service_representative_reports
    ORDER BY id DESC
");
$historyRows = $historyStmt->fetchAll();

/* =========================
   LOAD SINGLE RECORD
========================= */
$formData = defaultFormData();
$statusMessage = '';
$statusType = 'success';

if (isset($_GET['load_id']) && ctype_digit((string)$_GET['load_id'])) {
    $loadId = (int)$_GET['load_id'];
    $stmt = $pdo->prepare("SELECT * FROM housing_service_representative_reports WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $loadId]);
    $loaded = $stmt->fetch();
    if ($loaded) {
        $formData = array_merge($formData, $loaded);
        $statusMessage = 'Loaded saved report #' . $loadId . '.';
    }
}

/* =========================
   AUTO SAVE / MANUAL SAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAutosave = isset($_POST['autosave']) && $_POST['autosave'] === '1';

    $incoming = [
        'id' => trim((string)posted('id')),
        'week_from' => trim((string)posted('week_from')),
        'week_to' => trim((string)posted('week_to')),
        'house_visit' => trim((string)posted('house_visit')),
        'audit_done_yes' => checkboxPosted('audit_done_yes'),
        'audit_done_no' => checkboxPosted('audit_done_no'),
        'summary_done_yes' => checkboxPosted('summary_done_yes'),
        'summary_done_no' => checkboxPosted('summary_done_no'),
        'next_chapter_meeting' => trim((string)posted('next_chapter_meeting')),
        'address' => trim((string)posted('address')),
        'next_state_meeting' => trim((string)posted('next_state_meeting')),
        'number_of_applications' => trim((string)posted('number_of_applications')),
        'number_contacted_plan' => trim((string)posted('number_contacted_plan')),
        'new_members' => trim((string)posted('new_members')),
        'interviews_setup' => trim((string)posted('interviews_setup')),
        'chapter_news' => trim((string)posted('chapter_news')),
        'chapter_meeting_recap' => trim((string)posted('chapter_meeting_recap')),
        'upcoming_unity' => trim((string)posted('upcoming_unity')),
        'upcoming_presentations' => trim((string)posted('upcoming_presentations')),
        'hsr_name' => trim((string)posted('hsr_name')),
        'report_date' => trim((string)posted('report_date')),
    ];

    $formData = array_merge($formData, $incoming);

    $hasMeaningfulContent = false;
    foreach ($incoming as $k => $v) {
        if ($k === 'id') {
            continue;
        }
        if ((string)$v !== '' && (string)$v !== '0') {
            $hasMeaningfulContent = true;
            break;
        }
    }

    if (!$hasMeaningfulContent) {
        if ($isAutosave) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => true,
                'id' => '',
                'message' => 'Nothing to save yet.'
            ]);
            exit;
        }
    } else {
        if ($incoming['id'] !== '' && ctype_digit($incoming['id'])) {
            $update = $pdo->prepare("
                UPDATE housing_service_representative_reports SET
                    week_from = :week_from,
                    week_to = :week_to,
                    house_visit = :house_visit,
                    audit_done_yes = :audit_done_yes,
                    audit_done_no = :audit_done_no,
                    summary_done_yes = :summary_done_yes,
                    summary_done_no = :summary_done_no,
                    next_chapter_meeting = :next_chapter_meeting,
                    address = :address,
                    next_state_meeting = :next_state_meeting,
                    number_of_applications = :number_of_applications,
                    number_contacted_plan = :number_contacted_plan,
                    new_members = :new_members,
                    interviews_setup = :interviews_setup,
                    chapter_news = :chapter_news,
                    chapter_meeting_recap = :chapter_meeting_recap,
                    upcoming_unity = :upcoming_unity,
                    upcoming_presentations = :upcoming_presentations,
                    hsr_name = :hsr_name,
                    report_date = :report_date
                WHERE id = :id
            ");

            $update->execute([
                ':week_from' => $incoming['week_from'],
                ':week_to' => $incoming['week_to'],
                ':house_visit' => $incoming['house_visit'],
                ':audit_done_yes' => (int)$incoming['audit_done_yes'],
                ':audit_done_no' => (int)$incoming['audit_done_no'],
                ':summary_done_yes' => (int)$incoming['summary_done_yes'],
                ':summary_done_no' => (int)$incoming['summary_done_no'],
                ':next_chapter_meeting' => $incoming['next_chapter_meeting'],
                ':address' => $incoming['address'],
                ':next_state_meeting' => $incoming['next_state_meeting'],
                ':number_of_applications' => $incoming['number_of_applications'],
                ':number_contacted_plan' => $incoming['number_contacted_plan'],
                ':new_members' => $incoming['new_members'],
                ':interviews_setup' => $incoming['interviews_setup'],
                ':chapter_news' => $incoming['chapter_news'],
                ':chapter_meeting_recap' => $incoming['chapter_meeting_recap'],
                ':upcoming_unity' => $incoming['upcoming_unity'],
                ':upcoming_presentations' => $incoming['upcoming_presentations'],
                ':hsr_name' => $incoming['hsr_name'],
                ':report_date' => $incoming['report_date'],
                ':id' => (int)$incoming['id'],
            ]);

            $savedId = (int)$incoming['id'];
            $statusMessage = 'Report updated successfully.';
        } else {
            $insert = $pdo->prepare("
                INSERT INTO housing_service_representative_reports (
                    week_from, week_to, house_visit,
                    audit_done_yes, audit_done_no,
                    summary_done_yes, summary_done_no,
                    next_chapter_meeting, address, next_state_meeting,
                    number_of_applications, number_contacted_plan,
                    new_members, interviews_setup, chapter_news,
                    chapter_meeting_recap, upcoming_unity, upcoming_presentations,
                    hsr_name, report_date
                ) VALUES (
                    :week_from, :week_to, :house_visit,
                    :audit_done_yes, :audit_done_no,
                    :summary_done_yes, :summary_done_no,
                    :next_chapter_meeting, :address, :next_state_meeting,
                    :number_of_applications, :number_contacted_plan,
                    :new_members, :interviews_setup, :chapter_news,
                    :chapter_meeting_recap, :upcoming_unity, :upcoming_presentations,
                    :hsr_name, :report_date
                )
            ");

            $insert->execute([
                ':week_from' => $incoming['week_from'],
                ':week_to' => $incoming['week_to'],
                ':house_visit' => $incoming['house_visit'],
                ':audit_done_yes' => (int)$incoming['audit_done_yes'],
                ':audit_done_no' => (int)$incoming['audit_done_no'],
                ':summary_done_yes' => (int)$incoming['summary_done_yes'],
                ':summary_done_no' => (int)$incoming['summary_done_no'],
                ':next_chapter_meeting' => $incoming['next_chapter_meeting'],
                ':address' => $incoming['address'],
                ':next_state_meeting' => $incoming['next_state_meeting'],
                ':number_of_applications' => $incoming['number_of_applications'],
                ':number_contacted_plan' => $incoming['number_contacted_plan'],
                ':new_members' => $incoming['new_members'],
                ':interviews_setup' => $incoming['interviews_setup'],
                ':chapter_news' => $incoming['chapter_news'],
                ':chapter_meeting_recap' => $incoming['chapter_meeting_recap'],
                ':upcoming_unity' => $incoming['upcoming_unity'],
                ':upcoming_presentations' => $incoming['upcoming_presentations'],
                ':hsr_name' => $incoming['hsr_name'],
                ':report_date' => $incoming['report_date'],
            ]);

            $savedId = (int)$pdo->lastInsertId();
            $formData['id'] = (string)$savedId;
            $statusMessage = 'Report saved successfully.';
        }

        if ($isAutosave) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => true,
                'id' => $savedId,
                'message' => 'Auto-saved at ' . date('g:i:s A')
            ]);
            exit;
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?load_id=' . $savedId . '&saved=1');
        exit;
    }
}

/* =========================
   RELOAD HISTORY AFTER SAVE
========================= */
$historyStmt = $pdo->query("
    SELECT id, week_from, week_to, report_date
    FROM housing_service_representative_reports
    ORDER BY id DESC
");
$historyRows = $historyStmt->fetchAll();

if (isset($_GET['saved']) && $_GET['saved'] === '1' && $statusMessage === '') {
    $statusMessage = 'Report saved successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Housing Service Representative Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        .page {
            width: 8.5in;
            min-height: 11in;
            margin: 0 auto;
            background: #fff;
            padding: 0.45in 0.5in;
            box-shadow: 0 0 10px rgba(0,0,0,0.12);
        }

        .toolbar {
            width: 8.5in;
            margin: 0 auto 14px auto;
            background: #fff;
            padding: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .toolbar button,
        .toolbar select {
            font-size: 14px;
            padding: 8px 10px;
        }

        .toolbar .status {
            margin-left: auto;
            font-size: 13px;
            color: #1d5e2a;
            font-weight: 700;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 14px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .title-wrap {
            text-align: center;
            line-height: 1.2;
        }

        .title-top {
            font-size: 18px;
            font-weight: 700;
            text-transform: none;
        }

        .title-main {
            font-size: 24px;
            font-weight: 700;
            margin-top: 2px;
        }

        .line-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 8px 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .label {
            white-space: nowrap;
        }

        .fill-line {
            display: inline-flex;
            align-items: flex-end;
            min-width: 80px;
            flex: 1;
        }

        .short-line {
            width: 110px;
            flex: 0 0 110px;
        }

        .medium-line {
            width: 180px;
            flex: 0 0 180px;
        }

        .long-line {
            width: 100%;
            flex: 1 1 100%;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            border: none;
            border-bottom: 1px solid #000;
            outline: none;
            background: transparent;
            font-size: 14px;
            padding: 2px 2px 3px 2px;
            border-radius: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        textarea {
            resize: none;
            overflow: hidden;
            line-height: 1.35;
            min-height: 28px;
        }

        .inline-checks {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .check-pair {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .check-pair input[type="checkbox"] {
            width: 14px;
            height: 14px;
            margin: 0;
            vertical-align: middle;
        }

        .block {
            margin-top: 10px;
            margin-bottom: 12px;
        }

        .block-label {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .lined-box {
            width: 100%;
            border: none;
            outline: none;
            font-size: 14px;
            line-height: 1.35;
            padding: 2px 4px 6px 4px;
            background-image: linear-gradient(to bottom, transparent calc(1.35em - 1px), #000 calc(1.35em - 1px), #000 1.35em, transparent 1.35em);
            background-size: 100% 1.35em;
            background-position: 0 0.2em;
            min-height: 70px;
        }

        .recap-box {
            min-height: 240px;
        }

        .unity-box {
            min-height: 140px;
        }

        .presentations-box {
            min-height: 230px;
        }

        .footer-row {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-top: 20px;
            font-size: 14px;
        }

        .footer-col {
            flex: 1;
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        .footer-col .fill-line {
            flex: 1;
        }

        .muted {
            font-size: 12px;
            color: #666;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                margin: 0;
                padding: 0.35in 0.45in;
            }

            @page {
                size: Letter portrait;
                margin: 0.35in 0.4in;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <form method="get" action="" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin:0;">
            <label for="load_id"><strong>History by Week Date:</strong></label>
            <select name="load_id" id="load_id" onchange="this.form.submit()">
                <option value="">Select saved report</option>
                <?php foreach ($historyRows as $row): ?>
                    <option value="<?= (int)$row['id'] ?>" <?= ((string)$formData['id'] === (string)$row['id']) ? 'selected' : '' ?>>
                        <?= h(formatWeekLabel($row)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit">Load</button></noscript>
        </form>

        <button type="button" onclick="document.getElementById('hsrForm').requestSubmit();">Save Now</button>
        <button type="button" onclick="window.print();">Print</button>
        <span class="status" id="saveStatus"><?= h($statusMessage !== '' ? $statusMessage : 'Ready') ?></span>
    </div>

    <div class="page">
        <form id="hsrForm" method="post" action="">
            <input type="hidden" name="id" id="record_id" value="<?= h($formData['id']) ?>">

            <div class="header">
                <img src="<?= h(LOGO_PATH) ?>" alt="Oxford House Logo" class="logo">
                <div class="title-wrap">
                    <div class="title-top">Oxford House</div>
                    <div class="title-main">Housing Service Representative Report</div>
                </div>
            </div>

            <div class="line-row">
                <span class="label">Week From;</span>
                <span class="fill-line short-line">
                    <input type="text" name="week_from" value="<?= h($formData['week_from']) ?>">
                </span>
                <span class="label">to</span>
                <span class="fill-line short-line">
                    <input type="text" name="week_to" value="<?= h($formData['week_to']) ?>">
                </span>
            </div>

            <div class="line-row">
                <span class="label">House Visit which house Time and Day:</span>
                <span class="fill-line">
                    <input type="text" name="house_visit" value="<?= h($formData['house_visit']) ?>">
                </span>
            </div>

            <div class="line-row">
                <span class="label">Audit done:</span>
                <span class="inline-checks">
                    <label class="check-pair">yes <input type="checkbox" name="audit_done_yes" value="1" <?= $formData['audit_done_yes'] == '1' ? 'checked' : '' ?>></label>
                    <label class="check-pair">no <input type="checkbox" name="audit_done_no" value="1" <?= $formData['audit_done_no'] == '1' ? 'checked' : '' ?>></label>
                </span>

                <span class="label" style="margin-left:18px;">House summary report done :</span>
                <span class="inline-checks">
                    <label class="check-pair">Yes <input type="checkbox" name="summary_done_yes" value="1" <?= $formData['summary_done_yes'] == '1' ? 'checked' : '' ?>></label>
                    <label class="check-pair">No <input type="checkbox" name="summary_done_no" value="1" <?= $formData['summary_done_no'] == '1' ? 'checked' : '' ?>></label>
                </span>
            </div>

            <div class="line-row">
                <span class="label">Next Chapter Meeting Time &amp; date</span>
                <span class="fill-line">
                    <input type="text" name="next_chapter_meeting" value="<?= h($formData['next_chapter_meeting']) ?>">
                </span>
            </div>

            <div class="line-row">
                <span class="label">Address:</span>
                <span class="fill-line">
                    <input type="text" name="address" value="<?= h($formData['address']) ?>">
                </span>
            </div>

            <div class="line-row">
                <span class="label">Next State meeting Time &amp; Date</span>
                <span class="fill-line">
                    <input type="text" name="next_state_meeting" value="<?= h($formData['next_state_meeting']) ?>">
                </span>
            </div>

            <div class="line-row">
                <span class="label">Number of applications:</span>
                <span class="fill-line">
                    <input type="text" name="number_of_applications" value="<?= h($formData['number_of_applications']) ?>">
                </span>
            </div>

            <div class="block">
                <div class="block-label">Number Contacted and plan of interview:</div>
                <textarea class="lined-box auto-grow" name="number_contacted_plan" rows="3"><?= h($formData['number_contacted_plan']) ?></textarea>
            </div>

            <div class="block">
                <div class="block-label">New members:</div>
                <textarea class="lined-box auto-grow" name="new_members" rows="2"><?= h($formData['new_members']) ?></textarea>
            </div>

            <div class="block">
                <div class="block-label">Interviews setup:</div>
                <textarea class="lined-box auto-grow" name="interviews_setup" rows="2"><?= h($formData['interviews_setup']) ?></textarea>
            </div>

            <div class="block">
                <div class="block-label">Chapter News:</div>
                <textarea class="lined-box auto-grow" name="chapter_news" rows="2"><?= h($formData['chapter_news']) ?></textarea>
            </div>

            <div class="block">
                <div class="block-label">Chapter meeting Recap:</div>
                <textarea class="lined-box recap-box auto-grow" name="chapter_meeting_recap" rows="12"><?= h($formData['chapter_meeting_recap']) ?></textarea>
            </div>

            <div class="block">
                <div class="block-label">Upcoming Unity:</div>
                <textarea class="lined-box unity-box auto-grow" name="upcoming_unity" rows="7"><?= h($formData['upcoming_unity']) ?></textarea>
            </div>

            <div class="block">
                <div class="block-label">Up coming Presentations:</div>
                <textarea class="lined-box presentations-box auto-grow" name="upcoming_presentations" rows="11"><?= h($formData['upcoming_presentations']) ?></textarea>
            </div>

            <div class="footer-row">
                <div class="footer-col">
                    <span class="label">HSR Name / Signature:</span>
                    <span class="fill-line"><input type="text" name="hsr_name" value="<?= h($formData['hsr_name']) ?>"></span>
                </div>
                <div class="footer-col">
                    <span class="label">Date:</span>
                    <span class="fill-line"><input type="text" name="report_date" value="<?= h($formData['report_date']) ?>"></span>
                </div>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('hsrForm');
        const recordId = document.getElementById('record_id');
        const saveStatus = document.getElementById('saveStatus');
        let autosaveTimer = null;

        function autoGrow(el) {
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        }

        document.querySelectorAll('.auto-grow').forEach(el => {
            autoGrow(el);
            el.addEventListener('input', () => autoGrow(el));
        });

        function setSaveStatus(text, isError = false) {
            saveStatus.textContent = text;
            saveStatus.style.color = isError ? '#8a1c1c' : '#1d5e2a';
        }

        function normalizeYesNoPair(yesName, noName, changed) {
            const yes = form.querySelector(`[name="${yesName}"]`);
            const no = form.querySelector(`[name="${noName}"]`);
            if (!yes || !no) return;

            if (changed === yes && yes.checked) no.checked = false;
            if (changed === no && no.checked) yes.checked = false;
        }

        form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => {
                normalizeYesNoPair('audit_done_yes', 'audit_done_no', cb);
                normalizeYesNoPair('summary_done_yes', 'summary_done_no', cb);
                queueAutosave();
            });
        });

        function queueAutosave() {
            clearTimeout(autosaveTimer);
            setSaveStatus('Saving...');
            autosaveTimer = setTimeout(runAutosave, 900);
        }

        async function runAutosave() {
            const fd = new FormData(form);
            fd.append('autosave', '1');

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.ok) {
                    if (data.id) {
                        recordId.value = data.id;
                    }
                    setSaveStatus(data.message || 'Auto-saved');
                } else {
                    setSaveStatus('Auto-save failed.', true);
                }
            } catch (err) {
                setSaveStatus('Auto-save failed.', true);
            }
        }

        form.querySelectorAll('input[type="text"], textarea').forEach(el => {
            el.addEventListener('input', queueAutosave);
            el.addEventListener('change', queueAutosave);
        });
    </script>
</body>
</html>