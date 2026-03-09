<?php
/**
 * Submit Meal Choices
 * Allows elderly users to select meals for the day
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
    $sugar_intake = trim($_POST['sugar_intake'] ?? '');
    $salt_intake = trim($_POST['salt_intake'] ?? '');
    $spicy_intake = trim($_POST['spicy_intake'] ?? '');
    
    // Validate required fields
    if (empty($sugar_intake) || empty($salt_intake) || empty($spicy_intake)) {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Please select all three options (Sugar, Salt, and Spicy intake).');
    }
    
    // Validate enum values
    $valid_options = ['Low', 'Normal', 'High'];
    if (!in_array($sugar_intake, $valid_options) || !in_array($salt_intake, $valid_options) || !in_array($spicy_intake, $valid_options)) {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Invalid selection. Please select Low, Normal, or High for each option.');
    }
    
    // Connect to database
    $conn = getDBConnection();
    
    // Delete existing meal choices for today (to allow re-selection)
    $delete_sql = "DELETE FROM meal_choices WHERE resident_id = ? AND meal_date = CURDATE()";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $resident_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Insert meal choices (single record with all three intake levels)
    $sql = "INSERT INTO meal_choices (resident_id, meal_type, meal_name, sugar_intake, salt_intake, spicy_intake, meal_date) 
            VALUES (?, 'sugar', 'Sugar Intake', ?, ?, ?, CURDATE())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", 
        $resident_id, 
        $sugar_intake,
        $salt_intake,
        $spicy_intake
    );
    
    if ($stmt->execute()) {
        redirectWithMessage('../dashboard_elderly.php', 'success', 'Meal selection updated successfully!');
    } else {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Error updating meal selection. Please try again.');
    }
    
    $stmt->close();
    closeDBConnection($conn);
} else {
    header('Location: ../dashboard_elderly.php');
    exit();
}

?>
