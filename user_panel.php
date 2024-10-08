<?php
include 'db.php';
include 'functions/user_panel.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle form submission for settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        // Call the function to update user settings
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $discord_name = $_POST['discord_name'] ?? '';
        
        // Assuming you have a function updateUserSettings to handle the update
        updateUserSettings($conn, $_SESSION['user_id'], $email, $password, $discord_name);

        // Redirect after successful update to avoid duplicate submission
        header('Location: user_panel.php');
        exit;
    }
}

// Fetch user details after form submission handling
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
                               <form id="settingsForm" action="user_panel.php" method="post"> <!-- Updated action -->
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
