<?php
// admin/projects.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin_login();

$successMsg = '';
$errorMsg = '';

// Handle Create Project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create_project') {
    $clientId = intval($_POST['client_id']);
    $projectName = sanitize_input($_POST['project_name']);
    $description = sanitize_input($_POST['description']);
    $status = $_POST['status'];
    $progress = intval($_POST['progress_percentage']);
    
    if (empty($clientId) || empty($projectName)) {
        $errorMsg = "Client and Project Name are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO projects (client_id, project_name, description, status, progress_percentage) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $clientId, $projectName, $description, $status, $progress);
        if ($stmt->execute()) {
            $successMsg = "Project created successfully.";
        } else {
            $errorMsg = "Error creating project: " . $conn->error;
        }
    }
}

// Fetch clients for dropdown
$clients = $conn->query("SELECT id, organization_name FROM clients WHERE status = 'active' ORDER BY organization_name ASC");

// Fetch projects joined with client name
$projects = $conn->query("
    SELECT p.*, c.organization_name 
    FROM projects p 
    JOIN clients c ON p.client_id = c.id 
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; background: var(--light-bg); }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .card { background: var(--white); padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 30px; overflow-x: auto;}
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { font-weight: 600; color: #4B5563; background: #F9FAFB; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .badge-in_progress { background: #DBEAFE; color: #1E40AF; }
        .badge-active { background: #D1FAE5; color: #065F46; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }
        .badge-completed { background: #E5E7EB; color: #374151; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .progress-bar-container { width: 100%; background-color: #E5E7EB; border-radius: 10px; height: 10px; overflow: hidden; margin-top: 5px; }
        .progress-bar { height: 100%; background-color: var(--secondary-green); transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar fade-in">
                <h1>Project Management</h1>
            </div>

            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success fade-in"><?php echo $successMsg; ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-error fade-in"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="card fade-in">
                <h3 style="margin-bottom: 20px;">Create New Project</h3>
                <form action="projects.php" method="POST">
                    <input type="hidden" name="action" value="create_project">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Assign to Client *</label>
                            <select name="client_id" class="form-control" required style="cursor: pointer; appearance: auto;">
                                <option value="" disabled selected>Select active client...</option>
                                <?php if ($clients->num_rows > 0): while($c = $clients->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['organization_name']); ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Project Name *</label>
                            <input type="text" name="project_name" class="form-control" required placeholder="e.g. Website Development">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Overview of the project"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Initial Status *</label>
                            <select name="status" class="form-control" required style="cursor: pointer; appearance: auto;">
                                <option value="in_progress">In Progress</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Initial Progress (%) *</label>
                            <input type="number" name="progress_percentage" class="form-control" min="0" max="100" value="0" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </form>
            </div>

            <div class="card fade-in" style="animation-delay: 0.1s;">
                <h3 style="margin-bottom: 20px;">Tracked Projects</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Project Info</th>
                            <th>Assigned Client</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($projects->num_rows > 0): ?>
                            <?php while($row = $projects->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['project_name']); ?></strong><br>
                                        <small style="color: #6B7280;">Since: <?php echo date('M j, Y', strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['organization_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status']; ?>"><?php echo str_replace('_', ' ', ucfirst($row['status'])); ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; font-size: 0.9rem;"><?php echo $row['progress_percentage']; ?>%</span>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: <?php echo $row['progress_percentage']; ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="project_manage.php?id=<?php echo $row['id']; ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem; color: var(--primary-blue); border-color: var(--primary-blue);">Manage</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 30px;">No projects found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
