<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../classes/manage_blog.php';

// Kiểm tra quyền admin
requireLogin(['admin']);

$blogManager = new BlogManager();

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $blog_id = (int)($_POST['blog_id'] ?? 0);

    if ($action === 'delete' && $blog_id > 0) {
        if ($blogManager->deleteBlog($blog_id)) {
            setFlashMessage('Xóa blog thành công!', 'success');
        } else {
            setFlashMessage('Có lỗi xảy ra khi xóa blog!', 'error');
        }
    } elseif ($action === 'update_status' && $blog_id > 0) {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['draft', 'published', 'private'])) {
            if ($blogManager->updateBlogStatus($blog_id, $status)) {
                setFlashMessage('Cập nhật trạng thái blog thành công!', 'success');
            } else {
                setFlashMessage('Có lỗi xảy ra khi cập nhật trạng thái!', 'error');
            }
        }
    }

    // Redirect để tránh resubmit
    header('Location: blog-management.php');
    exit();
}

// Xử lý phân trang và tìm kiếm
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$blogs = $blogManager->getAllBlogsForAdmin($page, $limit, $search, $status_filter);
$totalBlogs = $blogManager->getTotalBlogs($search, $status_filter);
$totalPages = ceil($totalBlogs / $limit);
$stats = $blogManager->getBlogStats();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Blog - Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-card.published {
            border-left-color: #28a745;
        }

        .stats-card.draft {
            border-left-color: #ffc107;
        }

        .stats-card.private {
            border-left-color: #6c757d;
        }

        .stats-card.total {
            border-left-color: #007bff;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .blog-excerpt {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .featured-image {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin-right: 0.25rem;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fa;
        }

        .pagination {
            justify-content: center;
        }

        .filter-bar {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-blog mr-3"></i>Quản lý Blog</h1>
                    <p class="mb-0">Quản lý nội dung blog và bài viết</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="index.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Flash Messages -->
        <?= displayFlashMessages() ?>

        <!-- Thống kê -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card total">
                    <div class="stats-number text-primary"><?php echo number_format($stats['total']); ?></div>
                    <div class="stats-label">Tổng bài viết</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card published">
                    <div class="stats-number text-success"><?php echo number_format($stats['published']); ?></div>
                    <div class="stats-label">Đã xuất bản</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card draft">
                    <div class="stats-number text-warning"><?php echo number_format($stats['draft']); ?></div>
                    <div class="stats-label">Bản nháp</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card private">
                    <div class="stats-number text-secondary"><?php echo number_format($stats['private']); ?></div>
                    <div class="stats-label">Riêng tư</div>
                </div>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="filter-bar">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm bài viết..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">Tất cả trạng thái</option>
                        <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Đã xuất bản</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                        <option value="private" <?php echo $status_filter === 'private' ? 'selected' : ''; ?>>Riêng tư</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Tìm kiếm
                    </button>
                    <a href="blog-management.php" class="btn btn-secondary ml-2">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Danh sách blog -->
        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Trạng thái</th>
                            <th>Lượt xem</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($blogs) > 0): ?>
                            <?php foreach ($blogs as $blog): ?>
                                <tr>
                                    <td><?php echo $blog['id']; ?></td>
                                    <td>
                                        <?php if ($blog['featured_image']): ?>
                                            <img src="../<?php echo $blog['featured_image']; ?>" alt="Featured" class="featured-image">
                                        <?php else: ?>
                                            <div class="featured-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold"><?php echo htmlspecialchars($blog['title']); ?></div>
                                        <div class="blog-excerpt text-muted small">
                                            <?php echo htmlspecialchars($blog['excerpt'] ?: strip_tags(substr($blog['content'], 0, 100)) . '...'); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($blog['author_name']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        switch ($blog['status']) {
                                            case 'published':
                                                $statusClass = 'badge-success';
                                                $statusText = 'Đã xuất bản';
                                                break;
                                            case 'draft':
                                                $statusClass = 'badge-warning';
                                                $statusText = 'Bản nháp';
                                                break;
                                            case 'private':
                                                $statusClass = 'badge-secondary';
                                                $statusText = 'Riêng tư';
                                                break;
                                        }
                                        ?>
                                        <span class="badge status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td><?php echo number_format($blog['view_count']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($blog['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="../blog-detail.php?id=<?php echo $blog['id']; ?>"
                                                class="btn btn-sm btn-info" target="_blank" title="Xem">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- Dropdown cho thay đổi trạng thái -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-warning dropdown-toggle"
                                                    data-toggle="dropdown" title="Thay đổi trạng thái">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                                        <button type="submit" name="status" value="published" class="dropdown-item">
                                                            <i class="fas fa-check text-success mr-2"></i>Xuất bản
                                                        </button>
                                                        <button type="submit" name="status" value="draft" class="dropdown-item">
                                                            <i class="fas fa-edit text-warning mr-2"></i>Chuyển thành nháp
                                                        </button>
                                                        <button type="submit" name="status" value="private" class="dropdown-item">
                                                            <i class="fas fa-lock text-secondary mr-2"></i>Chuyển thành riêng tư
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete(<?php echo $blog['id']; ?>, '<?php echo addslashes($blog['title']); ?>')"
                                                title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Không có bài viết nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Blog pagination">
                    <ul class="pagination">
                        <!-- Trang trước -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>">
                                    &laquo; Trước
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Các trang -->
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Trang sau -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>">
                                    Sau &raquo;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Xác nhận xóa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa bài viết <strong id="blogTitle"></strong>?</p>
                    <p class="text-muted">Hành động này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="blog_id" id="deleteBlogId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-2"></i>Xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmDelete(blogId, blogTitle) {
            $('#deleteBlogId').val(blogId);
            $('#blogTitle').text(blogTitle);
            $('#deleteModal').modal('show');
        }

        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>

</html>