<?php
// client/login.php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['client_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errorMsg = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, organization_name, password, status FROM clients WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $client = $result->fetch_assoc();
            if ($client['status'] == 'inactive') {
                $errorMsg = "Your account is currently inactive. Please contact support.";
            } else if (password_verify($password, $client['password'])) {
                // Login success
                $_SESSION['client_id'] = $client['id'];
                $_SESSION['client_org'] = $client['organization_name'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $errorMsg = "Invalid email or password.";
            }
        } else {
            $errorMsg = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login | WISE Africa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--light-bg); }
        .login-card { background: var(--white); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; margin-bottom: 5px; }
        .login-card p.subtitle { text-align: center; color: #6b7280; font-size: 0.9rem; margin-bottom: 25px; }
        .logo-center { text-align: center; font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--primary-blue); margin-bottom: 30px; }
        .logo-center span { color: var(--secondary-green); }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card fade-in">
            <div class="logo-center">WISE <span>Africa</span></div>
            <h2>Client Portal</h2>
            <p class="subtitle">Access your organization's dashboard</p>
            
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-error"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="example@organization.org">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary submit-btn">Login</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="../index.php" style="font-size: 0.9rem; color: #4B5563;">&larr; Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
