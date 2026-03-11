<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: ../index.php');
                exit();
            } else {
                $error = "Tài khoản hoặc mật khẩu không chính xác.";
            }
        } else {
            $error = "Tài khoản hoặc mật khẩu không chính xác.";
        }
        $stmt->close();
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }
}

// Cấu hình Base Path và Nạp Header chung
define('BASE_PATH', '../');
require_once '../includes/header.php';
?>

    <main class="container py-5 my-5 flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4" style="background: #ffffff;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold hust-text-gradient mb-2">Đăng Nhập Hệ Thống</h2>
                            <p class="text-muted fs-6">Vui lòng nhập thông tin để truy cập</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold text-secondary">Tên đăng nhập</label>
                                <input type="text" class="form-control form-control-lg bg-light" id="username" name="username" placeholder="Nguyễn Văn A" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-secondary">Mật khẩu</label>
                                <input type="password" class="form-control form-control-lg bg-light" id="password" name="password" placeholder="••••••••" required>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-hust btn-lg fw-bold w-100">Đăng Nhập</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php 
// Nạp Footer chung
require_once '../includes/footer.php'; 
?>
