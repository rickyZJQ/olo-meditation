<?php
/**
 * 后台管理 - 登录页
 */
require_once __DIR__ . '/../config.php';

// 处理退出登录
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: index.php');
    exit;
}

// 已登录则跳转
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLO冥想 - 后台管理</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: #0a0e1a;
            color: #e8eaf6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 40px;
            width: 360px;
            max-width: 90vw;
        }
        .login-box h1 {
            font-size: 22px;
            font-weight: 400;
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: 4px;
        }
        .login-box p {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #e8eaf6;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #7c3aed;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            margin-top: 8px;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; }
        .error-msg {
            color: #ef4444;
            font-size: 13px;
            text-align: center;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>OLO 冥想</h1>
        <p>后台管理系统</p>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">登 录</button>
        </form>
    </div>
</body>
</html>
