<?php
require_once "../session.php";
require_once "../config.php";

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

// Get all pending requests (both users and admins)
$result = mysqli_query($db, "SELECT * FROM users WHERE status='pending' ORDER BY role, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Innoventory</title>
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
        h1 {
            color: #16191f;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .message.error {
            background: #fef2f2;
            color: #d13212;
            border: 1px solid #fecaca;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        table tr:hover {
            background: #f9fafb;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-badge.admin {
            background: #fef3c7;
            color: #92400e;
        }
        .role-badge.user {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }
        .action-links a {
            color: #0073bb;
            text-decoration: none;
            margin-right: 10px;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .action-links a.deny {
            color: #d13212;
        }
        .no-requests {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <p class="subtitle">Review and manage access requests</p>

        <?php if (isset($_GET["msg"])): ?>
            <div class="message <?php echo strpos($_GET["msg"], "Error") !== false ? "error" : "success"; ?>">
                <?php echo htmlspecialchars($_GET["msg"]); ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $hasRequests = false;
                while($row = mysqli_fetch_assoc($result)): 
                    $hasRequests = true;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row["name"]); ?></td>
                    <td><?= htmlspecialchars($row["email"]); ?></td>
                    <td>
                        <span class="role-badge <?= $row["role"]; ?>">
                            <?= ucfirst($row["role"]); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge pending">
                            <?= ucfirst($row["status"]); ?>
                        </span>
                    </td>
                    <td class="action-links">
                        <a href="approve.php?id=<?= $row['id'] ?>">Approve</a>
                        <a href="deny.php?id=<?= $row['id'] ?>" class="deny">Deny</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (!$hasRequests): ?>
                <tr>
                    <td colspan="5" class="no-requests">
                        No pending requests at this time.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
