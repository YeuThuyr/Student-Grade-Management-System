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

$gradeId = intval($_GET['id'] ?? 0);
$errors = [];

$students = $pdo->query('SELECT id, student_code, full_name FROM students WHERE is_active = 1 ORDER BY student_code ASC')->fetchAll();
$subjects = $pdo->query('SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code ASC')->fetchAll();

$values = [
    'student_id' => '',
    'subject_id' => '',
    'midterm_score' => '',
    'final_score' => '',
    'other_score' => '',
    'semester' => '',
    'academic_year' => ''
];

if ($gradeId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM grades WHERE id = ?');
    $stmt->execute([$gradeId]);
    $grade = $stmt->fetch();
    if (!$grade) {
        die('Bản ghi điểm không tồn tại.');
    }
    if (isGradeLocked($grade['academic_year'])) {
        header('Location: ' . BASE_PATH . 'grades/list.php?error=locked');
        exit();
    }
    $values = [
        'student_id' => $grade['student_id'],
        'subject_id' => $grade['subject_id'],
        'midterm_score' => $grade['midterm_score'],
        'final_score' => $grade['final_score'],
        'other_score' => $grade['other_score'],
        'semester' => $grade['semester'],
        'academic_year' => $grade['academic_year']
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    if ($values['student_id'] === '') {
        $errors[] = 'Vui lòng chọn sinh viên.';
    }
    if ($values['subject_id'] === '') {
        $errors[] = 'Vui lòng chọn môn học.';
    }
    foreach (['midterm_score', 'final_score', 'other_score'] as $scoreField) {
        if ($values[$scoreField] === '' || !is_numeric($values[$scoreField]) || $values[$scoreField] < 0 || $values[$scoreField] > 10) {
            $errors[] = 'Điểm phải là số trong khoảng 0 đến 10.';
            break;
        }
    }
    if ($values['semester'] === '') {
        $errors[] = 'Học kỳ là bắt buộc.';
    }
    if ($values['academic_year'] === '') {
        $errors[] = 'Năm học là bắt buộc.';
    }

    if (empty($errors)) {
        $average = calculateAverageScore($values['midterm_score'], $values['final_score'], $values['other_score']);
        $letter = determineLetterGrade($average);

        if ($gradeId > 0) {
            $stmt = $pdo->prepare(
                'UPDATE grades SET student_id = ?, subject_id = ?, midterm_score = ?, final_score = ?, other_score = ?, average_score = ?, letter_grade = ?, semester = ?, academic_year = ? WHERE id = ?'
            );
            $stmt->execute([
                $values['student_id'],
                $values['subject_id'],
                $values['midterm_score'],
                $values['final_score'],
                $values['other_score'],
                $average,
                $letter,
                $values['semester'],
                $values['academic_year'],
                $gradeId
            ]);
            header('Location: ' . BASE_PATH . 'grades/list.php?updated=1');
            exit();
        }

        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM grades WHERE student_id = ? AND subject_id = ? AND semester = ? AND academic_year = ?');
        $checkStmt->execute([$values['student_id'], $values['subject_id'], $values['semester'], $values['academic_year']]);
        if ($checkStmt->fetchColumn() > 0) {
            $errors[] = 'Bản ghi điểm này đã tồn tại cho sinh viên và học kỳ tương ứng.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO grades (student_id, subject_id, midterm_score, final_score, other_score, average_score, letter_grade, semester, academic_year)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $values['student_id'],
            $values['subject_id'],
            $values['midterm_score'],
            $values['final_score'],
            $values['other_score'],
            $average,
            $letter,
            $values['semester'],
            $values['academic_year']
        ]);
        header('Location: ' . BASE_PATH . 'grades/list.php?created=1');
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><?php echo $gradeId > 0 ? 'Chỉnh sửa điểm' : 'Thêm điểm mới'; ?></h2>
            <p class="text-muted">Nhập điểm cho sinh viên mỗi môn/học kỳ.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>grades/list.php" class="btn btn-outline-secondary">Quay lại danh sách điểm</a>
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
                    <label class="form-label">Sinh viên</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Chọn sinh viên</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo e($student['id']); ?>" <?php echo $values['student_id'] == $student['id'] ? 'selected' : ''; ?>>
                                <?php echo e($student['student_code'] . ' - ' . $student['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Môn học</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Chọn môn học</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo e($subject['id']); ?>" <?php echo $values['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo e($subject['subject_code'] . ' - ' . $subject['subject_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Điểm giữa kỳ</label>
                    <input type="number" step="0.01" name="midterm_score" class="form-control"
                        value="<?php echo e($values['midterm_score']); ?>" min="0" max="10" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Điểm cuối kỳ</label>
                    <input type="number" step="0.01" name="final_score" class="form-control"
                        value="<?php echo e($values['final_score']); ?>" min="0" max="10" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Điểm khác</label>
                    <input type="number" step="0.01" name="other_score" class="form-control"
                        value="<?php echo e($values['other_score']); ?>" min="0" max="10" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Học kỳ</label>
                    <input type="text" name="semester" class="form-control"
                        value="<?php echo e($values['semester']); ?>" placeholder="1, 2, Hè" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Năm học</label>
                    <input type="text" name="academic_year" class="form-control"
                        value="<?php echo e($values['academic_year']); ?>" placeholder="2023-2024" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit"
                        class="btn btn-hust"><?php echo $gradeId > 0 ? 'Cập nhật điểm' : 'Lưu điểm'; ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>