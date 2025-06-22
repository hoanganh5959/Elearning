<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/manage_blog.php';

$blogManager = new BlogManager();

// Lấy ID blog từ URL
$blog_id = $_GET['id'] ?? 0;

if (!$blog_id) {
    header('Location: blogs.php');
    exit();
}

// Lấy chi tiết blog
$blog = $blogManager->getBlogById($blog_id);

if (!$blog) {
    header('Location: 404.php');
    exit();
}

// Kiểm tra quyền xem (chỉ hiển thị blog published hoặc blog của chính mình)
if ($blog['status'] !== 'published') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $blog['author_id']) {
        $_SESSION['error_message'] = 'Bài viết không tồn tại hoặc chưa được công khai.';
        header('Location: blogs.php');
        exit();
    }
}

// Tăng view count (chỉ khi không phải tác giả)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $blog['author_id']) {
    $blogManager->incrementViewCount($blog_id);
    $blog['view_count']++;
}

// Lấy bài viết liên quan (cùng danh mục, trừ bài hiện tại)
$related_blogs = [];
if (!empty($blog['categories'])) {
    $related_blogs = $blogManager->getAllBlogs(1, 3, 'published', $blog['categories'][0]['id']);
    $related_blogs = array_filter($related_blogs, function($b) use ($blog_id) {
        return $b['id'] != $blog_id;
    });
    $related_blogs = array_slice($related_blogs, 0, 3);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($blog['title']) ?> - eLEARNING</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="description" content="<?= htmlspecialchars($blog['excerpt']) ?>">
    <meta name="keywords" content="<?= implode(', ', array_map(function($cat) { return $cat['name']; }, $blog['categories'])) ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($blog['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($blog['excerpt']) ?>">
    <meta property="og:type" content="article">
    <?php if ($blog['featured_image']): ?>
    <meta property="og:image" content="<?= htmlspecialchars($blog['featured_image']) ?>">
    <?php endif; ?>
    
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

    <!-- Custom Blog Styles -->
    <style>
        .blog-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .blog-meta {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .author-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
        }
        .share-buttons a {
            margin-right: 10px;
            padding: 8px 15px;
            border-radius: 25px;
            text-decoration: none !important;
            color: white;
            font-size: 14px;
        }
        .share-facebook { background: #3b5998; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-copy { background: #6c757d; }
    </style>
</head>

<body>
    <!-- Navbar Start -->
    <?php include 'includes/header.php'; ?>
    <!-- Navbar End -->

    <!-- Blog Detail Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <!-- Flash Messages -->
            <?= displayFlashMessages() ?>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="blogs.php">Blog</a></li>
                            <?php if (!empty($blog['categories'])): ?>
                                <li class="breadcrumb-item">
                                    <a href="blogs.php?category=<?= $blog['categories'][0]['id'] ?>">
                                        <?= htmlspecialchars($blog['categories'][0]['name']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= htmlspecialchars(substr($blog['title'], 0, 50)) ?>...
                            </li>
                        </ol>
                    </nav>

                    <!-- Article -->
                    <article class="blog-post">
                        <!-- Title -->
                        <h1 class="mb-4"><?= htmlspecialchars($blog['title']) ?></h1>

                        <!-- Meta Info -->
                        <div class="blog-meta">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-3">
                                        <?php if ($blog['author_avatar']): ?>
                                            <img src="<?= htmlspecialchars($blog['author_avatar']) ?>" 
                                                 class="rounded-circle me-3" width="50" height="50"
                                                 alt="<?= htmlspecialchars($blog['author_name']) ?>">
                                        <?php else: ?>
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($blog['author_name']) ?></h6>
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($blog['created_at'])) ?>
                                                <?php if ($blog['updated_at'] != $blog['created_at']): ?>
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-edit me-1"></i>
                                                    Cập nhật: <?= date('d/m/Y H:i', strtotime($blog['updated_at'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-flex align-items-center justify-content-md-end">
                                        <span class="me-3">
                                            <i class="fas fa-eye text-muted me-1"></i><?= $blog['view_count'] ?>
                                        </span>
                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $blog['author_id']): ?>
                                            <div class="btn-group">
                                                <a href="write-blog.php?edit=<?= $blog['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Sửa
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete(<?= $blog['id'] ?>, '<?= htmlspecialchars($blog['title']) ?>')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories -->
                            <?php if (!empty($blog['categories'])): ?>
                                <div class="mb-3">
                                    <?php foreach ($blog['categories'] as $cat): ?>
                                        <a href="blogs.php?category=<?= $cat['id'] ?>" 
                                           class="badge bg-primary text-decoration-none me-2">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Featured Image -->
                        <?php if ($blog['featured_image']): ?>
                            <div class="mb-4">
                                <img src="<?= htmlspecialchars($blog['featured_image']) ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?= htmlspecialchars($blog['title']) ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div class="blog-content">
                            <?= $blog['content'] ?>
                        </div>

                        <!-- Share Buttons -->
                        <div class="mt-5">
                            <h6 class="mb-3">Chia sẻ bài viết:</h6>
                            <div class="share-buttons">
                                <a href="#" class="share-facebook" onclick="shareOnFacebook()">
                                    <i class="fab fa-facebook-f me-2"></i>Facebook
                                </a>
                                <a href="#" class="share-twitter" onclick="shareOnTwitter()">
                                    <i class="fab fa-twitter me-2"></i>Twitter
                                </a>
                                <a href="#" class="share-linkedin" onclick="shareOnLinkedIn()">
                                    <i class="fab fa-linkedin-in me-2"></i>LinkedIn
                                </a>
                                <a href="#" class="share-copy" onclick="copyToClipboard()">
                                    <i class="fas fa-link me-2"></i>Copy Link
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- Author Info -->
                    <div class="author-info mt-5">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if ($blog['author_avatar']): ?>
                                    <img src="<?= htmlspecialchars($blog['author_avatar']) ?>" 
                                         class="img-fluid rounded-circle" 
                                         alt="<?= htmlspecialchars($blog['author_name']) ?>">
                                <?php else: ?>
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 80px; height: 80px;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-10">
                                <h5 class="mb-2"><?= htmlspecialchars($blog['author_name']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-user-tag me-2"></i>
                                    <?= ucfirst($blog['author_role']) ?>
                                </p>
                                <p class="mb-0">
                                    Tác giả đã đóng góp nhiều bài viết hữu ích cho cộng đồng học tập.
                                    <a href="blogs.php?author=<?= $blog['author_id'] ?>" class="text-primary">
                                        Xem thêm bài viết của tác giả »
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Related Posts -->
                    <?php if (!empty($related_blogs)): ?>
                        <div class="bg-light rounded p-4 mb-4">
                            <h5 class="mb-3">Bài viết liên quan</h5>
                            <?php foreach ($related_blogs as $related): ?>
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <?php if ($related['featured_image']): ?>
                                            <img src="<?= htmlspecialchars($related['featured_image']) ?>" 
                                                 class="img-fluid rounded" 
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 alt="<?= htmlspecialchars($related['title']) ?>">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-file-alt text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1">
                                            <a href="blog-detail.php?id=<?= $related['id'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars(substr($related['title'], 0, 60)) ?>...
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?= date('d/m/Y', strtotime($related['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="blogs.php?category=<?= $blog['categories'][0]['id'] ?? '' ?>" 
                                   class="btn btn-primary btn-sm">
                                    Xem thêm bài viết
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Categories -->
                    <div class="bg-light rounded p-4 mb-4">
                        <h5 class="mb-3">Danh mục</h5>
                        <?php
                        $all_categories = $blogManager->getAllCategories();
                        foreach ($all_categories as $cat):
                        ?>
                            <div class="d-flex justify-content-between mb-2">
                                <a href="blogs.php?category=<?= $cat['id'] ?>" 
                                   class="text-decoration-none">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                                <span class="badge bg-primary"><?= $cat['blog_count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Back to Blog -->
                    <div class="text-center">
                        <a href="blogs.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách blog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog Detail End -->

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
                        <input type="hidden" name="redirect_to" value="blogs.php">
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

        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?= htmlspecialchars($blog['title']) ?>');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
        }

        function shareOnLinkedIn() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Đã copy link bài viết!');
            }).catch(function() {
                // Fallback cho trình duyệt cũ
                const textArea = document.createElement('textarea');
                textArea.value = window.location.href;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Đã copy link bài viết!');
            });
        }
    </script>
</body>
</html> 