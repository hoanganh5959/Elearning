<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../classes/manage_user.php';

// Kiểm tra quyền admin
requireLogin(['admin']);

$userManager = new UserManager();

// Xử lý phân trang và tìm kiếm
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$search = trim($_GET['search'] ?? '');

$users = $userManager->getAllUsers($page, $limit, $search);
$totalUsers = $userManager->getTotalUsers($search);
$totalPages = ceil($totalUsers / $limit);
$stats = $userManager->getUserStats();

// Thông báo
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - eLEARNING</title>

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

        .stats-card.total {
            border-left-color: #007bff;
        }

        .stats-card.students {
            border-left-color: #28a745;
        }

        .stats-card.instructors {
            border-left-color: #ffc107;
        }

        .stats-card.admins {
            border-left-color: #dc3545;
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

        .search-box {
            margin-bottom: 1.5rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .role-badge {
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

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
        }

        .modal-header .close {
            color: white;
            opacity: 0.8;
        }

        .form-group label {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }

            .btn-sm {
                padding: 0.125rem 0.25rem;
                font-size: 0.75rem;
                margin: 0.125rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-tachometer-alt mr-3"></i>Admin Dashboard</h1>
                    <p class="mb-0">Quản lý hệ thống eLearning</p>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group">
                        <button class="btn btn-light btn-lg" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus mr-2"></i>Thêm người dùng
                        </button>
                        <a href="../instructor/index.php" class="btn btn-outline-light btn-lg ml-2">
                            <i class="fas fa-book mr-2"></i>Quản lý khóa học
                        </a>
                        <a href="blog-management.php" class="btn btn-outline-light btn-lg ml-2">
                            <i class="fas fa-blog mr-2"></i>Quản lý Blog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Thống kê -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card total">
                    <div class="stats-number text-primary"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stats-label">Tổng người dùng</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card students">
                    <div class="stats-number text-success"><?php echo number_format($stats['students']); ?></div>
                    <div class="stats-label">Học viên</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card instructors">
                    <div class="stats-number text-warning"><?php echo number_format($stats['instructors']); ?></div>
                    <div class="stats-label">Giảng viên</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card admins">
                    <div class="stats-number text-danger"><?php echo number_format($stats['admins']); ?></div>
                    <div class="stats-label">Quản trị viên</div>
                </div>
            </div>
        </div>

        <!-- Thông báo -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Nội dung chính -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="fas fa-users mr-2"></i>Quản lý Người dùng</h3>
                <a href="manage_users.php" class="btn btn-primary">
                    <i class="fas fa-eye mr-2"></i>Xem chi tiết
                </a>
            </div>

            <!-- Tìm kiếm -->
            <div class="search-box">
                <form method="GET" class="row">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, email, username..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Bảng người dùng -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Thông tin</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Khóa học</th>
                            <th>Đăng ký</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Không tìm thấy người dùng nào</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <img src="../<?php echo htmlspecialchars($user['avatar'] ?: 'assets/img/default-avatar.png'); ?>"
                                            alt="Avatar" class="user-avatar">
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        </div>
                                        <small class="text-muted">
                                            @<?php echo htmlspecialchars($user['username']); ?><br>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $roleColors = [
                                            'student' => 'success',
                                            'instructor' => 'warning',
                                            'admin' => 'danger'
                                        ];
                                        $roleNames = [
                                            'student' => 'Học viên',
                                            'instructor' => 'Giảng viên',
                                            'admin' => 'Quản trị'
                                        ];
                                        ?>
                                        <span class="badge badge-<?php echo $roleColors[$user['role']]; ?> role-badge">
                                            <?php echo $roleNames[$user['role']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $status = $user['status'] ?? 'active'; ?>
                                        <span class="badge badge-<?php echo $status === 'active' ? 'success' : 'secondary'; ?> status-badge">
                                            <?php echo $status === 'active' ? 'Hoạt động' : 'Bị khóa'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php if ($user['role'] === 'instructor'): ?>
                                                <i class="fas fa-chalkboard-teacher text-warning"></i>
                                                <?php echo (int)$user['course_count']; ?> khóa học
                                            <?php else: ?>
                                                <i class="fas fa-book-reader text-info"></i>
                                                <?php echo (int)$user['enrollment_count']; ?> đăng ký
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo (int)$user['enrollment_count']; ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <button class="btn btn-info btn-sm edit-user"
                                                data-user-id="<?php echo $user['user_id']; ?>"
                                                title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-<?php echo $status === 'active' ? 'warning' : 'success'; ?> btn-sm toggle-status"
                                                    data-user-id="<?php echo $user['user_id']; ?>"
                                                    title="<?php echo $status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản'; ?>">
                                                    <i class="fas fa-<?php echo $status === 'active' ? 'lock' : 'unlock'; ?>"></i>
                                                </button>

                                                <?php if ($user['role'] !== 'admin'): ?>
                                                    <button class="btn btn-danger btn-sm delete-user"
                                                        data-user-id="<?php echo $user['user_id']; ?>"
                                                        data-user-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                        title="Xóa người dùng">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Trước</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Sau</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal thêm người dùng -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng mới</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tên người dùng <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Vai trò</label>
                            <select name="role" class="form-control">
                                <option value="student">Học viên</option>
                                <option value="instructor">Giảng viên</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Bị khóa</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm người dùng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal chỉnh sửa người dùng -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa người dùng</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editUserForm">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tên người dùng <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Vai trò</label>
                            <select name="role" id="edit_role" class="form-control">
                                <option value="student">Học viên</option>
                                <option value="instructor">Giảng viên</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Bị khóa</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Thêm người dùng
            $('#addUserForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'add');

                $.ajax({
                    url: 'api/user_actions.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Có lỗi xảy ra, vui lòng thử lại');
                    }
                });
            });

            // Chỉnh sửa người dùng
            $('.edit-user').on('click', function() {
                const userId = $(this).data('user-id');

                $.ajax({
                    url: 'api/user_actions.php',
                    type: 'POST',
                    data: {
                        action: 'get_user',
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.success) {
                            const user = response.user;
                            $('#edit_user_id').val(user.user_id);
                            $('#edit_name').val(user.name);
                            $('#edit_username').val(user.username);
                            $('#edit_email').val(user.email);
                            $('#edit_role').val(user.role);
                            $('#edit_status').val(user.status || 'active');
                            $('#editUserModal').modal('show');
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    }
                });
            });

            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'edit');

                $.ajax({
                    url: 'api/user_actions.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    }
                });
            });



            // Thay đổi trạng thái
            $('.toggle-status').on('click', function() {
                if (!confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?')) {
                    return;
                }

                const userId = $(this).data('user-id');

                $.ajax({
                    url: 'api/user_actions.php',
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    }
                });
            });

            // Xóa người dùng
            $('.delete-user').on('click', function() {
                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');

                if (!confirm(`Bạn có chắc muốn xóa người dùng "${userName}"?\n\nHành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.`)) {
                    return;
                }

                $.ajax({
                    url: 'api/user_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + response.message);
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>