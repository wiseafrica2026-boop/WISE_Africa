<?php
// admin/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" style="width: 250px; background: var(--white); box-shadow: 2px 0 10px rgba(0,0,0,0.05); padding: 30px 0; display: flex; flex-direction: column;">
    <div class="sidebar-logo" style="padding: 0 20px; font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--primary-blue); margin-bottom: 40px;">
        WISE <span style="color: var(--secondary-green);">Africa</span>
    </div>
    
    <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" style="display: block; padding: 15px 20px; color: <?php echo $currentPage == 'dashboard.php' ? 'var(--primary-blue)' : '#4B5563'; ?>; font-weight: 500; border-left: 4px solid <?php echo $currentPage == 'dashboard.php' ? 'var(--primary-blue)' : 'transparent'; ?>; background: <?php echo $currentPage == 'dashboard.php' ? '#F3F4F6' : 'transparent'; ?>; text-decoration: none;">
        <i class="fa-solid fa-chart-pie" style="margin-right: 10px; width: 20px; text-align: center;"></i> Dashboard
    </a>
    
    <a href="applications.php" class="nav-item <?php echo $currentPage == 'applications.php' ? 'active' : ''; ?>" style="display: block; padding: 15px 20px; color: <?php echo $currentPage == 'applications.php' ? 'var(--primary-blue)' : '#4B5563'; ?>; font-weight: 500; border-left: 4px solid <?php echo $currentPage == 'applications.php' ? 'var(--primary-blue)' : 'transparent'; ?>; background: <?php echo $currentPage == 'applications.php' ? '#F3F4F6' : 'transparent'; ?>; text-decoration: none;">
        <i class="fa-solid fa-inbox" style="margin-right: 10px; width: 20px; text-align: center;"></i> Applications
    </a>
    
    <a href="clients.php" class="nav-item <?php echo $currentPage == 'clients.php' ? 'active' : ''; ?>" style="display: block; padding: 15px 20px; color: <?php echo $currentPage == 'clients.php' ? 'var(--primary-blue)' : '#4B5563'; ?>; font-weight: 500; border-left: 4px solid <?php echo $currentPage == 'clients.php' ? 'var(--primary-blue)' : 'transparent'; ?>; background: <?php echo $currentPage == 'clients.php' ? '#F3F4F6' : 'transparent'; ?>; text-decoration: none;">
        <i class="fa-solid fa-users" style="margin-right: 10px; width: 20px; text-align: center;"></i> Clients
    </a>
    
    <a href="projects.php" class="nav-item <?php echo $currentPage == 'projects.php' ? 'active' : ''; ?>" style="display: block; padding: 15px 20px; color: <?php echo $currentPage == 'projects.php' ? 'var(--primary-blue)' : '#4B5563'; ?>; font-weight: 500; border-left: 4px solid <?php echo $currentPage == 'projects.php' ? 'var(--primary-blue)' : 'transparent'; ?>; background: <?php echo $currentPage == 'projects.php' ? '#F3F4F6' : 'transparent'; ?>; text-decoration: none;">
        <i class="fa-solid fa-bars-progress" style="margin-right: 10px; width: 20px; text-align: center;"></i> Projects
    </a>
    
    <a href="services.php" class="nav-item <?php echo $currentPage == 'services.php' ? 'active' : ''; ?>" style="display: block; padding: 15px 20px; color: <?php echo $currentPage == 'services.php' ? 'var(--primary-blue)' : '#4B5563'; ?>; font-weight: 500; border-left: 4px solid <?php echo $currentPage == 'services.php' ? 'var(--primary-blue)' : 'transparent'; ?>; background: <?php echo $currentPage == 'services.php' ? '#F3F4F6' : 'transparent'; ?>; text-decoration: none;">
        <i class="fa-solid fa-server" style="margin-right: 10px; width: 20px; text-align: center;"></i> Services
    </a>
    
    <a href="logout.php" class="nav-item" style="display: block; padding: 15px 20px; color: #DC2626; font-weight: 500; margin-top: auto; border-left: 4px solid transparent; text-decoration: none;">
        <i class="fa-solid fa-arrow-right-from-bracket" style="margin-right: 10px; width: 20px; text-align: center;"></i> Logout
    </a>
</aside>
