<?php
session_start();

define('BASE_PATH', '../');
define('IS_HOMEPAGE', false);
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

handleAuth();
checkRole(['admin']);

$values = ['class_code' => '', 'class_name' => '', 'description' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    if ($values['class_code'] === '') {
        $errors[] = 'Mã lớp là bắt buộc.';
    }
    if ($values['class_name'] === '') {
        $errors[] = 'Tên lớp là bắt buộc.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM classes WHERE class_code = ?');
        $stmt->execute([$values['class_code']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Mã lớp đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO classes (class_code, class_name, description) VALUES (?, ?, ?)');
        $stmt->execute([$values['class_code'], $values['class_name'], $values['description']]);
        header('Location: ' . BASE_PATH . 'classes/list.php?created=1');
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Thêm lớp mới</h2>
            <p class="text-muted">Tạo lớp học mới để phân nhóm sinh viên.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>classes/list.php" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mã lớp</label>
                    <input type="text" name="class_code" class="form-control"
                        value="<?php echo e($values['class_code']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tên lớp</label>
                    <input type="text" name="class_name" class="form-control"
                        value="<?php echo e($values['class_name']); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control"
                        rows="4"><?php echo e($values['description']); ?></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-hust">Lưu lớp</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>