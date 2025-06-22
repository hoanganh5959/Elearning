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
$username = explode('@', $email)[0]; // luôn tách username từ email
$role = 'student';

// 1. Kiểm tra xem đã có user nào trong bảng users với email này chưa
$stmt = $conn->prepare("SELECT id, username, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Đã tồn tại → cập nhật name + username mới từ Google
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $role = $user['role'];

    $update = $conn->prepare("UPDATE users SET avatar = ?, name = ? WHERE id = ?");
    $update->bind_param("ssi", $avatar, $name, $userId);
    $update->execute();
} else {
    // Chưa có → tạo mới user
    $stmt = $conn->prepare("INSERT INTO users (username, name, email, avatar, password, role) VALUES (?, ?, ?, ?, '', ?)");
    $stmt->bind_param("sssss", $username, $name, $email, $avatar, $role);
    $stmt->execute();
    $userId = $stmt->insert_id;
}

// 2. Kiểm tra trong bảng google_users theo google_id
$stmt = $conn->prepare("SELECT id FROM google_users WHERE google_id = ?");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$googleUser = $stmt->get_result();

if ($googleUser->num_rows === 0) {
    // Chưa có → thêm
    $stmt = $conn->prepare("INSERT INTO google_users (user_id, google_id, email, name, avatar, verified_email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $userId, $googleId, $email, $name, $avatar, $verified);
    $stmt->execute();
} else {
    // Đã có → cập nhật
    $stmt = $conn->prepare("UPDATE google_users SET email=?, name=?, avatar=?, verified_email=?, last_login_at=NOW() WHERE google_id=?");
    $stmt->bind_param("sssis", $email, $name, $avatar, $verified, $googleId);
    $stmt->execute();
}

// 3. Tạo session từ bảng users
$_SESSION['id'] = $userId;
$_SESSION['user'] = $username;
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['avatar'] = $avatar;
$_SESSION['phanquyen'] = $role;
$_SESSION['login_with'] = 'google';

header("Location: index.php");
exit();
