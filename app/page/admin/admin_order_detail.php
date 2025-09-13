<?php
require '../../base.php';

// Check admin access
auth('Admin');

$order_id = req('id');
if (!$order_id) {
    temp('error', 'Order ID is required.');
    redirect('/page/admin/admin_orders.php');
}

// Handle status update
if (is_post()) {
    $action = req('action');
    
    if ($action === 'update_status') {
        $new_status = req('new_status');
        $notes = req('notes');
        
        if ($new_status) {
            try {
                // Get current order
                $stm = $_db->prepare('SELECT order_status FROM orders WHERE order_id = ?');
                $stm->execute([$order_id]);
                $current_order = $stm->fetch();
                
                if ($current_order) {
                    $_db->beginTransaction();
                    
                    // Update order status
                    $stm = $_db->prepare('UPDATE orders SET order_status = ? WHERE order_id = ?');
                    $stm->execute([$new_status, $order_id]);
                    
                    // Add to status history
                    $stm = $_db->prepare('
                        INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
                        VALUES (?, ?, ?, ?, ?)
                    ');
                    $stm->execute([
                        $order_id,
                        $current_order->order_status,
                        $new_status,
                        $_user->id,
                        $notes ?: "Status updated by admin"
                    ]);
                    
                    $_db->commit();
                    temp('success', 'Order status updated successfully');
                }
            } catch (Exception $e) {
                $_db->rollback();
                temp('error', 'Failed to update order status: ' . $e->getMessage());
            }
        }
    }
    
    // Handle COD payment confirmation
    if ($action === 'confirm_cod_payment') {
        $notes = req('notes');
        
        try {
            $_db->beginTransaction();
            
            // Update payment status to paid
            $stm = $_db->prepare('UPDATE orders SET payment_status = ? WHERE order_id = ?');
            $stm->execute(['paid', $order_id]);
            
            // Add payment confirmation to history
            $stm = $_db->prepare('
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stm->execute([
                $order_id,
                'COD Payment Pending',
                'COD Payment Confirmed',
                $_user->id,
                $notes ?: "COD payment confirmed by admin - cash received from customer"
            ]);
            
            $_db->commit();
            temp('success', 'COD payment confirmed successfully');
        } catch (Exception $e) {
            $_db->rollback();
            temp('error', 'Failed to confirm COD payment: ' . $e->getMessage());
        }
    }
    
    redirect("/page/admin/admin_order_detail.php?id=$order_id");
}

// Get order details with items
$stm = $_db->prepare('
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN user u ON o.user_id = u.id
    WHERE o.order_id = ?
');
$stm->execute([$order_id]);
$order = $stm->fetch();

if (!$order) {
    temp('error', 'Order not found.');
    redirect('/page/admin/admin_orders.php');
}

// Get order items
$stm = $_db->prepare('
    SELECT oi.*, p.product_name, p.photo, p.brand
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
    ORDER BY oi.order_item_id
');
$stm->execute([$order_id]);
$order_items = $stm->fetchAll();

// Get loyalty transactions for this order
$stm = $_db->prepare('
    SELECT * FROM loyalty_transactions 
    WHERE order_id = ? 
    ORDER BY created_at ASC
');
$stm->execute([$order_id]);
$loyalty_transactions = $stm->fetchAll();

// Get status history
$stm = $_db->prepare('
    SELECT h.*, u.username as changed_by_name
    FROM order_status_history h
    LEFT JOIN user u ON h.changed_by = u.id
    WHERE h.order_id = ?
    ORDER BY h.created_at DESC
');
$stm->execute([$order_id]);
$status_history = $stm->fetchAll();

$_title = "Order #$order->order_number - Admin";
include '../../head.php';
?>

<main>
    <div class="admin-container">
        <div class="admin-header">
            <h1>📦 Order #<?= $order->order_number ?></h1>
            <div class="header-actions">
                <a href="/page/admin/admin_orders.php" class="btn btn-secondary">← Back to Orders</a>
                <button class="btn btn-primary" onclick="updateOrderStatus('<?= $order->order_status ?>')">Update Status</button>
                
                <?php if ($order->payment_method === 'cash_on_delivery' && $order->payment_status === 'pending' && in_array($order->order_status, ['shipped', 'delivered'])): ?>
                    <button class="btn btn-success" onclick="confirmCODPayment()">✓ Confirm COD Payment</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (temp('success')): ?>
            <div class="alert alert-success">
                ✓ <?= temp('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (temp('error')): ?>
            <div class="alert alert-error">
                ✗ <?= temp('error') ?>
            </div>
        <?php endif; ?>

        <div class="order-detail-grid">
            <!-- Order Summary -->
            <div class="detail-card">
                <h3>📋 Order Summary</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Order Date:</label>
                        <value><?= date('M d, Y g:i A', strtotime($order->created_at)) ?></value>
                    </div>
                    <div class="detail-item">
                        <label>Order Status:</label>
                        <value>
                            <span class="status-badge status-<?= $order->order_status ?>">
                                <?= ucfirst($order->order_status) ?>
                            </span>
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Payment Method:</label>
                        <value><?= ucfirst(str_replace('_', ' ', $order->payment_method)) ?></value>
                    </div>
                    <div class="detail-item">
                        <label>Payment Status:</label>
                        <value>
                            <span class="payment-badge payment-<?= $order->payment_status ?>">
                                <?= ucfirst($order->payment_status) ?>
                            </span>
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Subtotal:</label>
                        <value>RM<?= number_format($order->total_amount, 2) ?></value>
                    </div>
                    <div class="detail-item">
                        <label>Shipping:</label>
                        <value>RM<?= number_format($order->shipping_fee, 2) ?></value>
                    </div>
                    <div class="detail-item">
                        <label>Tax:</label>
                        <value>RM<?= number_format($order->tax_amount, 2) ?></value>
                    </div>
                    <?php if (isset($order->loyalty_points_used) && $order->loyalty_points_used > 0): ?>
                    <div class="detail-item loyalty-discount" style="background: #d4edda; border-left: 4px solid #28a745; padding: 10px; margin: 5px 0;">
                        <label style="color: #155724;">🎉 Loyalty Points Used:</label>
                        <value style="color: #155724; font-weight: bold;">
                            <?= number_format($order->loyalty_points_used) ?> points 
                            (-RM<?= number_format($order->loyalty_discount, 2) ?>)
                        </value>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item total">
                        <label>Grand Total:</label>
                        <value>RM<?= number_format($order->grand_total, 2) ?></value>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="detail-card">
                <h3>👤 Customer Information</h3>
                <div class="customer-details">
                    <div class="customer-item">
                        <label>Name:</label>
                        <value><?= encode($order->username) ?></value>
                    </div>
                    <div class="customer-item">
                        <label>Email:</label>
                        <value><?= encode($order->email) ?></value>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="detail-card">
                <h3>🚚 Shipping Address</h3>
                <?php if ($order->shipping_address): ?>
                    <div class="address-info">
                        <?= nl2br(encode($order->shipping_address)) ?>
                    </div>
                <?php else: ?>
                    <p class="no-address">No shipping address on file</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="detail-card full-width">
            <h3>📦 Order Items</h3>
            <div class="items-table-container">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="/images/Products/<?= $item->photo ?>" alt="<?= encode($item->product_name) ?>" class="product-thumbnail">
                                        <div class="product-details">
                                            <div class="product-name"><?= encode($item->product_name) ?></div>
                                            <div class="product-brand"><?= encode($item->brand) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= encode($item->size) ?></td>
                                <td><?= $item->quantity ?></td>
                                <td>RM<?= number_format($item->price, 2) ?></td>
                                <td><strong>RM<?= number_format($item->total_price, 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loyalty Points Transactions -->
        <?php if (!empty($loyalty_transactions)): ?>
        <div class="detail-card full-width">
            <h3>🎁 Loyalty Points Transactions</h3>
            <div class="loyalty-transactions">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Points</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loyalty_transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <span class="loyalty-badge loyalty-<?= $transaction->transaction_type ?>">
                                        <?php
                                        $type_labels = [
                                            'earned' => '💰 Earned',
                                            'redeemed' => '🎉 Redeemed', 
                                            'refund' => '↩️ Refunded',
                                            'expired' => '❌ Expired',
                                            'bonus' => '🎁 Bonus'
                                        ];
                                        echo $type_labels[$transaction->transaction_type] ?? ucfirst($transaction->transaction_type);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="points-amount <?= $transaction->points > 0 ? 'positive' : 'negative' ?>">
                                        <?= $transaction->points > 0 ? '+' : '' ?><?= number_format($transaction->points) ?>
                                    </span>
                                </td>
                                <td><?= encode($transaction->description) ?></td>
                                <td><?= date('M d, Y g:i A', strtotime($transaction->created_at)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status History -->
        <div class="detail-card full-width">
            <h3>📊 Status History</h3>
            <?php if (empty($status_history)): ?>
                <p>No status changes recorded.</p>
            <?php else: ?>
                <div class="status-timeline">
                    <?php foreach ($status_history as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker status-<?= $history->new_status ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="status-change">
                                        Changed from <strong><?= ucfirst($history->old_status) ?></strong> 
                                        to <strong><?= ucfirst($history->new_status) ?></strong>
                                    </span>
                                    <span class="timeline-date"><?= date('M d, Y g:i A', strtotime($history->created_at)) ?></span>
                                </div>
                                <?php if ($history->notes): ?>
                                    <div class="timeline-notes"><?= encode($history->notes) ?></div>
                                <?php endif; ?>
                                <div class="timeline-by">
                                    By: <?= $history->changed_by_name ? encode($history->changed_by_name) : 'System' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Order Status</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="post" class="modal-form">
                <input type="hidden" name="action" value="update_status">
                
                <div class="form-group">
                    <label for="new_status">New Status:</label>
                    <select name="new_status" id="new_status" required>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Add notes about this status change..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- COD Payment Confirmation Modal -->
    <div id="codModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>💰 Confirm COD Payment</h3>
                <span class="close" onclick="closeCODModal()">&times;</span>
            </div>
            <form method="post" class="modal-form">
                <input type="hidden" name="action" value="confirm_cod_payment">
                
                <div class="cod-info">
                    <p><strong>Order Total: RM<?= number_format($order->grand_total, 2) ?></strong></p>
                    <p>Confirm that the customer has paid <strong>RM<?= number_format($order->grand_total, 2) ?></strong> in cash to the delivery person.</p>
                </div>
                
                <div class="form-group">
                    <label for="cod_notes">Confirmation Notes:</label>
                    <textarea name="notes" id="cod_notes" rows="3" placeholder="e.g., Cash received by delivery person [Name] on [Date/Time]..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn btn-success">✓ Confirm Payment Received</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCODModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e8ed;
}

.admin-header h1 {
    color: #2c3e50;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.order-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.detail-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.detail-card.full-width {
    grid-column: 1 / -1;
}

.detail-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #e1e8ed;
    padding-bottom: 10px;
}

.detail-grid {
    display: grid;
    gap: 10px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-item.total {
    font-weight: bold;
    font-size: 1.1em;
    border-top: 2px solid #e1e8ed;
    margin-top: 10px;
    padding-top: 15px;
}

.detail-item label {
    color: #666;
    font-weight: 500;
}

.detail-item value {
    color: #2c3e50;
    font-weight: 600;
}

.customer-details {
    display: grid;
    gap: 10px;
}

.customer-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.customer-item label {
    color: #666;
    font-weight: 500;
}

.customer-item value {
    color: #2c3e50;
    font-weight: 600;
}

.address-info {
    line-height: 1.6;
    color: #2c3e50;
}

.no-address {
    color: #666;
    font-style: italic;
}

.items-table-container {
    overflow-x: auto;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.items-table th,
.items-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.items-table th {
    background: #f8f9fa;
    font-weight: bold;
    color: #2c3e50;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.product-details {
    flex: 1;
}

.product-name {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 4px;
}

.product-brand {
    color: #666;
    font-size: 12px;
}

.status-timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #e1e8ed;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #3498db;
}

.timeline-marker.status-processing {
    background: #f39c12;
}

.timeline-marker.status-shipped {
    background: #3498db;
}

.timeline-marker.status-delivered {
    background: #27ae60;
}

.timeline-marker.status-cancelled {
    background: #e74c3c;
}

.timeline-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.status-change {
    font-weight: bold;
    color: #2c3e50;
}

.timeline-date {
    color: #666;
    font-size: 12px;
}

.timeline-notes {
    color: #2c3e50;
    margin-bottom: 8px;
    font-style: italic;
}

.timeline-by {
    color: #666;
    font-size: 12px;
}

.status-badge,
.payment-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-processing {
    background: #fff3cd;
    color: #856404;
}

.status-shipped {
    background: #d1ecf1;
    color: #0c5460;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
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

.payment-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    font-weight: bold;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-success {
    background: #27ae60;
    color: white;
}

.btn-success:hover {
    background: #229954;
}

.cod-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    border-left: 4px solid #27ae60;
}

.cod-info p {
    margin: 5px 0;
    color: #2c3e50;
}

.cod-info strong {
    color: #27ae60;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    background: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close {
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.modal-form {
    padding: 40px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .order-detail-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}

/* Loyalty Points Styles */
.loyalty-transactions {
    margin-top: 10px;
}

.loyalty-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
}

.loyalty-earned {
    background: #d4edda;
    color: #155724;
}

.loyalty-redeemed {
    background: #fff3cd;
    color: #856404;
}

.loyalty-refund {
    background: #d1ecf1;
    color: #0c5460;
}

.loyalty-expired {
    background: #f8d7da;
    color: #721c24;
}

.loyalty-bonus {
    background: #e7e3ff;
    color: #6f42c1;
}

.points-amount {
    font-weight: bold;
    font-size: 14px;
}

.points-amount.positive {
    color: #28a745;
}

.points-amount.negative {
    color: #dc3545;
}

.loyalty-discount {
    border-radius: 6px;
    margin: 8px 0;
}
</style>

<script>
function updateOrderStatus(currentStatus) {
    document.getElementById('new_status').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function confirmCODPayment() {
    document.getElementById('codModal').style.display = 'block';
}

function closeCODModal() {
    document.getElementById('codModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const statusModal = document.getElementById('statusModal');
    const codModal = document.getElementById('codModal');
    
    if (event.target === statusModal) {
        statusModal.style.display = 'none';
    }
    if (event.target === codModal) {
        codModal.style.display = 'none';
    }
}
</script>

<?php include '../../foot.php'; ?>
