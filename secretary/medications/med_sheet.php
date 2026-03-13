<?php
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

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
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

/* =========================
   SETTINGS
========================= */
$lineCount = 25;
$columnWidths = [9, 20, 12, 12, 12, 12, 11, 12];

/* =========================
   HELPERS
========================= */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function postedRows(int $lineCount): array
{
    $rows = [];

    for ($i = 0; $i < $lineCount; $i++) {
        $rows[] = [
            'entry_date'        => trim((string)($_POST['entry_date'][$i] ?? '')),
            'medication_name'   => trim((string)($_POST['medication_name'][$i] ?? '')),
            'dosage'            => trim((string)($_POST['dosage'][$i] ?? '')),
            'frequency'         => trim((string)($_POST['frequency'][$i] ?? '')),
            'previous_count'    => trim((string)($_POST['previous_count'][$i] ?? '')),
            'current_count'     => trim((string)($_POST['current_count'][$i] ?? '')),
            'member_initials'   => trim((string)($_POST['member_initials'][$i] ?? '')),
            'witness_initials'  => trim((string)($_POST['witness_initials'][$i] ?? '')),
        ];
    }

    return $rows;
}

function blankRows(int $lineCount): array
{
    $rows = [];
    for ($i = 0; $i < $lineCount; $i++) {
        $rows[] = [
            'entry_date'        => '',
            'medication_name'   => '',
            'dosage'            => '',
            'frequency'         => '',
            'previous_count'    => '',
            'current_count'     => '',
            'member_initials'   => '',
            'witness_initials'  => '',
        ];
    }
    return $rows;
}

function hasAnyRowData(array $row): bool
{
    foreach ($row as $value) {
        if (trim((string)$value) !== '') {
            return true;
        }
    }
    return false;
}

/* =========================
   DEFAULT FORM DATA
========================= */
$message = '';
$messageType = 'success';
$recordId = isset($_GET['load']) ? (int)$_GET['load'] : 0;

$form = [
    'resident_name' => '',
    'sheet_date'    => date('Y-m-d'),
    'rows'          => blankRows($lineCount),
];

/* =========================
   LOAD RECORD
========================= */
if ($recordId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM medication_count_sheets WHERE id = ?");
    $stmt->execute([$recordId]);
    $sheet = $stmt->fetch();

    if ($sheet) {
        $form['resident_name'] = (string)$sheet['resident_name'];
        $form['sheet_date']    = (string)$sheet['sheet_date'];

        $stmtRows = $pdo->prepare("
            SELECT *
            FROM medication_count_sheet_rows
            WHERE sheet_id = ?
            ORDER BY row_number ASC
        ");
        $stmtRows->execute([$recordId]);
        $dbRows = $stmtRows->fetchAll();

        $rows = blankRows($lineCount);

        foreach ($dbRows as $row) {
            $index = (int)$row['row_number'] - 1;
            if ($index >= 0 && $index < $lineCount) {
                $rows[$index] = [
                    'entry_date'        => (string)$row['entry_date'],
                    'medication_name'   => (string)$row['medication_name'],
                    'dosage'            => (string)$row['dosage'],
                    'frequency'         => (string)$row['frequency'],
                    'previous_count'    => (string)$row['previous_count'],
                    'current_count'     => (string)$row['current_count'],
                    'member_initials'   => (string)$row['member_initials'],
                    'witness_initials'  => (string)$row['witness_initials'],
                ];
            }
        }

        $form['rows'] = $rows;
    } else {
        $message = 'Requested record was not found.';
        $messageType = 'error';
    }
}

/* =========================
   SAVE RECORD
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;

    $form['resident_name'] = trim((string)($_POST['resident_name'] ?? ''));
    $form['sheet_date']    = trim((string)($_POST['sheet_date'] ?? date('Y-m-d')));
    $form['rows']          = postedRows($lineCount);

    if ($form['resident_name'] === '') {
        $message = 'Member Name is required.';
        $messageType = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            if ($recordId > 0) {
                $stmt = $pdo->prepare("
                    UPDATE medication_count_sheets
                    SET resident_name = ?, sheet_date = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $form['resident_name'],
                    $form['sheet_date'],
                    $recordId
                ]);

                $stmtDelete = $pdo->prepare("DELETE FROM medication_count_sheet_rows WHERE sheet_id = ?");
                $stmtDelete->execute([$recordId]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO medication_count_sheets (resident_name, sheet_date, created_at, updated_at)
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $form['resident_name'],
                    $form['sheet_date']
                ]);

                $recordId = (int)$pdo->lastInsertId();
            }

            $stmtRow = $pdo->prepare("
                INSERT INTO medication_count_sheet_rows
                (
                    sheet_id,
                    row_number,
                    entry_date,
                    medication_name,
                    dosage,
                    frequency,
                    previous_count,
                    current_count,
                    member_initials,
                    witness_initials
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($form['rows'] as $i => $row) {
                if (!hasAnyRowData($row)) {
                    continue;
                }

                $stmtRow->execute([
                    $recordId,
                    $i + 1,
                    $row['entry_date'],
                    $row['medication_name'],
                    $row['dosage'],
                    $row['frequency'],
                    $row['previous_count'],
                    $row['current_count'],
                    $row['member_initials'],
                    $row['witness_initials'],
                ]);
            }

            $pdo->commit();

            $message = 'Medication Count sheet saved successfully.';
            $messageType = 'success';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $message = 'Save failed: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

/* =========================
   HISTORY SEARCH
========================= */
$searchName = trim((string)($_GET['search_name'] ?? ''));
$historyRows = [];

if ($searchName !== '') {
    $stmt = $pdo->prepare("
        SELECT id, resident_name, sheet_date, created_at, updated_at
        FROM medication_count_sheets
        WHERE resident_name LIKE ?
        ORDER BY sheet_date DESC, id DESC
        LIMIT 100
    ");
    $stmt->execute(['%' . $searchName . '%']);
    $historyRows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Count & Daily Tracking Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #000;
            background: #f4f4f4;
        }

        .page-wrap {
            max-width: 1400px;
            margin: 0 auto;
        }

        .panel {
            background: #fff;
            border: 1px solid #ccc;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        h1, h2, h3 {
            margin-top: 0;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .message {
            padding: 12px 14px;
            margin-bottom: 16px;
            border-radius: 4px;
            font-weight: bold;
        }

        .message.success {
            background: #e7f6ea;
            border: 1px solid #8dce99;
            color: #226834;
        }

        .message.error {
            background: #fdeaea;
            border: 1px solid #e0a1a1;
            color: #8a1f1f;
        }

        .form-row {
            display: grid;
            grid-template-columns: 180px 1fr 180px 220px;
            gap: 10px;
            align-items: center;
            margin-bottom: 16px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #999;
            box-sizing: border-box;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        button,
        .btn-link {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            background: #2f5d91;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-secondary {
            background: #555;
        }

        .btn-link:hover,
        button:hover {
            opacity: 0.92;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #474747;
            color: #fff;
            text-align: center;
        }

        .data-table input[type="text"],
        .data-table input[type="date"] {
            border: none;
            padding: 4px;
            width: 100%;
            background: transparent;
            font-size: 13px;
        }

        .data-table td {
            padding: 2px 4px;
            height: 34px;
        }

        .history-table th,
        .history-table td {
            border: 1px solid #bbb;
            padding: 8px;
        }

        .history-table th {
            background: #333;
            color: #fff;
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin-bottom: 15px;
        }

        .muted {
            color: #666;
            font-size: 13px;
        }

        @media (max-width: 980px) {
            .top-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body {
                background: #fff;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .panel {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }

            .page-wrap {
                max-width: none;
            }
        }
    </style>
</head>
<body>
<div class="page-wrap">

    <?php if ($message !== ''): ?>
        <div class="message <?php echo h($messageType); ?>">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="top-grid no-print">
        <div class="panel">
            <h3>Search History by Member Name</h3>
            <form method="get">
                <div class="search-row">
                    <input type="text" name="search_name" value="<?php echo h($searchName); ?>" placeholder="Enter Member name">
                    <button type="submit">Search</button>
                </div>
            </form>

            <?php if ($searchName !== ''): ?>
                <?php if (!empty($historyRows)): ?>
                    <table class="history-table">
                        <tr>
                            <th>Member Name</th>
                            <th>Sheet Date</th>
                            <th>Saved</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($historyRows as $row): ?>
                            <tr>
                                <td><?php echo h($row['resident_name']); ?></td>
                                <td><?php echo h($row['sheet_date']); ?></td>
                                <td><?php echo h($row['updated_at']); ?></td>
                                <td>
                                    <a class="btn-link" href="?load=<?php echo (int)$row['id']; ?>">Load</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p class="muted">No matching history found.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h3>Instructions</h3>
            <p class="muted">
                Enter the member name and medication data, then click Save Sheet.
                Use the history search to find past sheets by member name and reload them for editing.
            </p>
        </div>
    </div>

    <div class="panel">
        <form method="post">
            <input type="hidden" name="record_id" value="<?php echo (int)$recordId; ?>">

            <center><h2>Medication Count</h2></center>

            <div class="form-row">
                <label for="resident_name">Member Name:</label>
                <input type="text" id="resident_name" name="resident_name" value="<?php echo h($form['resident_name']); ?>" required>

                <label for="sheet_date">Sheet Date:</label>
                <input type="date" id="sheet_date" name="sheet_date" value="<?php echo h($form['sheet_date']); ?>">
            </div>

            <table class="data-table">
                <tr>
                    <th style="width:<?php echo $columnWidths[0]; ?>%;">Date</th>
                    <th style="width:<?php echo $columnWidths[1]; ?>%;">Medication Name</th>
                    <th style="width:<?php echo $columnWidths[2]; ?>%;">Dosage</th>
                    <th style="width:<?php echo $columnWidths[3]; ?>%;">Frequency</th>
                    <th style="width:<?php echo $columnWidths[4]; ?>%;">Previous Count</th>
                    <th style="width:<?php echo $columnWidths[5]; ?>%;">Current Count</th>
                    <th style="width:<?php echo $columnWidths[6]; ?>%;">Member Initials</th>
                    <th style="width:<?php echo $columnWidths[7]; ?>%;">Witness Initials</th>
                </tr>

                <?php for ($i = 0; $i < $lineCount; $i++): ?>
                    <tr>
                        <td>
                            <input type="date" name="entry_date[]" value="<?php echo h($form['rows'][$i]['entry_date']); ?>">
                        </td>
                        <td>
                            <input type="text" name="medication_name[]" value="<?php echo h($form['rows'][$i]['medication_name']); ?>">
                        </td>
                        <td>
                            <input type="text" name="dosage[]" value="<?php echo h($form['rows'][$i]['dosage']); ?>">
                        </td>
                        <td>
                            <input type="text" name="frequency[]" value="<?php echo h($form['rows'][$i]['frequency']); ?>">
                        </td>
                        <td>
                            <input type="text" name="previous_count[]" value="<?php echo h($form['rows'][$i]['previous_count']); ?>">
                        </td>
                        <td>
                            <input type="text" name="current_count[]" value="<?php echo h($form['rows'][$i]['current_count']); ?>">
                        </td>
                        <td>
                            <input type="text" name="member_initials[]" value="<?php echo h($form['rows'][$i]['member_initials']); ?>">
                        </td>
                        <td>
                            <input type="text" name="witness_initials[]" value="<?php echo h($form['rows'][$i]['witness_initials']); ?>">
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

            <div class="actions no-print">
                <button type="submit">Save Sheet</button>
                <a class="btn-link btn-secondary" href="<?php echo h($_SERVER['PHP_SELF']); ?>">New Blank Form</a>
                <button type="button" class="btn-secondary" onclick="window.print()">Print</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>