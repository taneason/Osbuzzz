<?php
require '../../base.php';

$token = get('token');
$success_message = '';
$error_message = '';
$token_valid = false;
$user_email = '';

// Check if token is provided
if (!$token) {
    $error_message = 'Invalid reset link.';
} else {
    // Auto-cleanup: Delete all expired tokens first
    $stm = $_db->prepare('DELETE FROM password_resets WHERE expires_at < NOW()');
    $stm->execute();
    
    // Verify token and get user info (no used check since we delete after use)
    $stm = $_db->prepare('
        SELECT pr.user_id, pr.email, u.username 
        FROM password_resets pr
        JOIN user u ON pr.user_id = u.id
        WHERE pr.token = ? AND pr.expires_at > NOW()
    ');
    $stm->execute([$token]);
    $reset_record = $stm->fetch();
    
    if ($reset_record) {
        $token_valid = true;
        $user_email = $reset_record->email;
        $user_id = $reset_record->user_id;
    } else {
        $error_message = 'This reset link has expired or is invalid.';
    }
}

if (is_post() && $token_valid) {
    $password = req('password');
    $confirm_password = req('confirm_password');
    
    // Validation
    if ($password == '') {
        $_err['password'] = 'Password is required';
    } else if (strlen($password) < 6) {
        $_err['password'] = 'Password must be at least 6 characters';
    }
    
    if ($confirm_password == '') {
        $_err['confirm_password'] = 'Please confirm your password';
    } else if ($password != $confirm_password) {
        $_err['confirm_password'] = 'Passwords do not match';
    }
    
    if (!$_err) {
        // Update password using user_id for better security
        $hashed_password = sha1($password);
        $stm = $_db->prepare('UPDATE user SET password = ? WHERE id = ?');
        $stm->execute([$hashed_password, $user_id]);
        
        // Delete token after use (no need to mark as used)
        $stm = $_db->prepare('DELETE FROM password_resets WHERE token = ?');
        $stm->execute([$token]);
        
        $success_message = 'Your password has been successfully reset. You can now login with your new password.';
        $token_valid = false; // Hide the form
    }
}

$_title = 'Reset Password';
include '../../signuphead.php';
?>

<div class="login_body">
    <main>
        <div class="login_content">
            <img src="/images/logo.png" alt="OSBuzz Logo">
            <div class="wrapper">
                <h1>Reset Password</h1>
                
                <?php if ($success_message): ?>
                    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= $success_message ?>
                        <br><br>
                        <a href="/page/user/login.php" class="btn" style="display: inline-block; text-decoration: none;">
                            Go to Login
                        </a>
                    </div>
                <?php elseif ($error_message): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= $error_message ?>
                        <br><br>
                        <a href="/page/user/forgot_password.php" class="btn" style="display: inline-block; text-decoration: none;">
                            Request New Reset Link
                        </a>
                    </div>
                <?php elseif ($token_valid): ?>
                    <form method="post">
                        <p style="text-align: center; color: #666; margin-bottom: 20px; font-size: 14px;">
                            Enter your new password below.
                        </p>
                        
                        <div class="input_box">
                            <?= html_password('password', 'maxlength="100" placeholder="New Password"') ?>
                            <i class='bx bxs-lock-alt'></i>
                            <?= err('password') ?>
                        </div>
                        
                        <div class="input_box">
                            <?= html_password('confirm_password', 'maxlength="100" placeholder="Confirm New Password"') ?>
                            <i class='bx bxs-lock-alt'></i>
                            <?= err('confirm_password') ?>
                        </div>

                        <button type="submit" class="btn">Reset Password</button>

                        <div class="register_link">
                            <p>Remember your password? 
                            <a href="/page/user/login.php">Back to Login</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php
include '../../foot.php';
?>
