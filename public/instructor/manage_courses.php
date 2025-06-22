<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php'; // Đường dẫn đến tệp bootstrap chung
require_once __DIR__ . '/../../classes/CourseManager.php';
// Kiểm tra xem người dùng đã đăng nhập và có vai trò là giảng viên chưa

requireLogin(['instructor', 'admin']);


$instructor_id = $_SESSION['user_id'];
$courseManager = new CourseManager();

// Xử lý yêu cầu xóa khóa học
$delete_message = '';
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['course_id'])) {
    $course_id_to_delete = (int)$_GET['course_id'];
    // Thêm kiểm tra xem khóa học có thuộc về giảng viên này không trước khi xóa (trong CourseManager::deleteCourse đã có)
    $delete_result = $courseManager->deleteCourse($course_id_to_delete, $instructor_id);
    
    if ($delete_result === true) {
        $delete_message = '<div class="alert alert-success">Khóa học đã được xóa thành công.</div>';
    } else if (is_string($delete_result)) {
        // Nếu trả về string, đó là thông báo lỗi cụ thể
        $delete_message = '<div class="alert alert-warning">' . htmlspecialchars($delete_result) . '</div>';
    } else {
        $delete_message = '<div class="alert alert-danger">Lỗi: Không thể xóa khóa học hoặc bạn không có quyền.</div>';
    }
}

$courses = $courseManager->getCoursesByInstructor($instructor_id);
$allCategories = $courseManager->getAllCategories(); // Lấy tất cả danh mục để hiển thị

// Giả sử bạn có một layout chung (header, footer)
// Nếu không, bạn cần tự thêm HTML, CSS cơ bản
// Ví dụ:
// include __DIR__ . '/../includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khóa học của Tôi</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Giả sử bạn có file css chung -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            padding-top: 20px;
        }

        .container-custom {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table img.thumbnail {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .btn-action {
            margin-right: 5px;
        }

        .page-title {
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .add-course-btn {
            margin-bottom: 20px;
        }

        .back-btn {
            text-align: left;
        }

        .back-btn .btn {
            border-radius: 6px;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="container container-custom">
        <h1 class="page-title">Khóa học của Tôi</h1> <!-- Back to Dashboard Button -->
        <div class="back-btn mb-3"> <a href="index.php" class="btn btn-secondary"> <i class="fas fa-arrow-left mr-2"></i>Quay lại trang chủ </a> </div> <?php if ($delete_message) echo $delete_message; ?> <a href="add_course.php" class="btn btn-primary add-course-btn mb-3"> <i class="fas fa-plus"></i> Thêm Khóa học Mới </a>



        <?php if (empty($courses)): ?>
            <div class="alert alert-info">Bạn chưa có khóa học nào. Hãy <a href="add_course.php">thêm khóa học mới</a>!</div>
        <?php else: ?>
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Thumbnail</th>
                        <th>Tiêu đề</th>
                        <th>Giá (VNĐ)</th>
                        <th>Danh mục</th>
                        <th>Ngày tạo</th>
                        <th>Bài học</th>
                        <th>Học viên</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td>
                                <?php if (!empty($course['thumbnail'])): ?>
                                    <img src="../<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="thumbnail img-fluid">
                                <?php else: ?>
                                    <img src="../assets/img/default-course.png" alt="Default thumbnail" class="thumbnail img-fluid"> <!-- Hoặc ảnh mặc định -->
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo number_format($course['price'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $category_names = [];
                                if (!empty($course['categories'])) {
                                    foreach ($course['categories'] as $category) {
                                        $category_names[] = htmlspecialchars($category['name']);
                                    }
                                }
                                echo implode(', ', $category_names);
                                ?>
                            </td>
                            <td><?php echo date("d/m/Y H:i", strtotime($course['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($course['lesson_count']); ?></td>
                            <td><?php echo htmlspecialchars($course['student_count']); ?></td>
                            <td>
                                <a href="edit_course.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-info btn-action" title="Sửa">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="manage_lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-success btn-action" title="Quản lý bài học">
                                    <i class="fas fa-list-ul"></i> Bài học
                                </a>
                                <?php 
                                $enrollment_count = $courseManager->getEnrollmentCount($course['id']); 
                                if ($enrollment_count > 0): 
                                ?>
                                    <button class="btn btn-sm btn-secondary btn-action" 
                                            title="Không thể xóa vì có <?php echo $enrollment_count; ?> học viên đã đăng ký"
                                            disabled>
                                        <i class="fas fa-lock"></i> Có <?php echo $enrollment_count; ?> HV
                                    </button>
                                <?php else: ?>
                                    <a href="manage_courses.php?action=delete&course_id=<?php echo $course['id']; ?>"
                                        class="btn btn-sm btn-danger btn-action"
                                        title="Xóa"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa khóa học này không? Hành động này không thể hoàn tác.');">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>

    <?php
    // Ví dụ:
    // include __DIR__ . '/../includes/footer.php'; 
    ?>
</body>

</html>