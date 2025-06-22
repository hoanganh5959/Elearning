<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../personal-infor.php');
    exit();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Vui lòng đăng nhập';
    header('Location: ../index.php?show_login=1');
    exit();
}

$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'personal-infor.php';

// Xử lý cập nhật thông tin cá nhân
if (isset($_POST['update_personal'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $user_id = $_SESSION['user_id'];

    if (empty($name)) {
        $_SESSION['error_message'] = 'Tên không được để trống';
        header('Location: ../' . $redirect_to);
        exit();
    }

    require_once __DIR__ . '/../../public/includes/db.php';
    
    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
    $stmt->bind_param("si", $name, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['name'] = $name; // Cập nhật session
        $_SESSION['success_message'] = 'Cập nhật thông tin thành công';
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật thông tin';
    }
    
    header('Location: ../' . $redirect_to);
    exit();
}

// Xử lý đổi mật khẩu
if (isset($_POST['change_password'])) {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $user_id = $_SESSION['user_id'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = 'Vui lòng điền đầy đủ thông tin mật khẩu';
        header('Location: ../' . $redirect_to . '?tab=password');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = 'Mật khẩu mới và xác nhận mật khẩu không khớp';
        header('Location: ../' . $redirect_to . '?tab=password');
        exit();
    }

    require_once __DIR__ . '/../../classes/Auth.php';
    
    $auth = new Auth();
    $result = $auth->changePassword($user_id, $current_password, $new_password);
    
    if ($result === true) {
        $_SESSION['pass'] = $new_password; // Cập nhật session
        $_SESSION['success_message'] = 'Đổi mật khẩu thành công';
    } else {
        $_SESSION['error_message'] = $result;
    }
    
    header('Location: ../' . $redirect_to . '?tab=password');
    exit();
}

// Nếu không có action nào được xử lý
$_SESSION['error_message'] = 'Yêu cầu không hợp lệ';
header('Location: ../' . $redirect_to);
exit();
?> 