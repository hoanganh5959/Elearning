<?php
session_start();
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/../classes/Payment/PaymentProcessor.php");
require_once(__DIR__ . "/../classes/CourseManager.php");

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('../public/login.php');
    exit();
}

// Khởi tạo các đối tượng
$paymentProcessor = new PaymentProcessor();
$courseManager = new CourseManager();

// Lấy thông tin từ request
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : '';
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Kiểm tra dữ liệu
if (empty($orderId) || $amount <= 0 || $courseId <= 0) {
    setFlashMessage('Dữ liệu thanh toán không hợp lệ', 'error');
    redirect('../public/courses.php');
    exit();
}

// Lấy thông tin khóa học
$course = $courseManager->getCourseById($courseId);
if (!$course) {
    setFlashMessage('Khóa học không tồn tại', 'error');
    redirect('../public/courses.php');
    exit();
}

// Lấy thông tin người dùng
$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'];

// Cấu hình thanh toán VNPAY
$vnp_TxnRef = $orderId; // Mã đơn hàng
$vnp_OrderInfo = "Thanh toan khoa hoc: " . $course['title']; // Nội dung thanh toán
$vnp_OrderType = "other"; // Loại hình thanh toán
$vnp_Amount = $amount * 100; // Số tiền * 100 (VNĐ)
$vnp_Locale = 'vn'; // Ngôn ngữ
$vnp_BankCode = ''; // Mã ngân hàng
$vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // IP khách hàng

// Tạo dữ liệu gửi đến VNPay
$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $expire
);

// Thêm mã ngân hàng nếu có chọn
if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

// Sắp xếp dữ liệu theo thứ tự a-z
ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";

foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

// Tạo URL thanh toán
$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

// Chuyển hướng đến cổng thanh toán VNPAY
header('Location: ' . $vnp_Url);
exit(); 