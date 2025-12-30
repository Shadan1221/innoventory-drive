<?php
session_start();
require_once "config.php";

// 1. Handle Login Logic if Form Submitted
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $db->prepare("SELECT id, name, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password, $role, $status);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Check Status
            if ($status === 'pending') {
                $error = "Your account is still pending approval.";
            } elseif ($status === 'denied') {
                $error = "Your access has been denied.";
            } else {
                // Success - Set Session
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["name"] = $name;
                $_SESSION["role"] = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: pkg/user-management/admin_dashboard.php");
                } else {
                    header("Location: pkg/user-management/user_dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}

// 2. If already logged in, redirect to appropriate dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // FETCH USER FROM DB (TO CHECK STATUS)
    $id = $_SESSION["id"];
    $query = $db->prepare("SELECT status, role FROM users WHERE id=? LIMIT 1");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    // STATUS CHECK
    if ($user["status"] === "pending") {
        // Could redirect to a pending page if it exists
        $error = "Your account is still pending approval.";
    } elseif ($user["status"] === "denied") {
        $error = "Your access has been denied.";
    } else {
        // ROLE CHECK
        if ($user["role"] === "admin") {
            header("Location: pkg/user-management/admin_dashboard.php");
            exit;
        } elseif ($user["role"] === "user") {
            header("Location: pkg/user-management/user_dashboard.php");
            exit;
        }
    }
    $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innoventory - Login</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        body {
            font-family: "Inter", sans-serif;
            background: #f6f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            color: #16191f;
            margin-bottom: 30px;
            font-weight: 400;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #16191f;
            font-weight: 400;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 0;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #0073bb;
            box-shadow: 0 0 0 3px rgba(0, 115, 187, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #ff9900;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 400;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #e68900;
        }
        .divider {
            margin: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .btn-register {
            display: inline-block;
            text-decoration: none;
            color: #0073bb;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-register:hover {
            text-decoration: underline;
        }
        .error-msg {
            color: #d13212;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background: #fef2f2;
            border-radius: 4px;
            border: 1px solid #fecaca;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Innoventory Login</h2>
    
    <?php if($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Email Address" required autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
        </div>
        <button type="submit" name="login_btn" class="btn-login">Sign In</button>
    </form>

    <div class="divider"></div>

    <p style="color: #6b7280; font-size: 14px; margin: 0;">Don't have access?</p>
    <a href="pkg/user-management/register.php" class="btn-register">Request Access</a>
</div>

</body>
</html>
