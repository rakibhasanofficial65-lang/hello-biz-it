<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

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
        <?php echo isset($page_title) ? escape($page_title) : 'Admin Panel | ' . SITE_NAME; ?>
    </title>

    <link
    rel="stylesheet"
    href="<?php echo SITE_URL; ?>/admin/assets/admin.css"
>

</head>

<body>

<header class="admin-header">

    <div class="admin-header-inner">

        <!-- LOGO -->

        <a
            href="<?php echo SITE_URL; ?>/admin/index.php"
            class="admin-logo"
        >
            <span class="logo-hello">Hello</span>
            <span class="logo-biz">Biz</span>
            <span class="logo-it">IT</span>
        </a>


        <!-- NAVIGATION -->

        <nav class="admin-nav">

            <a
                href="<?php echo SITE_URL; ?>/admin/index.php"
                class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>"
            >
                Dashboard
            </a>

            <a
                href="<?php echo SITE_URL; ?>/admin/messages.php"
                class="<?php echo in_array($currentPage, ['messages.php', 'view-message.php'], true) ? 'active' : ''; ?>"
            >
                Messages
            </a>

            <a
                href="<?php echo SITE_URL; ?>/admin/applications.php"
                class="<?php echo in_array($currentPage, ['applications.php', 'view-application.php'], true) ? 'active' : ''; ?>"
            >
                Applications
            </a>

        </nav>


        <!-- ADMIN ACCOUNT -->

        <div class="admin-account">

            <span class="admin-email">
                <?php echo escape($_SESSION['admin_email'] ?? 'Admin'); ?>
            </span>

            <a
                href="<?php echo SITE_URL; ?>/admin/logout.php"
                class="admin-logout"
            >
                Logout
            </a>

        </div>

    </div>

</header>


<main class="admin-main">