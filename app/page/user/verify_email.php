<?php
require '../../base.php';

$token = trim($_GET['token'] ?? '');
$message = '';
$success = false;

if ($token) {
    $result = verify_email_token($token);
    $success = $result['success'];
    $message = $result['message'];
    
    if ($success) {
        $type = $result['type'];
        if ($type === 'registration') {
            temp('info', 'Email verified successfully! Welcome to OSBuzz. You have been awarded loyalty points!');
        } else if ($type === 'email_change') {
            // Refresh user session for email change
            if ($_user) {
                refresh_user_session($_user->id);
            }
            temp('info', 'Email address updated successfully!');
        } else {
            temp('info', 'Email verified successfully!');
        }
    } else {
        temp('error', $message);
    }
}

include '../../head.php';
?>

<main style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 500px; width: 100%; padding: 20px;">
        <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <?php if ($token): ?>
                <?php if ($success): ?>
                    <div style="color: #28a745; font-size: 48px; margin-bottom: 20px;">
                        ✓
                    </div>
                    <h2 style="color: #28a745; margin-bottom: 15px;">Email Verified!</h2>
                    <p style="color: #666; margin-bottom: 30px;"><?= htmlspecialchars($message) ?></p>
                    <div style="margin-bottom: 20px;">
                        <?php if (isset($result['type']) && $result['type'] === 'email_change'): ?>
                            <a href="../user/profile.php" class="btn" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;">View Profile</a>
                        <?php else: ?>
                            <a href="../user/login.php" class="btn" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Login</a>
                        <?php endif; ?>
                        <a href="../../index.php" class="btn" style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Go to Home</a>
                    </div>
                <?php else: ?>
                    <div style="color: #dc3545; font-size: 48px; margin-bottom: 20px;">
                        ✗
                    </div>
                    <h2 style="color: #dc3545; margin-bottom: 15px;">Verification Failed</h2>
                    <p style="color: #666; margin-bottom: 30px;"><?= htmlspecialchars($message) ?></p>
                    <div style="margin-bottom: 20px;">
                        <a href="../user/signup.php" class="btn" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Sign Up Again</a>
                        <a href="../../index.php" class="btn" style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Go to Home</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="color: #ffc107; font-size: 48px; margin-bottom: 20px;">
                    ⚠
                </div>
                <h2 style="color: #856404; margin-bottom: 15px;">Invalid Request</h2>
                <p style="color: #666; margin-bottom: 30px;">No verification token provided.</p>
                <a href="../../index.php" class="btn" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Go to Home</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../../foot.php'; ?>
