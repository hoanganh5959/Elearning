<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/Auth.php';

// Khai báo các biến
$step = isset($_GET['step']) ? $_GET['step'] : 'email';
$error = '';
$success = '';
$email = '';
$userId = 0;

// Khởi tạo Auth
$auth = new Auth();

// Xử lý yêu cầu đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bước 1: Nhập email
    if (isset($_POST['request_reset'])) {
        $email = trim($_POST['email']);

        if (empty($email)) {
            $error = 'Vui lòng nhập email của bạn.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ.';
        } else {
            // Kiểm tra email có tồn tại trong hệ thống
            $user = $auth->checkEmailExists($email);

            if (!$user) {
                $error = 'Email này chưa được đăng ký trong hệ thống.';
            } else {
                // Tạo mã xác nhận và gửi email
                $token = $auth->createResetToken($user['user_id']);
                $success = $auth->sendResetPasswordEmail($email, $token, $user['username']);

                if ($success) {
                    // Lưu thông tin vào session để chuyển sang bước tiếp theo
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_user_id'] = $user['user_id'];

                    // Chuyển sang bước nhập mã xác nhận
                    header("Location: forgot-password.php?step=verify");
                    exit();
                } else {
                    $error = 'Không thể gửi email. Vui lòng thử lại sau.';
                }
            }
        }
    }

    // Bước 2: Xác thực mã
    if (isset($_POST['verify_code'])) {
        $token = trim($_POST['token']);
        $userId = $_SESSION['reset_user_id'] ?? 0;

        if (empty($token)) {
            $error = 'Vui lòng nhập mã xác nhận.';
        } elseif (empty($userId)) {
            $error = 'Phiên làm việc hết hạn. Vui lòng thử lại.';
            $step = 'email'; // Quay lại bước nhập email
        } else {
            // Xác thực mã
            $verified = $auth->verifyResetToken($userId, $token);

            if ($verified) {
                // Chuyển sang bước đặt lại mật khẩu
                header("Location: forgot-password.php?step=reset");
                exit();
            } else {
                $error = 'Mã xác nhận không hợp lệ hoặc đã hết hạn.';
            }
        }
    }

    // Bước 3: Đặt lại mật khẩu
    if (isset($_POST['reset_password'])) {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['reset_user_id'] ?? 0;

        if (empty($password)) {
            $error = 'Vui lòng nhập mật khẩu mới.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } elseif (empty($userId)) {
            $error = 'Phiên làm việc hết hạn. Vui lòng thử lại.';
            $step = 'email'; // Quay lại bước nhập email
        } else {
            // Đặt lại mật khẩu
            $result = $auth->resetPassword($userId, $password);

            if ($result) {
                // Xóa dữ liệu phiên reset
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_user_id']);

                // Hiển thị thông báo thành công
                setFlashMessage('Đặt lại mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.', 'success');
                header("Location: http://localhost:8080/elearning_restructured_updated/public/");
                exit();
            } else {
                $error = 'Không thể đặt lại mật khẩu. Vui lòng thử lại sau.';
            }
        }
    }

    // Gửi lại mã xác nhận
    if (isset($_POST['resend_code'])) {
        $email = $_SESSION['reset_email'] ?? '';
        $userId = $_SESSION['reset_user_id'] ?? 0;

        if (empty($email) || empty($userId)) {
            $error = 'Phiên làm việc hết hạn. Vui lòng thử lại.';
            $step = 'email'; // Quay lại bước nhập email
        } else {
            $user = $auth->checkEmailExists($email);
            if ($user) {
                $token = $auth->createResetToken($userId);
                $success = $auth->sendResetPasswordEmail($email, $token, $user['username']);

                if ($success) {
                    $success = 'Mã xác nhận mới đã được gửi đến email của bạn.';
                } else {
                    $error = 'Không thể gửi email. Vui lòng thử lại sau.';
                }
            } else {
                $error = 'Email không hợp lệ.';
                $step = 'email'; // Quay lại bước nhập email
            }
        }
    }
}

// Lấy dữ liệu từ session
if ($step === 'verify' || $step === 'reset') {
    $email = $_SESSION['reset_email'] ?? '';
    $userId = $_SESSION['reset_user_id'] ?? 0;

    if (empty($email) || empty($userId)) {
        setFlashMessage('Phiên làm việc hết hạn. Vui lòng thử lại.', 'danger');
        header("Location: forgot-password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - eLEARNING</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .forgot-password-form {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -10px;
            width: 20px;
            height: 2px;
            background-color: #ddd;
            z-index: 0;
        }

        .step:last-child::after {
            display: none;
        }

        .step.active {
            color: #06BBCC;
            font-weight: bold;
        }

        .step.completed {
            color: #28a745;
        }

        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: #f0f0f0;
            margin-bottom: 5px;
        }

        .step.active .step-number {
            background-color: #06BBCC;
            color: white;
        }

        .step.completed .step-number {
            background-color: #28a745;
            color: white;
        }

        .timer {
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="forgot-password-form">
        <span class="close-btn" onclick="window.location.href='index.php'">&times;</span>

        <h3 class="text-center mb-4">Quên mật khẩu</h3>

        <div class="step-indicator">
            <div class="step <?= $step === 'email' ? 'active' : ($step === 'verify' || $step === 'reset' ? 'completed' : '') ?>">
                <div class="step-number">1</div>
                <small>Nhập Email</small>
            </div>
            <div class="step <?= $step === 'verify' ? 'active' : ($step === 'reset' ? 'completed' : '') ?>">
                <div class="step-number">2</div>
                <small>Xác thực</small>
            </div>
            <div class="step <?= $step === 'reset' ? 'active' : '' ?>">
                <div class="step-number">3</div>
                <small>Đặt lại mật khẩu</small>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($step === 'email'): ?>
            <!-- Bước 1: Nhập email -->
            <form method="post" action="forgot-password.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email của bạn" required value="<?= htmlspecialchars($email) ?>">
                </div>
                <div class="text-center">
                    <button type="submit" name="request_reset" class="btn btn-primary btn-block">Gửi yêu cầu</button>
                </div>
                <div class="mt-3 text-center">
                    <a href="javascript:void(0)" onclick="showLogin()" class="text-decoration-none">Quay lại đăng nhập</a>
                </div>
            </form>
        <?php elseif ($step === 'verify'): ?>
            <!-- Bước 2: Nhập mã xác nhận -->
            <form method="post" action="forgot-password.php?step=verify">
                <p class="mb-4">Chúng tôi đã gửi mã xác nhận đến email <strong><?= htmlspecialchars($email) ?></strong>. Vui lòng kiểm tra hộp thư đến của bạn.</p>

                <div class="mb-3">
                    <label for="token" class="form-label">Mã xác nhận</label>
                    <input type="text" class="form-control" id="token" name="token" placeholder="Nhập mã xác nhận 6 số" required maxlength="6" pattern="[0-9]{6}">
                </div>

                <div class="timer mb-3" id="timer">
                    Có thể gửi lại mã sau <span id="countdown">60</span> giây
                </div>

                <div class="text-center">
                    <button type="submit" name="verify_code" class="btn btn-primary btn-block mb-2">Xác nhận</button>
                    <button type="submit" name="resend_code" id="resend_btn" class="btn btn-link btn-block" disabled>Gửi lại mã</button>
                </div>
            </form>
        <?php elseif ($step === 'reset'): ?>
            <!-- Bước 3: Đặt lại mật khẩu -->
            <form method="post" action="forgot-password.php?step=reset">
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu mới</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu mới" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                </div>
                <div class="text-center">
                    <button type="submit" name="reset_password" class="btn btn-primary btn-block">Đặt lại mật khẩu</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Đếm ngược 60 giây cho chức năng gửi lại mã
        function startCountdown() {
            let timerElement = document.getElementById('countdown');
            let resendButton = document.getElementById('resend_btn');

            if (!timerElement || !resendButton) return;

            let seconds = 60;
            timerElement.textContent = seconds;

            let countdownInterval = setInterval(function() {
                seconds--;
                timerElement.textContent = seconds;

                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('timer').innerHTML = 'Không nhận được mã?';
                    resendButton.disabled = false;
                }
            }, 1000);
        }

        // Khởi động đếm ngược khi trang tải xong
        <?php if ($step === 'verify'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                startCountdown();
            });
        <?php endif; ?>

        // Mở modal đăng nhập
        function showLogin() {
            window.location.href = 'login.php';
        }
    </script>
</body>

</html>