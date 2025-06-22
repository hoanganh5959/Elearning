<?php
require_once '../classes/CourseManager.php';
session_start();

// Kiểm tra id khóa học
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: courses.php');
    exit();
}

$courseId = (int)$_GET['id'];
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$courseManager = new CourseManager();

// Thêm phương thức mới vào CourseManager để lấy chi tiết khóa học
class_exists('CourseManager') && method_exists($courseManager, 'getCourseById')
    or exit('Cần thêm phương thức getCourseById vào CourseManager');

// Lấy thông tin khóa học
$course = $courseManager->getCourseById($courseId);

if (!$course) {
    header('Location: courses.php');
    exit();
}

// Lấy danh sách bài học của khóa học
class_exists('CourseManager') && method_exists($courseManager, 'getLessonsByCourse')
    or exit('Cần thêm phương thức getLessonsByCourse vào CourseManager');

$lessons = $courseManager->getLessonsByCourse($courseId);

// Kiểm tra xem người dùng đã đăng ký khóa học chưa
$isEnrolled = $courseManager->isUserEnrolled($userId, $courseId);

// Nếu người dùng đã đăng ký, lấy trạng thái hoàn thành của các bài học
$lessonCompletionStatus = [];
if ($isEnrolled && $userId) {
    $lessonCompletionStatus = $courseManager->getLessonCompletionStatus($userId, $courseId);
}

// Xử lý đăng ký khóa học
if (isset($_POST['enroll']) && $userId) {
    // Kiểm tra nếu khóa học có giá tiền > 0 thì chuyển đến trang thanh toán
    if ($course['price'] > 0) {
        header("Location: payment.php?course_id=".$courseId);
        exit();
    } else {
        // Đăng ký trực tiếp cho khóa học miễn phí
        $enrollResult = $courseManager->enrollUserToCourse($userId, $courseId);
        if ($enrollResult) {
            $isEnrolled = true;
            $successMessage = 'Đăng ký khóa học thành công!';
        } else {
            $errorMessage = 'Có lỗi xảy ra khi đăng ký khóa học. Vui lòng thử lại sau.';
        }
    }
}

// Kiểm tra lại userId một lần nữa để đảm bảo
$isUserLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($course['title']); ?> - eLEARNING</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- Thêm style riêng cho course-detail -->
    <style>
        .lesson-content {
            margin-top: 15px;
        }

        .course-thumbnail {
            max-height: 500px;
            object-fit: cover;
        }

        .accordion-button {
            font-weight: 500;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(0, 152, 121, 0.05);
            color: #009879;
        }

        .locked-content {
            background-color: rgba(0, 0, 0, 0.05);
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
        }

        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


    <!-- Navbar Start -->
    <?php include 'includes/header.php'; ?>
    <!-- Navbar End -->


    <!-- Header Start -->
    <div class="container-fluid bg-primary py-5 mb-5 page-header">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="display-3 text-white animated slideInDown"><?php echo htmlspecialchars($course['title']); ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a class="text-white" href="courses.php">Courses</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->


    <!-- Course Detail Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <img class="img-fluid w-100 rounded mb-5" src="<?php echo $course['thumbnail'] ?: 'assets/img/course-1.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <h1 class="mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>

                        <?php if ($course['description']): ?>
                            <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        <?php else: ?>
                            <p>Không có mô tả chi tiết cho khóa học này.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Course Lessons -->
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="m-0">Danh sách bài học</h3>
                            <?php if (!$isEnrolled && $userId): ?>
                                <form method="post" action="">
                                    <button type="submit" name="enroll" class="btn btn-primary">
                                        <?php echo ($course['price'] > 0) ? 'Thanh toán và đăng ký' : 'Đăng ký khóa học này'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if (count($lessons) > 0): ?>
                            <div class="accordion" id="lessonAccordion">
                                <?php foreach ($lessons as $index => $lesson): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?php echo $index; ?>"
                                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                                aria-controls="collapse<?php echo $index; ?>">
                                                <span class="me-2">
                                                    <?php if ($index === 0 || $isEnrolled): ?>
                                                        <?php
                                                        $isLessonCompleted = $isEnrolled && $userId &&
                                                            isset($lessonCompletionStatus[$lesson['id']]) &&
                                                            $lessonCompletionStatus[$lesson['id']]['completed'];
                                                        ?>
                                                        <?php if ($isLessonCompleted): ?>
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        <?php else: ?>
                                                            <i class="fa fa-play-circle text-primary"></i>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <i class="fa fa-lock text-secondary"></i>
                                                    <?php endif; ?>
                                                </span>
                                                <?php echo htmlspecialchars($lesson['title']); ?>
                                                <?php if ($index === 0 && !$isEnrolled): ?>
                                                    <span class="badge bg-success ms-2">Miễn phí</span>
                                                <?php endif; ?>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                            aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#lessonAccordion">
                                            <div class="accordion-body">
                                                <?php if ($index === 0 || $isEnrolled): ?>
                                                    <?php if ($lesson['youtube_id']): ?>
                                                        <div class="ratio ratio-16x9 mb-3">
                                                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($lesson['youtube_id']); ?>"
                                                                title="<?php echo htmlspecialchars($lesson['title']); ?>"
                                                                frameborder="0"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                allowfullscreen></iframe>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($lesson['content']): ?>
                                                        <div class="lesson-content">
                                                            <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($isEnrolled && $userId): ?>
                                                        <div class="mt-3 d-flex justify-content-end">
                                                            <?php
                                                            $isCompleted = isset($lessonCompletionStatus[$lesson['id']]) &&
                                                                $lessonCompletionStatus[$lesson['id']]['completed'];
                                                            ?>
                                                            <?php if ($isCompleted): ?>
                                                                <div class="text-success">
                                                                    <i class="fas fa-check-circle me-1"></i> Đã hoàn thành
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="text-muted">
                                                                    <i class="far fa-circle me-1"></i> Chưa hoàn thành
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="locked-content">
                                                        <i class="fa fa-lock fa-3x text-secondary mb-3"></i>
                                                        <h5>Nội dung này chỉ dành cho học viên đã đăng ký</h5>
                                                        <?php if (!$userId): ?>
                                                            <p class="mb-3">Vui lòng đăng nhập và đăng ký khóa học để xem nội dung này.</p>
                                                            <a href="login.php" class="btn btn-outline-primary me-2">Đăng nhập</a>
                                                        <?php else: ?>
                                                            <p class="mb-3">Vui lòng đăng ký khóa học để xem nội dung này.</p>
                                                            <form method="post" action="" class="d-inline">
                                                                <button type="submit" name="enroll" class="btn btn-primary">
                                                                    <?php if ($course['price'] > 0): ?>
                                                                        <i class="fas fa-lock-open me-1"></i> Mở khóa với <?php echo number_format($course['price'], 0, ',', '.'); ?>đ
                                                                    <?php else: ?>
                                                                        Đăng ký ngay
                                                                    <?php endif; ?>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Khóa học này chưa có bài học nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Course Summary Start -->
                    <div class="bg-light rounded p-4 mb-4">
                        <h4 class="mb-3">Thông tin khóa học</h4>
                        <div class="d-flex mb-2">
                            <i class="fas fa-calendar-alt text-primary me-2 mt-1"></i>
                            <span>Ngày tạo: <?php echo date('d/m/Y', strtotime($course['created_at'])); ?></span>
                        </div>
                        <div class="d-flex mb-2">
                            <i class="fas fa-video text-primary me-2 mt-1"></i>
                            <span><?php echo $course['lesson_count']; ?> bài học</span>
                        </div>
                        <div class="d-flex mb-2">
                            <i class="fas fa-user-tie text-primary me-2 mt-1"></i>
                            <span>Giảng viên: <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                        </div>
                        <div class="d-flex mb-2">
                            <i class="fas fa-users text-primary me-2 mt-1"></i>
                            <span><?php echo $course['student_count']; ?> học viên</span>
                        </div>
                        <div class="d-flex mb-2">
                            <i class="fas fa-tag text-primary me-2 mt-1"></i>
                            <span>Danh mục:
                                <?php
                                $categoryNames = array_map(function ($cat) {
                                    return $cat['name'];
                                }, $course['categories']);
                                echo implode(', ', $categoryNames);
                                ?>
                            </span>
                        </div>
                        <div class="d-flex mb-4">
                            <i class="fas fa-money-bill text-primary me-2 mt-1"></i>
                            <span>Giá:
                                <?php if ($course['price'] > 0): ?>
                                    <span class="fw-bold text-danger"><?php echo number_format($course['price'], 0, ',', '.'); ?>đ</span>
                                    <div class="mt-2 small">
                                        <i class="fas fa-check-circle text-success me-1"></i> Học trọn đời<br>
                                        <i class="fas fa-check-circle text-success me-1"></i> Thanh toán một lần<br>
                                        <i class="fas fa-check-circle text-success me-1"></i> Hỗ trợ 24/7<br>
                                    </div>
                                <?php else: ?>
                                    <span class="text-primary fw-bold">Miễn phí</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if (!$isEnrolled): ?>
                            <?php if ($userId): ?>
                                <!-- Người dùng đã đăng nhập nhưng chưa đăng ký -->
                                <form method="post" action="" class="w-100">
                                    <button type="submit" name="enroll" class="btn btn-primary py-2 px-4 w-100 mb-3">
                                        <?php if ($course['price'] > 0): ?>
                                            <i class="fas fa-shopping-cart me-2"></i> Thanh toán <?php echo number_format($course['price'], 0, ',', '.'); ?>đ
                                        <?php else: ?>
                                            Đăng ký miễn phí
                                        <?php endif; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Người dùng chưa đăng nhập -->
                                <div class="w-100">
                                    <a href="javascript:void(0)" onclick="showLogin()" class="btn btn-primary py-2 px-4 w-100 mb-3">Đăng nhập để đăng ký</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Người dùng đã đăng ký khóa học -->
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i> Bạn đã đăng ký khóa học này
                            </div>
                            <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-success py-2 px-4 w-100 mb-3">
                                <i class="fas fa-play-circle me-2"></i> Học ngay
                            </a>
                        <?php endif; ?>

                        <div class="w-100 text-center">
                            <a href="courses.php" class="text-primary">Xem các khóa học khác</a>
                        </div>
                    </div>
                    <!-- Course Summary End -->

                    <!-- Categories Start -->
                    <div class="bg-light rounded p-4">
                        <h4 class="mb-3">Danh mục khóa học</h4>
                        <div class="d-flex flex-column bg-white mb-n4">
                            <?php
                            $allCategories = $courseManager->getAllCategories();
                            foreach ($allCategories as $cat):
                            ?>
                                <a href="courses.php?category=<?php echo $cat['id']; ?>" class="h5 d-flex justify-content-between bg-white px-4 py-2 mb-2">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span><?php echo $cat['course_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Categories End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Course Detail End -->


    <!-- Footer Start -->
    <?php include 'includes/footer.php'; ?>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/lib/wow/wow.min.js"></script>
    <script src="assets/lib/easing/easing.min.js"></script>
    <script src="assets/lib/waypoints/waypoints.min.js"></script>
    <script src="assets/lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>

    <?php if ($isEnrolled && $userId): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Hiển thị thông báo nhỏ - chức năng vẫn cần thiết để sử dụng khi cần
                function showToast(message) {
                    // Tạo toast nếu chưa có
                    if (!document.getElementById('toast-container')) {
                        const toastContainer = document.createElement('div');
                        toastContainer.id = 'toast-container';
                        toastContainer.style.position = 'fixed';
                        toastContainer.style.bottom = '20px';
                        toastContainer.style.right = '20px';
                        toastContainer.style.zIndex = '1050';
                        document.body.appendChild(toastContainer);
                    }

                    const toastId = 'toast-' + Date.now();
                    const toast = document.createElement('div');
                    toast.id = toastId;
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `
                    <div class="toast-header">
                        <strong class="me-auto text-primary">eLEARNING</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                `;

                    document.getElementById('toast-container').appendChild(toast);

                    // Hiển thị toast
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.show();

                    // Tự động xóa sau khi ẩn đi
                    toast.addEventListener('hidden.bs.toast', function() {
                        toast.remove();
                    });
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>