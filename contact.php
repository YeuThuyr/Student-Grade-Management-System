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
        $errors[] = __('contact_err_name');
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('contact_err_email');
    }

    if ($subject === '') {
        $errors[] = __('contact_err_subject');
    }

    if ($message === '') {
        $errors[] = __('contact_err_message');
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
            $errors[] = __('contact_err_send');
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
                            <h1 class="h3 fw-bold mb-3" data-i18n="contact_title"><?php echo __('contact_title'); ?></h1>
                            <p class="mb-4 opacity-75" data-i18n="contact_desc">
                                <?php echo __('contact_desc'); ?>
                            </p>
                        </div>

                        <div class="col-12 col-md-7 bg-white p-4 p-md-5">
                            <?php if ($success): ?>
                                <div class="alert alert-success" data-i18n="contact_success">
                                    <?php echo __('contact_success'); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger shadow-sm">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?php echo e($error); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="contact.php" class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label fw-semibold" data-i18n="contact_name"><?php echo __('contact_name'); ?></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo e($name); ?>" placeholder="<?php echo e(__('contact_name_placeholder')); ?>" data-i18n-placeholder="contact_name_placeholder" required>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label fw-semibold" data-i18n="contact_email"><?php echo __('contact_email'); ?></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo e($email); ?>" placeholder="<?php echo e(__('contact_email_placeholder')); ?>" data-i18n-placeholder="contact_email_placeholder" required>
                                </div>

                                <div class="col-12">
                                    <label for="subject" class="form-label fw-semibold" data-i18n="contact_subject"><?php echo __('contact_subject'); ?></label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                        value="<?php echo e($subject); ?>" placeholder="<?php echo e(__('contact_subject_placeholder')); ?>" data-i18n-placeholder="contact_subject_placeholder" required>
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label fw-semibold" data-i18n="contact_message"><?php echo __('contact_message'); ?></label>
                                    <textarea class="form-control" id="message" name="message" rows="6"
                                        placeholder="<?php echo e(__('contact_message_placeholder')); ?>" data-i18n-placeholder="contact_message_placeholder" required><?php echo e($message); ?></textarea>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-hust px-4">
                                        <i class="fas fa-paper-plane me-2"></i><span data-i18n="contact_submit"><?php echo __('contact_submit'); ?></span>
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
