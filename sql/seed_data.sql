INSERT INTO students (student_code, full_name, date_of_birth, gender, email, phone, address) VALUES
('SV001', 'Nguyen Van A', '2005-05-15', 'Male', 'vana@example.com', '0123456789', 'Hanoi, Vietnam'),
('SV002', 'Tran Thi B', '2005-08-20', 'Female', 'thib@example.com', '0987654321', 'HCM City, Vietnam'),
('SV003', 'Le Van C', '2004-12-10', 'Male', 'vanc@example.com', '0345678901', 'Da Nang, Vietnam');

INSERT INTO subjects (subject_code, subject_name, credit, description) VALUES
('MATH101', 'Calculus I', 3, 'Basic differentiation and integration'),
('PHYS101', 'General Physics I', 4, 'Classical mechanics'),
('CS101', 'Introduction to Programming', 4, 'Basics of C and programming logic');

INSERT INTO grades (student_id, subject_id, midterm_score, final_score, other_score, average_score, letter_grade, semester, academic_year) VALUES
(1, 1, 8.5, 9.0, 10.0, 9.1, 'A', '1', '2023-2024'),
(1, 2, 7.0, 8.0, 8.5, 7.8, 'B+', '1', '2023-2024'),
(2, 1, 9.0, 8.5, 9.0, 8.8, 'A', '1', '2023-2024'),
(2, 3, 10.0, 9.5, 10.0, 9.8, 'A+', '1', '2023-2024'),
(3, 2, 6.0, 7.0, 7.5, 6.8, 'B', '1', '2023-2024');
