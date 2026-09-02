<?php
session_start();
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password_hash FROM ussd_admins WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login � Factory Stock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="/mubeetech_icon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-container {
            background: #ffffff;
            border-radius: 22px;
            padding: 3rem 2.5rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header .logo {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: #1E2A5E;
        }
        .login-header .logo span { color: #2563eb; }
        .login-header .sub {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
        .login-header .badge {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 1rem;
        }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.25s, box-shadow 0.25s;
            background: #f8fafc;
            color: #1e293b;
            outline: none;
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
            background: #ffffff;
        }
        .form-group input::placeholder { color: #94a3b8; }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: #1E2A5E;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.25s, transform 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover { background: #15203F; transform: translateY(-2px); }
        .error-msg {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .error-msg i { font-size: 0.9rem; }
        .note {
            text-align: center;
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }
        .note strong { color: #1e293b; }
        @media (max-width: 480px) {
            .login-container { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="badge"><i class="fas fa-lock"></i> Admin Access</div>
            <div class="logo">Mubee<span>Tech</span></div>
            <div class="sub">Factory Stock Check � USSD Admin</div>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-arrow-right-to-bracket"></i> Login</button>
        </form>
        <div class="note">Default: <strong>admin</strong> / <strong>admin123</strong></div>
    </div>
</body>
</html>