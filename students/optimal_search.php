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

$message = "";
$messageType = "";

// Handle Seeder triggering via Web UI
if (isset($_POST['run_seeder'])) {
    try {
        // Define bulk insert helper
        $bulkInsertHelper = function ($pdo, $table, $columns, $dataList, $batchSize = 500) {
            if (empty($dataList)) return;
            
            $colString = implode(', ', $columns);
            $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
            
            $chunks = array_chunk($dataList, $batchSize);
            foreach ($chunks as $chunk) {
                $rowPlaceholders = implode(', ', array_fill(0, count($chunk), $placeholders));
                $sql = "INSERT INTO $table ($colString) VALUES $rowPlaceholders";
                
                $params = [];
                foreach ($chunk as $row) {
                    foreach ($row as $val) {
                        $params[] = $val;
                    }
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        };

        // Run the seeder directly in this request
        // Wiping tables
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("TRUNCATE TABLE grades;");
        $pdo->exec("TRUNCATE TABLE feedback_messages;");
        $pdo->exec("TRUNCATE TABLE users;");
        $pdo->exec("TRUNCATE TABLE students;");
        $pdo->exec("TRUNCATE TABLE subjects;");
        $pdo->exec("TRUNCATE TABLE classes;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Seed classes
        $classes = [
            ['CNTT-01', 'Công nghệ thông tin 1', 'Công nghệ thông tin', 'TS. Nguyễn Minh Quang', 'Active', 'Lớp chuyên ngành Công nghệ thông tin K68'],
            ['CNTT-02', 'Công nghệ thông tin 2', 'Công nghệ thông tin', 'ThS. Trần Thu Hà', 'Active', 'Lớp chuyên ngành Công nghệ thông tin K68'],
            ['CNTT-03', 'Công nghệ thông tin 3', 'Công nghệ thông tin', 'TS. Lê Anh Dũng', 'Active', 'Lớp chuyên ngành Công nghệ thông tin K68'],
            ['KHMT-01', 'Khoa học máy tính 1', 'Khoa học máy tính', 'PGS. Phạm Hoàng Nam', 'Active', 'Lớp chuyên ngành Khoa học máy tính K67'],
            ['KHMT-02', 'Khoa học máy tính 2', 'Khoa học máy tính', 'TS. Đặng Minh Trang', 'Active', 'Lớp chuyên ngành Khoa học máy tính K67'],
            ['KT-01', 'Kinh tế đối ngoại 1', 'Kinh tế đối ngoại', 'ThS. Vũ Thị Mai', 'Active', 'Lớp chuyên ngành Kinh tế đối ngoại'],
            ['KT-02', 'Quản trị kinh doanh 2', 'Quản trị kinh doanh', 'TS. Hoàng Anh Tuấn', 'Active', 'Lớp chuyên ngành Quản trị kinh doanh'],
            ['DTVT-01', 'Điện tử viễn thông 1', 'Điện tử viễn thông', 'PGS. Ngô Quang Huy', 'Active', 'Lớp chuyên ngành Điện tử viễn thông'],
            ['CĐT-01', 'Cơ điện tử 1', 'Cơ điện tử', 'TS. Bùi Minh Khánh', 'Active', 'Lớp chuyên ngành Cơ điện tử K66'],
        ];
        $classStmt = $pdo->prepare("INSERT INTO classes (class_code, class_name, major, teacher, status, description) VALUES (?, ?, ?, ?, ?, ?)");
        $classIds = [];
        foreach ($classes as $c) {
            $classStmt->execute($c);
            $classIds[] = $pdo->lastInsertId();
        }

        // Seed subjects
        $subjects = [
            ['MATH101', 'Giải tích I', 3, 'Đạo hàm, tích phân và ứng dụng thực tiễn'],
            ['PHYS101', 'Vật lý đại cương I', 4, 'Cơ học cổ điển và nhiệt động học'],
            ['CS101', 'Nhập môn lập trình C/C++', 4, 'Cơ bản về cấu trúc dữ liệu và tư duy thuật toán'],
            ['MATH201', 'Toán rời rạc', 3, 'Logic, lý thuyết tập hợp, đồ thị'],
            ['CS201', 'Cấu trúc dữ liệu và giải thuật', 4, 'Danh sách liên kết, cây, tìm kiếm tối ưu'],
            ['CS301', 'Hệ quản trị cơ sở dữ liệu', 3, 'Thiết kế cơ sở dữ liệu quan hệ, SQL'],
            ['ENG101', 'Tiếng Anh chuyên ngành', 2, 'Kỹ năng đọc viết tài liệu kỹ thuật'],
        ];
        $subjectStmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, credit, description) VALUES (?, ?, ?, ?)");
        $subjectIds = [];
        foreach ($subjects as $s) {
            $subjectStmt->execute($s);
            $subjectIds[] = $pdo->lastInsertId();
        }

        $pdo->beginTransaction();

        // Generate 5,000 Students
        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Phan', 'Dương', 'Lý', 'Vương', 'Trịnh'];
        $middleNames = ['Văn', 'Thị', 'Minh', 'Ngọc', 'Hữu', 'Đức', 'Khánh', 'Thu', 'Kim', 'Bảo', 'Xuân', 'Thành', 'Hoàng', 'Phương', 'Anh'];
        $firstNames = ['An', 'Bình', 'Cường', 'Đức', 'Hà', 'Hùng', 'Thảo', 'Huấn', 'Huy', 'Anh', 'Long', 'Hải', 'Tùng', 'Tiên', 'Sơn', 'Tuấn', 'Quân', 'Dũng', 'Trâm', 'Vy', 'Mai', 'Nam'];
        $cities = ['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Nghệ An', 'Thái Bình', 'Quảng Ninh', 'Bắc Ninh'];

        $studentCodeBase = 20200000;
        $totalToGen = 5000;
        $studentsToInsert = [];

        for ($i = 0; $i < $totalToGen; $i++) {
            $code = (string)($studentCodeBase + $i + 1);
            $lastName = $lastNames[array_rand($lastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $firstName = $firstNames[array_rand($firstNames)];
            $fullName = "$lastName $middleName $firstName";
            $gender = (strpos($middleName, 'Thị') !== false || $firstName === 'Tiên' || $firstName === 'Trang') ? 'Female' : 'Male';
            $dob = rand(2000, 2006) . "-" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
            $email = strtolower($firstName) . "." . strtolower($lastName) . $code . "@hust.edu.vn";
            $phone = "0" . rand(3, 9) . rand(10000000, 99999999);
            $address = $cities[array_rand($cities)];
            $classId = $classIds[array_rand($classIds)];

            $studentsToInsert[] = [$code, $fullName, $dob, $gender, $email, $phone, $address, $classId, 1];
        }

        // Bulk insert students (batch of 500)
        $bulkInsertHelper($pdo, 'students', ['student_code', 'full_name', 'date_of_birth', 'gender', 'email', 'phone', 'address', 'class_id', 'is_active'], $studentsToInsert, 500);

        // Fetch inserted students back to get their auto-incremented database IDs
        $studentsData = $pdo->query("SELECT id, student_code FROM students")->fetchAll(PDO::FETCH_ASSOC);

        // Grades
        $gradesToInsert = [];
        foreach ($studentsData as $s) {
            $chosen = array_rand($subjectIds, 3);
            foreach ($chosen as $subIdx) {
                $subId = $subjectIds[$subIdx];
                $mid = round(rand(40, 100) / 10, 1);
                $fin = round(rand(40, 100) / 10, 1);
                $oth = round(rand(60, 100) / 10, 1);
                $avg = round(($mid * 0.3) + ($fin * 0.5) + ($oth * 0.2), 1);
                
                $letter = determineLetterGrade($avg);

                $gradesToInsert[] = [$s['id'], $subId, $mid, $fin, $oth, $avg, $letter, '1', '2023-2024'];
            }
        }
        // Bulk insert grades (batch of 1000)
        $bulkInsertHelper($pdo, 'grades', ['student_id', 'subject_id', 'midterm_score', 'final_score', 'other_score', 'average_score', 'letter_grade', 'semester', 'academic_year'], $gradesToInsert, 1000);

        // Users
        $usersToInsert = [];
        $hashed = password_hash("password", PASSWORD_BCRYPT);
        
        // Insert admin account first
        $pdo->exec("INSERT IGNORE INTO users (username, password, role, student_id, is_active) VALUES ('admin', '$hashed', 'admin', NULL, 1)");

        foreach ($studentsData as $s) {
            $usersToInsert[] = [$s['student_code'], $hashed, 'student', $s['id'], 1];
        }
        // Bulk insert users (batch of 1000)
        $bulkInsertHelper($pdo, 'users', ['username', 'password', 'role', 'student_id', 'is_active'], $usersToInsert, 1000);

        $pdo->commit();
        $message = "Thành công! Đã tạo 5,000+ sinh viên mẫu và bảng điểm tối ưu hóa.";
        $messageType = "success";
    } catch (Exception $e) {
        try {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Exception $rollbackEx) {
            // Ignore secondary exception during rollback
        }
        $message = "Lỗi khi sinh dữ liệu mẫu: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get total count of active students
$totalStudents = 0;
try {
    $countQuery = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
    $totalStudents = (int)$countQuery->fetchColumn();
} catch (Exception $e) {
    // Database connection or table missing
}

// Implement Search Comparison Algorithms
$searchCode = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$benchmark = [];
$foundStudent = null;

if ($searchCode !== '' && $totalStudents > 0) {
    
    // --- ALGORITHM 1: SQL INDEXED LOOKUP (B-TREE INDEX) ---
    // MySQL has a unique index on 'student_code'. This is O(log N) depth point lookup.
    $startSql = microtime(true);
    $stmt = $pdo->prepare("
        SELECT s.*, c.class_name, ROUND(SUM(g.average_score * subj.credit) / SUM(subj.credit), 2) as gpa
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN grades g ON s.id = g.student_id
        LEFT JOIN subjects subj ON g.subject_id = subj.id
        WHERE s.student_code = ? AND s.is_active = 1
        GROUP BY s.id
        LIMIT 1
    ");
    $stmt->execute([$searchCode]);
    $sqlResult = $stmt->fetch();
    $endSql = microtime(true);
    
    $sqlTime = ($endSql - $startSql) * 1000; // milliseconds
    $benchmark['sql'] = [
        'name' => 'Database SQL Indexed Search',
        'complexity' => 'O(log N)',
        'time_ms' => $sqlTime,
        'steps' => '~1-3 B-Tree page reads',
        'desc' => 'Leverages native MySQL B-Tree indexing on the UNIQUE `student_code` column. Extremely fast point-query.',
        'icon' => 'fa-database text-success'
    ];
    
    if ($sqlResult) {
        $foundStudent = $sqlResult;
    }

    // Load full sorted dataset into memory for accurate in-memory benchmarks
    // PHP memory can easily hold 5,000 minimal items for simulation
    $loadStart = microtime(true);
    $dataStmt = $pdo->query("SELECT id, student_code, full_name, email, phone FROM students WHERE is_active = 1 ORDER BY student_code ASC");
    $allStudents = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    $loadEnd = microtime(true);
    
    $count = count($allStudents);

    // --- ALGORITHM 2: IN-MEMORY LINEAR SEARCH ---
    $startLinear = microtime(true);
    $linearSteps = 0;
    $linearFoundIdx = -1;
    for ($i = 0; $i < $count; $i++) {
        $linearSteps++;
        if ($allStudents[$i]['student_code'] === $searchCode) {
            $linearFoundIdx = $i;
            break;
        }
    }
    $endLinear = microtime(true);
    $linearTime = ($endLinear - $startLinear) * 1000;
    
    $benchmark['linear'] = [
        'name' => 'PHP In-Memory Linear Search',
        'complexity' => 'O(N)',
        'time_ms' => $linearTime,
        'steps' => $linearSteps . ' comparisons',
        'desc' => 'Scans each element sequentially from start to end. Performance degrades linearly as database size increases.',
        'icon' => 'fa-arrow-right-long text-danger'
    ];

    // --- ALGORITHM 3: IN-MEMORY BINARY SEARCH ---
    $startBinary = microtime(true);
    $binarySteps = 0;
    $binaryFoundIdx = -1;
    $low = 0;
    $high = $count - 1;
    
    while ($low <= $high) {
        $binarySteps++;
        $mid = floor(($low + $high) / 2);
        $midVal = $allStudents[$mid]['student_code'];
        
        if ($midVal === $searchCode) {
            $binaryFoundIdx = $mid;
            break;
        } elseif ($midVal < $searchCode) {
            $low = $mid + 1;
        } else {
            $high = $mid - 1;
        }
    }
    $endBinary = microtime(true);
    $binaryTime = ($endBinary - $startBinary) * 1000;
    
    $benchmark['binary'] = [
        'name' => 'PHP In-Memory Binary Search',
        'complexity' => 'O(log N)',
        'time_ms' => $binaryTime,
        'steps' => $binarySteps . ' comparisons',
        'desc' => 'Divide-and-conquer strategy on pre-sorted array. Halves the search space at each step. Excellent for large in-memory collections.',
        'icon' => 'fa-network-wired text-info'
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold hust-text-gradient">Giải thuật Tìm kiếm Tối ưu & Dữ liệu Lớn</h2>
            <p class="text-muted mb-0">Thử nghiệm so sánh hiệu năng các thuật toán tìm kiếm trên tập dữ liệu mô phỏng.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="list.php" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
            <form method="POST" onsubmit="return confirm('Hành động này sẽ xóa dữ liệu cũ và nạp 5,000+ sinh viên mẫu. Bạn có chắc chắn?');">
                <button type="submit" name="run_seeder" class="btn btn-hust rounded-pill px-4">
                    <i class="fa fa-database me-1"></i> Sinh 5,000+ dữ liệu mẫu
                </button>
            </form>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fa <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Status Indicators & Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                <div class="display-5 fw-bold text-primary mb-1"><?php echo number_format($totalStudents); ?></div>
                <div class="text-muted text-uppercase tracking-wider small fw-semibold">Tổng số sinh viên</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                <div class="display-5 fw-bold text-success mb-1">O(log N)</div>
                <div class="text-muted text-uppercase tracking-wider small fw-semibold">Độ phức tạp tối ưu</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center text-md-start d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light-success text-success p-3 rounded-circle" style="font-size: 1.5rem;">
                        <i class="fa fa-check-double"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">B-Tree Indexing</h6>
                        <p class="text-muted mb-0 small">Bảng sinh viên đã được tối ưu hóa chỉ mục UNIQUE trên mã số SV.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h4 class="fw-bold mb-3"><i class="fa fa-search text-hust me-2"></i>Tìm kiếm sinh viên</h4>
        <form method="GET" class="row g-3">
            <div class="col-lg-9 col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill px-3">
                        <i class="fa fa-user-graduate text-muted"></i>
                    </span>
                    <input type="text" name="student_code" class="form-control bg-light border-start-0 rounded-end-pill py-3 px-2" 
                           placeholder="Nhập mã sinh viên 8 chữ số (Ví dụ: 20200025, 20201456...)" 
                           value="<?php echo e($searchCode); ?>" required>
                </div>
                <div class="form-text ms-3 text-muted">
                    Mẹo: Nhập ngẫu nhiên mã số từ <strong>20200001</strong> đến <strong>20205000</strong> nếu đã sinh dữ liệu mẫu.
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <button type="submit" class="btn btn-hust w-100 rounded-pill py-3 fw-bold">
                    <i class="fa fa-bolt me-1"></i> Tìm & Phân Tích
                </button>
            </div>
        </form>
    </div>

    <?php if ($searchCode !== ''): ?>
        <?php if ($foundStudent): ?>
            <!-- Results Section -->
            <div class="row g-4 mb-4">
                <!-- Left: Student Info Card -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                        <div class="bg-hust p-4 text-white text-center position-relative">
                            <div class="position-absolute top-50 start-50 translate-middle opacity-10 fs-1" style="font-size: 8rem !important;">
                                <i class="fa fa-graduation-cap"></i>
                            </div>
                            <div class="avatar bg-white text-hust rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 80px; height: 80px; font-size: 2.2rem; font-weight: bold;">
                                <?php 
                                    $parts = explode(' ', $foundStudent['full_name']);
                                    echo mb_substr(end($parts), 0, 1, 'utf-8');
                                ?>
                            </div>
                            <h4 class="fw-bold mb-1"><?php echo e($foundStudent['full_name']); ?></h4>
                            <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold"><?php echo e($foundStudent['student_code']); ?></span>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between py-3 px-0 border-0 border-bottom">
                                    <span class="text-muted"><i class="fa fa-school me-2 text-muted"></i>Lớp:</span>
                                    <span class="fw-semibold text-dark"><?php echo e($foundStudent['class_name'] ?? 'Chưa chỉ định'); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between py-3 px-0 border-0 border-bottom">
                                    <span class="text-muted"><i class="fa fa-envelope me-2 text-muted"></i>Email:</span>
                                    <span class="fw-semibold text-dark text-break"><?php echo e($foundStudent['email']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between py-3 px-0 border-0 border-bottom">
                                    <span class="text-muted"><i class="fa fa-phone me-2 text-muted"></i>Điện thoại:</span>
                                    <span class="fw-semibold text-dark"><?php echo e($foundStudent['phone']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between py-3 px-0 border-0 border-bottom">
                                    <span class="text-muted"><i class="fa fa-star me-2 text-warning"></i>GPA tích lũy:</span>
                                    <span class="fw-bold text-success fs-5"><?php echo e($foundStudent['gpa'] !== null ? number_format($foundStudent['gpa'], 2) : '—'); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between py-3 px-0 border-0">
                                    <span class="text-muted"><i class="fa fa-map-marker-alt me-2 text-muted"></i>Quê quán:</span>
                                    <span class="fw-semibold text-dark"><?php echo e($foundStudent['address']); ?></span>
                                </li>
                            </ul>
                            <div class="mt-4">
                                <a href="edit.php?id=<?php echo $foundStudent['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100 py-2">
                                    <i class="fa fa-edit me-1"></i> Chỉnh sửa thông tin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Performance Benchmarks -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                        <h4 class="fw-bold mb-3"><i class="fa fa-chart-line text-success me-2"></i>Bảng so sánh hiệu năng</h4>
                        <p class="text-muted mb-4 small">Thời gian được đo trực tiếp khi thực hiện tìm kiếm trên cùng một điều kiện.</p>
                        
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($benchmark as $key => $bench): ?>
                                <div class="benchmark-item p-3 rounded-4 border bg-light position-relative overflow-hidden <?php echo $key === 'sql' ? 'border-success-subtle bg-success-light' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa <?php echo $bench['icon']; ?> fs-4"></i>
                                            <h6 class="fw-bold mb-0 text-dark"><?php echo $bench['name']; ?></h6>
                                        </div>
                                        <span class="badge rounded-pill bg-dark text-white fw-bold px-3 py-1"><?php echo $bench['complexity']; ?></span>
                                    </div>
                                    <div class="row g-2 align-items-center">
                                        <div class="col-sm-4">
                                            <div class="text-muted small">Thời gian chạy:</div>
                                            <div class="fs-4 fw-extrabold <?php echo $key === 'sql' ? 'text-success' : ($key === 'linear' ? 'text-danger' : 'text-info'); ?>">
                                                <?php echo number_format($bench['time_ms'], 5); ?> ms
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="text-muted small">Số bước thực hiện:</div>
                                            <div class="fw-bold text-dark"><?php echo $bench['steps']; ?></div>
                                        </div>
                                        <div class="col-sm-4 text-sm-end">
                                            <span class="badge <?php echo $key === 'sql' ? 'bg-success' : ($key === 'linear' ? 'bg-danger' : 'bg-info'); ?> text-white rounded-pill px-3">
                                                <?php echo $key === 'sql' ? 'Nhanh nhất (Gợi ý)' : ($key === 'binary' ? 'Khuyên dùng cho Memory' : 'Không khuyến khích'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-muted mt-2 mb-0 small lh-sm"><?php echo $bench['desc']; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
                <div class="text-danger mb-3" style="font-size: 3rem;">
                    <i class="fa fa-user-slash"></i>
                </div>
                <h4 class="fw-bold">Không tìm thấy sinh viên</h4>
                <p class="text-muted max-w-lg mx-auto">Mã sinh viên "<strong><?php echo e($searchCode); ?></strong>" không tồn tại trong hệ thống. Hãy kiểm tra lại hoặc chạy trình sinh dữ liệu mẫu ở trên.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Deep Dive into Search Algorithms -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
        <h4 class="fw-bold mb-4"><i class="fa fa-brain text-primary me-2"></i>Chi tiết Kỹ thuật về các Giải thuật tìm kiếm</h4>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-4 h-100 border">
                    <h5 class="fw-bold text-danger mb-2"><i class="fa fa-slash me-2"></i>1. Tuyến tính (Linear Search)</h5>
                    <p class="small text-muted">
                        Thuật toán đơn giản nhất. Duyệt qua từng bản ghi từ đầu đến cuối danh sách. 
                        Ở trường hợp tệ nhất, nó phải thực hiện <strong>N</strong> phép so sánh.
                    </p>
                    <div class="alert alert-danger py-2 px-3 small border-0 mb-0">
                        <strong>Tệ nhất:</strong> O(N) <br>
                        <strong>Tốt nhất:</strong> O(1)
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-4 h-100 border">
                    <h5 class="fw-bold text-info mb-2"><i class="fa fa-network-wired me-2"></i>2. Nhị phân (Binary Search)</h5>
                    <p class="small text-muted">
                        Yêu cầu dữ liệu phải <strong>được sắp xếp trước</strong> theo khóa tìm kiếm. Thuật toán liên tục chia đôi khoảng tìm kiếm. 
                        Với 5,000 bản ghi, Binary Search chỉ cần tối đa <strong>13 bước</strong> so sánh.
                    </p>
                    <div class="alert alert-info py-2 px-3 small border-0 mb-0">
                        <strong>Tệ nhất:</strong> O(log N) <br>
                        <strong>Tốt nhất:</strong> O(1)
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-4 h-100 border border-success-subtle bg-success-light">
                    <h5 class="fw-bold text-success mb-2"><i class="fa fa-database me-2"></i>3. Chỉ mục B-Tree (SQL Index)</h5>
                    <p class="small text-muted">
                        Cơ sở dữ liệu lưu trữ các cột được đánh chỉ mục dưới dạng cây cân bằng <strong>B-Tree / B+Tree</strong>. 
                        Giúp tìm kiếm cực kỳ nhanh chóng mà không cần duyệt dữ liệu thô vào bộ nhớ PHP.
                    </p>
                    <div class="alert alert-success py-2 px-3 small border-0 mb-0">
                        <strong>Độ phức tạp:</strong> O(log N) trên ổ đĩa<br>
                        <strong>Khuyên dùng:</strong> Luôn dùng ở DB layer.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-success-light {
    background-color: rgba(25, 135, 84, 0.05) !important;
}
.bg-light-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
}
.fw-extrabold {
    font-weight: 800;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
