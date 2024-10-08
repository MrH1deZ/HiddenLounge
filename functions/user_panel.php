<?php
function getUser($conn, $user_id) {
    $query = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Handle form submission for updating settings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    session_start();
    include '../db.php';

    $user_id = $_SESSION['user_id'];
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $discord_name = $_POST['discord_name'] ?? '';

    // Prepare the SQL query
    $query = "UPDATE users SET email=?, discord_name=?";
    $types = "ss";
    $params = [$email, $discord_name];

    // Hash the password if it's provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query .= ", password=?";
        $types .= "s";
        $params[] = $hashed_password;
    }

    $query .= " WHERE id=?";
    $types .= "i";
    $params[] = $user_id;

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            // Redirect after successful update to avoid duplicate submission
            header('Location: ../user_panel.php');
            exit;
        } else {
            echo "Error updating settings.";
        }
    } else {
        echo "Error preparing statement.";
    }
}
?>
