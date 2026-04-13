<?php
// index.php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch projects to showcase
$stmtProjects = $conn->query("
    SELECT p.project_name, p.description, p.status, c.organization_name 
    FROM projects p 
    JOIN clients c ON p.client_id = c.id 
    WHERE p.status IN ('in_progress', 'active', 'completed') 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
?>

<section class="hero">
    <div class="container">
        <h1>Empowering Schools, Churches, and Enterprises with Free Digital Infrastructure</h1>
        <p>Join WISE Africa in our mission to bridge the digital divide. Get access to professional organization websites, management tools, and modern networking opportunities.</p>
        <div class="hero-btns">
            <a href="apply.php" class="btn btn-primary">Apply Now</a>
            <a href="#demo" class="btn btn-outline">View Demo</a>
        </div>
    </div>
</section>

<section id="what-we-do" class="section features">
    <div class="container">
        <div class="section-header">
            <h2>Our Digital Solutions</h2>
            <p>Comprehensive tools designed specifically for educational institutions in Africa to thrive in the modern world.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <h3>Free Websites</h3>
                <p>High-quality, responsive websites tailored for your organization's unique needs, completely free of charge.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-server"></i>
                </div>
                <h3>Digital Infrastructure</h3>
                <p>Reliable hosting, secure data storage, and scalable infrastructure to keep your school online 24/7.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <h3>Organization Management</h3>
                <p>Powerful software to manage enrollment, memberships, or employees and internal communication.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <h3>Enterprise Connection</h3>
                <p>Link your organization directly to modern enterprises for training, sponsorships, and real-world opportunities.</p>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="section how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>A simple, transparent process to get your organization digitized and ready for the future.</p>
        </div>
        <div class="steps-container">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Submit Application</h3>
                <p>Fill out our short application form with your organization's basic information and needs.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Review & Approval</h3>
                <p>Our team reviews your application and validates your institutional details within 48 hours.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Setup & Deployment</h3>
                <p>We build and deploy your customized digital infrastructure and website platform.</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Training & Launch</h3>
                <p>Your staff receives training on how to use the tools, followed by the official launch.</p>
            </div>
        </div>
    </div>
</section>

<section id="projects" class="section" style="background-color: var(--light-bg);">
    <div class="container">
        <div class="section-header">
            <h2>Projects We've Built</h2>
            <p>Take a look at some of the digital infrastructures we have successfully deployed for organizations across Africa.</p>
        </div>
        <div class="features-grid">
            <?php if ($stmtProjects && $stmtProjects->num_rows > 0): ?>
                <?php while($proj = $stmtProjects->fetch_assoc()): ?>
                    <div class="feature-card" style="text-align: left; padding: 30px;">
                        <span style="display: inline-block; padding: 4px 10px; background: var(--primary-blue); color: var(--white); font-size: 0.8rem; border-radius: 20px; font-weight: 600; margin-bottom: 15px;"><?php echo htmlspecialchars($proj['organization_name']); ?></span>
                        <h3 style="font-size: 1.25rem; margin-bottom: 10px; color: var(--dark-text);"><?php echo htmlspecialchars($proj['project_name']); ?></h3>
                        <p style="color: #4B5563; font-size: 0.9rem;"><?php 
                            $desc = htmlspecialchars($proj['description']);
                            echo (strlen($desc) > 120) ? substr($desc, 0, 120) . '...' : $desc; 
                        ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #6B7280; padding: 40px; background: var(--white); border-radius: 8px;">
                    <i class="fa-solid fa-seedling" style="font-size: 2rem; color: var(--secondary-green); margin-bottom: 15px; display: block;"></i>
                    <p>We are currently onboarding our first organizations.<br>Check back soon to see our completed projects!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Take Your Organization Digital?</h2>
        <p style="margin-bottom: 30px; font-size: 1.2rem; opacity: 0.9;">Join hundreds of organizations already benefiting from our free infrastructure.</p>
        <a href="apply.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 35px; background-color: var(--white); color: var(--secondary-green);">Submit Your Application</a>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>
