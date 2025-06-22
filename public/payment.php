<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/CourseManager.php';
require_once __DIR__ . '/../classes/Payment/PaymentProcessor.php';

// Kiểm tra đăng nhập
requireLogin();

// Khởi tạo các đối tượng
$courseManager = new CourseManager();
$paymentProcessor = new PaymentProcessor();

// Lấy thông tin khóa học từ tham số URL
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$course = $courseManager->getCourseById($courseId);

// Kiểm tra khóa học tồn tại
if (!$course) {
    setFlashMessage('Khóa học không tồn tại!', 'error');
    redirect('courses.php');
    exit();
}

// Kiểm tra người dùng đã đăng ký khóa học này chưa
if ($courseManager->isUserEnrolled($_SESSION['user_id'], $courseId)) {
    setFlashMessage('Bạn đã đăng ký khóa học này rồi!', 'info');
    redirect('my-courses.php');
    exit();
}

// Xử lý form khi submit
if (isset($_POST['payment_method'])) {
    $paymentMethod = $_POST['payment_method'];
    
    // Tạo đơn hàng
    $orderId = $paymentProcessor->createOrder($_SESSION['user_id'], $courseId, $course['price']);
    
    if ($orderId === 'FREE') {
        // Nếu là khóa học miễn phí
        setFlashMessage('Đăng ký khóa học miễn phí thành công!', 'success');
        redirect('my-courses.php');
        exit();
    } elseif ($orderId) {
        if ($paymentMethod === 'vnpay') {
            // Lưu URL để chuyển hướng sau khi thanh toán với đường dẫn đầy đủ
            $_SESSION['payment_redirect'] = '../public/my-courses.php';
            
            // Chuyển hướng đến trang xử lý thanh toán VNPAY
            header('Location: ../vnpay_php/vnpay_create_payment_elearning.php?order_id=' . $orderId . '&amount=' . $course['price'] . '&course_id=' . $courseId);
            exit();
        } elseif ($paymentMethod === 'cash') {
            // Thanh toán trực tiếp - chỉ để trạng thái pending và đợi admin xác nhận
            setFlashMessage('Đơn hàng của bạn đã được tạo. Vui lòng thanh toán trực tiếp để được kích hoạt khóa học!', 'info');
            redirect('my-courses.php');
            exit();
        }
    } else {
        setFlashMessage('Có lỗi xảy ra khi tạo đơn hàng!', 'error');
    }
}

// Thiết lập tiêu đề trang
$pageTitle = "Thanh toán khóa học";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #6c757d;
        }
        .payment-method.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-method img {
            height: 40px;
            margin-right: 10px;
        }
        .course-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="payment-container">
            <h1 class="mb-4">Thanh toán khóa học</h1>
            
            <?= displayFlashMessages() ?>
            
            <div class="course-info">
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?= $course['thumbnail'] ?>" alt="<?= $course['title'] ?>" class="img-fluid rounded">
                    </div>
                    <div class="col-md-9">
                        <h4><?= $course['title'] ?></h4>
                        <p class="text-muted">Giảng viên: <?= $course['instructor_name'] ?></p>
                        <p class="fw-bold">Giá: <?= $course['price'] > 0 ? number_format($course['price'], 0, ',', '.') . ' VNĐ' : 'Miễn phí' ?></p>
                    </div>
                </div>
            </div>
            
            <?php if ($course['price'] > 0): ?>
                <h4>Chọn phương thức thanh toán</h4>
                <form method="post" id="payment-form">
                    <div class="payment-method selected" data-method="vnpay">
                        <div class="d-flex align-items-center">
                            <img src="../vnpay_php/assets/vnpay-logo.png" alt="VNPAY">
                            <div>
                                <strong>Thanh toán qua VNPAY</strong>
                                <p class="mb-0 text-muted">Thanh toán trực tuyến qua ATM, QR Code, Thẻ quốc tế</p>
                            </div>
                            <div class="ms-auto">
                                <input type="radio" name="payment_method" value="vnpay" checked class="form-check-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-method" data-method="cash">
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-primary" style="font-size: 30px;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <strong>Thanh toán trực tiếp</strong>
                                <p class="mb-0 text-muted">Thanh toán trực tiếp tại văn phòng</p>
                            </div>
                            <div class="ms-auto">
                                <input type="radio" name="payment_method" value="cash" class="form-check-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Tiến hành thanh toán</button>
                        <a href="course-detail.php?id=<?= $courseId ?>" class="btn btn-outline-secondary">Quay lại</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="payment_method" value="free">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">Đăng ký khóa học miễn phí</button>
                        <a href="course-detail.php?id=<?= $courseId ?>" class="btn btn-outline-secondary">Quay lại</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý chọn phương thức thanh toán
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Bỏ chọn tất cả
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                    m.querySelector('input[type="radio"]').checked = false;
                });
                
                // Chọn phương thức hiện tại
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html> 