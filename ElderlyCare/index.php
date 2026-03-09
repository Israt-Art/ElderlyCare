<?php
/**
 * Main Landing Page
 * Elderly Care Residence Management & Emergency Support Platform
 */

require_once __DIR__ . '/config/config.php';

// Get page parameter
$page = $_GET['page'] ?? 'home';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span><?php echo SITE_NAME; ?></span>
                </div>
                <nav class="main-nav">
                    <a href="index.php" class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>">Home</a>
                    <a href="index.php?page=about" class="nav-link <?php echo $page === 'about' ? 'active' : ''; ?>">About</a>
                    <a href="index.php?page=services" class="nav-link <?php echo $page === 'services' ? 'active' : ''; ?>">Services</a>
                    <a href="index.php?page=contact" class="nav-link <?php echo $page === 'contact' ? 'active' : ''; ?>">Contact</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="index.php?page=login" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                    <?php else: ?>
                        <?php
                        $role = getCurrentUserRole();
                        if ($role === 'elderly') {
                            $dashboard_url = 'dashboard_elderly.php';
                        } elseif ($role === 'admin') {
                            $dashboard_url = 'dashboard_admin.php';
                        } else {
                            $dashboard_url = 'index.php';
                        }
                        ?>
                        <a href="<?php echo $dashboard_url; ?>" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                        <a href="php/logout.php" class="btn btn-outline">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <?php
        // Display flash messages if any
        displayFlashMessage();
        
        // Load appropriate page
        switch ($page) {
            case 'login':
                include 'pages/login.php';
                break;
            case 'about':
                include 'pages/about.php';
                break;
            case 'services':
                include 'pages/services.php';
                break;
            case 'contact':
                include 'pages/contact.php';
                break;
            case 'home':
            default:
                include 'pages/home.php';
                break;
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-heartbeat"></i> <?php echo SITE_NAME; ?></h3>
                    <p>Comprehensive care and support for elderly residents.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php?page=about">About Us</a></li>
                        <li><a href="index.php?page=services">Services</a></li>
                        <li><a href="index.php?page=contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> +880 1234 567890</p>
                    <p><i class="fas fa-envelope"></i> info@elderlycare.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
