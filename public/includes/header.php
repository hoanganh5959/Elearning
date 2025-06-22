<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER['PHP_SELF']);

// Đảm bảo CourseManager luôn được tải ở mọi trang
require_once __DIR__ . '/../../classes/CourseManager.php';
$courseManager = new CourseManager();

$avatar = '../assets/img/default-avatar.png';
if (!empty($_SESSION['avatar'])) {
    $avatar = $_SESSION['avatar'];
}
?>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
    <a href="../public/index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        <h2 class="m-0 text-primary"><i class="fa fa-book me-3"></i>eLEARNING</h2>
    </a>
    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <a href="../public/index.php" class="nav-item nav-link <?= $current == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-home me-2"></i>Trang chủ
            </a>
            <a href="../public/about.php" class="nav-item nav-link <?= $current == 'about.php' ? 'active' : '' ?>">
                <i class="fas fa-info-circle me-2"></i>Về chúng tôi
            </a>
            <div class="nav-item dropdown">
                <a href="../public/courses.php" class="nav-link dropdown-toggle <?= $current == 'courses.php' || $current == 'course-detail.php' ? 'active' : '' ?>" data-bs-toggle="dropdown">
                    <i class="fas fa-graduation-cap me-2"></i>Khóa học
                </a>
                <div class="dropdown-menu fade-down m-0">
                    <a href="../public/courses.php" class="dropdown-item">
                        <i class="fas fa-list me-2"></i>Tất cả khóa học
                    </a>
                    <div class="dropdown-divider"></div>
                    <?php
                    // Khởi tạo CourseManager nếu chưa được khởi tạo
                    if (!isset($courseManager)) {
                        require_once __DIR__ . '/../../classes/CourseManager.php';
                        $courseManager = new CourseManager();
                    }

                    // Lấy danh sách danh mục course (tránh conflict với blog categories)
                    $course_categories = $courseManager->getAllCategories();

                    // Hiển thị danh mục trong dropdown
                    foreach ($course_categories as $category) {
                        echo '<a href="../public/courses.php?category=' . $category['id'] . '&scroll=courses" class="dropdown-item">
                                <i class="fas fa-tag me-2 text-primary"></i>' . htmlspecialchars($category['name']) . '
                              </a>';
                    }
                    ?>
                </div>
            </div>
            <a href="../public/blogs.php" class="nav-item nav-link <?= in_array($current, ['blogs.php', 'blog-detail.php', 'write-blog.php', 'my-blogs.php']) ? 'active' : '' ?>">
                <i class="fas fa-blog me-2"></i>Blog
            </a>
            <a href="../public/contact.php" class="nav-item nav-link <?= $current == 'contact.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope me-2"></i>Liên hệ
            </a>
        </div>

        <!-- Dynamic user menu -->
        <?php if (isset($_SESSION['name'])): ?>
            <div class="dropdown me-4">
                <button class="btn btn-outline-primary dropdown-toggle user-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="avatar" width="30" height="30" class="rounded-circle me-2">
                    <?= htmlspecialchars($_SESSION['name']) ?>
                    <span class="badge bg-primary ms-1"><?= htmlspecialchars($_SESSION['phanquyen'] ?? 'student') ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                    <li class="dropdown-header">
                        <i class="fas fa-user-circle me-2"></i>Tài khoản của tôi
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="../public/personal-infor.php">
                            <i class="fas fa-user me-2 text-info"></i>Thông tin cá nhân
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../public/my-courses.php">
                            <i class="fas fa-book-open me-2 text-success"></i>Khóa học của tôi
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../public/my-blogs.php">
                            <i class="fas fa-blog me-2 text-primary"></i>Blog của tôi
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../public/payment-history.php">
                            <i class="fas fa-history me-2 text-warning"></i>Lịch sử giao dịch
                        </a>
                    </li>

                    <?php if ($_SESSION['phanquyen'] == 'admin'): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="dropdown-header">
                            <i class="fas fa-cogs me-2"></i>Quản trị hệ thống
                        </li>
                        <li>
                            <a class="dropdown-item admin-item" href="../public/admin/index.php">
                                <i class="fas fa-tachometer-alt me-2 text-danger"></i>Admin Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="../public/instructor/index.php">
                                <i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Quản lí khóa học
                            </a>
                        </li>
                    <?php elseif ($_SESSION['phanquyen'] == 'instructor'): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="dropdown-header">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Giảng viên
                        </li>
                        <li>
                            <a class="dropdown-item" href="../public/instructor/index.php">
                                <i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Quản lí khóa học
                            </a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item logout-item" href="../public/logout.php">
                            <i class="fas fa-sign-out-alt me-2 text-secondary"></i>Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <a href="javascript:void(0)" onclick="showLogin()" class="btn btn-primary py-4 px-lg-5 d-none d-lg-block">
                Tham gia ngay<i class="fa fa-arrow-right ms-3"></i>
            </a>
        <?php endif; ?>


    </div>
</nav>
<!-- Navbar End -->
<!-- <style>
    .dropdown-menu-end {
        right: 0;
        left: auto !important;
    }
</style> -->
<!-- Popup Đăng nhập / Đăng ký -->
<?php include __DIR__ . '/../login.php'; ?>
<?php include __DIR__ . '/../register.php'; ?>