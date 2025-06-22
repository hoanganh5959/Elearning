<?php
// Bắt đầu session
session_start();

// Kết nối database và các file cần thiết
require_once(__DIR__ . "/config.php"); // Import config.php từ thư mục vnpay_php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../classes/Payment/PaymentProcessor.php");

// Khởi tạo PaymentProcessor
$paymentProcessor = new PaymentProcessor();

// Xử lý thông tin thanh toán từ VNPAY
$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$orderId = $_GET['vnp_TxnRef'] ?? '';

// Cập nhật trạng thái giao dịch
if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        // Thanh toán thành công
        $paymentProcessor->updateOrderStatus($orderId, 'success', $_GET);
        $_SESSION['success_message'] = 'Thanh toán thành công! Bạn đã được đăng ký vào khóa học.';
    } else {
        // Thanh toán thất bại
        $paymentProcessor->updateOrderStatus($orderId, 'failed', $_GET);
        $_SESSION['error_message'] = 'Thanh toán không thành công. Mã lỗi: ' . $_GET['vnp_ResponseCode'];
    }
} else {
    // Chữ ký không hợp lệ
    $paymentProcessor->updateOrderStatus($orderId, 'error', $_GET);
    $_SESSION['error_message'] = 'Chữ ký không hợp lệ!';
}

// Lấy thông tin thanh toán để hiển thị
$payment = $paymentProcessor->getOrderById($orderId);

// Lấy URL chuyển hướng từ session (nếu có)
$redirectUrl = isset($_SESSION['payment_redirect']) ? $_SESSION['payment_redirect'] : '../public/my-courses.php';
// Đảm bảo URL là đường dẫn đầy đủ và đúng
if (strpos($redirectUrl, 'http') !== 0 && substr($redirectUrl, 0, 2) != '..') {
    // Nếu là đường dẫn tương đối không bắt đầu bằng ../
    $redirectUrl = '../public/' . ltrim($redirectUrl, '/');
}
unset($_SESSION['payment_redirect']); // Xóa URL chuyển hướng

// Tùy chỉnh layout
$pageTitle = "Kết Quả Thanh Toán";
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 40px;
        }

        .payment-result {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .success-icon {
            color: #28a745;
            font-size: 60px;
            margin-bottom: 20px;
        }

        .error-icon {
            color: #dc3545;
            font-size: 60px;
            margin-bottom: 20px;
        }

        .payment-details {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="payment-result">
            <div class="text-center mb-4">
                <?php if ($_GET['vnp_ResponseCode'] == '00' && $secureHash == $vnp_SecureHash): ?>
                    <div class="success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                        </svg>
                    </div>
                    <h2>Thanh toán thành công!</h2>
                    <p class="text-muted">Bạn đã được đăng ký vào khóa học.</p>
                <?php else: ?>
                    <div class="error-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
                        </svg>
                    </div>
                    <h2>Thanh toán không thành công!</h2>
                    <p class="text-muted">Mã lỗi: <?= $_GET['vnp_ResponseCode'] ?></p>
                <?php endif; ?>
            </div>

            <div class="payment-details">
                <h4>Chi tiết thanh toán</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Mã đơn hàng:</th>
                                <td><?= $_GET['vnp_TxnRef'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Khóa học:</th>
                                <td><?= $payment['course_title'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Số tiền:</th>
                                <td><?= number_format(($_GET['vnp_Amount'] ?? 0) / 100, 0, ',', '.') ?> VNĐ</td>
                            </tr>
                            <tr>
                                <th>Nội dung thanh toán:</th>
                                <td><?= $_GET['vnp_OrderInfo'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Mã GD tại VNPAY:</th>
                                <td><?= $_GET['vnp_TransactionNo'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Mã ngân hàng:</th>
                                <td><?= $_GET['vnp_BankCode'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Thời gian thanh toán:</th>
                                <td>
                                    <?php
                                    if (!empty($_GET['vnp_PayDate'])) {
                                        $payDate = $_GET['vnp_PayDate'];
                                        echo substr($payDate, 6, 2) . '/' . substr($payDate, 4, 2) . '/' . substr($payDate, 0, 4) . ' ' .
                                            substr($payDate, 8, 2) . ':' . substr($payDate, 10, 2) . ':' . substr($payDate, 12, 2);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?= $redirectUrl ?>" class="btn btn-primary">Quay lại khóa học của tôi</a>
                <a href="../public/index.php" class="btn btn-outline-secondary ms-2">Về trang chủ</a>
                <!-- Thêm nút dự phòng nếu có vấn đề với URL chuyển hướng -->
                <?php if (strpos($redirectUrl, 'my-courses') === false): ?>
                <a href="../public/my-courses.php" class="btn btn-outline-primary ms-2">Khóa học của tôi</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tự động chuyển hướng sau 10 giây
        setTimeout(function() {
            // Đảm bảo URL chuyển hướng hợp lệ
            let redirectTo = '<?= $redirectUrl ?>';
            if (!redirectTo || redirectTo.indexOf('undefined') !== -1) {
                // Nếu URL không hợp lệ, sử dụng URL mặc định
                redirectTo = '../public/my-courses.php';
            }
            window.location.href = redirectTo;
        }, 10000);
    </script>
</body>

</html>