/* File Structure:
1. index.php - Main Login Page
2. register.php - Registration Page
3. user_panel.php - User Panel
4. admin_panel.php - Admin Panel
5. style.css - CSS for styling
6. script.js - JavaScript for AJAX actions
7. db.php - Database connection file
8. functions/ - Directory for separate function files
    - login.php - Functions related to login
    - register.php - Functions related to registration
    - user_panel.php - Functions for user panel operations
    - admin_panel.php - Functions for admin panel operations

Database Structure: A MySQL database for storing users and invitation codes. */

/* Database Structure */
CREATE DATABASE web_app_db;

USE web_app_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    discord_name VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE,
    hidez_points INT DEFAULT 0
);

CREATE TABLE invitation_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    is_used BOOLEAN DEFAULT FALSE
);

-- Create an admin user with username 'admin' and password 'Blahblah123@!'
INSERT INTO users (username, firstname, lastname, email, password, is_admin, hidez_points)
VALUES ('admin', 'Admin', 'User', 'admin@example.com', '$2y$10$eH7wJGcEM4ojj2UUnzUOEe1L5uUdTyaJzpOIgHtIbzZC/Kx1YYpOm', TRUE, 1000);
/* Password is hashed using bcrypt for 'Blahblah123@!' */

/* File: db.php */
<?php
$servername = "localhost";
$username = "fxinvrev_tester";
$password = "Tester123@!Test";
$dbname = "fxinvrev_tester";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

/* File: style.css */
body {
    background-color: #121212;
    color: #e5e5e5;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    width: 400px;
    margin: 50px auto;
    padding: 30px;
    background-color: #1f1f1f;
    border-radius: 15px;
    box-shadow: 0px 0px 15px #e50914;
}

input[type="text"],
input[type="password"],
input[type="email"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #333;
    border-radius: 8px;
    background: #333;
    color: #ddd;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    background: #e50914;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    border-radius: 8px;
    margin-top: 10px;
}

button:hover {
    background: #f45c42;
}

/* Horizontal and Vertical Menu Styles */
.horizontal-menu, .vertical-menu {
    background-color: #333;
    padding: 10px;
}

.horizontal-menu a, .vertical-menu a {
    color: #e50914;
    text-decoration: none;
    margin: 0 15px;
    padding: 10px;
}

.horizontal-menu a:hover, .vertical-menu a:hover {
    text-decoration: underline;
}

.user-panel {
    text-align: center;
    margin-top: 30px;
    background-color: #222;
    padding: 20px;
    border-radius: 15px;
}

.user-panel-initials {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background-color: #444;
    color: #e50914;
    font-size: 30px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* File: functions/admin_panel.php */
<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'generate_code':
                generateInvitationCode($conn);
                break;
            case 'load_codes':
                loadInvitationCodes($conn);
                break;
            case 'freeze_code':
                freezeInvitationCode($conn, $_POST['code_id']);
                break;
            case 'delete_code':
                deleteInvitationCode($conn, $_POST['code_id']);
                break;
            case 'load_users':
                loadUsers($conn);
                break;
            case 'search_users':
                searchUsers($conn, $_POST['query']);
                break;
            case 'update_points':
                updatePoints($conn, $_POST['user_id'], $_POST['operation'], $_POST['points']);
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
        echo "Code generated successfully: " . $code;
    } else {
        echo "Error generating code.";
    }
}

function loadInvitationCodes($conn) {
    $query = "SELECT id, code, is_used, is_frozen FROM invitation_codes";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        echo '<table><tr><th>Code</th><th>Status</th><th>Actions</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $status = $row['is_used'] ? 'Used' : ($row['is_frozen'] ? 'Frozen' : 'Available');
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['code']) . '</td>';
            echo '<td>' . $status . '</td>';
            echo '<td><button onclick="freezeCode(' . $row['id'] . ')">Freeze</button>';
            echo '<button onclick="deleteCode(' . $row['id'] . ')">Delete</button></td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo "No invitation codes available.";
    }
}

function freezeInvitationCode($conn, $codeId) {
    $query = "UPDATE invitation_codes SET is_frozen = TRUE WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $codeId);
    if ($stmt->execute()) {
        echo "Code frozen successfully.";
    } else {
        echo "Error freezing code.";
    }
}

function deleteInvitationCode($conn, $codeId) {
    $query = "DELETE FROM invitation_codes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $codeId);
    if ($stmt->execute()) {
        echo "Code deleted successfully.";
    } else {
        echo "Error deleting code.";
    }
}

function loadUsers($conn) {
    $query = "SELECT id, username, hidez_points FROM users";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        echo '<table><tr><th>Username</th><th>Hidez Points</th><th>Actions</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
            echo '<td>' . htmlspecialchars($row['hidez_points']) . '</td>';
            echo '<td><button onclick="updatePoints(' . $row['id'] . ', 'add')">Add Points</button>';
            echo '<button onclick="updatePoints(' . $row['id'] . ', 'remove')">Remove Points</button></td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo "No users available.";
    }
}

function searchUsers($conn, $query) {
    $query = "%" . $query . "%";
    $stmt = $conn->prepare("SELECT id, username, hidez_points FROM users WHERE username LIKE ?");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo '<table><tr><th>Username</th><th>Hidez Points</th><th>Actions</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
            echo '<td>' . htmlspecialchars($row['hidez_points']) . '</td>';
            echo '<td><button onclick="updatePoints(' . $row['id'] . ', 'add')">Add Points</button>';
            echo '<button onclick="updatePoints(' . $row['id'] . ', 'remove')">Remove Points</button></td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo "No users found.";
    }
}

function updatePoints($conn, $userId, $operation, $points) {
    $points = (int)$points;
    if ($operation === 'add') {
        $query = "UPDATE users SET hidez_points = hidez_points + ? WHERE id = ?";
    } elseif ($operation === 'remove') {
        $query = "UPDATE users SET hidez_points = GREATEST(hidez_points - ?, 0) WHERE id = ?";
    } else {
        echo "Invalid operation.";
        return;
    }
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $points, $userId);
    if ($stmt->execute()) {
        echo "Points updated successfully.";
    } else {
        echo "Error updating points.";
    }
}
?>

/* File: functions/login.php */

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
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: user_panel.php');
            exit;
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }
}
?>

/* File: functions/register.php */

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
        $query = "INSERT INTO users (username, firstname, lastname, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $username, $firstname, $lastname, $email, $password);

        if ($stmt->execute()) {
            // Mark invitation as used
            $update_inv_query = "UPDATE invitation_codes SET is_used=TRUE WHERE code=?";
            $update_stmt = $conn->prepare($update_inv_query);
            $update_stmt->bind_param("s", $invitation);
            $update_stmt->execute();

            header('Location: index.php');
            exit;
        } else {
            echo "Error registering user.";
        }
    } else {
        echo "Invalid or already used invitation code.";
    }
}
?>

/* File: functions/user_panel.php */

<?php
function getUser($conn, $user_id) {
    $query = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    session_start();
    include '../db.php';

    $user_id = $_SESSION['user_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $discord_name = $_POST['discord_name'];

    $query = "UPDATE users SET email=?, discord_name=?";
    $types = "ss";
    $params = [$email, $discord_name];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query .= ", password=?";
        $types .= "s";
        $params[] = $hashed_password;
    }

    $query .= " WHERE id=?";
    $types .= "i";
    $params[] = $user_id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        header('Location: ../user_panel.php');
        exit;
    } else {
        echo "Error updating settings.";
    }
}
?>

/* File: user_panel.php */

<?php
include 'db.php';
include 'functions/user_panel.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user = getUser($conn, $_SESSION['user_id']);
$hidez_points = $user['hidez_points'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <title>User Panel</title>
</head>
<body>
    <div class="horizontal-menu">
        <a href="#">Home</a>
        <a href="#">Profile</a>
        <span style="flex-grow: 1; text-align: center; color: #e50914;">Hidez Points: <?php echo $hidez_points; ?></span>
        <?php if ($user['is_admin']) echo '<a href="admin_panel.php">Admin Panel</a>'; ?>
        <a href="logout.php">Logout</a>
    </div>
    <div class="user-panel">
        <div class="user-panel-initials">
            <?php echo strtoupper($user['firstname'][0] . $user['lastname'][0]); ?>
        </div>
        <h3>Hello, <?php echo htmlspecialchars($user['firstname']); ?>!</h3>
        <div class="vertical-menu">
            <a href="#" onclick="showContent('settings')">Settings</a>
            <a href="#" onclick="showContent('subscriptions')">Subscriptions</a>
            <a href="#" onclick="showContent('licenses')">Licenses</a>
        </div>
        <div id="content-box" class="container" style="display:none;"></div>
    </div>
    <script>
        function showContent(tab) {
            const contentBox = document.getElementById('content-box');
            contentBox.style.display = 'block';
            let content = '';
            switch (tab) {
                case 'settings':
                    content = `<h3>Settings</h3>
                               <form id="settingsForm" action="functions/user_panel.php" method="post">
                                   <input type="email" name="email" placeholder="New E-mail" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                   <input type="password" name="password" placeholder="New Password">
                                   <input type="text" name="discord_name" placeholder="Discord Name (optional)" value="<?php echo htmlspecialchars($user['discord_name']); ?>">
                                   <button type="submit" name="update_settings">Update Settings</button>
                               </form>`;
                    break;
                case 'subscriptions':
                    content = '<h3>Subscriptions</h3><p>Here you can view and manage your subscriptions.</p>';
                    break;
                case 'licenses':
                    content = '<h3>Licenses</h3><p>Here you can view and manage your licenses.</p>';
                    break;
                default:
                    content = '';
            }
            contentBox.innerHTML = content;
        }
    </script>
</body>
</html>

/* File: register.php */

<?php
include 'db.php';
include 'functions/register.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    register($conn, $_POST);
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

/* File: index.php */

<?php
include 'db.php';
include 'functions/login.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    login($conn, $username, $password);
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
        <form id="loginForm" action="index.php" method="post">
            <input type="text" name="username" placeholder="Username / E-mail" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <button type="button" onclick="window.location.href='register.php'">Register</button>
        </form>
    </div>
</body>
</html>

/* File: admin_panel.php */

<?php
include 'db.php';
include 'functions/admin_panel.php';
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <title>Admin Panel</title>
</head>
<body>
    <div class="horizontal-menu">
        <a href="user_panel.php">User Panel</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="vertical-menu">
        <a href="#" onclick="showAdminContent('manage_users')">Manage Users</a>
        <a href="#" onclick="showAdminContent('manage_points')">Manage Hidez Points</a>
        <a href="#" onclick="showAdminContent('invitation_codes')">Invitation Codes</a>
    </div>
    <div id="admin-content-box" class="container" style="display:none;"></div>
    <script>
        function showAdminContent(tab) {
            const contentBox = document.getElementById('admin-content-box');
            contentBox.style.display = 'block';
            let content = '';
            switch (tab) {
                case 'manage_users':
                    content = '<h3>Manage Users</h3><p>Here you can manage users.</p>';
                    break;
                case 'manage_points':
                    content = `<h3>Manage Hidez Points</h3>
                               <input type="text" id="searchUser" placeholder="Search User" oninput="searchUser()">
                               <div id="user-list"></div>`;
                    loadUsers();
                    break;
                case 'invitation_codes':
                    content = `<h3>Invitation Codes</h3>
                               <button onclick="generateCode()">Generate New Code</button>
                               <div id="codes-list"></div>`;
                    loadCodes();
                    break;
                default:
                    content = '';
            }
            contentBox.innerHTML = content;
        }

        function loadUsers() {
            const formData = new FormData();
            formData.append('action', 'load_users');
            sendRequest('functions/admin_panel.php', formData, function(response) {
                document.getElementById('user-list').innerHTML = response;
            });
        }

        function searchUser() {
            const searchQuery = document.getElementById('searchUser').value;
            const formData = new FormData();
            formData.append('action', 'search_users');
            formData.append('query', searchQuery);
            sendRequest('functions/admin_panel.php', formData, function(response) {
                document.getElementById('user-list').innerHTML = response;
            });
        }

        function updatePoints(userId, operation) {
            const points = prompt("Enter points to " + operation);
            if (points !== null) {
                const formData = new FormData();
                formData.append('action', 'update_points');
                formData.append('user_id', userId);
                formData.append('operation', operation);
                formData.append('points', points);
                sendRequest('functions/admin_panel.php', formData, function(response) {
                    loadUsers();
                    alert(response);
                });
            }
        }

        function generateCode() {
            const formData = new FormData();
            formData.append('action', 'generate_code');
            sendRequest('functions/admin_panel.php', formData, function(response) {
                loadCodes();
            });
        }

        function loadCodes() {
            const formData = new FormData();
            formData.append('action', 'load_codes');
            sendRequest('functions/admin_panel.php', formData, function(response) {
                document.getElementById('codes-list').innerHTML = response;
            });
        }

        function freezeCode(codeId) {
            const formData = new FormData();
            formData.append('action', 'freeze_code');
            formData.append('code_id', codeId);
            sendRequest('functions/admin_panel.php', formData, function(response) {
                loadCodes();
            });
        }

        function deleteCode(codeId) {
            const formData = new FormData();
            formData.append('action', 'delete_code');
            formData.append('code_id', codeId);
            sendRequest('functions/admin_panel.php', formData, function(response) {
                loadCodes();
            });
        }
    </script>
</body>
</html>
