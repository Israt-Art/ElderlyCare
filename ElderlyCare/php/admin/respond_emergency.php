<?php
/**
 * Respond to Emergency
 * Admin action to respond to an emergency alert
 */

require_once __DIR__ . '/../../config/config.php';
requireRole('admin');

if (!isset($_GET['id'])) {
    redirectWithMessage('../dashboard_admin.php?tab=emergencies', 'error', 'Invalid emergency ID.');
}

$emergency_id = intval($_GET['id']);

// Connect to database
$conn = getDBConnection();

// Get emergency details
$sql = "SELECT * FROM emergency_logs WHERE emergency_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emergency_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    closeDBConnection($conn);
    redirectWithMessage('../dashboard_admin.php?tab=emergencies', 'error', 'Emergency not found or already responded.');
}

$emergency = $result->fetch_assoc();
$stmt->close();

// Update emergency status
$update_sql = "UPDATE emergency_logs 
               SET status = 'in-progress', 
                   responded_by = ?, 
                   response_time = NOW() 
               WHERE emergency_id = ?";
$update_stmt = $conn->prepare($update_sql);
$admin_id = getCurrentUserId();
$update_stmt->bind_param("ii", $admin_id, $emergency_id);

if ($update_stmt->execute()) {
    $update_stmt->close();
    closeDBConnection($conn);
    redirectWithMessage('../dashboard_admin.php?tab=emergencies', 'success', 'Emergency marked as responded. Response time recorded.');
} else {
    $update_stmt->close();
    closeDBConnection($conn);
    redirectWithMessage('../dashboard_admin.php?tab=emergencies', 'error', 'Error updating emergency status. Please try again.');
}

?>
