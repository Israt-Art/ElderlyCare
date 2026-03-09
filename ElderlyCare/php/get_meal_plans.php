<?php
/**
 * Get Available Meal Plans
 * Returns available meal plans based on user's diet type
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$resident_id = getCurrentResidentId();
$meal_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Connect to database
$conn = getDBConnection();

// If user is logged in as elderly, get their diet type
$diet_type = null;
if ($resident_id) {
    $diet_sql = "SELECT diet_type FROM medical_info WHERE resident_id = ?";
    $diet_stmt = $conn->prepare($diet_sql);
    $diet_stmt->bind_param("i", $resident_id);
    $diet_stmt->execute();
    $diet_result = $diet_stmt->get_result();
    if ($diet_result->num_rows === 1) {
        $diet_row = $diet_result->fetch_assoc();
        $diet_type = $diet_row['diet_type'];
    }
    $diet_stmt->close();
}

// Get meal plans
if ($diet_type) {
    // Get plans matching user's diet type or general plans
    $sql = "SELECT plan_id, meal_type, meal_name, meal_description, diet_type, calories, price
            FROM meal_plans 
            WHERE plan_date = ? AND is_available = TRUE 
            AND (diet_type = ? OR diet_type = 'normal')
            ORDER BY FIELD(meal_type, 'breakfast', 'lunch', 'snacks', 'dinner'), diet_type";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $meal_date, $diet_type);
} else {
    // Get all available plans
    $sql = "SELECT plan_id, meal_type, meal_name, meal_description, diet_type, calories, price
            FROM meal_plans 
            WHERE plan_date = ? AND is_available = TRUE
            ORDER BY FIELD(meal_type, 'breakfast', 'lunch', 'snacks', 'dinner')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $meal_date);
}

$stmt->execute();
$result = $stmt->get_result();

$meals = [];
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}

$stmt->close();
closeDBConnection($conn);

echo json_encode(['success' => true, 'data' => $meals, 'diet_type' => $diet_type]);

?>
