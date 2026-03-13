<?php
require_once __DIR__ . '/extras/master_config.php';

if (!empty($_SESSION['oxford_auth_user'])) {
    oxford_redirect('index.php');
}

$message = '';
$redirectTarget = trim((string)($_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php'));
if ($redirectTarget === '' || str_contains($redirectTarget, 'login.php')) {
    $redirectTarget = 'index.php';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $masterPdo->prepare('SELECT * FROM oxford_master_users WHERE email = ? AND status = ? LIMIT 1');
    $stmt->execute([$email, 'active']);
    $user = $stmt->fetch();

    if ($user && password_verify($password, (string)$user['password_hash'])) {
        $accessStmt = $masterPdo->prepare('SELECT house_id FROM oxford_master_house_user_access WHERE user_id = ? ORDER BY is_primary DESC, house_id ASC');
        $accessStmt->execute([(int)$user['id']]);
        $houseIds = array_map('intval', array_column($accessStmt->fetchAll(), 'house_id'));

        $_SESSION['oxford_auth_user'] = [
            'id' => (int)$user['id'],
            'full_name' => (string)$user['full_name'],
            'email' => (string)$user['email'],
            'role' => (string)$user['role'],
            'assigned_house_ids' => $houseIds,
        ];

        if (!empty($houseIds)) {
            $_SESSION['oxford_house_id'] = (int)$houseIds[0];
        }

        $masterPdo->prepare('UPDATE oxford_master_users SET last_login_at = NOW() WHERE id = ?')->execute([(int)$user['id']]);
        oxford_log_activity($masterPdo, !empty($houseIds) ? (int)$houseIds[0] : null, 'login.php', 'login_success', ['email' => $email], (int)$user['id']);
        oxford_log_audit($masterPdo, [
            'house_id' => !empty($houseIds) ? (int)$houseIds[0] : null,
            'user_id' => (int)$user['id'],
            'action_name' => 'login_success',
            'page_name' => 'login.php',
            'details' => ['email' => $email],
        ]);

        header('Location: ' . oxford_url($redirectTarget));
        exit;
    }

    $message = 'Invalid email or password.';
    oxford_log_audit($masterPdo, [
        'action_name' => 'login_failed',
        'page_name' => 'login.php',
        'details' => ['email' => $email],
    ]);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Oxford House Login</title>
<style>
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(135deg,#17365d,#244d7d);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;color:#17365d;}
.card{width:100%;max-width:460px;background:#fff;border-radius:22px;box-shadow:0 22px 60px rgba(0,0,0,.22);padding:28px;}
.logo{text-align:center;margin-bottom:10px;}
.logo img{width:120px;background:#fff;padding:8px;border-radius:12px;}
h1{text-align:center;margin:8px 0 6px;color:#17365d;}
.sub{text-align:center;color:#61758d;font-size:14px;margin-bottom:22px;}
label{display:block;font-weight:700;margin-bottom:6px;color:#17365d;}
input{width:100%;padding:12px 14px;border:1px solid #ccd8e7;border-radius:12px;font-size:15px;margin-bottom:16px;box-sizing:border-box;}
button{width:100%;border:0;background:#17365d;color:#fff;font-weight:700;font-size:15px;padding:14px;border-radius:12px;cursor:pointer;}
button:hover{background:#244d7d;}
.alert{background:#fff2f2;color:#a12a2a;border:1px solid #f1c7c7;padding:12px 14px;border-radius:12px;margin-bottom:16px;}
.demo{margin-top:18px;background:#f5f8fc;border:1px solid #dde7f3;border-radius:14px;padding:14px;}
.demo b{color:#17365d;}
.small{font-size:13px;color:#61758d;line-height:1.55;}
</style>
</head>
<body>
    <div class="card">
        <div class="logo"><img src="images/oxford_house_logo.png" alt="Oxford House Logo"></div>
        <h1>Oxford House Central System</h1>
        <div class="sub">Same visual system, now with central authentication, house access control, and cross-house reporting.</div>

        <?php if ($message !== ''): ?>
            <div class="alert"><?= oxford_h($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="redirect" value="<?= oxford_h($redirectTarget) ?>">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Sign In</button>
        </form>

        <div class="demo small">
            <label><b>Default Login Credentials for New Houses:</b></label>
            <div><b>House Login Email:</b> [housename]@oxfordhouse.us<code><br>Example: oakland@oxfordhouse.us</code><hr></div>
            <div><b>House Login Password:</b> Recovery[housenumber]!<code><br>Example: Recovery123!</code><hr></div>
            <label><b>Contact your Core Member if you need assistance with logging in.</b></label>
        </div>
    </div>
</body>
</html>
