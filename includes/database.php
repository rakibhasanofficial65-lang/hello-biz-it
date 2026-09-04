<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TiDB Cloud / Vercel Database Connection
|--------------------------------------------------------------------------
|
| Required environment variables:
|
| DB_HOST
| DB_PORT
| DB_NAME
| DB_USER
| DB_PASSWORD
|
| Optional:
|
| DB_SSL_CA
| TIDB_CA_CERT
|
|--------------------------------------------------------------------------
*/

$host = trim((string) (getenv('DB_HOST') ?: ''));
$port = trim((string) (getenv('DB_PORT') ?: '4000'));
$dbname = trim((string) (getenv('DB_NAME') ?: ''));
$username = trim((string) (getenv('DB_USER') ?: ''));
$password = (string) (getenv('DB_PASSWORD') ?: '');


/*
|--------------------------------------------------------------------------
| Validate required environment variables
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
        'Database configuration missing: ' .
        implode(', ', $missing)
    );

    die(
        'Database configuration error. Please contact the administrator.'
    );
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
    PDO::ATTR_STRINGIFY_FETCHES  => false,
];


/*
|--------------------------------------------------------------------------
| TiDB Cloud TLS / CA Certificate
|--------------------------------------------------------------------------
|
| TiDB Cloud Public Endpoint requires TLS.
|
| We support two methods:
|
| 1. DB_SSL_CA
|    A filesystem path to a CA certificate.
|
| 2. TIDB_CA_CERT
|    The actual PEM certificate stored as a Vercel
|    environment variable.
|
| TIDB_CA_CERT is recommended for Vercel.
|
|--------------------------------------------------------------------------
*/


$caFile = '';

/*
|--------------------------------------------------------------------------
| Method 1: DB_SSL_CA as a filesystem path
|--------------------------------------------------------------------------
*/

$dbSslCa = trim((string) (getenv('DB_SSL_CA') ?: ''));

if ($dbSslCa !== '' && is_file($dbSslCa)) {

    $caFile = $dbSslCa;
}


/*
|--------------------------------------------------------------------------
| Method 2: TIDB_CA_CERT as PEM content
|--------------------------------------------------------------------------
|
| Example:
|
| TIDB_CA_CERT=-----BEGIN CERTIFICATE-----
| ...
| -----END CERTIFICATE-----
|
| Vercel does not need the certificate to exist permanently on disk.
| We create it inside /tmp during the request.
|--------------------------------------------------------------------------
*/

if ($caFile === '') {

    $caContent = (string) (getenv('TIDB_CA_CERT') ?: '');

    if (trim($caContent) !== '') {

        /*
         * Normalize escaped newlines.
         */
        $caContent = str_replace(
            ["\\r\\n", "\\n", "\\r"],
            PHP_EOL,
            $caContent
        );

        /*
         * Create a temporary CA file.
         */
        $caFile = '/tmp/tidb-ca-' . md5($host) . '.pem';

        /*
         * Write certificate only if it does not already exist.
         */
        if (!is_file($caFile)) {

            $written = file_put_contents(
                $caFile,
                $caContent,
                LOCK_EX
            );

            if ($written === false) {

                error_log(
                    'Unable to create TiDB CA certificate file.'
                );

                die(
                    'Database SSL configuration error. Please contact the administrator.'
                );
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Enable TLS
|--------------------------------------------------------------------------
*/

if ($caFile !== '') {

    $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;

    /*
     * Verify TiDB Cloud server certificate.
     */
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {

        $options[
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
        ] = true;
    }

} else {

    /*
     * Do not silently continue with an unverified connection.
     *
     * TiDB Cloud Public Endpoint requires TLS.
     */
    error_log(
        'TiDB SSL CA certificate is not configured.'
    );

    die(
        'Database SSL configuration error. Please contact the administrator.'
    );
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
| Connect to TiDB Cloud
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

    /*
     * Log the real error.
     * Never expose credentials or connection details to visitors.
     */
    error_log(
        'TiDB database connection failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}


/*
|--------------------------------------------------------------------------
| Optional connection verification
|--------------------------------------------------------------------------
|
| This confirms that PDO successfully connected.
|--------------------------------------------------------------------------
*/

try {

    $pdo->query('SELECT 1');

} catch (PDOException $e) {

    error_log(
        'TiDB connection verification failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. Please try again later.'
    );
}
