<?php
// about.php
require_once 'includes/header.php';
?>
<section class="hero" style="padding: 100px 0 60px; background: var(--primary-blue);">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 15px;">About WISE Africa</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Bridging the digital divide for schools, churches, and enterprises across the continent.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; background: var(--white); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
            <h2>Our Mission</h2>
            <p style="margin-bottom: 20px; color: #4B5563; font-size: 1.05rem;">WISE Africa is dedicated to democratizing digital access by providing completely free websites and digital infrastructure to organizations that would otherwise be left behind in the modern economy. We believe that every school, church, and community enterprise deserves a robust online presence to expand their reach and operational capabilities.</p>
            
            <h2 style="margin-top: 40px;">Our Vision</h2>
            <p style="color: #4B5563; font-size: 1.05rem;">We envision a digitally interconnected Africa where technology acts as an equalizer rather than a barrier. By directly supplying the foundational layer of the internet—hosting, web design, and management software—we link rural and urban institutions directly to global enterprise networks, creating unprecedented opportunities for sponsorship, training, and overall organizational growth.</p>
        </div>
    </div>
    </div>
</section>

<section class="section" style="background-color: var(--light-bg);">
    <div class="container">
        <div class="section-header">
            <h2>Our Support Model</h2>
            <p style="font-size: 1.1rem;">WISE Africa ensures affordability by covering the most expensive initial setup costs, while schools sustain their digital presence through manageable ongoing services.</p>
        </div>
        
        <div style="display: flex; flex-wrap: wrap; gap: 30px; max-width: 1000px; margin: 0 auto;">
            <!-- Left: WISE Covers -->
            <div style="flex: 1; min-width: 300px; background: var(--white); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); border-top: 5px solid var(--secondary-green);">
                <h3 style="color: var(--secondary-green); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check"></i> WISE Africa Covers (FREE)
                </h3>
                <ul style="color: #4B5563; font-size: 1.05rem;">
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-code" style="color: var(--secondary-green); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">Website Development</strong><br><small>Full stack coding and scalable database architecture.</small></div>
                    </li>
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-object-group" style="color: var(--secondary-green); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">UI/UX Design</strong><br><small>Responsive layouts tailored to your organization's brand.</small></div>
                    </li>
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-rocket" style="color: var(--secondary-green); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">Initial Deployment & Setup</strong><br><small>Publishing the system online and generating admin portals.</small></div>
                    </li>
                </ul>
            </div>
            
            <!-- Right: Client Covers -->
            <div style="flex: 1; min-width: 300px; background: var(--white); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); border-top: 5px solid var(--primary-blue);">
                <h3 style="color: var(--primary-blue); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-school"></i> School Covers
                </h3>
                <ul style="color: #4B5563; font-size: 1.05rem;">
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-globe" style="color: var(--primary-blue); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">Domain Registration</strong><br><small>Purchasing your unique ".edu", ".org", or ".com" link.</small></div>
                    </li>
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-server" style="color: var(--primary-blue); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">Hosting Subscription</strong><br><small>Affordable shared hosting to keep the site online 24/7.</small></div>
                    </li>
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-magnifying-glass-chart" style="color: var(--primary-blue); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">SEO Optimization</strong><br><small>Ongoing indexing to rank well in Google search results.</small></div>
                    </li>
                    <li style="margin-bottom: 20px; display: flex; gap: 15px;">
                        <i class="fa-solid fa-screwdriver-wrench" style="color: var(--primary-blue); margin-top: 5px; font-size: 1.2rem;"></i> 
                        <div><strong style="color: #111827;">Maintenance & Updates</strong><br><small>Handling content moderation and monthly server checkups.</small></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>
