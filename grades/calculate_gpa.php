<?php
session_start();

define('BASE_PATH', '../');
define('IS_HOMEPAGE', false);

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

handleAuth();
checkRole(['admin', 'student']);

$role = $_SESSION['user']['role'] ?? 'student';
$currentStudentId = $_SESSION['user']['student_id'] ?? null;

// Determine target student ID
$targetStudentId = null;
if ($role === 'student') {
    $targetStudentId = $currentStudentId;
} else {
    $targetStudentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;
}

// Release session early for high concurrency (SYS-NFR-02)
session_write_close();

$students = [];
if ($role === 'admin') {
    $students = $pdo->query("SELECT id, student_code, full_name FROM students WHERE is_active = 1 ORDER BY student_code ASC")->fetchAll();
}

$studentInfo = null;
$grades = [];
$gpa10 = 0.0;
$gpa4 = 0.0;
$totalCredits = 0;
$semesterBreakdown = [];

if ($targetStudentId) {
    // Fetch Student Info
    $stmt = $pdo->prepare(
        "SELECT s.*, class_map.class_names
         FROM students s
         LEFT JOIN (
            SELECT sc.student_id, GROUP_CONCAT(c.class_name ORDER BY c.class_name SEPARATOR ', ') AS class_names
            FROM student_classes sc
            JOIN classes c ON c.id = sc.class_id
            GROUP BY sc.student_id
         ) class_map ON class_map.student_id = s.id
         WHERE s.id = ? AND s.is_active = 1"
    );
    $stmt->execute([$targetStudentId]);
    $studentInfo = $stmt->fetch();

    if ($studentInfo) {
        // Fetch student grades joined with subject credits
        $gStmt = $pdo->prepare("
            SELECT g.*, sub.subject_code, sub.subject_name, sub.credit 
            FROM grades g
            JOIN subjects sub ON g.subject_id = sub.id
            WHERE g.student_id = ?
            ORDER BY g.academic_year ASC, g.semester ASC, sub.subject_code ASC
        ");
        $gStmt->execute([$targetStudentId]);
        $grades = $gStmt->fetchAll();

        $totalWeightedScore = 0.0;
        $totalWeightedPoint = 0.0;

        foreach ($grades as $grade) {
            $credit = intval($grade['credit']);
            $avgScore = floatval($grade['average_score']);
            $point = gradePoint($grade['letter_grade']);

            $totalCredits += $credit;
            $totalWeightedScore += $avgScore * $credit;
            $totalWeightedPoint += $point * $credit;

            // Semester breakdown grouping
            $termKey = $grade['academic_year'] . ' - Học kỳ ' . $grade['semester'];
            if (!isset($semesterBreakdown[$termKey])) {
                $semesterBreakdown[$termKey] = [
                    'credits' => 0,
                    'score_sum' => 0.0,
                    'point_sum' => 0.0
                ];
            }
            $semesterBreakdown[$termKey]['credits'] += $credit;
            $semesterBreakdown[$termKey]['score_sum'] += $avgScore * $credit;
            $semesterBreakdown[$termKey]['point_sum'] += $point * $credit;
        }

        if ($totalCredits > 0) {
            $gpa10 = round($totalWeightedScore / $totalCredits, 2);
            $gpa4 = round($totalWeightedPoint / $totalCredits, 2);
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold hust-text-gradient"><i class="fa fa-calculator text-hust me-2"></i>Tính toán GPA & CPA Tích lũy</h2>
            <p class="text-muted mb-0">Công cụ đo lường và theo dõi tiến trình học tập học phần theo hệ thống đào tạo tín chỉ HUST.</p>
        </div>
        <?php if ($role === 'admin'): ?>
            <a href="<?php echo BASE_PATH; ?>grades/manage.php" class="btn btn-hust rounded-pill px-4">
                <i class="fa fa-plus me-1"></i> Nhập điểm mới
            </a>
        <?php endif; ?>
    </div>

    <?php if ($role === 'admin'): ?>
        <!-- Selector for Admin -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="fa fa-user-graduate text-muted me-2"></i>Chọn sinh viên để xem tiến độ</h5>
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <select name="student_id" class="form-select py-3 px-3 rounded-3" required>
                        <option value="">-- Chọn sinh viên --</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $targetStudentId == $s['id'] ? 'selected' : ''; ?>>
                                <?php echo e($s['student_code'] . ' - ' . $s['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100 py-3 rounded-3 fw-bold">
                        <i class="fa fa-chart-bar me-1"></i> Xem báo cáo GPA
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($targetStudentId && $studentInfo): ?>
        <!-- GPA Overview Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100 border-start border-danger border-4">
                    <div class="display-6 fw-bold text-danger mb-1"><?php echo number_format($gpa10, 2); ?></div>
                    <div class="text-muted text-uppercase tracking-wider small fw-semibold">GPA (Hệ 10)</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100 border-start border-success border-4">
                    <div class="display-6 fw-bold text-success mb-1"><?php echo number_format($gpa4, 2); ?></div>
                    <div class="text-muted text-uppercase tracking-wider small fw-semibold">CPA / GPA (Hệ 4)</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100 border-start border-primary border-4">
                    <div class="display-6 fw-bold text-primary mb-1"><?php echo $totalCredits; ?></div>
                    <div class="text-muted text-uppercase tracking-wider small fw-semibold">Số tín chỉ tích lũy</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100 border-start border-warning border-4 d-flex flex-column justify-content-center">
                    <div class="fs-4 fw-bold mb-1 text-dark"><?php echo gpaStatusLabel($gpa10); ?></div>
                    <div class="text-muted text-uppercase tracking-wider small fw-semibold">Xếp loại học lực</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Detailed Grades Breakdown -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-4"><i class="fa fa-list-check text-muted me-2"></i>Bảng điểm học phần</h5>
                    <?php if (empty($grades)): ?>
                        <p class="text-muted py-4 text-center">Chưa có bản ghi điểm học phần nào.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã HP</th>
                                        <th>Tên học phần</th>
                                        <th class="text-center">Số TC</th>
                                        <th class="text-center">Giữa kỳ</th>
                                        <th class="text-center">Cuối kỳ</th>
                                        <th class="text-center">Khác</th>
                                        <th class="text-center">Trung bình</th>
                                        <th class="text-center">Điểm chữ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $g): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?php echo e($g['subject_code']); ?></td>
                                            <td><?php echo e($g['subject_name']); ?></td>
                                            <td class="text-center fw-medium"><?php echo e($g['credit']); ?></td>
                                            <td class="text-center text-muted"><?php echo e($g['midterm_score']); ?></td>
                                            <td class="text-center text-muted"><?php echo e($g['final_score']); ?></td>
                                            <td class="text-center text-muted"><?php echo e($g['other_score']); ?></td>
                                            <td class="text-center fw-bold text-dark"><?php echo e($g['average_score']); ?></td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill px-3 py-2 fw-bold bg-light text-dark border">
                                                    <?php echo e($g['letter_grade']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Semester Progress & Student Details -->
            <div class="col-lg-4">
                <!-- Student Profile Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3"><i class="fa fa-id-card text-muted me-2"></i>Thông tin sinh viên</h5>
                    <div class="mb-2"><strong>Mã sinh viên:</strong> <?php echo e($studentInfo['student_code']); ?></div>
                    <div class="mb-2"><strong>Họ và tên:</strong> <?php echo e($studentInfo['full_name']); ?></div>
                    <div class="mb-2">
                        <strong>Lớp học:</strong>
                        <?php $studentClasses = array_filter(array_map('trim', explode(',', $studentInfo['class_names'] ?? ''))); ?>
                        <?php if (empty($studentClasses)): ?>
                            <span>Chưa chỉ định</span>
                        <?php else: ?>
                            <span class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo count($studentClasses); ?> lớp
                                </button>
                                <span class="dropdown-menu">
                                    <?php foreach ($studentClasses as $className): ?>
                                        <span class="dropdown-item-text"><?php echo e($className); ?></span>
                                    <?php endforeach; ?>
                                </span>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2"><strong>Email:</strong> <?php echo e($studentInfo['email'] ?: 'Chưa cập nhật'); ?></div>
                </div>

                <!-- Semester Progress Tracking -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-3"><i class="fa fa-line-chart text-muted me-2"></i>Tiến trình qua các học kỳ</h5>
                    <?php if (empty($semesterBreakdown)): ?>
                        <p class="text-muted small text-center mb-0">Chưa có tiến trình học tập.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($semesterBreakdown as $term => $stats): 
                                $termGpa10 = round($stats['score_sum'] / $stats['credits'], 2);
                                $termGpa4 = round($stats['point_sum'] / $stats['credits'], 2);
                            ?>
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fw-bold text-dark small mb-2"><?php echo e($term); ?></div>
                                    <div class="row g-1 text-center">
                                        <div class="col-4">
                                            <div class="text-muted xsmall">Số TC</div>
                                            <div class="fw-semibold text-dark"><?php echo $stats['credits']; ?></div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted xsmall">GPA (10)</div>
                                            <div class="fw-semibold text-danger"><?php echo number_format($termGpa10, 2); ?></div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted xsmall">CPA (4)</div>
                                            <div class="fw-semibold text-success"><?php echo number_format($termGpa4, 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($targetStudentId): ?>
        <div class="alert alert-danger rounded-3 shadow-sm mt-3">
            <i class="fa fa-exclamation-triangle me-2"></i> Sinh viên không tồn tại hoặc đã bị vô hiệu hóa.
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="text-muted mb-3" style="font-size: 3.5rem;">
                <i class="fa fa-calculator"></i>
            </div>
            <h4 class="fw-bold">Xem kết quả tính toán GPA & CPA</h4>
            <p class="text-muted max-w-lg mx-auto mb-0">Vui lòng chọn một sinh viên từ danh sách ở trên để bắt đầu tính toán và hiển thị tiến trình học tập.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.xsmall {
    font-size: 0.75rem;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
