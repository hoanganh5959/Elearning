<?php
/* Payment Notify
 * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
 * Các bước thực hiện:
 * Kiểm tra checksum 
 * Tìm giao dịch trong database
 * Kiểm tra số tiền giữa hai hệ thống
 * Kiểm tra tình trạng của giao dịch trước khi cập nhật
 * Cập nhật kết quả vào Database
 * Trả kết quả ghi nhận lại cho VNPAY
 */

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/../classes/Payment/PaymentProcessor.php");

// Khởi tạo PaymentProcessor
$paymentProcessor = new PaymentProcessor();

// Xử lý dữ liệu từ VNPAY
$inputData = array();
$returnData = array();

foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
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
$vnpTranId = $inputData['vnp_TransactionNo'] ?? ''; // Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode'] ?? ''; // Ngân hàng thanh toán
$vnp_Amount = isset($inputData['vnp_Amount']) ? $inputData['vnp_Amount'] / 100 : 0; // Số tiền thanh toán VNPAY phản hồi

$orderId = $inputData['vnp_TxnRef'] ?? '';

try {
    // Kiểm tra checksum của dữ liệu
    if ($secureHash == $vnp_SecureHash) {
        // Lấy thông tin đơn hàng từ database
        $order = $paymentProcessor->getOrderById($orderId);
        
        if ($order) {
            // Kiểm tra số tiền thanh toán
            if (floatval($order["amount"]) == floatval($vnp_Amount)) {
                // Kiểm tra trạng thái đơn hàng
                if ($order["transaction_status"] != 'success') {
                    if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                        // Cập nhật trạng thái thành công
                        $paymentProcessor->updateOrderStatus($orderId, 'success', $inputData);
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Success';
                    } else {
                        // Cập nhật trạng thái thất bại
                        $paymentProcessor->updateOrderStatus($orderId, 'failed', $inputData);
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Failed';
                    }
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            } else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'Invalid amount';
            }
        } else {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }
    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknow error: ' . $e->getMessage();
}

// Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);
