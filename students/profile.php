<?php
session_start();

define('BASE_PATH', '../');
define('IS_HOMEPAGE', false);
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

handleAuth();
checkRole(['student']);

$studentId = $_SESSION['user']['student_id'] ?? null;
if (!$studentId) {
    header('Location: ' . BASE_PATH . 'auth/login.php');
    exit();
}

$stmt = $pdo->prepare('SELECT id, student_code, full_name, date_of_birth, gender, email, phone, address, class_id FROM students WHERE id = ? AND is_active = 1');
$stmt->execute([$studentId]);
$student = $stmt->fetch();
if (!$student) {
    die('Sinh viên không tồn tại hoặc đã bị vô hiệu hóa.');
}

$gradesStmt = $pdo->prepare(
    'SELECT g.id, s.subject_code, s.subject_name, s.credit, g.midterm_score, g.final_score, g.other_score, g.average_score, g.letter_grade, g.semester, g.academic_year, g.updated_at
     FROM grades g
     JOIN subjects s ON g.subject_id = s.id
     WHERE g.student_id = ?
     ORDER BY g.academic_year DESC, g.semester ASC, s.subject_code ASC'
);
$gradesStmt->execute([$studentId]);
$grades = $gradesStmt->fetchAll();

$gpa = 0.0;
$gradeCount = count($grades);
if ($gradeCount > 0) {
    $total = 0;
    foreach ($grades as $grade) {
        $total += floatval($grade['average_score']);
    }
    $gpa = round($total / $gradeCount, 2);
}

$passFail = gpaPassFail($gpa);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Hồ sơ sinh viên</h2>
            <p class="text-muted"><?php echo e($student['full_name']); ?> - <?php echo e($student['student_code']); ?>
            </p>
        </div>
        <a href="<?php echo BASE_PATH; ?>auth/logout.php" class="btn btn-outline-danger">Đăng xuất</a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-3">Thông tin cá nhân</h5>
                <div class="mb-3"><strong>Mã sinh viên:</strong> <?php echo e($student['student_code']); ?></div>
                <div class="mb-3"><strong>Họ và tên:</strong> <?php echo e($student['full_name']); ?></div>
                <div class="mb-3"><strong>Ngày sinh:</strong> <?php echo e($student['date_of_birth']); ?></div>
                <div class="mb-3"><strong>Giới tính:</strong> <?php echo e($student['gender']); ?></div>
                <div class="mb-3"><strong>Email:</strong> <?php echo e($student['email']); ?></div>
                <div class="mb-3"><strong>SĐT:</strong> <?php echo e($student['phone']); ?></div>
                <div class="mb-3"><strong>Địa chỉ:</strong> <?php echo e($student['address']); ?></div>
                <div class="alert alert-info mt-4">
                    <div><strong>GPA hiện tại:</strong> <?php echo e(number_format($gpa, 2)); ?></div>
                    <div><strong>Trạng thái:</strong> <?php echo e($passFail); ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4">Bảng điểm</h5>
                <?php if (empty($grades)): ?>
                    <p class="text-muted">Chưa có điểm nào được nhập cho sinh viên này.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã MH</th>
                                    <th>Môn học</th>
                                    <th>HK</th>
                                    <th>Năm học</th>
                                    <th>Giữa kỳ</th>
                                    <th>Cuối kỳ</th>
                                    <th>Khác</th>
                                    <th>TB</th>
                                    <th>Điểm chữ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo e($grade['subject_code']); ?></td>
                                        <td><?php echo e($grade['subject_name']); ?></td>
                                        <td><?php echo e($grade['semester']); ?></td>
                                        <td><?php echo e($grade['academic_year']); ?></td>
                                        <td><?php echo e($grade['midterm_score']); ?></td>
                                        <td><?php echo e($grade['final_score']); ?></td>
                                        <td><?php echo e($grade['other_score']); ?></td>
                                        <td><?php echo e($grade['average_score']); ?></td>
                                        <td><?php echo e($grade['letter_grade']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>