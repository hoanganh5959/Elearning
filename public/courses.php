<?php
session_start();
require_once '../classes/CourseManager.php';
$courseManager = new CourseManager();

// Lấy category_id nếu có
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$currentCategory = null;

// Lấy thông tin người dùng đăng nhập (nếu có)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Nếu có category_id, lấy danh sách khóa học theo danh mục và thông tin danh mục
if ($categoryId) {
    $courses = $courseManager->getCoursesByCategory($categoryId);
    $currentCategory = $courseManager->getCategoryById($categoryId);
} else {
    // Ngược lại, lấy tất cả khóa học
    $courses = $courseManager->getAllCourses();
}

// Lấy tất cả danh mục
$categories = $courseManager->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>eLEARNING - eLearning HTML Template</title>
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
                    <h1 class="display-3 text-white animated slideInDown">
                        <?php echo $currentCategory ? $currentCategory['name'] . ' Courses' : 'Courses'; ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a class="text-white" href="courses.php">Courses</a></li>
                            <?php if ($currentCategory): ?>
                                <li class="breadcrumb-item text-white active" aria-current="page"><?php echo $currentCategory['name']; ?></li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->


    <!-- Categories Start -->
    <div class="container-xxl py-5 category">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Categories</h6>
                <h1 class="mb-5">Courses Categories</h1>
            </div>
            <div class="row g-3">
                <div class="col-lg-7 col-md-6">
                    <div class="row g-3">
                        <?php
                        $delay = 0.1;
                        $count = 0;
                        foreach ($categories as $category):
                            if ($count < 3): // Chỉ hiển thị 3 danh mục đầu tiên trong layout này
                                $colClass = $count == 0 ? 'col-lg-12 col-md-12' : 'col-lg-6 col-md-12';
                        ?>
                                <div class="<?php echo $colClass; ?> wow zoomIn" data-wow-delay="<?php echo $delay; ?>s">
                                    <a class="position-relative d-block overflow-hidden" href="javascript:void(0)"
                                        onclick="filterCoursesByCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>')"
                                        data-category-id="<?php echo $category['id']; ?>">
                                        <img class="img-fluid" src="assets/img/cat-<?php echo $count + 1; ?>.jpg" alt="">
                                        <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                            <h5 class="m-0"><?php echo $category['name']; ?></h5>
                                            <small class="text-primary"><?php echo $category['course_count']; ?> Courses</small>
                                        </div>
                                    </a>
                                </div>
                        <?php
                                $delay += 0.2;
                                $count++;
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php
                // Hiển thị danh mục thứ 4 nếu có
                if (isset($categories[3])):
                ?>
                    <div class="col-lg-5 col-md-6 wow zoomIn" data-wow-delay="0.7s" style="min-height: 350px;">
                        <a class="position-relative d-block h-100 overflow-hidden" href="javascript:void(0)"
                            onclick="filterCoursesByCategory(<?php echo $categories[3]['id']; ?>, '<?php echo addslashes($categories[3]['name']); ?>')"
                            data-category-id="<?php echo $categories[3]['id']; ?>">
                            <img class="img-fluid position-absolute w-100 h-100" src="assets/img/cat-4.jpg" alt="" style="object-fit: cover;">
                            <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin:  1px;">
                                <h5 class="m-0"><?php echo $categories[3]['name']; ?></h5>
                                <small class="text-primary"><?php echo $categories[3]['course_count']; ?> Courses</small>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Categories Start -->


    <!-- Courses Start -->
    <div class="container-xxl py-5" id="courses-section">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Courses</h6>
                <h1 class="mb-5">
                    <?php echo $currentCategory ? $currentCategory['name'] . ' Courses' : 'Popular Courses'; ?>
                </h1>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                $delay = 0.1;
                foreach ($courses as $course):
                ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?php echo $delay; ?>s">
                        <div class="course-item bg-light">
                            <div class="position-relative overflow-hidden">
                                <img class="img-fluid" src="<?php echo $course['thumbnail'] ?: 'assets/img/course-1.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                <div class="w-100 d-flex justify-content-center position-absolute bottom-0 start-0 mb-4">
                                    <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="flex-shrink-0 btn btn-sm btn-primary px-3 border-end" style="border-radius: 30px 0 0 30px;">Chi tiết</a>
                                    <?php if ($userId && $courseManager->isUserEnrolled($userId, $course['id'])): ?>
                                        <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="flex-shrink-0 btn btn-sm btn-success px-3" style="border-radius: 0 30px 30px 0;">
                                            <i class="fas fa-play-circle me-1"></i> Học ngay
                                        </a>
                                    <?php else: ?>
                                        <?php if ($course['price'] > 0): ?>
                                            <a href="payment.php?course_id=<?php echo $course['id']; ?>" class="flex-shrink-0 btn btn-sm btn-primary px-3" style="border-radius: 0 30px 30px 0;">
                                                <?php echo number_format($course['price'], 0, ',', '.'); ?>đ - Đăng ký
                                            </a>
                                        <?php else: ?>
                                            <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="flex-shrink-0 btn btn-sm btn-primary px-3" style="border-radius: 0 30px 30px 0;">
                                                Đăng ký miễn phí
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
                                </div>
                                <h5 class="mb-4"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <?php if ($course['description']): ?>
                                    <p class="mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex border-top">
                                <small class="flex-fill text-center border-end py-2"><i class="fa fa-user-tie text-primary me-2"></i><?php echo htmlspecialchars($course['instructor_name']); ?></small>
                                <small class="flex-fill text-center border-end py-2"><i class="fa fa-video text-primary me-2"></i><?php echo $course['lesson_count']; ?> Videos</small>
                                <small class="flex-fill text-center py-2"><i class="fa fa-user text-primary me-2"></i><?php echo $course['student_count']; ?> Students</small>
                            </div>
                        </div>
                    </div>
                <?php
                    $delay += 0.2;
                    if ($delay > 0.5) $delay = 0.1;
                endforeach;

                // Nếu không có khóa học nào
                if (count($courses) == 0):
                ?>
                    <div class="col-12 text-center">
                        <p>Chưa có khóa học nào trong danh mục này.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Courses End -->


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
                    <h5 class="mb-0">Client Name</h5>
                    <p>Profession</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit diam amet diam et eos. Clita erat ipsum et lorem et sit.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-2.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Client Name</h5>
                    <p>Profession</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit diam amet diam et eos. Clita erat ipsum et lorem et sit.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-3.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Client Name</h5>
                    <p>Profession</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit diam amet diam et eos. Clita erat ipsum et lorem et sit.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="assets/img/testimonial-4.jpg" style="width: 80px; height: 80px;">
                    <h5 class="mb-0">Client Name</h5>
                    <p>Profession</p>
                    <div class="testimonial-text bg-light text-center p-4">
                        <p class="mb-0">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit diam amet diam et eos. Clita erat ipsum et lorem et sit.</p>
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

    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>

    <!-- Scroll Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra xem có tham số scroll=courses trong URL không
            const urlParams = new URLSearchParams(window.location.search);
            const scrollTarget = urlParams.get('scroll');
            const categoryId = urlParams.get('category');

            if (scrollTarget === 'courses') {
                // Tìm phần tử có id="courses-section"
                const coursesSection = document.getElementById('courses-section');
                if (coursesSection) {
                    // Cuộn đến phần tử đó với hiệu ứng mượt mà
                    setTimeout(function() {
                        coursesSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }, 500); // Đợi 500ms để trang đã tải xong
                }
            }

            // Khởi tạo chức năng lọc nếu đã có category trong URL
            if (categoryId) {
                const categoryName = document.querySelector(`[data-category-id="${categoryId}"] h5`);
                if (categoryName) {
                    document.querySelector('#courses-section h1').innerText = categoryName.innerText + ' Courses';
                }
            }
        });

        // Hàm lọc khóa học theo danh mục
        function filterCoursesByCategory(categoryId, categoryName) {
            // Cuộn đến phần danh sách khóa học
            const coursesSection = document.getElementById('courses-section');
            if (coursesSection) {
                coursesSection.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            // Cập nhật tiêu đề phần khóa học
            const courseTitle = document.querySelector('#courses-section h1');
            if (courseTitle) {
                courseTitle.innerText = categoryName + ' Courses';
            }

            // Hiển thị loading
            const coursesContainer = document.querySelector('#courses-section .row');
            if (coursesContainer) {
                coursesContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            }

            // Lấy dữ liệu khóa học của danh mục mà không tải lại trang
            fetch(`courses-ajax.php?category=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật URL nhưng không tải lại trang
                        const newUrl = `courses.php?category=${categoryId}`;
                        history.pushState({
                            categoryId
                        }, '', newUrl);

                        // Cập nhật nội dung danh sách khóa học
                        if (coursesContainer) {
                            if (data.courses && data.courses.length > 0) {
                                let coursesHtml = '';
                                data.courses.forEach((course, index) => {
                                    const delay = (index % 3) * 0.2 + 0.1;
                                    coursesHtml += `
                                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="${delay}s">
                                        <div class="course-item bg-light">
                                            <div class="position-relative overflow-hidden">
                                                <img class="img-fluid" src="${course.thumbnail || 'assets/img/course-1.jpg'}" alt="${course.title}">
                                                <div class="w-100 d-flex justify-content-center position-absolute bottom-0 start-0 mb-4">
                                                    <a href="course-detail.php?id=${course.id}" class="flex-shrink-0 btn btn-sm btn-primary px-3 border-end" style="border-radius: 30px 0 0 30px;">Chi tiết</a>
                                                    ${course.is_enrolled ? 
                                                    `<a href="lessons.php?course_id=${course.id}" class="flex-shrink-0 btn btn-sm btn-success px-3" style="border-radius: 0 30px 30px 0;">
                                                        <i class="fas fa-play-circle me-1"></i> Học ngay
                                                    </a>` :
                                                    course.price > 0 ?
                                                    `<a href="payment.php?course_id=${course.id}" class="flex-shrink-0 btn btn-sm btn-primary px-3" style="border-radius: 0 30px 30px 0;">
                                                        ${new Intl.NumberFormat('vi-VN').format(course.price)}đ - Đăng ký
                                                    </a>` :
                                                    `<a href="enroll.php?course_id=${course.id}" class="flex-shrink-0 btn btn-sm btn-primary px-3" style="border-radius: 0 30px 30px 0;">
                                                        Đăng ký miễn phí
                                                    </a>`}
                                                </div>
                                            </div>
                                            <div class="text-center p-4 pb-0">
                                                <h3 class="mb-0">
                                                    ${course.price > 0 ? 
                                                    new Intl.NumberFormat('vi-VN').format(course.price) + 'đ' : 
                                                    '<span class="text-primary">Miễn phí</span>'}
                                                </h3>
                                                <div class="mb-3">
                                                    <small class="fa fa-star text-primary"></small>
                                                    <small class="fa fa-star text-primary"></small>
                                                    <small class="fa fa-star text-primary"></small>
                                                    <small class="fa fa-star text-primary"></small>
                                                    <small class="fa fa-star text-primary"></small>
                                                </div>
                                                <h5 class="mb-4">${course.title}</h5>
                                                ${course.description ? `<p class="mb-4">${course.description}</p>` : ''}
                                            </div>
                                            <div class="d-flex border-top">
                                                <small class="flex-fill text-center border-end py-2"><i class="fa fa-user-tie text-primary me-2"></i>${course.instructor_name}</small>
                                                <small class="flex-fill text-center border-end py-2"><i class="fa fa-video text-primary me-2"></i>${course.lesson_count} Videos</small>
                                                <small class="flex-fill text-center py-2"><i class="fa fa-user text-primary me-2"></i>${course.student_count} Students</small>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                });
                                coursesContainer.innerHTML = coursesHtml;

                                // Khởi tạo lại hiệu ứng WOW
                                if (typeof WOW !== 'undefined') {
                                    new WOW().init();
                                }
                            } else {
                                coursesContainer.innerHTML = '<div class="col-12 text-center"><p>Chưa có khóa học nào trong danh mục này.</p></div>';
                            }
                        }
                    } else {
                        console.error('Error fetching courses:', data.message);
                        if (coursesContainer) {
                            coursesContainer.innerHTML = '<div class="col-12 text-center"><p>Có lỗi xảy ra khi tải danh sách khóa học.</p></div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    if (coursesContainer) {
                        coursesContainer.innerHTML = '<div class="col-12 text-center"><p>Có lỗi kết nối. Vui lòng thử lại sau.</p></div>';
                    }
                });
        }
    </script>
</body>

</html>