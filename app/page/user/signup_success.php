<?php
require '../../base.php';

$_title = 'Registration Successful';
include '../../head.php';
?>

<main style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 600px; width: 100%; padding: 20px;">
        <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="color: #007cba; font-size: 48px; margin-bottom: 20px;">
                📧
            </div>
            <h2 style="color: #007cba; margin-bottom: 15px;">Check Your Email</h2>
            <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
                Thanks for signing up! We've sent a verification email to your address.<br>
                Please check your email and click the verification link to activate your account.<br>
                <strong style="color: #dc3545;">⚠️ The verification link will expire in 5 minutes!</strong>
            </p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="color: #495057; margin-bottom: 10px;">What's next?</h4>
                <ul style="text-align: left; color: #666; margin: 0; padding-left: 20px;">
                    <li>Check your email inbox (and spam folder) <strong>immediately</strong></li>
                    <li>Click the verification link in the email <strong>within 5 minutes</strong></li>
                    <li>Get <?= get_loyalty_setting('signup_bonus_points', 100) ?> loyalty points as welcome bonus!</li>
                    <li>Start shopping with Osbuzzz</li>
                </ul>
            </div>
            
            <div style="margin-bottom: 20px;">
                <a href="login.php" class="btn" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Go to Login</a>
                <a href="../../index.php" class="btn" style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Browse Products</a>
            </div>
            
            <p style="color: #999; font-size: 0.9em; margin-top: 30px;">
                Didn't receive the email? Check your spam folder or <a href="signup.php" style="color: #007cba;">sign up again</a>.
            </p>
        </div>
    </div>
</main>

<?php include '../../foot.php'; ?>
