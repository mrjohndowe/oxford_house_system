<?php
declare(strict_types=1);

/**
 * Oxford House Interview Minutes
 * - Single-file PHP app
 * - Auto-save to MySQL
 * - Search history by interview name, house name, interview date
 * - Load and edit saved interviews
 * - Print button prints a simplified summary of answered questions
 * - Roll call section after interview opening notes
 * - Oxford House logo displayed next to title
 *
 * Logo file expected at:
 * ./images/oxford_house_logo.png
 */

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

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
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/* =========================
   CREATE TABLE
========================= */
// $pdo->exec(" DROP TABLE IF EXISTS oxford_interview_minutes; "); // Drop existing table for clean slate - remove this line in production!
$pdo->exec("
CREATE TABLE IF NOT EXISTS oxford_interview_minutes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    interview_name VARCHAR(255) NOT NULL DEFAULT '',
    house_name VARCHAR(255) NOT NULL DEFAULT '',
    interview_date DATE DEFAULT NULL,
    interviewer_name VARCHAR(255) NOT NULL DEFAULT '',
    contact_phone VARCHAR(100) NOT NULL DEFAULT '',
    outcome_status VARCHAR(100) NOT NULL DEFAULT '',
    vote_percent VARCHAR(50) NOT NULL DEFAULT '',
    move_in_date VARCHAR(100) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    intro_notes MEDIUMTEXT NULL,
    interviewer_do_notes MEDIUMTEXT NULL,
    interviewer_dont_notes MEDIUMTEXT NULL,
    closing_notes MEDIUMTEXT NULL,

    roll_name_1 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_1 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_2 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_2 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_3 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_3 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_4 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_4 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_5 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_5 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_6 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_6 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_7 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_7 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_8 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_8 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_9 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_9 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_10 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_10 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_11 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_11 VARCHAR(20) NOT NULL DEFAULT '',
    roll_name_12 VARCHAR(255) NOT NULL DEFAULT '',
    roll_present_12 VARCHAR(20) NOT NULL DEFAULT '',


    q1 MEDIUMTEXT NULL,
    q2 MEDIUMTEXT NULL,
    q3 MEDIUMTEXT NULL,
    q4 MEDIUMTEXT NULL,
    q5 MEDIUMTEXT NULL,
    q6 MEDIUMTEXT NULL,
    q7 MEDIUMTEXT NULL,
    q8 MEDIUMTEXT NULL,
    q9 MEDIUMTEXT NULL,
    q10 MEDIUMTEXT NULL,
    q11 MEDIUMTEXT NULL,
    q12 MEDIUMTEXT NULL,
    q13 MEDIUMTEXT NULL,
    q14 MEDIUMTEXT NULL,
    q15 MEDIUMTEXT NULL,
    q16 MEDIUMTEXT NULL,
    q17 MEDIUMTEXT NULL,
    q18 MEDIUMTEXT NULL,
    q19 MEDIUMTEXT NULL,
    q20 MEDIUMTEXT NULL,
    q21 MEDIUMTEXT NULL,
    q22 MEDIUMTEXT NULL,
    q23 MEDIUMTEXT NULL,
    q24 MEDIUMTEXT NULL,
    q25 MEDIUMTEXT NULL,
    q26 MEDIUMTEXT NULL,
    q27 MEDIUMTEXT NULL,
    q28 MEDIUMTEXT NULL,
    q29 MEDIUMTEXT NULL,
    q30 MEDIUMTEXT NULL,
    q31 MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* =========================
   HELPERS
========================= */
function h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function postv(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function fv(array $loaded, string $key, string $default = ''): string
{
    return isset($loaded[$key]) ? (string)$loaded[$key] : $default;
}

$questions = [
    1  => 'Tell us a little about yourself.',
    2  => 'How did you get to this point in your recovery?',
    3  => 'How do you feel about your addiction?',
    4  => 'What is your plan for recovery?',
    5  => 'Do you go to AA/NA meetings? What step are you currently working on, if any?',
    6  => 'Do you have a sponsor? If not, will you get one within 30 days?',
    7  => 'Have you identified your relapse triggers? If so, what are they?',
    8  => 'What behaviors might indicate you are headed toward relapse?',
    9  => 'What is your job history? Do you have a profession, trade or skill? Do you have a re-entry plan?',
    10 => 'Do you have an anger problem?',
    11 => 'How do you feel about group living?',
    12 => 'Are you involved in a relationship (significant other, children, etc.)?',
    13 => 'Are you involved in any relationships that may be potentially disruptive to the house?',
    14 => 'Are you on any medications?',
    15 => 'Tell us about your legal problems.',
    16 => 'Are you a registered sex offender?',
    17 => 'Can you handle being confronted in a constructive manner?',
    18 => 'Can you confront others in a constructive manner?',
    19 => 'Do you have any prejudice issues?',
    20 => 'Would you have any problems performing chores?',
    21 => 'What do you feel you can offer Oxford House?',
    22 => 'What can Oxford House offer you?',
    23 => 'Why do you want to live in an Oxford House?',
    24 => 'How would you handle a relapse/confidentiality situation with a roommate?',
    25 => 'House guidelines explained.',
    26 => 'Chapter and HSC meetings explained.',
    27 => 'Officer positions and expectations explained.',
    28 => 'Move-in cost and rent expectations explained.',
    29 => 'If accepted, when could they pay rent and move in?',
    30 => 'Voting procedures explained.',
    31 => 'Final questions and house tour completed.'
];

$questionSummaryLabels = [
    1  => 'Background',
    2  => 'Recovery history',
    3  => 'View of addiction',
    4  => 'Recovery plan',
    5  => 'Meeting involvement',
    6  => 'Sponsor status',
    7  => 'Relapse triggers',
    8  => 'Relapse warning signs',
    9  => 'Employment and skills',
    10 => 'Anger management',
    11 => 'Group living compatibility',
    12 => 'Relationships',
    13 => 'Potential house disruptions',
    14 => 'Medications',
    15 => 'Legal issues',
    16 => 'Sex offender status',
    17 => 'Receives confrontation',
    18 => 'Gives confrontation',
    19 => 'Prejudice concerns',
    20 => 'Chore ability',
    21 => 'What applicant offers',
    22 => 'What applicant needs',
    23 => 'Reason for Oxford House',
    24 => 'Roommate relapse response',
    25 => 'House guidelines review',
    26 => 'Chapter/HSC review',
    27 => 'Officer role review',
    28 => 'Move-in and rent review',
    29 => 'Move-in readiness',
    30 => 'Voting procedure review',
    31 => 'Closing and questions'
];

$rollRows = [
    1 => 'President',
    2 => 'Secretary',
    3 => 'Treasurer',
    4 => 'Comptroller',
    5 => 'Coordinator',
    6 => 'HSR',
    7 => 'Member 1',
    8 => 'Member 2',
    9 => 'Member 3',
    10 => 'Member 4',
    11 => 'Member 5',
    12 => 'Member 6',
];

/* =========================
   LOAD ONE RECORD
========================= */
$loaded = [];
$loadId = isset($_GET['load_id']) ? (int)$_GET['load_id'] : 0;
if ($loadId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM oxford_interview_minutes WHERE id = ?");
    $stmt->execute([$loadId]);
    $loaded = $stmt->fetch() ?: [];
}

/* =========================
   AUTO SAVE / MANUAL SAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postv('action') === 'save') {
    $id = (int)postv('record_id', '0');

    $fields = [
        'interview_name'         => postv('interview_name'),
        'house_name'             => postv('house_name'),
        'interview_date'         => postv('interview_date') ?: null,
        'interviewer_name'       => postv('interviewer_name'),
        'contact_phone'          => postv('contact_phone'),
        'outcome_status'         => postv('outcome_status'),
        'vote_percent'           => postv('vote_percent'),
        'move_in_date'           => postv('move_in_date'),
        'intro_notes'            => postv('intro_notes'),
        'interviewer_do_notes'   => postv('interviewer_do_notes'),
        'interviewer_dont_notes' => postv('interviewer_dont_notes'),
        'closing_notes'          => postv('closing_notes'),
    ];

    for ($i = 1; $i <= 8; $i++) {
        $fields["roll_name_{$i}"] = postv("roll_name_{$i}");
        $fields["roll_present_{$i}"] = postv("roll_present_{$i}");
    }

    for ($i = 1; $i <= 31; $i++) {
        $fields["q{$i}"] = postv("q{$i}");
    }

    if ($id > 0) {
        $check = $pdo->prepare("SELECT id FROM oxford_interview_minutes WHERE id = ?");
        $check->execute([$id]);
        $exists = $check->fetchColumn();

        if ($exists) {
            $setParts = [];
            $values = [];
            foreach ($fields as $col => $val) {
                $setParts[] = "{$col} = ?";
                $values[] = $val;
            }
            $values[] = $id;

            $sql = "UPDATE oxford_interview_minutes SET " . implode(', ', $setParts) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            jsonResponse([
                'status' => 'success',
                'message' => 'Interview updated.',
                'record_id' => $id
            ]);
        }
    }

    $cols = array_keys($fields);
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO oxford_interview_minutes (" . implode(', ', $cols) . ") VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($fields));

    $newId = (int)$pdo->lastInsertId();

    jsonResponse([
        'status' => 'success',
        'message' => 'Interview saved.',
        'record_id' => $newId
    ]);
}

/* =========================
   SEARCH HISTORY
========================= */
$searchInterview = isset($_GET['search_interview']) ? trim((string)$_GET['search_interview']) : '';
$searchHouse     = isset($_GET['search_house']) ? trim((string)$_GET['search_house']) : '';
$searchDate      = isset($_GET['search_date']) ? trim((string)$_GET['search_date']) : '';

$where = [];
$params = [];

if ($searchInterview !== '') {
    $where[] = "interview_name LIKE ?";
    $params[] = '%' . $searchInterview . '%';
}
if ($searchHouse !== '') {
    $where[] = "house_name LIKE ?";
    $params[] = '%' . $searchHouse . '%';
}
if ($searchDate !== '') {
    $where[] = "interview_date = ?";
    $params[] = $searchDate;
}

$sql = "SELECT id, interview_name, house_name, interview_date, outcome_status, updated_at
        FROM oxford_interview_minutes";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY interview_date DESC, updated_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford House Interview Minutes</title>
    <style>
        :root{
            --bg:#f4f7fb;
            --card:#ffffff;
            --border:#d6dde8;
            --text:#1c2430;
            --muted:#667085;
            --primary:#225ea8;
            --primary-dark:#18497f;
            --ok:#16794a;
            --warn:#a15c00;
        }
        *{box-sizing:border-box;}
        html{scroll-behavior:smooth;}
        body{
            margin:0;
            font-family:Arial, Helvetica, sans-serif;
            background:var(--bg);
            color:var(--text);
        }
        .wrap{
            max-width:1450px;
            margin:0 auto;
            padding:20px;
        }
        .page-header{
            display:flex;
            align-items:center;
            gap:18px;
            margin-bottom:18px;
            background:#fff;
            border:1px solid var(--border);
            border-radius:16px;
            padding:16px 20px;
            box-shadow:0 8px 24px rgba(16,24,40,.06);
        }
        .page-header img{
            width:72px;
            height:72px;
            object-fit:contain;
            flex:0 0 auto;
        }
        .page-header h1{
            margin:0;
            font-size:30px;
            line-height:1.1;
        }
        .page-sub{
            color:var(--muted);
            margin-top:6px;
            font-size:14px;
        }
        .grid{
            display:grid;
            grid-template-columns:360px 1fr;
            gap:20px;
            align-items:start;
        }
        .card{
            background:var(--card);
            border:1px solid var(--border);
            border-radius:14px;
            box-shadow:0 8px 24px rgba(16,24,40,.06);
        }
        .sticky{
            position:sticky;
            top:16px;
        }
        .card-inner{
            padding:18px;
        }
        .statusbar{
            display:flex;
            justify-content:space-between;
            gap:12px;
            align-items:center;
            padding:12px 16px;
            border-bottom:1px solid var(--border);
            background:#fafcff;
            border-radius:14px 14px 0 0;
        }
        .status-pill{
            font-size:13px;
            color:var(--muted);
        }
        .save-indicator{
            font-weight:700;
            color:var(--warn);
        }
        .save-indicator.saved{
            color:var(--ok);
        }
        label{
            display:block;
            font-weight:700;
            margin:12px 0 6px;
            font-size:14px;
        }
        input[type="text"],
        input[type="date"],
        textarea,
        select{
            width:100%;
            border:1px solid var(--border);
            border-radius:10px;
            padding:10px 12px;
            font-size:14px;
            background:#fff;
        }
        textarea{
            min-height:88px;
            resize:vertical;
        }
        .small textarea{
            min-height:70px;
        }
        .row{
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:14px;
        }
        .row-2{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:14px;
        }
        .btns{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-top:16px;
        }
        button, .btn, a.btn{
            border:none;
            background:var(--primary);
            color:#fff;
            border-radius:10px;
            padding:10px 14px;
            font-weight:700;
            text-decoration:none;
            cursor:pointer;
            display:inline-block;
            font-size:14px;
        }
        button:hover, .btn:hover, a.btn:hover{
            background:var(--primary-dark);
        }
        .btn-secondary{
            background:#eef4ff;
            color:var(--primary-dark);
            border:1px solid #c8d8f0;
        }
        .section-note{
            background:#f8fbff;
            border:1px solid #dbe7f6;
            border-radius:12px;
            padding:14px;
            margin-bottom:16px;
        }
        .question{
            border:1px solid var(--border);
            border-radius:12px;
            padding:14px;
            margin-bottom:14px;
            background:#fcfdff;
        }
        .question-title{
            font-weight:700;
            margin-bottom:8px;
        }
        .history-list{
            max-height:540px;
            overflow:auto;
            border:1px solid var(--border);
            border-radius:10px;
            margin-top:12px;
        }
        .history-item{
            padding:12px;
            border-bottom:1px solid var(--border);
        }
        .history-item:last-child{
            border-bottom:none;
        }
        .history-item strong{
            display:block;
        }
        .history-meta{
            color:var(--muted);
            font-size:13px;
            margin:4px 0 8px;
        }
        .roll-call-table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }
        .roll-call-table th,
        .roll-call-table td{
            border:1px solid var(--border);
            padding:8px;
            vertical-align:middle;
            background:#fff;
        }
        .roll-call-table th{
            background:#eef4ff;
            text-align:left;
        }
        .roll-call-table select,
        .roll-call-table input[type="text"]{
            margin:0;
        }
        .footer-space{
            height:30px;
        }

        /* PRINT SUMMARY */
        #printSummary{
            display:none;
        }
        .print-summary-title{
            display:flex;
            align-items:center;
            gap:14px;
            margin-bottom:16px;
            border-bottom:2px solid #222;
            padding-bottom:12px;
        }
        .print-summary-title img{
            width:64px;
            height:64px;
            object-fit:contain;
        }
        .print-summary-title h2{
            margin:0;
            font-size:28px;
        }
        .print-summary-sub{
            margin-top:4px;
            color:#333;
            font-size:14px;
        }
        .print-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
            margin-bottom:18px;
        }
        .print-box{
            border:1px solid #333;
            padding:10px 12px;
            border-radius:6px;
            background:#fff;
        }
        .print-box h3{
            margin:0 0 8px;
            font-size:16px;
        }
        .print-list{
            margin:0;
            padding-left:18px;
        }
        .print-list li{
            margin-bottom:6px;
        }
        .print-roll-table{
            width:100%;
            border-collapse:collapse;
            margin-top:8px;
        }
        .print-roll-table th,
        .print-roll-table td{
            border:1px solid #333;
            padding:6px 8px;
            text-align:left;
        }
        .print-section{
            margin-top:18px;
        }
        .print-section h3{
            margin:0 0 8px;
            font-size:17px;
            border-bottom:1px solid #333;
            padding-bottom:4px;
        }
        .print-summary-item{
            margin-bottom:8px;
            line-height:1.4;
        }
        .print-summary-label{
            font-weight:700;
        }
        .print-muted{
            color:#555;
        }

        @media print{
            body{
                background:#fff;
            }
            .wrap{
                max-width:none;
                padding:0;
            }
            .page-header,
            .grid,
            .no-print{
                display:none !important;
            }
            #printSummary{
                display:block !important;
                color:#000;
                font-size:13px;
                line-height:1.35;
            }
        }

        @media (max-width:1100px){
            .grid{
                grid-template-columns:1fr;
            }
            .sticky{
                position:static;
            }
            .print-grid{
                grid-template-columns:1fr;
            }
        }
        @media (max-width:700px){
            .row, .row-2{
                grid-template-columns:1fr;
            }
            .page-header{
                align-items:flex-start;
            }
            .page-header img{
                width:58px;
                height:58px;
            }
        }
    </style>
</head>
<body>
<div class="wrap">

    <div class="page-header no-print">
        <img src="../images/oxford_house_logo.png" alt="Oxford House Logo" onerror="this.style.display='none';">
        <div>
            <h1>Oxford House Interview Minutes</h1>
            <div class="page-sub">Auto-saving interview form with searchable history and simplified print summary.</div>
        </div>
    </div>

    <div class="grid no-print">
        <aside class="card sticky">
            <div class="statusbar">
                <div class="status-pill">History & Search</div>
                <div id="saveState" class="save-indicator">Not saved yet</div>
            </div>
            <div class="card-inner">
                <form method="get">
                    <label for="search_interview">Search by Interview Name</label>
                    <input type="text" id="search_interview" name="search_interview" value="<?= h($searchInterview) ?>">

                    <label for="search_house">Search by House Name</label>
                    <input type="text" id="search_house" name="search_house" value="<?= h($searchHouse) ?>">

                    <label for="search_date">Search by Interview Date</label>
                    <input type="date" id="search_date" name="search_date" value="<?= h($searchDate) ?>">

                    <div class="btns">
                        <button type="submit">Search</button>
                        <a class="btn btn-secondary" href="<?= h(basename(__FILE__)) ?>">Clear</a>
                    </div>
                </form>

                <div class="history-list">
                    <?php if (!$history): ?>
                        <div class="history-item">No interview records found.</div>
                    <?php else: ?>
                        <?php foreach ($history as $item): ?>
                            <div class="history-item">
                                <strong><?= h($item['interview_name']) ?></strong>
                                <div class="history-meta">
                                    House: <?= h($item['house_name']) ?><br>
                                    Date: <?= h($item['interview_date']) ?><br>
                                    Status: <?= h($item['outcome_status']) ?><br>
                                    Updated: <?= h($item['updated_at']) ?>
                                </div>
                                <a class="btn btn-secondary" href="?load_id=<?= (int)$item['id'] ?>">Load Record</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <main class="card">
            <div class="statusbar">
                <div class="status-pill">
                    <?= !empty($loaded['id']) ? 'Editing Record #' . (int)$loaded['id'] : 'New Interview Record' ?>
                </div>
                <div class="status-pill" id="recordIdText">
                    Record ID: <?= !empty($loaded['id']) ? (int)$loaded['id'] : 'new' ?>
                </div>
            </div>

            <div class="card-inner">
                <form id="interviewForm">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="record_id" id="record_id" value="<?= h(fv($loaded, 'id')) ?>">

                    <div class="row">
                        <div>
                            <label for="interview_name">Interview Name</label>
                            <input type="text" id="interview_name" name="interview_name" value="<?= h(fv($loaded, 'interview_name')) ?>" required>
                        </div>
                        <div>
                            <label for="house_name">House Name</label>
                            <input type="text" id="house_name" name="house_name" value="<?= h(fv($loaded, 'house_name')) ?>" required>
                        </div>
                        <div>
                            <label for="interview_date">Date of Interview</label>
                            <input type="date" id="interview_date" name="interview_date" value="<?= h(fv($loaded, 'interview_date', date('Y-m-d'))) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div>
                            <label for="interviewer_name">President's Name</label>
                            <input type="text" id="interviewer_name" name="interviewer_name" value="<?= h(fv($loaded, 'interviewer_name')) ?>">
                        </div>
                        <div>
                            <label for="contact_phone">Applicant Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="<?= h(fv($loaded, 'contact_phone')) ?>">
                        </div>
                        <div>
                            <label for="outcome_status">Outcome Status</label>
                            <select id="outcome_status" name="outcome_status">
                                <?php
                                $outcome = fv($loaded, 'outcome_status');
                                $options = ['', 'Pending', 'Accepted', 'Declined', 'Waiting List','Needs Follow-up'];
                                foreach ($options as $opt):
                                ?>
                                    <option value="<?= h($opt) ?>" <?= $outcome === $opt ? 'selected' : '' ?>>
                                        <?= h($opt === '' ? 'Select status' : $opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div>
                            <label for="vote_percent">Vote Percentage</label>
                            <input type="text" id="vote_percent" name="vote_percent" value="<?= h(fv($loaded, 'vote_percent')) ?>" placeholder="Example: 80%">
                        </div>
                        <div>
                            <label for="move_in_date">Move In Date / Expected Move In</label>
                            <input type="text" id="move_in_date" name="move_in_date" value="<?= h(fv($loaded, 'move_in_date')) ?>">
                        </div>
                        <div></div>
                    </div>

                    <div class="section-note">
                        <strong>Interview Opening Notes</strong>
                        <label for="intro_notes">Opening / Application Review / Oxford House Concept / Introductions</label>
                        <textarea id="intro_notes" readonly placeholder="Once the interview is over, you may ask the applicant to set into another room. Explain that after they leave, the house members will discuss the interview and vote. Let the applicant know that someone will call them in to say if they have been accepted or not. In the event that the applicant was not accepted and they question why, simply explain to them that they did not get an 80% vote from the House members – then refer them to another house. "name="intro_notes"><?= h(fv($loaded, 'intro_notes')) ?></textarea>

                        <div class="row-2">
                            <div>
                                <label for="interviewer_do_notes">Interviewer DO Notes</label>
                                <textarea readonly id="interviewer_do_notes" name="interviewer_do_notes" placeholder="DO: Listen, share briefly about yourself, put the person at ease, and ask questions if you want more information. "><?= h(fv($loaded, 'interviewer_do_notes')) ?></textarea>
                            </div>
                            <div>
                                <label for="interviewer_dont_notes">Interviewer DON’T Notes</label>
                                <textarea readonly id="interviewer_dont_notes" name="interviewer_dont_notes" placeholder="DON’T: Give advice about their recovery, discuss anything related to house business or another member’s problems, or discuss sexual preference, politics or religion"><?= h(fv($loaded, 'interviewer_dont_notes')) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="section-note">
                        <strong>Roll Call</strong>
                        <table class="roll-call-table">
                            <thead>
                                <tr>
                                    <th style="width:28%;">Position</th>
                                    <th style="width:42%;">Name</th>
                                    <th style="width:30%;">Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rollRows as $i => $position): ?>
                                    <tr>
                                        <td><?= h($position) ?></td>
                                        <td>
                                            <input type="text" name="roll_name_<?= $i ?>" value="<?= h(fv($loaded, "roll_name_{$i}")) ?>">
                                        </td>
                                        <td>
                                            <?php $presentVal = fv($loaded, "roll_present_{$i}"); ?>
                                            <select name="roll_present_<?= $i ?>">
                                                <option value="" <?= $presentVal === '' ? 'selected' : '' ?>>Select</option>
                                                <option value="Yes" <?= $presentVal === 'Yes' ? 'selected' : '' ?>>Yes</option>
                                                <option value="No" <?= $presentVal === 'No' ? 'selected' : '' ?>>No</option>
                                                <option value="Excused" <?= $presentVal === 'Excused' ? 'selected' : '' ?>>Excused</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php foreach ($questions as $num => $text): ?>
                        <div class="question">
                            <div class="question-title"><?= $num ?>. <?= h($text) ?></div>
                            <div class="small">
                                <textarea name="q<?= $num ?>" id="q<?= $num ?>"><?= h(fv($loaded, 'q' . $num)) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="section-note">
                        <strong>Closing Notes</strong>
                        <label for="closing_notes">Final Questions / Show Around / Additional Notes</label>
                        <textarea id="closing_notes" name="closing_notes"><?= h(fv($loaded, 'closing_notes')) ?></textarea>
                    </div>

                    <div class="btns">
                        <button type="button" id="saveBtn">Save Now</button>
                        <button type="button" id="printBtn" class="btn-secondary">Print Summary</button>
                        <a class="btn btn-secondary" href="<?= h(basename(__FILE__)) ?>">Start New</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div id="printSummary">
        <div class="print-summary-title">
            <img src="../images/oxford_house_logo.png" alt="Oxford House Logo" onerror="this.style.display='none';">
            <div>
                <h2>Oxford House Interview Minutes</h2>
                <div class="print-summary-sub">Simplified Interview Summary</div>
            </div>
        </div>

        <div class="print-grid">
            <div class="print-box">
                <h3>Interview Information</h3>
                <div><strong>Interview Name:</strong> <span data-print="interview_name"></span></div>
                <div><strong>House Name:</strong> <span data-print="house_name"></span></div>
                <div><strong>Interview Date:</strong> <span data-print="interview_date"></span></div>
                <div><strong>Interviewer:</strong> <span data-print="interviewer_name"></span></div>
                <div><strong>Phone:</strong> <span data-print="contact_phone"></span></div>
            </div>

            <div class="print-box">
                <h3>Decision Information</h3>
                <div><strong>Status:</strong> <span data-print="outcome_status"></span></div>
                <div><strong>Vote %:</strong> <span data-print="vote_percent"></span></div>
                <div><strong>Move In Date:</strong> <span data-print="move_in_date"></span></div>
            </div>
        </div>

        <div class="print-section">
            <h3>Opening Notes</h3>
            <div class="print-summary-item" data-print="intro_notes"></div>
        </div>

        <div class="print-section">
            <h3>Roll Call</h3>
            <table class="print-roll-table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Name</th>
                        <th>Present</th>
                    </tr>
                </thead>
                <tbody id="printRollBody"></tbody>
            </table>
        </div>

        <div class="print-section">
            <h3>Interview Summary</h3>
            <div id="printQuestionSummary"></div>
        </div>

        <div class="print-section">
            <h3>Closing Notes</h3>
            <div class="print-summary-item" data-print="closing_notes"></div>
        </div>
    </div>

    <div class="footer-space no-print"></div>
</div>

<script>
(function () {
    const form = document.getElementById('interviewForm');
    const saveBtn = document.getElementById('saveBtn');
    const printBtn = document.getElementById('printBtn');
    const saveState = document.getElementById('saveState');
    const recordIdInput = document.getElementById('record_id');
    const recordIdText = document.getElementById('recordIdText');
    const printRollBody = document.getElementById('printRollBody');
    const printQuestionSummary = document.getElementById('printQuestionSummary');

    const questionSummaryLabels = <?= json_encode($questionSummaryLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const rollPositions = <?= json_encode($rollRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    let saveTimer = null;
    let isSaving = false;
    let hasChanges = false;

    function setStatus(text, saved = false) {
        if (!saveState) return;
        saveState.textContent = text;
        saveState.classList.toggle('saved', saved);
    }

    function autoResizeTextarea(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    function resizeAllTextareas() {
        form.querySelectorAll('textarea').forEach(autoResizeTextarea);
    }

    function queueSave() {
        hasChanges = true;
        setStatus('Changes pending...', false);

        if (saveTimer) {
            clearTimeout(saveTimer);
        }

        saveTimer = setTimeout(() => {
            saveForm(false);
        }, 1200);
    }

    function valueOf(name) {
        const el = form.querySelector('[name="' + name + '"]');
        return el ? el.value.trim() : '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function simpleSummary(text, maxLength = 180) {
        if (!text) return '';
        let cleaned = text
            .replace(/\s+/g, ' ')
            .replace(/\n+/g, ' ')
            .trim();

        cleaned = cleaned.replace(/^\-\s*/g, '');
        cleaned = cleaned.replace(/^\d+[\.\)]\s*/g, '');

        if (cleaned.length <= maxLength) {
            return cleaned;
        }

        let shortened = cleaned.slice(0, maxLength);
        const lastSpace = shortened.lastIndexOf(' ');
        if (lastSpace > 80) {
            shortened = shortened.slice(0, lastSpace);
        }
        return shortened.replace(/[,\s;:-]+$/g, '') + '...';
    }

    function fillPrintFields() {
        document.querySelectorAll('[data-print]').forEach(el => {
            const field = el.getAttribute('data-print');
            const value = valueOf(field);
            el.innerHTML = escapeHtml(value || '—');
        });
    }

    function buildPrintRollCall() {
        printRollBody.innerHTML = '';
        let hasAny = false;

        Object.keys(rollPositions).forEach(function (key) {
            const i = key;
            const position = rollPositions[i];
            const name = valueOf('roll_name_' + i);
            const present = valueOf('roll_present_' + i);

            if (!name && !present) {
                return;
            }

            hasAny = true;
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(position) + '</td>' +
                '<td>' + escapeHtml(name || '—') + '</td>' +
                '<td>' + escapeHtml(present || '—') + '</td>';
            printRollBody.appendChild(tr);
        });

        if (!hasAny) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="3" class="print-muted">No roll call entries provided.</td>';
            printRollBody.appendChild(tr);
        }
    }

    function buildPrintQuestionSummary() {
        printQuestionSummary.innerHTML = '';
        let count = 0;

        Object.keys(questionSummaryLabels).forEach(function (num) {
            const answer = valueOf('q' + num);
            if (!answer) return;

            count++;
            const div = document.createElement('div');
            div.className = 'print-summary-item';
            div.innerHTML =
                '<span class="print-summary-label">' + escapeHtml(questionSummaryLabels[num]) + ':</span> ' +
                '<span>' + escapeHtml(simpleSummary(answer)) + '</span>';
            printQuestionSummary.appendChild(div);
        });

        const introNotes = valueOf('interviewer_do_notes');
        const dontNotes = valueOf('interviewer_dont_notes');

        if (introNotes) {
            count++;
            const div = document.createElement('div');
            div.className = 'print-summary-item';
            div.innerHTML =
                '<span class="print-summary-label">Interviewer notes:</span> ' +
                '<span>' + escapeHtml(simpleSummary(introNotes)) + '</span>';
            printQuestionSummary.appendChild(div);
        }

        if (dontNotes) {
            count++;
            const div = document.createElement('div');
            div.className = 'print-summary-item';
            div.innerHTML =
                '<span class="print-summary-label">Concerns / cautions:</span> ' +
                '<span>' + escapeHtml(simpleSummary(dontNotes)) + '</span>';
            printQuestionSummary.appendChild(div);
        }

        if (count === 0) {
            const div = document.createElement('div');
            div.className = 'print-summary-item print-muted';
            div.textContent = 'No answered questions available for summary.';
            printQuestionSummary.appendChild(div);
        }
    }

    function preparePrintSummary() {
        fillPrintFields();
        buildPrintRollCall();
        buildPrintQuestionSummary();
    }

    async function saveForm(manual = false, afterSavePrint = false) {
        if (isSaving) return;

        const interviewName = valueOf('interview_name');
        const house = valueOf('house_name');
        const interviewDate = valueOf('interview_date');

        if (!interviewName || !house || !interviewDate) {
            if (manual || afterSavePrint) {
                alert('Interview name, house name, and interview date are required before saving.');
            }
            return;
        }

        isSaving = true;
        setStatus('Saving...', false);

        try {
            const formData = new FormData(form);

            const res = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();

            if (data.status === 'success') {
                if (data.record_id) {
                    recordIdInput.value = data.record_id;
                    recordIdText.textContent = 'Record ID: ' + data.record_id;
                }
                hasChanges = false;
                setStatus('Saved', true);

                if (afterSavePrint) {
                    preparePrintSummary();
                    setTimeout(() => window.print(), 150);
                }
            } else {
                setStatus('Save failed', false);
            }
        } catch (err) {
            console.error(err);
            setStatus('Save failed', false);
        } finally {
            isSaving = false;
        }
    }

    form.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', function () {
            if (el.tagName === 'TEXTAREA') {
                autoResizeTextarea(el);
            }
            queueSave();
        });
        el.addEventListener('change', queueSave);
    });

    saveBtn.addEventListener('click', function () {
        saveForm(true, false);
    });

    printBtn.addEventListener('click', function () {
        saveForm(true, true);
    });

    resizeAllTextareas();

    setInterval(() => {
        if (hasChanges) {
            saveForm(false, false);
        }
    }, 15000);
})();
</script>
</body>
</html>