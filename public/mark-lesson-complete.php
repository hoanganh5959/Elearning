<?php
require_once '../classes/CourseManager.php';
session_start();

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Trả về JSON báo lỗi nếu không đăng nhập
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
    ]);
    exit();
}

// Kiểm tra dữ liệu POST
if (!isset($_POST['lesson_id']) || !is_numeric($_POST['lesson_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin bài học'
    ]);
    exit();
}

// Lấy thông tin
$userId = $_SESSION['user_id'];
$lessonId = (int)$_POST['lesson_id'];
$isComplete = isset($_POST['completed']) ? (int)$_POST['completed'] : 1; // Mặc định là đánh dấu hoàn thành

$courseManager = new CourseManager();

// Cập nhật trạng thái hoàn thành
$result = $courseManager->markLessonComplete($userId, $lessonId, $isComplete);

// Trả về kết quả
if ($result) {
    echo json_encode([
        'success' => true,
        'message' => $isComplete ? 'Đánh dấu bài học đã hoàn thành' : 'Đánh dấu bài học chưa hoàn thành',
        'is_complete' => $isComplete
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi khi cập nhật trạng thái bài học'
    ]);
}
?> 