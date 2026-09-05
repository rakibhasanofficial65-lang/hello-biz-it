<?php

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if (!$host || !$dbname || !$username || !$password) {
    error_log('Database configuration error: missing DB environment variable.');
    die('Database configuration error. Please contact the administrator.');
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,

    // TiDB Cloud requires TLS
    PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
];

try {

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

    die('Database connection failed. Please try again later.');
}
