<?php
include 'db.php';
include 'functions/login.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Call the login function and check for success
    if (login($conn, $username, $password)) {
        // Successful login
        header('Location: user_panel.php'); // Redirect to user panel after login
        exit;
    } else {
        // Handle login failure (optional)
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login Page</title>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; // Display error if exists ?>
        <form id="loginForm" action="index.php" method="post">
            <input type="text" name="username" placeholder="Username / E-mail" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <button type="button" onclick="window.location.href='register.php'">Register</button>
        </form>
    </div>
</body>
</html>
