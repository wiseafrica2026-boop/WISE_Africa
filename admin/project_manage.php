<?php
// admin/project_manage.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin_login();

if (!isset($_GET['id'])) {
    header("Location: projects.php");
    exit();
}
$projectId = intval($_GET['id']);

$successMsg = '';
$errorMsg = '';

// Handle Status/Progress Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_project') {
    $status = $_POST['status'];
    $progress = intval($_POST['progress_percentage']);
    
    $stmt = $conn->prepare("UPDATE projects SET status = ?, progress_percentage = ? WHERE id = ?");
    $stmt->bind_param("sii", $status, $progress, $projectId);
    if ($stmt->execute()) {
        $successMsg = "Project settings updated.";
    } else {
        $errorMsg = "Error updating project.";
    }
}

// Handle Add Update Message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_update') {
    $message = sanitize_input($_POST['update_message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO project_updates (project_id, update_message) VALUES (?, ?)");
        $stmt->bind_param("is", $projectId, $message);
        if ($stmt->execute()) {
            $successMsg = "Update posted successfully.";
        } else {
            $errorMsg = "Error posting update.";
        }
    }
}

// Fetch project
$stmt = $conn->prepare("SELECT p.*, c.organization_name FROM projects p JOIN clients c ON p.client_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    header("Location: projects.php");
    exit();
}

// Fetch updates
$stmtUpdates = $conn->prepare("SELECT * FROM project_updates WHERE project_id = ? ORDER BY created_at DESC");
$stmtUpdates->bind_param("i", $projectId);
$stmtUpdates->execute();
$updates = $stmtUpdates->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Project | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; background: var(--light-bg); }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: var(--white); padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 30px;}
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .update-box { background: #F9FAFB; border-left: 4px solid var(--primary-blue); padding: 15px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div style="margin-bottom: 20px;">
                <a href="projects.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 500;"><i class="fa-solid fa-arrow-left"></i> Back to Projects</a>
            </div>
            
            <h1 style="margin-bottom: 5px;"><?php echo htmlspecialchars($project['project_name']); ?></h1>
            <p style="color: #6B7280; margin-bottom: 30px;">Assigned to: <strong><?php echo htmlspecialchars($project['organization_name']); ?></strong></p>

            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success fade-in"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-error fade-in"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="card fade-in">
                <h3 style="margin-bottom: 20px;">Update Status & Progress</h3>
                <form action="project_manage.php?id=<?php echo $projectId; ?>" method="POST">
                    <input type="hidden" name="action" value="update_project">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" style="cursor: pointer; appearance: auto;">
                                <option value="in_progress" <?php if($project['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                <option value="active" <?php if($project['status'] == 'active') echo 'selected'; ?>>Active</option>
                                <option value="inactive" <?php if($project['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                                <option value="completed" <?php if($project['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Progress (%)</label>
                            <input type="number" name="progress_percentage" class="form-control" min="0" max="100" value="<?php echo $project['progress_percentage']; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.9rem;">Save Changes</button>
                </form>
            </div>

            <div class="card fade-in" style="animation-delay: 0.1s;">
                <h3 style="margin-bottom: 20px;">Post a Project Update</h3>
                <form action="project_manage.php?id=<?php echo $projectId; ?>" method="POST" style="margin-bottom: 30px;">
                    <input type="hidden" name="action" value="add_update">
                    <div class="form-group">
                        <textarea name="update_message" class="form-control" rows="3" placeholder="Write an update to show the client... e.g., 'Completed initial UI mockups.'" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.9rem;">Post Update</button>
                </form>

                <h4>Update History</h4>
                <?php if ($updates->num_rows > 0): ?>
                    <?php while($u = $updates->fetch_assoc()): ?>
                        <div class="update-box">
                            <p style="margin-bottom: 5px; color: #111827;"><?php echo nl2br(htmlspecialchars($u['update_message'])); ?></p>
                            <small style="color: #6B7280;"><i class="fa-regular fa-clock"></i> <?php echo date('M j, Y, g:i a', strtotime($u['created_at'])); ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #6B7280; font-size: 0.9rem;">No updates posted yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
