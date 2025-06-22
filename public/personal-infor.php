<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/update_personal.php';

// Xác thực người dùng đã đăng nhập
requireLogin();

// Lấy dữ liệu người dùng từ session
$name = $_SESSION['name'] ?? 'Chưa có tên';
$email = $_SESSION['email'] ?? 'Chưa có email';
$role = $_SESSION['phanquyen'] ?? 'Chưa rõ';
$avatar = $_SESSION['avatar'] ?? 'assets/img/default-avatar.png';

// Xác định tab đang hiển thị
$currentTab = $_GET['tab'] ?? 'profile';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
        }

        .profile-sidebar,
        .profile-content {
            background: white;
            border-radius: 12px;
            padding: 20px;
        }

        .profile-item {
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 0;
        }

        .profile-item:last-child {
            border-bottom: none;
        }

        .avatar-img {
            border-radius: 50%;
            width: 70px;
            height: 70px;
            object-fit: cover;
        }

        .list-group-item {
            transition: background-color 0.2s ease;
            border-radius: 5rem !important;
        }

        .list-group-item:hover {
            background-color: #f0f0f0;
            border-radius: 5rem !important;
            cursor: pointer;
        }

        .rounded-3 {
            border-radius: 5rem !important;
        }

        /* CSS cho tab password */
        #password-tab .form-control {
            border-radius: 2rem;
            padding: 0.75rem 1.2rem;
            transition: all 0.3s ease;
        }

        #password-tab .form-control:focus {
            border-color: var(--primary, #06BBCC);
            box-shadow: 0 0 0 0.25rem rgba(6, 187, 204, 0.25);
        }

        #password-tab .btn {
            border-radius: 2rem;
            padding: 0.575rem 1.5rem;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <button onclick="goBack()" class="btn-close position-absolute top-0 end-0 m-4" aria-label="Close"></button>

        <!-- Hiển thị Flash Messages -->
        <?php echo displayFlashMessages(); ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="profile-sidebar bg-white p-4 rounded-4 shadow-sm">
                    <div class="text-center mb-4">
                        <a href="index.php" class="navbar-brand d-flex justify-content-center align-items-center">
                            <h2 class="m-0 text-primary">
                                <i class="fa fa-book me-2"></i>eLEARNING
                            </h2>
                        </a>
                    </div>
                    <h5 class="fw-bold">Cài đặt tài khoản</h5>
                    <p class="text-muted mb-4">Quản lý thông tin cá nhân, bảo mật và cài đặt.</p>
                    <ul class="list-group border-0">
                        <li class="list-group-item border-0 <?= $currentTab == 'profile' ? 'active bg-dark text-white' : '' ?> d-flex align-items-center rounded-3" style="margin-bottom: 15px;">
                            <a href="?tab=profile" class="nav-link <?= $currentTab == 'profile' ? 'text-white' : 'text-dark' ?>">
                                <i class="fa fa-user me-2"></i> Thông tin cá nhân
                            </a>
                        </li>
                        <li class="list-group-item border-0 <?= $currentTab == 'password' ? 'active bg-dark text-white' : '' ?> d-flex align-items-center rounded-3">
                            <a href="?tab=password" class="nav-link <?= $currentTab == 'password' ? 'text-white' : 'text-dark' ?>">
                                <i class="fa fa-shield-alt me-2"></i> Mật khẩu và bảo mật
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Tab thông tin cá nhân -->
                <div class="profile-content shadow-sm" id="profile-tab" <?= $currentTab != 'profile' ? 'style="display: none;"' : '' ?>>
                    <h4 class="mb-4">Thông tin cá nhân</h4>

                    <div class="profile-item">
                        <strong>Họ và tên</strong>
                        <p class="mb-0"><?= htmlspecialchars($name) ?></p>
                    </div>

                    <div class="profile-item">
                        <strong>Email</strong>
                        <p class="mb-0"><?= htmlspecialchars($email) ?></p>
                    </div>

                    <div class="profile-item">
                        <strong>Vai trò</strong>
                        <p class="mb-0"><?= htmlspecialchars($role) ?></p>
                    </div>

                    <div class="profile-item d-flex align-items-center">
                        <strong class="me-4">Ảnh đại diện</strong>
                        <img src="<?= htmlspecialchars($avatar) ?>" alt="avatar" class="avatar-img">
                    </div>

                    <button class="btn btn-outline-primary mt-4" data-bs-toggle="modal" data-bs-target="#updateModal">Chỉnh sửa</button>
                </div>

                <!-- Tab đổi mật khẩu -->
                <div class="profile-content shadow-sm" id="password-tab" <?= $currentTab != 'password' ? 'style="display: none;"' : '' ?>>
                    <h4 class="mb-4">Đổi mật khẩu</h4>

                    <?php if (isGoogleLogin()): ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i> Bạn đang đăng nhập bằng tài khoản Google. Không thể đổi mật khẩu cho tài khoản này.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="processors/personal_info_process.php">
                            <input type="hidden" name="redirect_to" value="personal-infor.php?tab=password">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="change_password" value="1">
                            <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cập nhật thông tin -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="processors/personal_info_process.php" enctype="multipart/form-data" class="modal-content">
                <input type="hidden" name="redirect_to" value="personal-infor.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Cập nhật thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Ảnh đại diện</label>
                        <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Thêm CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="update_personal" value="1">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function goBack() {
            window.location.href = "http://localhost:8080/elearning_restructured_updated/public/"; // Điều hướng về trang chính
        }
    </script>
</body>

</html>