<?php
session_start();
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/middleware/role.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
handleAuth();
checkRole(['admin']);
require_once __DIR__ . '/stats/summary.php'; // This provides $summary array

// Fetch distinct academic years for the filter
$years = [];
$y_res = $conn->query("SELECT DISTINCT academic_year FROM grades WHERE academic_year IS NOT NULL ORDER BY academic_year DESC");
while ($row = $y_res->fetch_assoc())
    $years[] = $row['academic_year'];

// --- Search logic ---
$search_code = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$f_year = isset($_GET['academic_year']) ? trim($_GET['academic_year']) : '';
$f_gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$f_gpa = isset($_GET['gpa_range']) ? trim($_GET['gpa_range']) : '';

$search_results = [];
$search_performed = ($search_code !== '' || $f_year !== '' || $f_gender !== '' || $f_gpa !== '');

if ($search_performed) {
    $where_clauses = ["1=1"];
    $params = [];
    $types = "";

    if ($search_code !== '') {
        $where_clauses[] = "s.student_code LIKE ?";
        $params[] = $search_code . '%';
        $types .= "s";
    }
    if ($f_gender !== '') {
        $where_clauses[] = "s.gender = ?";
        $params[] = $f_gender;
        $types .= "s";
    }

    $where_sql = implode(" AND ", $where_clauses);

    $join_clause = "LEFT JOIN grades g ON s.id = g.student_id";
    if ($f_year !== '') {
        $where_clauses[] = "g.academic_year = ?";
        $params[] = $f_year;
        $types .= "s";
        $where_sql = implode(" AND ", $where_clauses);
        // Change to INNER JOIN to ensure we only get students who actually have grades in this year
        $join_clause = "JOIN grades g ON s.id = g.student_id";
    }

    $having_clause = "";
    if ($f_gpa === 'excellent')
        $having_clause = " HAVING gpa >= 8.0";
    elseif ($f_gpa === 'good')
        $having_clause = " HAVING gpa >= 6.5 AND gpa < 8.0";
    elseif ($f_gpa === 'average')
        $having_clause = " HAVING gpa >= 5.0 AND gpa < 6.5";
    elseif ($f_gpa === 'weak')
        $having_clause = " HAVING gpa < 5.0";

    $stmt = $conn->prepare("
        SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone,
               ROUND(AVG(g.average_score), 2) as gpa
        FROM students s
        $join_clause
        WHERE $where_sql
        GROUP BY s.id
        $having_clause
        ORDER BY s.student_code ASC
    ");

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    $stmt->close();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold text-dark"><i class="fas fa-chart-line me-2 text-danger"></i><span data-i18n="dash_title">Dashboard Thống Kê</span></h2>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i><span data-i18n="dash_back">Quay lại</span>
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                <div class="display-5 fw-bold text-primary mb-2"><?php echo $summary['total_students']; ?></div>
                <div class="text-muted text-uppercase tracking-wider small fw-bold" data-i18n="dash_total_students">Tổng số sinh viên</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                <div class="display-5 fw-bold text-success mb-2"><?php echo $summary['average_gpa']; ?></div>
                <div class="text-muted text-uppercase tracking-wider small fw-bold" data-i18n="dash_avg_gpa">GPA Trung Bình</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                <div class="display-5 fw-bold text-danger mb-2"><?php echo $summary['pass_rate']; ?>%</div>
                <div class="text-muted text-uppercase tracking-wider small fw-bold" data-i18n="dash_pass_rate">Tỷ lệ Đạt</div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white search-section">
        <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-search me-2 text-danger"></i><span data-i18n="dash_search_title">Tìm kiếm Sinh viên theo Mã số</span>
        </h5>
        <form method="GET" action="dashboard.php" class="search-form" id="searchForm">
            <div class="input-group search-input-group">
                <span class="input-group-text search-icon-box">
                    <i class="fas fa-id-badge"></i>
                </span>
                <input type="text" name="student_code" id="searchStudentCode" class="form-control search-input"
                    placeholder="Nhập mã sinh viên (VD: 2023, 20239614...)"
                    value="<?php echo htmlspecialchars($search_code); ?>" maxlength="20" autocomplete="off">
                <button type="submit" class="btn search-btn" id="searchBtn">
                    <i class="fas fa-search me-1"></i> <span data-i18n="dash_search_btn">Tìm kiếm</span>
                </button>
                <?php if ($search_performed): ?>
                    <a href="dashboard.php" class="btn search-clear-btn" title="Xóa tìm kiếm">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Filters row -->
            <div class="row g-3 mt-2 user-select-none">
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted small fw-bold mb-1" data-i18n="dash_filter_year">Năm học</label>
                    <select name="academic_year" class="form-select filter-select"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" data-i18n="dash_all_years">Tất cả các năm</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php if ($f_year === $year)
                                   echo 'selected'; ?>>Năm học <?php echo htmlspecialchars($year); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted small fw-bold mb-1" data-i18n="dash_filter_gender">Giới tính</label>
                    <select name="gender" class="form-select filter-select"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" data-i18n="dash_all">Tất cả</option>
                        <option value="Male" <?php if ($f_gender === 'Male')
                            echo 'selected'; ?> data-i18n="dash_male">Nam</option>
                        <option value="Female" <?php if ($f_gender === 'Female')
                            echo 'selected'; ?> data-i18n="dash_female">Nữ</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted small fw-bold mb-1" data-i18n="dash_filter_gpa">Xếp loại GPA</label>
                    <select name="gpa_range" class="form-select filter-select"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" data-i18n="dash_all_gpa">Tất cả mức điểm</option>
                        <option value="excellent" <?php if ($f_gpa === 'excellent')
                            echo 'selected'; ?>>Xuất sắc (≥ 8.0)
                        </option>
                        <option value="good" <?php if ($f_gpa === 'good')
                            echo 'selected'; ?>>Khá (6.5 - 7.9)</option>
                        <option value="average" <?php if ($f_gpa === 'average')
                            echo 'selected'; ?>>Trung bình (5.0 - 6.4)
                        </option>
                        <option value="weak" <?php if ($f_gpa === 'weak')
                            echo 'selected'; ?>>Yếu (< 5.0)</option>
                    </select>
                </div>
            </div>

            <div class="search-hint mt-3">
                <i class="fas fa-info-circle me-1"></i>
                Có thể kết hợp tìm kiếm mã số và các bộ lọc. Biểu đồ và thẻ tóm tắt cũng sẽ tự động đồng bộ theo bộ lọc
                này.
        </form>

        <?php if ($search_performed): ?>
            <!-- Search Results -->
            <div class="search-results mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        Kết quả tìm kiếm cho "<span
                            class="text-danger"><?php echo htmlspecialchars($search_code); ?></span>"
                    </h6>
                    <span class="badge search-result-badge"><?php echo count($search_results); ?> kết quả</span>
                </div>

                <?php if (count($search_results) > 0): ?>
                    <div class="table-responsive search-results-table-container">
                        <table class="table search-table align-middle" id="searchResultsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Mã SV</th>
                                    <th>Họ và tên</th>
                                    <th>Ngày sinh</th>
                                    <th>Giới tính</th>
                                    <th>Email</th>
                                    <th>SĐT</th>
                                    <th>GPA TB</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $index => $student): ?>
                                    <tr class="search-result-row" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                                        <td class="text-muted"><?php echo $index + 1; ?></td>
                                        <td>
                                            <span
                                                class="student-code-badge"><?php echo htmlspecialchars($student['student_code']); ?></span>
                                        </td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : '—'; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['gender'] === 'Male'): ?>
                                                <span class="gender-badge gender-male"><i class="fas fa-mars me-1"></i>Nam</span>
                                            <?php elseif ($student['gender'] === 'Female'): ?>
                                                <span class="gender-badge gender-female"><i class="fas fa-venus me-1"></i>Nữ</span>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted"><?php echo htmlspecialchars($student['email'] ?? '—'); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($student['phone'] ?? '—'); ?></td>
                                        <td>
                                            <?php if ($student['gpa'] !== null): ?>
                                                <?php
                                                $gpa = (float) $student['gpa'];
                                                $gpa_class = 'gpa-low';
                                                if ($gpa >= 8.0)
                                                    $gpa_class = 'gpa-excellent';
                                                elseif ($gpa >= 6.5)
                                                    $gpa_class = 'gpa-good';
                                                elseif ($gpa >= 5.0)
                                                    $gpa_class = 'gpa-average';
                                                ?>
                                                <span class="gpa-badge <?php echo $gpa_class; ?>"><?php echo $student['gpa']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-results text-center py-5">
                        <div class="no-results-icon mb-3">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Không tìm thấy sinh viên</h6>
                        <p class="text-muted mb-0">Không có sinh viên nào có mã bắt đầu bằng
                            "<strong><?php echo htmlspecialchars($search_code); ?></strong>".<br>Hãy thử nhập lại mã sinh viên
                            khác.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Charts Row -->
    <div class="row g-4">
        <!-- Pass/Fail Chart -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h5 class="fw-bold mb-4 text-dark text-center" data-i18n="dash_pass_fail_chart">Tỷ lệ Đạt / Trượt</h5>
                <div style="height: 300px;">
                    <canvas id="passFailChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Grade Distribution Chart -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h5 class="fw-bold mb-4 text-dark text-center" data-i18n="dash_grade_dist_chart">Phân bố điểm chữ</h5>
                <div style="height: 300px;">
                    <canvas id="gradeDistChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Fetch data for charts with current filters
        const queryParams = window.location.search;
        fetch('stats/chart_data.php' + queryParams)
            .then(response => response.json())
            .then(data => {
                // Pass/Fail Chart
                const ctxPF = document.getElementById('passFailChart').getContext('2d');
                new Chart(ctxPF, {
                    type: 'doughnut',
                    data: {
                        labels: data.pass_fail.labels,
                        datasets: [{
                            data: data.pass_fail.data,
                            backgroundColor: ['#28a745', '#dc3545'],
                            hoverOffset: 10,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        cutout: '70%'
                    }
                });

                // Grade Distribution Chart
                const ctxGD = document.getElementById('gradeDistChart').getContext('2d');
                new Chart(ctxGD, {
                    type: 'bar',
                    data: {
                        labels: data.grade_distribution.labels,
                        datasets: [{
                            label: 'Số lượng sinh viên',
                            data: data.grade_distribution.data,
                            backgroundColor: '#dc3545',
                            borderRadius: 8,
                            barThickness: 30
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error fetching chart data:', error));
    });
</script>

<style>
    .rounded-4 {
        border-radius: 1rem !important;
    }

    .tracking-wider {
        letter-spacing: 0.05em;
    }

    .display-5 {
        font-size: 3rem;
    }

    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    }

    /* ===== Search Section ===== */
    .search-section {
        border-left: 4px solid #dc3545 !important;
    }

    .search-section:hover {
        transform: none !important;
    }

    .search-input-group {
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .filter-select {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        color: #495057;
        transition: all 0.2s ease;
    }

    .filter-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
    }

    .search-icon-box {
        background: linear-gradient(135deg, #dc3545, #c82333);
        border: none;
        color: #fff;
        padding: 0.6rem 1rem;
        font-size: 1.1rem;
    }

    .search-input {
        border: 2px solid #e9ecef;
        padding: 0.7rem 1rem;
        font-size: 0.95rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .search-input:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
    }

    .search-input::placeholder {
        color: #adb5bd;
        font-style: italic;
    }

    .search-btn {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: #fff;
        border: none;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .search-btn:hover {
        background: linear-gradient(135deg, #c82333, #a71d2a);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .search-clear-btn {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        color: #6c757d;
        padding: 0.6rem 0.9rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .search-clear-btn:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .search-hint {
        font-size: 0.82rem;
        color: #6c757d;
    }

    /* ===== Search Results ===== */
    .search-result-badge {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: #fff;
        font-size: 0.8rem;
        padding: 0.4rem 0.9rem;
        border-radius: 2rem;
    }

    .search-table {
        border-collapse: separate;
        border-spacing: 0 0.35rem;
    }

    .search-table thead th {
        background: #f8f9fa;
        border: none;
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        font-weight: 700;
    }

    .search-table thead th:first-child {
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .search-table thead th:last-child {
        border-radius: 0 0.5rem 0.5rem 0;
    }

    .search-table tbody td {
        border: none;
        padding: 0.8rem 1rem;
        background: #fff;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .search-table tbody tr {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .search-table tbody tr:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .search-table tbody tr td:first-child {
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .search-table tbody tr td:last-child {
        border-radius: 0 0.5rem 0.5rem 0;
    }

    /* Row animation */
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .search-result-row {
        animation: fadeSlideIn 0.4s ease forwards;
        opacity: 0;
    }

    /* Student code badge */
    .student-code-badge {
        display: inline-block;
        background: linear-gradient(135deg, #e8f4fd, #d1ecf1);
        color: #0c5460;
        padding: 0.25rem 0.65rem;
        border-radius: 0.4rem;
        font-weight: 700;
        font-size: 0.85rem;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.05em;
    }

    /* Gender badges */
    .gender-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .gender-male {
        background: #e8f4fd;
        color: #0d6efd;
    }

    .gender-female {
        background: #fce4ec;
        color: #e91e63;
    }

    /* GPA badges */
    .gpa-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 0.4rem;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .gpa-excellent {
        background: #d4edda;
        color: #155724;
    }

    .gpa-good {
        background: #d1ecf1;
        color: #0c5460;
    }

    .gpa-average {
        background: #fff3cd;
        color: #856404;
    }

    .gpa-low {
        background: #f8d7da;
        color: #721c24;
    }

    /* No results */
    .no-results-icon {
        font-size: 3rem;
        color: #dee2e6;
        animation: pulse 2s infinite ease-in-out;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.7;
        }

        50% {
            transform: scale(1.1);
            opacity: 1;
        }
    }

    /* Scrollable search results */
    .search-results-table-container {
        max-height: 480px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #dc3545 #f8f9fa;
        padding-right: 5px;
    }

    .search-results-table-container::-webkit-scrollbar {
        width: 6px;
    }

    .search-results-table-container::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 10px;
    }

    .search-results-table-container::-webkit-scrollbar-thumb {
        background-color: #dc3545;
        border-radius: 10px;
    }

    /* Sticky header fix for separate border-collapse */
    .search-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8f9fa !important;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>