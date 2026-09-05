<?php

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

if (
    $host === '' ||
    $dbname === '' ||
    $username === '' ||
    $password === ''
) {
    error_log('TiDB configuration is incomplete.');
    throw new RuntimeException('Database configuration is incomplete.');
}

try {

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,

        // TiDB Cloud requires TLS
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

    error_log('TiDB connection successful.');

} catch (PDOException $e) {

    error_log(
        'TiDB connection failed: ' . $e->getMessage()
    );

    throw $e;
}
