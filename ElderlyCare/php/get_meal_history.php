<?php
/**
 * Get Meal History
 * Returns meal choices for the logged-in elderly user
 */

require_once __DIR__ . '/../config/config.php';
requireRole('elderly');

header('Content-Type: application/json');

$resident_id = getCurrentResidentId();

if (!$resident_id) {
    echo json_encode(['error' => 'Resident information not found.']);
    exit();
}

// Get date range parameters (default: last 7 days)
$days = isset($_GET['days']) ? intval($_GET['days']) : 7;

// Connect to database
$conn = getDBConnection();

// Get meal choices (sugar/salt/spicy intake)
$sql = "SELECT meal_id, sugar_intake, salt_intake, spicy_intake, meal_date, selected_at
        FROM meal_choices 
        WHERE resident_id = ? AND meal_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ORDER BY meal_date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $resident_id, $days);
$stmt->execute();
$result = $stmt->get_result();

$meals = [];
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}

$stmt->close();
closeDBConnection($conn);

echo json_encode(['success' => true, 'data' => $meals]);

?>
