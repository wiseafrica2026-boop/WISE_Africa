<?php
// admin/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_admin_login();

// Fetch stats
$clientCount = $conn->query("SELECT COUNT(*) as c FROM clients")->fetch_assoc()['c'];
$pendingApps = $conn->query("SELECT COUNT(*) as c FROM applications WHERE status = 'pending'")->fetch_assoc()['c'];
$activeProjects = $conn->query("SELECT COUNT(*) as c FROM projects WHERE status IN ('in_progress', 'active')")->fetch_assoc()['c'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | WISE Africa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; background: var(--light-bg); }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .welcome-text h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); display: flex; align-items: center; }
        .stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 1.8rem; margin-right: 20px; }
        .stat-icon.blue { background: rgba(30, 58, 138, 0.1); color: var(--primary-blue); }
        .stat-icon.green { background: rgba(22, 163, 74, 0.1); color: var(--secondary-green); }
        .stat-icon.accent { background: rgba(37, 99, 235, 0.1); color: var(--accent); }
        .stat-details h3 { font-size: 1.8rem; margin-bottom: 0; color: var(--dark-text); }
        .stat-details p { color: #6B7280; font-size: 0.9rem; font-weight: 500; }
        .card { background: var(--white); padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar fade-in">
                <div class="welcome-text">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
                    <p style="color: #6B7280;">Here's what's happening today.</p>
                </div>
            </div>

            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-inbox"></i></div>
                    <div class="stat-details">
                        <h3><?php echo $pendingApps; ?></h3>
                        <p>Pending Applications</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-details">
                        <h3><?php echo $clientCount; ?></h3>
                        <p>Total Clients</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon accent"><i class="fa-solid fa-bars-progress"></i></div>
                    <div class="stat-details">
                        <h3><?php echo $activeProjects; ?></h3>
                        <p>Active Projects</p>
                    </div>
                </div>
            </div>
            
            <div class="card fade-in">
                <h3 style="margin-bottom: 20px;">Quick Actions</h3>
                <div style="display: flex; gap: 15px;">
                    <a href="applications.php" class="btn btn-primary">Review Applications</a>
                    <a href="projects.php" class="btn btn-primary" style="background-color: var(--white); color: var(--primary-blue); border: 1px solid #E5E7EB; box-shadow: none;">Manage Projects</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
