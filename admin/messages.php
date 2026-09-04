<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Messages | Admin";

require_once __DIR__ . '/includes/header.php';
/*
|--------------------------------------------------------------------------
| DELETE MESSAGE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_message'])
) {

    $messageId = (int) ($_POST['message_id'] ?? 0);

    if ($messageId > 0) {

        $stmt = $pdo->prepare("
            DELETE FROM contact_messages
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$messageId]);
    }

    header('Location: messages.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| UPDATE MESSAGE STATUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_status'])
) {

    $messageId = (int) ($_POST['message_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    $allowedStatuses = [
        'unread',
        'read',
        'replied'
    ];

    if (
        $messageId > 0 &&
        in_array($newStatus, $allowedStatuses, true)
    ) {

        $stmt = $pdo->prepare("
            UPDATE contact_messages
            SET status = ?
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $newStatus,
            $messageId
        ]);
    }

    header('Location: messages.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| VIEW MESSAGE
|--------------------------------------------------------------------------
*/

$viewMessage = null;

if (isset($_GET['view'])) {

    $viewId = (int) $_GET['view'];

    if ($viewId > 0) {

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

        $stmt->execute([$viewId]);

        $viewMessage = $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | Automatically mark unread message as read
        |--------------------------------------------------------------------------
        */

        if (
            $viewMessage &&
            $viewMessage['status'] === 'unread'
        ) {

            $update = $pdo->prepare("
                UPDATE contact_messages
                SET status = 'read'
                WHERE id = ?
                LIMIT 1
            ");

            $update->execute([$viewId]);

            $viewMessage['status'] = 'read';
        }
    }
}


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$statusFilter = $_GET['status'] ?? '';

$allowedFilters = [
    '',
    'unread',
    'read',
    'replied'
];

if (!in_array($statusFilter, $allowedFilters, true)) {
    $statusFilter = '';
}


/*
|--------------------------------------------------------------------------
| GET MESSAGES
|--------------------------------------------------------------------------
*/

if ($statusFilter !== '') {

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
        WHERE status = ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$statusFilter]);

} else {

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
    ");
}

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| COUNTS
|--------------------------------------------------------------------------
*/

$totalMessages = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM contact_messages
    ")
    ->fetchColumn();


$unreadMessages = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM contact_messages
        WHERE status = 'unread'
    ")
    ->fetchColumn();


$readMessages = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM contact_messages
        WHERE status = 'read'
    ")
    ->fetchColumn();


$repliedMessages = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM contact_messages
        WHERE status = 'replied'
    ")
    ->fetchColumn();


/*
|--------------------------------------------------------------------------
| ADMIN HEADER
|--------------------------------------------------------------------------
|
| IMPORTANT:
| header.php already handles:
| - session
| - authentication
| - navbar
| - admin.css
|
*/

require_once __DIR__ . '/includes/header.php';

?>


<!-- =========================================================
     MESSAGES PAGE
========================================================= -->

<div class="admin-container">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="admin-page-heading">

        <div>

            <span class="section-label">
                CONTACT
            </span>

            <h1>
                Messages
            </h1>

            <p>
                Manage messages received from your website.
            </p>

        </div>

    </div>


    <!-- =====================================================
         MESSAGE STATS
    ====================================================== -->

    <div class="admin-stat-grid">


        <a
            href="messages.php"
            class="admin-stat-card"
        >

            <div class="admin-stat-icon">
                ✉
            </div>

            <div>

                <span>
                    TOTAL
                </span>

                <strong>
                    <?php echo $totalMessages; ?>
                </strong>

            </div>

        </a>


        <a
            href="messages.php?status=unread"
            class="admin-stat-card"
        >

            <div class="admin-stat-icon">
                ●
            </div>

            <div>

                <span>
                    UNREAD
                </span>

                <strong>
                    <?php echo $unreadMessages; ?>
                </strong>

            </div>

        </a>


        <a
            href="messages.php?status=read"
            class="admin-stat-card"
        >

            <div class="admin-stat-icon">
                ✓
            </div>

            <div>

                <span>
                    READ
                </span>

                <strong>
                    <?php echo $readMessages; ?>
                </strong>

            </div>

        </a>


        <a
            href="messages.php?status=replied"
            class="admin-stat-card"
        >

            <div class="admin-stat-icon">
                ↗
            </div>

            <div>

                <span>
                    REPLIED
                </span>

                <strong>
                    <?php echo $repliedMessages; ?>
                </strong>

            </div>

        </a>

    </div>


    <!-- =====================================================
         VIEW MESSAGE
    ====================================================== -->

    <?php if ($viewMessage): ?>

        <section class="admin-message-view">

            <div class="admin-message-view-header">

                <div>

                    <span class="section-label">
                        MESSAGE DETAILS
                    </span>

                    <h2>
                        <?php
                        echo escape(
                            $viewMessage['subject']
                            ?: 'No Subject'
                        );
                        ?>
                    </h2>

                </div>

                <a
                    href="messages.php"
                    class="admin-back-button"
                >
                    ← Back to Messages
                </a>

            </div>


            <!-- MESSAGE META -->

            <div class="admin-message-meta">


                <div>

                    <span>
                        NAME
                    </span>

                    <strong>
                        <?php
                        echo escape($viewMessage['name']);
                        ?>
                    </strong>

                </div>


                <div>

                    <span>
                        EMAIL
                    </span>

                    <strong>

                        <a
                            href="mailto:<?php echo escape($viewMessage['email']); ?>"
                        >
                            <?php
                            echo escape($viewMessage['email']);
                            ?>
                        </a>

                    </strong>

                </div>


                <div>

                    <span>
                        PHONE
                    </span>

                    <strong>
                        <?php
                        echo escape(
                            $viewMessage['phone']
                            ?: 'Not provided'
                        );
                        ?>
                    </strong>

                </div>


                <div>

                    <span>
                        SERVICE
                    </span>

                    <strong>
                        <?php
                        echo escape(
                            $viewMessage['service']
                            ?: 'Not specified'
                        );
                        ?>
                    </strong>

                </div>


                <div>

                    <span>
                        DATE
                    </span>

                    <strong>
                        <?php
                        echo escape(
                            $viewMessage['created_at']
                        );
                        ?>
                    </strong>

                </div>


                <div>

                    <span>
                        STATUS
                    </span>

                    <strong>

                        <span
                            class="admin-status status-<?php
                            echo escape($viewMessage['status']);
                            ?>"
                        >
                            <?php
                            echo escape(
                                ucfirst(
                                    $viewMessage['status']
                                )
                            );
                            ?>
                        </span>

                    </strong>

                </div>

            </div>


            <!-- MESSAGE CONTENT -->

            <div class="admin-message-content">

                <span>
                    MESSAGE
                </span>

                <p>
                    <?php
                    echo nl2br(
                        escape(
                            $viewMessage['message']
                        )
                    );
                    ?>
                </p>

            </div>


            <!-- =================================================
                 MESSAGE ACTIONS
            ================================================== -->

            <div class="admin-message-actions">


                <!-- MARK UNREAD -->

                <form method="POST">

                    <input
                        type="hidden"
                        name="message_id"
                        value="<?php echo (int) $viewMessage['id']; ?>"
                    >

                    <input
                        type="hidden"
                        name="update_status"
                        value="1"
                    >

                    <button
                        type="submit"
                        name="status"
                        value="unread"
                        class="admin-action-button"
                    >
                        Mark Unread
                    </button>

                </form>


                <!-- MARK READ -->

                <form method="POST">

                    <input
                        type="hidden"
                        name="message_id"
                        value="<?php echo (int) $viewMessage['id']; ?>"
                    >

                    <input
                        type="hidden"
                        name="update_status"
                        value="1"
                    >

                    <button
                        type="submit"
                        name="status"
                        value="read"
                        class="admin-action-button"
                    >
                        Mark Read
                    </button>

                </form>


                <!-- MARK REPLIED -->

                <form method="POST">

                    <input
                        type="hidden"
                        name="message_id"
                        value="<?php echo (int) $viewMessage['id']; ?>"
                    >

                    <input
                        type="hidden"
                        name="update_status"
                        value="1"
                    >

                    <button
                        type="submit"
                        name="status"
                        value="replied"
                        class="admin-action-button"
                    >
                        Mark Replied
                    </button>

                </form>


                <!-- DELETE -->

                <form
                    method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this message?');"
                >

                    <input
                        type="hidden"
                        name="message_id"
                        value="<?php echo (int) $viewMessage['id']; ?>"
                    >

                    <input
                        type="hidden"
                        name="delete_message"
                        value="1"
                    >

                    <button
                        type="submit"
                        class="admin-delete-button"
                    >
                        Delete
                    </button>

                </form>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <div class="admin-filter-bar">

        <a
            href="messages.php"
            class="<?php echo $statusFilter === '' ? 'active' : ''; ?>"
        >
            All
        </a>

        <a
            href="messages.php?status=unread"
            class="<?php echo $statusFilter === 'unread' ? 'active' : ''; ?>"
        >
            Unread
        </a>

        <a
            href="messages.php?status=read"
            class="<?php echo $statusFilter === 'read' ? 'active' : ''; ?>"
        >
            Read
        </a>

        <a
            href="messages.php?status=replied"
            class="<?php echo $statusFilter === 'replied' ? 'active' : ''; ?>"
        >
            Replied
        </a>

    </div>


    <!-- =====================================================
         MESSAGE TABLE
    ====================================================== -->

    <section class="admin-table-card">

        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>
                            Name
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Service
                        </th>

                        <th>
                            Subject
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if (empty($messages)): ?>

                    <tr>

                        <td
                            colspan="7"
                            class="admin-table-empty"
                        >
                            No messages found.
                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($messages as $item): ?>

                        <tr
                            class="<?php
                            echo $item['status'] === 'unread'
                                ? 'message-unread'
                                : '';
                            ?>"
                        >


                            <!-- NAME -->

                            <td>

                                <strong>
                                    <?php
                                    echo escape(
                                        $item['name']
                                    );
                                    ?>
                                </strong>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <a
                                    href="mailto:<?php echo escape($item['email']); ?>"
                                >
                                    <?php
                                    echo escape(
                                        $item['email']
                                    );
                                    ?>
                                </a>

                            </td>


                            <!-- SERVICE -->

                            <td>

                                <?php
                                echo escape(
                                    $item['service']
                                    ?: '-'
                                );
                                ?>

                            </td>


                            <!-- SUBJECT -->

                            <td>

                                <?php
                                echo escape(
                                    $item['subject']
                                    ?: 'No Subject'
                                );
                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="admin-status status-<?php
                                    echo escape(
                                        $item['status']
                                    );
                                    ?>"
                                >
                                    <?php
                                    echo escape(
                                        ucfirst(
                                            $item['status']
                                        )
                                    );
                                    ?>
                                </span>

                            </td>


                            <!-- DATE -->

                            <td>

                                <small>
                                    <?php
                                    echo escape(
                                        $item['created_at']
                                    );
                                    ?>
                                </small>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="admin-table-actions">


                                    <!-- VIEW -->

                                    <a
                                       href="message-view.php?id=<?php echo (int) $item['id']; ?>"
                                        class="admin-view-button"
                                    >
                                        View Details →
                                    </a>


                                    <!-- READ / UNREAD -->

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="message_id"
                                            value="<?php echo (int) $item['id']; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="update_status"
                                            value="1"
                                        >

                                        <button
                                            type="submit"
                                            name="status"
                                            value="<?php
                                            echo $item['status'] === 'unread'
                                                ? 'read'
                                                : 'unread';
                                            ?>"
                                            class="admin-small-button"
                                        >

                                            <?php
                                            echo $item['status'] === 'unread'
                                                ? 'Mark Read'
                                                : 'Mark Unread';
                                            ?>

                                        </button>

                                    </form>


                                    <!-- REPLIED -->

                                    <?php if ($item['status'] !== 'replied'): ?>

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="message_id"
                                                value="<?php echo (int) $item['id']; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="update_status"
                                                value="1"
                                            >

                                            <button
                                                type="submit"
                                                name="status"
                                                value="replied"
                                                class="admin-small-button"
                                            >
                                                Replied
                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Delete this message?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="message_id"
                                            value="<?php echo (int) $item['id']; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="delete_message"
                                            value="1"
                                        >

                                        <button
                                            type="submit"
                                            class="admin-small-delete"
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

    </section>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>