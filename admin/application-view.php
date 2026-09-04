<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';

$page_title = "Messages | Admin";

require_once __DIR__ . '/includes/header.php';


session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: applications.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| UPDATE APPLICATION STATUS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'pending',
        'reviewed',
        'shortlisted',
        'rejected',
        'selected'
    ];

    if (in_array($status, $allowedStatuses, true)) {

        $stmt = $pdo->prepare("
            UPDATE career_applications
            SET status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $id
        ]);
    }

    header("Location: application-view.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| GET APPLICATION
|--------------------------------------------------------------------------
*/

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

$stmt->execute([$id]);

$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    header('Location: applications.php');
    exit;
}


$page_title = 'Applicant Details | ' . SITE_NAME;

include __DIR__ . '/includes/header.php';

?>

<div class="admin-page">

    <div class="admin-container">

        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="admin-page-header">

            <div>

                <span class="admin-eyebrow">
                    CAREER APPLICATION
                </span>

                <h1>
                    Applicant Details
                </h1>

                <p>
                    Review the complete application submitted by the candidate.
                </p>

            </div>

            <a
                href="applications.php"
                class="admin-btn admin-btn-secondary"
            >
                ← Back to Applications
            </a>

        </div>


        <!-- =====================================================
             APPLICANT TOP CARD
        ====================================================== -->

        <div class="applicant-profile-card">

            <div class="applicant-photo">

                <?php if (!empty($application['photo_file'])): ?>

                    <img
                        src="<?php echo base_url($application['photo_file']); ?>"
                        alt="<?php echo escape($application['full_name']); ?>"
                    >

                <?php else: ?>

                    <div class="no-photo">
                        No Photo
                    </div>

                <?php endif; ?>

            </div>


            <div class="applicant-profile-info">

                <span class="admin-eyebrow">
                    APPLICANT
                </span>

                <h2>
                    <?php echo escape($application['full_name']); ?>
                </h2>

                <p>
                    <?php echo escape($application['job_title'] ?? 'Position not available'); ?>
                </p>

                <span class="application-status status-<?php echo escape($application['status']); ?>">
                    <?php echo ucfirst(escape($application['status'])); ?>
                </span>

            </div>

        </div>


        <div class="application-details-layout">


            <!-- =================================================
                 MAIN INFORMATION
            ================================================== -->

            <div class="application-details-main">


                <!-- PERSONAL INFORMATION -->

                <div class="application-detail-card">

                    <div class="detail-card-heading">

                        <span>
                            01
                        </span>

                        <div>
                            <h3>
                                Personal Information
                            </h3>

                            <p>
                                Candidate contact and basic information.
                            </p>
                        </div>

                    </div>


                    <div class="detail-grid">

                        <div class="detail-item">

                            <span>
                                FULL NAME
                            </span>

                            <strong>
                                <?php echo escape($application['full_name']); ?>
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                EMAIL
                            </span>

                            <a href="mailto:<?php echo escape($application['email']); ?>">
                                <?php echo escape($application['email']); ?>
                            </a>

                        </div>


                        <div class="detail-item">

                            <span>
                                PHONE
                            </span>

                            <a href="tel:<?php echo escape($application['phone']); ?>">
                                <?php echo escape($application['phone']); ?>
                            </a>

                        </div>


                        <div class="detail-item">

                            <span>
                                EDUCATION
                            </span>

                            <strong>
                                <?php echo escape($application['education']); ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- JOB INFORMATION -->

                <div class="application-detail-card">

                    <div class="detail-card-heading">

                        <span>
                            02
                        </span>

                        <div>
                            <h3>
                                Position Information
                            </h3>

                            <p>
                                Job information related to this application.
                            </p>
                        </div>

                    </div>


                    <div class="detail-grid">

                        <div class="detail-item">

                            <span>
                                POSITION
                            </span>

                            <strong>
                                <?php echo escape($application['job_title'] ?? 'N/A'); ?>
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                JOB TYPE
                            </span>

                            <strong>
                                <?php echo escape($application['job_type'] ?? 'N/A'); ?>
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                LOCATION
                            </span>

                            <strong>
                                <?php echo escape($application['location'] ?? 'N/A'); ?>
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                APPLIED DATE
                            </span>

                            <strong>
                                <?php echo escape($application['created_at'] ?? 'N/A'); ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- EXPERIENCE -->

                <div class="application-detail-card">

                    <div class="detail-card-heading">

                        <span>
                            03
                        </span>

                        <div>
                            <h3>
                                Work Experience
                            </h3>

                            <p>
                                Candidate's previous experience.
                            </p>
                        </div>

                    </div>


                    <div class="detail-long-text">

                        <?php if (!empty($application['experience'])): ?>

                            <?php echo nl2br(escape($application['experience'])); ?>

                        <?php else: ?>

                            <span class="empty-value">
                                No work experience provided.
                            </span>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- ADDRESS -->

                <div class="application-detail-card">

                    <div class="detail-card-heading">

                        <span>
                            04
                        </span>

                        <div>
                            <h3>
                                Address
                            </h3>

                            <p>
                                Candidate's current address.
                            </p>
                        </div>

                    </div>


                    <div class="detail-long-text">

                        <?php echo nl2br(escape($application['address'])); ?>

                    </div>

                </div>


                <!-- EQUIPMENT -->

                <div class="application-detail-card">

                    <div class="detail-card-heading">

                        <span>
                            05
                        </span>

                        <div>
                            <h3>
                                Equipment Availability
                            </h3>

                            <p>
                                Computer and laptop availability.
                            </p>
                        </div>

                    </div>


                    <div class="equipment-result-grid">

                        <div>

                            <span>
                                COMPUTER
                            </span>

                            <strong>
                                <?php
                                echo $application['computer_available'] === 'yes'
                                    ? '✓ Yes, has a computer'
                                    : '✕ No, does not have a computer';
                                ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                LAPTOP
                            </span>

                            <strong>
                                <?php
                                echo $application['laptop_available'] === 'yes'
                                    ? '✓ Yes, has a laptop'
                                    : '✕ No, does not have a laptop';
                                ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- COVER MESSAGE -->

                <div class="application-detail-card">

                    <div class="detail-card-heading">

                        <span>
                            06
                        </span>

                        <div>
                            <h3>
                                Cover Message
                            </h3>

                            <p>
                                Additional information from the applicant.
                            </p>
                        </div>

                    </div>


                    <div class="detail-long-text">

                        <?php if (!empty($application['message'])): ?>

                            <?php echo nl2br(escape($application['message'])); ?>

                        <?php else: ?>

                            <span class="empty-value">
                                No cover message provided.
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SIDEBAR
            ================================================== -->

            <aside class="application-details-sidebar">


                <!-- DOCUMENTS -->

                <div class="application-action-card">

                    <span class="admin-eyebrow">
                        DOCUMENTS
                    </span>

                    <h3>
                        Applicant Files
                    </h3>

                    <p>
                        View or download the submitted files.
                    </p>


                    <?php if (!empty($application['photo_file'])): ?>

                        <a
                            href="<?php echo base_url($application['photo_file']); ?>"
                            target="_blank"
                            class="admin-btn admin-btn-secondary full-width"
                        >
                            🖼 View Photo
                        </a>

                    <?php endif; ?>


                    <?php if (!empty($application['cv_file'])): ?>

                        <a
                            href="<?php echo base_url($application['cv_file']); ?>"
                            target="_blank"
                            class="admin-btn admin-btn-primary full-width"
                        >
                            📄 View CV
                        </a>

                        <a
                            href="<?php echo base_url($application['cv_file']); ?>"
                            download
                            class="admin-btn admin-btn-secondary full-width"
                        >
                            ↓ Download CV
                        </a>

                    <?php endif; ?>

                </div>


                <!-- STATUS -->

                <div class="application-action-card">

                    <span class="admin-eyebrow">
                        APPLICATION STATUS
                    </span>

                    <h3>
                        Update Status
                    </h3>

                    <p>
                        Change the candidate's recruitment status.
                    </p>


                    <form method="POST">

                        <select
                            name="status"
                            class="admin-select"
                            required
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
                                value="selected"
                                <?php echo $application['status'] === 'selected' ? 'selected' : ''; ?>
                            >
                                Selected
                            </option>

                            <option
                                value="rejected"
                                <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>
                            >
                                Rejected
                            </option>

                        </select>


                        <button
                            type="submit"
                            class="admin-btn admin-btn-primary full-width"
                        >
                            Update Status
                        </button>

                    </form>

                </div>


                <!-- QUICK CONTACT -->

                <div class="application-action-card">

                    <span class="admin-eyebrow">
                        QUICK CONTACT
                    </span>

                    <h3>
                        Contact Applicant
                    </h3>


                    <a
                        href="mailto:<?php echo escape($application['email']); ?>"
                        class="admin-btn admin-btn-primary full-width"
                    >
                        ✉ Send Email
                    </a>


                    <a
                        href="tel:<?php echo escape($application['phone']); ?>"
                        class="admin-btn admin-btn-secondary full-width"
                    >
                        ☎ Call Applicant
                    </a>

                </div>

            </aside>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>