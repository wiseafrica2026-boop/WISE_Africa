<?php
// client/project_details.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_client_login();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$projectId = intval($_GET['id']);
$clientId = $_SESSION['client_id'];

// Fetch project ensuring it belongs to this client
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND client_id = ?");
$stmt->bind_param("ii", $projectId, $clientId);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    header("Location: dashboard.php");
    exit();
}

// Fetch Client Profile for header
$stmtClient = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmtClient->bind_param("i", $clientId);
$stmtClient->execute();
$clientInfo = $stmtClient->get_result()->fetch_assoc();

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
    <title>Project Details | WISE Africa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .client-layout { display: flex; flex-direction: column; min-height: 100vh; background: var(--light-bg); }
        .top-nav { background: var(--white); box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 0 40px; height: 80px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--primary-blue); display: flex; align-items: center; gap: 8px;}
        .nav-logo span { color: var(--secondary-green); }
        .main-content { padding: 40px; max-width: 1000px; margin: 0 auto; width: 100%; flex: 1;}
        .card { background: var(--white); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 30px; }
        .progress-bar-container { background-color: #E5E7EB; border-radius: 10px; height: 16px; overflow: hidden; margin-top: 15px; width: 100%;}
        .progress-bar { height: 100%; background-color: var(--secondary-green); transition: width 0.3s; }
        .update-timeline { border-left: 2px solid #E5E7EB; padding-left: 20px; margin-top: 30px; }
        .update-item { position: relative; margin-bottom: 30px; }
        .update-item::before { content: ''; position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--primary-blue); border: 2px solid var(--white); }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block;}
        .badge-in_progress { background: #DBEAFE; color: #1E40AF; }
        .badge-active { background: #D1FAE5; color: #065F46; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }
        .badge-completed { background: #E5E7EB; color: #374151; }
    </style>
</head>
<body>
    <div class="client-layout">
        <header class="top-nav">
            <div class="nav-logo">
                <i class="fa-solid fa-earth-africa" style="color: var(--secondary-green);"></i>
                WISE <span>Africa</span>
            </div>
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; justify-content: center;">
                <span style="font-weight: 600; color: #4B5563;">
                    <i class="fa-solid fa-building" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($clientInfo['organization_name']); ?>
                </span>
                <a href="settings.php" style="color: #4B5563; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="logout.php" style="color: #DC2626; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </header>

        <main class="main-content fade-in">
            <div style="margin-bottom: 20px;">
                <a href="dashboard.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 500;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                    <div>
                        <h1 style="margin-bottom: 10px; font-size: 2rem; color: var(--primary-blue);"><?php echo htmlspecialchars($project['project_name']); ?></h1>
                        <span class="badge badge-<?php echo $project['status']; ?>"><?php echo str_replace('_', ' ', ucfirst($project['status'])); ?></span>
                    </div>
                </div>
                
                <div style="background: #F9FAFB; padding: 20px; border-radius: 8px; margin-bottom: 30px; color: #4B5563;">
                    <h4 style="color: var(--dark-text); margin-bottom: 10px;">Project Overview</h4>
                    <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                </div>

                <div style="margin-bottom: 40px;">
                    <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 600;">
                        <span>Current Progress</span>
                        <span style="color: var(--secondary-green);"><?php echo $project['progress_percentage']; ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo $project['progress_percentage']; ?>%;"></div>
                    </div>
                </div>

                <h3 style="margin-bottom: 10px; border-bottom: 1px solid #E5E7EB; padding-bottom: 15px;">Project Updates & Timeline</h3>
                
                <?php if ($updates->num_rows > 0): ?>
                    <div class="update-timeline">
                        <?php while($u = $updates->fetch_assoc()): ?>
                            <div class="update-item">
                                <small style="color: #6B7280; font-weight: 600; display: block; margin-bottom: 5px;"><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i> <?php echo date('M j, Y - g:i a', strtotime($u['created_at'])); ?></small>
                                <p style="color: #111827; background: #F3F4F6; padding: 15px; border-radius: 8px; margin-top: 5px;"><?php echo nl2br(htmlspecialchars($u['update_message'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: #F9FAFB; border-radius: 8px; margin-top: 20px;">
                        <i class="fa-solid fa-timeline" style="font-size: 2rem; color: #D1D5DB; margin-bottom: 15px;"></i>
                        <p style="color: #6B7280; font-weight: 500;">No updates have been posted for this project yet.</p>
                        <p style="color: #9CA3AF; font-size: 0.9rem;">Check back later as our admin team begins work.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
