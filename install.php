<?php
declare(strict_types=1);

session_start();

const INSTALL_LOCK_FILE = __DIR__ . '/extras/install.lock';
const MASTER_CONFIG_FILE = __DIR__ . '/extras/master_config.php';
const SQLITE_DEFAULT_RELATIVE = 'extras/data/oxford.db';

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function post(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function installerIsLocked(): bool
{
    return file_exists(INSTALL_LOCK_FILE);
}

function writeInstallLock(array $data): void
{
    ensureDirectory(dirname(INSTALL_LOCK_FILE));
    file_put_contents(
        INSTALL_LOCK_FILE,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

function detectDatabaseSupport(): array
{
    $drivers = class_exists(PDO::class) ? PDO::getAvailableDrivers() : [];

    return [
        'pdo' => class_exists(PDO::class),
        'mysql' => in_array('mysql', $drivers, true),
        'sqlite' => in_array('sqlite', $drivers, true),
        'drivers' => $drivers,
    ];
}

function getPreferredInstallType(array $support): string
{
    if ($support['mysql']) {
        return 'mysql';
    }
    if ($support['sqlite']) {
        return 'sqlite';
    }
    return '';
}

function normalizeBaseUrl(string $url): string
{
    $url = trim($url);
    return $url === '' ? '' : rtrim($url, '/');
}

function absolutePathFromInput(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return __DIR__ . '/' . SQLITE_DEFAULT_RELATIVE;
    }

    if (
        str_starts_with($path, '/') ||
        preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1
    ) {
        return $path;
    }

    return __DIR__ . '/' . ltrim($path, '/\\');
}

function validateInstallInput(array $data, array $support): array
{
    $errors = [];

    if (!$support['pdo']) {
        $errors[] = 'PDO is not available on this server.';
        return $errors;
    }

    if (!$support['mysql'] && !$support['sqlite']) {
        $errors[] = 'No supported database driver found. Enable pdo_mysql or pdo_sqlite.';
        return $errors;
    }

    if (!in_array($data['install_type'], ['mysql', 'sqlite'], true)) {
        $errors[] = 'Please choose a valid database type.';
    }

    if ($data['install_type'] === 'mysql' && !$support['mysql']) {
        $errors[] = 'MySQL support is not enabled on this server.';
    }

    if ($data['install_type'] === 'sqlite' && !$support['sqlite']) {
        $errors[] = 'SQLite support is not enabled on this server.';
    }

    if ($data['app_name'] === '') {
        $errors[] = 'System Name is required.';
    }

    if ($data['admin_name'] === '') {
        $errors[] = 'Super Admin Full Name is required.';
    }

    if ($data['admin_email'] === '' || !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid Super Admin Email is required.';
    }

    if ($data['admin_password'] === '') {
        $errors[] = 'Super Admin Password is required.';
    } elseif (strlen($data['admin_password']) < 8) {
        $errors[] = 'Super Admin Password must be at least 8 characters.';
    }

    if ($data['contract_password'] === '') {
        $errors[] = 'Contract Password is required.';
    }

    if ($data['install_type'] === 'mysql') {
        if ($data['db_host'] === '') {
            $errors[] = 'MySQL Host is required.';
        }
        if ($data['db_name'] === '') {
            $errors[] = 'MySQL Database Name is required.';
        }
        if ($data['db_user'] === '') {
            $errors[] = 'MySQL Username is required.';
        }
    }

    if ($data['install_type'] === 'sqlite') {
        $sqlitePath = absolutePathFromInput($data['sqlite_path']);
        $dir = dirname($sqlitePath);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $errors[] = 'SQLite directory could not be created: ' . $dir;
        }
    }

    $extrasDir = __DIR__ . '/extras';
    if (!is_dir($extrasDir) && !@mkdir($extrasDir, 0775, true) && !is_dir($extrasDir)) {
        $errors[] = 'The extras directory could not be created.';
    }

    return $errors;
}

function createMySqlDatabaseIfMissing(string $host, string $dbName, string $user, string $pass): void
{
    $pdo = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $safeDbName = str_replace('`', '``', $dbName);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}

function connectToMySql(string $host, string $dbName, string $user, string $pass): PDO
{
    return new PDO(
        "mysql:host={$host};dbname={$dbName};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function connectToSqlite(string $path): PDO
{
    ensureDirectory(dirname($path));

    $pdo = new PDO(
        'sqlite:' . $path,
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

function tableExists(PDO $pdo, string $table): bool
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }

    if ($driver === 'sqlite') {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }

    return false;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
        foreach ($stmt->fetchAll() as $row) {
            if ((string)$row['Field'] === $column) {
                return true;
            }
        }
        return false;
    }

    if ($driver === 'sqlite') {
        $stmt = $pdo->query("PRAGMA table_info({$table})");
        foreach ($stmt->fetchAll() as $row) {
            if ((string)$row['name'] === $column) {
                return true;
            }
        }
        return false;
    }

    return false;
}

function createSchema(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $isSqlite = $driver === 'sqlite';

    $auto = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
    $bigAuto = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
    $bool = $isSqlite ? 'INTEGER NOT NULL DEFAULT 0' : 'TINYINT(1) NOT NULL DEFAULT 0';
    $dt = $isSqlite ? 'TEXT' : 'DATETIME';
    $longText = $isSqlite ? 'TEXT' : 'LONGTEXT';
    $intType = $isSqlite ? 'INTEGER' : 'INT UNSIGNED';

    $queries = [];

    $queries[] = "
        CREATE TABLE IF NOT EXISTS oxford_master_users (
            id {$auto},
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'house_user',
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            last_login_at {$dt} NULL,
            created_at {$dt} NOT NULL,
            updated_at {$dt} NOT NULL
        )
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS oxford_master_houses (
            id {$auto},
            house_name VARCHAR(255) NOT NULL,
            house_code VARCHAR(100) NULL,
            city VARCHAR(150) NULL,
            state VARCHAR(100) NULL,
            is_active {$bool},
            created_at {$dt} NOT NULL,
            updated_at {$dt} NOT NULL
        )
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS oxford_master_house_user_access (
            id {$auto},
            house_id {$intType} NOT NULL,
            user_id {$intType} NOT NULL,
            is_primary {$bool},
            created_at {$dt} NOT NULL
        )
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS oxford_audit_logs (
            id {$bigAuto},
            house_id {$intType} NULL,
            user_id {$intType} NULL,
            action_name VARCHAR(100) NOT NULL,
            page_name VARCHAR(255) NULL,
            target_table VARCHAR(255) NULL,
            target_id VARCHAR(255) NULL,
            details {$longText} NULL,
            created_at {$dt} NOT NULL
        )
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS oxford_master_settings (
            id {$auto},
            setting_key VARCHAR(150) NOT NULL UNIQUE,
            setting_value {$longText} NULL,
            updated_at {$dt} NOT NULL,
            updated_by_user_id {$intType} NULL
        )
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS oxford_contracts (
            id {$auto},
            house_id {$intType} NULL,
            member_name VARCHAR(255) NULL,
            contract_date DATE NULL,
            file_path VARCHAR(500) NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            contract_stamp VARCHAR(50) NULL,
            stamped_by_user_id {$intType} NULL,
            stamped_at {$dt} NULL,
            notes {$longText} NULL,
            created_at {$dt} NOT NULL,
            updated_at {$dt} NOT NULL
        )
    ";

    foreach ($queries as $sql) {
        $pdo->exec($sql);
    }

    createIndexes($pdo);
    runCompatibilityMigrations($pdo);
}

function createIndexes(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    $indexes = [
        ['oxford_master_users', 'idx_omu_role', 'role'],
        ['oxford_master_users', 'idx_omu_status', 'status'],
        ['oxford_master_houses', 'idx_omh_name', 'house_name'],
        ['oxford_master_houses', 'idx_omh_active', 'is_active'],
        ['oxford_master_house_user_access', 'idx_omhua_house', 'house_id'],
        ['oxford_master_house_user_access', 'idx_omhua_user', 'user_id'],
        ['oxford_audit_logs', 'idx_oal_user', 'user_id'],
        ['oxford_audit_logs', 'idx_oal_house', 'house_id'],
        ['oxford_audit_logs', 'idx_oal_action', 'action_name'],
        ['oxford_audit_logs', 'idx_oal_created', 'created_at'],
        ['oxford_contracts', 'idx_oc_house', 'house_id'],
        ['oxford_contracts', 'idx_oc_status', 'status'],
        ['oxford_contracts', 'idx_oc_member', 'member_name'],
    ];

    foreach ($indexes as [$table, $indexName, $column]) {
        try {
            $pdo->exec("CREATE INDEX {$indexName} ON {$table} ({$column})");
        } catch (Throwable $e) {
        }
    }

    try {
        if ($driver === 'sqlite') {
            $pdo->exec('CREATE UNIQUE INDEX idx_omhua_house_user_unique ON oxford_master_house_user_access (house_id, user_id)');
        } else {
            $pdo->exec('ALTER TABLE oxford_master_house_user_access ADD UNIQUE KEY uniq_house_user (house_id, user_id)');
        }
    } catch (Throwable $e) {
    }
}

function runCompatibilityMigrations(PDO $pdo): void
{
    if (tableExists($pdo, 'oxford_contracts') && !columnExists($pdo, 'oxford_contracts', 'contract_stamp')) {
        try {
            $pdo->exec('ALTER TABLE oxford_contracts ADD COLUMN contract_stamp VARCHAR(50) NULL');
        } catch (Throwable $e) {
        }
    }
}

function settingExists(PDO $pdo, string $key): bool
{
    $stmt = $pdo->prepare('SELECT id FROM oxford_master_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    return (bool)$stmt->fetchColumn();
}

function setSetting(PDO $pdo, string $key, string $value, ?int $userId = null): void
{
    $now = date('Y-m-d H:i:s');

    if (settingExists($pdo, $key)) {
        $stmt = $pdo->prepare('
            UPDATE oxford_master_settings
            SET setting_value = ?, updated_at = ?, updated_by_user_id = ?
            WHERE setting_key = ?
        ');
        $stmt->execute([$value, $now, $userId, $key]);
        return;
    }

    $stmt = $pdo->prepare('
        INSERT INTO oxford_master_settings (setting_key, setting_value, updated_at, updated_by_user_id)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$key, $value, $now, $userId]);
}

function userExistsByEmail(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare('SELECT id FROM oxford_master_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return (bool)$stmt->fetchColumn();
}

function createInitialSuperAdmin(PDO $pdo, string $name, string $email, string $password): int
{
    if (userExistsByEmail($pdo, $email)) {
        $stmt = $pdo->prepare('SELECT id FROM oxford_master_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn();
    }

    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare('
        INSERT INTO oxford_master_users (full_name, email, password_hash, role, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        'super_admin',
        'active',
        $now,
        $now,
    ]);

    return (int)$pdo->lastInsertId();
}

function logInstallEvent(PDO $pdo, int $userId, string $action, array $details = []): void
{
    $stmt = $pdo->prepare('
        INSERT INTO oxford_audit_logs (house_id, user_id, action_name, page_name, target_table, target_id, details, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        null,
        $userId,
        $action,
        'install.php',
        'system',
        'initial_install',
        json_encode($details, JSON_UNESCAPED_SLASHES),
        date('Y-m-d H:i:s'),
    ]);
}

function backupExistingMasterConfig(): ?string
{
    if (!file_exists(MASTER_CONFIG_FILE)) {
        return null;
    }

    $backupPath = MASTER_CONFIG_FILE . '.bak.' . date('Ymd_His');
    if (@copy(MASTER_CONFIG_FILE, $backupPath)) {
        return $backupPath;
    }

    return null;
}

function buildHelperBlock(): string
{
    return <<<'PHP'

/* ===== Oxford installer managed helpers start ===== */
if (!function_exists('oxford_h')) {
    function oxford_h(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('oxford_now')) {
    function oxford_now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('oxford_json_decode_array')) {
    function oxford_json_decode_array(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('oxford_get_pdo')) {
    function oxford_get_pdo(): PDO
    {
        static $pdo = null;

        global $dbType, $dbHost, $dbName, $dbUser, $dbPass, $sqlitePath;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        if (($dbType ?? 'mysql') === 'sqlite') {
            $dir = dirname($sqlitePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $pdo = new PDO(
                'sqlite:' . $sqlitePath,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            $pdo->exec('PRAGMA foreign_keys = ON');
            return $pdo;
        }

        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return $pdo;
    }
}

if (!isset($masterPdo) || !($masterPdo instanceof PDO)) {
    $masterPdo = oxford_get_pdo();
}

if (!function_exists('oxford_get_setting')) {
    function oxford_get_setting(PDO $pdo, string $key, mixed $default = null): mixed
    {
        $stmt = $pdo->prepare('SELECT setting_value FROM oxford_master_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        return $value !== false ? $value : $default;
    }
}

if (!function_exists('oxford_set_setting')) {
    function oxford_set_setting(PDO $pdo, string $key, string $value, ?int $userId = null): void
    {
        $stmt = $pdo->prepare('SELECT id FROM oxford_master_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $exists = (bool)$stmt->fetchColumn();

        if ($exists) {
            $update = $pdo->prepare('
                UPDATE oxford_master_settings
                SET setting_value = ?, updated_at = ?, updated_by_user_id = ?
                WHERE setting_key = ?
            ');
            $update->execute([$value, oxford_now(), $userId, $key]);
            return;
        }

        $insert = $pdo->prepare('
            INSERT INTO oxford_master_settings (setting_key, setting_value, updated_at, updated_by_user_id)
            VALUES (?, ?, ?, ?)
        ');
        $insert->execute([$key, $value, oxford_now(), $userId]);
    }
}

if (!function_exists('oxford_get_role_label')) {
    function oxford_get_role_label(string $role): string
    {
        return match ($role) {
            'house_user' => 'House User',
            'house_manager' => 'Core Member',
            'central_admin' => 'Central Admin',
            'super_admin' => 'Super Admin',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }
}

if (!function_exists('oxford_get_current_user')) {
    function oxford_get_current_user(PDO $pdo): ?array
    {
        $userId = (int)($_SESSION['oxford_user_id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM oxford_master_users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return is_array($user) ? $user : null;
    }
}

$oxfordUser = oxford_get_current_user($masterPdo);

if (!function_exists('oxford_require_login')) {
    function oxford_require_login(): void
    {
        global $oxfordUser;

        if (!$oxfordUser) {
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('oxford_require_role')) {
    function oxford_require_role(array $allowedRoles): void
    {
        global $oxfordUser;

        if (!$oxfordUser) {
            header('Location: login.php');
            exit;
        }

        if (!in_array((string)$oxfordUser['role'], $allowedRoles, true)) {
            http_response_code(403);
            exit('Access denied.');
        }
    }
}

if (!function_exists('oxford_log_audit')) {
    function oxford_log_audit(PDO $pdo, array $data): void
    {
        $stmt = $pdo->prepare('
            INSERT INTO oxford_audit_logs (house_id, user_id, action_name, page_name, target_table, target_id, details, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $details = $data['details'] ?? null;
        if (is_array($details)) {
            $details = json_encode($details, JSON_UNESCAPED_SLASHES);
        }

        $stmt->execute([
            $data['house_id'] ?? null,
            $data['user_id'] ?? null,
            $data['action_name'] ?? '',
            $data['page_name'] ?? null,
            $data['target_table'] ?? null,
            $data['target_id'] ?? null,
            $details,
            oxford_now(),
        ]);
    }
}

if (!function_exists('oxford_verify_contract_password')) {
    function oxford_verify_contract_password(PDO $pdo, string $password): bool
    {
        $hash = (string)oxford_get_setting($pdo, 'contract_password_hash', '');
        if ($hash === '') {
            return false;
        }

        return password_verify($password, $hash);
    }
}

if (!function_exists('oxford_update_contract_password')) {
    function oxford_update_contract_password(PDO $pdo, string $newPassword, ?int $userId = null): void
    {
        oxford_set_setting($pdo, 'contract_password_hash', password_hash($newPassword, PASSWORD_DEFAULT), $userId);
    }
}

if (!function_exists('oxford_get_user_house_ids')) {
    function oxford_get_user_house_ids(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare('SELECT house_id FROM oxford_master_house_user_access WHERE user_id = ? ORDER BY is_primary DESC, house_id ASC');
        $stmt->execute([$userId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'house_id'));
    }
}

if (!function_exists('oxford_user_has_house_access')) {
    function oxford_user_has_house_access(PDO $pdo, int $userId, int $houseId): bool
    {
        $stmt = $pdo->prepare('SELECT id FROM oxford_master_house_user_access WHERE user_id = ? AND house_id = ? LIMIT 1');
        $stmt->execute([$userId, $houseId]);
        return (bool)$stmt->fetchColumn();
    }
}
/* ===== Oxford installer managed helpers end ===== */

PHP;
}

function buildManagedConfigBlock(array $config): string
{
    $dbType = var_export($config['database_type'], true);
    $dbHost = var_export($config['db_host'], true);
    $dbName = var_export($config['db_name'], true);
    $dbUser = var_export($config['db_user'], true);
    $dbPass = var_export($config['db_pass'], true);
    $sqlitePath = var_export($config['sqlite_path'], true);
    $appName = var_export($config['app_name'], true);
    $baseUrl = var_export($config['base_url'], true);

    return <<<PHP
/* ===== Oxford installer managed config start ===== */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

\$dbType = {$dbType};
\$dbHost = {$dbHost};
\$dbName = {$dbName};
\$dbUser = {$dbUser};
\$dbPass = {$dbPass};
\$sqlitePath = {$sqlitePath};

\$oxfordAppName = {$appName};
\$oxfordBaseUrl = {$baseUrl};
/* ===== Oxford installer managed config end ===== */

PHP;
}

function mergeIntoExistingMasterConfig(string $existing, array $config): string
{
    $managedConfig = buildManagedConfigBlock($config);
    $helperBlock = buildHelperBlock();

    $existing = preg_replace(
        '/\/\* ===== Oxford installer managed config start ===== \*\/.*?\/\* ===== Oxford installer managed config end ===== \*\//s',
        trim($managedConfig),
        $existing,
        1,
        $configReplaced
    );

    if (!$configReplaced) {
        $existing = rtrim($existing) . "\n\n" . trim($managedConfig) . "\n";
    }

    $existing = preg_replace(
        '/\/\* ===== Oxford installer managed helpers start ===== \*\/.*?\/\* ===== Oxford installer managed helpers end ===== \*\//s',
        trim($helperBlock),
        $existing,
        1,
        $helpersReplaced
    );

    if (!$helpersReplaced) {
        $existing = rtrim($existing) . "\n\n" . trim($helperBlock) . "\n";
    }

    $patterns = [
        '/^\s*\$dbHost\s*=\s*.*?;\s*$/m' => '$dbHost = ' . var_export($config['db_host'], true) . ';',
        '/^\s*\$dbName\s*=\s*.*?;\s*$/m' => '$dbName = ' . var_export($config['db_name'], true) . ';',
        '/^\s*\$dbUser\s*=\s*.*?;\s*$/m' => '$dbUser = ' . var_export($config['db_user'], true) . ';',
        '/^\s*\$dbPass\s*=\s*.*?;\s*$/m' => '$dbPass = ' . var_export($config['db_pass'], true) . ';',
    ];

    foreach ($patterns as $pattern => $replacement) {
        $existing = preg_replace($pattern, $replacement, $existing);
    }

    if (!preg_match('/^\s*\$dbType\s*=/m', $existing)) {
        $existing = preg_replace(
            '/\/\* ===== Oxford installer managed config end ===== \*\//',
            '$dbType = ' . var_export($config['database_type'], true) . ";\n\$sqlitePath = " . var_export($config['sqlite_path'], true) . ";\n\$oxfordAppName = " . var_export($config['app_name'], true) . ";\n\$oxfordBaseUrl = " . var_export($config['base_url'], true) . ";\n/* ===== Oxford installer managed config end ===== */",
            $existing,
            1
        );
    }

    return $existing;
}

function buildNewMasterConfig(array $config): string
{
    return <<<PHP
<?php
declare(strict_types=1);

{$managed = trim(buildManagedConfigBlock($config))}

{$helpers = trim(buildHelperBlock())}

PHP;
}

function writeMasterConfigFile(array $config): ?string
{
    ensureDirectory(dirname(MASTER_CONFIG_FILE));
    $backupPath = backupExistingMasterConfig();

    if (file_exists(MASTER_CONFIG_FILE)) {
        $existing = file_get_contents(MASTER_CONFIG_FILE);
        if ($existing === false) {
            throw new RuntimeException('Unable to read existing master_config.php');
        }

        $newContent = mergeIntoExistingMasterConfig($existing, $config);
    } else {
        $newContent = buildNewMasterConfig($config);
    }

    file_put_contents(MASTER_CONFIG_FILE, $newContent);
    return $backupPath;
}

$support = detectDatabaseSupport();
$defaultInstallType = getPreferredInstallType($support);

$errors = [];
$success = false;
$installSummary = [];
$backupConfigPath = null;

if (installerIsLocked()) {
    $errors[] = 'Installer is locked. Delete extras/install.lock only if you intentionally want to reinstall.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !installerIsLocked()) {
    $data = [
        'install_type' => post('install_type', $defaultInstallType),
        'db_host' => post('db_host', 'localhost'),
        'db_name' => post('db_name', 'secretary'),
        'db_user' => post('db_user', 'root'),
        'db_pass' => (string)($_POST['db_pass'] ?? ''),
        'sqlite_path' => post('sqlite_path', SQLITE_DEFAULT_RELATIVE),
        'app_name' => post('app_name', 'Oxford Houses Central Management System'),
        'base_url' => normalizeBaseUrl(post('base_url', '')),
        'admin_name' => post('admin_name', ''),
        'admin_email' => post('admin_email', ''),
        'admin_password' => (string)($_POST['admin_password'] ?? ''),
        'contract_password' => (string)($_POST['contract_password'] ?? ''),
        'seed_demo_house' => isset($_POST['seed_demo_house']) ? '1' : '0',
    ];

    $errors = validateInstallInput($data, $support);

    if (empty($errors)) {
        try {
            if ($data['install_type'] === 'mysql') {
                createMySqlDatabaseIfMissing($data['db_host'], $data['db_name'], $data['db_user'], $data['db_pass']);
                $pdo = connectToMySql($data['db_host'], $data['db_name'], $data['db_user'], $data['db_pass']);
                $absoluteSqlitePath = __DIR__ . '/' . SQLITE_DEFAULT_RELATIVE;
            } else {
                $absoluteSqlitePath = absolutePathFromInput($data['sqlite_path']);
                $pdo = connectToSqlite($absoluteSqlitePath);
            }

            $pdo->beginTransaction();

            createSchema($pdo);

            $superAdminId = createInitialSuperAdmin(
                $pdo,
                $data['admin_name'],
                $data['admin_email'],
                $data['admin_password']
            );

            setSetting($pdo, 'app_name', $data['app_name'], $superAdminId);
            setSetting($pdo, 'base_url', $data['base_url'], $superAdminId);
            setSetting($pdo, 'contract_password_hash', password_hash($data['contract_password'], PASSWORD_DEFAULT), $superAdminId);
            setSetting($pdo, 'installation_complete', '1', $superAdminId);
            setSetting($pdo, 'installed_at', date('Y-m-d H:i:s'), $superAdminId);
            setSetting($pdo, 'installed_database_type', $data['install_type'], $superAdminId);

            if ($data['install_type'] === 'sqlite') {
                setSetting($pdo, 'installed_sqlite_path', $absoluteSqlitePath, $superAdminId);
            }

            if ($data['seed_demo_house'] === '1') {
                $now = date('Y-m-d H:i:s');

                $stmt = $pdo->prepare('
                    INSERT INTO oxford_master_houses (house_name, house_code, city, state, is_active, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    'Oxford House Demo',
                    'DEMO',
                    'Glendale',
                    'Colorado',
                    1,
                    $now,
                    $now,
                ]);

                $houseId = (int)$pdo->lastInsertId();

                $link = $pdo->prepare('
                    INSERT INTO oxford_master_house_user_access (house_id, user_id, is_primary, created_at)
                    VALUES (?, ?, ?, ?)
                ');
                $link->execute([$houseId, $superAdminId, 1, $now]);

                logInstallEvent($pdo, $superAdminId, 'demo_house_seeded', [
                    'house_id' => $houseId,
                    'house_name' => 'Oxford House Demo',
                ]);
            }

            logInstallEvent($pdo, $superAdminId, 'system_installed', [
                'database_type' => $data['install_type'],
                'admin_email' => $data['admin_email'],
                'app_name' => $data['app_name'],
            ]);

            $pdo->commit();

            $backupConfigPath = writeMasterConfigFile([
                'database_type' => $data['install_type'],
                'db_host' => $data['db_host'],
                'db_name' => $data['db_name'],
                'db_user' => $data['db_user'],
                'db_pass' => $data['db_pass'],
                'sqlite_path' => $absoluteSqlitePath,
                'app_name' => $data['app_name'],
                'base_url' => $data['base_url'],
            ]);

            writeInstallLock([
                'installed_at' => date('c'),
                'database_type' => $data['install_type'],
                'admin_email' => $data['admin_email'],
                'app_name' => $data['app_name'],
            ]);

            $success = true;
            $installSummary = [
                'System Name' => $data['app_name'],
                'Database Type' => strtoupper($data['install_type']),
                'Super Admin' => $data['admin_email'],
                'master_config.php' => 'extras/master_config.php',
                'Lock File' => 'extras/install.lock',
                'SQLite Path' => $data['install_type'] === 'sqlite' ? $absoluteSqlitePath : 'Not used',
                'Config Backup' => $backupConfigPath ?? 'No previous config found',
            ];
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Installation failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Oxford Houses Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{margin:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#17365d;}
        .wrap{max-width:980px;margin:30px auto;padding:0 18px;}
        .card{background:#fff;border-radius:18px;box-shadow:0 10px 30px rgba(15,44,80,.08);padding:24px;margin-bottom:18px;}
        h1,h2,h3{margin-top:0;}
        .muted{color:#6a7e95;}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        @media (max-width:860px){.grid{grid-template-columns:1fr;}}
        label{display:block;font-weight:700;margin-bottom:6px;color:#17365d;}
        input[type="text"],input[type="email"],input[type="password"],select{
            width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #ccd9e8;border-radius:12px;background:#fff;margin-bottom:12px;
        }
        .note{font-size:13px;color:#6a7e95;margin-top:-6px;margin-bottom:12px;}
        .radio-row{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:12px;}
        .radio-box{border:1px solid #d7e3f0;background:#fbfdff;border-radius:12px;padding:12px 14px;display:flex;align-items:center;gap:10px;}
        .check-row{display:flex;align-items:center;gap:10px;margin:8px 0 0;}
        .alert{padding:12px 14px;border-radius:12px;margin-bottom:14px;}
        .alert-error{background:#fff1f1;color:#982b2b;}
        .alert-success{background:#edf9ef;color:#186530;}
        .badge{display:inline-block;padding:6px 10px;border-radius:999px;background:#eef4fa;color:#17365d;font-size:12px;font-weight:700;margin-right:8px;margin-bottom:8px;}
        .btn{border:0;background:#17365d;color:#fff;border-radius:12px;padding:12px 18px;font-weight:700;cursor:pointer;}
        .btn:hover{background:#234b7c;}
        .kv{display:grid;grid-template-columns:220px 1fr;gap:10px;padding:8px 0;border-bottom:1px solid #edf2f7;}
        .kv strong{color:#17365d;}
        code{background:#f4f7fb;padding:2px 6px;border-radius:6px;}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Oxford Houses Central Management System Installer</h1>
        <div class="muted">This installer preserves your existing master_config.php style where possible, injects only the managed database block and helpers, creates the initial schema, and seeds the first super admin.</div>
    </div>

    <div class="card">
        <h2>Environment Detection</h2>
        <span class="badge">PDO: <?= $support['pdo'] ? 'Available' : 'Missing' ?></span>
        <span class="badge">MySQL: <?= $support['mysql'] ? 'Available' : 'Missing' ?></span>
        <span class="badge">SQLite: <?= $support['sqlite'] ? 'Available' : 'Missing' ?></span>
        <div class="muted" style="margin-top:10px;">
            Available drivers:
            <code><?= h(implode(', ', $support['drivers'])) ?></code>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="card">
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="card">
            <div class="alert alert-success">Installation completed successfully.</div>
            <h2>Installation Summary</h2>
            <?php foreach ($installSummary as $label => $value): ?>
                <div class="kv">
                    <strong><?= h($label) ?></strong>
                    <div><?= h($value) ?></div>
                </div>
            <?php endforeach; ?>
            <p class="muted" style="margin-top:16px;">
                Delete or rename <code>install.php</code> after confirming the system is working.
            </p>
        </div>
    <?php elseif (!installerIsLocked()): ?>
        <form method="post" class="card">
            <h2>Installation Settings</h2>

            <label>Database Type</label>
            <div class="radio-row">
                <?php if ($support['mysql']): ?>
                    <label class="radio-box">
                        <input type="radio" name="install_type" value="mysql" <?= post('install_type', $defaultInstallType) === 'mysql' ? 'checked' : '' ?>>
                        <span>MySQL / MariaDB</span>
                    </label>
                <?php endif; ?>

                <?php if ($support['sqlite']): ?>
                    <label class="radio-box">
                        <input type="radio" name="install_type" value="sqlite" <?= post('install_type', $defaultInstallType) === 'sqlite' ? 'checked' : '' ?>>
                        <span>SQLite</span>
                    </label>
                <?php endif; ?>
            </div>

            <div class="grid">
                <div>
                    <h3>System</h3>
                    <label>System Name</label>
                    <input type="text" name="app_name" value="<?= h(post('app_name', 'Oxford Houses Central Management System')) ?>">

                    <label>Base URL</label>
                    <input type="text" name="base_url" value="<?= h(post('base_url', '')) ?>">
                    <div class="note">Example: https://yourdomain.com/oxford</div>
                </div>

                <div>
                    <h3>Super Admin</h3>
                    <label>Full Name</label>
                    <input type="text" name="admin_name" value="<?= h(post('admin_name', '')) ?>">

                    <label>Email</label>
                    <input type="email" name="admin_email" value="<?= h(post('admin_email', '')) ?>">

                    <label>Password</label>
                    <input type="password" name="admin_password" value="">

                    <label>Initial Contract Password</label>
                    <input type="password" name="contract_password" value="">
                </div>
            </div>

            <div class="grid">
                <div>
                    <h3>MySQL Settings</h3>
                    <label>MySQL Host</label>
                    <input type="text" name="db_host" value="<?= h(post('db_host', 'localhost')) ?>">

                    <label>MySQL Database Name</label>
                    <input type="text" name="db_name" value="<?= h(post('db_name', 'secretary')) ?>">

                    <label>MySQL Username</label>
                    <input type="text" name="db_user" value="<?= h(post('db_user', 'root')) ?>">

                    <label>MySQL Password</label>
                    <input type="password" name="db_pass" value="">
                </div>

                <div>
                    <h3>SQLite Settings</h3>
                    <label>SQLite Database Path</label>
                    <input type="text" name="sqlite_path" value="<?= h(post('sqlite_path', SQLITE_DEFAULT_RELATIVE)) ?>">
                    <div class="note">Default: <?= h(SQLITE_DEFAULT_RELATIVE) ?></div>

                    <div class="check-row">
                        <input type="checkbox" id="seed_demo_house" name="seed_demo_house" value="1" <?= isset($_POST['seed_demo_house']) ? 'checked' : '' ?>>
                        <label for="seed_demo_house" style="margin:0;">Seed demo house and assign it to the super admin</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn">Install System</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>