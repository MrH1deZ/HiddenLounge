<?php
function login($conn, $username, $password) {
    $query = "SELECT * FROM users WHERE (username=? OR email=?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Start the session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];

            // Return true to indicate a successful login
            return true;
        } else {
            // Return false for incorrect password
            return false;
        }
    } else {
        // Return false if the user was not found
        return false;
    }
}
?>
