<?php
// includes/footer.php
?>
    <!-- 4. MOBILE APP SECTION & FOOTER SHARED -->


    <?php if (defined('SHOW_APP_SECTION') && SHOW_APP_SECTION): ?>
    <section class="app-section py-5 text-center mt-auto">
        <div class="container py-5">
            <h2 class="fw-bold text-dark mb-2 fs-2 mx-auto" style="max-width: 800px;" data-i18n="app_title">
                HỆ THỐNG DO ĐẠI HỌC BÁCH KHOA HÀ NỘI THIẾT KẾ & PHÁT TRIỂN
            </h2>
            <p class="text-muted fs-5 mb-5 mx-auto" data-i18n="app_subtitle">Cài đặt ứng dụng eHUST trên điện thoại</p>
            
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="#" class="app-btn rounded-3 px-4 py-2 d-inline-flex align-items-center justify-content-center text-start user-select-none">
                    <i class="fab fa-google-play fs-1 me-3"></i>
                    <div>
                        <span class="d-block text-uppercase" style="font-size: 0.7rem; opacity: 0.8;" data-i18n="get_it_on">Get it on</span>
                        <span class="d-block fw-semibold fs-5 lh-1 mt-1">Google Play</span>
                    </div>
                </a>
                
                <a href="#" class="app-btn rounded-3 px-4 py-2 d-inline-flex align-items-center justify-content-center text-start user-select-none">
                    <i class="fab fa-apple fs-1 me-3"></i>
                    <div>
                        <span class="d-block text-uppercase" style="font-size: 0.7rem; opacity: 0.8;" data-i18n="download_on">Download on the</span>
                        <span class="d-block fw-semibold fs-5 lh-1 mt-1">App Store</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <span class="text-white-50 fs-6" data-i18n="footer_copyright">&copy; <?php echo date('Y'); ?> Đại Học Bách Khoa Hà Nội</span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // =============================================
        // LANGUAGE SWITCHER - Full Translation System
        // =============================================
        (function() {
            // Translation dictionary serialized from server-side PHP source of truth
            const translations = <?php echo json_encode(get_all_translations(), JSON_UNESCAPED_UNICODE); ?>;

            let currentLang = localStorage.getItem('app_lang') || 'vi';
            
            // Sync cookie on load if missing
            if (!document.cookie.includes('app_lang=')) {
                document.cookie = "app_lang=" + currentLang + ";path=/;max-age=31536000";
            }

            // Apply translations to page
            function applyTranslations(lang) {
                const dict = translations[lang];
                if (!dict) return;

                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (dict[key] !== undefined) {
                        el.textContent = dict[key];
                    }
                });

                // Handle placeholders
                document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                    const key = el.getAttribute('data-i18n-placeholder');
                    if (dict[key] !== undefined) {
                        el.placeholder = dict[key];
                    }
                });

                // Update html lang attribute
                document.documentElement.lang = lang === 'vi' ? 'vi' : 'en';
            }

            // Language switcher UI logic
            function initLangSwitcher() {
                const switcher = document.getElementById('langSwitcher');
                const toggleBtn = document.getElementById('langToggleBtn');
                const dropdown = document.getElementById('langDropdown');
                const labelEl = document.getElementById('langLabel');
                const options = document.querySelectorAll('.lang-option');

                if (!switcher || !toggleBtn) return;

                // Set initial state
                labelEl.textContent = currentLang.toUpperCase();
                options.forEach(opt => {
                    opt.classList.toggle('active', opt.dataset.lang === currentLang);
                });

                // Toggle dropdown
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    switcher.classList.toggle('open');
                });

                // Select language
                options.forEach(opt => {
                    opt.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const lang = opt.dataset.lang;
                        if (lang === currentLang) {
                            switcher.classList.remove('open');
                            return;
                        }

                        currentLang = lang;
                        localStorage.setItem('app_lang', lang);
                        document.cookie = "app_lang=" + lang + ";path=/;max-age=31536000";

                        // Update UI
                        labelEl.textContent = lang.toUpperCase();
                        options.forEach(o => o.classList.remove('active'));
                        opt.classList.add('active');

                        // Pulse animation
                        toggleBtn.classList.add('pulse');
                        setTimeout(() => toggleBtn.classList.remove('pulse'), 600);

                        // Apply translations instantly
                        applyTranslations(lang);

                        // Close dropdown
                        switcher.classList.remove('open');

                        // Small delay to let the animation complete before page reload
                        setTimeout(() => {
                            location.reload();
                        }, 150);
                    });
                });

                // Close on outside click
                document.addEventListener('click', (e) => {
                    if (!switcher.contains(e.target)) {
                        switcher.classList.remove('open');
                    }
                });

                // Close on Escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        switcher.classList.remove('open');
                    }
                });
            }

            // Initialize on DOM ready
            document.addEventListener('DOMContentLoaded', () => {
                initLangSwitcher();
                // Apply saved language on load
                if (currentLang !== 'vi') {
                    applyTranslations(currentLang);
                }
            });
        })();

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
