<?php
// client/settings.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_client_login();

$clientId = $_SESSION['client_id'];
$successMsg = '';
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMsg = "Please fill in all fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMsg = "New passwords do not match.";
    } elseif (strlen($newPassword) < 6) {
        $errorMsg = "New password must be at least 6 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM clients WHERE id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        
        if (password_verify($currentPassword, $client['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE clients SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $clientId);
            if ($updateStmt->execute()) {
                $successMsg = "Password updated successfully.";
            } else {
                $errorMsg = "Error updating password.";
            }
        } else {
            $errorMsg = "Current password is incorrect.";
        }
    }
}

// Fetch Client Profile for header
$stmtClient = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmtClient->bind_param("i", $clientId);
$stmtClient->execute();
$clientInfo = $stmtClient->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | WISE Africa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .client-layout { display: flex; flex-direction: column; min-height: 100vh; background: var(--light-bg); }
        .top-nav { background: var(--white); box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 0 40px; height: 80px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--primary-blue); display: flex; align-items: center; gap: 8px;}
        .nav-logo span { color: var(--secondary-green); }
        .main-content { padding: 40px; max-width: 800px; margin: 0 auto; width: 100%; flex: 1;}
        .card { background: var(--white); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="client-layout">
        <header class="top-nav">
            <div class="nav-logo">
                <i class="fa-solid fa-earth-africa" style="color: var(--secondary-green);"></i>
                <span style="color: var(--primary-blue);">WISE</span> <span>Africa</span>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="font-weight: 600; color: #4B5563;">
                    <i class="fa-solid fa-building" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($clientInfo['organization_name']); ?>
                </span>
                <a href="settings.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 500;"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="logout.php" style="color: #DC2626; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </header>

        <main class="main-content fade-in">
            <div style="margin-bottom: 20px;">
                <a href="dashboard.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 500;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            
            <h1 style="margin-bottom: 20px;">Account Settings</h1>

            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success fade-in"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-error fade-in"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="card fade-in">
                <h3 style="margin-bottom: 20px;">Change Password</h3>
                <form action="settings.php" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.9rem;">Update Password</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
