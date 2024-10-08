<?php
function register($conn, $data) {
    $username = $data['username'];
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    $invitation = $data['invitation'];

    // Check if the invitation code is valid
    $inv_query = "SELECT * FROM invitation_codes WHERE code=? AND is_used=FALSE";
    $inv_stmt = $conn->prepare($inv_query);
    $inv_stmt->bind_param("s", $invitation);
    $inv_stmt->execute();
    $inv_result = $inv_stmt->get_result();

    if ($inv_result->num_rows > 0) {
        // Proceed with user registration
        $query = "INSERT INTO users (username, firstname, lastname, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $username, $firstname, $lastname, $email, $password);

        if ($stmt->execute()) {
            // Mark invitation as used
            $update_inv_query = "UPDATE invitation_codes SET is_used=TRUE WHERE code=?";
            $update_stmt = $conn->prepare($update_inv_query);
            $update_stmt->bind_param("s", $invitation);
            $update_stmt->execute();

            // Return success message instead of redirecting here
            return ['success' => true, 'message' => 'Registration successful.'];
        } else {
            return ['success' => false, 'message' => 'Error registering user.'];
        }
    } else {
        return ['success' => false, 'message' => 'Invalid or already used invitation code.'];
    }
}
?>
