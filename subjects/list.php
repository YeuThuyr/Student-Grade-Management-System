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
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(subject_code LIKE ? OR subject_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$query = "SELECT * FROM subjects WHERE $whereSql ORDER BY subject_code ASC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$subjects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold" data-i18n="subj_list_title">Quản lý môn học</h2>
            <p class="text-muted" data-i18n="subj_list_desc">Thêm, sửa và xóa thông tin môn học.</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>subjects/add.php" class="btn btn-hust" data-i18n="subj_add_btn">Thêm môn học</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label" data-i18n="common_search">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                    placeholder="Mã môn học hoặc tên môn" data-i18n-placeholder="subj_search_ph">
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
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="subj_th_code">Mã môn học</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="subj_th_name">Tên môn học</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="th_credits">Tín chỉ</th>
                        <th class="px-4 py-3 text-muted fw-semibold" data-i18n="subj_th_desc">Miêu tả</th>
                        <th class="px-4 py-3 text-muted fw-semibold text-end" data-i18n="common_actions">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5" data-i18n="subj_no_results">Không tìm thấy môn học.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $index => $subject): ?>
                            <tr>
                                <td class="px-4 py-3 text-muted"><?php echo e($offset + $index + 1); ?></td>
                                <td class="px-4 py-3 fw-bold text-dark"><?php echo e($subject['subject_code']); ?></td>
                                <td class="px-4 py-3 fw-semibold"><?php echo e($subject['subject_name']); ?></td>
                                <td class="px-4 py-3"><?php echo e($subject['credit']); ?></td>
                                <td class="px-4 py-3 text-muted"><?php echo e($subject['description']); ?></td>
                                <td class="px-4 py-3 text-end">
                                    <a href="<?php echo BASE_PATH; ?>subjects/edit.php?id=<?php echo e($subject['id']); ?>"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2" data-i18n="common_edit">Sửa</a>
                                    <a href="<?php echo BASE_PATH; ?>subjects/delete.php?id=<?php echo e($subject['id']); ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="return confirm('Bạn có chắc muốn xóa môn học này?');" data-i18n="common_delete">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4" aria-label="Subject pagination">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?search=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>