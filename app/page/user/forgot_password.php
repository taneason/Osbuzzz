<?php
require '../../base.php';

$success_message = '';
$error_message = '';
$error_class_email = '';

if (is_post()) {
    $email = req('email');
    
    // Validation
    if ($email == '') {
        $_err['email'] = 'Email is required';
        $error_class_email = 'class="error"';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
        $error_class_email = 'class="error"';
    } else {
        // Check if email exists in database
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
        $stm->execute([$email]);
        $user = $stm->fetch();
        
        if (!$user) {
            $_err['email'] = 'Email not found in our system';
            $error_class_email = 'class="error"';
        }
    }
    
    if (!$_err) {
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); // Token expires in 5 minutes
        
        // Check if password_resets table exists, if not create it
        try {
            $stm = $_db->prepare('
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    KEY user_id (user_id),
                    KEY email (email),
                    KEY token (token),
                    UNIQUE KEY token_unique (token),
                    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
                )
            ');
            $stm->execute();
        } catch (Exception $e) {
            // Table might already exist, ignore error
        }
        
        // Auto-cleanup: Delete all expired tokens first
        $stm = $_db->prepare('DELETE FROM password_resets WHERE expires_at < NOW()');
        $stm->execute();
        
        // Delete any existing tokens for this user
        $stm = $_db->prepare('DELETE FROM password_resets WHERE user_id = ?');
        $stm->execute([$user->id]);
        
        // Insert new reset token
        $stm = $_db->prepare('INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)');
        $stm->execute([$user->id, $email, $reset_token, $expires_at]);
        
        // Send email
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/page/user/reset_password.php?token=" . $reset_token;
        
        // Use name if available, otherwise use username
        $display_name = $user->username;
        
        if (send_reset_email($email, $display_name, $reset_link)) {
            $success_message = 'Password reset link has been sent to your email address.';
        } else {
            $error_message = 'Failed to send email. Please try again later.';
        }
    }
}

$_title = 'Forgot Password';
include '../../signuphead.php';
?>

<div class="login_body">
    <main>
        <div class="login_content">
            <img src="/images/logo.png" alt="OSBuzz Logo">
            <div class="wrapper">
                <form method="post">
                    <h1>Forgot Password</h1>
                    <p style="text-align: center; color: #666; margin-bottom: 20px; font-size: 14px;">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                    
                    <?php if ($success_message): ?>
                        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                            <?= $success_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="input_box">
                        <?= html_text('email', 'maxlength="100" placeholder="Enter your email address"'.$error_class_email) ?>
                        <i class='bx bxs-envelope'></i>
                        <?= err('email') ?>
                    </div>
                    <br>
                    <button type="submit" class="btn">Send Reset Link</button>

                    <div class="register_link">
                        <p>Remember your password? 
                        <a href="/page/user/login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include '../../foot.php';
?>
