<?php

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';


/*
|--------------------------------------------------------------------------
| ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = '';

$email = '';


/*
|--------------------------------------------------------------------------
| LOGIN SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $error = 'Please enter your email address.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif ($password === '') {

        $error = 'Please enter your password.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | FIND ADMIN
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | VERIFY PASSWORD
        |--------------------------------------------------------------------------
        */

        if (
            $admin &&
            password_verify($password, $admin['password_hash'])
        ) {

            /*
            |--------------------------------------------------------------------------
            | REGENERATE SESSION ID
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);


            /*
            |--------------------------------------------------------------------------
            | CREATE ADMIN SESSION
            |--------------------------------------------------------------------------
            */

            $_SESSION['admin_id'] = (int) $admin['id'];

            $_SESSION['admin_email'] = $admin['email'];

            $_SESSION['admin_logged_in'] = true;


            /*
            |--------------------------------------------------------------------------
            | REDIRECT TO DASHBOARD
            |--------------------------------------------------------------------------
            */

            header('Location: index.php');
            exit;

        } else {

            $error = 'Invalid email or password.';

        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Login | <?php echo escape(SITE_NAME); ?>
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                #0b0f19;

            color: #ffffff;

        }


        .login-wrapper {

            width: 100%;

            max-width: 430px;

        }


        .login-card {

            background: #111827;

            border: 1px solid #263244;

            border-radius: 18px;

            padding: 40px;

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.45);

        }


        .logo {

            text-align: center;

            margin-bottom: 30px;

        }


        .logo h1 {

            margin: 0;

            font-size: 30px;

            font-weight: 800;

            letter-spacing: -1px;

        }


        .logo h1 span {

            color: #38bdf8;

        }


        .logo p {

            margin: 8px 0 0;

            color: #94a3b8;

            font-size: 14px;

        }


        .form-group {

            margin-bottom: 20px;

        }


        label {

            display: block;

            margin-bottom: 8px;

            color: #e2e8f0;

            font-size: 14px;

            font-weight: 600;

        }


        input {

            width: 100%;

            padding: 14px 15px;

            border-radius: 9px;

            border: 1px solid #334155;

            background: #0f172a;

            color: #ffffff;

            font-size: 15px;

            outline: none;

            transition: 0.2s ease;

        }


        input:focus {

            border-color: #38bdf8;

            box-shadow:
                0 0 0 3px rgba(56, 189, 248, 0.12);

        }


        .login-button {

            width: 100%;

            border: 0;

            border-radius: 9px;

            padding: 14px 18px;

            background: #38bdf8;

            color: #07111f;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s ease;

        }


        .login-button:hover {

            transform: translateY(-1px);

            background: #7dd3fc;

        }


        .error-message {

            margin-bottom: 20px;

            padding: 13px 15px;

            border-radius: 9px;

            background: rgba(239, 68, 68, 0.12);

            border: 1px solid rgba(239, 68, 68, 0.3);

            color: #fca5a5;

            font-size: 14px;

        }


        .login-footer {

            text-align: center;

            margin-top: 25px;

            color: #64748b;

            font-size: 12px;

        }


        @media (max-width: 500px) {

            .login-card {

                padding: 30px 22px;

            }

        }

    </style>

</head>


<body>


<div class="login-wrapper">


    <div class="login-card">


        <div class="logo">

            <h1>
                Hello <span>Biz IT</span>
            </h1>

            <p>
                Administrator Login
            </p>

        </div>


        <?php if ($error !== ''): ?>

            <div class="error-message">

                <?php echo escape($error); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
        >


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo escape($email); ?>"
                    placeholder="Enter admin email"
                    autocomplete="email"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                Login to Admin Panel
            </button>


        </form>


        <div class="login-footer">

            <?php echo escape(SITE_NAME); ?>

            &copy;

            <?php echo date('Y'); ?>

        </div>


    </div>


</div>


</body>

</html>