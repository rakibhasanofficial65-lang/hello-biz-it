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
| TIMEZONE
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Dhaka');


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
    'SITE_URL',
    getenv('SITE_URL') ?: 'https://hello-biz-it.vercel.app'
);

/*
| Old code compatibility
*/

define(
    'BASE_URL',
    getenv('BASE_URL') ?: SITE_URL
);


/*
|--------------------------------------------------------------------------
| SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ||
        (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
            &&
            strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
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
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
|
| Vercel Environment Variables:
|
| DB_HOST
| DB_PORT
| DB_NAME
| DB_USER
| DB_PASSWORD
|
|--------------------------------------------------------------------------
*/

$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT') ?: '4000';
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');


/*
|--------------------------------------------------------------------------
| CHECK DATABASE VARIABLES
|--------------------------------------------------------------------------
*/

if (
    !$dbHost ||
    !$dbName ||
    !$dbUser ||
    !$dbPass
) {

    error_log(
        'TiDB environment variables are missing.'
    );

    die(
        'Database configuration is missing. ' .
        'Please check DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASSWORD ' .
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
        'mysql:host=' . $dbHost .
        ';port=' . $dbPort .
        ';dbname=' . $dbName .
        ';charset=utf8mb4';


    /*
    |--------------------------------------------------------------------------
    | PDO OPTIONS
    |--------------------------------------------------------------------------
    */

    $pdoOptions = [

        PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE =>
            PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES =>
            false
    ];


    /*
    |--------------------------------------------------------------------------
    | PHP 8.5+
    |--------------------------------------------------------------------------
    |
    | Avoid deprecated:
    | PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
    |
    */

    if (
        class_exists('Pdo\\Mysql') &&
        defined('Pdo\\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
    ) {

        $pdoOptions[
            constant('Pdo\\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
        ] = false;
    }


    /*
    |--------------------------------------------------------------------------
    | OLD PHP COMPATIBILITY
    |--------------------------------------------------------------------------
    */

    elseif (
        defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')
    ) {

        $pdoOptions[
            constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')
        ] = false;
    }


    /*
    |--------------------------------------------------------------------------
    | CONNECT TO TIDB CLOUD
    |--------------------------------------------------------------------------
    */

    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        $pdoOptions
    );


    /*
    |--------------------------------------------------------------------------
    | DATABASE CONNECTION SUCCESS
    |--------------------------------------------------------------------------
    */

    error_log(
        'TiDB connection successful.'
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
