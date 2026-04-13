<?php
function sanitize_input($data) {
    if ($data === null) return '';
    return htmlspecialchars(stripslashes(trim($data)));
}

function generate_random_password($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $count = strlen($chars);
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= substr($chars, $index, 1);
    }
    return $result;
}

function evaluate_subscription_statuses($conn) {
    if (!$conn) return;

    $today = date('Y-m-d');
    
    // Fetch all tracking rows
    $res = $conn->query("SELECT * FROM service_tracking");
    if($res && $res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            $updateNeeded = false;
            $dStatus = $row['domain_status'];
            $hStatus = $row['hosting_status'];
            $sStatus = $row['seo_status'];
            $mStatus = $row['maintenance_status'];
            
            // Domain rules
            if (!empty($row['domain_expiry_date']) && $dStatus != 'pending') {
                $daysToExpiry = (strtotime($row['domain_expiry_date']) - strtotime($today)) / (60 * 60 * 24);
                if ($daysToExpiry < 0 && $dStatus != 'expired') { 
                    $dStatus = 'expired'; $updateNeeded = true; 
                } elseif ($daysToExpiry >= 0 && $daysToExpiry <= 30 && $dStatus != 'expiring_soon' && $dStatus != 'expired') { 
                    $dStatus = 'expiring_soon'; $updateNeeded = true; 
                } elseif ($daysToExpiry > 30 && $dStatus != 'active') { 
                    $dStatus = 'active'; $updateNeeded = true;
                }
            }
            
            // Hosting rules
            if (!empty($row['hosting_expiry_date']) && $hStatus != 'pending') {
                $daysToExpiry = (strtotime($row['hosting_expiry_date']) - strtotime($today)) / (60 * 60 * 24);
                if ($daysToExpiry < 0 && $hStatus != 'expired') { 
                    $hStatus = 'expired'; $updateNeeded = true; 
                } elseif ($daysToExpiry >= 0 && $daysToExpiry <= 30 && $hStatus != 'expiring_soon' && $hStatus != 'expired') { 
                    $hStatus = 'expiring_soon'; $updateNeeded = true; 
                } elseif ($daysToExpiry > 30 && $hStatus != 'active') {
                    $hStatus = 'active'; $updateNeeded = true;
                }
            }
            
            // SEO rules
            if (!empty($row['seo_next_due_date']) && !in_array($sStatus, ['inactive', 'not_started'])) {
                $daysToDue = (strtotime($row['seo_next_due_date']) - strtotime($today)) / (60 * 60 * 24);
                if ($daysToDue < 0 && $sStatus != 'overdue') { 
                    $sStatus = 'overdue'; $updateNeeded = true; 
                } elseif ($daysToDue >= 0 && $daysToDue <= 5 && $sStatus != 'due_soon' && $sStatus != 'overdue') { 
                    $sStatus = 'due_soon'; $updateNeeded = true; 
                } elseif ($daysToDue > 5 && $sStatus != 'active') {
                    $sStatus = 'active'; $updateNeeded = true;
                }
            }
            
            // Maintenance rules
            if (!empty($row['maintenance_next_due_date']) && !in_array($mStatus, ['inactive', 'not_started'])) {
                $daysToDue = (strtotime($row['maintenance_next_due_date']) - strtotime($today)) / (60 * 60 * 24);
                if ($daysToDue < 0 && $mStatus != 'overdue') { 
                    $mStatus = 'overdue'; $updateNeeded = true; 
                } elseif ($daysToDue >= 0 && $daysToDue <= 5 && $mStatus != 'due_soon' && $mStatus != 'overdue') { 
                    $mStatus = 'due_soon'; $updateNeeded = true; 
                } elseif ($daysToDue > 5 && $mStatus != 'active') {
                    $mStatus = 'active'; $updateNeeded = true;
                }
            }
            
            if ($updateNeeded) {
                $stmt = $conn->prepare("UPDATE service_tracking SET domain_status=?, hosting_status=?, seo_status=?, maintenance_status=?, last_checked=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->bind_param("ssssi", $dStatus, $hStatus, $sStatus, $mStatus, $row['id']);
                $stmt->execute();
            }
        }
    }
}
?>
