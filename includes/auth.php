<?php
require_once __DIR__ . '/../config.php';

/**
 * Kiểm tra người dùng đã đăng nhập hay chưa
 *
 * @return bool True nếu đã đăng nhập, ngược lại là False
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user']) && isset($_SESSION['phanquyen']);
}

/**
 * Kiểm tra người dùng đã đăng nhập bằng Google hay không
 * 
 * @return bool True nếu đăng nhập bằng Google, ngược lại là False
 */
function isGoogleLogin()
{
    return isset($_SESSION['login_with']) && $_SESSION['login_with'] === 'google';
}

/**
 * Xác thực quyền truy cập trang, nếu không hợp lệ sẽ chuyển hướng về trang đăng nhập
 *
 * @param array $allowedRoles Mảng các role được phép truy cập
 * @return void
 */
function requireLogin($allowedRoles = [])
{
    if (!isLoggedIn()) {
        header('Location: ' . getSiteUrl('public/index.php'));
        exit();
    }

    // Kiểm tra role nếu được chỉ định
    if (!empty($allowedRoles) && !in_array($_SESSION['phanquyen'], $allowedRoles)) {
        header('Location: ' . getSiteUrl('public/404.php'));
        exit();
    }
}

/**
 * Tạo CSRF token và lưu vào session
 *
 * @return string CSRF token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Xác thực CSRF token
 *
 * @param string $token Token từ form gửi lên
 * @return bool True nếu hợp lệ, ngược lại là False
 */
function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
