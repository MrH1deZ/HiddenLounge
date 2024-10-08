<?php
include 'db.php';
include 'functions/admin_panel.php';
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Handle AJAX requests for actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_points':
            // Process update points action
            $userId = $_POST['user_id'] ?? null;
            $operation = $_POST['operation'] ?? null;
            $points = $_POST['points'] ?? null;

            // Call the function to update points
            $result = updateUserPoints($userId, $operation, $points);
            echo json_encode(['success' => $result]);
            exit; // Exit to prevent any further output

        case 'generate_code':
            // Process generate code action
            $result = generateNewCode(); // Ensure this function is defined in your functions file
            echo json_encode(['success' => $result]);
            exit;

        // Add other cases as needed for your AJAX actions
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
            exit;
    }
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
                    const result = JSON.parse(response);
                    if (result.success) {
                        loadUsers();
                        alert('Points updated successfully!');
                    } else {
                        alert('Failed to update points.');
                    }
                });
            }
        }

        function generateCode() {
            const formData = new FormData();
            formData.append('action', 'generate_code');
            sendRequest('functions/admin_panel.php', formData, function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    loadCodes();
                    alert('Code generated successfully!');
                } else {
                    alert('Failed to generate code.');
                }
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
