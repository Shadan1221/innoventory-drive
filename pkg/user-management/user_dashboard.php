<?php
require_once "../../session.php";
require_once "../../config.php";

// Check if user is logged in and is a user (not admin)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../../index.php");
    exit;
}

// Check user status
$id = $_SESSION["id"];
$query = $db->prepare("SELECT status, role FROM users WHERE id=? LIMIT 1");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if ($user["role"] === "admin") {
    header("Location: admin_dashboard.php");
    exit;
}

if ($user["status"] === "pending") {
    header("Location: ../../index.php?error=pending");
    exit;
}

if ($user["status"] === "denied") {
    header("Location: ../../index.php?error=denied");
    exit;
}

$query->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Innoventory</title>
    <link rel="stylesheet" href="../../css/main.css">
    <style>
        body {
            background: #f6f6f9;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .header-left h1 {
            margin: 0;
            color: #16191f;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info {
            color: #6b7280;
            font-size: 14px;
        }
        .btn-logout {
            padding: 8px 16px;
            background: #d13212;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background: #b0280f;
        }
        .welcome-message {
            background: #dbeafe;
            color: #1e40af;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }
        .welcome-message h2 {
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        .welcome-message p {
            margin: 0;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header-section">
            <div class="header-left">
                <h1>User Dashboard</h1>
            </div>
            <div class="header-right">
                <span class="user-info">Welcome, <?= htmlspecialchars($_SESSION["name"] ?? "User"); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <div class="welcome-message">
            <h2>Welcome to Innoventory!</h2>
            <p>You have successfully logged in. Your account has been approved and you can now access the system.</p>
        </div>

        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <p>More features coming soon...</p>
        </div>
    </div>
</body>
</html>
