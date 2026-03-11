<?php
// includes/footer.php
?>
    <!-- 4. MOBILE APP SECTION & FOOTER SHARED -->


    <?php if (defined('SHOW_APP_SECTION') && SHOW_APP_SECTION): ?>
    <section class="app-section py-5 text-center mt-auto">
        <div class="container py-5">
            <h2 class="fw-bold text-dark mb-2 fs-2 mx-auto" style="max-width: 800px;">
                HỆ THỐNG DO ĐẠI HỌC BÁCH KHOA HÀ NỘI THIẾT KẾ & PHÁT TRIỂN
            </h2>
            <p class="text-muted fs-5 mb-5 mx-auto">Cài đặt ứng dụng eHUST trên điện thoại</p>
            
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="#" class="app-btn rounded-3 px-4 py-2 d-inline-flex align-items-center justify-content-center text-start user-select-none">
                    <i class="fab fa-google-play fs-1 me-3"></i>
                    <div>
                        <span class="d-block text-uppercase" style="font-size: 0.7rem; opacity: 0.8;">Get it on</span>
                        <span class="d-block fw-semibold fs-5 lh-1 mt-1">Google Play</span>
                    </div>
                </a>
                
                <a href="#" class="app-btn rounded-3 px-4 py-2 d-inline-flex align-items-center justify-content-center text-start user-select-none">
                    <i class="fab fa-apple fs-1 me-3"></i>
                    <div>
                        <span class="d-block text-uppercase" style="font-size: 0.7rem; opacity: 0.8;">Download on the</span>
                        <span class="d-block fw-semibold fs-5 lh-1 mt-1">App Store</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <span class="text-white-50 fs-6">&copy; <?php echo date('Y'); ?> Đại Học Bách Khoa Hà Nội</span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            if(anchor.getAttribute('href') !== '#') {
                anchor.addEventListener('click', function (e) {
                    // prevent default only if on homepage and the section actually exists
                    const targetEl = document.querySelector(this.getAttribute('href'));
                    if(targetEl) {
                        e.preventDefault();
                        targetEl.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }
        });
    </script>
</body>
</html>
