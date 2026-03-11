<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);

// Xác định thư mục gốc để nạp file tĩnh đúng đường dẫn
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/grade_management/');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Trị Đại Học Trực Tuyến - Đại Học Bách Khoa Hà Nội</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Theme styles (thêm tham số v= để tránh lỗi cache giao diện cũ) -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/style.css?v=<?php echo time(); ?>">


</head>
<body>

    <!-- HERO HEADER SHARED -->
    <?php 
        // Allow dynamic class setting based on page
        $heroClass = defined('IS_HOMEPAGE') && IS_HOMEPAGE ? 'hero-lg' : 'hero-sm'; 
    ?>
    <header class="hero-section d-flex flex-column <?php echo $heroClass; ?>">
        <?php if (defined('IS_HOMEPAGE') && IS_HOMEPAGE): ?>
            <div class="hero-overlay"></div>
        <?php endif; ?>
        
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3 px-4 w-100">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center gap-3" href="<?php echo BASE_PATH; ?>index.php">
                    <div class="nav-logo">HUST</div>
                    <div class="d-flex flex-column lh-sm">
                        <span class="fw-bold fs-6 text-white text-uppercase tracking-wide">Đại Học Bách Khoa Hà Nội</span>
                        <span class="opacity-75" style="font-size: 0.8rem;">HỆ THỐNG QUẢN TRỊ ĐẠI HỌC TRỰC TUYẾN</span>
                    </div>
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menus & Actions -->
                <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
                    <ul class="navbar-nav align-items-center gap-2 gap-lg-4 mx-auto mb-3 mb-lg-0 mt-3 mt-lg-0">
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>index.php">Trang Chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>index.php#giang-vien">Giảng Viên</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>index.php#sinh-vien">Sinh Viên</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#">Cổng Thông Tin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#">Liên Hệ Và Phản Hồi</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        <select class="form-select w-auto lang-select px-3 py-1 text-white bg-transparent">
                            <option value="vi">VI</option>
                            <option value="en">EN</option>
                        </select>
                        
                        <?php if ($isLoggedIn): ?>
                            <a href="<?php echo BASE_PATH; ?>auth/logout.php" class="btn login-btn px-4 text-nowrap">ĐĂNG XUẤT</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_PATH; ?>auth/login.php" class="btn login-btn px-4 text-nowrap">ĐĂNG NHẬP</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
