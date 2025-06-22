<?php
require_once '../classes/CourseManager.php';
session_start();

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$courseManager = new CourseManager();

// Lấy danh sách khóa học đã đăng ký
$enrolledCourses = $courseManager->getEnrolledCourses($userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Khóa học của tôi - eLEARNING</title>
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

    <style>
        .progress {
            height: 10px;
            margin-top: 5px;
        }

        .course-card {
            transition: transform 0.3s;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .empty-state {
            text-align: center;
            padding: 50px 0;
        }

        .empty-state i {
            font-size: 5rem;
            color: #ccc;
            margin-bottom: 20px;
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
                    <h1 class="display-3 text-white animated slideInDown">Khóa học của tôi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Khóa học của tôi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- My Courses Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Học tập</h6>
                <h1 class="mb-5">Khóa học đã đăng ký</h1>
            </div>

            <?php if (count($enrolledCourses) > 0): ?>
                <div class="row g-4">
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="course-item bg-light h-100 course-card">
                                <div class="position-relative overflow-hidden">
                                    <img class="img-fluid" src="<?php echo $course['thumbnail'] ?: 'assets/img/course-1.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                </div>
                                <div class="text-center p-4 pb-0">
                                    <h5 class="mb-3"><?php echo htmlspecialchars($course['title']); ?></h5>

                                    <?php
                                    // Tính phần trăm hoàn thành
                                    $totalLessons = $course['lesson_count'];
                                    $completedLessons = $course['completed_lessons'];
                                    $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                                    ?>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <small>Tiến độ</small>
                                            <small><?php echo $progressPercent; ?>%</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progressPercent; ?>%"
                                                aria-valuenow="<?php echo $progressPercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <p class="mb-4">
                                        <small>
                                            <i class="fa fa-calendar-alt text-primary me-2"></i>
                                            Đăng ký: <?php echo date('d/m/Y', strtotime($course['enrolled_at'])); ?>
                                        </small>
                                    </p>

                                    <?php if ($course['description']): ?>
                                        <p class="text-truncate mb-3"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex border-top">
                                    <small class="flex-fill text-center border-end py-2">
                                        <i class="fa fa-user-tie text-primary me-2"></i><?php echo htmlspecialchars($course['instructor_name']); ?>
                                    </small>
                                    <small class="flex-fill text-center border-end py-2">
                                        <i class="fa fa-clock text-primary me-2"></i><?php echo $completedLessons . '/' . $totalLessons; ?> bài học
                                    </small>
                                    <small class="flex-fill text-center py-2">
                                        <i class="fa fa-user text-primary me-2"></i><?php echo $course['student_count']; ?> học viên
                                    </small>
                                </div>
                                <div class="p-4 pt-0">
                                    <a class="btn btn-primary w-100 py-2" href="lessons.php?course_id=<?php echo $course['course_id']; ?>">
                                        Tiếp tục học
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state wow fadeInUp" data-wow-delay="0.1s">
                    <i class="fa fa-book-open"></i>
                    <h3>Bạn chưa đăng ký khóa học nào</h3>
                    <p class="text-muted">Hãy khám phá các khóa học hấp dẫn của chúng tôi!</p>
                    <a href="courses.php" class="btn btn-primary mt-3">Xem khóa học</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- My Courses End -->

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
</body>

</html>