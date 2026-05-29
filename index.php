<?php
session_start();

define('IS_HOMEPAGE', true);
define('SHOW_APP_SECTION', false);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$studentCode = trim($_GET['student_code'] ?? '');
$academicYear = trim($_GET['academic_year'] ?? '');
$students = [];
$studentGrades = [];
$studentGpas = [];
$searched = $studentCode !== '' || $academicYear !== '';
$error = '';

$yearStmt = $pdo->query('SELECT DISTINCT academic_year FROM grades WHERE academic_year IS NOT NULL ORDER BY academic_year DESC');
$academicYears = $yearStmt->fetchAll();

if ($searched) {
    if ($studentCode !== '' && !preg_match('/^[0-9]{1,8}$/', $studentCode)) {
        $error = 'Mã sinh viên chỉ được nhập số và tối đa 8 chữ số.';
    } else {
        $where = ['s.is_active = 1'];
        $params = [];

        if ($studentCode !== '') {
            $where[] = 's.student_code LIKE ?';
            $params[] = $studentCode . '%';
        }

        if ($academicYear !== '') {
            $where[] = 'EXISTS (
                SELECT 1
                FROM grades gy
                WHERE gy.student_id = s.id AND gy.academic_year = ?
            )';
            $params[] = $academicYear;
        }

        $whereSql = implode(' AND ', $where);
        $studentStmt = $pdo->prepare(
            "SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone, c.class_name
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE $whereSql
             ORDER BY s.student_code ASC"
        );
        $studentStmt->execute($params);
        $students = $studentStmt->fetchAll();

        $gradesSql = 'SELECT subj.subject_code, subj.subject_name, subj.credit,
                        g.midterm_score, g.final_score, g.other_score, g.average_score,
                        g.letter_grade, g.semester, g.academic_year
                     FROM grades g
                     JOIN subjects subj ON g.subject_id = subj.id
                     WHERE g.student_id = ?';

        if ($academicYear !== '') {
            $gradesSql .= ' AND g.academic_year = ?';
        }

        $gradesSql .= ' ORDER BY g.academic_year DESC, g.semester ASC, subj.subject_code ASC';
        $gradesStmt = $pdo->prepare($gradesSql);

        foreach ($students as $student) {
            $gradeParams = [$student['id']];
            if ($academicYear !== '') {
                $gradeParams[] = $academicYear;
            }

            $gradesStmt->execute($gradeParams);
            $grades = $gradesStmt->fetchAll();
            $studentGrades[$student['id']] = $grades;

            if (!empty($grades)) {
                $total = 0;
                foreach ($grades as $grade) {
                    $total += (float) $grade['average_score'];
                }
                $studentGpas[$student['id']] = round($total / count($grades), 2);
            } else {
                $studentGpas[$student['id']] = null;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="homepage-main flex-grow-1">
    <div class="container py-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- AUTHENTICATED: Show grade search -->
            <div class="row justify-content-center mb-4">
                <div class="col-12 col-lg-10">
                    <div class="card border-0 shadow rounded-4 p-4 p-md-5 homepage-search-card">
                        <div class="text-center mb-4">
                            <h1 class="fw-bold mb-2">Tra cứu điểm sinh viên</h1>
                            <p class="text-muted mb-0">Nhập một phần mã sinh viên và lọc theo năm học để xem bảng điểm.</p>
                        </div>

                        <form method="GET" action="index.php" class="row g-3 align-items-end">
                            <div class="col-12 col-md-5">
                                <label for="student_code" class="form-label fw-semibold">Mã sinh viên</label>
                                <input type="text" class="form-control form-control-lg" id="student_code"
                                    name="student_code" value="<?php echo e($studentCode); ?>" placeholder="Ví dụ: 2023"
                                    maxlength="8" inputmode="numeric" pattern="[0-9]{1,8}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="academic_year" class="form-label fw-semibold">Năm học</label>
                                <select class="form-select form-select-lg" id="academic_year" name="academic_year">
                                    <option value="">Tất cả năm học</option>
                                    <?php foreach ($academicYears as $year): ?>
                                        <option value="<?php echo e($year['academic_year']); ?>" <?php echo $academicYear === $year['academic_year'] ? 'selected' : ''; ?>>
                                            <?php echo e($year['academic_year']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                <?php if (empty($students)): ?>
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                            <div class="alert alert-warning shadow-sm mb-0">
                                Không tìm thấy sinh viên phù hợp với điều kiện tra cứu.
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 fw-bold mb-0">Kết quả tra cứu</h2>
                        <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2">
                            <?php echo count($students); ?> sinh viên
                        </span>
                    </div>

                    <?php foreach ($students as $studentIndex => $student): ?>
                        <?php
                        $grades = $studentGrades[$student['id']] ?? [];
                        $gpa = $studentGpas[$student['id']] ?? null;
                        ?>
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <div class="row g-3 align-items-center">
                                    <div class="col-12 col-lg-8">
                                        <h3 class="h5 fw-bold mb-2"><?php echo e($student['full_name']); ?></h3>
                                        <div class="text-muted">
                                            <?php echo e($student['student_code']); ?>
                                            <?php if (!empty($student['class_name'])): ?>
                                                <span class="mx-2">|</span><?php echo e($student['class_name']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-4 text-lg-end">
                                        <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2">
                                            GPA: <?php echo $gpa === null ? 'N/A' : e(number_format($gpa, 2)); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

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
                        <?php if ($studentIndex < count($students) - 1): ?>
                            <hr class="my-4">
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            <!-- GUEST: Welcome landing section -->
            <div class="row justify-content-center mb-4">
                <div class="col-12 col-lg-10">
                    <div class="card border-0 shadow rounded-4 p-4 p-md-5 homepage-search-card text-center">
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--hust-red), var(--hust-dark-red)); box-shadow: 0 8px 24px rgba(200, 16, 46, 0.3);">
                                <i class="fas fa-graduation-cap fa-2x text-white"></i>
                            </div>
                            <h1 class="fw-bold mb-2">Chào mừng đến với Hệ Thống Quản Trị Đại Học</h1>
                            <p class="text-muted mb-0 fs-5">Đại Học Bách Khoa Hà Nội — Hệ thống tra cứu và quản lý điểm sinh viên trực tuyến</p>
                        </div>

                        <hr class="my-4">

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="p-4 rounded-4 h-100" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe3e3 100%); border: 1px solid rgba(200, 16, 46, 0.1);">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                         style="width: 56px; height: 56px; background: rgba(200, 16, 46, 0.1);">
                                        <i class="fas fa-search fa-lg" style="color: var(--hust-red);"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Tra Cứu Điểm</h5>
                                    <p class="text-muted mb-0 small">Xem bảng điểm chi tiết theo từng môn học, học kỳ và năm học một cách nhanh chóng.</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-4 rounded-4 h-100" style="background: linear-gradient(135deg, #f0f4ff 0%, #dbe4ff 100%); border: 1px solid rgba(59, 130, 246, 0.1);">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                         style="width: 56px; height: 56px; background: rgba(59, 130, 246, 0.1);">
                                        <i class="fas fa-chart-line fa-lg" style="color: #3b82f6;"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Thống Kê Học Tập</h5>
                                    <p class="text-muted mb-0 small">Theo dõi GPA, phân tích kết quả học tập và tiến độ qua các học kỳ.</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-4 rounded-4 h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid rgba(34, 197, 94, 0.1);">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                         style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.1);">
                                        <i class="fas fa-user-shield fa-lg" style="color: #22c55e;"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Quản Lý Tài Khoản</h5>
                                    <p class="text-muted mb-0 small">Quản lý hồ sơ cá nhân, cập nhật thông tin và bảo mật tài khoản.</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="<?php echo BASE_PATH; ?>auth/login.php" class="btn btn-hust btn-lg px-5 py-3 rounded-pill shadow-sm" style="font-size: 1rem;">
                                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập để tiếp tục
                            </a>
                            <p class="text-muted mt-3 mb-0 small">
                                <i class="fas fa-lock me-1"></i> Vui lòng đăng nhập bằng tài khoản sinh viên hoặc quản trị viên để sử dụng hệ thống.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
