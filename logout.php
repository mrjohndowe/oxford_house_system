<?php
require_once __DIR__ . '/extras/master_config.php';
$user = $_SESSION['oxford_auth_user'] ?? null;
if ($user) {
    oxford_log_activity($masterPdo, (int)($_SESSION['oxford_house_id'] ?? 0) ?: null, 'logout.php', 'logout', null, (int)$user['id']);
    oxford_log_audit($masterPdo, [
        'house_id' => (int)($_SESSION['oxford_house_id'] ?? 0) ?: null,
        'user_id' => (int)$user['id'],
        'action_name' => 'logout',
        'page_name' => 'logout.php',
    ]);
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: ' . oxford_url('login.php'));
exit;
