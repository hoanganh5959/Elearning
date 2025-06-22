<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot-password.php');
    exit();
}

require_once __DIR__ . '/../../classes/Auth.php';
$auth = new Auth();

$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'forgot-password.php';
$step = isset($_POST['step']) ? $_POST['step'] : 'email';

// Bước 1: Gửi email reset
if (isset($_POST['request_reset'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $_SESSION['error_message'] = 'Vui lòng nhập email của bạn';
        header('Location: ../' . $redirect_to);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Email không hợp lệ';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Kiểm tra email có tồn tại không
    $user = $auth->checkEmailExists($email);
    if (!$user) {
        $_SESSION['error_message'] = 'Email này chưa được đăng ký trong hệ thống';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Tạo mã xác nhận và gửi email
    $token = $auth->createResetToken($user['user_id']);
    $success = $auth->sendResetPasswordEmail($email, $token, $user['username']);

    if ($success) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_user_id'] = $user['user_id'];
        $_SESSION['success_message'] = 'Mã xác nhận đã được gửi đến email của bạn';
        header('Location: ../forgot-password.php?step=verify');
    } else {
        $_SESSION['error_message'] = 'Không thể gửi email. Vui lòng thử lại sau';
        header('Location: ../' . $redirect_to);
    }
    exit();
}

// Bước 2: Xác thực mã
if (isset($_POST['verify_code'])) {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $userId = $_SESSION['reset_user_id'] ?? 0;

    if (empty($token)) {
        $_SESSION['error_message'] = 'Vui lòng nhập mã xác nhận';
        header('Location: ../forgot-password.php?step=verify');
        exit();
    }

    if ($userId <= 0) {
        $_SESSION['error_message'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại';
        header('Location: ../forgot-password.php');
        exit();
    }

    if ($auth->verifyResetToken($userId, $token)) {
        $_SESSION['verified_token'] = $token;
        $_SESSION['success_message'] = 'Mã xác nhận hợp lệ. Vui lòng nhập mật khẩu mới';
        header('Location: ../forgot-password.php?step=reset');
    } else {
        $_SESSION['error_message'] = 'Mã xác nhận không đúng hoặc đã hết hạn';
        header('Location: ../forgot-password.php?step=verify');
    }
    exit();
}

// Bước 3: Đặt lại mật khẩu
if (isset($_POST['reset_password'])) {
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $userId = $_SESSION['reset_user_id'] ?? 0;
    $token = $_SESSION['verified_token'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = 'Vui lòng nhập đầy đủ mật khẩu';
        header('Location: ../forgot-password.php?step=reset');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = 'Mật khẩu xác nhận không khớp';
        header('Location: ../forgot-password.php?step=reset');
        exit();
    }

    if (strlen($new_password) < 6) {
        $_SESSION['error_message'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        header('Location: ../forgot-password.php?step=reset');
        exit();
    }

    if ($userId <= 0 || empty($token)) {
        $_SESSION['error_message'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại';
        header('Location: ../forgot-password.php');
        exit();
    }

    // Xác thực lại token trước khi reset
    if (!$auth->verifyResetToken($userId, $token)) {
        $_SESSION['error_message'] = 'Mã xác nhận đã hết hạn. Vui lòng thử lại';
        header('Location: ../forgot-password.php');
        exit();
    }

    if ($auth->resetPassword($userId, $new_password)) {
        // Xóa các session tạm thời
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['verified_token']);
        
        $_SESSION['success_message'] = 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập';
        header('Location: ../index.php?show_login=1');
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi đặt lại mật khẩu';
        header('Location: ../forgot-password.php?step=reset');
    }
    exit();
}

// Nếu không có action nào được xử lý
$_SESSION['error_message'] = 'Yêu cầu không hợp lệ';
header('Location: ../' . $redirect_to);
exit();
?> 