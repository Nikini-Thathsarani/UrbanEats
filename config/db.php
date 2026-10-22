<?php
// Single shared database connection (PDO). Every component requires this file.
$configFile = __DIR__ . '/config.php';

if (!is_file($configFile)) {
    error_log('config/config.php is missing. Copy config/config.sample.php to config/config.php');
    http_response_code(500);
    exit('Application is not configured. See README.');
}

$config = require $configFile;

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}