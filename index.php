<?php
session_start();

define('IS_HOMEPAGE', true);
define('SHOW_APP_SECTION', false);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$studentCode = trim($_GET['student_code'] ?? '');
$student = null;
$grades = [];
$gpa = null;
$searched = $studentCode !== '';
$error = '';

if ($searched) {
    if (!preg_match('/^[0-9]{8}$/', $studentCode)) {
        $error = 'Mã sinh viên phải gồm 8 chữ số.';
    } else {
        $studentStmt = $pdo->prepare(
            'SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone, c.class_name
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.student_code = ? AND s.is_active = 1'
        );
        $studentStmt->execute([$studentCode]);
        $student = $studentStmt->fetch();

        if ($student) {
            $gradesStmt = $pdo->prepare(
                'SELECT subj.subject_code, subj.subject_name, subj.credit,
                        g.midterm_score, g.final_score, g.other_score, g.average_score,
                        g.letter_grade, g.semester, g.academic_year
                 FROM grades g
                 JOIN subjects subj ON g.subject_id = subj.id
                 WHERE g.student_id = ?
                 ORDER BY g.academic_year DESC, g.semester ASC, subj.subject_code ASC'
            );
            $gradesStmt->execute([$student['id']]);
            $grades = $gradesStmt->fetchAll();

            if (!empty($grades)) {
                $total = 0;
                foreach ($grades as $grade) {
                    $total += (float) $grade['average_score'];
                }
                $gpa = round($total / count($grades), 2);
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="bg-white py-5 flex-grow-1">
    <div class="container py-4">
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold mb-2">Tra cứu điểm sinh viên</h1>
                        <p class="text-muted mb-0">Nhập mã sinh viên để xem bảng điểm và kết quả học tập.</p>
                    </div>

                    <form method="GET" action="index.php" class="row g-3 align-items-end">
                        <div class="col-12 col-md-9">
                            <label for="student_code" class="form-label fw-semibold">Mã sinh viên</label>
                            <input type="text" class="form-control form-control-lg" id="student_code"
                                name="student_code" value="<?php echo e($studentCode); ?>" placeholder="Ví dụ: 20230001"
                                maxlength="8" inputmode="numeric" pattern="[0-9]{8}" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <button type="submit" class="btn btn-hust btn-lg w-100">
                                <i class="fas fa-search me-1"></i> Tra cứu
                            </button>
                        </div>
                    </form>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger mt-4 mb-0"><?php echo e($error); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($searched && $error === ''): ?>
            <?php if (!$student): ?>
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="alert alert-warning shadow-sm mb-0">
                            Không tìm thấy sinh viên có mã <strong><?php echo e($studentCode); ?></strong>.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-lg-8">
                                <h2 class="h4 fw-bold mb-2"><?php echo e($student['full_name']); ?></h2>
                                <div class="text-muted">
                                    <?php echo e($student['student_code']); ?>
                                    <?php if (!empty($student['class_name'])): ?>
                                        <span class="mx-2">|</span><?php echo e($student['class_name']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 text-lg-end">
                                <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2">
                                    GPA: <?php echo $gpa === null ? 'N/A' : e($gpa); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Môn học</th>
                                    <th>Tín chỉ</th>
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
                                <?php if (empty($grades)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">Sinh viên này chưa có điểm.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($grades as $index => $grade): ?>
                                        <tr>
                                            <td><?php echo e($index + 1); ?></td>
                                            <td><?php echo e($grade['subject_code'] . ' - ' . $grade['subject_name']); ?></td>
                                            <td><?php echo e($grade['credit']); ?></td>
                                            <td><?php echo e($grade['semester']); ?></td>
                                            <td><?php echo e($grade['academic_year']); ?></td>
                                            <td><?php echo e($grade['midterm_score']); ?></td>
                                            <td><?php echo e($grade['final_score']); ?></td>
                                            <td><?php echo e($grade['other_score']); ?></td>
                                            <td><?php echo e($grade['average_score']); ?></td>
                                            <td><?php echo e($grade['letter_grade']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
