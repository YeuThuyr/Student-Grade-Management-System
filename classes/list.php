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
    $where[] = '(class_code LIKE ? OR class_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT * FROM classes WHERE $whereSql ORDER BY class_code ASC");
$stmt->execute($params);
$classes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Quản lý lớp</h2>
            <p class="text-muted">Tạo và cập nhật lớp học.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>classes/add.php" class="btn btn-hust">Thêm lớp</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                    placeholder="Mã lớp hoặc tên lớp">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Tìm kiếm</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Mã lớp</th>
                        <th>Tên lớp</th>
                        <th>Mô tả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy lớp học.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classes as $index => $class): ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e($class['class_code']); ?></td>
                                <td><?php echo e($class['class_name']); ?></td>
                                <td><?php echo e($class['description']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_PATH; ?>classes/edit.php?id=<?php echo e($class['id']); ?>"
                                        class="btn btn-sm btn-outline-primary me-2">Sửa</a>
                                    <a href="<?php echo BASE_PATH; ?>classes/delete.php?id=<?php echo e($class['id']); ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Bạn có chắc muốn xóa lớp này?');">Xóa</a>
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