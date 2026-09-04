<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';

$page_title = "Messages | Admin";


/*
|--------------------------------------------------------------------------
| ADMIN APPLICATIONS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';

session_start();

/*
|--------------------------------------------------------------------------
| ADMIN AUTH CHECK
|--------------------------------------------------------------------------
|
| Make sure your login.php creates:
| $_SESSION['admin_id']
|
*/

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE APPLICATION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $application_id = isset($_POST['application_id'])
        ? (int) $_POST['application_id']
        : 0;


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete' && $application_id > 0) {

        try {

            /*
            | Get uploaded files first
            */

            $stmt = $pdo->prepare("
                SELECT photo_file, cv_file
                FROM career_applications
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$application_id]);

            $application = $stmt->fetch(PDO::FETCH_ASSOC);


            /*
            | Delete database record
            */

            if ($application) {

                $delete = $pdo->prepare("
                    DELETE FROM career_applications
                    WHERE id = ?
                ");

                $delete->execute([$application_id]);


                /*
                | Delete photo
                */

                if (!empty($application['photo_file'])) {

                    $photoPath = dirname(__DIR__) . '/' .
                        ltrim($application['photo_file'], '/');

                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }


                /*
                | Delete CV
                */

                if (!empty($application['cv_file'])) {

                    $cvPath = dirname(__DIR__) . '/' .
                        ltrim($application['cv_file'], '/');

                    if (file_exists($cvPath)) {
                        unlink($cvPath);
                    }
                }
            }

            header('Location: applications.php?deleted=1');
            exit;

        } catch (Throwable $e) {

            error_log($e->getMessage());

            header('Location: applications.php?error=1');
            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGE STATUS
    |--------------------------------------------------------------------------
    */

    if ($action === 'status' && $application_id > 0) {

        $status = $_POST['status'] ?? '';

        $allowedStatuses = [
            'pending',
            'reviewed',
            'shortlisted',
            'rejected'
        ];

        if (in_array($status, $allowedStatuses, true)) {

            $stmt = $pdo->prepare("
                UPDATE career_applications
                SET status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $status,
                $application_id
            ]);
        }

        header('Location: applications.php');
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| VIEW APPLICATION
|--------------------------------------------------------------------------
*/

$view_id = isset($_GET['view'])
    ? (int) $_GET['view']
    : 0;

$viewApplication = null;

if ($view_id > 0) {

    $stmt = $pdo->prepare("
        SELECT
            ca.*,
            j.title AS job_title,
            j.job_type,
            j.location
        FROM career_applications ca
        LEFT JOIN jobs j
            ON ca.job_id = j.id
        WHERE ca.id = ?
        LIMIT 1
    ");

    $stmt->execute([$view_id]);

    $viewApplication = $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    | Mark application as reviewed
    */

    if (
        $viewApplication &&
        $viewApplication['status'] === 'pending'
    ) {

        $update = $pdo->prepare("
            UPDATE career_applications
            SET status = 'reviewed'
            WHERE id = ?
        ");

        $update->execute([$view_id]);

        $viewApplication['status'] = 'reviewed';
    }
}


/*
|--------------------------------------------------------------------------
| GET APPLICATIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        ca.id,
        ca.full_name,
        ca.email,
        ca.phone,
        ca.education,
        ca.status,
        ca.created_at,
        ca.photo_file,
        ca.cv_file,
        j.title AS job_title,
        j.job_type,
        j.location
    FROM career_applications ca
    LEFT JOIN jobs j
        ON ca.job_id = j.id
    ORDER BY ca.created_at DESC
");

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| COUNTS
|--------------------------------------------------------------------------
*/

$totalApplications = count($applications);

$pendingApplications = 0;
$reviewedApplications = 0;
$shortlistedApplications = 0;
$rejectedApplications = 0;

foreach ($applications as $application) {

    switch ($application['status']) {

        case 'pending':
            $pendingApplications++;
            break;

        case 'reviewed':
            $reviewedApplications++;
            break;

        case 'shortlisted':
            $shortlistedApplications++;
            break;

        case 'rejected':
            $rejectedApplications++;
            break;
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
        Applications | Admin | <?php echo escape(SITE_NAME); ?>
    </title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #080d18;
            color: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .admin-header {
            height: 72px;
            border-bottom: 1px solid #263247;
            background: #101827;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
        }

        .admin-logo {
            font-size: 23px;
            font-weight: 800;
        }

        .admin-logo span {
            color: #32b8ff;
        }

        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-email {
            color: #8fa8cf;
            font-size: 14px;
        }

        .logout-btn {
            border: 1px solid #394862;
            padding: 9px 17px;
            border-radius: 7px;
            color: #fff;
        }

        .logout-btn:hover {
            background: #182238;
        }

        .admin-nav {
            background: #0e1524;
            border-bottom: 1px solid #202c40;
            padding: 0 28px;
            display: flex;
            gap: 30px;
        }

        .admin-nav a {
            padding: 16px 0;
            color: #8fa8cf;
            font-size: 14px;
        }

        .admin-nav a.active,
        .admin-nav a:hover {
            color: #fff;
        }

        .container {
            width: min(1400px, calc(100% - 30px));
            margin: auto;
        }

        .page {
            padding: 38px 0 60px;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .page-heading small {
            color: #6f91c4;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .page-heading h1 {
            margin: 8px 0;
            font-size: 34px;
        }

        .page-heading p {
            color: #7287aa;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #111a2b;
            border: 1px solid #273650;
            border-radius: 13px;
            padding: 22px;
        }

        .stat-card small {
            color: #88a8d6;
            display: block;
            margin-bottom: 12px;
        }

        .stat-card strong {
            font-size: 30px;
        }

        .stat-card.pending {
            border-color: #70591d;
        }

        .stat-card.shortlisted {
            border-color: #225f45;
        }

        .stat-card.rejected {
            border-color: #71353c;
        }

        .applications-card {
            background: #101827;
            border: 1px solid #26354e;
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 22px 24px;
            border-bottom: 1px solid #26354e;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            margin: 0;
            font-size: 19px;
        }

        .card-header span {
            color: #7287aa;
            font-size: 13px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th {
            text-align: left;
            padding: 15px 16px;
            background: #0d1626;
            color: #84a8d9;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        td {
            padding: 17px 16px;
            border-top: 1px solid #202d42;
            vertical-align: middle;
            color: #dce5f4;
            font-size: 13px;
        }

        .applicant-name {
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .applicant-email {
            color: #7896bf;
            font-size: 12px;
        }

        .job-title {
            color: #dce8ff;
            font-weight: 600;
        }

        .phone {
            color: #9eb4d5;
        }

        .date {
            color: #7890b7;
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-pending {
            color: #ffc857;
            background: rgba(255, 200, 87, .12);
        }

        .status-reviewed {
            color: #67b7ff;
            background: rgba(103, 183, 255, .12);
        }

        .status-shortlisted {
            color: #52dc9b;
            background: rgba(82, 220, 155, .12);
        }

        .status-rejected {
            color: #ff7c88;
            background: rgba(255, 124, 136, .12);
        }

        .actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .action-btn {
            border: 1px solid #33445f;
            background: #121d30;
            color: #b9cae4;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 11px;
            cursor: pointer;
        }

        .action-btn:hover {
            background: #1b2a42;
            color: #fff;
        }

        .delete-btn {
            border-color: #66343a;
            color: #ff8993;
        }

        .status-form {
            display: inline;
        }

        .status-select {
            background: #101827;
            color: #dce5f4;
            border: 1px solid #33445f;
            border-radius: 6px;
            padding: 7px;
            font-size: 11px;
        }

        /*
        |--------------------------------------------------------------------------
        | DETAIL MODAL
        |--------------------------------------------------------------------------
        */

        .detail-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .78);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            z-index: 999;
        }

        .detail-modal {
            width: min(900px, 100%);
            max-height: 90vh;
            overflow-y: auto;
            background: #101827;
            border: 1px solid #34445f;
            border-radius: 16px;
            padding: 28px;
            position: relative;
        }

        .close-detail {
            position: absolute;
            right: 20px;
            top: 18px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid #34445f;
            background: #121d30;
            color: #fff;
            cursor: pointer;
            font-size: 18px;
        }

        .detail-header {
            display: flex;
            gap: 22px;
            align-items: center;
            padding-bottom: 24px;
            border-bottom: 1px solid #293750;
        }

        .applicant-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #34445f;
            background: #0b1220;
        }

        .detail-header h2 {
            margin: 0 0 7px;
            font-size: 25px;
        }

        .detail-header p {
            color: #7991b8;
            margin: 4px 0;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 25px;
        }

        .detail-item {
            background: #0c1422;
            border: 1px solid #243249;
            border-radius: 9px;
            padding: 15px;
        }

        .detail-item.full {
            grid-column: 1 / -1;
        }

        .detail-item small {
            display: block;
            color: #718bb2;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .detail-item strong {
            color: #eef4ff;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .file-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .file-btn {
            display: inline-block;
            padding: 10px 15px;
            border: 1px solid #38506e;
            background: #152238;
            border-radius: 7px;
            color: #d7e5fa;
            font-size: 13px;
        }

        .file-btn:hover {
            background: #1c304c;
        }

        .notice {
            margin-bottom: 20px;
            padding: 13px 16px;
            border-radius: 8px;
            background: #13251f;
            border: 1px solid #245b46;
            color: #65dba5;
        }

        @media (max-width: 900px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .admin-header {
                padding: 0 15px;
            }

            .admin-email {
                display: none;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-item.full {
                grid-column: auto;
            }
        }

        @media (max-width: 550px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-heading h1 {
                font-size: 28px;
            }

            .admin-nav {
                padding: 0 15px;
                gap: 18px;
                overflow-x: auto;
            }
        }

    </style>

</head>


<body>


<!-- =========================================================
     ADMIN HEADER
========================================================= -->

<header class="admin-header">

    <a href="index.php" class="admin-logo">
        Hello <span>Biz IT</span>
    </a>

    <div class="admin-header-right">

        <span class="admin-email">
            <?php echo escape($_SESSION['admin_email'] ?? 'Admin'); ?>
        </span>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</header>


<!-- =========================================================
     ADMIN NAV
========================================================= -->

<nav class="admin-nav">

    <a href="index.php">
        Dashboard
    </a>

    <a href="messages.php">
        Messages
    </a>

    <a href="applications.php" class="active">
        Applications
    </a>

</nav>


<!-- =========================================================
     PAGE
========================================================= -->

<main class="page">

    <div class="container">


        <?php if (isset($_GET['deleted'])): ?>

            <div class="notice">
                Application deleted successfully.
            </div>

        <?php endif; ?>


        <!-- PAGE HEADING -->

        <div class="page-heading">

            <small>
                CAREER
            </small>

            <h1>
                Applications
            </h1>

            <p>
                Manage career applications received from your website.
            </p>

        </div>


        <!-- =====================================================
             STATS
        ====================================================== -->

        <div class="stats-grid">

            <div class="stat-card">

                <small>
                    Total Applications
                </small>

                <strong>
                    <?php echo $totalApplications; ?>
                </strong>

            </div>


            <div class="stat-card pending">

                <small>
                    Pending
                </small>

                <strong>
                    <?php echo $pendingApplications; ?>
                </strong>

            </div>


            <div class="stat-card shortlisted">

                <small>
                    Shortlisted
                </small>

                <strong>
                    <?php echo $shortlistedApplications; ?>
                </strong>

            </div>


            <div class="stat-card rejected">

                <small>
                    Rejected
                </small>

                <strong>
                    <?php echo $rejectedApplications; ?>
                </strong>

            </div>

        </div>


        <!-- =====================================================
             APPLICATION TABLE
        ====================================================== -->

        <div class="applications-card">

            <div class="card-header">

                <h2>
                    Career Applications
                </h2>

                <span>
                    <?php echo $totalApplications; ?> applications
                </span>

            </div>


            <div class="table-wrapper">

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

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($applications)): ?>

                        <tr>

                            <td colspan="7" style="text-align:center;padding:50px;color:#7186a8;">

                                No career applications found.

                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($applications as $application): ?>

                            <tr>


                                <!-- APPLICANT -->

                                <td>

                                    <div class="applicant-name">
                                        <?php echo escape($application['full_name']); ?>
                                    </div>

                                    <div class="applicant-email">
                                        <?php echo escape($application['email']); ?>
                                    </div>

                                </td>


                                <!-- POSITION -->

                                <td>

                                    <div class="job-title">

                                        <?php
                                        echo escape(
                                            $application['job_title']
                                            ?? 'Unknown Position'
                                        );
                                        ?>

                                    </div>

                                </td>


                                <!-- PHONE -->

                                <td>

                                    <span class="phone">

                                        <?php
                                        echo escape(
                                            $application['phone']
                                        );
                                        ?>

                                    </span>

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

                                <td>

                                    <span class="date">

                                        <?php
                                        echo date(
                                            'd M Y, h:i A',
                                            strtotime($application['created_at'])
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions">


                                        <!-- VIEW -->

                                        <a
                                            href="applications.php?view=<?php echo (int) $application['id']; ?>"
                                            class="action-btn"
                                        >
                                            View Details →
                                        </a>


                                        <!-- STATUS -->

                                        <form
                                            method="POST"
                                            class="status-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="status"
                                            >

                                            <input
                                                type="hidden"
                                                name="application_id"
                                                value="<?php echo (int) $application['id']; ?>"
                                            >

                                            <select
                                                name="status"
                                                class="status-select"
                                                onchange="this.form.submit()"
                                            >

                                                <option
                                                    value="pending"
                                                    <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>
                                                >
                                                    Pending
                                                </option>

                                                <option
                                                    value="reviewed"
                                                    <?php echo $application['status'] === 'reviewed' ? 'selected' : ''; ?>
                                                >
                                                    Reviewed
                                                </option>

                                                <option
                                                    value="shortlisted"
                                                    <?php echo $application['status'] === 'shortlisted' ? 'selected' : ''; ?>
                                                >
                                                    Shortlisted
                                                </option>

                                                <option
                                                    value="rejected"
                                                    <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>
                                                >
                                                    Rejected
                                                </option>

                                            </select>

                                        </form>


                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            class="status-form"
                                            onsubmit="return confirm('Are you sure you want to delete this application?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete"
                                            >

                                            <input
                                                type="hidden"
                                                name="application_id"
                                                value="<?php echo (int) $application['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>


                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</main>


<!-- =========================================================
     VIEW DETAILS MODAL
========================================================= -->

<?php if ($viewApplication): ?>

<div class="detail-overlay">

    <div class="detail-modal">


        <button
            type="button"
            class="close-detail"
            onclick="window.location.href='applications.php'"
        >
            ×
        </button>


        <!-- HEADER -->

        <div class="detail-header">


            <?php if (!empty($viewApplication['photo_file'])): ?>

                <img
                    src="../<?php echo escape($viewApplication['photo_file']); ?>"
                    alt="Applicant Photo"
                    class="applicant-photo"
                >

            <?php else: ?>

                <div class="applicant-photo"></div>

            <?php endif; ?>


            <div>

                <h2>
                    <?php echo escape($viewApplication['full_name']); ?>
                </h2>

                <p>
                    <?php echo escape($viewApplication['email']); ?>
                </p>

                <p>
                    <?php echo escape($viewApplication['phone']); ?>
                </p>

            </div>

        </div>


        <!-- DETAILS -->

        <div class="detail-grid">


            <div class="detail-item">

                <small>
                    Position
                </small>

                <strong>
                    <?php
                    echo escape(
                        $viewApplication['job_title']
                        ?? 'Unknown Position'
                    );
                    ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Status
                </small>

                <strong>
                    <?php echo escape($viewApplication['status']); ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Job Type
                </small>

                <strong>
                    <?php echo escape($viewApplication['job_type'] ?? ''); ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Location
                </small>

                <strong>
                    <?php echo escape($viewApplication['location'] ?? ''); ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Education
                </small>

                <strong>
                    <?php echo escape($viewApplication['education']); ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Computer Available
                </small>

                <strong>
                    <?php echo escape($viewApplication['computer_available']); ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Laptop Available
                </small>

                <strong>
                    <?php echo escape($viewApplication['laptop_available']); ?>
                </strong>

            </div>


            <div class="detail-item">

                <small>
                    Applied At
                </small>

                <strong>
                    <?php
                    echo date(
                        'd M Y, h:i A',
                        strtotime($viewApplication['created_at'])
                    );
                    ?>
                </strong>

            </div>


            <div class="detail-item full">

                <small>
                    Address
                </small>

                <strong>
                    <?php
                    echo escape(
                        $viewApplication['address']
                    );
                    ?>
                </strong>

            </div>


            <div class="detail-item full">

                <small>
                    Work Experience
                </small>

                <strong>
                    <?php
                    echo !empty($viewApplication['experience'])
                        ? escape($viewApplication['experience'])
                        : 'Not provided';
                    ?>
                </strong>

            </div>


            <div class="detail-item full">

                <small>
                    Cover Message
                </small>

                <strong>
                    <?php
                    echo !empty($viewApplication['message'])
                        ? escape($viewApplication['message'])
                        : 'Not provided';
                    ?>
                </strong>

            </div>

        </div>


        <!-- FILE BUTTONS -->

        <div class="file-buttons">


            <?php if (!empty($viewApplication['photo_file'])): ?>

                <a
                    href="../<?php echo escape($viewApplication['photo_file']); ?>"
                    target="_blank"
                    class="file-btn"
                >
                    🖼 View Photo
                </a>

            <?php endif; ?>


            <?php if (!empty($viewApplication['cv_file'])): ?>

                <a
                    href="../<?php echo escape($viewApplication['cv_file']); ?>"
                    target="_blank"
                    class="file-btn"
                >
                    📄 View CV
                </a>

                <a
                    href="../<?php echo escape($viewApplication['cv_file']); ?>"
                    download
                    class="file-btn"
                >
                    ↓ Download CV
                </a>

            <?php endif; ?>

        </div>


    </div>

</div>

<?php endif; ?>


</body>

</html>

<?php require_once __DIR__ . '/includes/footer.php'; ?>