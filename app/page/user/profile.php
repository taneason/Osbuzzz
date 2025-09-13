<?php
require '../../base.php';

auth();
if(is_get()){
    $stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
    $stm->execute([$_user->id]);
    $u = $stm->fetch();
    if (!$u) {
        redirect('/');
    }

    // Update session with latest data
    $_SESSION['user'] = $u;
    $_user = $u;
    $_SESSION['photo'] = $u -> photo;
}


include '../../head.php';
?>


<main class="profile-edit-body">
    <div class="profile-edit-wrapper">
        <h1>My Profile</h1>
        <div class="profile-edit-avatar">
            <img src="<?= $_user->photo ? '/images/userAvatar/' . $_user->photo : '/images/default-avatar.png' ?>" alt="User Avatar">
        </div>
        <div class="profile-edit-form">
            <div class="edit-input-box">
                <label>Email:</label>
                <div>
                    <?= $_user->email ? encode($_user->email) : '<span class="profile-empty">Not set</span>' ?>
                    <?php if ($_user->email_verified): ?>
                        <span style="color: #28a745; font-size: 0.9em; margin-left: 10px;">✓ Verified</span>
                    <?php else: ?>
                        <span style="color: #dc3545; font-size: 0.9em; margin-left: 10px;">⚠ Not verified</span>
                        <a href="/page/user/resend_verification.php?email=<?= urlencode($_user->email) ?>" 
                           style="font-size: 0.85em; margin-left: 5px; color: #007cba; text-decoration: underline;">Verify now</a>
                    <?php endif; ?>
                    
                    <?php
                    // Check for pending email change verification
                    $stm = $_db->prepare('SELECT email FROM email_verification_logs WHERE user_id = ? AND action_type = "email_change" AND verified_at IS NULL AND expires_at > NOW() ORDER BY log_id DESC LIMIT 1');
                    $stm->execute([$_user->id]);
                    $pending_email = $stm->fetchColumn();
                    if ($pending_email):
                    ?>
                        <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; font-size: 0.9em;">
                            <strong>📧 Email Change Pending:</strong><br>
                            New email: <?= encode($pending_email) ?><br>
                            <span style="color: #856404;">Please check your new email and click the verification link within 5 minutes.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($_user && $_user->role != 'Admin'): ?>
            <div class="edit-input-box">
                <label>Loyalty Points:</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 1.2em; font-weight: bold; color: #007cba;"><?= number_format($_user->loyalty_points) ?> points</span>
                    <span style="font-size: 0.9em; color: #666;">
                        ≈ RM<?= number_format(calculate_discount_from_points($_user->loyalty_points), 2) ?> discount
                    </span>
                    <a href="/page/user/loyalty_history.php" style="font-size: 0.9em; color: #007cba; text-decoration: underline;">View History</a>
                </div>
            </div>
            <?php endif; ?>
            <div class="edit-input-box">
                <label>Password:</label>
                <div>
                    ********
                    <a href="/page/user/changePass.php" style="font-size:0.95em;margin-left:10px;color:#3793af;text-decoration:underline;">Change</a>
                </div>
            </div>
            <?php if ($_user && $_user->role != 'Admin'): ?>
            <div class="edit-input-box">
                <label>Shipping Addresses:</label>
                <div>
                    <a href="/page/user/addresses.php" style="color:#3793af;text-decoration:underline;">Manage Addresses</a>
                    <span style="font-size:0.9em;color:#666;margin-left:10px;">Add multiple shipping addresses</span>
                </div>
            </div>
            <div class="edit-input-box">
                <label>My Orders:</label>
                <div>
                    <a href="/page/user/orders.php" style="color:#3793af;text-decoration:underline;">View Order History</a>
                    <span style="font-size:0.9em;color:#666;margin-left:10px;">Track your purchases</span>
                </div>
            </div>
            <?php endif; ?>
            <form id="changeProfileForm" action="/page/user/profileEdit.php" method="get" style="margin-top:24px;">
                <button type="submit" class="edit-btn">Edit Profile</button>
            </form>
        </div>
    </div>
</main>



<?php
include '../../foot.php';
