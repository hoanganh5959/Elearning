<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("lesson_process.php called - Method: " . $_SERVER['REQUEST_METHOD']);

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../instructor/manage_lessons.php');
    exit();
}

// Kiểm tra đăng nhập và quyền instructor
if (!isset($_SESSION['user_id']) || $_SESSION['phanquyen'] !== 'instructor') {
    error_log("Session debug - user_id: " . ($_SESSION['user_id'] ?? 'NULL') . ", phanquyen: " . ($_SESSION['phanquyen'] ?? 'NULL'));
    $_SESSION['login_error'] = 'Bạn không có quyền truy cập';
    header('Location: ../index.php?show_login=1');
    exit();
}

require_once __DIR__ . '/../../classes/LessonManager.php';
$lessonManager = new LessonManager();
$instructor_id = $_SESSION['user_id'];

$action = isset($_POST['action']) ? $_POST['action'] : '';
$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'instructor/manage_lessons.php';

// Xử lý thêm bài học mới
if ($action === 'add_lesson') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $video_url = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
    $lesson_order = isset($_POST['lesson_order']) ? intval($_POST['lesson_order']) : 1;

    // Validate input
    if ($course_id <= 0) {
        $_SESSION['error_message'] = 'ID khóa học không hợp lệ';
        header('Location: ../' . $redirect_to);
        exit();
    }

    if (empty($title)) {
        $_SESSION['error_message'] = 'Tiêu đề bài học không được để trống';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Kiểm tra khóa học có thuộc về instructor này không
    if (!$lessonManager->checkCourseOwnership($course_id, $instructor_id)) {
        $_SESSION['error_message'] = 'Bạn không có quyền thêm bài học vào khóa học này';
        header('Location: ../' . $redirect_to);
        exit();
    }

    $data = [
        'course_id' => $course_id,
        'title' => $title,
        'content' => $content,
        'video_url' => $video_url,
        'lesson_order' => $lesson_order
    ];

    $result = $lessonManager->addLesson($data);
    
    if ($result) {
        $_SESSION['success_message'] = 'Thêm bài học thành công';
        header('Location: ../instructor/manage_lessons.php?course_id=' . $course_id);
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi thêm bài học';
        header('Location: ../' . $redirect_to);
    }
    exit();
}

// Xử lý cập nhật bài học
if ($action === 'update_lesson') {
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $video_url = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
    $lesson_order = isset($_POST['lesson_order']) ? intval($_POST['lesson_order']) : 1;

    if ($lesson_id <= 0) {
        $_SESSION['error_message'] = 'ID bài học không hợp lệ';
        header('Location: ../' . $redirect_to);
        exit();
    }

    if (empty($title)) {
        $_SESSION['error_message'] = 'Tiêu đề bài học không được để trống';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Kiểm tra bài học có thuộc về instructor này không
    if (!$lessonManager->checkLessonOwnership($lesson_id, $instructor_id)) {
        $_SESSION['error_message'] = 'Bạn không có quyền chỉnh sửa bài học này';
        header('Location: ../' . $redirect_to);
        exit();
    }

    $data = [
        'title' => $title,
        'content' => $content,
        'video_url' => $video_url,
        'lesson_order' => $lesson_order
    ];

    $result = $lessonManager->updateLesson($lesson_id, $data);
    
    if ($result) {
        $_SESSION['success_message'] = 'Cập nhật bài học thành công';
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật bài học';
    }
    
    header('Location: ../' . $redirect_to);
    exit();
}

// Xử lý xóa bài học
if ($action === 'delete_lesson') {
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;

    if ($lesson_id <= 0) {
        $_SESSION['error_message'] = 'ID bài học không hợp lệ';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Kiểm tra bài học có thuộc về instructor này không
    if (!$lessonManager->checkLessonOwnership($lesson_id, $instructor_id)) {
        $_SESSION['error_message'] = 'Bạn không có quyền xóa bài học này';
        header('Location: ../' . $redirect_to);
        exit();
    }

    $result = $lessonManager->deleteLesson($lesson_id);
    
    if ($result) {
        $_SESSION['success_message'] = 'Xóa bài học thành công';
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi xóa bài học';
    }
    
    header('Location: ../' . $redirect_to);
    exit();
}

// Nếu không có action nào được xử lý
$_SESSION['error_message'] = 'Yêu cầu không hợp lệ';
header('Location: ../' . $redirect_to);
exit();
?> 