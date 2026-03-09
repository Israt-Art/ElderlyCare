<?php
/**
 * Pay Bill
 * Allows elderly users to make bill payments
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
    $payment_amount = isset($_POST['payment_amount']) ? floatval($_POST['payment_amount']) : 0;
    $payment_method = trim($_POST['payment_method'] ?? '');
    $reference_number = trim($_POST['reference_number'] ?? '');
    
    // Validate required fields
    if ($payment_amount <= 0) {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Please enter a valid payment amount.');
    }
    
    if (empty($payment_method)) {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Please select a payment method.');
    }
    
    // Validate payment method
    $valid_methods = ['cash', 'card', 'bkash', 'rocket', 'bank_transfer'];
    if (!in_array($payment_method, $valid_methods)) {
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Invalid payment method selected.');
    }
    
    // Connect to database
    $conn = getDBConnection();
    
    // Get current account balance
    $balance_sql = "SELECT current_balance FROM account_balance WHERE resident_id = ?";
    $balance_stmt = $conn->prepare($balance_sql);
    $balance_stmt->bind_param("i", $resident_id);
    $balance_stmt->execute();
    $balance_result = $balance_stmt->get_result();
    
    $current_balance = 0;
    if ($balance_result->num_rows > 0) {
        $balance_row = $balance_result->fetch_assoc();
        $current_balance = floatval($balance_row['current_balance'] ?? 0);
    }
    $balance_stmt->close();
    
    // Calculate new balance
    $new_balance = $current_balance + $payment_amount;
    
    // Get package type (Normal or Premium)
    $package_sql = "SELECT is_active FROM premium_services WHERE resident_id = ? AND is_active = TRUE";
    $package_stmt = $conn->prepare($package_sql);
    $package_stmt->bind_param("i", $resident_id);
    $package_stmt->execute();
    $package_result = $package_stmt->get_result();
    $package_type = ($package_result->num_rows > 0) ? 'Premium' : 'Normal';
    $package_stmt->close();
    
    // Sanitize inputs
    $reference_number = sanitizeInput($conn, $reference_number);
    $description = "Bill payment via " . ucfirst(str_replace('_', ' ', $payment_method));
    if (!empty($reference_number)) {
        $description .= " (Ref: $reference_number)";
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert payment record
        $payment_sql = "INSERT INTO payments 
                       (resident_id, transaction_type, amount, payment_method, transaction_date, 
                        description, reference_number, balance_after, package_type, processed_by) 
                       VALUES (?, 'deposit', ?, ?, CURDATE(), ?, ?, ?, ?, ?)";
        
        $admin_id = getCurrentUserId();
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("idsssdsi", 
            $resident_id,
            $payment_amount,
            $payment_method,
            $description,
            $reference_number,
            $new_balance,
            $package_type,
            $admin_id
        );
        
        if (!$payment_stmt->execute()) {
            throw new Exception('Error recording payment transaction.');
        }
        $payment_stmt->close();
        
        // Update or insert account balance
        $update_balance_sql = "INSERT INTO account_balance (resident_id, current_balance, last_transaction_date) 
                              VALUES (?, ?, CURDATE())
                              ON DUPLICATE KEY UPDATE 
                              current_balance = ?, 
                              last_transaction_date = CURDATE()";
        
        $update_stmt = $conn->prepare($update_balance_sql);
        $update_stmt->bind_param("idd", $resident_id, $new_balance, $new_balance);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Error updating account balance.');
        }
        $update_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        redirectWithMessage('../dashboard_elderly.php', 'success', 
            "Payment of " . formatCurrency($payment_amount) . " processed successfully! New balance: " . formatCurrency($new_balance));
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        redirectWithMessage('../dashboard_elderly.php', 'error', 'Error processing payment: ' . $e->getMessage());
    }
    
    closeDBConnection($conn);
} else {
    header('Location: ../dashboard_elderly.php');
    exit();
}

?>
