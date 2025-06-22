<!--Google-->
<?php
require_once "../classes/GoogleAuth.php";
$google = new GoogleAuth();
$googleLoginUrl = $google->getLoginUrl();

?>

<!-- Login Popup -->
<div class="overlay" id="loginOverlay" onclick="hideLogin()">
    <div class="login-box" onclick="event.stopPropagation();">
        <h4 class="mb-4">Đăng nhập vào hệ thống</h4>

        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= $_SESSION['login_error'] ?>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <form method="POST" action="processors/login_process.php">
            <?php 
            // Lấy redirect URL từ URL parameter hoặc từ trang hiện tại
            $redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : basename($_SERVER['PHP_SELF']);
            
            // Đảm bảo redirect_to là tên file hợp lệ, không chứa thư mục
            $redirect_to = basename($redirect_to);
            
            // Nếu redirect_to không phải là trang hợp lệ, mặc định về index.php
            $valid_pages = ['index.php', 'courses.php', 'course-detail.php', 'contact.php', 'about.php', 'blogs.php', 'my-blogs.php', 'write-blog.php', 'blog-detail.php'];
            if (!in_array($redirect_to, $valid_pages)) {
                $redirect_to = 'index.php';
            }
            ?>
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
            <input type="text" name="user" class="form-control" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
            <button type="submit" class="btn btn-primary w-100" name="nut" id="nut">Đăng nhập</button>
        </form>
        <a href="<?= $googleLoginUrl ?>" class="btn btn-google w-100 mt-3">
            <img src="../public/assets/img/google-icon.svg" alt="Google"> Đăng nhập với Google
        </a>

        <p>
            Bạn chưa có tài khoản?
            <a href="javascript:void(0)" onclick="showRegister()"><strong>Đăng ký</strong></a>
        </p>

        <a href="forgot-password.php"><strong>Quên mật khẩu</strong></a>
    </div>
</div>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .login-box {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.5s ease;
    }

    .login-box input,
    .login-box button {
        margin-bottom: 15px;
        border-radius: 44px;
        padding: 12px 20px;
    }

    .login-box button {
        font-family: 'Nunito', sans-serif;
        font-weight: bold;
        transition: .5s;
    }

    .login-box .alert {
        border-radius: 25px;
        margin-bottom: 15px;
        text-align: center;
        font-weight: 600;
    }

    .login-box .btn-google {
        background: white;
        border: 2px solid #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-google img {
        width: 20px;
        margin-right: 10px;
    }

    .login-box p {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        /* khoảng cách giữa văn bản và link */
        flex-wrap: wrap;
        margin-bottom: 0;
        font-weight: bold;
    }

    /* .login-box a {
        display: inline;
        color: #2aa5c0;
        font-weight: bold;
        text-decoration: none;
    } */

    .login-box a {
        display: inline;
        text-decoration: none;
        font-weight: bold;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.5);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<script>
    function showLogin(redirectUrl) {
        if (redirectUrl) {
            // Cập nhật hidden field với redirect URL mới
            var redirectInput = document.querySelector('input[name="redirect_to"]');
            if (redirectInput) {
                redirectInput.value = redirectUrl;
            }
        }
        document.getElementById('loginOverlay').style.display = 'flex';
    }

    function hideLogin() {
        document.getElementById('loginOverlay').style.display = 'none';
    }
</script>