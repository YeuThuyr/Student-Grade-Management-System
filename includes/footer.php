<?php
// includes/footer.php
$feedbackUnreadCount = 0;
$feedbackNotifications = [];
$showFeedbackNotifications = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

if ($showFeedbackNotifications) {
    try {
        if (!isset($pdo)) {
            require_once __DIR__ . '/../config/database.php';
        }

        $feedbackUnreadCount = (int) $pdo->query('SELECT COUNT(*) FROM feedback_messages WHERE is_read = 0')->fetchColumn();
        $feedbackStmt = $pdo->query(
            'SELECT id, sender_name, sender_email, subject, created_at
             FROM feedback_messages
             WHERE is_read = 0
             ORDER BY created_at DESC
             LIMIT 20'
        );
        $feedbackNotifications = $feedbackStmt->fetchAll();
    } catch (Throwable $e) {
        $showFeedbackNotifications = false;
    }
}
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

    <?php if ($showFeedbackNotifications): ?>
        <div class="feedback-notification-widget" id="feedbackNotificationWidget">
            <div class="feedback-notification-panel" id="feedbackNotificationPanel" aria-hidden="true">
                <div class="feedback-notification-header">
                    <div>
                        <div class="fw-bold text-dark">Feedback</div>
                        <small class="text-muted"><?php echo $feedbackUnreadCount; ?> unread message<?php echo $feedbackUnreadCount === 1 ? '' : 's'; ?></small>
                    </div>
                </div>

                <div class="feedback-notification-list">
                    <?php if (empty($feedbackNotifications)): ?>
                        <div class="feedback-notification-empty">No unread feedback.</div>
                    <?php else: ?>
                        <?php foreach ($feedbackNotifications as $feedback): ?>
                            <a class="feedback-notification-item"
                               href="<?php echo BASE_PATH; ?>feedback/view.php?id=<?php echo (int) $feedback['id']; ?>">
                                <div class="feedback-notification-subject">
                                    <?php echo htmlspecialchars($feedback['subject'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="feedback-notification-meta">
                                    <?php echo htmlspecialchars($feedback['sender_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    &middot;
                                    <?php echo htmlspecialchars($feedback['sender_email'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="feedback-notification-time">
                                    <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($feedback['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <button class="feedback-notification-button"
                    id="feedbackNotificationButton"
                    type="button"
                    aria-label="Feedback notifications"
                    aria-expanded="false">
                <i class="fas fa-bell"></i>
                <?php if ($feedbackUnreadCount > 0): ?>
                    <span class="feedback-notification-badge">
                        <?php echo $feedbackUnreadCount > 99 ? '99+' : $feedbackUnreadCount; ?>
                    </span>
                <?php endif; ?>
            </button>
        </div>
    <?php endif; ?>

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

        (function() {
            const widget = document.getElementById('feedbackNotificationWidget');
            const button = document.getElementById('feedbackNotificationButton');
            const panel = document.getElementById('feedbackNotificationPanel');

            if (!widget || !button || !panel) return;

            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = widget.classList.toggle('open');
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            });

            document.addEventListener('click', (event) => {
                if (!widget.contains(event.target)) {
                    widget.classList.remove('open');
                    button.setAttribute('aria-expanded', 'false');
                    panel.setAttribute('aria-hidden', 'true');
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    widget.classList.remove('open');
                    button.setAttribute('aria-expanded', 'false');
                    panel.setAttribute('aria-hidden', 'true');
                }
            });
        })();
    </script>

    <style>
        .feedback-notification-widget {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1100;
        }

        .feedback-notification-button {
            position: relative;
            width: 56px;
            height: 56px;
            border: none;
            border-radius: 50%;
            background: var(--hust-red, #dc3545);
            color: #fff;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .feedback-notification-button:hover {
            background: #b02a37;
        }

        .feedback-notification-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            min-width: 24px;
            height: 24px;
            padding: 0 6px;
            border-radius: 999px;
            background: #212529;
            color: #fff;
            border: 2px solid #fff;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 20px;
        }

        .feedback-notification-panel {
            position: absolute;
            right: 0;
            bottom: 68px;
            width: min(360px, calc(100vw - 48px));
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
        }

        .feedback-notification-widget.open .feedback-notification-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .feedback-notification-header {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f3f5;
            background: #f8f9fa;
        }

        .feedback-notification-list {
            max-height: 360px;
            overflow-y: auto;
        }

        .feedback-notification-item {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            border-bottom: 1px solid #f1f3f5;
            background: #fff;
        }

        .feedback-notification-item:hover {
            background: #fff5f5;
        }

        .feedback-notification-subject {
            color: #212529;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .feedback-notification-meta,
        .feedback-notification-time,
        .feedback-notification-empty {
            color: #6c757d;
            font-size: 0.82rem;
        }

        .feedback-notification-meta {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 2px;
        }

        .feedback-notification-time {
            margin-top: 4px;
        }

        .feedback-notification-empty {
            padding: 24px 16px;
            text-align: center;
        }
    </style>
</body>
</html>
