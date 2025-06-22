<?php
session_start();
require_once '../classes/CourseManager.php';
require_once '../includes/helpers.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Tạo URL để redirect về sau khi đăng nhập
    $current_url = basename($_SERVER['PHP_SELF']);
    if (isset($_GET['course_id'])) {
        $current_url = 'course-detail.php?id=' . $_GET['course_id'];
    }
    
    // Lưu thông báo để hiển thị popup login
    $_SESSION['login_error'] = 'Vui lòng đăng nhập để đăng ký khóa học';
    
    // Redirect về trang course-detail hoặc courses với thông báo
    if (isset($_GET['course_id'])) {
        header('Location: course-detail.php?id=' . $_GET['course_id'] . '&show_login=1');
    } else {
        header('Location: courses.php?show_login=1');
    }
    exit();
}

// Kiểm tra id khóa học
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: courses.php');
    exit();
}

$courseId = (int)$_GET['course_id'];
$userId = $_SESSION['user_id'];
$courseManager = new CourseManager();

// Kiểm tra khóa học có tồn tại không
$course = $courseManager->getCourseById($courseId);
if (!$course) {
    $_SESSION['error_message'] = 'Khóa học không tồn tại';
    header('Location: courses.php');
    exit();
}

// Kiểm tra người dùng đã đăng ký khóa học chưa
if ($courseManager->isUserEnrolled($userId, $courseId)) {
    $_SESSION['info_message'] = 'Bạn đã đăng ký khóa học này rồi';
    header('Location: course-detail.php?id=' . $courseId);
    exit();
}

// Kiểm tra xem khóa học có phí hay không
if ((float)$course['price'] > 0) {
    // Nếu khóa học có phí, chuyển hướng đến trang thanh toán
    header('Location: payment.php?course_id=' . $courseId);
    exit();
} else {
    // Nếu khóa học miễn phí, đăng ký trực tiếp
    $result = $courseManager->enrollUserToCourse($userId, $courseId);

    if ($result) {
        $_SESSION['success_message'] = 'Đăng ký khóa học thành công!';
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi đăng ký khóa học. Vui lòng thử lại sau.';
    }

    // Chuyển hướng về trang chi tiết khóa học
    header('Location: course-detail.php?id=' . $courseId);
    exit();
}
?> 