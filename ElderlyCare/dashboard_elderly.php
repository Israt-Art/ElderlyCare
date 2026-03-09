<?php
/**
 * Elderly User Dashboard
 * Dashboard for elderly residents to update health info and select meals
 */

require_once __DIR__ . '/config/config.php';
requireRole('elderly');

// Get current user information
$resident_id = getCurrentResidentId();
$user_name = $_SESSION['name'] ?? 'User';
$room_number = $_SESSION['room_number'] ?? '';

// Connect to database
$conn = getDBConnection();

// Get resident information
$res_sql = "SELECT r.*, m.diet_type, m.has_diabetes, m.has_blood_pressure, m.has_heart_condition, m.allergies,
                   a.current_balance, p.is_active as is_premium
            FROM residents r
            LEFT JOIN medical_info m ON r.resident_id = m.resident_id
            LEFT JOIN account_balance a ON r.resident_id = a.resident_id
            LEFT JOIN premium_services p ON r.resident_id = p.resident_id AND p.is_active = TRUE
            WHERE r.resident_id = ?";

$res_stmt = $conn->prepare($res_sql);
$res_stmt->bind_param("i", $resident_id);
$res_stmt->execute();
$resident_result = $res_stmt->get_result();
$resident = $resident_result->fetch_assoc();

// Get today's health record
$health_sql = "SELECT * FROM health_records WHERE resident_id = ? AND recorded_date = CURDATE() ORDER BY recorded_time DESC LIMIT 1";
$health_stmt = $conn->prepare($health_sql);
$health_stmt->bind_param("i", $resident_id);
$health_stmt->execute();
$today_health = $health_stmt->get_result()->fetch_assoc();

// Get today's meal choices (sugar/salt/spicy intake)
$meals_sql = "SELECT sugar_intake, salt_intake, spicy_intake FROM meal_choices WHERE resident_id = ? AND meal_date = CURDATE() LIMIT 1";
$meals_stmt = $conn->prepare($meals_sql);
$meals_stmt->bind_param("i", $resident_id);
$meals_stmt->execute();
$meals_result = $meals_stmt->get_result();
$today_meals = $meals_result->fetch_assoc() ?: [];

$res_stmt->close();
$health_stmt->close();
$meals_stmt->close();
closeDBConnection($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elderly Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span><?php echo SITE_NAME; ?></span>
                </div>
                <nav class="main-nav">
                    <span>Welcome, <?php echo htmlspecialchars($user_name); ?> (Room: <?php echo htmlspecialchars($room_number); ?>)</span>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="php/logout.php" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php displayFlashMessage(); ?>
            
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">
                    <i class="fas fa-user-circle"></i> My Dashboard
                </h1>
                <p class="dashboard-subtitle">Room <?php echo htmlspecialchars($room_number); ?> | 
                <?php if ($resident && $resident['is_premium']): ?>
                    <span class="badge badge-success">Premium Member</span>
                <?php else: ?>
                    <span class="badge badge-info">Standard Member</span>
                <?php endif; ?>
                </p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo isset($today_health) ? '✓' : '—'; ?>
                    </div>
                    <div class="stat-label">Health Updated Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo (!empty($today_meals) && (isset($today_meals['sugar_intake']) || isset($today_meals['salt_intake']) || isset($today_meals['spicy_intake']))) ? '✓' : '—'; ?>
                    </div>
                    <div class="stat-label">Meals Selected Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $resident ? formatCurrency($resident['current_balance'] ?? 0) : formatCurrency(0); ?>
                    </div>
                    <div class="stat-label">Account Balance</div>
                </div>
            </div>

            <!-- Pay Bill Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-money-bill-wave"></i> Pay Bill</h2>
                </div>
                <div class="card-body">
                    <form action="php/pay_bill.php" method="POST" id="payBillForm">
                        <div class="form-group">
                            <label for="payment_amount">Payment Amount (BDT) <span class="required">*</span></label>
                            <input type="number" name="payment_amount" id="payment_amount" class="form-control" 
                                   step="0.01" min="0.01" placeholder="Enter amount" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method <span class="required">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bkash">bKash</option>
                                <option value="rocket">Rocket</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reference_number">Reference Number (Optional)</label>
                            <input type="text" name="reference_number" id="reference_number" class="form-control" 
                                   placeholder="Transaction reference number">
                        </div>
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-credit-card"></i> Pay Bill
                        </button>
                    </form>
                </div>
            </div>

            <!-- Health Update Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-heartbeat"></i> Update Health Information</h2>
                </div>
                <div class="card-body">
                    <form action="php/update_health.php" method="POST" id="healthForm">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label for="sugar_level">Sugar Level (mg/dL) <span class="required">*</span></label>
                                <input type="number" name="sugar_level" id="sugar_level" class="form-control" 
                                       step="0.1" placeholder="e.g., 120.5" required
                                       value="<?php echo $today_health['sugar_level'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="bp">Blood Pressure (BP) <span class="required">*</span></label>
                                <input type="text" name="bp" id="bp" class="form-control" 
                                       placeholder="e.g., 120/80" required
                                       value="<?php echo htmlspecialchars($today_health['blood_pressure'] ?? ''); ?>">
                                <small class="form-text text-muted">Enter blood pressure as systolic/diastolic (e.g., 120/80)</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-save"></i> Submit Health Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Health Update Today Display -->
            <?php if (isset($today_health) && $today_health): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-heartbeat"></i> Health Update Today</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <strong>Sugar Level:</strong><br>
                            <span style="font-size: 1.2em;"><?php echo $today_health['sugar_level'] ? $today_health['sugar_level'] . ' mg/dL' : '-'; ?></span>
                        </div>
                        <div>
                            <strong>Blood Pressure:</strong><br>
                            <span style="font-size: 1.2em;"><?php echo htmlspecialchars($today_health['blood_pressure'] ?? '-'); ?></span>
                        </div>
                        <div>
                            <strong>Updated At:</strong><br>
                            <span><?php echo date('h:i A', strtotime($today_health['recorded_time'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Meal Selection Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-utensils"></i> Today's Meals Selection</h2>
                </div>
                <div class="card-body">
                    <form action="php/submit_meals.php" method="POST" id="mealForm">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label for="sugar_intake">Sugar Intake <span class="required">*</span></label>
                                <select name="sugar_intake" id="sugar_intake" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="Low" <?php echo (isset($today_meals['sugar_intake']) && $today_meals['sugar_intake'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="Normal" <?php echo (isset($today_meals['sugar_intake']) && $today_meals['sugar_intake'] === 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                    <option value="High" <?php echo (isset($today_meals['sugar_intake']) && $today_meals['sugar_intake'] === 'High') ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="salt_intake">Salt Intake <span class="required">*</span></label>
                                <select name="salt_intake" id="salt_intake" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="Low" <?php echo (isset($today_meals['salt_intake']) && $today_meals['salt_intake'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="Normal" <?php echo (isset($today_meals['salt_intake']) && $today_meals['salt_intake'] === 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                    <option value="High" <?php echo (isset($today_meals['salt_intake']) && $today_meals['salt_intake'] === 'High') ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="spicy_intake">Spicy Intake <span class="required">*</span></label>
                                <select name="spicy_intake" id="spicy_intake" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="Low" <?php echo (isset($today_meals['spicy_intake']) && $today_meals['spicy_intake'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="Normal" <?php echo (isset($today_meals['spicy_intake']) && $today_meals['spicy_intake'] === 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                    <option value="High" <?php echo (isset($today_meals['spicy_intake']) && $today_meals['spicy_intake'] === 'High') ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large mt-3">
                            <i class="fas fa-check"></i> Update Meal Selection
                        </button>
                    </form>
                </div>
            </div>

            <!-- Meals Selected Today Display -->
            <?php if (!empty($today_meals) && (isset($today_meals['sugar_intake']) || isset($today_meals['salt_intake']) || isset($today_meals['spicy_intake']))): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-utensils"></i> Meals Selected Today</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <strong>Sugar Intake:</strong><br>
                            <span style="font-size: 1.2em;"><?php echo htmlspecialchars($today_meals['sugar_intake'] ?? '-'); ?></span>
                        </div>
                        <div>
                            <strong>Salt Intake:</strong><br>
                            <span style="font-size: 1.2em;"><?php echo htmlspecialchars($today_meals['salt_intake'] ?? '-'); ?></span>
                        </div>
                        <div>
                            <strong>Spicy Intake:</strong><br>
                            <span style="font-size: 1.2em;"><?php echo htmlspecialchars($today_meals['spicy_intake'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- History Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-history"></i> History</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h3><i class="fas fa-heartbeat"></i> Health History</h3>
                            <div id="health-history" style="max-height: 400px; overflow-y: auto;">
                                <p>Loading...</p>
                            </div>
                        </div>
                        <div>
                            <h3><i class="fas fa-utensils"></i> Meal History</h3>
                            <div id="meal-history" style="max-height: 400px; overflow-y: auto;">
                                <p>Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Emergency Button -->
    <form action="php/emergency.php" method="POST" id="emergencyForm">
        <input type="hidden" name="emergency" value="1">
        <button type="submit" class="emergency-button" title="Emergency Alert">
            <i class="fas fa-exclamation-triangle"></i>
        </button>
    </form>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/dashboard_elderly.js"></script>
</body>
</html>
