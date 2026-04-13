<?php
// client/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_client_login();

$clientId = $_SESSION['client_id'];

// Auto-evaluate subscriptions
evaluate_subscription_statuses($conn);

// Fetch Client Profile
$stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Fetch Projects
$stmtProjects = $conn->prepare("SELECT * FROM projects WHERE client_id = ? ORDER BY created_at DESC");
$stmtProjects->bind_param("i", $clientId);
$stmtProjects->execute();
$projects = $stmtProjects->get_result();

// Fetch Service Tracking
$stmtTracking = $conn->prepare("SELECT * FROM service_tracking WHERE client_id = ?");
$stmtTracking->bind_param("i", $clientId);
$stmtTracking->execute();
$svcResult = $stmtTracking->get_result();
$tracking = $svcResult->num_rows > 0 ? $svcResult->fetch_assoc() : [
    'domain_status' => 'pending', 'hosting_status' => 'pending', 
    'seo_status' => 'not_started', 'maintenance_status' => 'inactive',
    'domain_expiry_date' => null, 'hosting_expiry_date' => null,
    'seo_next_due_date' => null, 'maintenance_next_due_date' => null
];

// Assess Alert Logic
$alerts = [];
$dStat = $tracking['domain_status']; 
$hStat = $tracking['hosting_status'];
$sStat = $tracking['seo_status']; 
$mStat = $tracking['maintenance_status'];

if ($dStat == 'expired' || $hStat == 'expired') {
    $alerts[] = ['type' => 'urgent', 'msg' => 'Your domain/hosting has expired. Your website may be offline.'];
} elseif ($dStat == 'expiring_soon' || $hStat == 'expiring_soon') {
    $alerts[] = ['type' => 'warning', 'msg' => 'Your hosting/domain will expire soon. Kindly renew to avoid disruption.'];
}

if ($sStat == 'overdue' || $mStat == 'overdue') {
    $alerts[] = ['type' => 'urgent', 'msg' => 'Your SEO/Maintenance payment is overdue. Please renew immediately.'];
} elseif ($sStat == 'due_soon' || $mStat == 'due_soon') {
    $alerts[] = ['type' => 'warning', 'msg' => 'Your SEO/Maintenance subscription is due soon.'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard | WISE Africa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .client-layout { display: flex; flex-direction: column; min-height: 100vh; background: var(--light-bg); }
        .top-nav { background: var(--white); box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 0 40px; height: 80px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--primary-blue); display: flex; align-items: center; gap: 8px;}
        .nav-logo span { color: var(--secondary-green); }
        .main-content { padding: 40px; max-width: 1200px; margin: 0 auto; width: 100%; flex: 1;}
        .card { background: var(--white); padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 30px; }
        .progress-bar-container { background-color: #E5E7EB; border-radius: 10px; height: 12px; overflow: hidden; margin-top: 10px; margin-bottom: 10px; width: 100%;}
        .progress-bar { height: 100%; background-color: var(--secondary-green); transition: width 0.3s; }
        .project-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;}
        .project-card { border: 1px solid #E5E7EB; padding: 25px; border-radius: 8px; transition: 0.3s; background: var(--white); }
        .project-card:hover { transform: translateY(-5px); box-shadow: var(--hover-shadow); }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 15px; text-transform: capitalize;}
        .badge-pending, .badge-not_started { background: #E5E7EB; color: #4B5563; }
        .badge-in_progress, .badge-ongoing { background: #DBEAFE; color: #1E40AF; }
        .badge-active, .badge-paid, .badge-completed { background: #D1FAE5; color: #065F46; }
        .badge-inactive, .badge-expired, .badge-overdue { background: #FEE2E2; color: #991B1B; }
        .badge-expiring_soon, .badge-due_soon { background: #FEF3C7; color: #D97706; }
        
        .c-alert { padding: 15px; border-radius: 6px; margin-bottom: 15px; font-weight: 500;}
        .c-alert-urgent { background: #FEE2E2; color: #991B1B; border-left: 4px solid #991B1B; }
        .c-alert-warning { background: #FEF3C7; color: #D97706; border-left: 4px solid #D97706; }
        .c-alert-good { background: #D1FAE5; color: #065F46; border-left: 4px solid #065F46; }
        
        .date-text { font-size: 0.8rem; color: #6B7280; display: block; margin-top: 5px; font-weight: 500;}
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
                    <i class="fa-solid fa-building" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($client['organization_name']); ?>
                </span>
                <a href="settings.php" style="color: #4B5563; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="logout.php" style="color: #DC2626; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </header>

        <main class="main-content fade-in">
            <h1 style="margin-bottom: 10px;">Your Dashboard</h1>
            <p style="color: #6B7280; margin-bottom: 40px;">Welcome back. Here is the status of your assigned projects.</p>

            <div class="card">
                <h3 style="margin-bottom: 20px;">My Projects</h3>
                <?php if ($projects->num_rows > 0): ?>
                    <div class="project-grid">
                        <?php while($p = $projects->fetch_assoc()): ?>
                            <div class="project-card">
                                <span class="badge badge-<?php echo $p['status']; ?>"><?php echo str_replace('_', ' ', ucfirst($p['status'])); ?></span>
                                <h4 style="font-size: 1.25rem; margin-bottom: 5px;"><?php echo htmlspecialchars($p['project_name']); ?></h4>
                                <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 15px; height: 40px; overflow: hidden;"><?php echo str_replace(array("\r\n", "\n"), ' ', mb_substr(htmlspecialchars($p['description']), 0, 80)); ?>...</p>
                                
                                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                                    <span>Progress</span>
                                    <span><?php echo $p['progress_percentage']; ?>%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: <?php echo $p['progress_percentage']; ?>%;"></div>
                                </div>
                                
                                <div style="margin-top: 20px;">
                                    <a href="project_details.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="width: 100%; border-color: var(--primary-blue); color: var(--primary-blue); padding: 8px; display: block;">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #6B7280; padding: 40px 0;">No projects have been assigned to your organization yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="card" style="margin-top: 40px; border-left: 4px solid var(--secondary-green);">
                <h3 style="margin-bottom: 20px;">Service Status & Payments</h3>
                
                <?php if(!empty($alerts)): ?>
                    <div style="margin-bottom: 25px;">
                    <?php foreach($alerts as $alert): ?>
                        <div class="c-alert c-alert-<?php echo $alert['type']; ?>">
                            <i class="fa-solid <?php echo $alert['type'] == 'urgent' ? 'fa-triangle-exclamation' : 'fa-bell'; ?>"></i>
                            <?php echo $alert['msg']; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="c-alert c-alert-good">
                        <i class="fa-solid fa-circle-check"></i> All your services and subscriptions are in good standing!
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: #F3F4F6; padding: 20px; border-radius: 8px; text-align: center;">
                        <i class="fa-solid fa-globe" style="font-size: 1.5rem; color: var(--primary-blue); margin-bottom: 10px; display: block;"></i>
                        <strong style="display: block; margin-bottom: 10px;">Domain Name</strong>
                        <span class="badge badge-<?php echo $tracking['domain_status']; ?>" style="margin-bottom: 0px;"><?php echo str_replace('_', ' ', $tracking['domain_status']); ?></span>
                        <div class="date-text">Exp: <?php echo $tracking['domain_expiry_date'] ? date('M j, Y', strtotime($tracking['domain_expiry_date'])) : 'N/A'; ?></div>
                    </div>
                    <div style="background: #F3F4F6; padding: 20px; border-radius: 8px; text-align: center;">
                        <i class="fa-solid fa-server" style="font-size: 1.5rem; color: var(--primary-blue); margin-bottom: 10px; display: block;"></i>
                        <strong style="display: block; margin-bottom: 10px;">Hosting Plan</strong>
                        <span class="badge badge-<?php echo $tracking['hosting_status']; ?>" style="margin-bottom: 0px;"><?php echo str_replace('_', ' ', $tracking['hosting_status']); ?></span>
                        <div class="date-text">Exp: <?php echo $tracking['hosting_expiry_date'] ? date('M j, Y', strtotime($tracking['hosting_expiry_date'])) : 'N/A'; ?></div>
                    </div>
                    <div style="background: #F3F4F6; padding: 20px; border-radius: 8px; text-align: center;">
                        <i class="fa-solid fa-magnifying-glass-chart" style="font-size: 1.5rem; color: var(--primary-blue); margin-bottom: 10px; display: block;"></i>
                        <strong style="display: block; margin-bottom: 10px;">SEO Services</strong>
                        <span class="badge badge-<?php echo $tracking['seo_status']; ?>" style="margin-bottom: 0px;"><?php echo str_replace('_', ' ', $tracking['seo_status']); ?></span>
                        <div class="date-text">Due: <?php echo $tracking['seo_next_due_date'] ? date('M j, Y', strtotime($tracking['seo_next_due_date'])) : 'N/A'; ?></div>
                    </div>
                    <div style="background: #F3F4F6; padding: 20px; border-radius: 8px; text-align: center;">
                        <i class="fa-solid fa-screwdriver-wrench" style="font-size: 1.5rem; color: var(--primary-blue); margin-bottom: 10px; display: block;"></i>
                        <strong style="display: block; margin-bottom: 10px;">Maintenance</strong>
                        <span class="badge badge-<?php echo $tracking['maintenance_status']; ?>" style="margin-bottom: 0px;"><?php echo str_replace('_', ' ', $tracking['maintenance_status']); ?></span>
                        <div class="date-text">Due: <?php echo $tracking['maintenance_next_due_date'] ? date('M j, Y', strtotime($tracking['maintenance_next_due_date'])) : 'N/A'; ?></div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 40px;">
                <h3 style="margin-bottom: 20px;">Organization Profile</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; color: #4B5563; background: #F9FAFB; padding: 20px; border-radius: 8px;">
                    <div>
                        <strong style="color: #111827;">Email Address:</strong><br>
                        <?php echo htmlspecialchars($client['email']); ?>
                    </div>
                    <div>
                        <strong style="color: #111827;">Phone Number:</strong><br>
                        <?php echo htmlspecialchars($client['phone']); ?>
                    </div>
                    <div>
                        <strong style="color: #111827;">Joined Date:</strong><br>
                        <?php echo date('M j, Y', strtotime($client['created_at'])); ?>
                    </div>
                    <div>
                        <strong style="color: #111827;">Account Status:</strong><br>
                        <span style="color: <?php echo $client['status'] == 'active' ? 'var(--secondary-green)' : '#DC2626'; ?>; font-weight: 600;">
                            <?php echo ucfirst($client['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
