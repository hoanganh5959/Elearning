<?php
session_start();
require_once __DIR__ . '/../classes/update_personal.php';

if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
    require_once __DIR__ . '/../classes/Auth.php';
    $q = new Auth();
    $q->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen']);
} else {
    header('location: http://localhost:8080/elearning_restructured_updated/public/index.php');
}

// Giả sử dữ liệu người dùng đã được lưu trong session
$name = $_SESSION['name'] ?? 'Chưa có tên';
$email = $_SESSION['email'] ?? 'Chưa có email';
$role = $_SESSION['phanquyen'] ?? 'Chưa rõ';
$avatar = $_SESSION['avatar'] ?? 'assets/img/default-avatar.png';

// Xử lý cập nhật nếu form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personal'])) {
    $userId = $_SESSION['id'];
    $newName = $_POST['name'] ?? '';
    $avatarFile = $_FILES['avatar'] ?? null;

    $updater = new PersonalUpdater($conn);
    $result = $updater->update($userId, $newName, $avatarFile);

    if ($result['success']) {
        $_SESSION['name'] = $newName;
        if (!empty($result['avatar'])) {
            $_SESSION['avatar'] = $result['avatar'];
        }
        header("Location: personal-infor.php");
        exit;
    } else {
        $errorMsg = $result['message'];
    }
}
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
        }

        .list-group-item:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <button onclick="goBack()" class="btn-close position-absolute top-0 end-0 m-4" aria-label="Close"></button>

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
                        <li class="list-group-item border-0 active d-flex align-items-center rounded-3 bg-dark text-white">
                            <i class="fa fa-user me-2"></i> Thông tin cá nhân
                        </li>
                        <li class="list-group-item border-0 d-flex align-items-center">
                            <i class="fa fa-shield-alt me-2"></i> Mật khẩu và bảo mật
                        </li>
                    </ul>
                </div>
            </div>



            <div class="col-md-8">
                <div class="profile-content shadow-sm">
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
            </div>
        </div>
    </div>

    <!-- Modal Cập nhật thông tin -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" enctype="multipart/form-data" class="modal-content">
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
                        <input type="file" name="avatar" id="avatar" class="form-control">
                    </div>
                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger"><?= $errorMsg ?></div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
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
            window.history.back(); // Trở lại trang trước đó
        }
    </script>

</body>

</html>