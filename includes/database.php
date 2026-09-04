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


/*
|--------------------------------------------------------------------------
| Check required environment variables
|--------------------------------------------------------------------------
*/

$missing = [];

if ($host === '') {
    $missing[] = 'DB_HOST';
}

if ($port === '') {
    $missing[] = 'DB_PORT';
}

if ($dbname === '') {
    $missing[] = 'DB_NAME';
}

if ($username === '') {
    $missing[] = 'DB_USER';
}

if ($password === '') {
    $missing[] = 'DB_PASSWORD';
}

if (!empty($missing)) {

    error_log(
        'Database environment variables missing: ' .
        implode(', ', $missing)
    );

    die('Database configuration error.');
}


/*
|--------------------------------------------------------------------------
| PDO Options
|--------------------------------------------------------------------------
*/

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


/*
|--------------------------------------------------------------------------
| TiDB Cloud TLS
|--------------------------------------------------------------------------
|
| TiDB Cloud Public Endpoint requires TLS.
|
| First try a custom CA if DB_SSL_CA is provided.
| Otherwise use the operating system CA bundle.
|--------------------------------------------------------------------------
*/

$caFile = '';

/*
|--------------------------------------------------------------------------
| 1. Custom CA path
|--------------------------------------------------------------------------
*/

$customCa = trim((string) (getenv('DB_SSL_CA') ?: ''));

if (
    $customCa !== '' &&
    is_file($customCa) &&
    is_readable($customCa)
) {
    $caFile = $customCa;
}


/*
|--------------------------------------------------------------------------
| 2. Common Linux CA bundle paths
|--------------------------------------------------------------------------
*/

if ($caFile === '') {

    $caCandidates = [
        '/etc/ssl/certs/ca-certificates.crt',
        '/etc/ssl/cert.pem',
        '/etc/pki/tls/certs/ca-bundle.crt',
        '/etc/ssl/ca-bundle.pem',
    ];

    foreach ($caCandidates as $candidate) {

        if (
            is_file($candidate) &&
            is_readable($candidate)
        ) {
            $caFile = $candidate;
            break;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Configure SSL
|--------------------------------------------------------------------------
*/

if ($caFile !== '') {

    $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;

    /*
     * Verify the TiDB Cloud server certificate.
     */
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {

        $options[
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
        ] = true;
    }
}


/*
|--------------------------------------------------------------------------
| Build DSN
|--------------------------------------------------------------------------
*/

$dsn =
    'mysql:' .
    'host=' . $host .
    ';port=' . $port .
    ';dbname=' . $dbname .
    ';charset=utf8mb4';


/*
|--------------------------------------------------------------------------
| Connect
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        $options
    );

} catch (PDOException $e) {

    error_log(
        'TiDB connection failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}


/*
|--------------------------------------------------------------------------
| Test connection
|--------------------------------------------------------------------------
*/

try {

    $pdo->query('SELECT 1');

} catch (PDOException $e) {

    error_log(
        'TiDB connection test failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}
