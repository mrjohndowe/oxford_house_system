<?php
require_once __DIR__ . '/extras/master_config.php';

$message = '';
$messageType = 'success';
$canManageContractPassword = in_array($oxfordUser['role'] ?? '', ['house_manager', 'central_admin', 'super_admin'], true);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'change_password') {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $stmt = $masterPdo->prepare('SELECT password_hash FROM oxford_master_users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$oxfordUser['id']]);
        $existingHash = (string)($stmt->fetchColumn() ?: '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $message = 'All password fields are required.';
            $messageType = 'error';
        } elseif (!password_verify($currentPassword, $existingHash)) {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 8) {
            $message = 'New password must be at least 8 characters.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New password and confirm password must match.';
            $messageType = 'error';
        } else {
            $masterPdo->prepare('UPDATE oxford_master_users SET password_hash = ? WHERE id = ?')->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                (int)$oxfordUser['id'],
            ]);

            oxford_log_audit($masterPdo, [
                'house_id' => $currentHouseId,
                'user_id' => (int)$oxfordUser['id'],
                'action_name' => 'password_changed',
                'page_name' => 'security.php',
                'target_table' => 'oxford_master_users',
                'target_id' => (string)$oxfordUser['id'],
                'details' => ['account' => $oxfordUser['email']],
            ]);
            oxford_log_activity($masterPdo, $currentHouseId, 'security.php', 'password_changed', ['account' => $oxfordUser['email']], (int)$oxfordUser['id']);
            $message = 'Your password was updated successfully.';
        }
    }

    if ($action === 'change_contract_password' && $canManageContractPassword) {
        $currentStampPassword = (string)($_POST['current_contract_password'] ?? '');
        $newStampPassword = (string)($_POST['new_contract_password'] ?? '');
        $confirmStampPassword = (string)($_POST['confirm_contract_password'] ?? '');

        if ($currentStampPassword === '' || $newStampPassword === '' || $confirmStampPassword === '') {
            $message = 'All contract password fields are required.';
            $messageType = 'error';
        } elseif (!oxford_verify_contract_stamp_password($masterPdo, (int)$currentHouseId, $currentStampPassword)) {
            $message = 'Current contract password is incorrect.';
            $messageType = 'error';
        } elseif (strlen($newStampPassword) < 8) {
            $message = 'New contract password must be at least 8 characters.';
            $messageType = 'error';
        } elseif ($newStampPassword !== $confirmStampPassword) {
            $message = 'New contract password and confirm contract password must match.';
            $messageType = 'error';
        } else {
            oxford_set_contract_stamp_password($masterPdo, (int)$currentHouseId, $newStampPassword, (int)$oxfordUser['id']);
            oxford_log_audit($masterPdo, [
                'house_id' => $currentHouseId,
                'user_id' => (int)$oxfordUser['id'],
                'action_name' => 'contract_password_changed',
                'page_name' => 'security.php',
                'target_table' => 'oxford_master_house_settings',
                'target_id' => (string)$currentHouseId,
                'details' => ['house' => $currentHouseName],
            ]);
            oxford_log_activity($masterPdo, $currentHouseId, 'security.php', 'contract_password_changed', ['house' => $currentHouseName], (int)$oxfordUser['id']);
            $message = 'The contract password was updated successfully.';
        }
    }
}

include __DIR__ . '/extras/header.php';
?>
<style>
.wrap{max-width:1100px;margin:0 auto;}.hero,.panel{background:#fff;border-radius:18px;box-shadow:0 10px 26px rgba(15,44,80,.08);padding:18px;margin-bottom:18px;}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}@media(max-width:980px){.grid{grid-template-columns:1fr;}}label{display:block;font-weight:700;margin-bottom:6px;color:#17365d;}input{width:100%;padding:12px 13px;border:1px solid #d6e2ef;border-radius:12px;margin-bottom:12px;font-size:14px;}button{border:0;background:#17365d;color:#fff;border-radius:12px;padding:12px 16px;font-weight:700;cursor:pointer;}button:hover{background:#244d7d;}.msg{padding:12px 14px;border-radius:12px;margin-bottom:16px;font-weight:700;}.success{background:#edf8ef;color:#186530;}.error{background:#fff2f2;color:#a12a2a;}.muted{color:#6d8096;line-height:1.6;}
</style>
<div class="wrap">
    <div class="hero">
        <h1 style="margin:0 0 8px;color:#17365d;">Security</h1>
        <div class="muted">Manage your sign-in password and, when allowed by your role, update the contract password for the currently selected house.</div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="msg <?= $messageType === 'error' ? 'error' : 'success' ?>"><?= oxford_h($message) ?></div>
    <?php endif; ?>

    <div class="grid">
        <div class="panel">
            <h2 style="margin-top:0;color:#17365d;">Change My Password</h2>
            <form method="post">
                <input type="hidden" name="action" value="change_password">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
                <label>New Password</label>
                <input type="password" name="new_password" required>
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
                <button type="submit">Update My Password</button>
            </form>
        </div>

        <?php if ($canManageContractPassword): ?>
            <div class="panel">
                <h2 style="margin-top:0;color:#17365d;">Change Contract Password</h2>
                <div class="muted" style="margin-bottom:14px;">Current house: <strong><?= oxford_h($currentHouseLabel ?: $currentHouseName) ?></strong></div>
                <form method="post">
                    <input type="hidden" name="action" value="change_contract_password">
                    <label>Current Contract Password</label>
                    <input type="password" name="current_contract_password" required>
                    <label>New Contract Password</label>
                    <input type="password" name="new_contract_password" required>
                    <label>Confirm New Contract Password</label>
                    <input type="password" name="confirm_contract_password" required>
                    <button type="submit">Update Contract Password</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/extras/footer.php'; ?>
