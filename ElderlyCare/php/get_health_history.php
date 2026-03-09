<?php
/**
 * Get Health History
 * Returns health records for the logged-in elderly user
 */

require_once __DIR__ . '/../config/config.php';
requireRole('elderly');

header('Content-Type: application/json');

$resident_id = getCurrentResidentId();

if (!$resident_id) {
    echo json_encode(['error' => 'Resident information not found.']);
    exit();
}

// Get limit parameter (default: 30 records)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 30;

// Connect to database
$conn = getDBConnection();

// Get health records
$sql = "SELECT record_id, sugar_level, blood_pressure, 
               recorded_date, recorded_time, created_at
        FROM health_records 
        WHERE resident_id = ? 
        ORDER BY recorded_date DESC, recorded_time DESC 
        LIMIT ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $resident_id, $limit);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

$stmt->close();
closeDBConnection($conn);

echo json_encode(['success' => true, 'data' => $records]);

?>
