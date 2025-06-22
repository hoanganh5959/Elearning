<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}


/**
 * Tạo URL redirect đúng để tránh redirect về trang không tồn tại
 */
function getCorrectRedirectUrl($redirect_to)
{
    // Nếu redirect_to chứa 'processors/' thì loại bỏ nó
    if (strpos($redirect_to, 'processors/') !== false) {
        $redirect_to = str_replace('processors/', '', $redirect_to);
    }

    // Nếu redirect_to không phải là trang hợp lệ, mặc định về index.php
    $valid_pages = ['index.php', 'course.php', 'contact.php', 'about.php', 'blogs.php', 'my-blogs.php', 'write-blog.php', 'test-login-debug.php', 'courses.php', 'course-detail.php', 'blog-detail.php'];
    if (!in_array($redirect_to, $valid_pages)) {
        $redirect_to = 'index.php';
    }

    return '../' . $redirect_to;
}

// Lấy thông tin từ form
$username = isset($_POST['user']) ? trim($_POST['user']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php';

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Vui lòng nhập đầy đủ thông tin đăng nhập';
    // Redirect về trang trước với đúng đường dẫn
    $redirect_url = getCorrectRedirectUrl($redirect_to);
    header('Location: ' . $redirect_url);
    exit();
}

// Xử lý đăng nhập
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();
$success_redirect_url = "http://localhost:8080/elearning_restructured_updated/public/" . $redirect_to;
$result = $auth->myLogin($username, $password, $success_redirect_url);

// Xử lý kết quả đăng nhập
if ($result === -1) {
    // Tài khoản bị khóa
    $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
    $redirect_url = getCorrectRedirectUrl($redirect_to);
    header('Location: ' . $redirect_url);
    exit();
} elseif ($result === 0) {
    // Sai tên đăng nhập hoặc mật khẩu
    $_SESSION['login_error'] = 'Sai tên đăng nhập hoặc mật khẩu';
    $redirect_url = getCorrectRedirectUrl($redirect_to);
    header('Location: ' . $redirect_url);
    exit();
}

// Nếu đến đây có nghĩa là có lỗi gì đó trong Auth::myLogin
$_SESSION['login_error'] = 'Có lỗi xảy ra trong quá trình đăng nhập';
$redirect_url = getCorrectRedirectUrl($redirect_to);
header('Location: ' . $redirect_url);
exit();
