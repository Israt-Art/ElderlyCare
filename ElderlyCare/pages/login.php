<!-- Login Page -->
<section class="login-section">
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Sign In</h2>
                    <p>Please select your user type and enter your credentials</p>
                </div>
                
                <form action="php/login.php" method="POST" class="login-form" id="loginForm">
                    <div class="form-group">
                        <label for="user_type">User Type <span class="required">*</span></label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="elderly">Elderly User</option>
                            <option value="admin">Management/Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">ID / Username <span class="required">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" 
                               placeholder="Enter your ID or username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="password-input-group">
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </div>
                </form>
                
                <div class="login-footer">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Elderly User: Username: <code>elderly1</code>, Password: <code>elderly123</code></p>
                    <p>Admin: Username: <code>admin</code>, Password: <code>admin123</code></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>
