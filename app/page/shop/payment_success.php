<?php
require '../../base.php';

// Redirect to login if not logged in
auth();

$order_id = get('order_id');

if (!$order_id) {
    temp('error', 'Invalid order ID');
    redirect('/page/shop/cart.php');
}

// Get order details
$stm = $_db->prepare('
    SELECT o.*, u.username as customer_username, 
           u.email as customer_email
    FROM orders o
    JOIN user u ON o.user_id = u.id
    WHERE o.order_id = ? AND o.user_id = ?
');
$stm->execute([$order_id, $_user->id]);
$order = $stm->fetch();

if (!$order) {
    temp('error', 'Order not found');
    redirect('/page/shop/cart.php');
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

$_title = 'Order Confirmation';
include '../../head.php';
?>

<main>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">✅</div>
            <h1>Order Confirmed!</h1>
            <p class="success-message">Thank you for your purchase. Your order has been successfully placed.</p>
        </div>
        
        <div class="order-details">
            <div class="order-summary-card">
                <h2>Order Summary</h2>
                
                <div class="order-info">
                    <div class="info-row">
                        <span class="label">Order Number:</span>
                        <span class="value"><?= $order->order_number ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Order Date:</span>
                        <span class="value"><?= date('F d, Y', strtotime($order->created_at)) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Payment Method:</span>
                        <span class="value"><?= ucfirst(str_replace('_', ' ', $order->payment_method)) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Order Status:</span>
                        <span class="value status-<?= $order->order_status ?>"><?= ucfirst($order->order_status) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Payment Status:</span>
                        <span class="value payment-<?= $order->payment_status ?>"><?= ucfirst($order->payment_status) ?></span>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Items Ordered</h3>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../../images/Products/<?= $item->photo ?? 'defaultProduct.png' ?>" alt="<?= $item->product_name ?>">
                            </div>
                            <div class="item-details">
                                <h4><?= $item->product_name ?></h4>
                                <p class="item-brand">Brand: <?= $item->product_brand ?></p>
                                <?php if ($item->size): ?>
                                    <p class="item-size">Size: <?= format_size($item->size) ?></p>
                                <?php endif; ?>
                                <p class="item-quantity">Quantity: <?= $item->quantity ?></p>
                                <p class="item-price">RM<?= number_format($item->price, 2) ?> each</p>
                            </div>
                            <div class="item-total">
                                RM<?= number_format($item->total_price, 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>RM<?= number_format($order->total_amount, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>RM<?= number_format($order->shipping_fee, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>RM<?= number_format($order->tax_amount, 2) ?></span>
                    </div>
                    <div class="total-row total-grand">
                        <span><strong>Total:</strong></span>
                        <span><strong>RM<?= number_format($order->grand_total, 2) ?></strong></span>
                    </div>
                </div>
            </div>
            
            <div class="shipping-info-card">
                <h2>Shipping Information</h2>
                <div class="shipping-address">
                    <?= nl2br(htmlspecialchars($order->shipping_address)) ?>
                </div>
                
                <?php if ($order->customer_notes): ?>
                    <div class="customer-notes">
                        <h3>Order Notes</h3>
                        <p><?= nl2br(htmlspecialchars($order->customer_notes)) ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="next-steps">
                    <h3>What's Next?</h3>
                    <ul>
                        <li>✅ You will receive an order confirmation email shortly at <strong><?= htmlspecialchars($order->customer_email) ?></strong></li>
                        <li>📦 Your order is being processed and will be shipped within 1-2 business days</li>
                        <li>🚚 You'll receive a tracking number once your order ships</li>
                        <li>📅 Estimated delivery: 3-5 business days</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="/page/user/order_detail.php?id=<?= $order->order_id ?>" class="btn btn-primary">View Order Details</a>
            <a href="/page/shop/sales.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="/page/user/orders.php" class="btn btn-outline">View All Orders</a>
        </div>
        
        <div class="support-info">
            <h3>Need Help?</h3>
            <p>If you have any questions about your order, please contact our customer support:</p>
            <p>Email: support@osbuzz.com | Phone: 1-800-OSBUZZ-1</p>
        </div>
    </div>
</main>

<style>
.success-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.success-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(46, 204, 113, 0.3);
}

.success-icon {
    font-size: 60px;
    margin-bottom: 20px;
}

.success-header h1 {
    margin: 0 0 10px 0;
    font-size: 36px;
    font-weight: bold;
}

.success-message {
    margin: 0;
    font-size: 18px;
    opacity: 0.9;
}

.order-details {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.order-summary-card,
.shipping-info-card {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-summary-card h2,
.shipping-info-card h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.order-info {
    margin-bottom: 30px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: bold;
    color: #2c3e50;
}

.value {
    color: #666;
}

.status-processing {
    color: #f39c12;
    font-weight: bold;
}

.status-shipped {
    color: #3498db;
    font-weight: bold;
}

.status-delivered {
    color: #27ae60;
    font-weight: bold;
}

.payment-paid {
    color: #27ae60;
    font-weight: bold;
}

.payment-pending {
    color: #f39c12;
    font-weight: bold;
}

.order-items h3 {
    margin-bottom: 20px;
    color: #2c3e50;
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
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
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

.item-details p {
    margin: 4px 0;
    color: #666;
    font-size: 14px;
}

.item-total {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
}

.order-totals {
    border-top: 2px solid #eee;
    padding-top: 20px;
    margin-top: 30px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #2c3e50;
}

.total-grand {
    border-top: 2px solid #3498db;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 20px;
}

.shipping-address {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 4px;
    margin-bottom: 25px;
    line-height: 1.6;
    color: #2c3e50;
}

.customer-notes {
    margin-bottom: 25px;
}

.customer-notes h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.customer-notes p {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    color: #666;
    line-height: 1.6;
}

.next-steps h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.next-steps ul {
    padding-left: 20px;
    color: #666;
    line-height: 1.8;
}

.next-steps li {
    margin-bottom: 8px;
}

.action-buttons {
    text-align: center;
    margin-bottom: 40px;
}

.btn {
    padding: 15px 30px;
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

.support-info {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    color: #666;
}

.support-info h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.support-info p {
    margin: 10px 0;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .order-details {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .order-summary-card,
    .shipping-info-card {
        padding: 20px;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
        padding: 15px 0;
    }
    
    .item-image {
        margin: 0 0 15px 0;
    }
    
    .item-details {
        margin: 0 0 15px 0;
    }
    
    .btn {
        display: block;
        margin: 10px 0;
        width: 100%;
    }
    
    .success-header h1 {
        font-size: 28px;
    }
    
    .success-icon {
        font-size: 48px;
    }
}
</style>

<?php
include '../../foot.php';
?>
