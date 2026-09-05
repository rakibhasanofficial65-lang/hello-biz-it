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
        || (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
        );

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

define('SITE_NAME', 'Hello Biz IT');

define(
    'SITE_URL',
    'https://hello-biz-it.vercel.app'
);

define(
    'BASE_URL',
    'https://hello-biz-it.vercel.app'
);

date_default_timezone_set('Asia/Dhaka');


/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/

$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT') ?: '4000';
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');


/*
|--------------------------------------------------------------------------
| CHECK DATABASE ENVIRONMENT VARIABLES
|--------------------------------------------------------------------------
*/

if (
    empty($dbHost) ||
    empty($dbName) ||
    empty($dbUser) ||
    empty($dbPass)
) {

    error_log(
        'TiDB environment variables are missing.'
    );

    die('Database configuration error.');
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
            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
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
