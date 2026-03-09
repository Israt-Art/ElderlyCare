<!-- Registration Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-user-plus"></i> New Resident Registration
    </h1>
    <p class="dashboard-subtitle">Register a new resident to the system</p>
</div>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_resident'])) {
    $conn = getDBConnection();
    
    // Get form data
    $name_en = trim($_POST['name_en'] ?? '');
    $name_bn = trim($_POST['name_bn'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $address = trim($_POST['address'] ?? '');
    $health_condition = trim($_POST['health_condition'] ?? '');
    $medicine_taken = trim($_POST['medicine_taken'] ?? '');
    $room_number = trim($_POST['room_number'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $guardian_name = trim($_POST['guardian_name'] ?? '');
    $guardian_address = trim($_POST['guardian_address'] ?? '');
    $guardian_phone = trim($_POST['guardian_phone'] ?? '');
    $guardian_nid = trim($_POST['guardian_nid'] ?? '');
    $guardian_relationship = trim($_POST['guardian_relationship'] ?? '');
    $package_choice = trim($_POST['package_choice'] ?? 'Normal');
    $bill_payment = floatval($_POST['bill_payment'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate required fields
    $errors = [];
    if (empty($name_en)) $errors[] = 'User Name is required';
    if (empty($age) || $age < 1) $errors[] = 'Valid Age is required';
    if (empty($room_number)) $errors[] = 'Room Number is required';
    if (empty($guardian_name)) $errors[] = 'Guardian Name is required';
    if (empty($guardian_phone)) $errors[] = 'Guardian Phone Number is required';
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    
    if (empty($errors)) {
        // Check if room number already exists
        $check_room_sql = "SELECT resident_id FROM residents WHERE room_number = ?";
        $check_stmt = $conn->prepare($check_room_sql);
        $check_stmt->bind_param("s", $room_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = 'Room number already exists. Please choose a different room.';
        }
        $check_stmt->close();
        
        // Check if username already exists
        $check_user_sql = "SELECT user_id FROM users WHERE username = ?";
        $check_user_stmt = $conn->prepare($check_user_sql);
        $check_user_stmt->bind_param("s", $username);
        $check_user_stmt->execute();
        $check_user_result = $check_user_stmt->get_result();
        
        if ($check_user_result->num_rows > 0) {
            $errors[] = 'Username already exists. Please choose a different username.';
        }
        $check_user_stmt->close();
    }
    
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Sanitize inputs
            $name_en = sanitizeInput($conn, $name_en);
            $name_bn = sanitizeInput($conn, $name_bn);
            $address = sanitizeInput($conn, $address);
            $health_condition = sanitizeInput($conn, $health_condition);
            $medicine_taken = sanitizeInput($conn, $medicine_taken);
            $room_number = sanitizeInput($conn, $room_number);
            $phone = sanitizeInput($conn, $phone);
            $guardian_name = sanitizeInput($conn, $guardian_name);
            $guardian_address = sanitizeInput($conn, $guardian_address);
            $guardian_phone = sanitizeInput($conn, $guardian_phone);
            $guardian_nid = sanitizeInput($conn, $guardian_nid);
            $guardian_relationship = sanitizeInput($conn, $guardian_relationship);
            
            // Insert resident
            $resident_sql = "INSERT INTO residents 
                            (name_en, name_bn, age, address, health_condition, medicine_taken, 
                             room_number, phone, emergency_contact, emergency_contact_name,
                             guardian_name, guardian_address, guardian_phone, guardian_nid, 
                             guardian_relationship, package_choice, admission_date, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), TRUE)";
            
            $resident_stmt = $conn->prepare($resident_sql);
            $resident_stmt->bind_param("ssisssssssssssss",
                $name_en, $name_bn, $age, $address, $health_condition, $medicine_taken,
                $room_number, $phone, $guardian_phone, $guardian_name,
                $guardian_name, $guardian_address, $guardian_phone, $guardian_nid,
                $guardian_relationship, $package_choice
            );
            
            if (!$resident_stmt->execute()) {
                throw new Exception('Error creating resident record.');
            }
            
            $resident_id = $conn->insert_id;
            $resident_stmt->close();
            
            // Create user account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_sql = "INSERT INTO users (username, password, role, resident_id) VALUES (?, ?, 'elderly', ?)";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("ssi", $username, $hashed_password, $resident_id);
            
            if (!$user_stmt->execute()) {
                throw new Exception('Error creating user account.');
            }
            $user_stmt->close();
            
            // Initialize account balance if payment provided
            if ($bill_payment > 0) {
                $balance_sql = "INSERT INTO account_balance (resident_id, current_balance, last_transaction_date) 
                               VALUES (?, ?, CURDATE())";
                $balance_stmt = $conn->prepare($balance_sql);
                $balance_stmt->bind_param("id", $resident_id, $bill_payment);
                $balance_stmt->execute();
                $balance_stmt->close();
                
                // Record initial payment
                $payment_sql = "INSERT INTO payments 
                              (resident_id, transaction_type, amount, payment_method, transaction_date, 
                               description, balance_after, package_type, processed_by) 
                              VALUES (?, 'deposit', ?, 'cash', CURDATE(), 'Initial registration payment', ?, ?, ?)";
                $payment_stmt = $conn->prepare($payment_sql);
                $admin_id = getCurrentUserId();
                $payment_stmt->bind_param("iddsi", $resident_id, $bill_payment, $bill_payment, $package_choice, $admin_id);
                $payment_stmt->execute();
                $payment_stmt->close();
            } else {
                // Initialize with zero balance
                $balance_sql = "INSERT INTO account_balance (resident_id, current_balance) VALUES (?, 0.00)";
                $balance_stmt = $conn->prepare($balance_sql);
                $balance_stmt->bind_param("i", $resident_id);
                $balance_stmt->execute();
                $balance_stmt->close();
            }
            
            // If Premium package, create premium service record
            if ($package_choice === 'Premium') {
                $premium_sql = "INSERT INTO premium_services (resident_id, package_name, package_price, start_date, is_active) 
                               VALUES (?, 'Premium Package', 10000.00, CURDATE(), TRUE)";
                $premium_stmt = $conn->prepare($premium_sql);
                $premium_stmt->bind_param("i", $resident_id);
                $premium_stmt->execute();
                $premium_stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            redirectWithMessage('dashboard_admin.php?tab=residents', 'success', 
                "Resident registered successfully! Username: $username");
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = 'Registration failed: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
    
    closeDBConnection($conn);
}

if (isset($error_message)) {
    echo "<div class='alert alert-danger'>$error_message</div>";
}
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Registration Form</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="register_resident" value="1">
            
            <h3 style="margin-top: 0;">User Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="name_en">User Name <span class="required">*</span></label>
                    <input type="text" name="name_en" id="name_en" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="name_bn">Name (Bangla) - Optional</label>
                    <input type="text" name="name_bn" id="name_bn" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="age">Age <span class="required">*</span></label>
                    <input type="number" name="age" id="age" class="form-control" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="health_condition">Health Condition</label>
                    <textarea name="health_condition" id="health_condition" class="form-control" rows="2" 
                              placeholder="Describe any existing health conditions..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="medicine_taken">Medicine Taken</label>
                    <textarea name="medicine_taken" id="medicine_taken" class="form-control" rows="2" 
                              placeholder="List current medications..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="room_number">Room Number <span class="required">*</span></label>
                    <input type="text" name="room_number" id="room_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control">
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Guardian Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="guardian_name">Guardian Name <span class="required">*</span></label>
                    <input type="text" name="guardian_name" id="guardian_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="guardian_address">Guardian Address</label>
                    <textarea name="guardian_address" id="guardian_address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="guardian_phone">Guardian Phone Number <span class="required">*</span></label>
                    <input type="text" name="guardian_phone" id="guardian_phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="guardian_nid">Guardian NID Number</label>
                    <input type="text" name="guardian_nid" id="guardian_nid" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="guardian_relationship">Relationship with User</label>
                    <input type="text" name="guardian_relationship" id="guardian_relationship" class="form-control" 
                           placeholder="e.g., Son, Daughter, Spouse, etc.">
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Package & Payment</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="package_choice">Package Choice <span class="required">*</span></label>
                    <select name="package_choice" id="package_choice" class="form-control" required>
                        <option value="Normal">Normal</option>
                        <option value="Premium">Premium</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bill_payment">Bill Payment (Initial)</label>
                    <input type="number" name="bill_payment" id="bill_payment" class="form-control" 
                           step="0.01" min="0" value="0" placeholder="0.00">
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Login Credentials</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" 
                           minlength="6" required>
                    <small class="form-text text-muted">Minimum 6 characters</small>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-user-plus"></i> Submit Registration
                </button>
            </div>
        </form>
    </div>
</div>
