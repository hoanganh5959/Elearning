<?php
// Ngăn lỗi xuất hiện trong phản hồi JSON
error_reporting(0);
ini_set('display_errors', 0);

// Xóa mọi output trước đó
ob_clean();

require_once 'LessonManager.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Phương thức không được hỗ trợ."]);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$lessonManager = new LessonManager();

try {
    switch ($action) {
        case 'add':
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $youtube_id = isset($_POST['youtube_id']) ? trim($_POST['youtube_id']) : '';
            $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
            if (empty($title) || empty($youtube_id) || $course_id <= 0) {
                throw new Exception("Thiếu thông tin bắt buộc.");
            }
            $result = $lessonManager->addLesson($title, $youtube_id, $course_id);
            break;

        case 'update':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $youtube_id = isset($_POST['youtube_id']) ? trim($_POST['youtube_id']) : '';
            if ($id <= 0 || empty($title) || empty($youtube_id)) {
                throw new Exception("Thiếu thông tin bắt buộc.");
            }
            $result = $lessonManager->updateLesson($id, $title, $youtube_id);
            break;

        case 'delete':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                throw new Exception("ID bài học không hợp lệ.");
            }
            $result = $lessonManager->deleteLesson($id);
            break;

        default:
            throw new Exception("Hành động không hợp lệ.");
    }
} catch (Exception $e) {
    $result = ["success" => false, "message" => "Lỗi: " . $e->getMessage()];
}

echo json_encode($result);
exit;
?>