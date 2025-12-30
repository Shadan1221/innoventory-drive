<?php
/**
 * TEMPORARY ADMIN CREATION SCRIPT
 * 
 * WARNING: DELETE THIS FILE AFTER CREATING YOUR ADMIN USER!
 * This file should NOT be accessible in production.
 */

require_once "config.php";

// Admin credentials - CHANGE THESE!
$name = "Super Admin";
$email = "admin@innoventory.com";
$password = "admin123"; // ⚠️ CHANGE THIS PASSWORD!
$role = "admin";
$status = "approved";

// Check if admin already exists
$check = $db->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("Admin user already exists! Please delete this file.");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert admin user
$stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $status);

if ($stmt->execute()) {
    echo "<h2>✅ Admin user created successfully!</h2>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Password:</strong> $password</p>";
    echo "<hr>";
    echo "<p style='color: red; font-weight: bold;'>⚠️ IMPORTANT: DELETE THIS FILE NOW!</p>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
} else {
    echo "<h2>❌ Error creating admin user</h2>";
    echo "<p>Error: " . $stmt->error . "</p>";
}

$stmt->close();
$check->close();
?>

