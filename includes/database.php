<?php

declare(strict_types=1);

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME') ?: 'hello-biz-it';
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if (!$host || !$username || !$password) {
    error_log('Database environment variables are missing.');

    die('Database configuration is missing. Please try again later.');
}

try {

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,

        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
    ];

    /*
    |--------------------------------------------------------------------------
    | TiDB Cloud SSL
    |--------------------------------------------------------------------------
    */

    $caCandidates = [
        getenv('DB_SSL_CA') ?: '',
        '/etc/ssl/certs/ca-certificates.crt',
        '/etc/ssl/cert.pem',
    ];

    foreach ($caCandidates as $caFile) {

        if ($caFile !== '' && is_file($caFile)) {

            $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;

            break;
        }
    }

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

} catch (PDOException $e) {

    error_log(
        'TiDB database connection failed: ' . $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}
