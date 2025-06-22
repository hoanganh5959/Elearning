<?php
require_once __DIR__ . '/../config.php';

/**
 * Làm sạch dữ liệu đầu vào để tránh XSS
 *
 * @param string $data Dữ liệu cần làm sạch
 * @return string Dữ liệu đã được làm sạch
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Chuyển hướng đến URL cụ thể
 *
 * @param string $url Đường dẫn cần chuyển hướng tới
 * @return void
 */
function redirect($url)
{
    header("Location: $url");
    exit();
}

/**
 * Hiển thị thông báo flash
 *
 * @param string $message Nội dung thông báo
 * @param string $type Loại thông báo (success, error, warning, info)
 * @return void
 */
function setFlashMessage($message, $type = 'info')
{
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['message' => $message, 'type' => $type];
}

/**
 * Lấy và xóa tất cả thông báo flash
 *
 * @return array Mảng các thông báo flash
 */
function getFlashMessages()
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Hiển thị HTML cho thông báo flash
 *
 * @return string HTML của các thông báo
 */
function displayFlashMessages()
{
    $html = '';
    $messageTypes = ['success_message', 'error_message', 'info_message'];
    
    foreach ($messageTypes as $type) {
        if (isset($_SESSION[$type])) {
            $alertClass = str_replace('_message', '', $type);
            if ($alertClass === 'error') $alertClass = 'danger';
            
            $html .= '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show">';
            $html .= htmlspecialchars($_SESSION[$type]);
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            $html .= '</div>';
            
            unset($_SESSION[$type]);
        }
    }
    
    return $html;
}

/**
 * Kiểm tra có phải là request AJAX hay không
 *
 * @return bool True nếu là AJAX request
 */
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
