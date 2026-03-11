<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);

// Các hằng số điều khiển việc hiển thị trong header/footer
define('IS_HOMEPAGE', true);
define('SHOW_APP_SECTION', true);

// Nạp Header
require_once __DIR__ . '/includes/header.php';
?>

    <!-- 2. TEACHER SERVICES SECTION -->
    <section class="container py-5 mt-4" id="giang-vien">
        <h2 class="section-title text-center fw-bold text-dark mb-5">Dịch Vụ Cho Giảng Viên</h2>
        
        <div class="row g-4">
            <!-- Card 1 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Giảng Dạy" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Giảng Dạy</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Kế hoạch giảng dạy, thời khóa biểu và quản lý lớp học phần.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Hướng Dẫn" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Hướng Dẫn</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Quản lý sinh viên thực tập, đồ án tốt nghiệp, hướng dẫn NCKH.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Phân Công" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Phân Công</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Tra cứu khối lượng công việc, lịch trực và phân công giảng dạy.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Tổ Chức Cán Bộ" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Tổ Chức Cán Bộ</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Hồ sơ cán bộ, đánh giá xếp loại và thông tin nhân sự.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 5 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Cơ Sở Vật Chất" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Cơ Sở Vật Chất</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Đăng ký phòng học, mượn trang thiết bị và báo cáo sửa chữa.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 6 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Hợp Tác Đối Ngoại" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Hợp Tác Đối Ngoại</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Chương trình trao đổi, học thuật, quản lý dự án quốc tế.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 7 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Học Viên" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Học Viên</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Thông tin học viên Sau Đại học, bảo vệ luận văn, luận án.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            <!-- Card 8 -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm rounded-3 teacher-card">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="card-img-top" alt="Dashboard" style="height: 140px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold text-dark mb-2">Dashboard</h5>
                        <p class="card-text text-muted flex-grow-1" style="font-size: 0.9rem;">Bảng thống kê toàn cảnh dành cho cán bộ quản lý.</p>
                        <a href="#" class="tc-btn d-inline-block px-3 py-1 align-self-start text-decoration-none rounded-pill fw-semibold" style="background-color: #fce4e4; color: var(--hust-red); font-size: 0.85rem; transition: all 0.3s ease;">Chi tiết <i class="fas fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                    </div>
                </div>
            </div>
            

        </div>
    </section>

    <!-- 3. STUDENT SERVICES SECTION -->
    <section class="bg-white py-5 shadow-sm" id="sinh-vien">
        <div class="container py-4">
            <h2 class="section-title text-center fw-bold text-dark mb-5">Dịch Vụ Cho Sinh Viên</h2>
            


            <div class="row g-4">
                <!-- Data for Student Cards -->
                <?php
                $studentServices = [
                    ['icon' => 'fa-envelope', 'title' => 'Thư Báo Tin Nhắn', 'desc' => 'Hộp thư điện tử, thông báo.'],
                    ['icon' => 'fa-money-bill-wave', 'title' => 'Học Phí Công Nợ', 'desc' => 'Thanh toán và tra cứu học phí.'],
                    ['icon' => 'fa-briefcase', 'title' => 'Hướng Nghiệp', 'desc' => 'Cơ hội việc làm, thực tập doanh nghiệp.'],
                    ['icon' => 'fa-id-card', 'title' => 'Hồ Sơ Sinh Viên', 'desc' => 'Cập nhật thông tin lý lịch cá nhân.'],
                    ['icon' => 'fa-file-invoice', 'title' => 'Đồ Án Tốt Nghiệp', 'desc' => 'Đăng ký và theo dõi tiến độ đồ án.'],
                    ['icon' => 'fa-award', 'title' => 'Học Bổng', 'desc' => 'Khuyến khích học tập, học bổng tài trợ.'],
                    ['icon' => 'fa-calendar-alt', 'title' => 'Thời Khoá Biểu', 'desc' => 'Lịch học, lịch thi cá nhân.'],
                    ['icon' => 'fa-book-open', 'title' => 'Chương Trình Đào Tạo', 'desc' => 'Niên giám, chuẩn đầu ra, học phần.'],
                    ['icon' => 'fa-clipboard-list', 'title' => 'Thủ Tục Hành Chính', 'desc' => 'Cấp giấy chứng nhận, thẻ sinh viên.'],
                    ['icon' => 'fa-chart-line', 'title' => 'Kết Quả Học Tập', 'desc' => 'Bảng điểm, đánh giá quá trình học.'],
                    ['icon' => 'fa-running', 'title' => 'Hoạt Động Ngoại Khoá', 'desc' => 'Câu lạc bộ, điểm rèn luyện.'],
                    ['icon' => 'fa-plus-circle', 'title' => 'Đăng Ký Dịch Vụ', 'desc' => 'Mua BHYT, internet ký túc xá.'],
                    ['icon' => 'fa-pen-square', 'title' => 'Đăng Ký Học Tập', 'desc' => 'Đăng ký lớp học phần, học cải thiện.'],
                    ['icon' => 'fa-star', 'title' => 'Đánh Giá Rèn Luyện', 'desc' => 'Phiếu đánh giá, phản hồi môn học.'],
                    ['icon' => 'fa-laptop', 'title' => 'Thư Viện Số', 'desc' => 'Tra cứu sách, mượn tài liệu số.'],
                    ['icon' => 'fa-question-circle', 'title' => 'Trợ Giúp', 'desc' => 'Hỏi đáp, tư vấn tâm lý học đường.'],
                    ['icon' => 'fa-home', 'title' => 'Ký Túc Xá', 'desc' => 'Đăng ký phòng, thanh toán tiền lưu trú.'],
                    ['icon' => 'fa-balance-scale', 'title' => 'Văn Bản Pháp Luật', 'desc' => 'Quy chế nội quy, quy định sinh viên.']
                ];
                
                foreach ($studentServices as $service) {
                    echo '
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 rounded-3 p-3 student-card">
                            <div class="d-flex align-items-center">
                                <div class="sc-icon flex-shrink-0 me-3">
                                    <i class="fas ' . $service['icon'] . '"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">' . $service['title'] . '</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.85rem;">' . $service['desc'] . '</p>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

<?php 
// Nạp Footer
require_once __DIR__ . '/includes/footer.php'; 
?>
