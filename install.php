
<?php
require_once __DIR__ . '/extras/master_config.php';

try {
    $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // create central db if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS oxford_central");
    echo "Central database created.<br>";

    // import base schema
    $sql = file_get_contents(__DIR__ . '/extras/sql/oxford_central_install.sql');
    $pdo->exec($sql);

    echo "Installation complete.";
} catch (PDOException $e) {
    echo "Install failed: " . $e->getMessage();
}
?>
