
<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TiDB Cloud + Vercel Database Connection
|--------------------------------------------------------------------------
*/

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

/*
|--------------------------------------------------------------------------
| Fallback / validation
|--------------------------------------------------------------------------
*/

$host = is_string($host) && $host !== ''
    ? $host
    : '';

$port = is_string($port) && $port !== ''
    ? $port
    : '4000';

$dbname = is_string($dbname) && $dbname !== ''
    ? $dbname
    : '';

$username = is_string($username) && $username !== ''
    ? $username
    : '';

$password = is_string($password)
    ? $password
    : '';

/*
|--------------------------------------------------------------------------
| Check required variables
|--------------------------------------------------------------------------
*/

if (
    $host === '' ||
    $dbname === '' ||
    $username === '' ||
    $password === ''
) {
    error_log(
        'Database configuration error: required environment variable is missing.'
    );

    die(
        'Database configuration error. Please contact the administrator.'
    );
}

/*
|--------------------------------------------------------------------------
| DSN
|--------------------------------------------------------------------------
*/

$dsn =
    'mysql:host=' . $host .
    ';port=' . $port .
    ';dbname=' . $dbname .
    ';charset=utf8mb4';

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
| Vercel normally has the system CA bundle.
|
*/

$caFiles = [
    '/etc/ssl/certs/ca-certificates.crt',
    '/etc/ssl/cert.pem',
];

$caFound = false;

foreach ($caFiles as $caFile) {

    if (is_file($caFile) && is_readable($caFile)) {

        $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;

        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        $caFound = true;

        break;
    }
}

if (!$caFound) {

    error_log(
        'Database SSL configuration error: CA certificate not found.'
    );

    die(
        'Database SSL configuration error. Please contact the administrator.'
    );
}

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

    error_log('TiDB connection successful.');

} catch (PDOException $e) {

    error_log(
        'TiDB connection failed: ' . $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}
