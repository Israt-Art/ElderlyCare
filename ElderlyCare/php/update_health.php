<?php
/**
 * Update Health Information
 * Allows elderly users to update their daily health records
 */

require_once __DIR__ . '/../config/config.php';
requireRole('elderly');

// Get current resident ID
$resident_id = getCurrentResidentId();

if (!$resident_id) {
    redirectWithMessage('../dashboard_elderly.php', 'error', 'Resident information not found.');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $sugar_level = isset($_POST['sugar_level']) && $_POST['sugar_level'] !== '' ? floatval($_POST['sugar_level']) : null;
    $bp = trim($_POST['bp'] ?? '');
    
    // Validate required fields
    if ($sugar_level === null || empty($bp)) {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Please fill in all required fields (Sugar Level and Blood Pressure).');
    }
    
    // Connect to database
    $conn = getDBConnection();
    
    // Sanitize inputs
    $bp = sanitizeInput($conn, $bp);
    
    // Delete existing health record for today (to allow re-submission)
    $delete_sql = "DELETE FROM health_records WHERE resident_id = ? AND recorded_date = CURDATE()";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $resident_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Prepare SQL query
    $sql = "INSERT INTO health_records 
            (resident_id, sugar_level, blood_pressure, 
             recorded_date, recorded_time, recorded_by) 
            VALUES (?, ?, ?, CURDATE(), CURTIME(), 'resident')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", 
        $resident_id, 
        $sugar_level, 
        $bp
    );
    
    if ($stmt->execute()) {
        redirectWithMessage('../dashboard_elderly.php', 'success', 'Health update submitted successfully!');
    } else {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Error updating health information. Please try again.');
    }
    
    $stmt->close();
    closeDBConnection($conn);
} else {
    header('Location: ../dashboard_elderly.php');
    exit();
}

?>
