SET NAMES utf8mb4;

-- 1. Xóa dữ liệu cũ (để tránh lỗi trùng khóa khi chạy lại)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM grades;
DELETE FROM subjects;
DELETE FROM users;
DELETE FROM students;
DELETE FROM classes;

ALTER TABLE grades AUTO_INCREMENT = 1;
ALTER TABLE subjects AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE classes AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Dữ liệu lớp học
INSERT INTO classes (class_code, class_name, major, teacher, status, description) VALUES
('CNTT-01', 'Công nghệ thông tin 1', 'Công nghệ thông tin', 'TS. Nguyễn Minh Quang', 'Active', 'Lớp chuyên ngành Công nghệ thông tin'),
('CNTT-02', 'Công nghệ thông tin 2', 'Công nghệ thông tin', 'ThS. Trần Thu Hà', 'Active', 'Lớp chuyên ngành Công nghệ thông tin'),
('KT-01', 'Kinh tế 1', 'Kinh tế', 'PGS. Phạm Hoàng Nam', 'Active', 'Lớp chuyên ngành Kinh tế');

-- 3. Dữ liệu sinh viên (Đa dạng các năm: 2021, 2022, 2023, 2024)
INSERT INTO students (student_code, full_name, date_of_birth, gender, email, phone, address, class_id, is_active) VALUES
('20230001', 'Nguyễn Văn An', '2005-05-15', 'Male', 'an.nv@gmail.com', '0123456789', 'Hà Nội', 1, 1),
('20230002', 'Trần Thị Bình', '2005-08-20', 'Female', 'binh.tt@gmail.com', '0987654321', 'TP. Hồ Chí Minh', 1, 1),
('20239614', 'Lê Văn Cường', '2004-12-10', 'Male', 'cuong.lv@gmail.com', '0345678901', 'Đà Nẵng', 2, 1),
('20230010', 'Phạm Minh Đức', '2005-01-05', 'Male', 'duc.pm@gmail.com', '0912123456', 'Hải Phòng', 2, 1),
('20230025', 'Hoàng Ngọc Hà', '2005-11-22', 'Female', 'ha.hn@gmail.com', '0923234567', 'Cần Thơ', 1, 1),
('20220001', 'Vũ Việt Hùng', '2004-03-12', 'Male', 'hung.vv@gmail.com', '0934345678', 'Nghệ An', 3, 1),
('20220015', 'Đặng Thu Thảo', '2004-09-30', 'Female', 'thao.dt@gmail.com', '0945456789', 'Thái Bình', 3, 1),
('20240001', 'Bùi Xuân Huấn', '2006-02-14', 'Male', 'huan.bx@gmail.com', '0956567890', 'Lào Cai', 2, 1),
('20240456', 'Ngô Kiến Huy', '2006-07-08', 'Male', 'huy.nk@gmail.com', '0967678901', 'Gia Lai', 2, 1),
('20210012', 'Nguyễn Lan Anh', '2003-10-15', 'Female', 'anh.nl@gmail.com', '0978789012', 'Quảng Ninh', 1, 1),
('20230105', 'Trần Bảo Long', '2005-04-18', 'Male', 'long.tb@gmail.com', '0989890123', 'Bắc Ninh', 1, 1),
('20220500', 'Lý Hải', '2004-06-06', 'Male', 'hai.l@gmail.com', '0990901234', 'Tiền Giang', 3, 1),
('20238888', 'Sơn Tùng M-TP', '2005-07-05', 'Male', 'tung.st@gmail.com', '0911999888', 'Thái Bình', 2, 1),
('20231234', 'Nguyễn Thúc Thùy Tiên', '2005-08-12', 'Female', 'tien.ntt@gmail.com', '0922888777', 'TP. Hồ Chí Minh', 1, 1),
('20235555', 'Hieuthuhai', '2005-02-28', 'Male', 'hieu.thh@gmail.com', '0933777666', 'Đồng Nai', 2, 1);

-- 4. Dữ liệu học phần
INSERT INTO subjects (subject_code, subject_name, credit, description) VALUES
('MATH101', 'Giải tích I', 3, 'Đạo hàm, tích phân và ứng dụng'),
('PHYS101', 'Vật lý đại cương I', 4, 'Cơ học cổ điển'),
('CS101', 'Nhập môn lập trình', 4, 'Cơ bản về logic lập trình và C/C++'),
('MATH201', 'Toán rời rạc', 3, 'Logic, tập hợp, đồ thị'),
('CS201', 'Cấu trúc dữ liệu và giải thuật', 4, 'Danh sách, cây, sắp xếp, tìm kiếm'),
('CS301', 'Hệ quản trị cơ sở dữ liệu', 3, 'SQL, thiết kế DB'),
('ENG101', 'Tiếng Anh chuyên ngành', 2, 'Tiếng Anh kỹ thuật'),
('MKT101', 'Marketing cơ bản', 3, 'Nguyên lý Marketing');

-- 5. Dữ liệu điểm số (trộn lẫn các mức điểm)
-- Ghi chú: student_id sẽ tương ứng với thứ tự insert ở trên (1-15)
INSERT INTO grades (student_id, subject_id, midterm_score, final_score, other_score, average_score, letter_grade, semester, academic_year) VALUES
-- Sinh viên 1 (An)
(1, 1, 8.5, 9.0, 10.0, 9.1, 'A+', '1', '2023-2024'),
(1, 2, 7.0, 8.0, 8.5, 7.8, 'B+', '1', '2023-2024'),
(1, 4, 9.0, 9.5, 10.0, 9.5, 'A+', '1', '2023-2024'),
-- Sinh viên 2 (Bình)
(2, 1, 9.0, 8.5, 9.0, 8.8, 'A', '1', '2023-2024'),
(2, 3, 10.0, 9.5, 10.0, 9.8, 'A+', '1', '2023-2024'),
-- Sinh viên 3 (Cường)
(3, 2, 6.0, 7.0, 7.5, 6.8, 'B', '1', '2023-2024'),
(3, 5, 8.5, 9.0, 8.0, 8.6, 'A', '1', '2023-2024'),
-- Sinh viên 4 (Đức)
(4, 3, 4.0, 4.5, 5.0, 4.5, 'D', '1', '2023-2024'),
(4, 1, 3.0, 2.0, 4.0, 2.7, 'F', '1', '2023-2024'),
-- Sinh viên 5 (Hà)
(5, 7, 9.5, 10.0, 10.0, 9.9, 'A+', '1', '2023-2024'),
(5, 8, 8.0, 8.5, 9.0, 8.5, 'A', '1', '2023-2024'),
-- Sinh viên 6 (Hùng)
(6, 1, 6.5, 6.0, 7.0, 6.4, 'C+', '1', '2022-2023'),
(6, 4, 7.5, 8.0, 7.0, 7.6, 'B', '1', '2022-2023'),
-- Sinh viên 7 (Thảo)
(7, 2, 9.0, 9.5, 9.0, 9.3, 'A', '1', '2022-2023'),
(7, 3, 8.5, 8.0, 9.0, 8.4, 'B+', '1', '2022-2023'),
-- Sinh viên 8 (Huấn)
(8, 1, 4.0, 3.0, 5.0, 3.8, 'F', '1', '2024-2025'),
-- Sinh viên 10 (Lan Anh)
(10, 5, 10.0, 10.0, 10.0, 10.0, 'A+', '1', '2021-2022'),
(10, 6, 9.0, 8.5, 9.5, 8.9, 'A', '1', '2021-2022'),
-- Sinh viên 13 (Sơn Tùng)
(13, 1, 10.0, 10.0, 10.0, 10.0, 'A+', '1', '2023-2024'),
(13, 8, 10.0, 9.5, 10.0, 9.8, 'A+', '1', '2023-2024'),
-- Sinh viên 15 (Hieuthuhai)
(15, 3, 8.0, 8.5, 9.0, 8.5, 'A', '1', '2023-2024');

-- 6. Tài khoản mẫu
INSERT INTO users (username, password, role, student_id, is_active) VALUES
('admin', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'admin', NULL, 1),
('20230001', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 1, 1),
('20230002', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 2, 1),
('20239614', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 3, 1),
('20230010', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 4, 1),
('20230025', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 5, 1),
('20220001', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 6, 1),
('20220015', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 7, 1),
('20240001', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 8, 1),
('20240456', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 9, 1),
('20210012', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 10, 1),
('20230105', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 11, 1),
('20220500', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 12, 1),
('20238888', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 13, 1),
('20231234', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 14, 1),
('20235555', '$2y$10$LvYXyriTsrXpn93olLCXNeekAVHd4o6dm/VbwaMr9Pl0lA0dAEuqW', 'student', 15, 1);
