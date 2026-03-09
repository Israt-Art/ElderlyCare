<?php
/**
 * Logout Script
 * Destroys session and redirects to login page
 */

require_once __DIR__ . '/../config/config.php';

// Destroy session
session_destroy();

// Redirect to home page
header('Location: ../index.php?page=login');
exit();

?>
