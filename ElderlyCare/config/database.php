<?php
/**
 * Database Configuration File
 * Elderly Care Residence Management & Emergency Support Platform
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elderly_care_db');

/**
 * Create database connection
 * @return mysqli|false Returns mysqli connection object or false on failure
 */
function getDBConnection() {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for proper Bangla character support
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Close database connection
 * @param mysqli $conn Database connection object
 */
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

/**
 * Sanitize user input to prevent SQL injection
 * @param mysqli $conn Database connection
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

?>
