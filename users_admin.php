<?php
require_once __DIR__ . '/extras/master_config.php';
oxford_require_role(['central_admin', 'super_admin']);

$message = '';
$messageType = 'success';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'create_user') {
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role = (string)($_POST['role'] ?? 'house_user');
        $status = (string)($_POST['status'] ?? 'active');
        $houseIds = array_map('intval', $_POST['house_ids'] ?? []);

        if ($fullName === '' || $email === '' || $password === '') {
            $message = 'Name, email, and password are required.';
            $messageType = 'error';
        } else {
            $stmt = $masterPdo->prepare('
                INSERT INTO oxford_master_users (full_name, email, password_hash, role, status)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $fullName,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role,
                $status
            ]);

            $newUserId = (int)$masterPdo->lastInsertId();

            if (!empty($houseIds)) {
                $link = $masterPdo->prepare('
                    INSERT IGNORE INTO oxford_master_house_user_access (house_id, user_id, is_primary)
                    VALUES (?, ?, ?)
                ');
                foreach ($houseIds as $i => $houseId) {
                    $link->execute([$houseId, $newUserId, $i === 0 ? 1 : 0]);
                }
            }

            oxford_log_audit($masterPdo, [
                'house_id' => null,
                'user_id' => (int)$oxfordUser['id'],
                'action_name' => 'user_created',
                'page_name' => 'users_admin.php',
                'target_table' => 'oxford_master_users',
                'target_id' => (string)$newUserId,
                'details' => [
                    'email' => $email,
                    'role' => $role,
                    'house_ids' => $houseIds
                ],
            ]);

            $message = 'User created.';
        }
    }

    if ($action === 'update_access') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $role = (string)($_POST['role'] ?? 'house_user');
        $status = (string)($_POST['status'] ?? 'active');
        $houseIds = array_map('intval', $_POST['house_ids'] ?? []);

        if ($userId > 0) {
            $masterPdo->prepare('UPDATE oxford_master_users SET role = ?, status = ? WHERE id = ?')
                ->execute([$role, $status, $userId]);

            $masterPdo->prepare('DELETE FROM oxford_master_house_user_access WHERE user_id = ?')
                ->execute([$userId]);

            $link = $masterPdo->prepare('
                INSERT INTO oxford_master_house_user_access (house_id, user_id, is_primary)
                VALUES (?, ?, ?)
            ');

            foreach ($houseIds as $i => $houseId) {
                $link->execute([$houseId, $userId, $i === 0 ? 1 : 0]);
            }

            oxford_log_audit($masterPdo, [
                'user_id' => (int)$oxfordUser['id'],
                'action_name' => 'user_access_updated',
                'page_name' => 'users_admin.php',
                'target_table' => 'oxford_master_house_user_access',
                'target_id' => (string)$userId,
                'details' => [
                    'role' => $role,
                    'status' => $status,
                    'house_ids' => $houseIds
                ],
            ]);

            $message = 'User access updated.';
        }
    }
}

$houses = $masterPdo->query('
    SELECT *
    FROM oxford_master_houses
    WHERE is_active = 1
    ORDER BY house_name ASC
')->fetchAll();

$users = $masterPdo->query('
    SELECT *
    FROM oxford_master_users
    ORDER BY full_name ASC
')->fetchAll();

$userHouseMap = [];
$links = $masterPdo->query('
    SELECT *
    FROM oxford_master_house_user_access
    ORDER BY is_primary DESC, house_id ASC
')->fetchAll();

foreach ($links as $row) {
    $userHouseMap[(int)$row['user_id']][] = (int)$row['house_id'];
}

$houseNameMap = [];
foreach ($houses as $house) {
    $houseNameMap[(int)$house['id']] = (string)$house['house_name'];
}

include __DIR__ . '/extras/header.php';
?>

<style>
    .wrap{
        max-width:1440px;
        margin:0 auto;
    }

    .panel{
        background:#fff;
        border-radius:18px;
        box-shadow:0 10px 26px rgba(15,44,80,.08);
        padding:20px;
        margin-bottom:18px;
    }

    .grid{
        display:grid;
        grid-template-columns:1fr 1.25fr;
        gap:18px;
    }

    @media(max-width:1150px){
        .grid{
            grid-template-columns:1fr;
        }
    }

    label{
        display:block;
        font-weight:700;
        margin-bottom:6px;
        color:#17365d;
    }

    input[type="text"],
    input[type="email"],
    select{
        width:100%;
        padding:11px 12px;
        border:1px solid #cfdceb;
        border-radius:12px;
        margin-bottom:12px;
        background:#fff;
        box-sizing:border-box;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    select:focus{
        outline:none;
        border-color:#17365d;
        box-shadow:0 0 0 3px rgba(23,54,93,.10);
    }

    table{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
    }

    th, td{
        padding:10px;
        border-bottom:1px solid #e7eef6;
        text-align:left;
        vertical-align:top;
    }

    th{
        font-size:12px;
        text-transform:uppercase;
        color:#70839a;
        letter-spacing:.04em;
    }

    .msg{
        padding:12px 14px;
        border-radius:12px;
        margin-bottom:16px;
    }

    .success{
        background:#edf8ef;
        color:#186530;
    }

    .error{
        background:#fff2f2;
        color:#a12a2a;
    }

    button{
        border:0;
        background:#17365d;
        color:#fff;
        border-radius:12px;
        padding:10px 14px;
        font-weight:700;
        cursor:pointer;
        transition:.18s ease;
    }

    button:hover{
        background:#224a7c;
    }

    .muted-note{
        color:#667b92;
    }

    .assigned-houses{
        color:#5e7188;
        font-size:13px;
        line-height:1.45;
        word-break:break-word;
    }

    .assigned-houses span{
        display:inline-block;
        margin:0 6px 6px 0;
        padding:5px 9px;
        border-radius:999px;
        background:#eef4fa;
        color:#17365d;
        font-size:12px;
        font-weight:600;
    }

    .user-card-form{
        width:100%;
        min-width:0;
    }

    .user-form-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
        margin-bottom:10px;
    }

    @media(max-width:900px){
        .user-form-grid{
            grid-template-columns:1fr;
        }
    }

    .houses-list{
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:10px;
        margin-bottom:12px;
    }

    @media(max-width:1300px){
        .houses-list{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width:700px){
        .houses-list{
            grid-template-columns:1fr;
        }
    }

    .house-option{
        position:relative;
        display:flex;
        align-items:center;
        gap:8px;
        min-height:38px;
        padding:8px 10px;
        margin:0;
        border:1px solid #d7e3f0;
        border-radius:10px;
        background:#fbfdff;
        cursor:pointer;
        transition:.16s ease;
        box-sizing:border-box;
        overflow:hidden;
    }

    .house-option:hover{
        border-color:#9fb6d2;
        box-shadow:0 4px 12px rgba(20,53,90,.06);
    }

    .house-option input[type="checkbox"]{
        appearance:none;
        -webkit-appearance:none;
        width:16px;
        height:16px;
        min-width:16px;
        border:2px solid #9fb3c9;
        border-radius:4px;
        background:#fff;
        display:inline-grid;
        place-content:center;
        margin:0;
        cursor:pointer;
        transition:.14s ease;
    }

    .house-option input[type="checkbox"]::before{
        content:"";
        width:8px;
        height:8px;
        transform:scale(0);
        transition:transform .12s ease-in-out;
        clip-path:polygon(14% 44%, 0 59%, 42% 100%, 100% 16%, 84% 0, 42% 62%);
        background:#fff;
    }

    .house-option input[type="checkbox"]:checked{
        background:#17365d;
        border-color:#17365d;
    }

    .house-option input[type="checkbox"]:checked::before{
        transform:scale(1);
    }

    .house-option span{
        display:block;
        font-size:13px;
        font-weight:600;
        color:#17365d;
        line-height:1.2;
        word-break:break-word;
    }

    .current-users table{
        table-layout:auto;
    }

    .current-users .update-cell{
        width:100%;
        min-width:0;
    }

    .current-users .user-card-form{
        display:block;
        width:100%;
    }

    .current-users .houses-editor{
        margin-top:10px;
        padding:12px;
        border:1px solid #e3ebf5;
        border-radius:14px;
        background:#f8fbff;
    }

    .current-users .houses-editor-title{
        display:block;
        margin:0 0 10px;
        color:#17365d;
        font-size:13px;
        font-weight:700;
    }

    .current-users .houses-list{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        width:100%;
        margin-bottom:12px;
    }

    .current-users .house-option{
        flex:1 1 190px;
        max-width:none;
        min-width:170px;
    }

    @media(max-width:900px){
        .current-users .house-option{
            flex:1 1 100%;
            min-width:100%;
        }
    }

    .save-row{
        display:flex;
        justify-content:flex-start;
        align-items:center;
        margin-top:6px;
    }
</style>

<div class="wrap">
    <div class="panel">
        <h1 style="margin:0 0 8px;color:#17365d;">User &amp; Access Manager</h1>
        <div class="muted-note">Create house users, central admins, assign houses, and control permissions from one page.</div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="msg <?= $messageType === 'error' ? 'error' : 'success' ?>">
            <?= oxford_h($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div class="panel">
            <h2 style="margin-top:0;color:#17365d;">Create User</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_user">

                <label>Full Name</label>
                <input type="text" name="full_name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="text" name="password" required>

                <label>Role</label>
                <select name="role">
                    <option value="house_user">House User</option>
                    <option value="house_manager">Core Member</option>
                    <option value="central_admin">Central Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>

                <label>Status</label>
                <select name="status">
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                </select>

                <label>Accessible Houses</label>
                <div class="houses-list">
                    <?php foreach ($houses as $house): ?>
                        <label class="house-option">
                            <input type="checkbox" name="house_ids[]" value="<?= (int)$house['id'] ?>">
                            <span><?= oxford_h($house['house_name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit">Create User</button>
            </form>
        </div>

        <div class="panel current-users">
            <h2 style="margin-top:0;color:#17365d;">Current Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Assigned Houses</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $assigned = $userHouseMap[(int)$user['id']] ?? [];
                        $assignedNames = [];

                        foreach ($assigned as $assignedId) {
                            if (isset($houseNameMap[$assignedId])) {
                                $assignedNames[] = $houseNameMap[$assignedId];
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?= oxford_h($user['full_name']) ?></strong><br>
                                <span style="color:#678;font-size:13px;"><?= oxford_h($user['email']) ?></span>
                            </td>
                            <td><?= oxford_h(oxford_get_role_label((string)$user['role'])) ?></td>
                            <td><?= oxford_h((string)$user['status']) ?></td>
                            <td class="assigned-houses">
                                <?php if (!empty($assignedNames)): ?>
                                    <?php foreach ($assignedNames as $assignedName): ?>
                                        <span><?= oxford_h($assignedName) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="background:#f3f6fa;color:#90a0b2;">No houses assigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="update-cell">
                                <form method="post" class="user-card-form">
                                    <input type="hidden" name="action" value="update_access">
                                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">

                                    <div class="user-form-grid">
                                        <div>
                                            <label>Role</label>
                                            <select name="role">
                                                <?php foreach (['house_user', 'house_manager', 'central_admin', 'super_admin'] as $role): ?>
                                                    <option value="<?= oxford_h($role) ?>" <?= (string)$user['role'] === (string)$role ? 'selected' : '' ?>>
                                                        <?= oxford_h(oxford_get_role_label((string)$role)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div>
                                            <label>Status</label>
                                            <select name="status">
                                                <option value="active" <?= (string)$user['status'] === 'active' ? 'selected' : '' ?>>active</option>
                                                <option value="inactive" <?= (string)$user['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="houses-editor">
                                        <span class="houses-editor-title">Accessible Houses</span>
                                        <div class="houses-list">
                                            <?php foreach ($houses as $house): ?>
                                                <label class="house-option">
                                                    <input
                                                        type="checkbox"
                                                        name="house_ids[]"
                                                        value="<?= (int)$house['id'] ?>"
                                                        <?= in_array((int)$house['id'], $assigned, true) ? 'checked' : '' ?>
                                                    >
                                                    <span><?= oxford_h($house['house_name']) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="save-row">
                                            <button type="submit">Save Access</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/extras/footer.php'; ?>