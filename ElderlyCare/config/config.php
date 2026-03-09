<?php
/**
 * General Configuration File
 * Elderly Care Residence Management & Emergency Support Platform
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Site configuration
define('SITE_NAME', 'Elderly Care Residence Management');
define('SITE_URL', 'http://localhost/elderly_care');

// Premium package configuration
define('PREMIUM_PACKAGE_PRICE', 10000.00);
define('PREMIUM_PACKAGE_NAME', 'Premium Package');

// Currency
define('CURRENCY', 'BDT');
define('CURRENCY_SYMBOL', '৳');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Asia/Dhaka');

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user has specific role
 * @param string $role Role to check ('elderly', 'admin', 'family')
 * @return bool True if user has the role, false otherwise
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php?page=login');
        exit();
    }
}

/**
 * Require specific role - redirect if user doesn't have required role
 * @param string $role Required role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ../dashboard.php?error=access_denied');
        exit();
    }
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current resident ID (for elderly users)
 * @return int|null Resident ID or null if not available
 */
function getCurrentResidentId() {
    return $_SESSION['resident_id'] ?? null;
}

/**
 * Redirect with message
 * @param string $url Redirect URL
 * @param string $type Message type (success, error, info)
 * @param string $message Message to display
 */
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit();
}

/**
 * Display flash message if exists
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $type = $flash['type'];
        $message = $flash['message'];
        unset($_SESSION['flash_message']);
        
        $alertClass = 'alert-info';
        if ($type === 'success') $alertClass = 'alert-success';
        if ($type === 'error') $alertClass = 'alert-danger';
        if ($type === 'warning') $alertClass = 'alert-warning';
        
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($message);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
    }
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format (default: 'd M Y')
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    return date('d M Y, h:i A', strtotime($datetime));
}

?>
