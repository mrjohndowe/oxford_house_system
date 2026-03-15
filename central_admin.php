<?php
require_once __DIR__ . '/extras/master_config.php';
oxford_require_role(['central_admin', 'super_admin']);

$message = '';
$messageType = 'success';
$newHouseCredentials = null;

function oxford_safe_count(PDO $pdo, string $sql, array $params = []): int
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function oxford_safe_fetch_all(PDO $pdo, string $sql, array $params = []): array
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'create_house') {
        $houseName = trim((string)($_POST['house_name'] ?? ''));
        $houseCode = oxford_slugify((string)($_POST['house_code'] ?? ''));
        $databaseNameInput = strtolower(trim((string)($_POST['database_name'] ?? '')));
        $city = trim((string)($_POST['city'] ?? ''));
        $state = trim((string)($_POST['state'] ?? ''));
        $managerName = trim((string)($_POST['manager_name'] ?? ''));
        $managerEmail = trim((string)($_POST['manager_email'] ?? ''));
        $managerPassword = (string)($_POST['manager_password'] ?? '');
        $templateDatabase = trim((string)($_POST['template_database'] ?? '')) ?: 'secretary';

        if ($houseName === '' || $managerName === '' || $managerEmail === '' || $managerPassword === '') {
            $message = 'House name, manager name, manager email, and manager password are required.';
            $messageType = 'error';
        } elseif (!filter_var($managerEmail, FILTER_VALIDATE_EMAIL)) {
            $message = 'The initial login email address is not valid.';
            $messageType = 'error';
        } else {
            try {
                $emailCheck = $masterPdo->prepare('SELECT id FROM oxford_master_users WHERE email = ? LIMIT 1');
                $emailCheck->execute([$managerEmail]);
                if ($emailCheck->fetchColumn()) {
                    throw new RuntimeException('That initial login email already exists.');
                }

                $houseCode = $houseCode !== ''
                    ? oxford_generate_unique_house_code($masterPdo, $houseCode)
                    : oxford_generate_unique_house_code($masterPdo, $houseName);

                if ($databaseNameInput !== '') {
                    $databaseNameInput = preg_replace('/[^a-z0-9_]+/i', '_', $databaseNameInput) ?? '';
                    $databaseNameInput = trim($databaseNameInput, '_');
                }

                $databaseName = $databaseNameInput !== ''
                    ? $databaseNameInput
                    : oxford_generate_unique_database_name($masterPdo, $oxfordServerPdo, $houseName);

                $dbCheck = $masterPdo->prepare('SELECT id FROM oxford_master_houses WHERE database_name = ? LIMIT 1');
                $dbCheck->execute([$databaseName]);
                if ($dbCheck->fetchColumn()) {
                    throw new RuntimeException('That database name is already registered in the central system.');
                }

                if (oxford_database_exists($oxfordServerPdo, $databaseName)) {
                    throw new RuntimeException('That database name already exists on the MySQL server.');
                }

                $masterPdo->beginTransaction();

                $copiedTables = oxford_create_house_database($oxfordServerPdo, $databaseName, $templateDatabase);

                $insertHouse = $masterPdo->prepare(
                    'INSERT INTO oxford_master_houses (house_name, house_code, database_name, city, state, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)'
                );
                $insertHouse->execute([$houseName, $houseCode, $databaseName, $city, $state]);
                $houseId = (int)$masterPdo->lastInsertId();

                $insertUser = $masterPdo->prepare(
                    'INSERT INTO oxford_master_users (full_name, email, password_hash, role, status)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $insertUser->execute([
                    $managerName,
                    $managerEmail,
                    password_hash($managerPassword, PASSWORD_DEFAULT),
                    'house_manager',
                    'active'
                ]);
                $userId = (int)$masterPdo->lastInsertId();

                $linkUser = $masterPdo->prepare(
                    'INSERT INTO oxford_master_house_user_access (house_id, user_id, is_primary)
                     VALUES (?, ?, 1)'
                );
                $linkUser->execute([$houseId, $userId]);

                oxford_log_activity($masterPdo, $houseId, 'central_admin.php', 'house_created', [
                    'house_name' => $houseName,
                    'database_name' => $databaseName,
                    'initial_login' => $managerEmail,
                    'template_database' => $templateDatabase,
                    'tables_created' => count($copiedTables),
                ], (int)$oxfordUser['id']);

                oxford_log_audit($masterPdo, [
                    'house_id' => $houseId,
                    'user_id' => (int)$oxfordUser['id'],
                    'action_name' => 'house_created',
                    'page_name' => 'central_admin.php',
                    'target_table' => 'oxford_master_houses',
                    'target_id' => (string)$houseId,
                    'details' => [
                        'house_name' => $houseName,
                        'house_code' => $houseCode,
                        'database_name' => $databaseName,
                        'template_database' => $templateDatabase,
                        'tables_created' => $copiedTables,
                        'initial_user_email' => $managerEmail,
                    ],
                ]);

                $masterPdo->commit();

                $message = 'House created successfully. The central system registered the house, created its database, copied the template tables, and created the initial house manager login.';
                $newHouseCredentials = [
                    'house_name' => $houseName,
                    'house_code' => $houseCode,
                    'database_name' => $databaseName,
                    'manager_name' => $managerName,
                    'manager_email' => $managerEmail,
                    'manager_password' => $managerPassword,
                    'template_database' => $templateDatabase,
                    'table_count' => count($copiedTables),
                ];
            } catch (Throwable $e) {
                if ($masterPdo->inTransaction()) {
                    $masterPdo->rollBack();
                }
                $message = $e->getMessage();
                $messageType = 'error';
            }
        }
    }

    if ($action === 'clear_logs') {
        $logType = (string)($_POST['log_type'] ?? '');

        try {
            $allowed = ['activity', 'audit', 'all'];
            if (!in_array($logType, $allowed, true)) {
                throw new RuntimeException('Invalid log clear option selected.');
            }

            $activityBefore = oxford_safe_count($masterPdo, 'SELECT COUNT(*) FROM oxford_master_activity');
            $auditBefore = oxford_safe_count($masterPdo, 'SELECT COUNT(*) FROM oxford_master_audit_log');

            $masterPdo->beginTransaction();

            if ($logType === 'activity') {
                // Keep an audit note that activity was cleared.
                oxford_log_audit($masterPdo, [
                    'house_id' => null,
                    'user_id' => (int)$oxfordUser['id'],
                    'action_name' => 'activity_log_cleared',
                    'page_name' => 'central_admin.php',
                    'target_table' => 'oxford_master_activity',
                    'target_id' => null,
                    'details' => [
                        'rows_deleted' => $activityBefore,
                        'cleared_by' => (string)($oxfordUser['full_name'] ?? ''),
                    ],
                ]);

                $masterPdo->exec('DELETE FROM oxford_master_activity');
                $message = 'Activity log cleared successfully.';
            } elseif ($logType === 'audit') {
                // Keep an activity note that audit was cleared.
                oxford_log_activity($masterPdo, null, 'central_admin.php', 'audit_log_cleared', [
                    'rows_deleted' => $auditBefore,
                    'cleared_by' => (string)($oxfordUser['full_name'] ?? ''),
                ], (int)$oxfordUser['id']);

                $masterPdo->exec('DELETE FROM oxford_master_audit_log');
                $message = 'Audit log cleared successfully.';
            } else {
                // For full clear, delete both.
                // Since both log tables are being wiped, do the delete directly.
                $masterPdo->exec('DELETE FROM oxford_master_activity');
                $masterPdo->exec('DELETE FROM oxford_master_audit_log');
                $message = 'Activity and audit logs cleared successfully.';
            }

            $masterPdo->commit();
            $messageType = 'success';
        } catch (Throwable $e) {
            if ($masterPdo->inTransaction()) {
                $masterPdo->rollBack();
            }
            $message = 'Unable to clear logs: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

include __DIR__ . '/extras/header.php';

$knownTables = [
    'chapter_meeting_minutes' => 'Chapter Minutes',
    'hsc_meeting_minutes_json' => 'HSC Minutes',
    'house_visit_houses' => 'HSC House List',
    'house_visit_schedules' => 'HSC Schedules',
    'housing_service_representative_reports' => 'HSR Reports',
    'house_visit_reports' => 'House Visit Reports',
    'house_tour_forms' => 'House Tour Forms',
    'oxford_financial_audits' => 'Financial Audits',
    'oxford_house_financial_audits' => 'New Audit Forms',
    'oxford_house_financial_reports' => 'Financial Status Reports',
    'oxford_house_ledger_forms' => 'House Ledgers',
    'oxford_house_member_ledger' => 'EES Member Ledgers',
    'petty_cash_ledgers' => 'Petty Cash Ledgers',
    'oxford_member_financial_contracts' => 'Financial Contracts',
    'oxford_disruptive_contracts' => 'Behavior Contracts',
    'oxford_interview_minutes' => 'Interview Minutes',
    'oxford_new_member_packets' => 'New Member Packets',
    'oxford_red_creek_member_packets' => 'Newcomer Packets',
    'oxford_residency_forms' => 'Residency Forms',
    'oxford_chore_lists' => 'Chore Lists',
    'oxford_shopping_lists' => 'Shopping Lists',
    'safety_inspection_checklists' => 'Safety Checklists',
    'bedroom_essentials_checklists' => 'Bedroom Checklists',
];

$houses = oxford_safe_fetch_all(
    $masterPdo,
    'SELECT h.*, 
            (SELECT COUNT(*) FROM oxford_master_house_user_access hua WHERE hua.house_id = h.id) AS assigned_users
     FROM oxford_master_houses h
     ORDER BY h.house_name ASC'
);

$totalHouses = count($houses);
$activeUsers = oxford_safe_count($masterPdo, "SELECT COUNT(*) FROM oxford_master_users WHERE status = 'active'");
$totalAuditRows = oxford_safe_count($masterPdo, 'SELECT COUNT(*) FROM oxford_master_audit_log');
$totalActivityRows = oxford_safe_count($masterPdo, 'SELECT COUNT(*) FROM oxford_master_activity');

$moduleTotals = array_fill_keys(array_keys($knownTables), 0);
$houseBreakdown = [];

foreach ($houses as $house) {
    $db = (string)$house['database_name'];
    $tableCounts = [];
    $totalRows = 0;

    try {
        $tables = $oxfordServerPdo->query(
            "SELECT TABLE_NAME, TABLE_ROWS
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = " . $oxfordServerPdo->quote($db)
        )->fetchAll(PDO::FETCH_ASSOC);

        $tableRowsMap = [];
        foreach ($tables as $t) {
            $tableRowsMap[(string)$t['TABLE_NAME']] = (int)($t['TABLE_ROWS'] ?? 0);
        }

        foreach ($knownTables as $tableName => $label) {
            $count = $tableRowsMap[$tableName] ?? 0;
            $tableCounts[$tableName] = $count;
            $moduleTotals[$tableName] += $count;
            $totalRows += $count;
        }
    } catch (Throwable $e) {
        foreach ($knownTables as $tableName => $label) {
            $tableCounts[$tableName] = 0;
        }
    }

    $lastActivityStmt = $masterPdo->prepare('SELECT MAX(created_at) FROM oxford_master_activity WHERE house_id = ?');
    $lastActivityStmt->execute([(int)$house['id']]);
    $lastActivity = (string)($lastActivityStmt->fetchColumn() ?: '');

    $houseBreakdown[] = [
        'id' => (int)$house['id'],
        'house_name' => (string)$house['house_name'],
        'database_name' => $db,
        'assigned_users' => (int)$house['assigned_users'],
        'total_rows' => $totalRows,
        'last_activity' => $lastActivity,
        'table_counts' => $tableCounts,
    ];
}

$recentActivity = oxford_safe_fetch_all(
    $masterPdo,
    'SELECT a.*, h.house_name, u.full_name
     FROM oxford_master_activity a
     LEFT JOIN oxford_master_houses h ON h.id = a.house_id
     LEFT JOIN oxford_master_users u ON u.id = a.user_id
     ORDER BY a.created_at DESC
     LIMIT 20'
);

$recentAudit = oxford_safe_fetch_all(
    $masterPdo,
    'SELECT l.*, h.house_name, u.full_name
     FROM oxford_master_audit_log l
     LEFT JOIN oxford_master_houses h ON h.id = l.house_id
     LEFT JOIN oxford_master_users u ON u.id = l.user_id
     ORDER BY l.created_at DESC
     LIMIT 25'
);
?>
<style>
.dashboard-wrap{max-width:1500px;margin:0 auto;}
.dashboard-title{margin:0 0 8px;color:#17365d;}
.dashboard-sub{color:#647992;margin-bottom:20px;}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:18px;}
.stat-card,.panel,.panel2{background:#fff;border-radius:18px;box-shadow:0 10px 26px rgba(15,44,80,.08);}
.stat-card{padding:18px;}
.stat-label{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#6f8298;margin-bottom:8px;}
.stat-value{font-size:30px;font-weight:700;color:#17365d;}
.panel{padding:18px;margin-bottom:18px;width:fit-content; max-width:100%;}
.panel2{padding:18px;margin-bottom:18px; overflow:auto;}
.table-wrap{overflow:auto;}
table{width:100%;border-collapse:collapse;}
th,td{padding:11px 10px;border-bottom:1px solid #e7eef6;text-align:left;white-space:nowrap;vertical-align:top;}
th{font-size:12px;text-transform:uppercase;color:#6c7f96;letter-spacing:.06em;}
.badge{display:inline-block;padding:5px 9px;border-radius:999px;background:#e9f1fb;color:#17365d;font-size:12px;font-weight:700;}
.open-btn,.submit-btn,.danger-btn{display:inline-block;padding:10px 14px;border-radius:10px;color:#fff;text-decoration:none;font-weight:700;border:0;cursor:pointer;}
.open-btn,.submit-btn{background:#17365d;}
.open-btn:hover,.submit-btn:hover{background:#244d7d;}
.danger-btn{background:#a63333;}
.danger-btn:hover{background:#8f2b2b;}
.muted{color:#71859a;}
.split{display:grid;grid-template-columns:1.25fr .95fr;gap:18px;}
.triple{display:grid;grid-template-columns:1.1fr .9fr;gap:18px;margin-bottom:18px;}
.alert{padding:14px 16px;border-radius:14px;margin-bottom:16px;font-weight:700;}
.alert.success{background:#eef8ef;border:1px solid #cfe8d2;color:#27653a;}
.alert.error{background:#fff2f2;border:1px solid #efcccc;color:#a63333;}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.form-grid .full{grid-column:1/-1;}
label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6c7f96;margin-bottom:6px;}
input,select{width:100%;padding:12px 13px;border:1px solid #d9e4f1;border-radius:12px;font-size:14px;}
.cred-box{background:#f5f8fc;border:1px solid #dce7f3;border-radius:14px;padding:14px;margin-top:16px;line-height:1.7;}
.clear-log-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:12px;}
.note-box{background:#f8fafc;border:1px solid #dbe5f0;border-radius:14px;padding:14px;line-height:1.6;}
@media(max-width:1100px){
    .split,.triple,.form-grid,.clear-log-grid{grid-template-columns:1fr;}
}
</style>

<div class="dashboard-wrap">
    <h1 class="dashboard-title">Oxford Central Dashboard</h1>
    <div class="dashboard-sub">Cross-house reporting, central administration, permission-based access, and a full audit trail while keeping each house isolated inside its own database.</div>

    <?php if ($message !== ''): ?>
        <div class="alert <?= $messageType === 'error' ? 'error' : 'success' ?>"><?= oxford_h($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Active Houses</div><div class="stat-value"><?= (int)$totalHouses ?></div></div>
        <div class="stat-card"><div class="stat-label">Active Users</div><div class="stat-value"><?= (int)$activeUsers ?></div></div>
        <div class="stat-card"><div class="stat-label">Activity Events</div><div class="stat-value"><?= (int)$totalActivityRows ?></div></div>
        <div class="stat-card"><div class="stat-label">Audit Entries</div><div class="stat-value"><?= (int)$totalAuditRows ?></div></div>
    </div>

    <div class="triple">
        <div class="panel">
            <h2 style="margin-top:0;color:#17365d;">Create House</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_house">
                <div class="form-grid">
                    <div>
                        <label>House Name</label>
                        <input type="text" name="house_name" required>
                    </div>
                    <div>
                        <label>House Code</label>
                        <input type="text" name="house_code" placeholder="Optional auto-slug if blank">
                    </div>
                    <div>
                        <label>Database Name</label>
                        <input type="text" name="database_name" placeholder="Optional auto name if blank">
                    </div>
                    <div>
                        <label>Template Database</label>
                        <input disabled type="text" name="template_database" value="oxford_tables" placeholder="Database to copy tables from">
                    </div>
                    <div>
                        <label>Address</label>
                        <input type="text" name="city">
                    </div>
                    <div>
                        <label>City/State/Zip</label>
                        <input type="text" name="state">
                    </div>
                    <div>
                        <label>Initial Manager Name</label>
                        <input type="text" name="manager_name" required>
                    </div>
                    <div>
                        <label>Initial Login Email</label>
                        <input type="email" name="manager_email" required>
                    </div>
                    <div class="full">
                        <label>Initial Password</label>
                        <input type="text" name="manager_password" required>
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <button class="submit-btn" type="submit">Create House, Database, Tables, and Login</button>
                </div>
            </form>

            <?php if ($newHouseCredentials): ?>
                <div class="cred-box">
                    <strong>Initial login created</strong><br>
                    House: <?= oxford_h($newHouseCredentials['house_name']) ?><br>
                    House Code: <?= oxford_h($newHouseCredentials['house_code']) ?><br>
                    Database: <?= oxford_h($newHouseCredentials['database_name']) ?><br>
                    Email: <?= oxford_h($newHouseCredentials['manager_email']) ?><br>
                    Password: <?= oxford_h($newHouseCredentials['manager_password']) ?><br>
                    Template Source: <?= oxford_h($newHouseCredentials['template_database']) ?><br>
                    Tables Prepared: <?= (int)$newHouseCredentials['table_count'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h2 style="margin-top:0;color:#17365d;">Security & Access Snapshot</h2>
            <div class="table-wrap">
                <table>
                    <tbody>
                        <tr><th>Central Admin Access</th><td>All houses, house creation, user management, and reporting panels</td></tr>
                        <tr><th>House User Access</th><td>Only assigned houses</td></tr>
                        <tr><th>Panel Visibility</th><td>Central controls only render for central admins and super admins</td></tr>
                        <tr><th>Audit Coverage</th><td>Login, logout, house switches, page opens, POST form activity, and house creation</td></tr>
                        <tr><th>Isolation Model</th><td>Per-house database with a master central index</td></tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:18px;">
                <h2 style="margin:0 0 10px;color:#17365d;">Clear Logs</h2>
                <div class="note-box">
                    Use these controls to clear the central log tables. This permanently removes the selected log records.
                    <div class="clear-log-grid">
                        <form method="post" onsubmit="return confirm('Clear the activity log? This cannot be undone.');">
                            <input type="hidden" name="action" value="clear_logs">
                            <input type="hidden" name="log_type" value="activity">
                            <button class="danger-btn" type="submit">Clear Activity Log</button>
                        </form>

                        <form method="post" onsubmit="return confirm('Clear the audit log? This cannot be undone.');">
                            <input type="hidden" name="action" value="clear_logs">
                            <input type="hidden" name="log_type" value="audit">
                            <button class="danger-btn" type="submit">Clear Audit Log</button>
                        </form>

                        <form method="post" onsubmit="return confirm('Clear BOTH logs? This cannot be undone.');">
                            <input type="hidden" name="action" value="clear_logs">
                            <input type="hidden" name="log_type" value="all">
                            <button class="danger-btn" type="submit">Clear Both Logs</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="split">
        <div class="panel">
            <h2 style="margin-top:0;color:#17365d;">Cross-House Reporting Panels</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Module</th><th>Total Records Across Houses</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($knownTables as $tableName => $label): ?>
                        <tr>
                            <td><strong><?= oxford_h($label) ?></strong></td>
                            <td><?= (int)$moduleTotals[$tableName] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <h2 style="margin-top:0;color:#17365d;">Central Admin Notes</h2>
            <div class="table-wrap">
                <table>
                    <tbody>
                        <tr><th>When a house is created</th><td>The system registers the house, builds a dedicated database, clones the template table structure, and creates the initial core member login.</td></tr>
                        <tr><th>Template Source</th><td>Default template database is <strong>oxford_tables</strong>, which preserves the current setup and visual workflow.</td></tr>
                        <tr><th>Non-Central Users</th><td>They will not see the central dashboard links or management panels.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2 style="margin-top:0;color:#17365d;">House Rollup</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>House</th>
                    <th>Assigned Users</th>
                    <th>Total Known Form Rows</th>
                    <th>Financial Status</th>
                    <th>Audits</th>
                    <th>Ledgers</th>
                    <th>Contracts</th>
                    <th>Recent Activity</th>
                    <th>Open</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$houseBreakdown): ?>
                    <tr>
                        <td colspan="9" class="muted">No houses found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($houseBreakdown as $house): ?>
                        <tr>
                            <td><strong><?= oxford_h($house['house_name']) ?></strong></td>
                            <td><?= (int)$house['assigned_users'] ?></td>
                            <td><?= (int)$house['total_rows'] ?></td>
                            <td><?= (int)($house['table_counts']['oxford_house_financial_reports'] ?? 0) ?></td>
                            <td><?= (int)(($house['table_counts']['oxford_financial_audits'] ?? 0) + ($house['table_counts']['oxford_house_financial_audits'] ?? 0)) ?></td>
                            <td><?= (int)(($house['table_counts']['oxford_house_ledger_forms'] ?? 0) + ($house['table_counts']['oxford_house_member_ledger'] ?? 0) + ($house['table_counts']['petty_cash_ledgers'] ?? 0)) ?></td>
                            <td><?= (int)(($house['table_counts']['oxford_member_financial_contracts'] ?? 0) + ($house['table_counts']['oxford_disruptive_contracts'] ?? 0)) ?></td>
                            <td><?= $house['last_activity'] !== '' ? oxford_h($house['last_activity']) : '<span class="muted">No activity</span>' ?></td>
                            <td><a class="open-btn" href="index.php?house_id=<?= (int)$house['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="split">
        <div class="panel2">
            <h2 style="margin-top:0;color:#17365d;">Recent Central Activity</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Date</th><th>House</th><th>User</th><th>Page</th><th>Event</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!$recentActivity): ?>
                        <tr>
                            <td colspan="6" class="muted">No activity log entries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $row): ?>
                            <tr>
                                <td><?= oxford_h((string)$row['created_at']) ?></td>
                                <td><?= oxford_h((string)($row['house_name'] ?? 'System')) ?></td>
                                <td><?= oxford_h((string)($row['full_name'] ?? '')) ?></td>
                                <td><?= oxford_h(oxford_format_log_name((string)$row['page_name'])) ?></td>
                                <td><?= oxford_h(oxford_format_log_name((string)$row['event_name'])) ?></td>
                                <td class="muted"><?= oxford_h(oxford_format_log_details((string)($row['details_json'] ?? ''))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <br>

        <div class="panel2">
            <h2 style="margin-top:0;color:#17365d;">Recent Audit Log</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Date</th><th>House</th><th>User</th><th>Action</th><th>Page</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!$recentAudit): ?>
                        <tr>
                            <td colspan="6" class="muted">No audit log entries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentAudit as $row): ?>
                            <tr>
                                <td><?= oxford_h((string)$row['created_at']) ?></td>
                                <td><?= oxford_h((string)($row['house_name'] ?? 'System')) ?></td>
                                <td><?= oxford_h((string)($row['full_name'] ?? '')) ?></td>
                                <td><?= oxford_h(oxford_format_log_name((string)$row['action_name'])) ?></td>
                                <td><?= oxford_h(oxford_format_log_name((string)$row['page_name'])) ?></td>
                                <td class="muted"><?= oxford_h(oxford_format_log_details((string)($row['details_json'] ?? ''))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/extras/footer.php'; ?>