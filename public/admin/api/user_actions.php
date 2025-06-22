<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../classes/manage_user.php';

// Kiểm tra quyền admin
requireLogin(['admin']);

$userManager = new UserManager();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Chỉ chấp nhận method POST';
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'role' => $_POST['role'] ?? 'student',
                'status' => $_POST['status'] ?? 'active'
            ];

            // Validate dữ liệu
            if (empty($data['username']) || empty($data['email']) || empty($data['name']) || empty($data['password'])) {
                throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ');
            }

            if (!in_array($data['role'], ['student', 'instructor', 'admin'])) {
                throw new Exception('Vai trò không hợp lệ');
            }

            $result = $userManager->addUser($data);
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Thêm người dùng thành công';
                $response['user_id'] = $result;
            } else {
                throw new Exception('Không thể thêm người dùng. Username hoặc email có thể đã tồn tại.');
            }
            break;

        case 'edit':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                throw new Exception('ID người dùng không hợp lệ');
            }

            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role' => $_POST['role'] ?? 'student',
                'status' => $_POST['status'] ?? 'active'
            ];

            // Validate dữ liệu
            if (empty($data['username']) || empty($data['email']) || empty($data['name'])) {
                throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ');
            }

            if (!in_array($data['role'], ['student', 'instructor', 'admin'])) {
                throw new Exception('Vai trò không hợp lệ');
            }

            if ($userManager->updateUser($user_id, $data)) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật người dùng thành công';
            } else {
                throw new Exception('Không thể cập nhật người dùng. Username hoặc email có thể đã tồn tại.');
            }
            break;

        case 'delete':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                throw new Exception('ID người dùng không hợp lệ');
            }

            // Không cho phép xóa chính mình
            if ($user_id == $_SESSION['user_id']) {
                throw new Exception('Không thể xóa chính mình');
            }

            if ($userManager->deleteUser($user_id)) {
                $response['success'] = true;
                $response['message'] = 'Xóa người dùng thành công';
            } else {
                throw new Exception('Không thể xóa người dùng. Có thể đây là tài khoản admin hoặc có lỗi xảy ra.');
            }
            break;

        case 'toggle_status':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                throw new Exception('ID người dùng không hợp lệ');
            }

            // Không cho phép khóa chính mình
            if ($user_id == $_SESSION['user_id']) {
                throw new Exception('Không thể thay đổi trạng thái của chính mình');
            }

            if ($userManager->toggleUserStatus($user_id)) {
                $response['success'] = true;
                $response['message'] = 'Thay đổi trạng thái tài khoản thành công';
            } else {
                throw new Exception('Không thể thay đổi trạng thái tài khoản');
            }
            break;

        case 'change_password':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $new_password = trim($_POST['new_password'] ?? '');

            if ($user_id <= 0) {
                throw new Exception('ID người dùng không hợp lệ');
            }

            if (empty($new_password)) {
                throw new Exception('Mật khẩu mới không được để trống');
            }

            if (strlen($new_password) < 3) {
                throw new Exception('Mật khẩu phải có ít nhất 3 ký tự');
            }

            if ($userManager->changePassword($user_id, $new_password)) {
                $response['success'] = true;
                $response['message'] = 'Đổi mật khẩu thành công';
            } else {
                throw new Exception('Không thể đổi mật khẩu');
            }
            break;

        case 'get_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                throw new Exception('ID người dùng không hợp lệ');
            }

            $user = $userManager->getUserById($user_id);
            if ($user) {
                $response['success'] = true;
                $response['user'] = $user;
            } else {
                throw new Exception('Không tìm thấy người dùng');
            }
            break;

        default:
            throw new Exception('Hành động không hợp lệ');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
