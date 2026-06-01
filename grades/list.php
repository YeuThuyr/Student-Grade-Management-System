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
$studentId = $_SESSION['user']['student_id'] ?? null;

$search = trim($_GET['search'] ?? '');
$subjectId = trim($_GET['subject_id'] ?? '');
$semester = trim($_GET['semester'] ?? '');
$academicYear = trim($_GET['academic_year'] ?? '');

$where = ['1=1'];
$params = [];

if ($role === 'student') {
    $where[] = 'g.student_id = ?';
    $params[] = $studentId;
} elseif ($search !== '') {
    $where[] = '(s.student_code LIKE ? OR s.full_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($subjectId !== '') {
    $where[] = 'g.subject_id = ?';
    $params[] = $subjectId;
}
if ($semester !== '') {
    $where[] = 'g.semester = ?';
    $params[] = $semester;
}
if ($academicYear !== '') {
    $where[] = 'g.academic_year = ?';
    $params[] = $academicYear;
}
$whereSql = implode(' AND ', $where);

$grades = [];
$hasFilter = ($search !== '' || $subjectId !== '' || $semester !== '' || $academicYear !== '');

if ($hasFilter) {
    $gradesStmt = $pdo->prepare(
        "SELECT g.id, g.semester, g.academic_year, g.midterm_score, g.final_score, g.other_score, g.average_score, g.letter_grade,
                s.student_code, s.full_name, subj.subject_code AS subject_code, subj.subject_name
         FROM grades g
         JOIN students s ON g.student_id = s.id
         JOIN subjects subj ON g.subject_id = subj.id
         WHERE $whereSql
         ORDER BY g.academic_year DESC, g.semester ASC, s.student_code ASC"
    );
    $gradesStmt->execute($params);
    $grades = $gradesStmt->fetchAll();
}

$subjectStmt = $pdo->query('SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code ASC');
$subjects = $subjectStmt->fetchAll();

$yearStmt = $pdo->query('SELECT DISTINCT academic_year FROM grades WHERE academic_year IS NOT NULL ORDER BY academic_year DESC');
$years = $yearStmt->fetchAll();
$semesterStmt = $pdo->query('SELECT DISTINCT semester FROM grades WHERE semester IS NOT NULL ORDER BY semester ASC');
$semesters = $semesterStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold" data-i18n="grade_list_title">Danh sách điểm</h2>
            <p class="text-muted" data-i18n="grade_list_desc">Xem và lọc điểm theo môn, học kỳ và năm học.</p>
        </div>
        <?php if ($role === 'admin'): ?>
            <a href="<?php echo BASE_PATH; ?>grades/manage.php" class="btn btn-hust" data-i18n="grade_add_edit">Thêm/Chỉnh sửa điểm</a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 align-items-end">
            <?php if ($role === 'admin'): ?>
                <div class="col-md-4">
                    <label class="form-label" data-i18n="grade_search_student">Tìm sinh viên</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                        placeholder="Mã SV hoặc tên">
                </div>
            <?php endif; ?>
            <div class="col-md-2">
                <label class="form-label" data-i18n="nav_subjects">Môn học</label>
                <select name="subject_id" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo e($subject['id']); ?>" <?php echo $subjectId == $subject['id'] ? 'selected' : ''; ?>><?php echo e($subject['subject_code'] . ' - ' . $subject['subject_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" data-i18n="grade_semester">Học kỳ</label>
                <select name="semester" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($semesters as $row): ?>
                        <option value="<?php echo e($row['semester']); ?>" <?php echo $semester == $row['semester'] ? 'selected' : ''; ?>><?php echo e($row['semester']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" data-i18n="label_academic_year">Năm học</label>
                <select name="academic_year" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($years as $row): ?>
                        <option value="<?php echo e($row['academic_year']); ?>" <?php echo $academicYear == $row['academic_year'] ? 'selected' : ''; ?>>
                            <?php echo e($row['academic_year']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-outline-primary w-100" data-i18n="common_filter">Lọc</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th data-i18n="grade_th_student">Sinh viên</th>
                        <th data-i18n="th_subject">Môn học</th>
                        <th data-i18n="th_semester">HK</th>
                        <th data-i18n="th_year">Năm học</th>
                        <th data-i18n="th_midterm">Giữa kỳ</th>
                        <th data-i18n="th_final">Cuối kỳ</th>
                        <th data-i18n="th_other">Khác</th>
                        <th data-i18n="th_average">TB</th>
                        <th data-i18n="th_letter">Điểm chữ</th>
                        <?php if ($role === 'admin'): ?>
                            <th data-i18n="common_actions">Hành động</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$hasFilter): ?>
                        <tr>
                            <td colspan="<?php echo $role === 'admin' ? 11 : 10; ?>" class="text-center text-muted py-4" data-i18n="grade_filter_prompt">
                                Vui lòng nhập hoặc chọn ít nhất một bộ lọc để xem điểm.</td>
                        </tr>
                    <?php elseif (empty($grades)): ?>
                        <tr>
                            <td colspan="<?php echo $role === 'admin' ? 11 : 10; ?>" class="text-center text-muted py-4" data-i18n="grade_no_results">
                                Không có bản ghi điểm phù hợp.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grades as $index => $grade): ?>
                            <tr>
                                <td class="ps-4"><?php echo e($index + 1); ?></td>
                                <td><?php echo e($grade['student_code'] . ' - ' . $grade['full_name']); ?></td>
                                <td><?php echo e($grade['subject_code'] . ' - ' . $grade['subject_name']); ?></td>
                                <td><?php echo e($grade['semester']); ?></td>
                                <td><?php echo e($grade['academic_year']); ?></td>
                                <td><?php echo e($grade['midterm_score']); ?></td>
                                <td><?php echo e($grade['final_score']); ?></td>
                                <td><?php echo e($grade['other_score']); ?></td>
                                <td><?php echo e($grade['average_score']); ?></td>
                                <td><?php echo e($grade['letter_grade']); ?></td>
                                <?php if ($role === 'admin'): ?>
                                    <td>
                                        <a href="<?php echo BASE_PATH; ?>grades/manage.php?id=<?php echo e($grade['id']); ?>"
                                            class="btn btn-sm btn-outline-primary" data-i18n="common_edit">Sửa</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
