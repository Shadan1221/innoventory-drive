<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if config file exists
if (!file_exists("config.php")) {
    die("Error: config.php file not found. Please check your file structure.");
}

require_once "config.php";

// Check database connection
if (!$db) {
    die("Database connection failed: " . mysqli_connect_error() . "<br>Please check your database settings in config.php");
}

// 1. Handle Login Logic if Form Submitted
$error = "";

// Check for error messages from redirects
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'pending') {
        $error = "Your account is still pending approval.";
    } elseif ($_GET['error'] === 'denied') {
        $error = "Your access has been denied.";
    }
}

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
    <title>Sign In - Innoventory</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        body {
            font-family: "Amazon Ember", "Helvetica Neue", Arial, sans-serif;
            background: #f6f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h1 {
            font-size: 24px;
            font-weight: 400;
            margin-bottom: 30px;
            color: #16191f;
            text-align: left;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 400;
            color: #16191f;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #0073bb;
            box-shadow: 0 0 0 3px rgba(0, 115, 187, 0.1);
        }
        .password-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .checkbox-group label {
            font-size: 14px;
            color: #16191f;
            cursor: pointer;
            margin: 0;
        }
        .help-link {
            font-size: 14px;
            color: #0073bb;
            text-decoration: none;
            border-bottom: 1px dotted #0073bb;
        }
        .help-link:hover {
            text-decoration: none;
        }
        .btn-signin {
            width: 100%;
            padding: 12px;
            background: #ff9900;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 400;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .btn-signin:hover {
            background: #e68900;
        }
        .btn-signin-admin {
            width: 100%;
            padding: 12px;
            background: #ffffff;
            color: #16191f;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 400;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            box-sizing: border-box;
        }
        .btn-signin-admin:hover {
            background: #f9fafb;
        }
        .error-message {
            color: #d13212;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background: #fef2f2;
            border-radius: 4px;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Sign in</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">IAM username</label>
                <input type="email" id="email" name="email" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <div class="password-options">
                <div class="checkbox-group">
                    <input type="checkbox" id="showPassword" onchange="togglePassword()">
                    <label for="showPassword">Show Password</label>
                </div>
                <a href="#" class="help-link">Having trouble?</a>
            </div>

            <button type="submit" name="login_btn" class="btn-signin">Sign in</button>
        </form>

        <a href="pkg/user-management/register.php?role=admin" class="btn-signin-admin">Sign in using root user email</a>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showPasswordCheckbox = document.getElementById('showPassword');
            
            if (showPasswordCheckbox.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>
