<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$masterDbHost = 'localhost';
$masterDbName = 'oxford_central';
$masterDbUser = 'secretary';
$masterDbPass = 'EK@rL4mIpKgU5b)P';

function oxford_h(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function oxford_master_connect_server(string $host, string $user, string $pass): PDO
{
    return new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function oxford_master_connect_db(string $host, string $dbName, string $user, string $pass): PDO
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

function oxford_base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
}

function oxford_request_path(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $base = oxford_base_path();
    if ($script !== '' && str_starts_with($script, $base)) {
        return ltrim(substr($script, strlen($base)), '/');
    }
    return ltrim(basename($_SERVER['PHP_SELF'] ?? ''), '/');
}

function oxford_url(string $path): string
{
    $prefix = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    $prefix = $prefix === '.' ? '' : $prefix;
    $path = ltrim($path, '/');
    return ($prefix !== '' ? $prefix . '/' : '') . $path;
}

function oxford_redirect(string $path): never
{
    header('Location: ' . oxford_url($path));
    exit;
}


function oxford_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'house';
}

function oxford_database_exists(PDO $serverPdo, string $dbName): bool
{
    $stmt = $serverPdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1');
    $stmt->execute([$dbName]);
    return (bool)$stmt->fetchColumn();
}

function oxford_generate_unique_house_code(PDO $masterPdo, string $seed): string
{
    $base = oxford_slugify($seed);
    $code = $base;
    $i = 2;
    $stmt = $masterPdo->prepare('SELECT id FROM oxford_master_houses WHERE house_code = ? LIMIT 1');
    while (true) {
        $stmt->execute([$code]);
        if (!$stmt->fetchColumn()) {
            return $code;
        }
        $code = $base . '-' . $i;
        $i++;
    }
}

function oxford_generate_unique_database_name(PDO $masterPdo, PDO $serverPdo, string $seed): string
{
    $base = 'oxford_' . str_replace('-', '_', oxford_slugify($seed));
    $dbName = $base;
    $i = 2;
    $stmt = $masterPdo->prepare('SELECT id FROM oxford_master_houses WHERE database_name = ? LIMIT 1');
    while (true) {
        $stmt->execute([$dbName]);
        if (!$stmt->fetchColumn() && !oxford_database_exists($serverPdo, $dbName)) {
            return $dbName;
        }
        $dbName = $base . '_' . $i;
        $i++;
    }
}

function oxford_create_house_database(PDO $serverPdo, string $databaseName, string $templateDatabase = 'oxford_tables'): array
{
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $copiedTables = [];
    if (oxford_database_exists($serverPdo, $templateDatabase)) {
        $sourceTables = $serverPdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $serverPdo->quote($templateDatabase) . " AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($sourceTables as $sourceTable) {
            $sourceTable = (string)$sourceTable;
            if ($sourceTable === '') {
                continue;
            }
            $serverPdo->exec("CREATE TABLE IF NOT EXISTS `{$databaseName}`.`{$sourceTable}` LIKE `{$templateDatabase}`.`{$sourceTable}`");
            $copiedTables[] = $sourceTable;
        }
    }

    if (empty($copiedTables)) {
        $serverPdo->exec("CREATE TABLE IF NOT EXISTS `{$databaseName}`.`oxford_house_bootstrap` (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            note VARCHAR(255) NOT NULL DEFAULT 'Initial bootstrap table'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $copiedTables[] = 'oxford_house_bootstrap';
    }

    return $copiedTables;
}


function oxford_collect_module_schema_sql(string $basePath, array $folders = ['chapter', 'state']): array
{
    $sqlStatements = [];

    foreach ($folders as $folder) {
        $folderPath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . trim($folder, '/\\');
        if (!is_dir($folderPath)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
                continue;
            }

            $contents = @file_get_contents($fileInfo->getPathname());
            if (!is_string($contents) || $contents === '') {
                continue;
            }

            if (preg_match_all('/\$pdo->exec\(\s*(["\'])\s*(CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS.*?)\s*\1\s*\)/is', $contents, $createMatches)) {
                foreach ($createMatches[2] as $statement) {
                    $statement = trim((string)$statement);
                    if ($statement !== '') {
                        $statement = rtrim($statement, ";\r\n\t ") . ';';
                        $sqlStatements[$statement] = $statement;
                    }
                }
            }

            if (preg_match_all('/=>\s*"\s*(ALTER\s+TABLE.*?)"/is', $contents, $alterMatches)) {
                foreach ($alterMatches[1] as $statement) {
                    $statement = trim((string)$statement);
                    if ($statement !== '' && preg_match('/^ALTER\s+TABLE\s+/i', $statement)) {
                        $statement = rtrim($statement, ";\r\n\t ") . ';';
                        $sqlStatements[$statement] = $statement;
                    }
                }
            }
        }
    }

    $fallbackStatements = [
        "CREATE TABLE IF NOT EXISTS hsc_meeting_minutes_json (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            house_name VARCHAR(255) NOT NULL DEFAULT '',
            meeting_date DATE NOT NULL,
            start_time TIME NULL DEFAULT NULL,
            end_time TIME NULL DEFAULT NULL,
            report_json LONGTEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_house_date (house_name, meeting_date),
            KEY idx_meeting_date (meeting_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ];

    foreach ($fallbackStatements as $statement) {
        $sqlStatements[$statement] = $statement;
    }

    return array_values($sqlStatements);
}
function oxford_sync_module_schema(PDO $pdo, string $basePath, array $folders = ['chapter', 'state']): array
{
    $applied = [];
    foreach (oxford_collect_module_schema_sql($basePath, $folders) as $statement) {
        try {
            $pdo->exec($statement);
            if (preg_match('/(?:CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS|ALTER\s+TABLE)\s+`?([a-zA-Z0-9_]+)`?/i', $statement, $match)) {
                $applied[] = $match[1];
            }
        } catch (Throwable $e) {
            // Ignore duplicate-column and already-applied migration errors so older house DBs can self-heal safely.
            $message = strtolower((string)$e->getMessage());
            if (!str_contains($message, 'duplicate column') && !str_contains($message, 'duplicate key') && !str_contains($message, '1060') && !str_contains($message, '1061')) {
                throw $e;
            }
        }
    }

    return array_values(array_unique($applied));
}

function oxford_sync_all_house_module_schema(PDO $serverPdo, PDO $masterPdo, string $dbHost, string $dbUser, string $dbPass, string $basePath, array $folders = ['chapter', 'state']): array
{
    $synced = [];
    $stmt = $masterPdo->query('SELECT id, database_name FROM oxford_master_houses WHERE is_active = 1 ORDER BY id ASC');
    $houses = $stmt ? $stmt->fetchAll() : [];

    foreach ($houses as $house) {
        $databaseName = (string)($house['database_name'] ?? '');
        if ($databaseName === '') {
            continue;
        }

        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $housePdo = oxford_master_connect_db($dbHost, $databaseName, $dbUser, $dbPass);
        $synced[$databaseName] = oxford_sync_module_schema($housePdo, $basePath, $folders);
    }

    return $synced;
}

function oxford_role_rank(string $role): int
{
    return match ($role) {
        'super_admin' => 100,
        'central_admin' => 90,
        'regional_admin' => 70,
        'house_manager' => 50,
        'house_user' => 10,
        default => 0,
    };
}

function oxford_is_central_role(?string $role): bool
{
    return in_array((string)$role, ['central_admin', 'super_admin', 'regional_admin'], true);
}

function oxford_can_access_house(array $user, int $houseId): bool
{
    if ($houseId <= 0) {
        return false;
    }
    if (oxford_is_central_role($user['role'] ?? '')) {
        return true;
    }
    $allowed = $user['assigned_house_ids'] ?? [];
    return in_array($houseId, $allowed, true);
}

function oxford_ensure_logged_in(): void
{
    $publicPaths = ['login.php'];
    if (!empty($_SESSION['oxford_auth_user'])) {
        return;
    }
    if (in_array(oxford_request_path(), $publicPaths, true)) {
        return;
    }
    $redirect = oxford_request_path();
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect .= '?' . $_SERVER['QUERY_STRING'];
    }
    header('Location: ' . oxford_url('login.php?redirect=' . urlencode($redirect)));
    exit;
}


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

function oxford_get_contract_stamp_default_password(): string
{
    return 'OxfordContractStamp2026!';
}

function oxford_get_contract_stamp_password_hash(PDO $masterPdo, int $houseId): ?string
{
    if ($houseId <= 0) {
        return null;
    }
    try {
        $stmt = $masterPdo->prepare('SELECT contract_stamp_password_hash FROM oxford_master_house_settings WHERE house_id = ? LIMIT 1');
        $stmt->execute([$houseId]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) && $hash !== '' ? $hash : null;
    } catch (Throwable $e) {
        return null;
    }
}

function oxford_verify_contract_stamp_password(PDO $masterPdo, int $houseId, string $password): bool
{
    $password = (string)$password;
    if ($password === '') {
        return false;
    }
    $hash = oxford_get_contract_stamp_password_hash($masterPdo, $houseId);
    if ($hash !== null) {
        return password_verify($password, $hash);
    }
    return hash_equals(oxford_get_contract_stamp_default_password(), $password);
}

function oxford_set_contract_stamp_password(PDO $masterPdo, int $houseId, string $newPassword, ?int $updatedByUserId = null): void
{
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $masterPdo->prepare('INSERT INTO oxford_master_house_settings (house_id, contract_stamp_password_hash, updated_by_user_id) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE contract_stamp_password_hash = VALUES(contract_stamp_password_hash), updated_by_user_id = VALUES(updated_by_user_id), updated_at = CURRENT_TIMESTAMP');
    $stmt->execute([$houseId, $hash, $updatedByUserId]);
}

function oxford_humanize_key(string $value): string
{
    $value = trim(str_replace(['_', '-'], ' ', $value));
    return $value === '' ? '' : ucwords($value);
}

function oxford_format_log_value(mixed $value): string
{
    if (is_array($value)) {
        $parts = [];
        foreach ($value as $item) {
            if (is_scalar($item) || $item === null) {
                $parts[] = (string)$item;
            }
        }
        return implode(', ', array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }
    if ($value === null) {
        return '';
    }
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    return trim((string)$value);
}

function oxford_format_log_details(?string $detailsJson): string
{
    $detailsJson = trim((string)$detailsJson);
    if ($detailsJson === '') {
        return '';
    }
    $decoded = json_decode($detailsJson, true);
    if (!is_array($decoded)) {
        return $detailsJson;
    }

    $segments = [];
    foreach ($decoded as $key => $value) {
        $formatted = oxford_format_log_value($value);
        if ($formatted === '') {
            continue;
        }
        $segments[] = oxford_humanize_key((string)$key) . ': ' . $formatted;
    }

    return implode(' • ', $segments);
}

function oxford_format_log_name(string $value): string
{
    $value = trim($value);
    return $value === '' ? '' : ucwords(str_replace(['_', '-'], ' ', $value));
}

function oxford_require_role(array $roles): void
{
    $user = $_SESSION['oxford_auth_user'] ?? null;
    if (!$user) {
        oxford_ensure_logged_in();
    }
    $role = (string)($user['role'] ?? '');
    foreach ($roles as $allowed) {
        if ($role === $allowed) {
            return;
        }
    }
    oxford_redirect('access_denied.php');
}

function oxford_log_activity(PDO $masterPdo, ?int $houseId, string $pageName, string $eventName, ?array $details = null, ?int $userId = null): void
{
    try {
        $stmt = $masterPdo->prepare('INSERT INTO oxford_master_activity (house_id, user_id, page_name, event_name, details_json) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $houseId,
            $userId,
            $pageName,
            $eventName,
            $details ? json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) {
    }
}

function oxford_log_audit(PDO $masterPdo, array $payload): void
{
    try {
        $stmt = $masterPdo->prepare('INSERT INTO oxford_master_audit_log (house_id, user_id, action_name, page_name, target_table, target_id, ip_address, details_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $payload['house_id'] ?? null,
            $payload['user_id'] ?? null,
            $payload['action_name'] ?? '',
            $payload['page_name'] ?? '',
            $payload['target_table'] ?? '',
            $payload['target_id'] ?? '',
            substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
            isset($payload['details']) ? json_encode($payload['details'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) {
    }
}

try {
    $oxfordServerPdo = oxford_master_connect_server($masterDbHost, $masterDbUser, $masterDbPass);
    $oxfordServerPdo->exec("CREATE DATABASE IF NOT EXISTS `{$masterDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $masterPdo = oxford_master_connect_db($masterDbHost, $masterDbName, $masterDbUser, $masterDbPass);

    $masterPdo->exec("CREATE TABLE IF NOT EXISTS oxford_master_houses (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        house_name VARCHAR(150) NOT NULL,
        house_code VARCHAR(100) NOT NULL UNIQUE,
        database_name VARCHAR(150) NOT NULL UNIQUE,
        city VARCHAR(100) NOT NULL DEFAULT '',
        state VARCHAR(50) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $masterPdo->exec("CREATE TABLE IF NOT EXISTS oxford_master_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('house_user','house_manager','regional_admin','central_admin','super_admin') NOT NULL DEFAULT 'house_user',
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        last_login_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $masterPdo->exec("CREATE TABLE IF NOT EXISTS oxford_master_house_user_access (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        house_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_house_user (house_id, user_id),
        CONSTRAINT fk_oxford_house_access_house FOREIGN KEY (house_id) REFERENCES oxford_master_houses(id) ON DELETE CASCADE,
        CONSTRAINT fk_oxford_house_access_user FOREIGN KEY (user_id) REFERENCES oxford_master_users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $masterPdo->exec("CREATE TABLE IF NOT EXISTS oxford_master_house_settings (
        house_id INT UNSIGNED NOT NULL PRIMARY KEY,
        contract_stamp_password_hash VARCHAR(255) NOT NULL DEFAULT '',
        updated_by_user_id INT UNSIGNED NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_oxford_master_house_settings_house FOREIGN KEY (house_id) REFERENCES oxford_master_houses(id) ON DELETE CASCADE,
        CONSTRAINT fk_oxford_master_house_settings_user FOREIGN KEY (updated_by_user_id) REFERENCES oxford_master_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $masterPdo->exec("CREATE TABLE IF NOT EXISTS oxford_master_activity (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        house_id INT UNSIGNED NULL,
        user_id INT UNSIGNED NULL,
        page_name VARCHAR(255) NOT NULL DEFAULT '',
        event_name VARCHAR(100) NOT NULL DEFAULT '',
        details_json LONGTEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_house_created (house_id, created_at),
        INDEX idx_user_created (user_id, created_at),
        CONSTRAINT fk_oxford_master_activity_house FOREIGN KEY (house_id) REFERENCES oxford_master_houses(id) ON DELETE SET NULL,
        CONSTRAINT fk_oxford_master_activity_user FOREIGN KEY (user_id) REFERENCES oxford_master_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $masterPdo->exec("CREATE TABLE IF NOT EXISTS oxford_master_audit_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        house_id INT UNSIGNED NULL,
        user_id INT UNSIGNED NULL,
        action_name VARCHAR(100) NOT NULL DEFAULT '',
        page_name VARCHAR(255) NOT NULL DEFAULT '',
        target_table VARCHAR(150) NOT NULL DEFAULT '',
        target_id VARCHAR(150) NOT NULL DEFAULT '',
        ip_address VARCHAR(64) NOT NULL DEFAULT '',
        details_json LONGTEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_house (house_id, created_at),
        INDEX idx_audit_user (user_id, created_at),
        CONSTRAINT fk_oxford_master_audit_house FOREIGN KEY (house_id) REFERENCES oxford_master_houses(id) ON DELETE SET NULL,
        CONSTRAINT fk_oxford_master_audit_user FOREIGN KEY (user_id) REFERENCES oxford_master_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $exists = (int)$masterPdo->query('SELECT COUNT(*) FROM oxford_master_houses')->fetchColumn();
    if ($exists === 0) {
        $seed = $masterPdo->prepare('INSERT INTO oxford_master_houses (house_name, house_code, database_name, city, state) VALUES (?, ?, ?, ?, ?)');
        $seed->execute(['Default Oxford House', 'default-house', 'secretary', '', '']);
    }

    $adminEmail = 'admin@oxford.local';
    $adminExists = $masterPdo->prepare('SELECT id FROM oxford_master_users WHERE email = ? LIMIT 1');
    $adminExists->execute([$adminEmail]);
    $adminId = (int)($adminExists->fetchColumn() ?: 0);
    if ($adminId <= 0) {
        $insertAdmin = $masterPdo->prepare('INSERT INTO oxford_master_users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)');
        $insertAdmin->execute([
            'Oxford Central Admin',
            $adminEmail,
            password_hash('Admin123!', PASSWORD_DEFAULT),
            'central_admin',
            'active',
        ]);
        $adminId = (int)$masterPdo->lastInsertId();
    }

    $houseRows = $masterPdo->query('SELECT * FROM oxford_master_houses WHERE is_active = 1 ORDER BY house_name ASC')->fetchAll();
    $oxfordModuleSchemaSync = oxford_sync_all_house_module_schema($oxfordServerPdo, $masterPdo, $masterDbHost, $masterDbUser, $masterDbPass, oxford_base_path(), ['chapter', 'state']);
    $houseMap = [];
    foreach ($houseRows as $houseRow) {
        $houseMap[(int)$houseRow['id']] = $houseRow;
    }

    $defaultHouseId = (int)array_key_first($houseMap);
    $houseUserEmail = 'houseuser@default-house.local';
    $houseUserStmt = $masterPdo->prepare('SELECT id FROM oxford_master_users WHERE email = ? LIMIT 1');
    $houseUserStmt->execute([$houseUserEmail]);
    $defaultHouseUserId = (int)($houseUserStmt->fetchColumn() ?: 0);
    if ($defaultHouseUserId <= 0) {
        $insertHouseUser = $masterPdo->prepare('INSERT INTO oxford_master_users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)');
        $insertHouseUser->execute([
            'Default House Manager',
            $houseUserEmail,
            password_hash('House123!', PASSWORD_DEFAULT),
            'house_manager',
            'active',
        ]);
        $defaultHouseUserId = (int)$masterPdo->lastInsertId();
    }
    if ($defaultHouseId > 0 && $defaultHouseUserId > 0) {
        $mapStmt = $masterPdo->prepare('INSERT IGNORE INTO oxford_master_house_user_access (house_id, user_id, is_primary) VALUES (?, ?, 1)');
        $mapStmt->execute([$defaultHouseId, $defaultHouseUserId]);
    }

    $activeUser = null;
    if (!empty($_SESSION['oxford_auth_user']['id'])) {
        $loadUser = $masterPdo->prepare('SELECT * FROM oxford_master_users WHERE id = ? AND status = ? LIMIT 1');
        $loadUser->execute([(int)$_SESSION['oxford_auth_user']['id'], 'active']);
        $userRow = $loadUser->fetch();
        if ($userRow) {
            $accessStmt = $masterPdo->prepare('SELECT house_id FROM oxford_master_house_user_access WHERE user_id = ? ORDER BY is_primary DESC, house_id ASC');
            $accessStmt->execute([(int)$userRow['id']]);
            $houseIds = array_map('intval', array_column($accessStmt->fetchAll(), 'house_id'));
            $activeUser = [
                'id' => (int)$userRow['id'],
                'full_name' => (string)$userRow['full_name'],
                'email' => (string)$userRow['email'],
                'role' => (string)$userRow['role'],
                'status' => (string)$userRow['status'],
                'assigned_house_ids' => $houseIds,
            ];
            $_SESSION['oxford_auth_user'] = $activeUser;
        } else {
            unset($_SESSION['oxford_auth_user']);
        }
    }

    oxford_ensure_logged_in();

    $activeUser = $_SESSION['oxford_auth_user'] ?? null;
    $requestedHouseId = isset($_GET['house_id']) ? (int)$_GET['house_id'] : (isset($_POST['house_id']) ? (int)$_POST['house_id'] : 0);

    $allowedHouseIds = [];
    if ($activeUser) {
        $allowedHouseIds = oxford_is_central_role($activeUser['role'])
            ? array_map('intval', array_keys($houseMap))
            : array_values(array_filter($activeUser['assigned_house_ids'] ?? [], static fn ($id) => isset($houseMap[$id])));
    }

    if ($requestedHouseId > 0 && $activeUser && oxford_can_access_house($activeUser, $requestedHouseId) && isset($houseMap[$requestedHouseId])) {
        $_SESSION['oxford_house_id'] = $requestedHouseId;
        if (($requestedHouseId ?? 0) !== (int)($_SESSION['oxford_house_id_previous'] ?? 0)) {
            oxford_log_activity($masterPdo, $requestedHouseId, basename($_SERVER['PHP_SELF'] ?? ''), 'house_switched', ['house_id' => $requestedHouseId], (int)$activeUser['id']);
            oxford_log_audit($masterPdo, [
                'house_id' => $requestedHouseId,
                'user_id' => (int)$activeUser['id'],
                'action_name' => 'house_switched',
                'page_name' => basename($_SERVER['PHP_SELF'] ?? ''),
                'details' => ['house_id' => $requestedHouseId],
            ]);
        }
        $_SESSION['oxford_house_id_previous'] = $requestedHouseId;
    }

    $currentHouseId = (int)($_SESSION['oxford_house_id'] ?? 0);
    if (!$activeUser || !oxford_can_access_house($activeUser, $currentHouseId) || !isset($houseMap[$currentHouseId])) {
        $currentHouseId = (int)($allowedHouseIds[0] ?? $defaultHouseId);
        $_SESSION['oxford_house_id'] = $currentHouseId;
    }

    if ($currentHouseId <= 0 || !isset($houseMap[$currentHouseId])) {
        throw new RuntimeException('No accessible house could be selected for this account.');
    }

    $selectedHouse = $houseMap[$currentHouseId];

    $dbHost = $masterDbHost;
    $dbUser = $masterDbUser;
    $dbPass = $masterDbPass;
    $dbName = (string)$selectedHouse['database_name'];
    $currentHouseName = (string)$selectedHouse['house_name'];
    $currentHouseCode = (string)$selectedHouse['house_code'];
    $currentHouseLabel = trim($currentHouseName . (($selectedHouse['city'] || $selectedHouse['state']) ? ' - ' . trim(($selectedHouse['city'] ? $selectedHouse['city'] . ', ' : '') . $selectedHouse['state']) : ''));
    $allOxfordHouses = array_values(array_filter($houseRows, static fn ($row) => in_array((int)$row['id'], $allowedHouseIds, true)));

    $oxfordServerPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = oxford_master_connect_db($dbHost, $dbName, $dbUser, $dbPass);

    $oxfordUser = $activeUser;
    $oxfordIsCentralAdmin = in_array($oxfordUser['role'] ?? '', ['central_admin', 'super_admin'], true);
    $oxfordIsCentralRole = oxford_is_central_role($oxfordUser['role'] ?? '');
    $oxfordPageName = basename($_SERVER['PHP_SELF'] ?? '');

    if (empty($_SESSION['oxford_page_log']) || $_SESSION['oxford_page_log'] !== md5($oxfordPageName . '|' . $currentHouseId . '|' . ($oxfordUser['id'] ?? 0))) {
        oxford_log_activity($masterPdo, $currentHouseId, $oxfordPageName, 'page_opened', [
            'house_name' => $currentHouseName,
            'role' => $oxfordUser['role'] ?? '',
        ], (int)($oxfordUser['id'] ?? 0));
        $_SESSION['oxford_page_log'] = md5($oxfordPageName . '|' . $currentHouseId . '|' . ($oxfordUser['id'] ?? 0));
    }

    register_shutdown_function(static function () use ($masterPdo, $currentHouseId, $oxfordPageName, $oxfordUser): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $keys = array_values(array_filter(array_keys($_POST), static fn ($key) => !preg_match('/password|pass|token/i', (string)$key)));
            oxford_log_audit($masterPdo, [
                'house_id' => $currentHouseId,
                'user_id' => (int)($oxfordUser['id'] ?? 0),
                'action_name' => 'post_request',
                'page_name' => $oxfordPageName,
                'details' => [
                    'post_keys' => $keys,
                    'query' => $_GET,
                ],
            ]);
            oxford_log_activity($masterPdo, $currentHouseId, $oxfordPageName, 'post_request', ['post_keys' => $keys], (int)($oxfordUser['id'] ?? 0));
        }
    });
} catch (Throwable $e) {
    die('Oxford central database connection failed: ' . oxford_h($e->getMessage()));
}
