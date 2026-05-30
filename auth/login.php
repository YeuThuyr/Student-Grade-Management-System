<?php
session_start();

define('BASE_PATH', '../');
define('IS_HOMEPAGE', false);
define('SHOW_APP_SECTION', false);

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit();
}

require_once __DIR__ . '/../middleware/rate_limit.php';
// Limit login/brute-force attempts to 60 requests per minute
handleRateLimit(60, 60);

require_once '../config/database.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = __('login_err_empty');
    } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        // Prevent 500 error / collation mismatch with Vietnamese text or special characters
        // Usernames are only admin or numeric student codes anyway, so anything else is invalid
        $error = __('login_err_failed');
    } else {
        $user = false;
        try {
            $stmt = $pdo->prepare(
                "SELECT u.id, u.username, u.password, u.role, u.student_id, u.is_active, s.student_code
                 FROM users u
                 LEFT JOIN students s ON u.student_id = s.id
                 WHERE u.username = ?"
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            // Gracefully catch database exceptions (like charset errors on free hosting)
            $user = false;
        }

        if ($user && password_verify($password, $user['password']) && $user['is_active']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'username' => $user['username'],
                'role' => $user['role'],
                'student_id' => $user['student_id'],
                'student_code' => $user['student_code']
            ];

            if ($user['role'] === 'admin') {
                // Cryptographic fingerprint for session hijacking defense (SYS-FR-01)
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['admin_fingerprint'] = hash_hmac(
                    'sha256', 
                    $user['id'] . '|' . $ip . '|' . $userAgent, 
                    'HUST-GradeMgmt-Secret-Key-2026'
                );
                header('Location: ../dashboard.php');
            } else {
                header('Location: ../students/profile.php');
            }
            exit();
        }

        $error = __('login_err_failed');
    }
}

require_once '../includes/header.php';
?>

<main class="container py-5 my-5 flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4" style="background: #ffffff;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold hust-text-gradient mb-2" data-i18n="login_title"><?php echo __('login_title'); ?></h2>
                        <p class="text-muted fs-6" data-i18n="login_subtitle"><?php echo __('login_subtitle'); ?></p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold text-secondary" data-i18n="login_username"><?php echo __('login_username'); ?></label>
                            <input type="text" class="form-control form-control-lg bg-light" id="username"
                                name="username" placeholder="<?php echo e(__('login_username')); ?>" required data-i18n-placeholder="login_username">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold text-secondary" data-i18n="login_password"><?php echo __('login_password'); ?></label>
                            <input type="password" class="form-control form-control-lg bg-light" id="password"
                                name="password" placeholder="••••••••" required>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-hust btn-lg fw-bold w-100" data-i18n="login_submit"><?php echo __('login_submit'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>