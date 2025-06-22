<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/manage_blog.php';

$blogManager = new BlogManager();

// Lấy parameters từ URL
$page = max(1, $_GET['page'] ?? 1);
$category_id = $_GET['category'] ?? null;
$search = trim($_GET['search'] ?? '');
$limit = 9; // Số bài viết mỗi trang

// Lấy danh sách blog
if ($search) {
    $blogs = $blogManager->searchBlogs($search, $page, $limit);
    $total_blogs = $blogManager->countBlogs('published', null, null, $search);
} else {
    $blogs = $blogManager->getAllBlogs($page, $limit, 'published', $category_id);
    $total_blogs = $blogManager->countBlogs('published', $category_id);
}

// Tính toán phân trang
$total_pages = ceil($total_blogs / $limit);

// Lấy danh mục
$categories = $blogManager->getAllCategories();

// Lấy thông tin danh mục hiện tại
$current_category = null;
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $current_category = $cat;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Blog - eLEARNING</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

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

    <!-- Customized Bootstrap Stylesheet -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar Start -->
    <?php include 'includes/header.php'; ?>
    <!-- Navbar End -->

    <!-- Header Start -->
    <div class="container-fluid bg-primary py-5 mb-5 page-header">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="display-3 text-white animated slideInDown">
                        <?php if ($current_category): ?>
                            Blog - <?= htmlspecialchars($current_category['name']) ?>
                        <?php elseif ($search): ?>
                            Kết quả tìm kiếm: "<?= htmlspecialchars($search) ?>"
                        <?php else: ?>
                            Blog & Tin tức
                        <?php endif; ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a class="text-white" href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Blog</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Blog Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <!-- Flash Messages -->
            <?= displayFlashMessages() ?>

            <!-- Search and Filter -->
            <div class="row mb-5">
                <div class="col-lg-8">
                    <form method="GET" action="blogs.php" class="d-flex">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            class="form-control me-2" placeholder="Tìm kiếm bài viết...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-lg-4 text-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="write-blog.php" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Viết bài mới
                        </a>
                        <a href="my-blogs.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-edit me-2"></i>Bài viết của tôi
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categories -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="blogs.php" class="btn <?= !$category_id ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                            Tất cả (<?= $blogManager->countBlogs('published') ?>)
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="blogs.php?category=<?= $cat['id'] ?>"
                                class="btn <?= $category_id == $cat['id'] ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                                <?= htmlspecialchars($cat['name']) ?> (<?= $cat['blog_count'] ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Blog Posts -->
            <div class="row g-4">
                <?php if (empty($blogs)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-file-alt fa-5x text-muted mb-3"></i>
                        <h3 class="text-muted">Chưa có bài viết nào</h3>
                        <p class="text-muted">
                            <?php if ($search): ?>
                                Không tìm thấy kết quả cho từ khóa "<?= htmlspecialchars($search) ?>"
                            <?php else: ?>
                                Hãy là người đầu tiên chia sẻ kiến thức!
                            <?php endif; ?>
                        </p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="write-blog.php" class="btn btn-primary">Viết bài đầu tiên</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="blog-item bg-light rounded overflow-hidden h-100">
                                <?php if ($blog['featured_image']): ?>
                                    <div class="blog-img position-relative">
                                        <img class="img-fluid w-100" src="<?= htmlspecialchars($blog['featured_image']) ?>"
                                            alt="<?= htmlspecialchars($blog['title']) ?>" style="height: 200px; object-fit: cover;">
                                        <a class="position-absolute top-0 start-0 bg-primary text-white rounded-end px-3 py-2"
                                            href="blogs.php?category=<?= $blog['categories'][0]['id'] ?? '' ?>">
                                            <?= htmlspecialchars($blog['categories'][0]['name'] ?? 'Chưa phân loại') ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="p-4">
                                    <div class="d-flex mb-3">
                                        <small class="me-3">
                                            <i class="far fa-user text-primary me-2"></i>
                                            <?= htmlspecialchars($blog['author_name']) ?>
                                        </small>
                                        <small>
                                            <i class="far fa-calendar-alt text-primary me-2"></i>
                                            <?= date('d/m/Y', strtotime($blog['created_at'])) ?>
                                        </small>
                                    </div>
                                    <h4 class="mb-3">
                                        <a href="blog-detail.php?id=<?= $blog['id'] ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($blog['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="text-muted"><?= htmlspecialchars($blog['excerpt']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a class="btn btn-primary" href="blog-detail.php?id=<?= $blog['id'] ?>">
                                            Đọc thêm <i class="fa fa-arrow-right ms-2"></i>
                                        </a>
                                        <div class="text-muted small">
                                            <i class="fas fa-eye me-1"></i><?= $blog['view_count'] ?>
                                            <i class="fas fa-comments ms-2 me-1"></i><?= $blog['comment_count'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                            <i class="fa fa-angle-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Blog End -->

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

    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>
</body>

</html>