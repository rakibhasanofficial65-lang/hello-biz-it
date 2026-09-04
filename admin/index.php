<?php

/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['admin_logged_in']) ||
    empty($_SESSION['admin_id'])
) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DASHBOARD DATA
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| TOTAL MESSAGES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM contact_messages
");

$totalMessages = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| UNREAD MESSAGES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM contact_messages
    WHERE status = 'unread'
");

$unreadMessages = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| TOTAL APPLICATIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM career_applications
");

$totalApplications = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| PENDING APPLICATIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM career_applications
    WHERE status = 'pending'
");

$pendingApplications = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| RECENT CONTACT MESSAGES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        name,
        email,
        phone,
        service,
        subject,
        message,
        status,
        created_at
    FROM contact_messages
    ORDER BY created_at DESC
    LIMIT 10
");

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| RECENT CAREER APPLICATIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        ca.id,
        ca.full_name,
        ca.email,
        ca.phone,
        ca.education,
        ca.experience,
        ca.status,
        ca.created_at,
        j.title AS job_title
    FROM career_applications ca
    LEFT JOIN jobs j
        ON ca.job_id = j.id
    ORDER BY ca.created_at DESC
    LIMIT 10
");

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Admin Dashboard | <?php echo escape(SITE_NAME); ?>
    </title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            margin: 0;

            background: #080d18;

            color: #e8eef8;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            line-height: 1.5;

        }


        a {

            color: inherit;

            text-decoration: none;

        }


        button,
        input,
        select,
        textarea {

            font-family: inherit;

        }


        /* =====================================================
           ADMIN HEADER
        ===================================================== */

        .admin-header {

            width: 100%;

            height: 72px;

            background: #101827;

            border-bottom: 1px solid #26344c;

            position: sticky;

            top: 0;

            z-index: 1000;

        }


        .admin-header-inner {

            width: min(1400px, calc(100% - 40px));

            height: 100%;

            margin: 0 auto;

            display: flex;

            align-items: center;

            gap: 25px;

        }


        /* =====================================================
           LOGO
        ===================================================== */

        .admin-logo {

            flex-shrink: 0;

            display: inline-flex;

            align-items: center;

            font-size: 23px;

            font-weight: 800;

            letter-spacing: -0.5px;

            color: #ffffff;

        }


        .logo-hello {

            color: #ffffff;

        }


        .logo-biz {

            color: #38bdf8;

            margin-left: 5px;

        }


        .logo-it {

            color: #ffffff;

        }


        /* =====================================================
           NAVIGATION
        ===================================================== */

        .admin-nav {

            display: flex;

            align-items: center;

            gap: 4px;

            flex: 1;

            margin-left: 10px;

        }


        .admin-nav a {

            position: relative;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 40px;

            padding: 0 15px;

            border-radius: 8px;

            color: #8fa4c4;

            font-size: 13px;

            font-weight: 600;

            transition:
                background .2s ease,
                color .2s ease;

        }


        .admin-nav a:hover {

            color: #ffffff;

            background: #182338;

        }


        .admin-nav a.active {

            color: #ffffff;

            background: #1a2940;

        }


        .admin-nav a.active::after {

            content: "";

            position: absolute;

            left: 14px;

            right: 14px;

            bottom: 3px;

            height: 2px;

            border-radius: 20px;

            background: #38bdf8;

        }


        /* =====================================================
           ACCOUNT
        ===================================================== */

        .admin-account {

            display: flex;

            align-items: center;

            gap: 15px;

            flex-shrink: 0;

        }


        .admin-email {

            max-width: 240px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: #8fa4c4;

            font-size: 13px;

        }


        .admin-logout {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 8px 14px;

            border: 1px solid #35445d;

            border-radius: 7px;

            color: #d4deed;

            font-size: 12px;

            font-weight: 600;

            transition:
                background .2s ease,
                border-color .2s ease,
                color .2s ease;

        }


        .admin-logout:hover {

            background: #1b2a42;

            border-color: #4b607f;

            color: #ffffff;

        }


        /* =====================================================
           MAIN
        ===================================================== */

        .admin-main {

            width: min(1400px, calc(100% - 40px));

            margin: 0 auto;

            padding: 38px 0 70px;

        }


        /* =====================================================
           DASHBOARD HEADING
        ===================================================== */

        .dashboard-heading {

            margin-bottom: 28px;

        }


        .dashboard-heading small {

            display: block;

            margin-bottom: 7px;

            color: #4e9fd0;

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 1.4px;

            text-transform: uppercase;

        }


        .dashboard-heading h1 {

            margin: 0 0 8px;

            color: #ffffff;

            font-size: 32px;

            line-height: 1.2;

        }


        .dashboard-heading p {

            margin: 0;

            color: #7185a5;

            font-size: 14px;

        }


        /* =====================================================
           STATISTICS
        ===================================================== */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 34px;

        }


        .stat-card {

            min-height: 130px;

            padding: 22px;

            background: #111a2b;

            border: 1px solid #273650;

            border-radius: 13px;

        }


        .stat-card.unread {

            border-color: rgba(255, 105, 105, .32);

        }


        .stat-card.pending {

            border-color: rgba(255, 200, 87, .32);

        }


        .stat-label {

            margin-bottom: 13px;

            color: #8ca3c7;

            font-size: 13px;

            font-weight: 500;

        }


        .stat-number {

            color: #ffffff;

            font-size: 32px;

            font-weight: 800;

            line-height: 1;

        }


        /* =====================================================
           SECTION
        ===================================================== */

        .dashboard-section {

            margin-top: 32px;

        }


        .section-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 14px;

        }


        .section-header h2 {

            margin: 0;

            color: #ffffff;

            font-size: 19px;

            font-weight: 700;

        }


        .section-header span {

            color: #667b9f;

            font-size: 12px;

        }


        .section-link {

            color: #4fbfff;

            font-size: 12px;

            font-weight: 600;

        }


        .section-link:hover {

            color: #8bd7ff;

        }


        /* =====================================================
           TABLE CARD
        ===================================================== */

        .table-wrapper {

            width: 100%;

            overflow-x: auto;

            background: #101827;

            border: 1px solid #26354e;

            border-radius: 13px;

        }


        table {

            width: 100%;

            min-width: 950px;

            border-collapse: collapse;

        }


        th {

            padding: 14px 16px;

            background: #0d1626;

            border-bottom: 1px solid #26354e;

            color: #86a2ca;

            text-align: left;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: .8px;

            text-transform: uppercase;

            white-space: nowrap;

        }


        td {

            padding: 16px;

            border-bottom: 1px solid #202d42;

            color: #d7e1ef;

            font-size: 13px;

            vertical-align: top;

        }


        tbody tr {

            transition: background .15s ease;

        }


        tbody tr:hover {

            background: #121d2e;

        }


        tbody tr:last-child td {

            border-bottom: none;

        }


        /* =====================================================
           APPLICANT / NAME
        ===================================================== */

        .name {

            color: #ffffff;

            font-weight: 700;

        }


        .email {

            margin-top: 4px;

            color: #7892b9;

            font-size: 12px;

        }


        .message-preview {

            max-width: 320px;

            color: #8da1c0;

            line-height: 1.55;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status {

            display: inline-flex;

            align-items: center;

            padding: 5px 9px;

            border-radius: 6px;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: .3px;

            text-transform: uppercase;

            white-space: nowrap;

        }


        .status-unread {

            background: rgba(255, 124, 136, .12);

            color: #ff8993;

        }


        .status-read {

            background: rgba(103, 183, 255, .12);

            color: #67b7ff;

        }


        .status-replied {

            background: rgba(82, 220, 155, .12);

            color: #52dc9b;

        }


        .status-pending {

            background: rgba(255, 200, 87, .12);

            color: #ffc857;

        }


        .status-reviewed {

            background: rgba(103, 183, 255, .12);

            color: #67b7ff;

        }


        .status-shortlisted {

            background: rgba(82, 220, 155, .12);

            color: #52dc9b;

        }


        .status-rejected {

            background: rgba(255, 124, 136, .12);

            color: #ff7c88;

        }


        /* =====================================================
           DATE
        ===================================================== */

        .date {

            color: #7187aa;

            white-space: nowrap;

            font-size: 12px;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {

            padding: 48px 20px;

            color: #7186a8;

            text-align: center;

            font-size: 13px;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .admin-header-inner {

                width: min(100% - 30px, 1400px);

                gap: 15px;

            }


            .admin-nav {

                margin-left: 0;

            }


            .admin-nav a {

                padding-left: 11px;

                padding-right: 11px;

            }


            .admin-email {

                display: none;

            }


            .admin-main {

                width: min(100% - 30px, 1400px);

            }

        }


        @media (max-width: 850px) {

            .admin-header {

                height: auto;

            }


            .admin-header-inner {

                min-height: 70px;

                flex-wrap: wrap;

                padding: 12px 0;

            }


            .admin-nav {

                order: 3;

                width: 100%;

                overflow-x: auto;

                padding-bottom: 2px;

            }


            .admin-nav a {

                flex-shrink: 0;

            }


            .admin-account {

                margin-left: auto;

            }


            .stats-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

            }

        }


        @media (max-width: 600px) {

            .admin-header-inner {

                width: calc(100% - 24px);

            }


            .admin-main {

                width: calc(100% - 24px);

                padding-top: 28px;

            }


            .admin-logo {

                font-size: 20px;

            }


            .admin-account {

                gap: 8px;

            }


            .admin-logout {

                padding: 7px 10px;

            }


            .stats-grid {

                grid-template-columns: 1fr;

            }


            .dashboard-heading h1 {

                font-size: 27px;

            }


            .section-header {

                align-items: flex-start;

                flex-direction: column;

                gap: 5px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     ADMIN HEADER
========================================================= -->

<header class="admin-header">

    <div class="admin-header-inner">


        <!-- LOGO -->

        <a
            href="index.php"
            class="admin-logo"
        >

            <span class="logo-hello">
                Hello
            </span>

            <span class="logo-biz">
                Biz
            </span>

            <span class="logo-it">
                IT
            </span>

        </a>


        <!-- NAVIGATION -->

        <nav class="admin-nav">


            <a
                href="index.php"
                class="active"
            >
                Dashboard
            </a>


            <a
                href="messages.php"
            >
                Messages
            </a>


            <a
                href="applications.php"
            >
                Applications
            </a>


        </nav>


        <!-- ADMIN ACCOUNT -->

        <div class="admin-account">


            <span class="admin-email">

                <?php
                echo escape(
                    $_SESSION['admin_email'] ?? 'Admin'
                );
                ?>

            </span>


            <a
                href="logout.php"
                class="admin-logout"
            >
                Logout
            </a>


        </div>


    </div>

</header>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="admin-main">


    <!-- PAGE HEADING -->

    <div class="dashboard-heading">

        <small>
            ADMIN PANEL
        </small>

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Manage contact messages and career applications.
        </p>

    </div>


    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="stats-grid">


        <!-- TOTAL MESSAGES -->

        <div class="stat-card">

            <div class="stat-label">
                Total Messages
            </div>

            <div class="stat-number">
                <?php echo $totalMessages; ?>
            </div>

        </div>


        <!-- UNREAD -->

        <div class="stat-card unread">

            <div class="stat-label">
                Unread Messages
            </div>

            <div class="stat-number">
                <?php echo $unreadMessages; ?>
            </div>

        </div>


        <!-- TOTAL APPLICATIONS -->

        <div class="stat-card">

            <div class="stat-label">
                Total Applications
            </div>

            <div class="stat-number">
                <?php echo $totalApplications; ?>
            </div>

        </div>


        <!-- PENDING -->

        <div class="stat-card pending">

            <div class="stat-label">
                Pending Applications
            </div>

            <div class="stat-number">
                <?php echo $pendingApplications; ?>
            </div>

        </div>


    </div>


    <!-- =====================================================
         CONTACT MESSAGES
    ====================================================== -->

    <section class="dashboard-section">


        <div class="section-header">

            <h2>
                Contact Messages
            </h2>


            <div>

                <span>
                    Latest 10 messages
                </span>

                &nbsp;

                <a
                    href="messages.php"
                    class="section-link"
                >
                    View All →
                </a>

            </div>

        </div>


        <div class="table-wrapper">


            <?php if (!empty($messages)): ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                Name
                            </th>

                            <th>
                                Service
                            </th>

                            <th>
                                Subject
                            </th>

                            <th>
                                Message
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($messages as $msg): ?>


                        <tr>


                            <!-- NAME -->

                            <td>

                                <div class="name">

                                    <?php
                                    echo escape(
                                        $msg['name']
                                    );
                                    ?>

                                </div>


                                <div class="email">

                                    <?php
                                    echo escape(
                                        $msg['email']
                                    );
                                    ?>

                                </div>


                                <?php if (!empty($msg['phone'])): ?>

                                    <div class="email">

                                        <?php
                                        echo escape(
                                            $msg['phone']
                                        );
                                        ?>

                                    </div>

                                <?php endif; ?>


                            </td>


                            <!-- SERVICE -->

                            <td>

                                <?php
                                echo escape(
                                    $msg['service']
                                );
                                ?>

                            </td>


                            <!-- SUBJECT -->

                            <td>

                                <?php

                                echo !empty($msg['subject'])
                                    ? escape($msg['subject'])
                                    : '—';

                                ?>

                            </td>


                            <!-- MESSAGE -->

                            <td>

                                <div class="message-preview">

                                    <?php

                                    echo escape(
                                        mb_strimwidth(
                                            $msg['message'],
                                            0,
                                            120,
                                            '...'
                                        )
                                    );

                                    ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status status-<?php echo escape($msg['status']); ?>"
                                >

                                    <?php
                                    echo escape(
                                        $msg['status']
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- DATE -->

                            <td class="date">

                                <?php

                                echo date(
                                    'd M Y, h:i A',
                                    strtotime(
                                        $msg['created_at']
                                    )
                                );

                                ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="empty">

                    No contact messages yet.

                </div>


            <?php endif; ?>


        </div>


    </section>


    <!-- =====================================================
         CAREER APPLICATIONS
    ====================================================== -->

    <section class="dashboard-section">


        <div class="section-header">

            <h2>
                Career Applications
            </h2>


            <div>

                <span>
                    Latest 10 applications
                </span>

                &nbsp;

                <a
                    href="applications.php"
                    class="section-link"
                >
                    View All →
                </a>

            </div>

        </div>


        <div class="table-wrapper">


            <?php if (!empty($applications)): ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                Applicant
                            </th>

                            <th>
                                Position
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Education
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($applications as $application): ?>


                        <tr>


                            <!-- APPLICANT -->

                            <td>

                                <div class="name">

                                    <?php
                                    echo escape(
                                        $application['full_name']
                                    );
                                    ?>

                                </div>


                                <div class="email">

                                    <?php
                                    echo escape(
                                        $application['email']
                                    );
                                    ?>

                                </div>

                            </td>


                            <!-- POSITION -->

                            <td>

                                <?php

                                echo escape(
                                    $application['job_title']
                                    ?? 'Position unavailable'
                                );

                                ?>

                            </td>


                            <!-- PHONE -->

                            <td>

                                <?php

                                echo escape(
                                    $application['phone']
                                );

                                ?>

                            </td>


                            <!-- EDUCATION -->

                            <td>

                                <?php

                                echo escape(
                                    $application['education']
                                );

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status status-<?php echo escape($application['status']); ?>"
                                >

                                    <?php

                                    echo escape(
                                        $application['status']
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- DATE -->

                            <td class="date">

                                <?php

                                echo date(
                                    'd M Y, h:i A',
                                    strtotime(
                                        $application['created_at']
                                    )
                                );

                                ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="empty">

                    No career applications yet.

                </div>


            <?php endif; ?>


        </div>


    </section>


</main>


</body>

</html>