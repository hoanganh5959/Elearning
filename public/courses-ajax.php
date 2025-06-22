<?php
// Thiết lập header JSON
header('Content-Type: application/json');

// Bắt đầu session
session_start();

// Nhập các lớp cần thiết
require_once '../classes/CourseManager.php';
$courseManager = new CourseManager();

// Lấy thông tin người dùng đăng nhập (nếu có)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Lấy category_id từ tham số truy vấn
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

try {
    if ($categoryId) {
        // Lấy danh sách khóa học theo danh mục
        $courses = $courseManager->getCoursesByCategory($categoryId);
        $category = $courseManager->getCategoryById($categoryId);
        
        // Thêm thông tin về tình trạng đăng ký của người dùng
        if ($userId) {
            foreach ($courses as &$course) {
                $course['is_enrolled'] = $courseManager->isUserEnrolled($userId, $course['id']);
            }
        }

        // Trả về dữ liệu dưới dạng JSON
        echo json_encode([
            'success' => true,
            'courses' => $courses,
            'category' => $category,
            'is_logged_in' => !empty($userId)
        ]);
    } else {
        // Nếu không có category_id, trả về tất cả khóa học
        $courses = $courseManager->getAllCourses();
        
        // Thêm thông tin về tình trạng đăng ký của người dùng
        if ($userId) {
            foreach ($courses as &$course) {
                $course['is_enrolled'] = $courseManager->isUserEnrolled($userId, $course['id']);
            }
        }

        echo json_encode([
            'success' => true,
            'courses' => $courses,
            'is_logged_in' => !empty($userId)
        ]);
    }
} catch (Exception $e) {
    // Xử lý lỗi và trả về thông báo lỗi
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
