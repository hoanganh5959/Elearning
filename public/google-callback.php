<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../classes/GoogleAuth.php';

$googleAuth = new GoogleAuth();
$userInfo = $googleAuth->handleCallback();

if (!$userInfo) {
    echo "Lỗi khi xác thực Google. Vui lòng thử lại.";
    exit();
}

$googleId = $userInfo['google_id'];
$email = $userInfo['email'];
$name = $userInfo['name'];
$avatar = $userInfo['avatar'];
$verified = $userInfo['verified_email'] ? 1 : 0;
$username = explode('@', $email)[0];
$role = 'student';

// Kiểm tra xem đã có user trong bảng users chưa và kiểm tra status
$stmt = $conn->prepare("SELECT user_id, username, name, avatar, role, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Kiểm tra trạng thái tài khoản
    if ($user['status'] === 'inactive') {
        $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
        header("Location: index.php");
        exit();
    }
    
    $userId = $user['user_id'];
    $role = $user['role'];

    // Chỉ update name/avatar nếu bị trống
    if (empty($user['name']) || $user['name'] === 'Chưa có tên') {
        $update = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
        $update->bind_param("si", $name, $userId);
        $update->execute();
    }

    if (empty($user['avatar']) || $user['avatar'] === 'assets/img/default-avatar.png') {
        $update = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $update->bind_param("si", $avatar, $userId);
        $update->execute();
    }
} else {
    // Chưa có user → tạo mới với status active
    $stmt = $conn->prepare("INSERT INTO users (username, name, email, avatar, password, role, status) VALUES (?, ?, ?, ?, '', ?, 'active')");
    $stmt->bind_param("sssss", $username, $name, $email, $avatar, $role);
    $stmt->execute();
    $userId = $stmt->insert_id;
}

// google_users: đã có hay chưa
$stmt = $conn->prepare("SELECT id FROM google_users WHERE google_id = ?");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$googleUser = $stmt->get_result();

if ($googleUser->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO google_users (user_id, google_id, email, name, avatar, verified_email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $userId, $googleId, $email, $name, $avatar, $verified);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("UPDATE google_users SET email=?, name=?, avatar=?, verified_email=?, last_login_at=NOW() WHERE google_id=?");
    $stmt->bind_param("sssis", $email, $name, $avatar, $verified, $googleId);
    $stmt->execute();
}

// Đồng bộ session từ DB users
$stmt = $conn->prepare("SELECT username, name, avatar FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$_SESSION['user_id'] = $userId;
$_SESSION['user'] = $user['username'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $email;
$_SESSION['avatar'] = $user['avatar'] ?: 'assets/img/default-avatar.png';
$_SESSION['phanquyen'] = $role;
$_SESSION['login_with'] = 'google';

// Đảm bảo session được lưu trước khi redirect
session_write_close();

header("Location: index.php");
// Flush để đảm bảo headers được gửi
if (ob_get_level()) {
    ob_end_flush();
}
flush();
exit();
