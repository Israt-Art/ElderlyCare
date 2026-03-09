<?php
/**
 * Emergency Button Handler
 * Processes emergency alerts from elderly users
 */

require_once __DIR__ . '/../config/config.php';
requireRole('elderly');

// Get current resident ID and room number
$resident_id = getCurrentResidentId();
$room_number = $_SESSION['room_number'] ?? '';

if (!$resident_id || empty($room_number)) {
    redirectWithMessage('../dashboard_elderly.php', 'error', 'Resident information not found.');
}

// Check if emergency button is pressed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emergency'])) {
    // Connect to database
    $conn = getDBConnection();
    
    // Get resident info
    $res_sql = "SELECT name_en, room_number FROM residents WHERE resident_id = ?";
    $res_stmt = $conn->prepare($res_sql);
    $res_stmt->bind_param("i", $resident_id);
    $res_stmt->execute();
    $res_result = $res_stmt->get_result();
    
    if ($res_result->num_rows === 1) {
        $resident = $res_result->fetch_assoc();
        $room_number = $resident['room_number'];
        
        // Get medical info for emergency type
        $med_sql = "SELECT has_diabetes, has_blood_pressure, has_heart_condition FROM medical_info WHERE resident_id = ?";
        $med_stmt = $conn->prepare($med_sql);
        $med_stmt->bind_param("i", $resident_id);
        $med_stmt->execute();
        $med_result = $med_stmt->get_result();
        $medical = $med_result->fetch_assoc();
        
        // Determine emergency type based on medical conditions
        $emergency_type = 'unknown';
        if ($medical && $medical['has_heart_condition']) {
            $emergency_type = 'medical';
        } elseif ($medical && $medical['has_diabetes']) {
            $emergency_type = 'medical';
        }
        
        $description = "Emergency alert from Room " . $room_number . " - " . $resident['name_en'];
        
        // Insert emergency log
        $sql = "INSERT INTO emergency_logs 
                (resident_id, room_number, emergency_type, description, emergency_time, status) 
                VALUES (?, ?, ?, ?, NOW(), 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $resident_id, $room_number, $emergency_type, $description);
        
        if ($stmt->execute()) {
            $stmt->close();
            $res_stmt->close();
            $med_stmt->close();
            closeDBConnection($conn);
            redirectWithMessage('../dashboard_elderly.php', 'success', 'Emergency alert sent! Help is on the way.');
        } else {
            $stmt->close();
            $res_stmt->close();
            $med_stmt->close();
            closeDBConnection($conn);
            redirectWithMessage('../dashboard_elderly.php', 'error', 'Error sending emergency alert. Please try again or contact staff directly.');
        }
    } else {
        $res_stmt->close();
        closeDBConnection($conn);
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Resident information not found.');
    }
} else {
    header('Location: ../dashboard_elderly.php');
    exit();
}

?>
