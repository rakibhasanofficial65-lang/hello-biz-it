<?php

declare(strict_types=1);

$host = trim(getenv('DB_HOST') ?: '');
$port = trim(getenv('DB_PORT') ?: '4000');
$dbname = trim(getenv('DB_NAME') ?: '');
$username = trim(getenv('DB_USER') ?: '');
$password = getenv('DB_PASSWORD') ?: '';

try {

    if ($host === '' || $dbname === '' || $username === '') {
        throw new RuntimeException(
            'Database environment variables are missing.'
        );
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    /*
     * TiDB Cloud SSL
     */

    $caFile = getenv('DB_SSL_CA') ?: '';

    if ($caFile !== '' && is_file($caFile)) {

        $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;

        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
    }

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

} catch (Throwable $e) {

    error_log(
        'DATABASE ERROR: ' .
        $e->getMessage()
    );

    /*
     * TEMPORARY DEBUG
     * Remove after database works.
     */

    die(
        'Database connection failed: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}
