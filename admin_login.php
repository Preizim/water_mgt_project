<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "water_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_username = $_POST['admin_username'];
    $admin_password = $_POST['admin_password'];

    $sql = "SELECT id, password FROM admins WHERE username = '$admin_username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($admin_password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            header("Location: admin_panel.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Username not found";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Water Management Admin Login</title>
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: url('background.jpg') no-repeat center center fixed; background-size: cover; font-family: Arial, sans-serif;">
    <div style="background-color: rgba(255, 255, 255, 0.9); padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.2); width: 300px;">
        <h1 style="text-align: center; color: #333;">Water Management Admin Panel</h1>
        <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <label for="admin_username" style="color: #555;">Username:</label>
            <input type="text" name="admin_username" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <label for="admin_password" style="color: #555;">Password:</label>
            <input type="password" name="admin_password" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" style="padding: 12px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px;">Login</button>
        </form>
        <?php if (isset($error)) echo "<p style='color: red; text-align: center; margin-top: 10px;'>$error</p>"; ?>
    </div>
</body>
</html>
