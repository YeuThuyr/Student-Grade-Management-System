SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS student_classes (
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_student_classes_class_id (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO student_classes (student_id, class_id)
SELECT id, class_id
FROM students
WHERE class_id IS NOT NULL;

