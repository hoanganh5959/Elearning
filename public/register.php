<?php
require_once "../classes/Auth.php";

$message = "";
$shouldShake = false;

// Hiển thị thông báo từ session nếu có
if (isset($_SESSION['register_error'])) {
    $message = $_SESSION['register_error'];
    $shouldShake = true;
    unset($_SESSION['register_error']);
} elseif (isset($_SESSION['register_success'])) {
    $message = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}
?>

<!-- Register Popup -->
<div class="overlay" id="registerOverlay" onclick="hideRegister()">
    <div class="login-box" id="registerBox" onclick="event.stopPropagation();">
        <div class="header-register">
            <span class="arrow" onclick="goBackToLogin()">←</span>
            <span class="title">Đăng ký tài khoản</span>
        </div>

        <form method="POST" action="processors/register_process.php" onsubmit="return validateForm();">
            <input type="hidden" name="redirect_to" value="register.php">
            <input type="text" name="name" class="form-control" placeholder="Họ và tên" required>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" id="password" class="form-control" placeholder="Mật khẩu" required>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
            <input type="hidden" name="role" value="student">

            <button type="submit" class="btn btn-success w-100 mt-2">Đăng ký</button>
            <?php if (!empty($message)) echo "<p style='color:red; margin-top:10px;'>$message</p>"; ?>
        </form>

        <p>
            Đã có tài khoản?
            <a href="javascript:void(0)" onclick="goBackToLogin()" class="d-block">
                <strong>Đăng nhập</strong>
            </a>
        </p>
    </div>
</div>

<style>
    .header-register {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin-bottom: 25px;
    }

    .header-register .arrow {
        position: absolute;
        left: 0;
        font-size: 22px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .header-register .arrow:hover {
        transform: translateX(-4px);
    }

    .header-register .title {
        font-size: 20px;
        font-weight: bold;
    }

    .shake {
        animation: shake 0.3s ease-in-out;
    }

    @keyframes shake {
        0% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-8px);
        }

        50% {
            transform: translateX(8px);
        }

        75% {
            transform: translateX(-6px);
        }

        100% {
            transform: translateX(0);
        }
    }
</style>

<script>
    function showLogin() {
        document.getElementById('loginOverlay').style.display = 'flex';
        document.getElementById('registerOverlay').style.display = 'none';
    }

    function hideLogin() {
        document.getElementById('loginOverlay').style.display = 'none';
    }

    function showRegister() {
        document.getElementById('registerOverlay').style.display = 'flex';
        document.getElementById('loginOverlay').style.display = 'none';
    }

    function hideRegister() {
        document.getElementById('registerOverlay').style.display = 'none';
    }

    function goBackToLogin() {
        hideRegister();
        showLogin();
    }

    function validateForm() {
        const pw = document.getElementById("password").value;
        const confirmPw = document.getElementById("confirm_password").value;
        if (pw !== confirmPw) {
            alert("Mật khẩu không khớp. Vui lòng nhập lại.");
            return false;
        }
        return true;
    }

    // Hiển thị popup dựa theo URL hoặc lỗi phía server
    window.onload = function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get("popup") === "register") {
            showRegister();
        } else if (params.get("popup") === "login") {
            showLogin();
        }
    };
</script>

<?php if (!empty($message)): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            showRegister(); // Hiện lại form khi có lỗi
            <?php if ($shouldShake): ?>
                const registerBox = document.getElementById("registerBox");
                registerBox.classList.add("shake");
                setTimeout(() => registerBox.classList.remove("shake"), 600);
            <?php endif; ?>
        });
    </script>
<?php endif; ?>