<?php
require '../../base.php';
auth('Admin');

$_title = 'User Cleanup Management';
include '../../head.php';

$message = '';
$stats = '';

if (is_post()) {
    $action = req('action');
    
    if ($action === 'cleanup') {
        $deleted_count = cleanup_expired_unverified_users();
        if ($deleted_count !== false) {
            $message = "Successfully cleaned up $deleted_count expired unverified users.";
        } else {
            $message = "Error occurred during cleanup. Check error logs.";
        }
    }
}

// Get current statistics
try {
    $stm = $_db->prepare('SELECT COUNT(*) as total_users FROM user');
    $stm->execute();
    $total_users = $stm->fetch()->total_users;
    
    $stm = $_db->prepare('SELECT COUNT(*) as unverified_users FROM user WHERE email_verified = 0');
    $stm->execute();
    $unverified_users = $stm->fetch()->unverified_users;
    
    $stm = $_db->prepare('
        SELECT COUNT(DISTINCT u.id) as expired_users 
        FROM user u
        INNER JOIN email_verification_logs evl ON u.id = evl.user_id 
        WHERE u.email_verified = 0 
        AND evl.expires_at < NOW()
        AND evl.verified_at IS NULL
    ');
    $stm->execute();
    $expired_users = $stm->fetch()->expired_users;
    
    $stats = [
        'total' => $total_users,
        'unverified' => $unverified_users,
        'expired' => $expired_users
    ];
} catch (Exception $e) {
    $stats = ['error' => $e->getMessage()];
}
?>

<main>
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1>User Cleanup Management</h1>
        
        <?php if ($message): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #155724;">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($stats['error'])): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #721c24;">
                Error: <?= $stats['error'] ?>
            </div>
        <?php else: ?>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                <h3>User Statistics</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Total Users:</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?= $stats['total'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Unverified Users:</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?= $stats['unverified'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Expired Unverified (Ready for cleanup):</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; color: #dc3545;"><strong><?= $stats['expired'] ?></strong></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
        
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>Cleanup Options</h3>
            <p style="color: #666; margin-bottom: 20px;">
                This tool will permanently delete users whose email verification has expired and they haven't verified their email address.
                <br><strong>Warning:</strong> This action cannot be undone.
            </p>
            
            <?php if (isset($stats['expired']) && $stats['expired'] > 0): ?>
                <form method="post" onsubmit="return confirm('Are you sure you want to delete <?= $stats['expired'] ?> expired unverified users? This action cannot be undone.');">
                    <input type="hidden" name="action" value="cleanup">
                    <button type="submit" style="background: #dc3545; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        Delete <?= $stats['expired'] ?> Expired Unverified Users
                    </button>
                </form>
            <?php else: ?>
                <p style="color: #28a745; font-weight: bold;">✓ No expired unverified users found. Database is clean!</p>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <h4>How it works:</h4>
                <ul style="color: #666;">
                    <li>Users who register but don't verify their email within 5 minutes will have expired verification tokens</li>
                    <li>This cleanup tool removes these expired unverified users and their associated data</li>
                    <li>Automatic cleanup runs on every login and registration page visit</li>
                    <li>Verified users are never affected by cleanup</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="index.php" style="color: #007cba; text-decoration: none;">← Back to Admin Dashboard</a>
        </div>
    </div>
</main>

<?php include '../../foot.php'; ?>
