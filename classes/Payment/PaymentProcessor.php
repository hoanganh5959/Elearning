<?php
require_once __DIR__ . '/../../public/includes/db.php';

class PaymentProcessor {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Tạo đơn hàng mới trong hệ thống
     * 
     * @param int $userId ID người dùng
     * @param int $courseId ID khóa học
     * @param float $amount Số tiền thanh toán
     * @return string|bool Order ID nếu thành công, false nếu thất bại
     */
    public function createOrder($userId, $courseId, $amount) {
        // Tạo mã đơn hàng theo định dạng: ORD + USER_ID + TIMESTAMP
        $orderId = 'ORD' . $userId . time();
        
        // Kiểm tra xem khóa học có tồn tại không
        $checkCourse = $this->conn->prepare("SELECT id, price FROM courses WHERE id = ?");
        $checkCourse->bind_param("i", $courseId);
        $checkCourse->execute();
        $courseResult = $checkCourse->get_result();
        
        if ($courseResult->num_rows === 0) {
            return false;
        }
        
        $course = $courseResult->fetch_assoc();
        
        // Nếu price của khóa học = 0, thì không cần thanh toán
        if ((float)$course['price'] <= 0) {
            // Tự động đăng ký khóa học miễn phí
            $enrollStmt = $this->conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE enrolled_at = CURRENT_TIMESTAMP");
            $enrollStmt->bind_param("ii", $userId, $courseId);
            $enrollStmt->execute();
            return 'FREE';
        }
        
        // Thêm vào bảng payments
        $stmt = $this->conn->prepare("INSERT INTO payments (order_id, user_id, course_id, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siid", $orderId, $userId, $courseId, $amount);
        
        if ($stmt->execute()) {
            return $orderId;
        } else {
            return false;
        }
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     * 
     * @param string $orderId Mã đơn hàng
     * @param string $status Trạng thái mới
     * @param array $paymentData Dữ liệu thanh toán từ VNPAY
     * @return bool True nếu cập nhật thành công
     */
    public function updateOrderStatus($orderId, $status, $paymentData = []) {
        $stmt = $this->conn->prepare("
            UPDATE payments 
            SET transaction_status = ?,
                vnp_transaction_no = ?,
                bank_code = ?,
                payment_date = ?,
                transaction_date = ?
            WHERE order_id = ?
        ");
        
        $transactionNo = $paymentData['vnp_TransactionNo'] ?? null;
        $bankCode = $paymentData['vnp_BankCode'] ?? null;
        $paymentDate = !empty($paymentData['vnp_PayDate']) ? 
            date('Y-m-d H:i:s', strtotime(substr($paymentData['vnp_PayDate'], 0, 4) . '-' . 
            substr($paymentData['vnp_PayDate'], 4, 2) . '-' . 
            substr($paymentData['vnp_PayDate'], 6, 2) . ' ' . 
            substr($paymentData['vnp_PayDate'], 8, 2) . ':' . 
            substr($paymentData['vnp_PayDate'], 10, 2) . ':' . 
            substr($paymentData['vnp_PayDate'], 12, 2))) : null;
        $transactionDate = date('Y-m-d H:i:s');
        
        $stmt->bind_param("ssssss", $status, $transactionNo, $bankCode, $paymentDate, $transactionDate, $orderId);
        
        if ($stmt->execute()) {
            // Nếu thanh toán thành công, tự động đăng ký khóa học
            if ($status === 'success') {
                $this->enrollCourseAfterPayment($orderId);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Tự động đăng ký khóa học sau khi thanh toán thành công
     * 
     * @param string $orderId Mã đơn hàng
     * @return bool True nếu đăng ký thành công
     */
    private function enrollCourseAfterPayment($orderId) {
        // Lấy thông tin đơn hàng
        $stmt = $this->conn->prepare("SELECT user_id, course_id FROM payments WHERE order_id = ? AND transaction_status = 'success'");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $payment = $result->fetch_assoc();
            
            // Kiểm tra xem đã đăng ký khóa học chưa
            $checkEnroll = $this->conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
            $checkEnroll->bind_param("ii", $payment['user_id'], $payment['course_id']);
            $checkEnroll->execute();
            $enrollResult = $checkEnroll->get_result();
            
            if ($enrollResult->num_rows === 0) {
                // Chưa đăng ký, thêm mới vào bảng enrollments
                $enrollStmt = $this->conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
                $enrollStmt->bind_param("ii", $payment['user_id'], $payment['course_id']);
                return $enrollStmt->execute();
            }
            
            return true; // Đã đăng ký rồi
        }
        
        return false;
    }
    
    /**
     * Lấy thông tin đơn hàng
     * 
     * @param string $orderId Mã đơn hàng
     * @return array|null Thông tin đơn hàng
     */
    public function getOrderById($orderId) {
        $stmt = $this->conn->prepare("
            SELECT p.*, c.title as course_title, u.name as user_name 
            FROM payments p
            JOIN courses c ON p.course_id = c.id
            JOIN users u ON p.user_id = u.user_id
            WHERE p.order_id = ?
        ");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Lấy lịch sử thanh toán của người dùng
     * 
     * @param int $userId ID người dùng
     * @return array Danh sách các giao dịch
     */
    public function getUserPaymentHistory($userId) {
        $stmt = $this->conn->prepare("
            SELECT p.*, c.title as course_title 
            FROM payments p
            JOIN courses c ON p.course_id = c.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = array();
        
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        return $payments;
    }
} 