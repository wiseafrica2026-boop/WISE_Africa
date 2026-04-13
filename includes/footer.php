<?php
// includes/footer.php
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col" style="max-width: 300px;">
                <h3><i class="fa-solid fa-earth-africa" style="color: var(--secondary-green); margin-right: 8px;"></i>WISE Africa</h3>
                <p>Bridging Schools, Churches, and Enterprises Through Digital Infrastructure. Empowering the next generation with modern technology tools.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#about">About Us</a></li>
                    <li><a href="index.php#how-it-works">How It Works</a></li>
                    <li><a href="apply.php">Apply Now</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact Us</h3>
                <ul>
                    <li><i class="fa-solid fa-envelope" style="margin-right: 8px;"></i> info@wiseafrica.org</li>
                    <li><i class="fa-solid fa-phone" style="margin-right: 8px;"></i> +1 (555) 000-0000</li>
                    <li><i class="fa-solid fa-location-dot" style="margin-right: 8px;"></i> Global Operations</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> WISE Africa. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
    // Mobile navigation toggle
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = hamburger.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Scroll animation for elements
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        const cards = document.querySelectorAll('.feature-card, .step');
        cards.forEach(card => {
            card.style.opacity = '0';
            observer.observe(card);
        });
    });
</script>
</body>
</html>
