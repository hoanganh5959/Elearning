<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../classes/LessonManager.php';
require_once __DIR__ . '/../../classes/CourseManager.php';

// Kiểm tra quyền truy cập
requireLogin(['instructor', 'admin']);


$instructor_id = $_SESSION['user_id'];
$lessonManager = new LessonManager();
$courseManager = new CourseManager();

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Lấy chỉ các khóa học của giảng viên hiện tại
$instructor_courses = $courseManager->getCoursesByInstructor($instructor_id);

// Kiểm tra xem course_id có thuộc về giảng viên này không (nếu có course_id)
if ($course_id > 0) {
    $valid_course = false;
    foreach ($instructor_courses as $course) {
        if ($course['id'] == $course_id) {
            $valid_course = true;
            break;
        }
    }
    if (!$valid_course) {
        $_SESSION['error_message'] = "Bạn không có quyền truy cập khóa học này.";
        header("Location: manage_courses.php");
        exit;
    }
}

$lessons = $course_id ? json_decode($lessonManager->getLessonsByCourseId($course_id), true) : [];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>eLEARNING - Quản Lý Bài Học</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="../assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f7f6;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .container-custom {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .lesson-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .lesson-table th,
        .lesson-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .lesson-table th {
            background-color: #007bff;
            color: white;
        }

        .lesson-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .lesson-table tr:hover {
            background-color: #f0f0f0;
        }

        .action-buttons .btn {
            margin-right: 5px;
        }

        .alert-dismissible {
            margin-bottom: 20px;
        }

        .add-lesson-btn {
            margin-bottom: 20px;
        }

        .course-selector {
            margin-bottom: 20px;
        }

        .page-title {
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .back-btn {
            margin-bottom: 20px;
        }

        @media (max-width: 767px) {

            .lesson-table th,
            .lesson-table td {
                font-size: 0.9rem;
                padding: 8px;
            }

            .action-buttons .btn {
                font-size: 0.8rem;
                padding: 5px 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container container-custom">
            <!-- Back to Courses Button -->
            <div class="back-btn">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại trang chủ
                </a>
            </div>

            <h1 class="page-title">Quản Lý Bài Học</h1>

            <!-- Alert for Feedback -->
            <div id="alertMessage" class="alert alert-dismissible fade show d-none" role="alert">
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Course Selector -->
            <div class="course-selector">
                <label for="courseSelect" class="form-label">Chọn khóa học của bạn:</label>
                <select id="courseSelect" class="form-select" onchange="loadLessons()">
                    <option value="0" <?php echo $course_id == 0 ? 'selected' : ''; ?>>-- Chọn khóa học --</option>
                    <?php foreach ($instructor_courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $course_id == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Add Lesson Button -->
            <div class="add-lesson-btn" <?php echo $course_id == 0 ? 'style="display:none;"' : ''; ?>>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLessonModal">
                    <i class="fas fa-plus me-2"></i>Thêm Bài Học
                </button>
            </div>

            <!-- Lessons Table -->
            <table class="lesson-table table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tiêu đề</th>
                        <th>YouTube ID</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="lessonTableBody">
                    <?php if ($course_id && !empty($lessons)): ?>
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <tr data-lesson-id="<?php echo $lesson['id']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                <td><?php echo htmlspecialchars($lesson['youtube_id']); ?></td>
                                <td class="action-buttons">
                                    <button class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $lesson['id']; ?>, '<?php echo htmlspecialchars(addslashes($lesson['title'])); ?>', '<?php echo htmlspecialchars($lesson['youtube_id']); ?>')">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteLesson(<?php echo $lesson['id']; ?>)">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                <?php if (empty($instructor_courses)): ?>
                                    Bạn chưa có khóa học nào. <a href="add_course.php">Tạo khóa học mới</a>
                                <?php else: ?>
                                    Vui lòng chọn khóa học để xem bài học.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Lesson Modal -->
        <div class="modal fade" id="addLessonModal" tabindex="-1" aria-labelledby="addLessonModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLessonModalLabel">Thêm Bài Học Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addLessonForm">
                            <div class="mb-3">
                                <label for="lessonTitle" class="form-label">Tiêu đề bài học</label>
                                <input type="text" class="form-control" id="lessonTitle" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="youtubeId" class="form-label">YouTube ID</label>
                                <input type="text" class="form-control" id="youtubeId" name="youtube_id" placeholder="Ví dụ: dQw4w9WgXcQ" required>
                                <div class="form-text">Lấy ID từ URL YouTube. Ví dụ: https://www.youtube.com/watch?v=<strong>dQw4w9WgXcQ</strong></div>
                            </div>
                            <input type="hidden" id="addLessonCourseId" name="course_id" value="<?php echo $course_id; ?>">
                            <input type="hidden" name="action" value="add">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" onclick="addLesson()">Lưu</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Lesson Modal -->
        <div class="modal fade" id="editLessonModal" tabindex="-1" aria-labelledby="editLessonModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editLessonModalLabel">Sửa Bài Học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editLessonForm">
                            <div class="mb-3">
                                <label for="editLessonTitle" class="form-label">Tiêu đề bài học</label>
                                <input type="text" class="form-control" id="editLessonTitle" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="editYoutubeId" class="form-label">YouTube ID</label>
                                <input type="text" class="form-control" id="editYoutubeId" name="youtube_id" required>
                                <div class="form-text">Lấy ID từ URL YouTube. Ví dụ: https://www.youtube.com/watch?v=<strong>dQw4w9WgXcQ</strong></div>
                            </div>
                            <input type="hidden" id="editLessonId" name="id">
                            <input type="hidden" name="action" value="update">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" onclick="updateLesson()">Lưu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        function showAlert(message, isSuccess) {
            const alertMessage = document.getElementById("alertMessage");
            const alertText = document.getElementById("alertText");
            alertMessage.classList.remove("d-none", "alert-success", "alert-danger");
            alertMessage.classList.add(isSuccess ? "alert-success" : "alert-danger");
            alertText.textContent = message;
        }

        function loadLessons() {
            const courseId = document.getElementById("courseSelect").value;
            const addLessonBtn = document.querySelector(".add-lesson-btn");
            const addLessonCourseId = document.getElementById("addLessonCourseId");

            if (courseId == 0) {
                document.getElementById("lessonTableBody").innerHTML = '<tr><td colspan="4" class="text-center">Vui lòng chọn khóa học để xem bài học.</td></tr>';
                addLessonBtn.style.display = "none";
                return;
            }

            addLessonBtn.style.display = "block";
            addLessonCourseId.value = courseId;

            // Reload page with new course_id
            window.location.href = `manage_lessons.php?course_id=${courseId}`;
        }

        function addLesson() {
            const form = document.getElementById("addLessonForm");
            
            // Tạo form mới để submit
            const submitForm = document.createElement('form');
            submitForm.method = 'POST';
            submitForm.action = '../processors/lesson_process.php';
            
            // Copy tất cả fields từ modal form
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                submitForm.appendChild(input);
            }
            
            // Thêm action cho processor
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'add_lesson';
            submitForm.appendChild(actionInput);
            
            // Thêm redirect_to
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to';
            redirectInput.value = 'instructor/manage_lessons.php?course_id=' + document.getElementById("addLessonCourseId").value;
            submitForm.appendChild(redirectInput);
            
            // Map youtube_id field to video_url cho processor
            const videoUrlInput = document.createElement('input');
            videoUrlInput.type = 'hidden';
            videoUrlInput.name = 'video_url';
            videoUrlInput.value = document.getElementById("youtubeId").value;
            submitForm.appendChild(videoUrlInput);
            
            document.body.appendChild(submitForm);
            submitForm.submit();
        }

        function openEditModal(id, title, youtube_id) {
            document.getElementById("editLessonId").value = id;
            document.getElementById("editLessonTitle").value = title;
            document.getElementById("editYoutubeId").value = youtube_id;
            const modal = new bootstrap.Modal(document.getElementById("editLessonModal"));
            modal.show();
        }

        function updateLesson() {
            const form = document.getElementById("editLessonForm");
            
            // Tạo form mới để submit
            const submitForm = document.createElement('form');
            submitForm.method = 'POST';
            submitForm.action = '../processors/lesson_process.php';
            
            // Copy tất cả fields từ modal form
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                submitForm.appendChild(input);
            }
            
            // Thêm action cho processor
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update_lesson';
            submitForm.appendChild(actionInput);
            
            // Map id field to lesson_id cho processor
            const lessonIdInput = document.createElement('input');
            lessonIdInput.type = 'hidden';
            lessonIdInput.name = 'lesson_id';
            lessonIdInput.value = document.getElementById("editLessonId").value;
            submitForm.appendChild(lessonIdInput);
            
            // Map youtube_id field to video_url cho processor
            const videoUrlInput = document.createElement('input');
            videoUrlInput.type = 'hidden';
            videoUrlInput.name = 'video_url';
            videoUrlInput.value = document.getElementById("editYoutubeId").value;
            submitForm.appendChild(videoUrlInput);
            
            // Thêm redirect_to
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to';
            redirectInput.value = 'instructor/manage_lessons.php?course_id=' + new URLSearchParams(window.location.search).get('course_id');
            submitForm.appendChild(redirectInput);
            
            document.body.appendChild(submitForm);
            submitForm.submit();
        }

        function deleteLesson(id) {
            if (confirm("Bạn có chắc muốn xóa bài học này?")) {
                // Tạo form để submit delete
                const submitForm = document.createElement('form');
                submitForm.method = 'POST';
                submitForm.action = '../processors/lesson_process.php';
                
                // Thêm action
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_lesson';
                submitForm.appendChild(actionInput);
                
                // Thêm lesson_id
                const lessonIdInput = document.createElement('input');
                lessonIdInput.type = 'hidden';
                lessonIdInput.name = 'lesson_id';
                lessonIdInput.value = id;
                submitForm.appendChild(lessonIdInput);
                
                // Thêm redirect_to
                const redirectInput = document.createElement('input');
                redirectInput.type = 'hidden';
                redirectInput.name = 'redirect_to';
                redirectInput.value = 'instructor/manage_lessons.php?course_id=' + new URLSearchParams(window.location.search).get('course_id');
                submitForm.appendChild(redirectInput);
                
                document.body.appendChild(submitForm);
                submitForm.submit();
            }
        }

        // Hiển thị flash messages nếu có
        <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success_message'])): ?>
            showAlert('<?php echo addslashes($_SESSION['success_message']); ?>', true);
            <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
            showAlert('<?php echo addslashes($_SESSION['error_message']); ?>', false);
            <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });
        <?php endif; ?>
    </script>
</body>

</html>