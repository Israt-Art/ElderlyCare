<?php
/**
 * Admin/Management Dashboard
 * Dashboard for admin to view residents, health updates, meals, emergencies, and finances
 */

require_once __DIR__ . '/config/config.php';
requireRole('admin');

// Connect to database
$conn = getDBConnection();

// Get today's date
$today = date('Y-m-d');

// Get statistics
$stats = [];

// Total residents
$stats_sql = "SELECT COUNT(*) as total FROM residents WHERE is_active = TRUE";
$stats_result = $conn->query($stats_sql);
$stats['total_residents'] = $stats_result->fetch_assoc()['total'];

// Today's health updates
$stats_sql = "SELECT COUNT(DISTINCT resident_id) as total FROM health_records WHERE recorded_date = CURDATE()";
$stats_result = $conn->query($stats_sql);
$stats['today_health_updates'] = $stats_result->fetch_assoc()['total'];

// Pending emergencies
$stats_sql = "SELECT COUNT(*) as total FROM emergency_logs WHERE status = 'pending'";
$stats_result = $conn->query($stats_sql);
$stats['pending_emergencies'] = $stats_result->fetch_assoc()['total'];

// Today's meal selections
$stats_sql = "SELECT COUNT(DISTINCT resident_id) as total FROM meal_choices WHERE meal_date = CURDATE()";
$stats_result = $conn->query($stats_sql);
$stats['today_meals'] = $stats_result->fetch_assoc()['total'];

// Get pending emergencies
$emergency_sql = "SELECT e.*, r.name_en, r.room_number, r.phone, r.emergency_contact 
                  FROM emergency_logs e
                  JOIN residents r ON e.resident_id = r.resident_id
                  WHERE e.status = 'pending'
                  ORDER BY e.emergency_time DESC
                  LIMIT 10";
$emergencies = $conn->query($emergency_sql)->fetch_all(MYSQLI_ASSOC);

// Get today's health updates
$health_sql = "SELECT h.*, r.name_en, r.room_number
               FROM health_records h
               JOIN residents r ON h.resident_id = r.resident_id
               WHERE h.recorded_date = CURDATE()
               ORDER BY h.recorded_time DESC
               LIMIT 20";
$today_health = $conn->query($health_sql)->fetch_all(MYSQLI_ASSOC);

// Get today's meal selections (sugar/salt/spicy intake)
$meals_sql = "SELECT mc.*, r.name_en, r.room_number
              FROM meal_choices mc
              JOIN residents r ON mc.resident_id = r.resident_id
              WHERE mc.meal_date = CURDATE()
              ORDER BY mc.selected_at DESC";
$today_meals = $conn->query($meals_sql)->fetch_all(MYSQLI_ASSOC);

// Get resident list
$residents_sql = "SELECT r.*, m.diet_type, m.has_diabetes, m.has_blood_pressure, m.has_heart_condition,
                         a.current_balance, p.is_active as is_premium,
                         (SELECT COUNT(*) FROM health_records hr WHERE hr.resident_id = r.resident_id AND hr.recorded_date = CURDATE()) as has_health_update_today
                  FROM residents r
                  LEFT JOIN medical_info m ON r.resident_id = m.resident_id
                  LEFT JOIN account_balance a ON r.resident_id = a.resident_id
                  LEFT JOIN premium_services p ON r.resident_id = p.resident_id AND p.is_active = TRUE
                  WHERE r.is_active = TRUE
                  ORDER BY r.room_number";
$residents = $conn->query($residents_sql)->fetch_all(MYSQLI_ASSOC);

// Get recent financial transactions
$payments_sql = "SELECT p.*, r.name_en, r.room_number
                 FROM payments p
                 JOIN residents r ON p.resident_id = r.resident_id
                 ORDER BY p.transaction_time DESC
                 LIMIT 20";
$recent_payments = $conn->query($payments_sql)->fetch_all(MYSQLI_ASSOC);

// Get recent financial transactions (for finance tab)
$payments_sql = "SELECT p.*, r.name_en, r.room_number
                 FROM payments p
                 JOIN residents r ON p.resident_id = r.resident_id
                 ORDER BY p.transaction_time DESC
                 LIMIT 20";
$recent_payments_result = $conn->query($payments_sql);
$recent_payments = $recent_payments_result ? $recent_payments_result->fetch_all(MYSQLI_ASSOC) : [];

closeDBConnection($conn);

// Initialize variables for all tabs
if (!isset($residents)) $residents = [];
if (!isset($today_health)) $today_health = [];
if (!isset($today_meals)) $today_meals = [];
if (!isset($emergencies)) $emergencies = [];
if (!isset($recent_payments)) $recent_payments = [];

// Get current tab
$tab = $_GET['tab'] ?? 'overview';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span><?php echo SITE_NAME; ?> - Admin</span>
                </div>
                <nav class="main-nav">
                    <span>Welcome, Admin</span>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="php/logout.php" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php displayFlashMessage(); ?>
            
            <!-- Dashboard Container -->
            <div class="dashboard-container">
                <!-- Sidebar -->
                <aside class="dashboard-sidebar">
                    <h3><i class="fas fa-tachometer-alt"></i> Admin Panel</h3>
                    <ul class="sidebar-menu">
                        <li><a href="?tab=overview" class="<?php echo $tab === 'overview' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Overview
                        </a></li>
                        <li><a href="?tab=registration" class="<?php echo $tab === 'registration' ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i> Registration
                        </a></li>
                        <li><a href="?tab=residents" class="<?php echo $tab === 'residents' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Residents
                        </a></li>
                        <li><a href="?tab=health" class="<?php echo $tab === 'health' ? 'active' : ''; ?>">
                            <i class="fas fa-heartbeat"></i> Health Updates
                        </a></li>
                        <li><a href="?tab=meals" class="<?php echo $tab === 'meals' ? 'active' : ''; ?>">
                            <i class="fas fa-utensils"></i> Meal Selections
                        </a></li>
                        <li><a href="?tab=emergencies" class="<?php echo $tab === 'emergencies' ? 'active' : ''; ?>">
                            <i class="fas fa-exclamation-triangle"></i> Emergencies
                            <?php if ($stats['pending_emergencies'] > 0): ?>
                                <span class="badge badge-danger"><?php echo $stats['pending_emergencies']; ?></span>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="?tab=finance" class="<?php echo $tab === 'finance' ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill-wave"></i> Financial
                        </a></li>
                        <li><a href="?tab=reports" class="<?php echo $tab === 'reports' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Reports
                        </a></li>
                    </ul>
                </aside>

                <!-- Main Dashboard Content -->
                <div class="dashboard-content">
                    <?php
                    switch ($tab) {
                        case 'registration':
                            include 'pages/admin/registration.php';
                            break;
                        case 'residents':
                            include 'pages/admin/residents.php';
                            break;
                        case 'health':
                            include 'pages/admin/health.php';
                            break;
                        case 'meals':
                            include 'pages/admin/meals.php';
                            break;
                        case 'emergencies':
                            include 'pages/admin/emergencies.php';
                            break;
                        case 'finance':
                            include 'pages/admin/finance.php';
                            break;
                        case 'reports':
                            include 'pages/admin/reports.php';
                            break;
                        case 'overview':
                        default:
                            include 'pages/admin/overview.php';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/dashboard_admin.js"></script>
</body>
</html>
