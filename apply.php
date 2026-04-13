<?php
// apply.php
session_start();

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP empty password
$dbname = 'wise_africa';

$successMsg = '';
$errorMsg = '';

// Create connection to MySQL to ensure DB exists
// Suppress warnings in case connection fails to handle it cleanly below
$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    $errorMsg = "Database Connection failed: " . $conn->connect_error . ". Please make sure XAMPP MySQL is running.";
} else {
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        $conn->select_db($dbname);
        
        $tableSql = "CREATE TABLE IF NOT EXISTS applications (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            organization_name VARCHAR(255) NOT NULL,
            organization_type VARCHAR(50) NOT NULL,
            location VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            size_count INT(11) NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if ($conn->query($tableSql) !== TRUE) {
            $errorMsg = "Error creating table: " . $conn->error;
        } else {
            // Auto-Migration logic: Check if the old 'school_name' column exists and migrate the table
            $checkColumn = $conn->query("SHOW COLUMNS FROM applications LIKE 'school_name'");
            if ($checkColumn && $checkColumn->num_rows > 0) {
                // Rename columns to new schema
                $conn->query("ALTER TABLE applications CHANGE school_name organization_name VARCHAR(255) NOT NULL");
                $conn->query("ALTER TABLE applications CHANGE student_count size_count INT(11) NOT NULL");
                // Add the new organization_type column if it doesn't exist
                $checkOrgType = $conn->query("SHOW COLUMNS FROM applications LIKE 'organization_type'");
                if ($checkOrgType && $checkOrgType->num_rows == 0) {
                    $conn->query("ALTER TABLE applications ADD organization_type VARCHAR(50) NOT NULL AFTER organization_name");
                }
            }
        }
    } else {
        $errorMsg = "Error creating database: " . $conn->error;
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errorMsg)) {
    // Sanitize inputs
    $orgName = $conn->real_escape_string(trim($_POST['organization_name']));
    $orgType = $conn->real_escape_string(trim($_POST['organization_type']));
    $location = $conn->real_escape_string(trim($_POST['location']));
    $contactPerson = $conn->real_escape_string(trim($_POST['contact_person']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $sizeCount = intval(trim($_POST['size_count']));

    if (empty($orgName) || empty($orgType) || empty($location) || empty($contactPerson) || empty($email) || empty($phone) || empty($sizeCount)) {
        $errorMsg = "Please fill in all required fields.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } else {
        // Insert data using parameterized query logic equivalent for simplicity here
        $insertSql = "INSERT INTO applications (organization_name, organization_type, location, contact_person, email, phone, size_count) 
                      VALUES ('$orgName', '$orgType', '$location', '$contactPerson', '$email', '$phone', '$sizeCount')";
                      
        if ($conn->query($insertSql) === TRUE) {
            $successMsg = "Thank you! Your application for <strong>$orgName</strong> has been successfully submitted. Our team will review it and contact you within 48 hours.";
        } else {
            $errorMsg = "Error submitting application: " . $conn->error;
        }
    }
}

if(!$conn->connect_error) {
    $conn->close();
}

require_once 'includes/header.php';
?>

<section class="hero" style="padding: 80px 0 60px;">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 20px;">Apply for Digital Infrastructure</h1>
        <p style="margin-bottom: 0;">Take the first step towards transforming your organization.</p>
    </div>
</section>

<section class="apply-section fade-in">
    <div class="container">
        <div class="form-container">
            
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
                    <?php echo $successMsg; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i>
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($successMsg)): ?>
            <form action="apply.php" method="POST" id="applicationForm">
                <div class="form-group">
                    <label for="organization_name">Organization Name *</label>
                    <input type="text" id="organization_name" name="organization_name" class="form-control" placeholder="e.g. St. Patrick's High School / Grace Community Church" required>
                </div>

                <div class="form-group">
                    <label for="organization_type">Organization Type *</label>
                    <select id="organization_type" name="organization_type" class="form-control" required style="cursor: pointer; appearance: auto; background-color: var(--white);">
                        <option value="" disabled selected>Select Organization Type</option>
                        <option value="School">School / Educational Institution</option>
                        <option value="Church">Church / Religious Organization</option>
                        <option value="Business Enterprise">Business Enterprise</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location">Location / Region *</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Nairobi, Kenya" required>
                </div>

                <div class="form-group">
                    <label for="contact_person">Contact Person *</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="example@organization.org" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="+254 700 000000" required>
                </div>

                <div class="form-group">
                    <label for="size_count">Estimated Size (Students/Members/Employees) *</label>
                    <input type="number" id="size_count" name="size_count" class="form-control" placeholder="e.g. 450" min="1" required>
                </div>

                <button type="submit" class="btn btn-primary submit-btn" id="submitBtn">
                    Submit Application
                </button>
            </form>
            <?php else: ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.php" class="btn btn-primary">Return to Homepage</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('applicationForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if(form) {
            form.addEventListener('submit', function() {
                // Add loading effect
                submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" style="margin-right: 8px;"></i>Submitting...';
                submitBtn.style.opacity = '0.8';
                submitBtn.style.pointerEvents = 'none';
            });
        }
    });
</script>

<?php
require_once 'includes/footer.php';
?>
