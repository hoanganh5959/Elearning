<?php
require_once '../classes/LessonManager.php';
session_start();
$lessonManager = new LessonManager();
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$courses = json_decode($lessonManager->getAllCourses(), true);
$lessons = $course_id ? json_decode($lessonManager->getLessonsByCourseId($course_id), true) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>eLEARNING - Admin Lessons</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="../public/assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../public/assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="../public/assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../public/assets/css/style.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .main-content {
            flex: 1;
            padding: 20px;
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
    <!-- Spinner Start -->
        <!-- <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div> -->
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <?php include("../public/includes/header.php"); ?>
    <!-- Navbar End -->

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Quản Lý Bài Học</h1>

            <!-- Alert for Feedback -->
            <div id="alertMessage" class="alert alert-dismissible fade show d-none" role="alert">
                <span id="alertText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Course Selector -->
            <div class="course-selector">
                <label for="courseSelect" class="form-label">Chọn khóa học:</label>
                <select id="courseSelect" class="form-select" onchange="loadLessons()">
                    <option value="0" <?php echo $course_id == 0 ? 'selected' : ''; ?>>-- Chọn khóa học --</option>
                    <?php foreach ($courses as $course): ?>
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
            <table class="lesson-table">
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
                            <td colspan="4" class="text-center">Vui lòng chọn khóa học để xem bài học.</td>
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

    <!-- Footer Start -->
    <?php include("../public/includes/footer.php"); ?>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/assets/lib/wow/wow.min.js"></script>
    <script src="../public/assets/lib/easing/easing.min.js"></script>
    <script src="../public/assets/lib/waypoints/waypoints.min.js"></script>
    <script src="../public/assets/lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../public/assets/js/main.js"></script>

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

            fetch(`admin_lessons.php?course_id=${courseId}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");
                    const newTableBody = doc.getElementById("lessonTableBody").innerHTML;
                    document.getElementById("lessonTableBody").innerHTML = newTableBody;
                })
                .catch(error => showAlert("Lỗi khi làm mới bảng: " + error.message, false));
        }

        function addLesson() {
            const form = document.getElementById("addLessonForm");
            const formData = new FormData(form);

            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            fetch("../classes/manage_lesson.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text().then(text => {
                    console.log("Phản hồi thô:", text);
                    return JSON.parse(text);
                }))
                .then(data => {
                    showAlert(data.message, data.success);
                    if (data.success) {
                        loadLessons();
                        const modal = bootstrap.Modal.getInstance(document.getElementById("addLessonModal"));
                        modal.hide();
                        form.reset();
                    }
                })
                .catch(error => showAlert("Lỗi: " + error.message, false));
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
            const formData = new FormData(form);

            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            fetch("../classes/manage_lesson.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text().then(text => {
                    console.log("Phản hồi thô:", text);
                    return JSON.parse(text);
                }))
                .then(data => {
                    showAlert(data.message, data.success);
                    if (data.success) {
                        loadLessons();
                        const modal = bootstrap.Modal.getInstance(document.getElementById("editLessonModal"));
                        modal.hide();
                    }
                })
                .catch(error => showAlert("Lỗi: " + error.message, false));
        }

        function deleteLesson(id) {
            if (confirm("Bạn có chắc muốn xóa bài học này?")) {
                const formData = new FormData();
                formData.append("action", "delete");
                formData.append("id", id);

                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                fetch("../classes/manage_lesson.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text().then(text => {
                        console.log("Phản hồi thô:", text);
                        return JSON.parse(text);
                    }))
                    .then(data => {
                        showAlert(data.message, data.success);
                        if (data.success) {
                            loadLessons();
                        }
                    })
                    .catch(error => showAlert("Lỗi: " + error.message, false));
            }
        }
    </script>
</body>

</html>