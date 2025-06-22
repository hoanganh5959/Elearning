<?php
// Khởi động session nếu chưa được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config và các file cần thiết
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

// Thiết lập báo lỗi dựa trên môi trường
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Thiết lập timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');