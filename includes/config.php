<?php

/*
|--------------------------------------------------------------------------
| ERROR REPORTING
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


/*
|--------------------------------------------------------------------------
| SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}


/*
|--------------------------------------------------------------------------
| SITE CONFIGURATION
|--------------------------------------------------------------------------
*/

define(
    'SITE_NAME',
    getenv('SITE_NAME') ?: 'Hello Biz IT'
);

define(
    'BASE_URL',
    getenv('BASE_URL') ?: ''
);


/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/

$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT') ?: '4000';
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USERNAME');
$dbPass = getenv('DB_PASSWORD');


/*
|--------------------------------------------------------------------------
| CHECK DATABASE ENVIRONMENT VARIABLES
|--------------------------------------------------------------------------
*/

if (!$dbHost || !$dbName || !$dbUser || !$dbPass) {

    die(
        'Database configuration is missing. ' .
        'Please check DB_HOST, DB_NAME, DB_USERNAME and DB_PASSWORD ' .
        'in Vercel Environment Variables.'
    );
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

try {

    $dsn =
        "mysql:host={$dbHost};" .
        "port={$dbPort};" .
        "dbname={$dbName};" .
        "charset=utf8mb4";


    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
                false,

            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT =>
                false
        ]
    );


} catch (PDOException $e) {

    error_log(
        'Database connection failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. ' .
        'Please check your TiDB Cloud configuration.'
    );
}
