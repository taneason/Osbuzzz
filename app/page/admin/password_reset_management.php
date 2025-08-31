<?php
require '../../base.php';
auth('Admin'); // Only admins can access this page

// Handle cleanup of expired tokens
if (is_post() && req('action') === 'cleanup') {
    $deleted_count = cleanup_expired_tokens();
    temp('info', "Cleaned up $deleted_count expired password reset tokens.");
    redirect();
}

// Auto-cleanup on page load (optional - helps keep the system clean)
cleanup_expired_tokens();

// Get password reset statistics
$stm = $_db->query('
    SELECT 
        COUNT(*) as total_requests,
        COUNT(CASE WHEN expires_at < NOW() THEN 1 END) as expired_requests,
        COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_requests
    FROM password_resets
');
$stats = $stm->fetch();

// Get recent password reset requests
$stm = $_db->query('
    SELECT 
        pr.*,
        u.username,
        u.email,
        CASE 
            WHEN pr.expires_at < NOW() THEN "Expired"
            ELSE "Active"
        END as status
    FROM password_resets pr
    JOIN user u ON pr.user_id = u.id
    ORDER BY pr.created_at DESC
    LIMIT 20
');
$recent_requests = $stm->fetchAll();

include '../../head.php';
?>

<main style="max-width: 1200px; margin: 20px auto; padding: 20px;">
    <h1>Password Reset Management</h1>
    
    <?= temp('info') ? '<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 20px 0;">' . temp('info') . '</div>' : '' ?>
    
    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #007cba;"><?= $stats->total_requests ?></h3>
            <p style="margin: 5px 0 0 0; color: #666;">Total Requests</p>
        </div>
        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #ffeaa7;">
            <h3 style="margin: 0; color: #856404;"><?= $stats->active_requests ?></h3>
            <p style="margin: 5px 0 0 0; color: #856404;">Active (5 min)</p>
        </div>
        <div style="background: #f8d7da; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #f5c6cb;">
            <h3 style="margin: 0; color: #721c24;"><?= $stats->expired_requests ?></h3>
            <p style="margin: 5px 0 0 0; color: #721c24;">Expired</p>
        </div>
    </div>
    
    <!-- Actions -->
    <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>Admin Actions</h3>
        <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to clean up expired and used tokens?')">
            <input type="hidden" name="action" value="cleanup">
            <button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Clean Up Expired Tokens
            </button>
        </form>
    </div>
    
    <!-- Recent Requests -->
    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="margin: 0; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
            Recent Password Reset Requests (Last 20)
        </h3>
        
        <?php if (empty($recent_requests)): ?>
            <div style="padding: 40px; text-align: center; color: #666;">
                No password reset requests found.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">User</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Email</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Token</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Status</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Created</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6;">Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_requests as $request): ?>
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td style="padding: 12px;">
                                <strong><?= htmlspecialchars($request->username) ?></strong>
                                <br>
                                <small style="color: #666;">@<?= htmlspecialchars($request->username) ?></small>
                            </td>
                            <td style="padding: 12px;"><?= htmlspecialchars($request->email) ?></td>
                            <td style="padding: 12px;">
                                <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                    <?= substr($request->token, 0, 16) ?>...
                                </code>
                            </td>
                            <td style="padding: 12px;">
                                <?php
                                $status_colors = [
                                    'Active' => 'background: #fff3cd; color: #856404;',
                                    'Expired' => 'background: #f8d7da; color: #721c24;'
                                ];
                                ?>
                                <span style="<?= $status_colors[$request->status] ?> padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                    <?= $request->status ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <?= date('M j, Y H:i', strtotime($request->created_at)) ?>
                            </td>
                            <td style="padding: 12px;">
                                <?= date('M j, Y H:i', strtotime($request->expires_at)) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="index.php" style="color: #007cba; text-decoration: none;">← Back to Admin Dashboard</a>
    </div>
</main>

<?php include '../../foot.php'; ?>
