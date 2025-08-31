<?php
require '../../base.php';

// Redirect to login if not logged in
auth();

$order_id = get('id');

if (!$order_id) {
    temp('error', 'Invalid order ID');
    redirect('/page/user/profile.php');
}

// Get order details - ensure user can only view their own orders
$stm = $_db->prepare('
    SELECT o.*, u.username as customer_name, u.email as customer_email
    FROM orders o
    JOIN user u ON o.user_id = u.id
    WHERE o.order_id = ? AND o.user_id = ?
');
$stm->execute([$order_id, $_user->id]);
$order = $stm->fetch();

if (!$order) {
    temp('error', 'Order not found or access denied');
    redirect('/page/user/profile.php');
}

// Get order items
$stm = $_db->prepare('
    SELECT oi.*, p.photo
    FROM order_items oi
    LEFT JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
    ORDER BY oi.order_item_id
');
$stm->execute([$order_id]);
$order_items = $stm->fetchAll();

// Get order status history
$stm = $_db->prepare('
    SELECT osh.*, u.username as changed_by_name
    FROM order_status_history osh
    LEFT JOIN user u ON osh.changed_by = u.id
    WHERE osh.order_id = ?
    ORDER BY osh.created_at DESC
');
$stm->execute([$order_id]);
$status_history = $stm->fetchAll();

$_title = 'Order Details - #' . $order->order_number;
include '../../head.php';
?>

<main>
    <div class="order-detail-container">
        <!-- Header with back button -->
        <div class="page-header">
            <div class="header-left">
                <a href="/page/user/profile.php" class="btn btn-secondary">← Back to Orders</a>
            </div>
            <div class="header-center">
                <h1>Order Details</h1>
                <p class="order-number">Order #<?= $order->order_number ?></p>
            </div>
            <div class="header-right">
                <span class="order-status status-<?= $order->order_status ?>">
                    <?= ucfirst($order->order_status) ?>
                </span>
            </div>
        </div>

        <div class="order-content">
            <!-- Order Information Card -->
            <div class="order-info-card">
                <h2>Order Information</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Order Date:</span>
                        <span class="value"><?= date('F d, Y \a\t g:i A', strtotime($order->created_at)) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Payment Method:</span>
                        <span class="value"><?= ucfirst(str_replace('_', ' ', $order->payment_method)) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Payment Status:</span>
                        <span class="value payment-<?= $order->payment_status ?>">
                            <?= ucfirst($order->payment_status) ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Order Status:</span>
                        <span class="value status-<?= $order->order_status ?>">
                            <?= ucfirst($order->order_status) ?>
                        </span>
                    </div>
                    <?php if ($order->payment_id): ?>
                    <div class="info-item">
                        <span class="label">Payment ID:</span>
                        <span class="value"><?= $order->payment_id ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items-card">
                <h2>Items Ordered (<?= count($order_items) ?> items)</h2>
                
                <div class="items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../../images/Products/<?= $item->photo ?? 'defaultProduct.png' ?>" 
                                     alt="<?= encode($item->product_name) ?>">
                            </div>
                            
                            <div class="item-details">
                                <h4><?= encode($item->product_name) ?></h4>
                                <div class="item-meta">
                                    <span class="brand">Brand: <?= encode($item->product_brand) ?></span>
                                    <?php if ($item->size): ?>
                                        <span class="size">Size: <?= format_size($item->size) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-pricing">
                                    <span class="unit-price">RM<?= number_format($item->price, 2) ?> each</span>
                                    <span class="quantity">Qty: <?= $item->quantity ?></span>
                                </div>
                            </div>
                            
                            <div class="item-total">
                                <span class="total-price">RM<?= number_format($item->total_price, 2) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>RM<?= number_format($order->total_amount, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping Fee:</span>
                        <span>RM<?= number_format($order->shipping_fee, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (6%):</span>
                        <span>RM<?= number_format($order->tax_amount, 2) ?></span>
                    </div>
                    <div class="summary-row total-row">
                        <span><strong>Total:</strong></span>
                        <span><strong>RM<?= number_format($order->grand_total, 2) ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="shipping-info-card">
                <h2>Shipping Information</h2>
                <div class="shipping-address">
                    <?= nl2br(encode($order->shipping_address)) ?>
                </div>
                
                <?php if ($order->customer_notes): ?>
                    <div class="customer-notes">
                        <h3>Order Notes</h3>
                        <p><?= nl2br(encode($order->customer_notes)) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Status History -->
            <?php if (!empty($status_history)): ?>
            <div class="status-history-card">
                <h2>Order History</h2>
                <div class="timeline">
                    <?php foreach ($status_history as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="status-change">
                                        <?php if ($history->old_status): ?>
                                            Status changed from <strong><?= ucfirst($history->old_status) ?></strong> to <strong><?= ucfirst($history->new_status) ?></strong>
                                        <?php else: ?>
                                            Order <strong><?= ucfirst($history->new_status) ?></strong>
                                        <?php endif; ?>
                                    </span>
                                    <span class="timeline-date"><?= date('M d, Y g:i A', strtotime($history->created_at)) ?></span>
                                </div>
                                <?php if ($history->notes): ?>
                                    <div class="timeline-notes"><?= encode($history->notes) ?></div>
                                <?php endif; ?>
                                <?php if ($history->changed_by_name): ?>
                                    <div class="timeline-user">by <?= encode($history->changed_by_name) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/page/shop/sales.php" class="btn btn-primary">Continue Shopping</a>
            <button onclick="window.print()" class="btn btn-outline">Print Order</button>
            <?php if ($order->order_status == 'processing' && $order->payment_method == 'cash_on_delivery'): ?>
                <a href="#" class="btn btn-secondary" onclick="cancelOrder(<?= $order->order_id ?>)">Cancel Order</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.order-detail-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e8ed;
}

.header-center {
    text-align: center;
    flex: 1;
}

.header-center h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 28px;
}

.order-number {
    color: #666;
    margin: 5px 0 0 0;
    font-size: 16px;
}

.order-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
}

.status-processing {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-shipped {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.order-content {
    display: grid;
    gap: 25px;
}

.order-info-card,
.order-items-card,
.shipping-info-card,
.status-history-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-info-card h2,
.order-items-card h2,
.shipping-info-card h2,
.status-history-card h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item .label {
    font-weight: bold;
    color: #2c3e50;
}

.info-item .value {
    color: #666;
}

.payment-paid {
    color: #27ae60 !important;
    font-weight: bold;
}

.payment-pending {
    color: #f39c12 !important;
    font-weight: bold;
}

.items-list {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 80px;
    margin-right: 20px;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.item-details {
    flex: 1;
    margin-right: 20px;
}

.item-details h4 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 16px;
}

.item-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 8px;
}

.item-meta span {
    color: #666;
    font-size: 14px;
}

.item-pricing {
    display: flex;
    gap: 15px;
}

.item-pricing span {
    color: #666;
    font-size: 14px;
}

.item-total {
    text-align: right;
}

.total-price {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
}

.order-summary {
    border-top: 2px solid #eee;
    padding-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #2c3e50;
}

.total-row {
    border-top: 2px solid #3498db;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 18px;
}

.shipping-address {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    line-height: 1.6;
    color: #2c3e50;
}

.customer-notes h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.customer-notes p {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    background: #3498db;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #3498db;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -30px;
    top: 17px;
    bottom: -25px;
    width: 2px;
    background: #e1e8ed;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.status-change {
    color: #2c3e50;
    font-weight: 500;
}

.timeline-date {
    color: #666;
    font-size: 14px;
}

.timeline-notes {
    color: #666;
    margin-bottom: 8px;
    font-style: italic;
}

.timeline-user {
    color: #999;
    font-size: 13px;
}

.action-buttons {
    text-align: center;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    margin: 0 10px;
    transition: all 0.3s;
    font-weight: bold;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .header-left,
    .header-right {
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
        padding: 20px 0;
    }
    
    .item-image {
        margin: 0 0 15px 0;
    }
    
    .item-details {
        margin: 0 0 15px 0;
    }
    
    .item-meta,
    .item-pricing {
        justify-content: center;
    }
    
    .timeline-header {
        flex-direction: column;
        gap: 5px;
    }
    
    .btn {
        display: block;
        margin: 10px 0;
        width: 100%;
    }
}

/* Print styles */
@media print {
    .page-header .btn,
    .action-buttons {
        display: none;
    }
    
    .order-detail-container {
        padding: 0;
    }
    
    .order-info-card,
    .order-items-card,
    .shipping-info-card,
    .status-history-card {
        box-shadow: none;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
}
</style>

<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        // You can implement cancel order functionality here
        alert('Cancel order functionality needs to be implemented.');
    }
}
</script>

<?php include '../../foot.php'; ?>
