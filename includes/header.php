<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WISE Africa | Bridging Schools, Churches, and Enterprises Through Digital Infrastructure</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="header">
    <div class="container nav-container">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-earth-africa" style="color: var(--secondary-green);"></i>
            WISE <span>Africa</span>
        </a>
        
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="nav-links" id="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="index.php#how-it-works">How It Works</a>
            <a href="index.php#projects">Projects</a>
            <a href="client/login.php">Client Login</a>
            <a href="admin/login.php">Admin Login</a>
            <a href="apply.php" class="btn btn-primary">Apply Now</a>
        </nav>
    </div>
</header>
