<?php

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/CourseManager.php';

// Khởi tạo CourseManager để lấy dữ liệu từ database
$courseManager = new CourseManager();

// Lấy tất cả các danh mục
$categories = $courseManager->getAllCategories();

// Lấy các khóa học phổ biến (6 khóa học mới nhất)
$popularCourses = $courseManager->getAllCourses(6);

// Hiển thị thông báo nếu có
if (isset($_SESSION['alert_message'])) {
    $alertMessage = $_SESSION['alert_message'];
    unset($_SESSION['alert_message']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>eLEARNING - Nền tảng Học trực tuyến</title>
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

    <!-- Inline script để ẩn spinner ngay lập tức -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var spinner = document.getElementById('spinner');
            if (spinner) {
                spinner.classList.remove('show');
                spinner.style.display = 'none';
            }
        });

        // Fallback với setTimeout
        setTimeout(function() {
            var spinner = document.getElementById('spinner');
            if (spinner) {
                spinner.classList.remove('show');
                spinner.style.display = 'none';
            }
        }, 100);
    </script>

    <!-- CSS để force hide spinner nếu cần -->
    <style>
        .spinner-force-hide {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Đang tải...</span>
        </div>
    </div>
    <!-- Spinner End -->


    <!-- Navbar Start -->
    <?php include 'includes/header.php'; ?>
    <!-- Navbar End -->


    <!-- Carousel Start -->
    <div class="container-fluid p-0 mb-5">
        <div class="owl-carousel header-carousel position-relative">
            <div class="owl-carousel-item position-relative">
                <img class="img-fluid" src="assets/img/carousel-1.jpg" alt="">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(24, 29, 56, .7);">
                    <div class="container">
                        <div class="row justify-content-start">
                            <div class="col-sm-10 col-lg-8">
                                <h5 class="text-primary text-uppercase mb-3 animated slideInDown">Khóa học trực tuyến tốt nhất</h5>
                                <h1 class="display-3 text-white animated slideInDown">Nền tảng học trực tuyến hàng đầu</h1>
                                <p class="fs-5 text-white mb-4 pb-2">Khám phá hàng nghìn khóa học chất lượng cao với phương pháp giảng dạy hiện đại. Học mọi lúc mọi nơi cùng đội ngũ giảng viên chuyên nghiệp.</p>
                                <a href="" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Tìm hiểu thêm</a>
                                <a href="" class="btn btn-light py-md-3 px-md-5 animated slideInRight">Tham gia ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="owl-carousel-item position-relative">
                <img class="img-fluid" src="assets/img/carousel-2.jpg" alt="">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(24, 29, 56, .7);">
                    <div class="container">
                        <div class="row justify-content-start">
                            <div class="col-sm-10 col-lg-8">
                                <h5 class="text-primary text-uppercase mb-3 animated slideInDown">Khóa học trực tuyến tốt nhất</h5>
                                <h1 class="display-3 text-white animated slideInDown">Học tập trực tuyến từ nhà</h1>
                                <p class="fs-5 text-white mb-4 pb-2">Trải nghiệm học tập linh hoạt với công nghệ tiên tiến. Nâng cao kỹ năng và kiến thức với các khóa học được thiết kế bởi chuyên gia hàng đầu.</p>
                                <a href="" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Tìm hiểu thêm</a>
                                <a href="" class="btn btn-light py-md-3 px-md-5 animated slideInRight">Tham gia ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->


    <!-- Service Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-graduation-cap text-primary mb-4"></i>
                            <h5 class="mb-3">Giảng viên có kinh nghiệm</h5>
                            <p>Đội ngũ giảng viên chuyên nghiệp với nhiều năm kinh nghiệm trong lĩnh vực giảng dạy và thực tiễn</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-globe text-primary mb-4"></i>
                            <h5 class="mb-3">Lớp học trực tuyến</h5>
                            <p>Học mọi lúc mọi nơi với hệ thống lớp học trực tuyến hiện đại và tương tác cao</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-home text-primary mb-4"></i>
                            <h5 class="mb-3">Dự án thực hành</h5>
                            <p>Áp dụng kiến thức vào thực tế thông qua các dự án thực hành và bài tập ứng dụng</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-book-open text-primary mb-4"></i>
                            <h5 class="mb-3">Thư viện sách</h5>
                            <p>Kho tài liệu phong phú với hàng ngàn cuốn sách và tài liệu học tập chất lượng cao</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->


    <!-- About Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                    <div class="position-relative h-100">
                        <img class="img-fluid position-absolute w-100 h-100" src="assets/img/about.jpg" alt="" style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                    <h6 class="section-title bg-white text-start text-primary pe-3">Về Chúng Tôi</h6>
                    <h1 class="mb-4">Chào mừng đến với eLEARNING</h1>
                    <p class="mb-4">eLEARNING là nền tảng học trực tuyến hàng đầu, cung cấp các khóa học chất lượng cao với phương pháp giảng dạy hiện đại và tương tác.</p>
                    <p class="mb-4">Chúng tôi cam kết mang đến trải nghiệm học tập tốt nhất với đội ngũ giảng viên giàu kinh nghiệm, nội dung phong phú và công nghệ tiên tiến để hỗ trợ quá trình học tập của bạn.</p>
                    <div class="row gy-2 gx-4 mb-4">
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Giảng viên chuyên nghiệp</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Lớp học trực tuyến</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Chứng chỉ quốc tế</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Hỗ trợ 24/7</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Học liệu đa dạng</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Cộng đồng học tập</p>
                        </div>
                    </div>
                    <a class="btn btn-primary py-3 px-5 mt-2" href="">Tìm hiểu thêm</a>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->


    <!-- Categories Start -->
    <div class="container-xxl py-5 category">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Danh mục</h6>
                <h1 class="mb-5">Danh mục khóa học</h1>
            </div>
            <div class="row g-3">
                <div class="col-lg-7 col-md-6">
                    <div class="row g-3">
                        <?php
                        // Hiển thị tối đa 3 danh mục đầu tiên
                        $firstCategories = array_slice($categories, 0, min(3, count($categories)));
                        $delay = 0.1;
                        foreach ($firstCategories as $index => $category):
                            $width = $index == 0 ? 'col-lg-12' : 'col-lg-6';
                        ?>
                            <div class="<?php echo $width; ?> col-md-12 wow zoomIn" data-wow-delay="<?php echo $delay; ?>s">
                                <a class="position-relative d-block overflow-hidden" href="courses.php?category=<?php echo $category['id']; ?>">
                                    <img class="img-fluid" src="assets/img/cat-<?php echo $index + 1; ?>.jpg" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                    <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                        <h5 class="m-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                                        <small class="text-primary"><?php echo $category['course_count']; ?> Khóa học</small>
                                    </div>
                                </a>
                            </div>
                        <?php
                            $delay += 0.2;
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php if (isset($categories[3])): ?>
                    <div class="col-lg-5 col-md-6 wow zoomIn" data-wow-delay="0.7s" style="min-height: 350px;">
                        <a class="position-relative d-block h-100 overflow-hidden" href="courses.php?category=<?php echo $categories[3]['id']; ?>">
                            <img class="img-fluid position-absolute w-100 h-100" src="assets/img/cat-4.jpg" alt="<?php echo htmlspecialchars($categories[3]['name']); ?>" style="object-fit: cover;">
                            <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                <h5 class="m-0"><?php echo htmlspecialchars($categories[3]['name']); ?></h5>
                                <small class="text-primary"><?php echo $categories[3]['course_count']; ?> Khóa học</small>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Categories End -->


    <!-- Courses Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Khóa học</h6>
                <h1 class="mb-5">Khóa học phổ biến</h1>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                $delay = 0.1;
                foreach ($popularCourses as $index => $course):
                    // Giới hạn hiển thị 3 khóa học
                    if ($index >= 3) break;
                ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?php echo $delay; ?>s">
                        <div class="course-item bg-light">
                            <div class="position-relative overflow-hidden">
                                <img class="img-fluid" src="<?php echo $course['thumbnail'] ?: 'assets/img/course-' . ($index + 1) . '.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                <div class="w-100 d-flex justify-content-center position-absolute bottom-0 start-0 mb-4">
                                    <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="flex-shrink-0 btn btn-sm btn-primary px-3 border-end" style="border-radius: 30px 0 0 30px;">Chi tiết</a>
                                    <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="flex-shrink-0 btn btn-sm btn-primary px-3" style="border-radius: 0 30px 30px 0;">Đăng ký</a>
                                </div>
                            </div>
                            <div class="text-center p-4 pb-0">
                                <h3 class="mb-0">
                                    <?php if ($course['price'] > 0): ?>
                                        <?php echo number_format($course['price'], 0, ',', '.'); ?>đ
                                    <?php else: ?>
                                        <span class="text-primary">Miễn phí</span>
                                    <?php endif; ?>
                                </h3>
                                <div class="mb-3">
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small class="fa fa-star text-primary"></small>
                                    <small>(<?php echo $course['student_count']; ?>)</small>
                                </div>
                                <h5 class="mb-4"><?php echo htmlspecialchars($course['title']); ?></h5>
                            </div>
                            <div class="d-flex border-top">
                                <small class="flex-fill text-center border-end py-2"><i class="fa fa-user-tie text-primary me-2"></i><?php echo htmlspecialchars($course['instructor_name']); ?></small>
                                <small class="flex-fill text-center border-end py-2"><i class="fa fa-clock text-primary me-2"></i><?php echo $course['lesson_count']; ?> bài</small>
                                <small class="flex-fill text-center py-2"><i class="fa fa-user text-primary me-2"></i><?php echo $course['student_count']; ?> học viên</small>
                            </div>
                        </div>
                    </div>
                <?php
                    $delay += 0.2;
                endforeach;
                ?>
            </div>
            <?php if (count($popularCourses) > 3): ?>
                <div class="text-center mt-5">
                    <a href="courses.php" class="btn btn-primary py-3 px-5">Xem tất cả khóa học</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Courses End -->


    <!-- Team Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Giảng viên</h6>
                <h1 class="mb-5">Đội ngũ giảng viên chuyên gia</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item bg-light">
                        <div class="overflow-hidden">
                            <img class="img-fluid" src="assets/img/team-1.jpg" alt="">
                        </div>
                        <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                            <div class="bg-light d-flex justify-content-center pt-2 px-1">
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="text-center p-4">
                            <h5 class="mb-0">Nguyễn Văn An</h5>
                            <small>Giảng viên Công nghệ thông tin</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item bg-light">
                        <div class="overflow-hidden">
                            <img class="img-fluid" src="assets/img/team-2.jpg" alt="">
                        </div>
                        <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                            <div class="bg-light d-flex justify-content-center pt-2 px-1">
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="text-center p-4">
                            <h5 class="mb-0">Trần Thị Bình</h5>
                            <small>Giảng viên Kinh tế</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item bg-light">
                        <div class="overflow-hidden">
                            <img class="img-fluid" src="assets/img/team-3.jpg" alt="">
                        </div>
                        <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                            <div class="bg-light d-flex justify-content-center pt-2 px-1">
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="text-center p-4">
                            <h5 class="mb-0">Lê Minh Châu</h5>
                            <small>Giảng viên Ngôn ngữ Anh</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item bg-light">
                        <div class="overflow-hidden">
                            <img class="img-fluid" src="assets/img/team-4.jpg" alt="">
                        </div>
                        <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                            <div class="bg-light d-flex justify-content-center pt-2 px-1">
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="text-center p-4">
                            <h5 class="mb-0">Phạm Hoàng Dũng</h5>
                            <small>Giảng viên Marketing</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->


    <!-- Testimonial Start -->
    <div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="text-center">
                <h6 class="section-title bg-white text-center text-primary px-3">Testimonial</h6>
                <h1 class="mb-5">Our Students Say!</h1>
            </div>
            <div class="owl-carousel testimonial-carousel position-relative">
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-1.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Nguyễn Minh Hưng</h5>
                    <p>Sinh viên IT</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Tôi đã học được rất nhiều kiến thức bổ ích từ các khóa học trên eLEARNING. Giảng viên nhiệt tình và nội dung rất thực tế.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-2.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Trần Thị Lan</h5>
                    <p>Nhân viên Marketing</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Nền tảng học trực tuyến tuyệt vời! Tôi có thể học mọi lúc mọi nơi và áp dụng ngay vào công việc của mình.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-3.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Lê Văn Đức</h5>
                    <p>Giám đốc kinh doanh</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Chất lượng giảng dạy xuất sắc với đội ngũ giảng viên giàu kinh nghiệm. Tôi rất hài lòng với trải nghiệm học tập.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-4.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Phạm Thị Mai</h5>
                    <p>Kế toán trưởng</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Hệ thống học tập hiện đại và dễ sử dụng. Các khóa học được cập nhật liên tục và phù hợp với thực tế công việc.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->


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

    <!-- Spinner Handler - Load trước main.js -->
    <script src="assets/js/spinner.js"></script>

    <!-- Form Handler - Xử lý POST/Redirect/GET pattern -->
    <script src="assets/js/form-handler.js"></script>

    <!-- Login Handler - Xử lý popup login -->
    <script src="assets/js/login-handler.js"></script>

    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>

    <?php
    if (isset($_SESSION['alert_message'])) {
        echo "<script>
            // Sử dụng hàm global để ẩn spinner
            if (typeof hideSpinnerNow === 'function') {
                hideSpinnerNow();
            }
            
            // Đảm bảo spinner được ẩn hoàn toàn trước khi hiển thị alert
            $(document).ready(function() {
                if (typeof hideSpinnerNow === 'function') {
                    hideSpinnerNow();
                }
                
                var spinner = $('#spinner');
                if (spinner.length > 0) {
                    spinner.removeClass('show').addClass('spinner-force-hide').hide();
                }
                
                // Hiển thị alert sau khi đã ẩn spinner
                alert('" . $_SESSION['alert_message'] . "');
                
                // Tự động hiển thị form đăng nhập sau khi alert
                setTimeout(function() {
                    showLogin();
                }, 100);
            });
        </script>";
        unset($_SESSION['alert_message']); // Xóa thông báo sau khi hiển thị
    }

    // Hiển thị popup login nếu có lỗi đăng nhập  
    if (isset($_SESSION['login_error'])) {
        echo "<script>
            $(document).ready(function() {
                // Ẩn spinner
                if (typeof hideSpinnerNow === 'function') {
                    hideSpinnerNow();
                }
                
                var spinner = $('#spinner');
                if (spinner.length > 0) {
                    spinner.removeClass('show').addClass('spinner-force-hide').hide();
                }
                
                // Hiển thị popup login với lỗi
                setTimeout(function() {
                    showLogin();
                }, 100);
            });
        </script>";
    }
    ?>
</body>

</html>