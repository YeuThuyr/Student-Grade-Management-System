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

$where = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(c.class_code LIKE ? OR c.class_name LIKE ? OR c.major LIKE ? OR c.teacher LIKE ? OR c.status LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare(
    "SELECT c.*, COUNT(s.id) AS student_count
     FROM classes c
     LEFT JOIN students s ON s.class_id = c.id AND s.is_active = 1
     WHERE $whereSql
     GROUP BY c.id
     ORDER BY c.class_code ASC"
);
$stmt->execute($params);
$classes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold" data-i18n="class_list_title">Quản lý lớp</h2>
            <p class="text-muted" data-i18n="class_list_desc">Tạo và cập nhật lớp học.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>classes/add.php" class="btn btn-hust" data-i18n="class_add_btn">Thêm lớp</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label" data-i18n="common_search">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                    placeholder="Mã lớp, tên lớp, chuyên ngành hoặc giáo viên" data-i18n-placeholder="class_search_ph">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100" data-i18n="common_search">Tìm kiếm</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3 text-muted fw-semibold">#</th>
                        <th class="px-4 py-3 text-muted fw-semibold">Class Code</th>
                        <th class="px-4 py-3 text-muted fw-semibold">Class Name</th>
                        <th class="px-4 py-3 text-muted fw-semibold">Major</th>
                        <th class="px-4 py-3 text-muted fw-semibold text-center">Students</th>
                        <th class="px-4 py-3 text-muted fw-semibold">Teacher</th>
                        <th class="px-4 py-3 text-muted fw-semibold">Status</th>
                        <th class="px-4 py-3 text-muted fw-semibold">Description</th>
                        <th class="px-4 py-3 text-muted fw-semibold text-end" data-i18n="common_actions">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5" data-i18n="class_no_results">Không tìm thấy lớp học.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classes as $index => $class): ?>
                            <tr>
                                <td class="px-4 py-3 text-muted"><?php echo e($index + 1); ?></td>
                                <td class="px-4 py-3 fw-bold text-dark"><?php echo e($class['class_code']); ?></td>
                                <td class="px-4 py-3 fw-semibold"><?php echo e($class['class_name']); ?></td>
                                <td class="px-4 py-3 text-muted"><?php echo e($class['major'] ?: '—'); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <?php echo e($class['student_count']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted"><?php echo e($class['teacher'] ?: '—'); ?></td>
                                <td class="px-4 py-3">
                                    <?php if (($class['status'] ?? '') === 'Active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-muted"><?php echo e($class['description']); ?></td>
                                <td class="px-4 py-3 text-end">
                                    <a href="<?php echo BASE_PATH; ?>classes/edit.php?id=<?php echo e($class['id']); ?>"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2" data-i18n="common_edit">Sửa</a>
                                    <a href="<?php echo BASE_PATH; ?>classes/delete.php?id=<?php echo e($class['id']); ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="return confirm('Bạn có chắc muốn xóa lớp này?');" data-i18n="common_delete">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
