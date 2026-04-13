<?php
// admin/services.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin_login();

// Auto-evaluate subscriptions
evaluate_subscription_statuses($conn);

$successMsg = '';
$errorMsg = '';

// Handle Renewals
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $trackingId = intval($_POST['tracking_id']);
    $clientId = intval($_POST['client_id']);
    $today = date('Y-m-d');
    
    if ($_POST['action'] == 'renew_domain') {
        $expiry = date('Y-m-d', strtotime('+1 year'));
        $update = $conn->prepare("UPDATE service_tracking SET domain_start_date=?, domain_expiry_date=?, domain_status='active' WHERE id=?");
        $update->bind_param("ssi", $today, $expiry, $trackingId);
        if($update->execute()) {
            $conn->query("INSERT INTO payment_logs (client_id, service_type, payment_date, next_due_date) VALUES ($clientId, 'domain', '$today', '$expiry')");
            $successMsg = "Domain reliably renewed for 1 year.";
        }
    } 
    elseif ($_POST['action'] == 'renew_hosting') {
        $expiry = date('Y-m-d', strtotime('+1 year'));
        $update = $conn->prepare("UPDATE service_tracking SET hosting_start_date=?, hosting_expiry_date=?, hosting_status='active' WHERE id=?");
        $update->bind_param("ssi", $today, $expiry, $trackingId);
        if($update->execute()) {
            $conn->query("INSERT INTO payment_logs (client_id, service_type, payment_date, next_due_date) VALUES ($clientId, 'hosting', '$today', '$expiry')");
            $successMsg = "Hosting successfully renewed for 1 year.";
        }
    }
    elseif ($_POST['action'] == 'renew_seo') {
        $expiry = date('Y-m-d', strtotime('+1 month'));
        $update = $conn->prepare("UPDATE service_tracking SET seo_last_payment_date=?, seo_next_due_date=?, seo_status='active' WHERE id=?");
        $update->bind_param("ssi", $today, $expiry, $trackingId);
        if($update->execute()) {
            $conn->query("INSERT INTO payment_logs (client_id, service_type, payment_date, next_due_date) VALUES ($clientId, 'seo', '$today', '$expiry')");
            $successMsg = "SEO Services renewed for 1 month.";
        }
    }
    elseif ($_POST['action'] == 'renew_maintenance') {
        $expiry = date('Y-m-d', strtotime('+1 month'));
        $update = $conn->prepare("UPDATE service_tracking SET maintenance_last_payment_date=?, maintenance_next_due_date=?, maintenance_status='active' WHERE id=?");
        $update->bind_param("ssi", $today, $expiry, $trackingId);
        if($update->execute()) {
            $conn->query("INSERT INTO payment_logs (client_id, service_type, payment_date, next_due_date) VALUES ($clientId, 'maintenance', '$today', '$expiry')");
            $successMsg = "Maintenance Plan renewed for 1 month.";
        }
    }
    
    // Evaluate immediately after update in case
    evaluate_subscription_statuses($conn);
}

// Filters logic
$filterWHERE = "1=1";
if(isset($_GET['filter'])) {
    if($_GET['filter'] == 'expired') {
        $filterWHERE = "(st.domain_status='expired' OR st.hosting_status='expired')";
    } elseif($_GET['filter'] == 'overdue') {
        $filterWHERE = "(st.seo_status='overdue' OR st.maintenance_status='overdue')";
    } elseif($_GET['filter'] == 'due_soon') {
        $filterWHERE = "(st.domain_status='expiring_soon' OR st.hosting_status='expiring_soon' OR st.seo_status='due_soon' OR st.maintenance_status='due_soon')";
    }
}

$sql = "SELECT st.*, c.organization_name, c.email FROM clients c INNER JOIN service_tracking st ON c.id = st.client_id WHERE $filterWHERE ORDER BY c.organization_name ASC";
$servicesResult = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Billing | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; background: var(--light-bg); }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .card { background: var(--white); padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); overflow-x: auto;}
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { font-weight: 600; color: #4B5563; background: #F9FAFB; }
        
        .svc-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; }
        
        .svc-pending, .svc-not_started { background: #E5E7EB; color: #4B5563; }
        .svc-active, .svc-paid, .svc-ongoing, .svc-completed { background: #D1FAE5; color: #065F46; }
        .svc-expired, .svc-overdue, .svc-inactive { background: #FEE2E2; color: #991B1B; } /* Red */
        .svc-expiring_soon, .svc-due_soon { background: #FEF3C7; color: #D97706; } /* Yellow */

        .date-display { font-size: 0.8rem; color: #6B7280; display: block; margin-top: 5px;}

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; }
        .close-btn { color: #aaa; float: right; font-size: 1.5rem; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: #333; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="topbar fade-in">
                <div>
                    <h1 style="margin-bottom: 5px;">Service Billing Dashboard</h1>
                    <p style="color: #6B7280; font-weight: 500;">Monitor client subscription dates, renewals, and payments.</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="services.php" class="btn btn-outline" style="color: #4B5563; border-color: #D1D5DB;">All</a>
                    <a href="services.php?filter=expired" class="btn btn-outline" style="color: #991B1B; border-color: #991B1B;">Expired</a>
                    <a href="services.php?filter=overdue" class="btn btn-outline" style="color: #991B1B; border-color: #991B1B;">Overdue</a>
                    <a href="services.php?filter=due_soon" class="btn btn-outline" style="color: #D97706; border-color: #D97706;">Due Soon</a>
                </div>
            </div>

            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success fade-in"><?php echo $successMsg; ?></div>
            <?php endif; ?>

            <div class="card fade-in">
                <table>
                    <thead>
                        <tr>
                            <th>School Name</th>
                            <th>Domain (Annual)</th>
                            <th>Hosting (Annual)</th>
                            <th>SEO (Monthly)</th>
                            <th>Maintenance (Monthly)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($servicesResult && $servicesResult->num_rows > 0): ?>
                            <?php while($row = $servicesResult->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['organization_name']); ?></strong><br>
                                        <small style="color: #6B7280;"><?php echo htmlspecialchars($row['email']); ?></small><br>
                                    </td>
                                    <td>
                                        <span class="svc-badge svc-<?php echo $row['domain_status']; ?>"><?php echo str_replace('_', ' ', $row['domain_status']); ?></span>
                                        <div class="date-display">Exp: <?php echo $row['domain_expiry_date'] ? date('M j, Y', strtotime($row['domain_expiry_date'])) : 'Not Set'; ?></div>
                                    </td>
                                    <td>
                                        <span class="svc-badge svc-<?php echo $row['hosting_status']; ?>"><?php echo str_replace('_', ' ', $row['hosting_status']); ?></span>
                                        <div class="date-display">Exp: <?php echo $row['hosting_expiry_date'] ? date('M j, Y', strtotime($row['hosting_expiry_date'])) : 'Not Set'; ?></div>
                                    </td>
                                    <td>
                                        <span class="svc-badge svc-<?php echo $row['seo_status']; ?>"><?php echo str_replace('_', ' ', $row['seo_status']); ?></span>
                                        <div class="date-display">Due: <?php echo $row['seo_next_due_date'] ? date('M j, Y', strtotime($row['seo_next_due_date'])) : 'Not Set'; ?></div>
                                    </td>
                                    <td>
                                        <span class="svc-badge svc-<?php echo $row['maintenance_status']; ?>"><?php echo str_replace('_', ' ', $row['maintenance_status']); ?></span>
                                        <div class="date-display">Due: <?php echo $row['maintenance_next_due_date'] ? date('M j, Y', strtotime($row['maintenance_next_due_date'])) : 'Not Set'; ?></div>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;" 
                                                onclick="openBillingModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            Update Payments
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 30px;">No clients matching this billing filter.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Billing Modal -->
    <div id="billingModal" class="modal">
        <div class="modal-content fade-in">
            <span class="close-btn" onclick="closeBillingModal()">&times;</span>
            <h2 style="margin-bottom: 20px; font-size: 1.5rem;" id="modalOrgName">Update Billing</h2>
            <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 20px;">Applying a renewal instantly resets the start date to today and pushes the expiry timeframe forward.</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form action="services.php" method="POST" style="display: flex; justify-content: space-between; align-items: center; background: #F9FAFB; padding: 15px; border-radius: 8px;">
                    <input type="hidden" name="tracking_id" class="mTrackingId">
                    <input type="hidden" name="client_id" class="mClientId">
                    <input type="hidden" name="action" value="renew_domain">
                    <strong style="color: #374151;">Domain Registration</strong>
                    <button type="submit" class="btn btn-outline" style="border-color: var(--primary-blue); color: var(--primary-blue); padding: 5px 10px; font-size: 0.85rem;">+1 Year</button>
                </form>

                <form action="services.php" method="POST" style="display: flex; justify-content: space-between; align-items: center; background: #F9FAFB; padding: 15px; border-radius: 8px;">
                    <input type="hidden" name="tracking_id" class="mTrackingId">
                    <input type="hidden" name="client_id" class="mClientId">
                    <input type="hidden" name="action" value="renew_hosting">
                    <strong style="color: #374151;">Hosting Plan</strong>
                    <button type="submit" class="btn btn-outline" style="border-color: var(--primary-blue); color: var(--primary-blue); padding: 5px 10px; font-size: 0.85rem;">+1 Year</button>
                </form>

                <form action="services.php" method="POST" style="display: flex; justify-content: space-between; align-items: center; background: #F9FAFB; padding: 15px; border-radius: 8px;">
                    <input type="hidden" name="tracking_id" class="mTrackingId">
                    <input type="hidden" name="client_id" class="mClientId">
                    <input type="hidden" name="action" value="renew_seo">
                    <strong style="color: #374151;">SEO Services</strong>
                    <button type="submit" class="btn btn-outline" style="border-color: var(--secondary-green); color: var(--secondary-green); padding: 5px 10px; font-size: 0.85rem;">+1 Month</button>
                </form>

                <form action="services.php" method="POST" style="display: flex; justify-content: space-between; align-items: center; background: #F9FAFB; padding: 15px; border-radius: 8px;">
                    <input type="hidden" name="tracking_id" class="mTrackingId">
                    <input type="hidden" name="client_id" class="mClientId">
                    <input type="hidden" name="action" value="renew_maintenance">
                    <strong style="color: #374151;">Maintenance</strong>
                    <button type="submit" class="btn btn-outline" style="border-color: var(--secondary-green); color: var(--secondary-green); padding: 5px 10px; font-size: 0.85rem;">+1 Month</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('billingModal');
        
        function openBillingModal(data) {
            document.getElementById('modalOrgName').innerText = "Update Billing: " + data.organization_name;
            
            // Send hidden inputs to all 4 forms
            let trackInputs = document.querySelectorAll('.mTrackingId');
            let clientInputs = document.querySelectorAll('.mClientId');
            
            trackInputs.forEach(i => i.value = data.id);
            clientInputs.forEach(i => i.value = data.client_id);
            
            modal.style.display = "block";
        }

        function closeBillingModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeBillingModal();
            }
        }
    </script>
</body>
</html>
