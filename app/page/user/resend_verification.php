<?php
require '../../base.php';

$email = trim($_GET['email'] ?? '');
$success = false;
$message = '';

if (is_post()) {
    $email = req('email');
    
    if (!$email) {
        $_err['email'] = 'Email is required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    } else {
        // Check if user exists and is not verified
        $stm = $_db->prepare('SELECT id, username, email_verified FROM user WHERE email = ?');
        $stm->execute([$email]);
        $user = $stm->fetch();
        
        if (!$user) {
            $_err['email'] = 'No account found with this email address';
        } else if ($user->email_verified) {
            $_err['email'] = 'This email is already verified';
        } else {
            // Create new verification token
            $token = create_email_verification($user->id, $email, 'registration');
            if ($token) {
                $email_sent = send_verification_email($email, $token, 'registration', $user->username);
                if ($email_sent) {
                    $success = true;
                    $message = 'Verification email sent successfully! Please check your email.';
                } else {
                    $_err['email'] = 'Failed to send verification email. Please try again later.';
                }
            } else {
                $_err['email'] = 'Failed to create verification token. Please try again later.';
            }
        }
    }
}

$_title = 'Resend Verification Email';
include '../../head.php';
?>

<main style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 500px; width: 100%; padding: 20px;">
        <div style="padding: 40px 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <?php if ($success): ?>
                <div style="text-align: center;">
                    <div style="color: #28a745; font-size: 48px; margin-bottom: 20px;">
                        ✓
                    </div>
                    <h2 style="color: #28a745; margin-bottom: 15px;">Email Sent!</h2>
                    <p style="color: #666; margin-bottom: 30px;"><?= htmlspecialchars($message) ?></p>
                    <div>
                        <a href="login.php" class="btn" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Go to Login</a>
                        <a href="../../index.php" class="btn" style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Go to Home</a>
                    </div>
                </div>
            <?php else: ?>
                <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Resend Verification Email</h2>
                
                <form method="post" style="margin-bottom: 20px;">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Email Address:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" 
                               style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;" 
                               placeholder="Enter your email address" required>
                        <?= err('email') ?>
                    </div>
                    
                    <button type="submit" style="width: 100%; background: #007cba; color: white; padding: 12px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                        Send Verification Email
                    </button>
                </form>
                
                <div style="text-align: center;">
                    <p style="color: #666; margin-bottom: 15px;">Remember your login details?</p>
                    <a href="login.php" style="color: #007cba; text-decoration: none;">Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../../foot.php'; ?>
