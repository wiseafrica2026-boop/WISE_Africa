<?php
// includes/db.php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP empty password
$dbname = 'wise_africa';

// Create connection
$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error . ". Please make sure XAMPP MySQL is running.");
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($dbname);

// 1. Applications Table (with auto-migration)
$tableApp = "CREATE TABLE IF NOT EXISTS applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(255) NOT NULL,
    organization_type VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    size_count INT(11) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($tableApp) !== TRUE) {
    die("Error creating applications table: " . $conn->error);
}

// Migration logic: Check if the old 'school_name' column exists and migrate the table
$checkColumn = $conn->query("SHOW COLUMNS FROM applications LIKE 'school_name'");
if ($checkColumn && $checkColumn->num_rows > 0) {
    $conn->query("ALTER TABLE applications CHANGE school_name organization_name VARCHAR(255) NOT NULL");
    $conn->query("ALTER TABLE applications CHANGE student_count size_count INT(11) NOT NULL");
    $checkOrgType = $conn->query("SHOW COLUMNS FROM applications LIKE 'organization_type'");
    if ($checkOrgType && $checkOrgType->num_rows == 0) {
        $conn->query("ALTER TABLE applications ADD organization_type VARCHAR(50) NOT NULL AFTER organization_name");
    }
}

// Migration logic: Add status column if it's missing (from old version of the script)
$checkStatus = $conn->query("SHOW COLUMNS FROM applications LIKE 'status'");
if ($checkStatus && $checkStatus->num_rows == 0) {
    $conn->query("ALTER TABLE applications ADD status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER size_count");
}

// 2. Admins Table
$tableAdmins = "CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($tableAdmins) !== TRUE) {
    die("Error creating admins table: " . $conn->error);
}

// Seed default admin if table is empty
$adminCheck = $conn->query("SELECT id FROM admins LIMIT 1");
if ($adminCheck && $adminCheck->num_rows == 0) {
    $defaultHash = password_hash('password123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (full_name, email, password, role) VALUES ('Super Admin', 'admin@wiseafrica.org', '$defaultHash', 'super_admin')");
}

// 3. Clients Table
$tableClients = "CREATE TABLE IF NOT EXISTS clients (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($tableClients) !== TRUE) {
    die("Error creating clients table: " . $conn->error);
}

// 4. Projects Table
$tableProjects = "CREATE TABLE IF NOT EXISTS projects (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('in_progress', 'active', 'inactive', 'completed') DEFAULT 'in_progress',
    progress_percentage INT(3) DEFAULT 0,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
)";
if ($conn->query($tableProjects) !== TRUE) {
    die("Error creating projects table: " . $conn->error);
}

// 5. Project Updates Table
$tableUpdates = "CREATE TABLE IF NOT EXISTS project_updates (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_id INT(11) NOT NULL,
    update_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";
if ($conn->query($tableUpdates) !== TRUE) {
    die("Error creating project updates table: " . $conn->error);
}

// 6. Service Tracking Table
$tableServices = "CREATE TABLE IF NOT EXISTS service_tracking (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    domain_status VARCHAR(50) DEFAULT 'pending',
    domain_start_date DATE NULL,
    domain_expiry_date DATE NULL,
    hosting_status VARCHAR(50) DEFAULT 'pending',
    hosting_start_date DATE NULL,
    hosting_expiry_date DATE NULL,
    seo_status VARCHAR(50) DEFAULT 'not_started',
    seo_last_payment_date DATE NULL,
    seo_next_due_date DATE NULL,
    maintenance_status VARCHAR(50) DEFAULT 'inactive',
    maintenance_last_payment_date DATE NULL,
    maintenance_next_due_date DATE NULL,
    last_checked TIMESTAMP NULL,
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
)";
if ($conn->query($tableServices) !== TRUE) {
    die("Error creating service tracking table: " . $conn->error);
}

// Migration: Update existing service_tracking structures dynamically
$checkDates = $conn->query("SHOW COLUMNS FROM service_tracking LIKE 'domain_start_date'");
if ($checkDates && $checkDates->num_rows == 0) {
    $conn->query("ALTER TABLE service_tracking MODIFY domain_status VARCHAR(50) DEFAULT 'pending'");
    $conn->query("ALTER TABLE service_tracking MODIFY hosting_status VARCHAR(50) DEFAULT 'pending'");
    $conn->query("ALTER TABLE service_tracking MODIFY seo_status VARCHAR(50) DEFAULT 'not_started'");
    $conn->query("ALTER TABLE service_tracking MODIFY maintenance_status VARCHAR(50) DEFAULT 'inactive'");
    
    $conn->query("ALTER TABLE service_tracking ADD domain_start_date DATE NULL AFTER domain_status");
    $conn->query("ALTER TABLE service_tracking ADD domain_expiry_date DATE NULL AFTER domain_start_date");
    $conn->query("ALTER TABLE service_tracking ADD hosting_start_date DATE NULL AFTER hosting_status");
    $conn->query("ALTER TABLE service_tracking ADD hosting_expiry_date DATE NULL AFTER hosting_start_date");
    $conn->query("ALTER TABLE service_tracking ADD seo_last_payment_date DATE NULL AFTER seo_status");
    $conn->query("ALTER TABLE service_tracking ADD seo_next_due_date DATE NULL AFTER seo_last_payment_date");
    $conn->query("ALTER TABLE service_tracking ADD maintenance_last_payment_date DATE NULL AFTER maintenance_status");
    $conn->query("ALTER TABLE service_tracking ADD maintenance_next_due_date DATE NULL AFTER maintenance_last_payment_date");
    $conn->query("ALTER TABLE service_tracking ADD last_checked TIMESTAMP NULL AFTER maintenance_next_due_date");
}

// 7. Payment Logs Table
$tablePayments = "CREATE TABLE IF NOT EXISTS payment_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) DEFAULT 0.00,
    payment_date DATE NOT NULL,
    next_due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
)";
if ($conn->query($tablePayments) !== TRUE) {
    die("Error creating payment logs table: " . $conn->error);
}
?>
