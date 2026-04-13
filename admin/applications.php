<?php
// admin/applications.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin_login();

$successMsg = '';
$errorMsg = '';
$generatedPassword = '';

// Handle application approval
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $appId = intval($_POST['app_id']);
    
    // Fetch application
    $stmt = $conn->prepare("SELECT * FROM applications WHERE id = ? AND status = 'pending' LIMIT 1");
    $stmt->bind_param("i", $appId);
    $stmt->execute();
    $appResult = $stmt->get_result();
    
    if ($appResult->num_rows == 1) {
        $app = $appResult->fetch_assoc();
        
        // Check if client email already exists
        $checkStmt = $conn->prepare("SELECT id FROM clients WHERE email = ?");
        $checkStmt->bind_param("s", $app['email']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $errorMsg = "A client with this email already exists.";
        } else {
            // Generate password & Hash
            $generatedPassword = generate_random_password(10);
            $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);
            
            $conn->begin_transaction();
            try {
                // Insert into clients
                $insertClient = $conn->prepare("INSERT INTO clients (organization_name, email, phone, password, status) VALUES (?, ?, ?, ?, 'active')");
                $insertClient->bind_param("ssss", $app['organization_name'], $app['email'], $app['phone'], $hashedPassword);
                $insertClient->execute();
                $newClientId = $conn->insert_id;
                
                // Initialize Service Tracking
                $insertService = $conn->prepare("INSERT INTO service_tracking (client_id) VALUES (?)");
                $insertService->bind_param("i", $newClientId);
                $insertService->execute();
                
                // Update Application Status
                $updateApp = $conn->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
                $updateApp->bind_param("i", $appId);
                $updateApp->execute();
                
                $conn->commit();
                $successMsg = "Application approved! Client account created for <strong>" . htmlspecialchars($app['organization_name']) . "</strong>. <br>Their temporary password is: <strong>" . $generatedPassword . "</strong> <br><small>(Please copy this and send it securely to the client along with the login link!)</small>";
            } catch (Exception $e) {
                $conn->rollback();
                $errorMsg = "Error approving application: " . $e->getMessage();
            }
        }
    } else {
        $errorMsg = "Application not found or already processed.";
    }
}

// Fetch all applications
$apps = $conn->query("SELECT * FROM applications ORDER BY applied_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; background: var(--light-bg); }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .card { background: var(--white); padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); overflow-x: auto;}
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { font-weight: 600; color: #4B5563; background: #F9FAFB; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .badge-pending { background: #FEF3C7; color: #92400E; }
        .badge-approved { background: #D1FAE5; color: #065F46; }
        .badge-rejected { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar fade-in">
                <h1>Review Applications</h1>
            </div>

            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success fade-in"><?php echo $successMsg; ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-error fade-in"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="card fade-in">
                <table>
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($apps->num_rows > 0): ?>
                            <?php while($row = $apps->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['organization_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['organization_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_person']); ?><br><small><?php echo htmlspecialchars($row['email']); ?></small></td>
                                    <td><?php echo htmlspecialchars($row['size_count']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <form action="applications.php" method="POST" style="display:inline;" onsubmit="return confirm('Approve this application and auto-generate client account?');">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn btn-primary" style="padding: 8px 15px; font-size: 0.9rem;">Approve</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #9CA3AF;">Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center; padding: 30px;">No applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
