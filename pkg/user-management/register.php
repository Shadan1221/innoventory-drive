<?php
require_once "../../config.php"; // Go up two levels to find config

$msg = "";
$error = "";

// Get role from URL parameter (admin signup) or default to 'user' (access request)
$role = isset($_GET['role']) && $_GET['role'] === 'admin' ? 'admin' : 'user';
$isAdminSignup = $role === 'admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]); // Get role from hidden field
    
    // Check if email exists
    $check = $db->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email already exists.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Both admin and user requests start as 'pending'
        $status = 'pending';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $status);

        if ($stmt->execute()) {
            if ($role === 'admin') {
                $msg = "Admin registration request submitted! Please wait for approval from an existing admin.";
            } else {
                $msg = "Request submitted successfully! Please wait for Admin approval.";
            }
        } else {
            $error = "Error submitting request.";
        }
        $stmt->close();
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Access - Innoventory</title>
    <link rel="stylesheet" href="../../css/main.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f6f6f9;
            font-family: "Inter", sans-serif;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .card h3 {
            color: #16191f;
            margin-bottom: 20px;
            font-weight: 400;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #16191f;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 0;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #0073bb;
            box-shadow: 0 0 0 3px rgba(0, 115, 187, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #ff9900;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 400;
        }
        button:hover {
            background: #e68900;
        }
        .success {
            color: #059669;
            margin-bottom: 15px;
            padding: 10px;
            background: #d1fae5;
            border-radius: 4px;
            border: 1px solid #a7f3d0;
            font-size: 14px;
        }
        .error {
            color: #d13212;
            margin-bottom: 15px;
            padding: 10px;
            background: #fef2f2;
            border-radius: 4px;
            border: 1px solid #fecaca;
            font-size: 14px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #0073bb;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <h3><?php echo $isAdminSignup ? 'Admin Registration' : 'Request Access'; ?></h3>
        <?php if($msg) echo "<div class='success'>$msg</div>"; ?>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        
        <?php if ($isAdminSignup): ?>
            <div style="background: #fef3c7; color: #92400e; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 13px;">
                <strong>Note:</strong> Admin registration requires approval from an existing administrator.
            </div>
        <?php else: ?>
            <div style="background: #dbeafe; color: #1e40af; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 13px;">
                Submit a request for access. An administrator will review and approve your request.
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full Name" required autocomplete="name">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Create Password</label>
                <input type="password" id="password" name="password" placeholder="Create Password" required autocomplete="new-password" minlength="6">
            </div>
            <button type="submit"><?php echo $isAdminSignup ? 'Submit Admin Request' : 'Submit Request'; ?></button>
        </form>
        <a href="../../index.php" class="back-link">Back to Login</a>
    </div>
</body>
</html>
