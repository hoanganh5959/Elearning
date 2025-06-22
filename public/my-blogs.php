<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/manage_blog.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Bạn cần đăng nhập để xem blog của mình.';
    header('Location: login.php?redirect=my-blogs.php');
    exit();
}

$blogManager = new BlogManager();
$user_id = $_SESSION['user_id'];

// Lấy parameters từ URL
$page = max(1, $_GET['page'] ?? 1);
$limit = 10;

// Lấy danh sách blog của user
$blogs = $blogManager->getBlogsByUser($user_id, $page, $limit);
$total_blogs = $blogManager->countBlogs(null, null, $user_id);
$total_pages = ceil($total_blogs / $limit);

function getStatusBadge($status) {
    switch ($status) {
        case 'published':
            return '<span class="badge bg-success">Đã xuất bản</span>';
        case 'draft':
            return '<span class="badge bg-warning">Bản nháp</span>';
        case 'private':
            return '<span class="badge bg-secondary">Riêng tư</span>';
        default:
            return '<span class="badge bg-secondary">Không xác định</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Blog của tôi - eLEARNING</title>
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
                    <h1 class="display-3 text-white animated slideInDown">Blog của tôi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a class="text-white" href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a class="text-white" href="blogs.php">Blog</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Blog của tôi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- My Blogs Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <!-- Flash Messages -->
            <?= displayFlashMessages() ?>

            <!-- Header Actions -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h3>Quản lý bài viết của bạn</h3>
                    <p class="text-muted">Tổng cộng: <strong><?= $total_blogs ?></strong> bài viết</p>
                </div>
                <div class="col-lg-4 text-end">
                    <a href="write-blog.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Viết bài mới
                    </a>
                    <a href="blogs.php" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-2"></i>Xem tất cả blog
                    </a>
                </div>
            </div>

            <!-- Blog List -->
            <?php if (empty($blogs)): ?>
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-file-alt fa-5x text-muted mb-3"></i>
                        <h3 class="text-muted">Bạn chưa có bài viết nào</h3>
                        <p class="text-muted">Hãy bắt đầu chia sẻ kiến thức và kinh nghiệm của bạn!</p>
                        <a href="write-blog.php" class="btn btn-primary">
                            <i class="fas fa-pen me-2"></i>Viết bài đầu tiên
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($blogs as $blog): ?>
                        <div class="col-12 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <!-- Thumbnail -->
                                        <div class="col-md-2">
                                            <?php if ($blog['featured_image']): ?>
                                                <img src="<?= htmlspecialchars($blog['featured_image']) ?>" 
                                                     class="img-fluid rounded" 
                                                     style="width: 100px; height: 80px; object-fit: cover;"
                                                     alt="<?= htmlspecialchars($blog['title']) ?>">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 100px; height: 80px;">
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Content -->
                                        <div class="col-md-6">
                                            <h5 class="card-title mb-2">
                                                <a href="blog-detail.php?id=<?= $blog['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($blog['title']) ?>
                                                </a>
                                            </h5>
                                            <p class="card-text text-muted small mb-2">
                                                <?= htmlspecialchars(substr($blog['excerpt'], 0, 100)) ?>...
                                            </p>
                                            <div class="mb-2">
                                                <?= getStatusBadge($blog['status']) ?>
                                                <?php if (!empty($blog['categories'])): ?>
                                                    <?php foreach ($blog['categories'] as $cat): ?>
                                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($cat['name']) ?></span>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Stats -->
                                        <div class="col-md-2 text-center">
                                            <div class="small text-muted">
                                                <div><i class="fas fa-eye"></i> <?= $blog['view_count'] ?></div>
                                                <div><i class="fas fa-comments"></i> <?= $blog['comment_count'] ?></div>
                                                <div><i class="far fa-calendar"></i> <?= date('d/m/Y', strtotime($blog['created_at'])) ?></div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="col-md-2 text-end">
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="blog-detail.php?id=<?= $blog['id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm mb-1">
                                                    <i class="fas fa-eye"></i> Xem
                                                </a>
                                                <a href="write-blog.php?edit=<?= $blog['id'] ?>" 
                                                   class="btn btn-outline-warning btn-sm mb-1">
                                                    <i class="fas fa-edit"></i> Sửa
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="confirmDelete(<?= $blog['id'] ?>, '<?= htmlspecialchars($blog['title']) ?>')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>">
                                                <i class="fa fa-angle-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>">
                                                <i class="fa fa-angle-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- My Blogs End -->

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa bài viết <strong id="blogTitle"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteForm" method="POST" action="processors/blog_process.php" style="display: inline;">
                        <input type="hidden" name="action" value="delete_blog">
                        <input type="hidden" name="blog_id" id="deleteBlogId">
                        <input type="hidden" name="redirect_to" value="my-blogs.php">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Xóa bài viết
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Start -->
    <?php include 'includes/footer.php'; ?>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>

    <!-- Custom Scripts -->
    <script>
        function confirmDelete(blogId, blogTitle) {
            $('#deleteBlogId').val(blogId);
            $('#blogTitle').text(blogTitle);
            $('#deleteModal').modal('show');
        }
    </script>
</body>
</html> 