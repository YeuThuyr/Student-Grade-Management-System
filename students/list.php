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
    $where[] = 's.class_id = ?';
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
    "SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone, s.created_at, c.class_name,
            ROUND(AVG(g.average_score), 2) AS gpa
     FROM students s
     LEFT JOIN grades g ON s.id = g.student_id
     LEFT JOIN classes c ON s.class_id = c.id
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Quản lý sinh viên</h2>
            <p class="text-muted">Thêm, sửa, tìm kiếm và hiển thị danh sách sinh viên.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>students/add.php" class="btn btn-hust">Thêm sinh viên</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                    placeholder="Mã sinh viên, tên hoặc email">
            </div>
            <div class="col-md-4">
                <label class="form-label">Lớp</label>
                <select name="class_id" class="form-select">
                    <option value="">Tất cả lớp</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo e($class['id']); ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>><?php echo e($class['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Lọc</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Mã SV</th>
                        <th>Họ và tên</th>
                        <th>Lớp</th>
                        <th>Email</th>
                        <th>GPA</th>
                        <th>Ngày đăng ký</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Không tìm thấy sinh viên nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?php echo e($offset + $index + 1); ?></td>
                                <td><?php echo e($student['student_code']); ?></td>
                                <td><?php echo e($student['full_name']); ?></td>
                                <td><?php echo e($student['class_name'] ?? 'Chưa có'); ?></td>
                                <td><?php echo e($student['email']); ?></td>
                                <td><?php echo e($student['gpa'] !== null ? number_format($student['gpa'], 2) : '—'); ?></td>
                                <td><?php echo e(date('d/m/Y', strtotime($student['created_at']))); ?></td>
                                <td>
                                    <a href="<?php echo BASE_PATH; ?>students/edit.php?id=<?php echo e($student['id']); ?>"
                                        class="btn btn-sm btn-outline-primary me-2">Sửa</a>
                                    <a href="<?php echo BASE_PATH; ?>students/delete.php?id=<?php echo e($student['id']); ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Bạn có chắc muốn vô hiệu sinh viên này?');">Vô hiệu</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4" aria-label="Student pagination">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?search=<?php echo urlencode($search); ?>&class_id=<?php echo urlencode($classFilter); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>