<?php
session_start();

define('BASE_PATH', '../');
define('IS_HOMEPAGE', false);
define('SHOW_APP_SECTION', false);

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

handleAuth();
checkRole(['admin']);

$feedbackId = intval($_GET['id'] ?? 0);
if ($feedbackId <= 0) {
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM feedback_messages WHERE id = ?');
$stmt->execute([$feedbackId]);
$feedback = $stmt->fetch();

if (!$feedback) {
    http_response_code(404);
    die('Feedback message not found.');
}

if ((int) $feedback['is_read'] === 0) {
    $updateStmt = $pdo->prepare('UPDATE feedback_messages SET is_read = 1, read_at = NOW() WHERE id = ?');
    $updateStmt->execute([$feedbackId]);
    $feedback['is_read'] = 1;
    $feedback['read_at'] = date('Y-m-d H:i:s');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5 mt-4 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Feedback Detail</h2>
            <p class="text-muted mb-0">Message submitted from the contact form.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h5 class="fw-bold mb-3">Sender</h5>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold">Name</div>
                    <div class="fw-semibold text-dark"><?php echo e($feedback['sender_name']); ?></div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold">Email</div>
                    <a href="mailto:<?php echo e($feedback['sender_email']); ?>" class="fw-semibold">
                        <?php echo e($feedback['sender_email']); ?>
                    </a>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold">Submitted At</div>
                    <div><?php echo e(date('d/m/Y H:i', strtotime($feedback['created_at']))); ?></div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold">Status</div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                        Read
                    </span>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold">IP Address</div>
                    <div><?php echo e($feedback['sender_ip'] ?: '—'); ?></div>
                </div>

                <div>
                    <div class="text-muted small fw-semibold">User Agent</div>
                    <div class="small text-break"><?php echo e($feedback['user_agent'] ?: '—'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Subject</div>
                        <h3 class="h4 fw-bold text-dark mb-0"><?php echo e($feedback['subject']); ?></h3>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                        #<?php echo (int) $feedback['id']; ?>
                    </span>
                </div>

                <div class="text-muted small fw-semibold mb-2">Message</div>
                <div class="feedback-message-body border rounded-3 p-4 bg-light">
                    <?php echo nl2br(e($feedback['message'])); ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .feedback-message-body {
        white-space: normal;
        line-height: 1.7;
        min-height: 220px;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

