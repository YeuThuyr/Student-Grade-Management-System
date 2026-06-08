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

$search = trim($_GET['search'] ?? '');
$classFilter = trim($_GET['class_id'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ['s.is_active = 1'];
$params = [];

if ($search !== '') {
    $where[] = '(s.student_code LIKE ? OR s.full_name LIKE ? OR s.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($classFilter !== '') {
    $where[] = 'EXISTS (SELECT 1 FROM student_classes scf WHERE scf.student_id = s.id AND scf.class_id = ?)';
    $params[] = $classFilter;
}
$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM students s WHERE $whereSql"
);
$countStmt->execute($params);
$totalStudents = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalStudents / $perPage));

$query =
    "SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone, s.created_at, MAX(class_map.class_names) AS class_names,
            ROUND(SUM(
                (CASE g.letter_grade
                    WHEN 'A+' THEN 4.0
                    WHEN 'A' THEN 3.7
                    WHEN 'B+' THEN 3.5
                    WHEN 'B' THEN 3.0
                    WHEN 'C+' THEN 2.5
                    WHEN 'C' THEN 2.0
                    WHEN 'D' THEN 1.0
                    ELSE 0.0
                END) * subj.credit
            ) / SUM(subj.credit), 2) AS gpa
     FROM students s
     LEFT JOIN grades g ON s.id = g.student_id
     LEFT JOIN subjects subj ON g.subject_id = subj.id
     LEFT JOIN (
        SELECT sc.student_id, GROUP_CONCAT(c.class_name ORDER BY c.class_name SEPARATOR ', ') AS class_names
        FROM student_classes sc
        JOIN classes c ON c.id = sc.class_id
        GROUP BY sc.student_id
     ) class_map ON class_map.student_id = s.id
     WHERE $whereSql
     GROUP BY s.id
     ORDER BY s.student_code ASC
     LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

$classStmt = $pdo->query('SELECT id, class_name FROM classes ORDER BY class_name ASC');
$classes = $classStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold" data-i18n="stu_list_title">Quản lý sinh viên</h2>
            <p class="text-muted mb-0" data-i18n="stu_list_desc">Thêm, sửa, tìm kiếm và hiển thị danh sách sinh viên.</p>
        </div>
        <div>
            <a href="<?php echo BASE_PATH; ?>students/add.php" class="btn btn-hust" data-i18n="stu_add_btn">Thêm sinh viên</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" data-i18n="common_search">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                    placeholder="Mã sinh viên, tên hoặc email">
            </div>
            <div class="col-md-4">
                <label class="form-label" data-i18n="common_class">Lớp</label>
                <select name="class_id" class="form-select">
                    <option value="" data-i18n="stu_all_classes">Tất cả lớp</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo e($class['id']); ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>><?php echo e($class['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100" data-i18n="common_filter">Lọc</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3 text-muted fw-semibold">#</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="stu_th_code">Mã SV</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="stu_th_name">Họ và tên</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="common_class">Lớp</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="stu_th_email">Email</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="stu_th_gpa">GPA</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="stu_th_reg_date">Ngày đăng ký</th>
                        <th class="px-4 py-3 text-muted fw-semibold text-end" data-i18n="common_actions">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5" data-i18n="stu_no_results">Không tìm thấy sinh viên nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td class="px-4 py-3 text-muted"><?php echo e($offset + $index + 1); ?></td>
                                <td class="px-4 py-3 fw-bold text-dark"><?php echo e($student['student_code']); ?></td>
                                <td class="px-4 py-3 fw-semibold"><?php echo e($student['full_name']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">
                                        <?php echo e($student['class_names'] ?? 'Chưa có'); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted"><?php echo e($student['email']); ?></td>
                                <td class="px-4 py-3">
                                    <strong class="text-success fs-6"><?php echo e($student['gpa'] !== null ? number_format($student['gpa'], 2) : '—'); ?></strong>
                                </td>
                                <td class="px-4 py-3 text-muted"><?php echo e(date('d/m/Y', strtotime($student['created_at']))); ?></td>
                                <td class="px-4 py-3 text-end">
                                    <a href="<?php echo BASE_PATH; ?>students/edit.php?id=<?php echo e($student['id']); ?>"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" data-i18n="common_edit">Sửa</a>
                                    <a href="<?php echo BASE_PATH; ?>students/delete.php?id=<?php echo e($student['id']); ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="return confirm('Bạn có chắc muốn vô hiệu sinh viên này?');" data-i18n="stu_deactivate">Vô hiệu</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    if ($totalPages > 1):
        $range = 2; 
        $startPage = max(1, $page - $range);
        $endPage = min($totalPages, $page + $range);
        
        if ($page - 1 < $range) {
            $endPage = min($totalPages, $startPage + ($range * 2));
        }
        if ($totalPages - $page < $range) {
            $startPage = max(1, $endPage - ($range * 2));
        }
        
        $pageLink = function($p) use ($search, $classFilter) {
            return '?search=' . urlencode($search) . '&class_id=' . urlencode($classFilter) . '&page=' . $p;
        };
    ?>
        <nav class="mt-4" aria-label="Student pagination">
            <ul class="pagination justify-content-center align-items-center gap-1">
                <!-- First Page -->
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center" 
                           href="<?php echo $pageLink(1); ?>" title="Trang đầu" style="width: 40px; height: 40px; color: #555;">
                            <i class="fa fa-angle-double-left"></i>
                        </a>
                    </li>
                    <!-- Previous Page -->
                    <li class="page-item">
                        <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center" 
                           href="<?php echo $pageLink($page - 1); ?>" title="Trang trước" style="width: 40px; height: 40px; color: #555;">
                            <i class="fa fa-angle-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($startPage > 1): ?>
                    <li class="page-item"><span class="page-link border-0 bg-transparent text-muted px-2">...</span></li>
                <?php endif; ?>

                <!-- Page numbers -->
                <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center fw-bold" 
                           href="<?php echo $pageLink($p); ?>" style="width: 40px; height: 40px; <?php echo $p === $page ? 'background-color: var(--hust-red); color: white;' : 'color: #555;'; ?>">
                            <?php echo $p; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <li class="page-item"><span class="page-link border-0 bg-transparent text-muted px-2">...</span></li>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center" 
                           href="<?php echo $pageLink($page + 1); ?>" title="Trang sau" style="width: 40px; height: 40px; color: #555;">
                            <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                    <!-- Last Page -->
                    <li class="page-item">
                        <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center" 
                           href="<?php echo $pageLink($totalPages); ?>" title="Trang cuối" style="width: 40px; height: 40px; color: #555;">
                            <i class="fa fa-angle-double-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
