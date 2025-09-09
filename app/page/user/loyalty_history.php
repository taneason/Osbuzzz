<?php
require '../../base.php';
auth();

// Get user's loyalty transaction history
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$stm = $_db->prepare('SELECT COUNT(*) FROM loyalty_transactions WHERE user_id = ?');
$stm->execute([$_user->id]);
$total_transactions = $stm->fetchColumn();
$total_pages = ceil($total_transactions / $limit);

// Get transactions
$stm = $_db->prepare('
    SELECT lt.*, o.order_id as order_number 
    FROM loyalty_transactions lt 
    LEFT JOIN orders o ON lt.order_id = o.order_id 
    WHERE lt.user_id = ? 
    ORDER BY lt.created_at DESC 
    LIMIT ? OFFSET ?
');
$stm->bindValue(1, $_user->id, PDO::PARAM_INT);
$stm->bindValue(2, $limit, PDO::PARAM_INT);
$stm->bindValue(3, $offset, PDO::PARAM_INT);
$stm->execute();
$transactions = $stm->fetchAll();

$_title = 'Loyalty Points History';
include '../../head.php';
?>

<main style="padding: 20px; max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <h1 style="color: #333; margin-bottom: 10px;">Loyalty Points History</h1>
        <div style="background: linear-gradient(135deg, #007cba, #005a87); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h2 style="margin: 0; font-size: 2em;"><?= number_format($_user->loyalty_points) ?></h2>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">Current Balance</p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; font-size: 1.2em;">≈ RM<?= number_format(calculate_discount_from_points($_user->loyalty_points), 2) ?></p>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">Available Discount</p>
                </div>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #495057;">How it works:</h3>
            <ul style="margin: 0; padding-left: 20px; color: #666;">
                <li>Earn <?= get_loyalty_setting('points_per_ringgit', 1) ?> point for every RM1 spent</li>
                <li>Redeem <?= get_loyalty_setting('points_to_ringgit_ratio', 100) ?> points = RM1 discount</li>
                <li>Minimum <?= get_loyalty_setting('minimum_points_redeem', 100) ?> points required to redeem</li>
                <li>Maximum <?= get_loyalty_setting('maximum_discount_percentage', 50) ?>% of order total can be paid with points</li>
            </ul>
        </div>
    </div>

    <?php if (empty($transactions)): ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; color: #ccc; margin-bottom: 20px;">💳</div>
            <h3 style="color: #666; margin-bottom: 10px;">No transactions yet</h3>
            <p style="color: #999; margin-bottom: 20px;">Start shopping to earn loyalty points!</p>
            <a href="../../index.php" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Start Shopping</a>
        </div>
    <?php else: ?>
        <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Date</th>
                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6;">Description</th>
                        <th style="padding: 15px; text-align: center; border-bottom: 1px solid #dee2e6;">Points</th>
                        <th style="padding: 15px; text-align: center; border-bottom: 1px solid #dee2e6;">Type</th>
                        <th style="padding: 15px; text-align: center; border-bottom: 1px solid #dee2e6;">Order</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr style="border-bottom: 1px solid #f8f9fa;">
                        <td style="padding: 15px; color: #666;">
                            <?= date('M j, Y g:i A', strtotime($transaction->created_at)) ?>
                        </td>
                        <td style="padding: 15px;">
                            <?= htmlspecialchars($transaction->description) ?>
                        </td>
                        <td style="padding: 15px; text-align: center; font-weight: bold; 
                                   color: <?= $transaction->points > 0 ? '#28a745' : '#dc3545' ?>;">
                            <?= $transaction->points > 0 ? '+' : '' ?><?= number_format($transaction->points) ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php
                            $type_colors = [
                                'earned' => '#28a745',
                                'redeemed' => '#dc3545', 
                                'bonus' => '#007cba',
                                'expired' => '#6c757d',
                                'refund' => '#fd7e14'
                            ];
                            $color = $type_colors[$transaction->transaction_type] ?? '#666';
                            ?>
                            <span style="background: <?= $color ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">
                                <?= ucfirst($transaction->transaction_type) ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($transaction->order_number): ?>
                                <a href="../user/order_detail.php?id=<?= $transaction->order_id ?>" 
                                   style="color: #007cba; text-decoration: none;">#<?= $transaction->order_number ?></a>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <?php if ($page > 1): ?>
                <a href="?page=1" style="background: #007cba; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 0 2px;">First</a>
                <a href="?page=<?= $page - 1 ?>" style="background: #007cba; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 0 2px;">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span style="background: #495057; color: white; padding: 8px 12px; border-radius: 4px; margin: 0 2px;"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" style="background: #007cba; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 0 2px;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>" style="background: #007cba; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 0 2px;">Next</a>
                <a href="?page=<?= $total_pages ?>" style="background: #007cba; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 0 2px;">Last</a>
            <?php endif; ?>
            
            <div style="margin-top: 10px; color: #666;">
                Page <?= $page ?> of <?= $total_pages ?> (<?= $total_transactions ?> transactions)
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="profile.php" style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Back to Profile</a>
    </div>
</main>

<?php include '../../foot.php'; ?>
