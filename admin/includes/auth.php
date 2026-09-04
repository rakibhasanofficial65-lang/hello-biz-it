<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ADMIN SESSION CHECK
|--------------------------------------------------------------------------
*/

function is_admin_logged_in()
{
    return !empty($_SESSION['admin_id']);
}


/*
|--------------------------------------------------------------------------
| REMEMBER LOGIN
|--------------------------------------------------------------------------
*/

function attempt_remember_login()
{
    global $pdo;

    if (is_admin_logged_in()) {
        return true;
    }

    if (empty($_COOKIE['hello_biz_admin'])) {
        return false;
    }

    $token = $_COOKIE['hello_biz_admin'];

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return false;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("
        SELECT
            id,
            email
        FROM admin_users
        WHERE remember_token_hash = ?
          AND remember_expires_at > NOW()
        LIMIT 1
    ");

    $stmt->execute([$tokenHash]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {

        setcookie(
            'hello_biz_admin',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN SESSION
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];

    return true;
}


/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN LOGIN
|--------------------------------------------------------------------------
*/

function require_admin()
{
    if (is_admin_logged_in()) {
        return;
    }

    if (attempt_remember_login()) {
        return;
    }

    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

function admin_login($email, $password, $remember = true)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            id,
            email,
            password_hash
        FROM admin_users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        return false;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | SESSION
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];


    /*
    |--------------------------------------------------------------------------
    | REMEMBER DEVICE
    |--------------------------------------------------------------------------
    */

    if ($remember) {

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $expires = time() + (60 * 60 * 24 * 30);

        $stmt = $pdo->prepare("
            UPDATE admin_users
            SET
                remember_token_hash = ?,
                remember_expires_at = FROM_UNIXTIME(?)
            WHERE id = ?
        ");

        $stmt->execute([
            $tokenHash,
            $expires,
            $admin['id']
        ]);


        setcookie(
            'hello_biz_admin',
            $token,
            [
                'expires' => $expires,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

function admin_logout()
{
    global $pdo;

    if (!empty($_SESSION['admin_id'])) {

        $stmt = $pdo->prepare("
            UPDATE admin_users
            SET
                remember_token_hash = NULL,
                remember_expires_at = NULL
            WHERE id = ?
        ");

        $stmt->execute([
            $_SESSION['admin_id']
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION = [];


    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();


    /*
    |--------------------------------------------------------------------------
    | CLEAR REMEMBER COOKIE
    |--------------------------------------------------------------------------
    */

    setcookie(
        'hello_biz_admin',
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}