<?php
require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Thêm autoload để sử dụng PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Auth
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function myLogin($username, $password, $redirect)
    {
        // Kiểm tra thông tin đăng nhập và lấy cả status
        $stmt = $this->conn->prepare("SELECT user_id, username, name, email, password, role, avatar, status FROM users WHERE username = ? AND password = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra trạng thái tài khoản
            if ($user['status'] === 'inactive') {
                return -1; // Trả về -1 để báo tài khoản bị khóa
            }
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['pass'] = $user['password'];
            $_SESSION['phanquyen'] = $user['role'];

            $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : '<div class="">assets/img/default-avatar.png';

            // Đảm bảo session được lưu trước khi redirect  
            session_write_close();
            
            header("Location: $redirect");
            // Flush để đảm bảo headers được gửi
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
            exit();
        } else {
            return 0; // Trả về 0 cho sai username/password
        }
    }

    // public function confirmLogin($id, $user, $pass, $phanquyen)
    // {
    //     $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND username = ? AND password = ? AND role = ? LIMIT 1");
    //     $stmt->bind_param("isss", $id, $user, $pass, $phanquyen);
    //     $stmt->execute();
    //     $stmt->store_result();

    //     if ($stmt->num_rows !== 1) {
    //         header("Location: ../login.php");
    //         exit();
    //     }
    // }

    // public function confirmGoogleLogin($id, $user, $phanquyen)
    // {
    //     $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND username = ? AND role = ? LIMIT 1");
    //     $stmt->bind_param("iss", $id, $user, $phanquyen);
    //     $stmt->execute();
    //     $stmt->store_result();

    //     if ($stmt->num_rows !== 1) {
    //         header("Location: ../login.php");
    //         exit();
    //     }
    // }

    public function registerUser($username, $email, $name, $password)
    {
        // Kiểm tra username hoặc email đã tồn tại
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return "Tài khoản hoặc email đã tồn tại!";
        }

        // Mã hóa mật khẩu nếu cần
        // $password = md5($password); // nếu bạn chưa dùng password_hash

        // Chèn tài khoản mới
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, name, password, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->bind_param("ssss", $username, $email, $name, $password);

        if ($stmt->execute()) {
            return true;
        } else {
            return "Lỗi khi đăng ký: " . $stmt->error;
        }
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        // Kiểm tra mật khẩu cũ
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows !== 1) {
            return "Tài khoản không tồn tại.";
        }
        $user = $result->fetch_assoc();
        if ($user['password'] !== $oldPassword) {
            return "Mật khẩu cũ không đúng.";
        }
        // Cập nhật mật khẩu mới
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newPassword, $userId);
        if ($stmt->execute()) {
            return true;
        } else {
            return "Lỗi khi đổi mật khẩu: " . $stmt->error;
        }
    }

    /**
     * Kiểm tra email tồn tại trong hệ thống chưa
     * 
     * @param string $email Email cần kiểm tra
     * @return bool|array false nếu không tìm thấy, hoặc mảng thông tin người dùng nếu tìm thấy
     */
    public function checkEmailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT user_id, username, email FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }

        return false;
    }

    /**
     * Tạo và lưu mã xác nhận cho quên mật khẩu
     * 
     * @param int $userId ID người dùng
     * @return string Mã xác nhận
     */
    public function createResetToken($userId)
    {
        // Tạo mã xác nhận 6 chữ số
        $resetToken = sprintf("%06d", mt_rand(1, 999999));
        $expireTime = date('Y-m-d H:i:s', strtotime('+1 hour')); // Hết hạn sau 1 giờ

        // Kiểm tra bảng password_resets đã tồn tại chưa
        $query = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(10) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )";
        $this->conn->query($query);

        // Xóa token cũ nếu có
        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Lưu token mới
        $stmt = $this->conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $resetToken, $expireTime);
        $stmt->execute();

        return $resetToken;
    }

    /**
     * Gửi email chứa mã xác nhận quên mật khẩu
     * 
     * @param string $email Email người nhận
     * @param string $token Mã xác nhận
     * @param string $name Tên người nhận
     * @return bool Trạng thái gửi email
     */
    public function sendResetPasswordEmail($email, $token, $name = '')
    {
        $mail = new PHPMailer(true);

        try {
            // Cấu hình server
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hoanganh5923@gmail.com'; // Thay bằng email của bạn
            $mail->Password = 'hwpv yxpf mhhq xptj'; // Thay bằng mật khẩu ứng dụng của bạn
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Người nhận
            $mail->setFrom('email@gmail.com', 'eLEARNING');
            $mail->addAddress($email, $name);

            // Nội dung
            $mail->isHTML(true);
            $mail->Subject = 'Mã xác nhận đặt lại mật khẩu';
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
                    <h2 style="color: #4285f4; text-align: center;">eLEARNING</h2>
                    <p>Xin chào ' . $name . ',</p>
                    <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                    <p>Mã xác nhận của bạn là: <b style="font-size: 18px; letter-spacing: 2px;">' . $token . '</b></p>
                    <p>Mã này sẽ hết hạn sau 1 giờ.</p>
                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                    <p>Trân trọng,<br>Đội ngũ eLEARNING</p>
                </div>
            ';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Không thể gửi email: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Xác thực mã token
     * 
     * @param int $userId ID người dùng
     * @param string $token Mã xác nhận
     * @return bool Kết quả xác thực
     */
    public function verifyResetToken($userId, $token)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM password_resets 
            WHERE user_id = ? AND token = ? AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->bind_param("is", $userId, $token);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows === 1;
    }

    /**
     * Đặt lại mật khẩu mới
     * 
     * @param int $userId ID người dùng
     * @param string $newPassword Mật khẩu mới
     * @return bool Kết quả cập nhật
     */
    public function resetPassword($userId, $newPassword)
    {
        // Cập nhật mật khẩu
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newPassword, $userId);

        if (!$stmt->execute()) {
            return false;
        }

        // Xóa token sau khi sử dụng
        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return true;
    }
}
