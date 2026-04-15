<?php
session_start();
require_once __DIR__ . '/../config/site_config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$remember_me = false;

// Check for remember me cookie
if (isset($_COOKIE['lsuc_admin_remember'])) {
    $remember_me = true;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Default credentials: admin / admin123
    // In production, use password_hash() and store in database/file
    $valid_username = 'admin';
    $valid_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    if ($username === $valid_username && password_verify($password, $valid_password_hash)) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        
        // Set remember me cookie (30 days)
        if ($remember) {
            setcookie('lsuc_admin_remember', 'true', time() + (30 * 24 * 60 * 60), '/');
        }
        
        // Log activity
        logAdminActivity($username, 'Login successful');
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
        logAdminActivity($username, 'Login failed', 'failed');
    }
}

function logAdminActivity($username, $action, $status = 'success') {
    $log_file = __DIR__ . '/data/activity_log.json';
    $logs = [];
    
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
    }
    
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'username' => $username,
        'action' => $action,
        'status' => $status,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    // Keep last 1000 logs
    $logs = array_slice($logs, -1000);
    
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e5a3a 0%, #2e8b57 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #ff8c00, #ffb347);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
        }
        
        .login-header h1 {
            color: #1e5a3a;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .input-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .input-icon input:focus {
            outline: none;
            border-color: #ff8c00;
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        .forgot-password {
            color: #2e8b57;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #1e5a3a;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2e8b57, #1e5a3a);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(46, 139, 87, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-to-site a {
            color: #2e8b57;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .back-to-site a:hover {
            color: #1e5a3a;
        }
        
        .security-note {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1>Admin Panel</h1>
            <p><?php echo SITE_NAME; ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
            </div>
            
            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember" <?php echo $remember_me ? 'checked' : ''; ?>>
                    Remember me
                </label>
                <a href="#" class="forgot-password" onclick="alert('Contact system administrator to reset password')">Forgot Password?</a>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>
        </form>
        
        <div class="back-to-site">
            <a href="../index.html">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
        </div>
        
        <div class="security-note">
            <i class="fas fa-shield-alt"></i> Secure Admin Portal | Session expires after 30 minutes
        </div>
    </div>
    
    <script>
        // Auto-hide error messages after 5 seconds
        setTimeout(function() {
            const errorDiv = document.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.style.transition = 'opacity 0.5s ease';
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>
