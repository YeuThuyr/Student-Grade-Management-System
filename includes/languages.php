<?php
// includes/languages.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get current application language ('vi' or 'en')
 */
function get_app_lang()
{
    // Try cookie first, then session, then default to 'vi'
    if (isset($_COOKIE['app_lang'])) {
        return $_COOKIE['app_lang'] === 'en' ? 'en' : 'vi';
    }
    if (isset($_SESSION['app_lang'])) {
        return $_SESSION['app_lang'] === 'en' ? 'en' : 'vi';
    }
    return 'vi';
}

/**
 * Get all translations array
 */
function get_all_translations()
{
    $current_year = date('Y');
    return [
        'vi' => [
            // Navbar
            'brand_name' => 'Đại Học Bách Khoa Hà Nội',
            'brand_subtitle' => 'HỆ THỐNG QUẢN TRỊ ĐẠI HỌC TRỰC TUYẾN',
            'nav_home' => 'Trang Chủ',
            'nav_students' => 'Sinh viên',
            'nav_subjects' => 'Môn học',
            'nav_grades' => 'Điểm',
            'nav_classes' => 'Lớp',
            'nav_profile' => 'Hồ sơ',
            'nav_transcript' => 'Bảng điểm',
            'nav_contact' => 'Liên Hệ Và Phản Hồi',
            'nav_login' => 'ĐĂNG NHẬP',
            'nav_logout' => 'ĐĂNG XUẤT',

            // Homepage - Guest
            'welcome_title' => 'Chào mừng đến với Hệ Thống Quản Trị Đại Học',
            'welcome_subtitle' => 'Đại Học Bách Khoa Hà Nội — Hệ thống tra cứu và quản lý điểm sinh viên trực tuyến',
            'feature_search_title' => 'Tra Cứu Điểm',
            'feature_search_desc' => 'Xem bảng điểm chi tiết theo từng môn học, học kỳ và năm học một cách nhanh chóng.',
            'feature_stats_title' => 'Thống Kê Học Tập',
            'feature_stats_desc' => 'Theo dõi GPA, phân tích kết quả học tập và tiến độ qua các học kỳ.',
            'feature_account_title' => 'Quản Lý Tài Khoản',
            'feature_account_desc' => 'Quản lý hồ sơ cá nhân, cập nhật thông tin và bảo mật tài khoản.',
            'login_cta' => 'Đăng nhập để tiếp tục',
            'login_note' => 'Vui lòng đăng nhập bằng tài khoản sinh viên hoặc quản trị viên để sử dụng hệ thống.',

            // Homepage - Authenticated
            'search_title' => 'Tra cứu điểm sinh viên',
            'search_subtitle' => 'Nhập mã sinh viên hoặc họ tên để tra cứu điểm.',
            'label_student_code' => 'Mã SV hoặc Họ và tên',
            'label_academic_year' => 'Năm học',
            'option_all_years' => 'Tất cả năm học',
            'btn_search' => 'Tra cứu',
            'results_title' => 'Kết quả tra cứu',
            'unit_students' => 'sinh viên',
            'no_results' => 'Không tìm thấy sinh viên phù hợp với điều kiện tra cứu.',
            'no_grades' => 'Sinh viên này chưa có điểm.',

            // Table headers
            'th_subject' => 'Môn học',
            'th_credits' => 'Tín chỉ',
            'th_semester' => 'HK',
            'th_year' => 'Năm học',
            'th_midterm' => 'Giữa kỳ',
            'th_final' => 'Cuối kỳ',
            'th_other' => 'Khác',
            'th_average' => 'TB',
            'th_letter' => 'Điểm chữ',

            // Footer & App section
            'app_title' => 'HỆ THỐNG DO ĐẠI HỌC BÁCH KHOA HÀ NỘI THIẾT KẾ & PHÁT TRIỂN',
            'app_subtitle' => 'Cài đặt ứng dụng eHUST trên điện thoại',
            'get_it_on' => 'Get it on',
            'download_on' => 'Download on the',
            'footer_copyright' => '© ' . $current_year . ' Đại Học Bách Khoa Hà Nội',

            // Placeholder
            'placeholder_student_code' => 'Nhập mã SV hoặc họ tên...',

            // Contact page
            'contact_title' => 'Liên hệ và phản hồi',
            'contact_desc' => 'Gửi ý kiến, câu hỏi hoặc báo lỗi về hệ thống quản lý điểm.',
            'contact_success' => 'Phản hồi của bạn đã được gửi thành công.',
            'contact_name' => 'Họ và tên',
            'contact_email' => 'Email',
            'contact_subject' => 'Tiêu đề',
            'contact_message' => 'Nội dung',
            'contact_submit' => 'Gửi phản hồi',
            'contact_name_placeholder' => 'Họ và tên của bạn',
            'contact_email_placeholder' => 'Địa chỉ email của bạn',
            'contact_subject_placeholder' => 'Tiêu đề tin nhắn',
            'contact_message_placeholder' => 'Nhập nội dung phản hồi tại đây...',

            // Contact validation errors
            'contact_err_name' => 'Vui lòng nhập họ và tên.',
            'contact_err_email' => 'Vui lòng nhập email hợp lệ.',
            'contact_err_subject' => 'Vui lòng nhập tiêu đề phản hồi.',
            'contact_err_message' => 'Vui lòng nhập nội dung phản hồi.',
            'contact_err_send' => 'Không thể gửi phản hồi lúc này. Vui lòng thử lại sau hoặc gửi trực tiếp đến thienstyle2k5@gmail.com.',

            // Login page
            'login_title' => 'Đăng Nhập Hệ Thống',
            'login_subtitle' => 'Vui lòng nhập thông tin để truy cập',
            'login_username' => 'Tên đăng nhập',
            'login_password' => 'Mật khẩu',
            'login_submit' => 'Đăng Nhập',
            'login_err_empty' => 'Vui lòng nhập đầy đủ thông tin.',
            'login_err_failed' => 'Tài khoản hoặc mật khẩu không chính xác.',

            // Dashboard
            'dash_title' => 'Dashboard Thống Kê',
            'dash_back' => 'Quay lại',
            'dash_total_students' => 'Tổng số sinh viên',
            'dash_avg_gpa' => 'GPA Trung Bình',
            'dash_pass_rate' => 'Tỷ lệ Đạt',
            'dash_search_title' => 'Tìm kiếm Sinh viên theo Mã số',
            'dash_search_btn' => 'Tìm kiếm',
            'dash_filter_year' => 'Năm học',
            'dash_all_years' => 'Tất cả các năm',
            'dash_filter_gender' => 'Giới tính',
            'dash_all' => 'Tất cả',
            'dash_male' => 'Nam',
            'dash_female' => 'Nữ',
            'dash_filter_gpa' => 'Xếp loại GPA',
            'dash_all_gpa' => 'Tất cả mức điểm',
            'dash_pass_fail_chart' => 'Tỷ lệ Đạt / Trượt',
            'dash_grade_dist_chart' => 'Phân bố điểm chữ',

            // Student list
            'stu_list_title' => 'Quản lý sinh viên',
            'stu_list_desc' => 'Thêm, sửa, tìm kiếm và hiển thị danh sách sinh viên.',
            'stu_optimal_search' => 'Tìm kiếm Tối ưu & Benchmarks',
            'stu_add_btn' => 'Thêm sinh viên',
            'stu_all_classes' => 'Tất cả lớp',
            'stu_th_code' => 'Mã SV',
            'stu_th_name' => 'Họ và tên',
            'stu_th_email' => 'Email',
            'stu_th_gpa' => 'GPA',
            'stu_th_reg_date' => 'Ngày đăng ký',
            'stu_no_results' => 'Không tìm thấy sinh viên nào.',
            'stu_deactivate' => 'Vô hiệu',

            // Subject list
            'subj_list_title' => 'Quản lý môn học',
            'subj_list_desc' => 'Thêm, sửa và xóa thông tin môn học.',
            'subj_add_btn' => 'Thêm môn học',
            'subj_search_ph' => 'Mã môn học hoặc tên môn',
            'subj_th_code' => 'Mã môn học',
            'subj_th_name' => 'Tên môn học',
            'subj_th_desc' => 'Miêu tả',
            'subj_no_results' => 'Không tìm thấy môn học.',

            // Grade list
            'grade_list_title' => 'Danh sách điểm',
            'grade_list_desc' => 'Xem và lọc điểm theo môn, học kỳ và năm học.',
            'grade_add_edit' => 'Thêm/Chỉnh sửa điểm',
            'grade_search_student' => 'Tìm sinh viên',
            'grade_semester' => 'Học kỳ',
            'grade_th_student' => 'Sinh viên',
            'grade_no_results' => 'Không có bản ghi điểm phù hợp.',
            'grade_filter_prompt' => 'Vui lòng nhập hoặc chọn ít nhất một bộ lọc để xem điểm.',
            'grade_locked' => 'Đã khóa',

            // Class list
            'class_list_title' => 'Quản lý lớp',
            'class_list_desc' => 'Tạo và cập nhật lớp học.',
            'class_add_btn' => 'Thêm lớp',
            'class_search_ph' => 'Mã lớp, tên lớp, chuyên ngành hoặc giáo viên',
            'class_th_code' => 'Mã lớp',
            'class_th_name' => 'Tên lớp',
            'class_no_results' => 'Không tìm thấy lớp học.',

            // Common
            'common_search' => 'Tìm kiếm',
            'common_filter' => 'Lọc',
            'common_edit' => 'Sửa',
            'common_delete' => 'Xóa',
            'common_actions' => 'Hành động',
            'common_class' => 'Lớp',
            'common_back_list' => 'Quay lại danh sách',
            'common_save' => 'Lưu',

            // Error messages
            'err_numeric_code' => 'Mã sinh viên chỉ được nhập số và tối đa 8 chữ số.',

            // GPA levels
            'gpa_excellent' => 'Xuất sắc',
            'gpa_good' => 'Khá',
            'gpa_average' => 'Trung bình',
            'gpa_weak' => 'Yếu',
            'gpa_pass' => 'Đạt',
            'gpa_fail' => 'Trượt',
        ],
        'en' => [
            // Navbar
            'brand_name' => 'Hanoi University of Science and Technology',
            'brand_subtitle' => 'ONLINE UNIVERSITY MANAGEMENT SYSTEM',
            'nav_home' => 'Home',
            'nav_students' => 'Students',
            'nav_subjects' => 'Subjects',
            'nav_grades' => 'Grades',
            'nav_classes' => 'Classes',
            'nav_profile' => 'Profile',
            'nav_transcript' => 'Transcript',
            'nav_contact' => 'Contact & Feedback',
            'nav_login' => 'LOG IN',
            'nav_logout' => 'LOG OUT',

            // Homepage - Guest
            'welcome_title' => 'Welcome to the University Management System',
            'welcome_subtitle' => 'Hanoi University of Science and Technology — Online student grade lookup and management system',
            'feature_search_title' => 'Grade Lookup',
            'feature_search_desc' => 'View detailed transcripts by subject, semester and academic year quickly.',
            'feature_stats_title' => 'Academic Statistics',
            'feature_stats_desc' => 'Track GPA, analyze academic results and progress across semesters.',
            'feature_account_title' => 'Account Management',
            'feature_account_desc' => 'Manage personal profile, update information and secure your account.',
            'login_cta' => 'Log in to continue',
            'login_note' => 'Please log in with your student or administrator account to use the system.',

            // Homepage - Authenticated
            'search_title' => 'Student Grade Lookup',
            'search_subtitle' => 'Enter student ID or full name to view grades.',
            'label_student_code' => 'Student ID or Full Name',
            'label_academic_year' => 'Academic Year',
            'option_all_years' => 'All Academic Years',
            'btn_search' => 'Search',
            'results_title' => 'Search Results',
            'unit_students' => 'students',
            'no_results' => 'No students found matching the search criteria.',
            'no_grades' => 'This student has no grades yet.',

            // Table headers
            'th_subject' => 'Subject',
            'th_credits' => 'Credits',
            'th_semester' => 'Sem',
            'th_year' => 'Year',
            'th_midterm' => 'Midterm',
            'th_final' => 'Final',
            'th_other' => 'Other',
            'th_average' => 'Avg',
            'th_letter' => 'Letter',

            // Footer & App section
            'app_title' => 'SYSTEM DESIGNED & DEVELOPED BY HANOI UNIVERSITY OF SCIENCE AND TECHNOLOGY',
            'app_subtitle' => 'Install the eHUST app on your phone',
            'get_it_on' => 'Get it on',
            'download_on' => 'Download on the',
            'footer_copyright' => '© ' . $current_year . ' Hanoi University of Science and Technology',

            // Placeholder
            'placeholder_student_code' => 'Enter student ID or name...',

            // Contact page
            'contact_title' => 'Contact & Feedback',
            'contact_desc' => 'Send opinions, questions or report issues about the grade management system.',
            'contact_success' => 'Your feedback has been sent successfully.',
            'contact_name' => 'Full Name',
            'contact_email' => 'Email',
            'contact_subject' => 'Subject',
            'contact_message' => 'Message',
            'contact_submit' => 'Send Feedback',
            'contact_name_placeholder' => 'Your full name',
            'contact_email_placeholder' => 'Your email address',
            'contact_subject_placeholder' => 'Message subject',
            'contact_message_placeholder' => 'Type your feedback message here...',

            // Contact validation errors
            'contact_err_name' => 'Please enter your full name.',
            'contact_err_email' => 'Please enter a valid email address.',
            'contact_err_subject' => 'Please enter a message subject.',
            'contact_err_message' => 'Please enter your message.',
            'contact_err_send' => 'Unable to send feedback at this time. Please try again later or email directly to thienstyle2k5@gmail.com.',

            // Login page
            'login_title' => 'System Login',
            'login_subtitle' => 'Please enter your credentials to access',
            'login_username' => 'Username',
            'login_password' => 'Password',
            'login_submit' => 'Log In',
            'login_err_empty' => 'Please enter all fields.',
            'login_err_failed' => 'Incorrect username or password.',

            // Dashboard
            'dash_title' => 'Statistics Dashboard',
            'dash_back' => 'Go Back',
            'dash_total_students' => 'Total Students',
            'dash_avg_gpa' => 'Average GPA',
            'dash_pass_rate' => 'Pass Rate',
            'dash_search_title' => 'Search Students by Code',
            'dash_search_btn' => 'Search',
            'dash_filter_year' => 'Academic Year',
            'dash_all_years' => 'All Years',
            'dash_filter_gender' => 'Gender',
            'dash_all' => 'All',
            'dash_male' => 'Male',
            'dash_female' => 'Female',
            'dash_filter_gpa' => 'GPA Classification',
            'dash_all_gpa' => 'All GPA Levels',
            'dash_pass_fail_chart' => 'Pass / Fail Rate',
            'dash_grade_dist_chart' => 'Letter Grade Distribution',

            // Student list
            'stu_list_title' => 'Student Management',
            'stu_list_desc' => 'Add, edit, search and display student lists.',
            'stu_optimal_search' => 'Optimal Search & Benchmarks',
            'stu_add_btn' => 'Add Student',
            'stu_all_classes' => 'All Classes',
            'stu_th_code' => 'Student ID',
            'stu_th_name' => 'Full Name',
            'stu_th_email' => 'Email',
            'stu_th_gpa' => 'GPA',
            'stu_th_reg_date' => 'Registration Date',
            'stu_no_results' => 'No students found.',
            'stu_deactivate' => 'Deactivate',

            // Subject list
            'subj_list_title' => 'Subject Management',
            'subj_list_desc' => 'Add, edit and delete subject information.',
            'subj_add_btn' => 'Add Subject',
            'subj_search_ph' => 'Subject code or name',
            'subj_th_code' => 'Subject Code',
            'subj_th_name' => 'Subject Name',
            'subj_th_desc' => 'Description',
            'subj_no_results' => 'No subjects found.',

            // Grade list
            'grade_list_title' => 'Grade List',
            'grade_list_desc' => 'View and filter grades by subject, semester and year.',
            'grade_add_edit' => 'Add/Edit Grades',
            'grade_search_student' => 'Search Student',
            'grade_semester' => 'Semester',
            'grade_th_student' => 'Student',
            'grade_no_results' => 'No matching grade records.',
            'grade_filter_prompt' => 'Please enter or select at least one filter option to view grades.',
            'grade_locked' => 'Locked',

            // Class list
            'class_list_title' => 'Class Management',
            'class_list_desc' => 'Create and update classes.',
            'class_add_btn' => 'Add Class',
            'class_search_ph' => 'Class code, name, major, or teacher',
            'class_th_code' => 'Class Code',
            'class_th_name' => 'Class Name',
            'class_no_results' => 'No classes found.',

            // Common
            'common_search' => 'Search',
            'common_filter' => 'Filter',
            'common_edit' => 'Edit',
            'common_delete' => 'Delete',
            'common_actions' => 'Actions',
            'common_class' => 'Class',
            'common_back_list' => 'Back to List',
            'common_save' => 'Save',

            // Error messages
            'err_numeric_code' => 'Student code must contain only numbers and be up to 8 digits.',

            // GPA levels
            'gpa_excellent' => 'Excellent',
            'gpa_good' => 'Good',
            'gpa_average' => 'Average',
            'gpa_weak' => 'Weak',
            'gpa_pass' => 'Passed',
            'gpa_fail' => 'Failed',
        ]
    ];
}

/**
 * Translate a key to current language
 */
function __($key, $default = '')
{
    $lang = get_app_lang();
    $translations = get_all_translations();
    
    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }
    
    // Fallback to Vietnamese if not found in current language
    if (isset($translations['vi'][$key])) {
        return $translations['vi'][$key];
    }
    
    return $default !== '' ? $default : $key;
}
