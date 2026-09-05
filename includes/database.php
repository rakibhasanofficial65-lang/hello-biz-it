<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Vercel + TiDB Cloud Database Connection
|--------------------------------------------------------------------------
*/

$host = trim((string) (getenv('DB_HOST') ?: ''));
$port = trim((string) (getenv('DB_PORT') ?: '4000'));
$dbname = trim((string) (getenv('DB_NAME') ?: ''));
$username = trim((string) (getenv('DB_USER') ?: ''));
$password = (string) (getenv('DB_PASSWORD') ?: '');

if (
    $host === '' ||
    $dbname === '' ||
    $username === '' ||
    $password === ''
) {
    error_log('TiDB configuration is incomplete.');

    die('Database configuration error.');
}

/*
|--------------------------------------------------------------------------
| TiDB Cloud TLS
|--------------------------------------------------------------------------
|
| Vercel + TiDB Cloud Public Endpoint
|
*/

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $dbname
);

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,

    /*
     * Enable TLS.
     */
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

/*
|--------------------------------------------------------------------------
| CA certificate
|--------------------------------------------------------------------------
*/

$caFile = '/etc/ssl/certs/ca-certificates.crt';

if (is_readable($caFile)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
}

try {

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

    /*
    |--------------------------------------------------------------------------
    | Test connection
    |--------------------------------------------------------------------------
    */

    $pdo->query('SELECT 1');

    error_log('TiDB connection successful.');

} catch (PDOException $e) {

    error_log(
        'TiDB connection failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}
