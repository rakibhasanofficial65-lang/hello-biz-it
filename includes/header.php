<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Hello Biz IT - Technology, Digital Marketing, Data and Creative Solutions for Business Growth."
    >

    <title>
        <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Hello Biz IT'; ?>
    </title>


    <!-- ==========================================
         GOOGLE FONTS
    =========================================== -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- ==========================================
         MAIN WEBSITE CSS
    =========================================== -->

<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/responsive.css">
<link rel="stylesheet" href="/assets/css/animations.css">

</head>


<body>


<!-- ==========================================
     HEADER / NAVBAR
=========================================== -->

<header
    class="site-header"
    id="siteHeader"
>

    <div class="container navbar">


        <!-- ======================================
             TEXT LOGO
        ======================================= -->

        <a
            href="/index.php"
            class="brand-logo"
            aria-label="Hello Biz IT Home"
        >

            <span class="logo-hello">Hello</span>
            <span class="logo-biz">Biz</span>
            <span class="logo-it">IT</span>

        </a>


        <!-- ======================================
             DESKTOP NAVIGATION
        ======================================= -->

        <nav
            class="main-nav"
            id="mainNav"
            aria-label="Main Navigation"
        >

            <a
               href="/index.php"
                class="nav-link"
            >
                Home
            </a>

            <a
                href="/about.php"
                class="nav-link"
            >
                About
            </a>

            <a
                href="/services.php"
                class="nav-link"
            >
                Services
            </a>

            <a
                href="/career.php"
                class="nav-link"
            >
                Career
            </a>

            <a
                href="/contact.php"
                class="nav-link"
            >
                Contact
            </a>

        </nav>


        <!-- ======================================
             CONTACT BUTTON
        ======================================= -->

        <a
            href="/contact.php"
            class="nav-contact-btn"
        >

            Contact Us

            <span>→</span>

        </a>


        <!-- ======================================
             MOBILE MENU BUTTON
        ======================================= -->

        <button
            class="menu-toggle"
            id="menuToggle"
            type="button"
            aria-label="Open Menu"
            aria-expanded="false"
        >

            <span></span>
            <span></span>
            <span></span>

        </button>

    </div>

</header>


<!-- ==========================================
     MAIN CONTENT START
=========================================== -->

<main>
