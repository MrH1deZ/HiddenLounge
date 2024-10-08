<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'generate_code':
                $response = generateInvitationCode($conn);
                echo json_encode($response);
                break;
            case 'load_codes':
                $response = loadInvitationCodes($conn);
                echo json_encode($response);
                break;
            case 'freeze_code':
                $response = freezeInvitationCode($conn, $_POST['code_id']);
                echo json_encode($response);
                break;
            case 'delete_code':
                $response = deleteInvitationCode($conn, $_POST['code_id']);
                echo json_encode($response);
                break;
            case 'load_users':
                $response = loadUsers($conn);
                echo json_encode($response);
                break;
            case 'search_users':
                $response = searchUsers($conn, $_POST['query']);
                echo json_encode($response);
                break;
            case 'update_points':
                $response = updatePoints($conn, $_POST['user_id'], $_POST['operation'], $_POST['points']);
                echo json_encode($response);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    }
}

function generateInvitationCode($conn) {
    $code = bin2hex(random_bytes(4));
    $query = "INSERT INTO invitation_codes (code) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $code);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => "Code generated successfully: " . $code];
    } else {
        return ['success' => false, 'message' => "Error generating code."];
    }
}

function loadInvitationCodes($conn) {
    $query = "SELECT id, code, is_used, is_frozen FROM invitation_codes";
    $result = $conn->query($query);
    $codes = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['is_used'] ? 'Used' : ($row['is_frozen'] ? 'Frozen' : 'Available');
            $codes[] = [
                'id' => $row['id'],
                'code' => htmlspecialchars($row['code']),
                'status' => $status,
            ];
        }
        return ['success' => true, 'codes' => $codes];
    } else {
        return ['success' => false, 'message' => "No invitation codes available."];
    }
}

function freezeInvitationCode($conn, $codeId) {
    $query = "UPDATE invitation_codes SET is_frozen = TRUE WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $codeId);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => "Code frozen successfully."];
    } else {
        return ['success' => false, 'message' => "Error freezing code."];
    }
}

function deleteInvitationCode($conn, $codeId) {
    $query = "DELETE FROM invitation_codes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $codeId);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => "Code deleted successfully."];
    } else {
        return ['success' => false, 'message' => "Error deleting code."];
    }
}

function loadUsers($conn) {
    $query = "SELECT id, username, hidez_points FROM users";
    $result = $conn->query($query);
    $users = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'id' => $row['id'],
                'username' => htmlspecialchars($row['username']),
                'hidez_points' => htmlspecialchars($row['hidez_points']),
            ];
        }
        return ['success' => true, 'users' => $users];
    } else {
        return ['success' => false, 'message' => "No users available."];
    }
}

function searchUsers($conn, $query) {
    $query = "%" . $query . "%";
    $stmt = $conn->prepare("SELECT id, username, hidez_points FROM users WHERE username LIKE ?");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'id' => $row['id'],
                'username' => htmlspecialchars($row['username']),
                'hidez_points' => htmlspecialchars($row['hidez_points']),
            ];
        }
        return ['success' => true, 'users' => $users];
    } else {
        return ['success' => false, 'message' => "No users found."];
    }
}

function updatePoints($conn, $userId, $operation, $points) {
    $points = (int)$points;
    if ($operation === 'add') {
        $query = "UPDATE users SET hidez_points = hidez_points + ? WHERE id = ?";
    } elseif ($operation === 'remove') {
        $query = "UPDATE users SET hidez_points = GREATEST(hidez_points - ?, 0) WHERE id = ?";
    } else {
        return ['success' => false, 'message' => "Invalid operation."];
    }
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $points, $userId);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => "Points updated successfully."];
    } else {
        return ['success' => false, 'message' => "Error updating points."];
    }
}
?>
