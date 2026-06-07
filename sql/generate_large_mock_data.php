<?php
/**
 * -------------------------------------------------------------
 * Large Mock Data Generator & Database Seeder (CLI / Web)
 * -------------------------------------------------------------
 * This script generates 5,000+ realistic Vietnamese student records,
 * assigns them to classes, populates random grades across subjects,
 * and creates matching student login accounts in secure batches.
 * 
 * Optimized to prevent "MySQL server has gone away" using multi-row bulk insert.
 */

header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300); // 5 minutes max execution time
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../config/database.php';

echo "=== HUST Student Grade Management System - Large Data Seeder ===\n";
echo "Starting data generation at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Clean up old tables safely
    echo "[1/6] Cleaning up old tables...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DELETE FROM grades;");
    $pdo->exec("DELETE FROM users;");
    $pdo->exec("DELETE FROM students;");
    $pdo->exec("DELETE FROM subjects;");
    $pdo->exec("DELETE FROM classes;");
    $pdo->exec("ALTER TABLE grades AUTO_INCREMENT = 1;");
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1;");
    $pdo->exec("ALTER TABLE students AUTO_INCREMENT = 1;");
    $pdo->exec("ALTER TABLE subjects AUTO_INCREMENT = 1;");
    $pdo->exec("ALTER TABLE classes AUTO_INCREMENT = 1;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "✔ Tables cleaned successfully.\n\n";

    // Define bulk insert helper
    $bulkInsert = function ($pdo, $table, $columns, $dataList, $batchSize = 500) {
        if (empty($dataList))
            return;

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

    // 2. Insert Classes
    echo "[2/6] Generating classes...\n";
    $classes = [
        ['CNTT-01', 'Công nghệ thông tin 1', 'Công nghệ thông tin', 'TS. Nguyễn Minh Quang', 'Active', 'Lớp chuyên ngành Công nghệ thông tin K68'],
        ['CNTT-02', 'Công nghệ thông tin 2', 'Công nghệ thông tin', 'ThS. Trần Thu Hà', 'Active', 'Lớp chuyên ngành Công nghệ thông tin K68'],
        ['CNTT-03', 'Công nghệ thông tin 3', 'Công nghệ thông tin', 'TS. Lê Anh Dũng', 'Active', 'Lớp chuyên ngành Công nghệ thông tin K68'],
        ['KHMT-01', 'Khoa học máy tính 1', 'Khoa học máy tính', 'PGS. Phạm Hoàng Nam', 'Active', 'Lớp chuyên ngành Khoa học máy tính K67'],
        ['KHMT-02', 'Khoa học máy tính 2', 'Khoa học máy tính', 'TS. Đặng Minh Trang', 'Active', 'Lớp chuyên ngành Khoa học máy tính K67'],
        ['KT-01', 'Kinh tế đối ngoại 1', 'Kinh tế đối ngoại', 'ThS. Vũ Thị Mai', 'Active', 'Lớp chuyên ngành Kinh tế đối ngoại'],
        ['KT-02', 'Quản trị kinh doanh 2', 'Quản trị kinh doanh', 'TS. Hoàng Anh Tuấn', 'Active', 'Lớp chuyên ngành Quản trị kinh doanh'],
        ['DTVT-01', 'Điện tử viễn thông 1', 'Điện tử viễn thông', 'PGS. Ngô Quang Huy', 'Active', 'Lớp chuyên ngành Điện tử viễn thông'],
        ['DTVT-02', 'Điện tử viễn thông 2', 'Điện tử viễn thông', 'TS. Nguyễn Thị Lan', 'Inactive', 'Lớp chuyên ngành Điện tử viễn thông'],
        ['CĐT-01', 'Cơ điện tử 1', 'Cơ điện tử', 'TS. Bùi Minh Khánh', 'Active', 'Lớp chuyên ngành Cơ điện tử K66'],
    ];

    $classStmt = $pdo->prepare("INSERT INTO classes (class_code, class_name, major, teacher, status, description) VALUES (?, ?, ?, ?, ?, ?)");
    $classIds = [];
    foreach ($classes as $c) {
        $classStmt->execute($c);
        $classIds[] = $pdo->lastInsertId();
    }
    echo "✔ Seeded " . count($classes) . " classes.\n\n";

    // 3. Insert Subjects
    echo "[3/6] Generating academic subjects...\n";
    $subjects = [
        ['MATH101', 'Giải tích I', 3, 'Đạo hàm, tích phân và ứng dụng thực tiễn'],
        ['MATH102', 'Giải tích II', 3, 'Tích phân bội, phương trình vi phân'],
        ['PHYS101', 'Vật lý đại cương I', 4, 'Cơ học cổ điển và nhiệt động học'],
        ['CS101', 'Nhập môn lập trình C/C++', 4, 'Cơ bản về cấu trúc dữ liệu tuyến tính và tư duy thuật toán'],
        ['MATH201', 'Toán rời rạc', 3, 'Logic, lý thuyết tập hợp, đồ thị và đại số Boole'],
        ['CS201', 'Cấu trúc dữ liệu và giải thuật', 4, 'Danh sách liên kết, cây, đồ thị, thuật toán sắp xếp và tìm kiếm tối ưu'],
        ['CS301', 'Hệ quản trị cơ sở dữ liệu', 3, 'Thiết kế cơ sở dữ liệu quan hệ, SQL, tối ưu hóa truy vấn'],
        ['ENG101', 'Tiếng Anh chuyên ngành', 2, 'Kỹ năng đọc và viết tài liệu kỹ thuật'],
        ['MKT101', 'Marketing căn bản', 3, 'Các chiến lược Marketing trong kỷ nguyên số'],
        ['CS402', 'Trí tuệ nhân tạo', 4, 'Học máy cơ bản, mạng nơ-ron và thuật toán tìm kiếm A*'],
    ];

    $subjectStmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, credit, description) VALUES (?, ?, ?, ?)");
    $subjectIds = [];
    foreach ($subjects as $s) {
        $subjectStmt->execute($s);
        $subjectIds[] = $pdo->lastInsertId();
    }
    echo "✔ Seeded " . count($subjects) . " subjects.\n\n";

    // 4. Generate 5,000 Students
    echo "[4/6] Generating 5,000 realistic student records...\n";

    // Arrays of Vietnamese names for random generation
    $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Phan', 'Dương', 'Lý', 'Vương', 'Trịnh'];
    $middleNames = ['Văn', 'Thị', 'Minh', 'Ngọc', 'Hữu', 'Đức', 'Khánh', 'Thu', 'Kim', 'Bảo', 'Xuân', 'Thành', 'Hoàng', 'Phương', 'Anh'];
    $firstNames = [
        'An',
        'Bình',
        'Cường',
        'Đức',
        'Hà',
        'Hùng',
        'Thảo',
        'Huấn',
        'Huy',
        'Anh',
        'Long',
        'Hải',
        'Tùng',
        'Tiên',
        'Sơn',
        'Tuấn',
        'Quân',
        'Dũng',
        'Trâm',
        'Vy',
        'Mai',
        'Nam',
        'Phong',
        'Quốc',
        'Thịnh',
        'Khoa',
        'Kiên',
        'Hòa'
    ];
    $cities = ['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Nghệ An', 'Thái Bình', 'Quảng Ninh', 'Bắc Ninh', 'Đồng Nai', 'Gia Lai', 'Lào Cai', 'Thừa Thiên Huế'];

    $pdo->beginTransaction();

    $studentCodeBase = 20200000;
    $totalStudentsToGenerate = 5000;
    $studentsToInsert = [];

    for ($i = 0; $i < $totalStudentsToGenerate; $i++) {
        $code = (string) ($studentCodeBase + $i + 1);
        $lastName = $lastNames[array_rand($lastNames)];
        $middleName = $middleNames[array_rand($middleNames)];
        $firstName = $firstNames[array_rand($firstNames)];
        $fullName = "$lastName $middleName $firstName";

        $gender = (strpos($middleName, 'Thị') !== false || $firstName === 'Tiên' || $firstName === 'Trang' || $firstName === 'Trâm' || $firstName === 'Vy') ? 'Female' : 'Male';

        $year = rand(2000, 2006);
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $dob = "$year-$month-$day";

        $email = strtolower($firstName) . "." . strtolower($lastName) . $code . "@hust.edu.vn";
        $phone = "0" . rand(3, 9) . rand(10000000, 99999999);
        $address = $cities[array_rand($cities)];
        $classId = $classIds[array_rand($classIds)];

        $studentsToInsert[] = [$code, $fullName, $dob, $gender, $email, $phone, $address, $classId, 1];
    }

    // Bulk insert students (batch of 500)
    $bulkInsert($pdo, 'students', ['student_code', 'full_name', 'date_of_birth', 'gender', 'email', 'phone', 'address', 'class_id', 'is_active'], $studentsToInsert, 500);
    echo "✔ Seeded all students into database.\n\n";

    // Fetch them back to map actual auto-incremented database IDs
    $studentsData = $pdo->query("SELECT id, student_code FROM students")->fetchAll(PDO::FETCH_ASSOC);

    // 5. Generate Grades
    echo "[5/6] Generating academic grades for students (3-5 subjects per student)...\n";

    $semesters = ['1', '2'];
    $academicYears = ['2023-2024', '2024-2025'];

    function calculateLetterGrade($avg)
    {
        if ($avg >= 9.5)
            return 'A+';
        if ($avg >= 8.5)
            return 'A';
        if ($avg >= 8.0)
            return 'B+';
        if ($avg >= 7.0)
            return 'B';
        if ($avg >= 6.5)
            return 'C+';
        if ($avg >= 5.5)
            return 'C';
        if ($avg >= 5.0)
            return 'D+';
        if ($avg >= 4.0)
            return 'D';
        return 'F';
    }

    $gradesToInsert = [];
    foreach ($studentsData as $s) {
        $chosenSubjects = array_rand($subjectIds, rand(3, 5));
        if (!is_array($chosenSubjects)) {
            $chosenSubjects = [$chosenSubjects];
        }

        foreach ($chosenSubjects as $subIdx) {
            $subjectId = $subjectIds[$subIdx];
            $mid = round(rand(30, 100) / 10, 1);
            $fin = round(rand(30, 100) / 10, 1);
            $oth = round(rand(50, 100) / 10, 1);
            $avg = round(($mid * 0.3) + ($fin * 0.5) + ($oth * 0.2), 1);
            $letter = calculateLetterGrade($avg);

            $sem = $semesters[array_rand($semesters)];
            $year = $academicYears[array_rand($academicYears)];

            $gradesToInsert[] = [$s['id'], $subjectId, $mid, $fin, $oth, $avg, $letter, $sem, $year];
        }
    }
    // Bulk insert grades (batch of 1000)
    $bulkInsert($pdo, 'grades', ['student_id', 'subject_id', 'midterm_score', 'final_score', 'other_score', 'average_score', 'letter_grade', 'semester', 'academic_year'], $gradesToInsert, 1000);
    echo "✔ Seeded all student grade records.\n\n";

    // 6. Generate User Login Accounts
    echo "[6/6] Creating user login accounts for students...\n";

    $usersToInsert = [];
    $hashedPassword = password_hash("password", PASSWORD_BCRYPT);

    // Insert admin account first
    $pdo->exec("INSERT IGNORE INTO users (username, password, role, student_id, is_active) VALUES ('admin', '$hashedPassword', 'admin', NULL, 1)");

    foreach ($studentsData as $s) {
        $usersToInsert[] = [$s['student_code'], $hashedPassword, 'student', $s['id'], 1];
    }
    // Bulk insert users (batch of 1000)
    $bulkInsert($pdo, 'users', ['username', 'password', 'role', 'student_id', 'is_active'], $usersToInsert, 1000);

    $pdo->commit();
    echo "✔ Created all login profiles successfully.\n\n";
    echo "=== SEEDING COMPLETED SUCCESSFULY ===\n";
    echo "Total Students: " . count($studentsData) . "\n";
    echo "Total Grades: " . count($gradesToInsert) . "\n";
    echo "Admin Account: admin / password\n";
    echo "Student Accounts: [Student Code] / password (e.g. 20200001 / password)\n";

} catch (Exception $e) {
    try {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Exception $rollBackEx) {
        // Ignore rollback exceptions
    }
    echo "\n❌ ERROR DURING SEEDING:\n" . $e->getMessage() . "\n";
}
?>
