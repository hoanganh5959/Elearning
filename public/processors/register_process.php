<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit();
}

// Lấy thông tin từ form
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'register.php';

// Validate input
if (empty($username) || empty($email) || empty($name) || empty($password)) {
    $_SESSION['register_error'] = 'Vui lòng điền đầy đủ thông tin';
    header('Location: ../' . $redirect_to);
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['register_error'] = 'Mật khẩu xác nhận không khớp';
    header('Location: ../' . $redirect_to);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Email không hợp lệ';
    header('Location: ../' . $redirect_to);
    exit();
}

// Xử lý đăng ký
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();
$result = $auth->registerUser($username, $email, $name, $password);

if ($result === true) {
    $_SESSION['register_success'] = 'Đăng ký thành công! Vui lòng đăng nhập';
    header('Location: ../index.php?show_login=1');
} else {
    $_SESSION['register_error'] = $result; // $result chứa thông báo lỗi
    header('Location: ../' . $redirect_to);
}
exit();
?> 