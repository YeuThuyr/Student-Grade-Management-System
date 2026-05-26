<?php
session_start();

define('IS_HOMEPAGE', false);
define('SHOW_APP_SECTION', false);

require_once __DIR__ . '/includes/helpers.php';

$recipientEmail = 'thienstyle2k5@gmail.com';
$errors = [];
$success = false;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $errors[] = 'Vui lòng nhập họ và tên.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vui lòng nhập email hợp lệ.';
    }

    if ($subject === '') {
        $errors[] = 'Vui lòng nhập tiêu đề phản hồi.';
    }

    if ($message === '') {
        $errors[] = 'Vui lòng nhập nội dung phản hồi.';
    }

    if (empty($errors)) {
        $mailSubject = '[Grade Management] ' . $subject;
        $mailBody = "Họ và tên: {$name}\n";
        $mailBody .= "Email: {$email}\n\n";
        $mailBody .= "Nội dung phản hồi:\n{$message}\n";

        $headers = [
            'From: Grade Management <no-reply@localhost>',
            'Reply-To: ' . $email,
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $success = mail($recipientEmail, $mailSubject, $mailBody, implode("\r\n", $headers));

        if ($success) {
            $name = '';
            $email = '';
            $subject = '';
            $message = '';
        } else {
            $errors[] = 'Không thể gửi phản hồi lúc này. Vui lòng thử lại sau hoặc gửi trực tiếp đến ' . $recipientEmail . '.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-grow-1 py-5">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-12 col-md-5 bg-hust text-white p-4 p-md-5">
                            <h1 class="h3 fw-bold mb-3">Liên hệ và phản hồi</h1>
                            <p class="mb-4 opacity-75">
                                Gửi ý kiến, câu hỏi hoặc báo lỗi về hệ thống quản lý điểm.
                            </p>
                        </div>

                        <div class="col-12 col-md-7 bg-white p-4 p-md-5">
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    Phản hồi của bạn đã được gửi thành công.
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?php echo e($error); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="contact.php" class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label fw-semibold">Họ và tên</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo e($name); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo e($email); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label for="subject" class="form-label fw-semibold">Tiêu đề</label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                        value="<?php echo e($subject); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label fw-semibold">Nội dung</label>
                                    <textarea class="form-control" id="message" name="message" rows="6"
                                        required><?php echo e($message); ?></textarea>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-hust px-4">
                                        <i class="fas fa-paper-plane me-2"></i>Gửi phản hồi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
