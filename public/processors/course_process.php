<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../instructor/manage_courses.php');
    exit();
}

// Kiểm tra đăng nhập và quyền instructor
if (!isset($_SESSION['user_id']) || $_SESSION['phanquyen'] !== 'instructor') {
    $_SESSION['login_error'] = 'Bạn không có quyền truy cập';
    header('Location: ../index.php?show_login=1');
    exit();
}

require_once __DIR__ . '/../../classes/CourseManager.php';
$courseManager = new CourseManager();
$instructor_id = $_SESSION['user_id'];

$action = isset($_POST['action']) ? $_POST['action'] : '';
$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'instructor/manage_courses.php';

// Xử lý thêm khóa học mới
if ($action === 'add_course') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];

    // Validate input
    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = 'Tiêu đề và mô tả không được để trống';
        header('Location: ../' . $redirect_to);
        exit();
    }

    if (empty($categories)) {
        $_SESSION['error_message'] = 'Vui lòng chọn ít nhất một danh mục';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Xử lý upload thumbnail
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/courses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadPath)) {
            $thumbnail = 'uploads/courses/' . $fileName;
        }
    }

    $data = [
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'instructor_id' => $instructor_id,
        'thumbnail' => $thumbnail,
        'category_ids' => $categories
    ];

    $result = $courseManager->addCourse($data);
    
    if ($result) {
        $_SESSION['success_message'] = 'Thêm khóa học thành công';
        header('Location: ../instructor/manage_courses.php');
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi thêm khóa học';
        header('Location: ../' . $redirect_to);
    }
    exit();
}

// Xử lý cập nhật khóa học
if ($action === 'update_course') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];

    if ($course_id <= 0) {
        $_SESSION['error_message'] = 'ID khóa học không hợp lệ';
        header('Location: ../' . $redirect_to);
        exit();
    }

    // Validate input
    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = 'Tiêu đề và mô tả không được để trống';
        header('Location: ../instructor/edit_course.php?course_id=' . $course_id);
        exit();
    }

    if (empty($categories)) {
        $_SESSION['error_message'] = 'Vui lòng chọn ít nhất một danh mục';
        header('Location: ../instructor/edit_course.php?course_id=' . $course_id);
        exit();
    }

    // Xử lý upload thumbnail mới (nếu có)
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/courses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadPath)) {
            $thumbnail = 'uploads/courses/' . $fileName;
        }
    }

    $data = [
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'category_ids' => $categories,
        'instructor_id' => $instructor_id
    ];

    if ($thumbnail) {
        $data['thumbnail'] = $thumbnail;
    }

    $result = $courseManager->updateCourse($course_id, $data);
    
    if ($result) {
        $_SESSION['success_message'] = 'Cập nhật khóa học thành công';
        header('Location: ../instructor/manage_courses.php');
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật khóa học';
        header('Location: ../instructor/edit_course.php?course_id=' . $course_id);
    }
    exit();
}

// Nếu không có action nào được xử lý
$_SESSION['error_message'] = 'Yêu cầu không hợp lệ';
header('Location: ../' . $redirect_to);
exit();
?> 