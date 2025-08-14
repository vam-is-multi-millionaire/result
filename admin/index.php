<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT password FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'Invalid username.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Shree Bhawani Secondary School</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="login-page">
        <div class="login-half branding">
            <div class="branding-content">
                <h1>Welcome Back to the Admin Panel</h1>
                <p>Manage students, results, and more with ease.</p>
                 <div class="branding-footer">
                    <p>&copy; <?php echo date('Y'); ?> Shree Bhawani Secondary School</p>
                </div>
            </div>
        </div>
        <div class="login-half form-section">
            <div class="login-container">
                <div class="login-header">
                    <h2>Admin Login</h2>
                    <p>Please log in to your account.</p>
                </div>
                <form action="index.php" method="post" novalidate>
                    <?php if ($error): ?>
                        <div class="error-banner">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <i class="fas fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <div class="login-actions">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            Remember Me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" name="login" class="btn btn-login">
                        <span>Login</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
