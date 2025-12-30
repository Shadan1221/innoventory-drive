<?php
require_once "../../session.php";
require_once "../../config.php";

// ONLY ADMIN CAN ACCESS THIS PAGE
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

$msg = "";

// UPDATE USER DATA
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id     = $_POST["id"];
    $name   = $_POST["name"];
    $role   = $_POST["role"];
    $status = $_POST["status"];

    $query = $db->prepare("
        UPDATE users 
        SET name=?, role=?, status=? 
        WHERE id=?
    ");
    $query->bind_param("sssi", $name, $role, $status, $id);

    if ($query->execute()) {
        $msg = "User updated successfully!";
    } else {
        $msg = "Error updating user!";
    }

    $query->close();
}

// GET USERS FOR DROPDOWN DISPLAY
$users = mysqli_query($db, "SELECT id, name, email FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update User</title>
</head>
<body>

<h2>Update User</h2>

<?php if($msg) echo "<p style='color:green;'>$msg</p>"; ?>

<!-- SELECT USER -->
<form method="GET" action="">
    <select name="uid" required>
        <option value="">Select user</option>
        <?php while($u = mysqli_fetch_assoc($users)): ?>
            <option value="<?= $u['id']; ?>">
                <?= $u['name']; ?> (<?= $u['email']; ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Load User</button>
</form>

<hr>

<?php
// SHOW USER DETAILS FOR UPDATE
if(isset($_GET["uid"])):

    $uid = $_GET["uid"];
    $data = mysqli_query($db, "SELECT * FROM users WHERE id='$uid'");
    $user = mysqli_fetch_assoc($data);
?>

<form method="POST">

    <input type="hidden" name="id" value="<?= $user['id']; ?>">

    <label>Name:</label><br>
    <input type="text" name="name" value="<?= $user['name']; ?>" required><br><br>

    <label>Role:</label><br>
    <select name="role" required>
        <option value="user"  <?= ($user['role']=="user")  ? "selected" : "" ?>>User</option>
        <option value="admin" <?= ($user['role']=="admin") ? "selected" : "" ?>>Admin</option>
    </select><br><br>

    <label>Status:</label><br>
    <select name="status" required>
        <option value="pending"  <?= ($user['status']=="pending")  ? "selected" : "" ?>>Pending</option>
        <option value="approved" <?= ($user['status']=="approved") ? "selected" : "" ?>>Approved</option>
        <option value="denied"   <?= ($user['status']=="denied")   ? "selected" : "" ?>>Denied</option>
    </select><br><br>

    <button type="submit">Update User</button>
</form>

<?php endif; ?>

</body>
</html>
