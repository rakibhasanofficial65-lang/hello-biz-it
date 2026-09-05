<?php

/*
|--------------------------------------------------------------------------
| SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    $isHttps = (
        !empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off'
    );

    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30, // 30 days
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

// আপনার existing config.php-এর বাকি code এখানে থাকবে
