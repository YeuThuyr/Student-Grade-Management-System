<?php
session_start();
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/middleware/role.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
handleAuth();
checkRole(['admin']);

// Fetch distinct academic years for the filter
$years = [];
$y_res = $conn->query("SELECT DISTINCT academic_year FROM grades WHERE academic_year IS NOT NULL ORDER BY academic_year DESC");
while ($row = $y_res->fetch_assoc())
    $years[] = $row['academic_year'];

// Fetch distinct entry years (Khóa học) dynamically from student code
$entry_years = [];
$ey_res = $conn->query("SELECT DISTINCT SUBSTRING(student_code, 1, 4) AS entry_year FROM students WHERE student_code IS NOT NULL AND is_active = 1 ORDER BY entry_year DESC");
while ($row = $ey_res->fetch_assoc()) {
    if (!empty($row['entry_year'])) {
        $entry_years[] = $row['entry_year'];
    }
}

// Fetch all classes dynamically (Field of study)
$classes_list = [];
$c_res = $conn->query("SELECT id, class_code, class_name FROM classes ORDER BY class_name ASC");
while ($row = $c_res->fetch_assoc()) {
    $classes_list[] = $row;
}

// --- Filter and Search logic ---
$search_code = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$f_year = isset($_GET['academic_year']) ? trim($_GET['academic_year']) : '';
$f_gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$f_gpa = isset($_GET['gpa_range']) ? trim($_GET['gpa_range']) : '';
$f_entry_year = isset($_GET['entry_year']) ? trim($_GET['entry_year']) : '';
$f_class_id = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';

$has_specific_filters = ($f_year !== '' || $f_entry_year !== '' || $f_class_id !== '');
$search_performed = ($search_code !== '' || $f_year !== '' || $f_gender !== '' || $f_gpa !== '' || $f_entry_year !== '' || $f_class_id !== '');

function buildDashboardFilterContext($search_code, $f_year, $f_gender, $f_gpa, $f_entry_year, $f_class_id)
{
    $where_clauses = ["1=1"];
    $params = [];
    $types = "";
    $join_clause = "LEFT JOIN grades g ON s.id = g.student_id";

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
    if ($f_entry_year !== '') {
        $where_clauses[] = "SUBSTRING(s.student_code, 1, 4) = ?";
        $params[] = $f_entry_year;
        $types .= "s";
    }
    if ($f_class_id !== '') {
        $where_clauses[] = "EXISTS (SELECT 1 FROM student_classes scf WHERE scf.student_id = s.id AND scf.class_id = ?)";
        $params[] = (int) $f_class_id;
        $types .= "i";
    }
    if ($f_year !== '') {
        $where_clauses[] = "g.academic_year = ?";
        $params[] = $f_year;
        $types .= "s";
        $join_clause = "JOIN grades g ON s.id = g.student_id";
    }

    $having_clause = "";
    if ($f_gpa === 'excellent')
        $having_clause = " HAVING gpa >= 3.6";
    elseif ($f_gpa === 'good')
        $having_clause = " HAVING gpa >= 3.0 AND gpa < 3.6";
    elseif ($f_gpa === 'average')
        $having_clause = " HAVING gpa >= 2.0 AND gpa < 3.0";
    elseif ($f_gpa === 'weak')
        $having_clause = " HAVING gpa < 2.0";

    return [
        'where_sql' => implode(" AND ", $where_clauses),
        'params' => $params,
        'types' => $types,
        'join_clause' => $join_clause,
        'having_clause' => $having_clause
    ];
}

$filter_context = buildDashboardFilterContext($search_code, $f_year, $f_gender, $f_gpa, $f_entry_year, $f_class_id);
$where_sql = $filter_context['where_sql'];
$params = $filter_context['params'];
$types = $filter_context['types'];
$join_clause = $filter_context['join_clause'];
$having_clause = $filter_context['having_clause'];
$gradePointSql = "
    CASE g.letter_grade
        WHEN 'A+' THEN 4.0
        WHEN 'A' THEN 3.7
        WHEN 'B+' THEN 3.5
        WHEN 'B' THEN 3.0
        WHEN 'C+' THEN 2.5
        WHEN 'C' THEN 2.0
        WHEN 'D' THEN 1.0
        ELSE 0.0
    END
";

if ($has_specific_filters) {
    require_once __DIR__ . '/stats/summary.php'; // This provides $summary array

    $filter_context = buildDashboardFilterContext($search_code, $f_year, $f_gender, $f_gpa, $f_entry_year, $f_class_id);
    $where_sql = $filter_context['where_sql'];
    $params = $filter_context['params'];
    $types = $filter_context['types'];
    $join_clause = $filter_context['join_clause'];
    $having_clause = $filter_context['having_clause'];
    
    // Fetch Top 5 Students
    $top_students = [];
    $ts_where = $where_sql;
    $ts_query = "
        SELECT s.student_code, s.full_name, MAX(class_map.class_names) AS class_names, ROUND(SUM(($gradePointSql) * sub.credit) / SUM(sub.credit), 2) as gpa
        FROM students s
        LEFT JOIN (
            SELECT sc.student_id, GROUP_CONCAT(c.class_name ORDER BY c.class_name SEPARATOR ', ') AS class_names
            FROM student_classes sc
            JOIN classes c ON c.id = sc.class_id
            GROUP BY sc.student_id
        ) class_map ON class_map.student_id = s.id
        JOIN grades g ON s.id = g.student_id
        JOIN subjects sub ON g.subject_id = sub.id
        WHERE $ts_where
        GROUP BY s.id
        $having_clause
        ORDER BY gpa DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($ts_query);
    $stmt->execute($params);
    $top_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Top 5 Subjects
    $top_subjects = [];
    $sub_query = "
        SELECT sub.subject_code, sub.subject_name, ROUND(AVG(g.average_score), 2) as avg_score, COUNT(g.id) as enrollments
        FROM grades g
        JOIN subjects sub ON g.subject_id = sub.id
        JOIN students s ON g.student_id = s.id
        WHERE $where_sql
        GROUP BY sub.id
        ORDER BY avg_score DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sub_query);
    $stmt->execute($params);
    $top_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Gender Breakdown
    $gender_breakdown = [];
    $gender_query = "
        SELECT s.gender, COUNT(DISTINCT s.id) as student_count, ROUND(AVG($gradePointSql), 2) as avg_gpa
        FROM students s
        JOIN grades g ON s.id = g.student_id
        WHERE $where_sql
        GROUP BY s.gender
    ";
    $stmt = $pdo->prepare($gender_query);
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $gender_breakdown[$row['gender']] = $row;
    }
} else {
    $summary = [
        'total_students' => 0,
        'average_gpa' => 0,
        'pass_rate' => 0,
        'pass_count' => 0,
        'fail_count' => 0
    ];
}

$search_results = [];

if ($search_performed) {
    $stmt = $pdo->prepare("
        SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone,
               ROUND(SUM(($gradePointSql) * sub.credit) / SUM(sub.credit), 2) as gpa
        FROM students s
        $join_clause
        LEFT JOIN subjects sub ON g.subject_id = sub.id
        WHERE $where_sql
        GROUP BY s.id
        $having_clause
        ORDER BY s.student_code ASC
    ");

    $stmt->execute($params);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- Summary Cards and Analytics State -->
    <?php if ($has_specific_filters): ?>
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
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center mb-5 bg-white position-relative overflow-hidden" style="border: 1px dashed rgba(220, 53, 69, 0.2) !important;">
            <div class="py-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
                     style="width: 90px; height: 90px; background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));">
                    <i class="fas fa-filter fa-3x text-danger animate-pulse"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2" data-i18n="dash_select_filter_title">Chưa chọn bộ lọc thống kê</h4>
                <p class="text-muted mx-auto" style="max-width: 500px;" data-i18n="dash_select_filter_desc">
                    Vui lòng chọn <strong>Khóa học</strong>, <strong>Chuyên ngành</strong>, hoặc <strong>Năm học</strong> để xem thống kê số liệu sinh viên, tỷ lệ đạt/trượt và các biểu đồ phân tích.
                </p>
            </div>
        </div>
    <?php endif; ?>

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
                <div class="col-12 col-sm-6 col-lg">
                    <label class="form-label text-muted small fw-bold mb-1" data-i18n="dash_filter_entry_year">Khóa học</label>
                    <select name="entry_year" class="form-select filter-select"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" data-i18n="dash_all_entry_years">Tất cả các khóa</option>
                        <?php foreach ($entry_years as $ey): ?>
                            <option value="<?php echo htmlspecialchars($ey); ?>" <?php if ($f_entry_year === $ey)
                                   echo 'selected'; ?>>Khóa <?php echo htmlspecialchars($ey); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg">
                    <label class="form-label text-muted small fw-bold mb-1" data-i18n="dash_filter_class">Chuyên ngành</label>
                    <select name="class_id" class="form-select filter-select"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" data-i18n="dash_all_classes">Tất cả các ngành</option>
                        <?php foreach ($classes_list as $cls): ?>
                            <option value="<?php echo htmlspecialchars($cls['id']); ?>" <?php if ($f_class_id == $cls['id'])
                                   echo 'selected'; ?>><?php echo htmlspecialchars($cls['class_name']); ?> (<?php echo htmlspecialchars($cls['class_code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg">
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
                <div class="col-12 col-sm-6 col-lg">
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
                <div class="col-12 col-sm-6 col-lg">
                    <label class="form-label text-muted small fw-bold mb-1" data-i18n="dash_filter_gpa">Xếp loại GPA</label>
                    <select name="gpa_range" class="form-select filter-select"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" data-i18n="dash_all_gpa">Tất cả mức điểm</option>
                        <option value="excellent" <?php if ($f_gpa === 'excellent')
                            echo 'selected'; ?>>Xuất sắc (>= 3.6)
                        </option>
                        <option value="good" <?php if ($f_gpa === 'good')
                            echo 'selected'; ?>>Khá (3.0 - 3.59)</option>
                        <option value="average" <?php if ($f_gpa === 'average')
                            echo 'selected'; ?>>Trung bình (2.0 - 2.99)
                        </option>
                        <option value="weak" <?php if ($f_gpa === 'weak')
                            echo 'selected'; ?>>Yếu (< 2.0)</option>
                    </select>
                </div>
            </div>

            <div class="search-hint mt-3">
                <i class="fas fa-info-circle me-1"></i>
                Có thể kết hợp tìm kiếm mã số và các bộ lọc. Biểu đồ và thẻ tóm tắt cũng sẽ tự động đồng bộ theo bộ lọc
                này.
            </div>
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
                                                if ($gpa >= 3.6)
                                                    $gpa_class = 'gpa-excellent';
                                                elseif ($gpa >= 3.0)
                                                    $gpa_class = 'gpa-good';
                                                elseif ($gpa >= 2.0)
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

    <!-- Dynamic Analytics Charts & Rich Widgets -->
    <?php if ($has_specific_filters): ?>
        <!-- Rich Analytical Widgets -->
        <div class="row g-4 mb-5">
            <!-- Top 5 Students -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-trophy text-warning me-2"></i>Top Sinh Viên Xuất Sắc</h5>
                    <?php if (!empty($top_students)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($top_students as $idx => $student): ?>
                                <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between border-0 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-weight: 700;"><?php echo $idx + 1; ?></span>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($student['student_code']); ?> | <?php echo htmlspecialchars($student['class_names'] ?? 'Chưa rõ'); ?></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold">GPA: <?php echo $student['gpa']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-friends fa-2x mb-2 text-black-50"></i>
                            <div>Không có dữ liệu sinh viên phù hợp</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top 5 Subjects -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-book-open text-primary me-2"></i>Môn Học Điểm Cao Nhất</h5>
                    <?php if (!empty($top_subjects)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($top_subjects as $idx => $subj): ?>
                                <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between border-0 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-weight: 700;"><?php echo $idx + 1; ?></span>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($subj['subject_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($subj['subject_code']); ?> (<?php echo htmlspecialchars($subj['enrollments']); ?> lượt học)</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 fw-bold"><?php echo $subj['avg_score']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-book fa-2x mb-2 text-black-50"></i>
                            <div>Không có dữ liệu môn học phù hợp</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Gender & GPA Analytics -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-chart-pie text-danger me-2"></i>Thống Kê Theo Giới Tính</h5>
                    <div class="d-flex flex-column justify-content-center h-100 py-3">
                        <?php 
                        $male_stats = $gender_breakdown['Male'] ?? ['student_count' => 0, 'avg_gpa' => '—'];
                        $female_stats = $gender_breakdown['Female'] ?? ['student_count' => 0, 'avg_gpa' => '—'];
                        ?>
                        <div class="p-3 mb-3 rounded-3 bg-light border-start border-4 border-primary d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-dark"><i class="fas fa-mars text-primary me-2"></i>Nam giới</div>
                                <small class="text-muted"><?php echo $male_stats['student_count']; ?> sinh viên</small>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted mb-1">GPA TB</div>
                                <span class="badge bg-primary text-white fs-6 px-3 py-2 fw-bold"><?php echo $male_stats['avg_gpa']; ?></span>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 bg-light border-start border-4 border-pink d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-dark"><i class="fas fa-venus text-pink me-2"></i>Nữ giới</div>
                                <small class="text-muted"><?php echo $female_stats['student_count']; ?> sinh viên</small>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted mb-1">GPA TB</div>
                                <span class="badge bg-pink text-white fs-6 px-3 py-2 fw-bold"><?php echo $female_stats['avg_gpa']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-5">
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
    <?php endif; ?>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passFailCanvas = document.getElementById('passFailChart');
        if (!passFailCanvas) return; // Skip chart building if filters are not applied

        // Fetch data for charts with current filters
        const queryParams = window.location.search;
        fetch('stats/chart_data.php' + queryParams)
            .then(response => response.json())
            .then(data => {
                // Pass/Fail Chart
                const ctxPF = passFailCanvas.getContext('2d');
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
        border-radius: 0.5rem !important;
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

    .border-pink {
        border-color: #e91e63 !important;
    }

    .text-pink {
        color: #e91e63 !important;
    }

    .bg-pink {
        background-color: #e91e63 !important;
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
