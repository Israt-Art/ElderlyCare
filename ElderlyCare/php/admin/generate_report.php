<?php
/**
 * Generate Monthly Report
 * Generates PDF or HTML report based on selected criteria
 */

require_once __DIR__ . '/../../config/config.php';
requireRole('admin');

// Get report parameters
$report_month = $_POST['report_month'] ?? date('Y-m');
$report_type = $_POST['report_type'] ?? 'comprehensive';
$resident_id = isset($_POST['resident_id']) && !empty($_POST['resident_id']) ? intval($_POST['resident_id']) : null;

// Parse month
$year = substr($report_month, 0, 4);
$month = substr($report_month, 5, 2);
$start_date = "$year-$month-01";
$end_date = date("Y-m-t", strtotime($start_date));

// Connect to database
$conn = getDBConnection();

// Build report data
$report_data = [
    'month' => date('F Y', strtotime($start_date)),
    'start_date' => $start_date,
    'end_date' => $end_date,
    'type' => $report_type
];

// Generate HTML report (can be converted to PDF later)
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Report - <?php echo $report_data['month']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #2c5aa0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #2c5aa0; color: white; }
        .summary { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>Monthly Report - <?php echo $report_data['month']; ?></h1>
    <p><strong>Report Type:</strong> <?php echo ucfirst($report_type); ?></p>
    <p><strong>Date Range:</strong> <?php echo formatDate($start_date); ?> to <?php echo formatDate($end_date); ?></p>

    <?php
    if ($report_type === 'health' || $report_type === 'comprehensive'):
        // Health Summary
        $health_sql = "SELECT r.name_en, r.room_number, 
                      COUNT(h.record_id) as total_records,
                      AVG(h.sugar_level) as avg_sugar,
                      AVG(h.blood_pressure_systolic) as avg_bp_sys,
                      AVG(h.blood_pressure_diastolic) as avg_bp_dia,
                      AVG(h.sleep_time_hours) as avg_sleep
                      FROM residents r
                      LEFT JOIN health_records h ON r.resident_id = h.resident_id 
                      AND h.recorded_date >= ? AND h.recorded_date <= ?
                      WHERE r.is_active = TRUE";
        
        if ($resident_id) {
            $health_sql .= " AND r.resident_id = ?";
            $stmt = $conn->prepare($health_sql);
            $stmt->bind_param("ssi", $start_date, $end_date, $resident_id);
        } else {
            $stmt = $conn->prepare($health_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        $health_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    ?>
        <div class="summary">
            <h2>Health Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Total Records</th>
                        <th>Avg Sugar (mg/dL)</th>
                        <th>Avg BP (mmHg)</th>
                        <th>Avg Sleep (hours)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($health_results as $health): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($health['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($health['name_en']); ?></td>
                        <td><?php echo $health['total_records']; ?></td>
                        <td><?php echo $health['avg_sugar'] ? number_format($health['avg_sugar'], 2) : '-'; ?></td>
                        <td>
                            <?php if ($health['avg_bp_sys'] && $health['avg_bp_dia']): ?>
                                <?php echo number_format($health['avg_bp_sys'], 0); ?>/<?php echo number_format($health['avg_bp_dia'], 0); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo $health['avg_sleep'] ? number_format($health['avg_sleep'], 2) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php
    if ($report_type === 'meals' || $report_type === 'comprehensive'):
        // Meal Summary
        $meal_sql = "SELECT r.name_en, r.room_number, 
                    mc.meal_type,
                    COUNT(mc.meal_id) as meal_count,
                    GROUP_CONCAT(DISTINCT mc.meal_name SEPARATOR ', ') as meals
                    FROM residents r
                    LEFT JOIN meal_choices mc ON r.resident_id = mc.resident_id 
                    AND mc.meal_date >= ? AND mc.meal_date <= ?
                    WHERE r.is_active = TRUE";
        
        if ($resident_id) {
            $meal_sql .= " AND r.resident_id = ?";
            $meal_sql .= " GROUP BY r.resident_id, mc.meal_type";
            $stmt = $conn->prepare($meal_sql);
            $stmt->bind_param("ssi", $start_date, $end_date, $resident_id);
        } else {
            $meal_sql .= " GROUP BY r.resident_id, mc.meal_type";
            $stmt = $conn->prepare($meal_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        $meal_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    ?>
        <div class="summary">
            <h2>Meal Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Meal Type</th>
                        <th>Count</th>
                        <th>Meals Selected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meal_results as $meal): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($meal['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($meal['name_en']); ?></td>
                        <td><?php echo ucfirst($meal['meal_type']); ?></td>
                        <td><?php echo $meal['meal_count']; ?></td>
                        <td><?php echo htmlspecialchars($meal['meals'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php
    if ($report_type === 'financial' || $report_type === 'comprehensive'):
        // Financial Summary
        $finance_sql = "SELECT r.name_en, r.room_number, 
                       SUM(CASE WHEN p.transaction_type = 'deposit' THEN p.amount ELSE 0 END) as deposits,
                       SUM(CASE WHEN p.transaction_type = 'withdrawal' THEN p.amount ELSE 0 END) as withdrawals,
                       SUM(CASE WHEN p.transaction_type = 'service_charge' THEN p.amount ELSE 0 END) as service_charges,
                       COUNT(p.payment_id) as transaction_count,
                       a.current_balance
                       FROM residents r
                       LEFT JOIN payments p ON r.resident_id = p.resident_id 
                       AND p.transaction_date >= ? AND p.transaction_date <= ?
                       LEFT JOIN account_balance a ON r.resident_id = a.resident_id
                       WHERE r.is_active = TRUE";
        
        if ($resident_id) {
            $finance_sql .= " AND r.resident_id = ?";
            $finance_sql .= " GROUP BY r.resident_id";
            $stmt = $conn->prepare($finance_sql);
            $stmt->bind_param("ssi", $start_date, $end_date, $resident_id);
        } else {
            $finance_sql .= " GROUP BY r.resident_id";
            $stmt = $conn->prepare($finance_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        $finance_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    ?>
        <div class="summary">
            <h2>Financial Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Deposits</th>
                        <th>Withdrawals</th>
                        <th>Service Charges</th>
                        <th>Transactions</th>
                        <th>Current Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($finance_results as $finance): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($finance['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($finance['name_en']); ?></td>
                        <td><?php echo formatCurrency($finance['deposits'] ?? 0); ?></td>
                        <td><?php echo formatCurrency($finance['withdrawals'] ?? 0); ?></td>
                        <td><?php echo formatCurrency($finance['service_charges'] ?? 0); ?></td>
                        <td><?php echo $finance['transaction_count']; ?></td>
                        <td><strong><?php echo formatCurrency($finance['current_balance'] ?? 0); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php
    if ($report_type === 'emergencies' || $report_type === 'comprehensive'):
        // Emergency Summary
        $emergency_sql = "SELECT e.*, r.name_en, r.room_number 
                         FROM emergency_logs e
                         JOIN residents r ON e.resident_id = r.resident_id
                         WHERE DATE(e.emergency_time) >= ? AND DATE(e.emergency_time) <= ?";
        
        if ($resident_id) {
            $emergency_sql .= " AND r.resident_id = ?";
            $stmt = $conn->prepare($emergency_sql);
            $stmt->bind_param("ssi", $start_date, $end_date, $resident_id);
        } else {
            $stmt = $conn->prepare($emergency_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        $emergency_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    ?>
        <div class="summary">
            <h2>Emergency Incidents Summary</h2>
            <p><strong>Total Emergencies:</strong> <?php echo count($emergency_results); ?></p>
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emergency_results as $emergency): ?>
                    <tr>
                        <td><?php echo formatDateTime($emergency['emergency_time']); ?></td>
                        <td><?php echo htmlspecialchars($emergency['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($emergency['name_en']); ?></td>
                        <td><?php echo ucfirst($emergency['emergency_type']); ?></td>
                        <td><?php echo ucfirst(str_replace('-', ' ', $emergency['status'])); ?></td>
                        <td><?php echo $emergency['response_time'] ? formatDateTime($emergency['response_time']) : 'Not responded'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <p style="margin-top: 30px; text-align: center; color: #666;">
        Generated on <?php echo date('d M Y, h:i A'); ?> by <?php echo htmlspecialchars($_SESSION['username']); ?>
    </p>

    <p style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Report</button>
    </p>
</body>
</html>

<?php
closeDBConnection($conn);
?>
