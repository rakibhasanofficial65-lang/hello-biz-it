<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$page_title = "Message Details | " . SITE_NAME;


/*
|--------------------------------------------------------------------------
| GET MESSAGE ID
|--------------------------------------------------------------------------
|
| Supports:
| message-view.php?id=9
|
*/

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: messages.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| GET MESSAGE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
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
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$message = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| MESSAGE NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$message) {
    header('Location: messages.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| HANDLE STATUS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'unread',
        'read',
        'replied'
    ];

    if (in_array($status, $allowedStatuses, true)) {

        $update = $pdo->prepare("
            UPDATE contact_messages
            SET status = ?
            WHERE id = ?
            LIMIT 1
        ");

        $update->execute([
            $status,
            $id
        ]);
    }

    header(
        'Location: message-view.php?id=' . $id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| AUTOMATICALLY MARK UNREAD AS READ
|--------------------------------------------------------------------------
*/

if ($message['status'] === 'unread') {

    $update = $pdo->prepare("
        UPDATE contact_messages
        SET status = 'read'
        WHERE id = ?
        LIMIT 1
    ");

    $update->execute([$id]);

    $message['status'] = 'read';
}


/*
|--------------------------------------------------------------------------
| ADMIN HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     MESSAGE DETAILS PAGE
========================================================= -->

<div class="admin-page">

    <div class="admin-container">


        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="admin-page-header">

            <div>

                <span class="admin-eyebrow">
                    CONTACT MESSAGE
                </span>

                <h1>
                    Message Details
                </h1>

                <p>
                    View the complete message received from your website.
                </p>

            </div>


            <a
                href="messages.php"
                class="admin-btn admin-btn-secondary"
            >
                ← Back to Messages
            </a>

        </div>


        <!-- =====================================================
             MESSAGE DETAILS LAYOUT
        ====================================================== -->

        <div class="message-details-layout">


            <!-- =================================================
                 MESSAGE INFORMATION
            ================================================== -->

            <section class="message-details-card">


                <!-- CARD HEADER -->

                <div class="message-card-header">

                    <div>

                        <span class="admin-eyebrow">
                            MESSAGE
                        </span>

                        <h2>

                            <?php
                            echo escape(
                                $message['subject']
                                ?: 'No Subject'
                            );
                            ?>

                        </h2>

                    </div>


                    <span
                        class="message-status status-<?php
                        echo escape($message['status']);
                        ?>"
                    >

                        <?php
                        echo escape(
                            ucfirst($message['status'])
                        );
                        ?>

                    </span>

                </div>


                <!-- =================================================
                     MESSAGE META
                ================================================== -->

                <div class="message-meta-grid">


                    <!-- NAME -->

                    <div class="message-meta-item">

                        <span>
                            FULL NAME
                        </span>

                        <strong>
                            <?php
                            echo escape($message['name']);
                            ?>
                        </strong>

                    </div>


                    <!-- EMAIL -->

                    <div class="message-meta-item">

                        <span>
                            EMAIL ADDRESS
                        </span>

                        <a
                            href="mailto:<?php echo escape($message['email']); ?>"
                        >
                            <?php
                            echo escape($message['email']);
                            ?>
                        </a>

                    </div>


                    <!-- PHONE -->

                    <div class="message-meta-item">

                        <span>
                            PHONE NUMBER
                        </span>

                        <strong>

                            <?php if (!empty($message['phone'])): ?>

                                <a
                                    href="tel:<?php echo escape($message['phone']); ?>"
                                >
                                    <?php
                                    echo escape($message['phone']);
                                    ?>
                                </a>

                            <?php else: ?>

                                Not provided

                            <?php endif; ?>

                        </strong>

                    </div>


                    <!-- SERVICE -->

                    <div class="message-meta-item">

                        <span>
                            SERVICE
                        </span>

                        <strong>
                            <?php
                            echo escape(
                                $message['service']
                                ?: 'Not specified'
                            );
                            ?>
                        </strong>

                    </div>


                    <!-- DATE -->

                    <div class="message-meta-item">

                        <span>
                            RECEIVED
                        </span>

                        <strong>
                            <?php
                            echo escape(
                                $message['created_at']
                            );
                            ?>
                        </strong>

                    </div>


                    <!-- STATUS -->

                    <div class="message-meta-item">

                        <span>
                            STATUS
                        </span>

                        <strong>

                            <span
                                class="message-status status-<?php
                                echo escape($message['status']);
                                ?>"
                            >
                                <?php
                                echo escape(
                                    ucfirst($message['status'])
                                );
                                ?>
                            </span>

                        </strong>

                    </div>

                </div>


                <!-- =================================================
                     MESSAGE CONTENT
                ================================================== -->

                <div class="message-content">

                    <span>
                        MESSAGE
                    </span>

                    <div class="message-body">

                        <?php
                        echo nl2br(
                            escape($message['message'])
                        );
                        ?>

                    </div>

                </div>


            </section>


            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <aside class="message-actions-card">

                <span class="admin-eyebrow">
                    MESSAGE ACTIONS
                </span>

                <h3>
                    Update Status
                </h3>

                <p>
                    Change the message status after reviewing or replying.
                </p>


                <!-- STATUS BUTTONS -->

                <div class="message-action-buttons">


                    <!-- MARK UNREAD -->

                    <form method="POST">

                        <input
                            type="hidden"
                            name="status"
                            value="unread"
                        >

                        <button
                            type="submit"
                            class="message-action unread-action"
                        >
                            ● Mark Unread
                        </button>

                    </form>


                    <!-- MARK READ -->

                    <form method="POST">

                        <input
                            type="hidden"
                            name="status"
                            value="read"
                        >

                        <button
                            type="submit"
                            class="message-action read-action"
                        >
                            ✓ Mark Read
                        </button>

                    </form>


                    <!-- MARK REPLIED -->

                    <form method="POST">

                        <input
                            type="hidden"
                            name="status"
                            value="replied"
                        >

                        <button
                            type="submit"
                            class="message-action replied-action"
                        >
                            ↗ Mark Replied
                        </button>

                    </form>


                </div>


                <!-- =================================================
                     EMAIL ACTION
                ================================================== -->

                <div class="message-contact-action">

                    <span>
                        QUICK ACTION
                    </span>

                    <a
                        href="mailto:<?php echo escape($message['email']); ?>?subject=<?php echo rawurlencode('Re: ' . ($message['subject'] ?: 'Your Message')); ?>"
                        class="admin-btn admin-btn-primary"
                    >
                        ✉ Reply by Email
                    </a>

                </div>


                <!-- =================================================
                     DELETE
                ================================================== -->

                <div class="message-delete-area">

                    <span>
                        DANGER ZONE
                    </span>

                    <form
                        method="POST"
                        action="delete-message.php"
                        onsubmit="return confirm('Are you sure you want to permanently delete this message?');"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo (int) $message['id']; ?>"
                        >

                        <button
                            type="submit"
                            class="admin-btn admin-btn-danger"
                        >
                            Delete Message
                        </button>

                    </form>

                </div>


            </aside>


        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>