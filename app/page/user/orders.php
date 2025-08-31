<?php
require '../../base.php';

auth();

// Get user's orders
$stm = $_db->prepare('
    SELECT o.*, COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
');
$stm->execute([$_user->id]);
$orders = $stm->fetchAll();

$_title = 'My Orders';
include '../../head.php';
?>

<main class="orders-page">
    <div class="scrollable-content">
        <div class="container">
            <h1>My Orders</h1>
            
            <div class="page-actions">
                <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <p>You haven't placed any orders yet.</p>
                    <p><a href="/page/shop/sales.php" class="btn btn-primary">Start Shopping</a></p>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?= $order->order_number ?></h3>
                                    <p class="order-date"><?= date('F d, Y', strtotime($order->created_at)) ?></p>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?= $order->order_status ?>"><?= ucfirst($order->order_status) ?></span>
                                    <span class="payment-badge payment-<?= $order->payment_status ?>"><?= ucfirst($order->payment_status) ?></span>
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <div class="order-summary">
                                    <p><strong><?= $order->item_count ?></strong> item(s)</p>
                                    <p><strong>Total: RM<?= number_format($order->grand_total, 2) ?></strong></p>
                                    <p>Payment: <?= ucfirst(str_replace('_', ' ', $order->payment_method)) ?></p>
                                </div>
                                
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order->order_id ?>" class="btn btn-sm btn-primary">View Details</a>
                                    
                                    <?php 
                                    // Allow cancellation for orders that haven't been shipped yet
                                    $can_cancel = in_array($order->order_status, ['pending', 'processing']) && 
                                                  !in_array($order->order_status, ['shipped', 'delivered', 'cancelled']);
                                    
                                    // For paid orders (except COD), show different message
                                    if ($can_cancel): 
                                    ?>
                                        <button class="btn btn-sm btn-outline" onclick="cancelOrder(<?= $order->order_id ?>, '<?= $order->payment_method ?>', '<?= $order->payment_status ?>')">Cancel Order</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
/* Full height layout with more content space */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header adjustments */
header {
    flex-shrink: 0;
    position: relative;
    z-index: 100;
}

/* Main content area - takes most of the space */
.orders-page {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 70vh; /* Ensure minimum height */
    max-height: calc(100vh - 120px); /* Leave space for header/footer but maximize content */
    overflow: hidden;
}

.scrollable-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px 0 40px 0;
    height: 100%;
}

/* Custom scrollbar */
.scrollable-content::-webkit-scrollbar {
    width: 8px;
}

.scrollable-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.scrollable-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 20px;
}

.orders-page h1 {
    color: #2c3e50;
    margin-bottom: 30px;
    text-align: center;
    font-size: 2rem;
}

.page-actions {
    margin-bottom: 30px;
}

.empty-orders {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-orders p {
    margin: 15px 0;
    font-size: 16px;
}

.orders-list {
    display: grid;
    gap: 20px;
    padding-bottom: 20px;
}

.order-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.order-header {
    background: #f8f9fa;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
}

.order-info h3 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 18px;
}

.order-date {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.order-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.status-badge,
.payment-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.payment-pending {
    background: #fff3cd;
    color: #856404;
}

.payment-paid {
    background: #d4edda;
    color: #155724;
}

.payment-failed {
    background: #f8d7da;
    color: #721c24;
}

.order-details {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-summary p {
    margin: 5px 0;
    color: #2c3e50;
}

.order-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    font-weight: bold;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-outline {
    background: transparent;
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

.btn-outline:hover {
    background: #e74c3c;
    color: white;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* Footer - compact but visible */
footer {
    flex-shrink: 0;
    margin-top: auto;
    max-height: 25vh; /* Limit footer height */
    overflow-y: auto;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .orders-page {
        max-height: calc(100vh - 100px); /* Less space on mobile */
    }
    
    .orders-page h1 {
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        padding: 15px;
    }
    
    .order-status {
        align-items: flex-start;
        flex-direction: row;
        gap: 10px;
    }
    
    .order-details {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        padding: 15px;
    }
    
    .order-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .container {
        padding: 0 15px;
    }
    
    footer {
        max-height: 30vh; /* More footer space on mobile if needed */
    }
}

/* Tablet adjustments */
@media (min-width: 769px) and (max-width: 1024px) {
    .orders-page {
        max-height: calc(100vh - 110px);
    }
}
</style>

<script>
function cancelOrder(orderId, paymentMethod, paymentStatus) {
    let confirmMessage = 'Are you sure you want to cancel this order?';
    
    // Different messages based on payment status
    if (paymentMethod !== 'cash_on_delivery' && paymentStatus === 'paid') {
        confirmMessage = 'This order has been paid. Cancelling will require a refund process. Are you sure you want to proceed?';
    }
    
    if (confirm(confirmMessage + ' This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('order_id', orderId);
        
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Cancelling...';
        button.disabled = true;
        
        fetch('cancel_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order cancelled successfully. ' + (data.message || ''));
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel order');
                // Restore button
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel order. Please try again.');
            // Restore button
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}
</script>

<?php
include '../../foot.php';
?>
