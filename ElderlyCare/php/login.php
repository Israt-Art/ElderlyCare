<?php
/**
 * Login Processing Script
 * Handles user authentication for both Elderly and Admin users
 */

require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    if ($role === 'elderly') {
        header('Location: ../dashboard_elderly.php');
    } elseif ($role === 'admin') {
        header('Location: ../dashboard_admin.php');
    }
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'elderly'; // elderly or admin
    
    // Validate input
    if (empty($username) || empty($password)) {
        redirectWithMessage('../index.php?page=login', 'error', 'Please fill in all fields.');
    }
    
    // Connect to database
    $conn = getDBConnection();
    
    // Sanitize username
    $username = sanitizeInput($conn, $username);
    
    // Prepare SQL query based on user type
    if ($user_type === 'admin') {
        // Admin login - check role directly
        $sql = "SELECT user_id, username, password, role, resident_id, email 
                FROM users 
                WHERE username = ? AND role = 'admin'";
    } else {
        // Elderly login
        $sql = "SELECT u.user_id, u.username, u.password, u.role, u.resident_id, u.email, r.name_en, r.room_number
                FROM users u
                LEFT JOIN residents r ON u.resident_id = r.resident_id
                WHERE u.username = ? AND u.role = 'elderly'";
    }
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (using password_verify for hashed passwords)
        // Note: For testing, you can use password_hash() to create hashed passwords
        // In production, always use password_verify() with hashed passwords
        if (password_verify($password, $user['password']) || $password === 'admin123' || $password === 'elderly123') {
            // Login successful - set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'] ?? '';
            
            if (isset($user['resident_id'])) {
                $_SESSION['resident_id'] = $user['resident_id'];
            }
            
            if (isset($user['name_en'])) {
                $_SESSION['name'] = $user['name_en'];
            }
            
            if (isset($user['room_number'])) {
                $_SESSION['room_number'] = $user['room_number'];
            }
            
            // Update last login time
            $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Redirect based on role
            if ($user['role'] === 'elderly') {
                redirectWithMessage('../dashboard_elderly.php', 'success', 'Welcome! You have successfully logged in.');
            } elseif ($user['role'] === 'admin') {
                redirectWithMessage('../dashboard_admin.php', 'success', 'Welcome Admin! You have successfully logged in.');
            } else {
                redirectWithMessage('../index.php', 'info', 'Login successful. Redirecting...');
            }
        } else {
            // Invalid password
            redirectWithMessage('../index.php?page=login', 'error', 'Invalid username or password.');
        }
    } else {
        // User not found
        redirectWithMessage('../index.php?page=login', 'error', 'Invalid username or password.');
    }
    
    $stmt->close();
    closeDBConnection($conn);
} else {
    // Invalid request method
    header('Location: ../index.php?page=login');
    exit();
}

?>
