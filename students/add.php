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

$values = [
    'student_code' => '',
    'full_name' => '',
    'date_of_birth' => '',
    'gender' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'class_ids' => []
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        if ($field === 'class_ids') {
            $values[$field] = array_values(array_unique(array_filter(array_map('intval', $_POST[$field] ?? []))));
        } else {
            $values[$field] = trim($_POST[$field] ?? '');
        }
    }

    if ($values['student_code'] === '') {
        $errors[] = 'Mã sinh viên là bắt buộc.';
    } elseif (!preg_match('/^[0-9]{8}$/', $values['student_code'])) {
        $errors[] = 'Mã sinh viên phải gồm 8 chữ số.';
    }
    if ($values['full_name'] === '') {
        $errors[] = 'Tên sinh viên là bắt buộc.';
    }
    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Định dạng email không hợp lệ.';
    }
    if (!empty($values['class_ids'])) {
        $placeholders = implode(',', array_fill(0, count($values['class_ids']), '?'));
        $classCheck = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE id IN ($placeholders)");
        $classCheck->execute($values['class_ids']);
        if ((int) $classCheck->fetchColumn() !== count($values['class_ids'])) {
            $errors[] = 'Một hoặc nhiều lớp đã chọn không hợp lệ.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE student_code = ?');
        $stmt->execute([$values['student_code']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Mã sinh viên đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $primaryClassId = $values['class_ids'][0] ?? null;
        $stmt = $pdo->prepare(
            'INSERT INTO students (student_code, full_name, date_of_birth, gender, email, phone, address, class_id, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            $values['student_code'],
            $values['full_name'],
            $values['date_of_birth'] ?: null,
            $values['gender'] ?: null,
            $values['email'] ?: null,
            $values['phone'] ?: null,
            $values['address'] ?: null,
            $primaryClassId,
        ]);

        $studentId = $pdo->lastInsertId();
        if (!empty($values['class_ids'])) {
            $classInsert = $pdo->prepare('INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)');
            foreach ($values['class_ids'] as $classId) {
                $classInsert->execute([$studentId, $classId]);
            }
        }

        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $userStmt = $pdo->prepare(
            'INSERT INTO users (username, password, role, student_id, is_active) VALUES (?, ?, "student", ?, 1)'
        );
        $userStmt->execute([$values['student_code'], $passwordHash, $studentId]);

        header('Location: ' . BASE_PATH . 'students/list.php?success=1');
        exit();
    }
}

$classStmt = $pdo->query('SELECT id, class_name FROM classes ORDER BY class_name ASC');
$classes = $classStmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Thêm sinh viên mới</h2>
            <p class="text-muted">Tự động tạo tài khoản sinh viên với mật khẩu mặc định <strong>password123</strong>.
            </p>
        </div>
        <a href="<?php echo BASE_PATH; ?>students/list.php" class="btn btn-outline-secondary">Quay lại danh sách</a>
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
                    <label class="form-label">Mã sinh viên</label>
                    <input type="text" name="student_code" class="form-control"
                        value="<?php echo e($values['student_code']); ?>" placeholder="20230001" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="full_name" class="form-control"
                        value="<?php echo e($values['full_name']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" name="date_of_birth" class="form-control"
                        value="<?php echo e($values['date_of_birth']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">Chọn giới tính</option>
                        <option value="Male" <?php echo $values['gender'] === 'Male' ? 'selected' : ''; ?>>Nam</option>
                        <option value="Female" <?php echo $values['gender'] === 'Female' ? 'selected' : ''; ?>>Nữ</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lớp học</label>
                    <select name="class_ids[]" class="form-select" multiple size="5">
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo e($class['id']); ?>" <?php echo in_array((int) $class['id'], $values['class_ids'], true) ? 'selected' : ''; ?>><?php echo e($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Giữ Ctrl để chọn nhiều lớp.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e($values['email']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo e($values['phone']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="address" class="form-control"
                        rows="3"><?php echo e($values['address']); ?></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-hust">Lưu sinh viên</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
