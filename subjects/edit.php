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

$subjectId = intval($_GET['id'] ?? 0);
if ($subjectId <= 0) {
    header('Location: ' . BASE_PATH . 'subjects/list.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM subjects WHERE id = ?');
$stmt->execute([$subjectId]);
$subject = $stmt->fetch();
if (!$subject) {
    die('Môn học không tồn tại.');
}

$values = [
    'subject_code' => $subject['subject_code'],
    'subject_name' => $subject['subject_name'],
    'credit' => $subject['credit'],
    'description' => $subject['description']
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    if ($values['subject_code'] === '') {
        $errors[] = 'Mã môn học là bắt buộc.';
    }
    if ($values['subject_name'] === '') {
        $errors[] = 'Tên môn học là bắt buộc.';
    }
    if ($values['credit'] === '' || !ctype_digit($values['credit']) || intval($values['credit']) <= 0) {
        $errors[] = 'Tín chỉ phải là số nguyên dương.';
    }

    if (empty($errors)) {
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM subjects WHERE subject_code = ? AND id <> ?');
        $checkStmt->execute([$values['subject_code'], $subjectId]);
        if ($checkStmt->fetchColumn() > 0) {
            $errors[] = 'Mã môn học đã được sử dụng bởi môn khác.';
        }
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare('UPDATE subjects SET subject_code = ?, subject_name = ?, credit = ?, description = ? WHERE id = ?');
        $updateStmt->execute([$values['subject_code'], $values['subject_name'], $values['credit'], $values['description'], $subjectId]);
        header('Location: ' . BASE_PATH . 'subjects/list.php?updated=1');
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Chỉnh sửa môn học</h2>
            <p class="text-muted">Cập nhật thông tin môn học.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>subjects/list.php" class="btn btn-outline-secondary">Quay lại danh sách</a>
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
                    <label class="form-label">Mã môn học</label>
                    <input type="text" name="subject_code" class="form-control"
                        value="<?php echo e($values['subject_code']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tên môn học</label>
                    <input type="text" name="subject_name" class="form-control"
                        value="<?php echo e($values['subject_name']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tín chỉ</label>
                    <input type="number" name="credit" class="form-control" value="<?php echo e($values['credit']); ?>"
                        min="1" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control"
                        rows="4"><?php echo e($values['description']); ?></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-hust">Cập nhật môn học</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>