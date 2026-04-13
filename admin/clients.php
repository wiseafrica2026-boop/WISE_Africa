<?php
// admin/clients.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin_login();

$successMsg = '';
$errorMsg = '';

// Handle status toggle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $clientId = intval($_POST['client_id']);
    $newStatus = $_POST['new_status'];
    
    if (in_array($newStatus, ['active', 'inactive'])) {
        $stmt = $conn->prepare("UPDATE clients SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $clientId);
        if ($stmt->execute()) {
            $successMsg = "Client status updated successfully.";
        } else {
            $errorMsg = "Error updating status.";
        }
    }
}

// Fetch all clients
$clients = $conn->query("SELECT * FROM clients ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients | Admin Dashboard</title>
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
        .badge-active { background: #D1FAE5; color: #065F46; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar fade-in">
                <h1>Client Management</h1>
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
                            <th>Organization Name</th>
                            <th>Email Address</th>
                            <th>Phone</th>
                            <th>Joined Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($clients->num_rows > 0): ?>
                            <?php while($row = $clients->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['organization_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php $toggleStatus = ($row['status'] == 'active') ? 'inactive' : 'active'; ?>
                                        <form action="clients.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="client_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="new_status" value="<?php echo $toggleStatus; ?>">
                                            <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem; color: #4B5563; border-color: #D1D5DB;">
                                                Set <?php echo ucfirst($toggleStatus); ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 30px;">No clients found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
