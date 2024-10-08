<?php
include 'db.php';
include 'functions/register.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Call the register function and check for success
    $result = register($conn, $_POST);

    if ($result['success']) {
        // Successful registration, redirect to the login page
        header('Location: index.php'); // Redirect to the login page
        exit;
    } else {
        // Handle registration failure (optional)
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Register Page</title>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; // Display error if exists ?>
        <form id="registerForm" action="register.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="firstname" placeholder="Name" required>
            <input type="text" name="lastname" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="invitation" placeholder="Invitation Code">
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
