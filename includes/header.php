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
<html lang="<?php echo get_app_lang(); ?>">

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

<body class="d-flex flex-column min-vh-100">

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
                        <span class="fw-bold fs-6 text-white text-uppercase tracking-wide" data-i18n="brand_name"><?php echo __('brand_name'); ?></span>
                        <span class="opacity-75" style="font-size: 0.8rem;" data-i18n="brand_subtitle"><?php echo __('brand_subtitle'); ?></span>
                    </div>
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menus & Actions -->
                <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
                    <ul class="navbar-nav align-items-center gap-2 gap-lg-4 mx-auto mb-3 mb-lg-0 mt-3 mt-lg-0">
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>index.php" data-i18n="nav_home"><?php echo __('nav_home'); ?></a>
                        </li>
                        <?php if ($isLoggedIn): ?>
                            <?php $role = $_SESSION['user']['role'] ?? ''; ?>
                            <?php if ($role === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom"
                                        href="<?php echo BASE_PATH; ?>dashboard.php">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>students/list.php" data-i18n="nav_students"><?php echo __('nav_students'); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>subjects/list.php" data-i18n="nav_subjects"><?php echo __('nav_subjects'); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>grades/list.php" data-i18n="nav_grades"><?php echo __('nav_grades'); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>classes/list.php" data-i18n="nav_classes"><?php echo __('nav_classes'); ?></a>
                                </li>
                            <?php elseif ($role === 'student'): ?>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>students/profile.php" data-i18n="nav_profile"><?php echo __('nav_profile'); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>grades/list.php" data-i18n="nav_transcript"><?php echo __('nav_transcript'); ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo BASE_PATH; ?>contact.php" data-i18n="nav_contact"><?php echo __('nav_contact'); ?></a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        <!-- Language Switcher -->
                        <div class="lang-switcher" id="langSwitcher">
                            <button class="lang-toggle-btn" id="langToggleBtn" type="button" aria-label="Switch language">
                                <i class="fas fa-globe lang-globe-icon"></i>
                                <span class="lang-label" id="langLabel">VI</span>
                                <i class="fas fa-chevron-down lang-chevron"></i>
                            </button>
                            <div class="lang-dropdown" id="langDropdown">
                                <button class="lang-option active" data-lang="vi" type="button">
                                    <span class="lang-flag">🇻🇳</span>
                                    <span class="lang-name">Tiếng Việt</span>
                                    <i class="fas fa-check lang-check"></i>
                                </button>
                                <button class="lang-option" data-lang="en" type="button">
                                    <span class="lang-flag">🇬🇧</span>
                                    <span class="lang-name">English</span>
                                    <i class="fas fa-check lang-check"></i>
                                </button>
                            </div>
                        </div>

                        <?php if ($isLoggedIn): ?>
                            <a href="<?php echo BASE_PATH; ?>auth/logout.php" class="btn login-btn px-4 text-nowrap" data-i18n="nav_logout"><?php echo __('nav_logout'); ?></a>
                        <?php else: ?>
                            <a href="<?php echo BASE_PATH; ?>auth/login.php" class="btn login-btn px-4 text-nowrap" data-i18n="nav_login"><?php echo __('nav_login'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
