<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Payment/PaymentProcessor.php';

// Kiểm tra đăng nhập
requireLogin();

// Khởi tạo đối tượng xử lý thanh toán
$paymentProcessor = new PaymentProcessor();

// Lấy lịch sử thanh toán của người dùng
$userId = $_SESSION['user_id'];
$payments = $paymentProcessor->getUserPaymentHistory($userId);

// Thiết lập tiêu đề trang
$pageTitle = "Lịch sử thanh toán";
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
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Menu tài khoản</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="personal-infor.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i> Thông tin cá nhân
                        </a>
                        <a href="my-courses.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-graduation-cap me-2"></i> Khóa học của tôi
                        </a>
                        <a href="payment-history.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-history me-2"></i> Lịch sử thanh toán
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Lịch sử thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <?= displayFlashMessages() ?>
                        
                        <?php if (empty($payments)): ?>
                            <div class="alert alert-info">
                                Bạn chưa có giao dịch thanh toán nào.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn hàng</th>
                                            <th>Khóa học</th>
                                            <th>Số tiền</th>
                                            <th>Phương thức</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày thanh toán</th>
                                            <th>Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?= $payment['order_id'] ?></td>
                                                <td>
                                                    <a href="course-detail.php?id=<?= $payment['course_id'] ?>"><?= $payment['course_title'] ?></a>
                                                </td>
                                                <td><?= number_format($payment['amount'], 0, ',', '.') ?> VNĐ</td>
                                                <td>
                                                    <?php if ($payment['payment_method'] == 'VNPAY'): ?>
                                                        <span class="badge bg-primary">VNPAY</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= $payment['payment_method'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($payment['transaction_status'] == 'success'): ?>
                                                        <span class="badge bg-success">Thành công</span>
                                                    <?php elseif ($payment['transaction_status'] == 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                                    <?php elseif ($payment['transaction_status'] == 'failed'): ?>
                                                        <span class="badge bg-danger">Thất bại</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= $payment['transaction_status'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= !empty($payment['payment_date']) ? date('d/m/Y H:i', strtotime($payment['payment_date'])) : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary payment-detail-btn" 
                                                            data-bs-toggle="modal" data-bs-target="#paymentDetailModal" 
                                                            data-id="<?= $payment['id'] ?>"
                                                            data-order="<?= $payment['order_id'] ?>"
                                                            data-course="<?= $payment['course_title'] ?>"
                                                            data-amount="<?= number_format($payment['amount'], 0, ',', '.') ?>"
                                                            data-status="<?= $payment['transaction_status'] ?>"
                                                            data-method="<?= $payment['payment_method'] ?>"
                                                            data-bank="<?= $payment['bank_code'] ?? 'N/A' ?>"
                                                            data-transaction="<?= $payment['vnp_transaction_no'] ?? 'N/A' ?>"
                                                            data-date="<?= !empty($payment['payment_date']) ? date('d/m/Y H:i:s', strtotime($payment['payment_date'])) : 'N/A' ?>"
                                                            data-created="<?= date('d/m/Y H:i:s', strtotime($payment['created_at'])) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Chi tiết thanh toán -->
    <div class="modal fade" id="paymentDetailModal" tabindex="-1" aria-labelledby="paymentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentDetailModalLabel">Chi tiết thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">Mã đơn hàng:</th>
                                    <td id="modal-order-id"></td>
                                </tr>
                                <tr>
                                    <th>Khóa học:</th>
                                    <td id="modal-course"></td>
                                </tr>
                                <tr>
                                    <th>Số tiền:</th>
                                    <td id="modal-amount"></td>
                                </tr>
                                <tr>
                                    <th>Phương thức thanh toán:</th>
                                    <td id="modal-method"></td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td id="modal-status"></td>
                                </tr>
                                <tr>
                                    <th>Mã giao dịch:</th>
                                    <td id="modal-transaction"></td>
                                </tr>
                                <tr>
                                    <th>Ngân hàng:</th>
                                    <td id="modal-bank"></td>
                                </tr>
                                <tr>
                                    <th>Ngày thanh toán:</th>
                                    <td id="modal-date"></td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo đơn:</th>
                                    <td id="modal-created"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý khi nhấn nút chi tiết
        document.querySelectorAll('.payment-detail-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Lấy dữ liệu từ data attributes
                const orderId = this.getAttribute('data-order');
                const course = this.getAttribute('data-course');
                const amount = this.getAttribute('data-amount');
                const status = this.getAttribute('data-status');
                const method = this.getAttribute('data-method');
                const bank = this.getAttribute('data-bank');
                const transaction = this.getAttribute('data-transaction');
                const date = this.getAttribute('data-date');
                const created = this.getAttribute('data-created');
                
                // Cập nhật modal
                document.getElementById('modal-order-id').textContent = orderId;
                document.getElementById('modal-course').textContent = course;
                document.getElementById('modal-amount').textContent = amount + ' VNĐ';
                document.getElementById('modal-method').textContent = method;
                
                // Hiển thị trạng thái
                let statusHtml = '';
                if (status === 'success') {
                    statusHtml = '<span class="badge bg-success">Thành công</span>';
                } else if (status === 'pending') {
                    statusHtml = '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                } else if (status === 'failed') {
                    statusHtml = '<span class="badge bg-danger">Thất bại</span>';
                } else {
                    statusHtml = '<span class="badge bg-secondary">' + status + '</span>';
                }
                document.getElementById('modal-status').innerHTML = statusHtml;
                
                document.getElementById('modal-transaction').textContent = transaction;
                document.getElementById('modal-bank').textContent = bank;
                document.getElementById('modal-date').textContent = date;
                document.getElementById('modal-created').textContent = created;
            });
        });
    </script>
</body>
</html> 