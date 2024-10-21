<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "water_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $initial_balance = 0.00; // Default initial balance

    $sql = "INSERT INTO users (first_name, last_name, username, email, password, balance) 
            VALUES ('$first_name', '$last_name', '$username', '$email', '$hashed_password', '$initial_balance')";
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: url('background.jpg') no-repeat center center fixed; background-size: cover; font-family: Arial, sans-serif;">
    <div style="background-color: rgba(255, 255, 255, 0.9); padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.2); width: 300px;">
        <h1 style="text-align: center; color: #333;">Register</h1>
        <h3 style="text-align: center; color: #666;">Water Management System</h3>
        <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <label for="first_name" style="color: #555;">First Name:</label>
            <input type="text" name="first_name" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <label for="last_name" style="color: #555;">Last Name:</label>
            <input type="text" name="last_name" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <label for="username" style="color: #555;">Username:</label>
            <input type="text" name="username" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <label for="email" style="color: #555;">Email:</label>
            <input type="email" name="email" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <label for="password" style="color: #555;">Password:</label>
            <input type="password" name="password" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" style="padding: 12px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px;">Register</button>
        </form>
        <?php if (isset($error)) echo "<p style='color: red; text-align: center; margin-top: 10px;'>$error</p>"; ?>
    </div>
</body>
</html>
