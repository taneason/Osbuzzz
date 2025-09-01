<?php
require '../../base.php';
auth('Admin');

// Handle order status updates
if (is_post()) {
    $action = req('action');
    $order_id = req('order_id');
    $new_status = req('new_status');
    $notes = req('notes');
    
    if ($action === 'update_status' && $order_id && $new_status) {
        try {
            // Get current order status
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
                temp('info', 'Order status updated successfully');
            }
        } catch (Exception $e) {
            $_db->rollback();
            temp('error', 'Failed to update order status: ' . $e->getMessage());
        }
        redirect('/page/admin/admin_orders.php');
    }
}

// Order table sorting
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'desc';
$allowed = ['order_id','order_number','username','email','order_status','payment_status','payment_method','grand_total','created_at'];
if (!in_array($sort, $allowed)) $sort = 'created_at';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// Search and filters
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'o.order_status = ?';
    $params[] = $status_filter;
}

if ($payment_filter) {
    $where_conditions[] = 'o.payment_status = ?';
    $params[] = $payment_filter;
}

if ($search) {
    $where_conditions[] = '(o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; // Orders per page
$offset = ($page - 1) * $limit;

// Get total count
$count_sql = "
    SELECT COUNT(*) 
    FROM orders o 
    JOIN user u ON o.user_id = u.id 
    $where_clause
";
$stm = $_db->prepare($count_sql);
$stm->execute($params);
$total_orders = $stm->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Build ORDER BY clause
$orderByField = $sort;
if ($sort === 'username' || $sort === 'email') {
    $orderByField = 'u.' . $sort;
} elseif (in_array($sort, ['order_id', 'order_number', 'order_status', 'payment_status', 'payment_method', 'grand_total', 'created_at'])) {
    $orderByField = 'o.' . $sort;
}

// Get orders
$sql = "
    SELECT o.*, u.username, u.email,
           COUNT(oi.order_item_id) as item_count,
           SUM(oi.quantity) as total_quantity
    FROM orders o
    JOIN user u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    $where_clause
    GROUP BY o.order_id
    ORDER BY $orderByField $order
    LIMIT $limit OFFSET $offset
";

$stm = $_db->prepare($sql);
$stm->execute($params);
$orders = $stm->fetchAll();


include '../../head.php';
?>

<main>
    <section id="orders" style="margin-top:40px;">
        <h2>Order Management</h2>
        
        <!-- Search and Filters -->
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <input type="search" name="search" placeholder="Search by order number, username, or email" value="<?= htmlspecialchars($search) ?>" class="admin-form-input" style="width:250px;">
            
            <select name="status" class="admin-form-input" style="width:130px;">
                <option value="">All Status</option>
                <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            
            <select name="payment" class="admin-form-input" style="width:140px;">
                <option value="">All Payment</option>
                <option value="pending" <?= $payment_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="paid" <?= $payment_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="cancelled" <?= $payment_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            
            <button type="submit" class="admin-btn" style="padding: 4px 16px;">Search</button>
            <?php if($search || $status_filter || $payment_filter): ?>
                <a href="admin_orders.php" class="admin-btn" style="padding: 4px 16px; text-align:center; line-height:normal;">Clear</a>
            <?php endif; ?>
        </form>

        <?= temp('info') ? '<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 20px 0;">' . temp('info') . '</div>' : '' ?>
        <?= temp('error') ? '<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 20px 0;">' . temp('error') . '</div>' : '' ?>

        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                <p style="color: #666; font-size: 16px;">No orders found matching your criteria.</p>
            </div>
        <?php else: ?>
            <table class="admin-product-table">
                <tr>
                    <th><?= sort_link('order_id','ID',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th><?= sort_link('order_number','Order Number',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th><?= sort_link('username','Customer',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th>Items</th>
                    <th><?= sort_link('grand_total','Total',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th><?= sort_link('payment_method','Payment',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th><?= sort_link('order_status','Status',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th><?= sort_link('created_at','Date',$sort,$order,$search,$page,$status_filter,$payment_filter) ?></th>
                    <th>Action</th>
                </tr>
                <?php foreach ($orders as $order_item): ?>
                <tr>
                    <td><?= $order_item->order_id ?></td>
                    <td><strong><?= $order_item->order_number ?></strong></td>
                    <td>
                        <?= htmlspecialchars($order_item->username) ?><br>
                        <small style="color: #666;"><?= htmlspecialchars($order_item->email) ?></small>
                    </td>
                    <td>
                        <?= $order_item->item_count ?> items<br>
                        <small style="color: #666;"><?= $order_item->total_quantity ?> pieces</small>
                    </td>
                    <td><strong>RM<?= number_format($order_item->grand_total, 2) ?></strong></td>
                    <td>
                        <?= ucfirst(str_replace('_', ' ', $order_item->payment_method)) ?><br>
                        <span style="padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; 
                              <?= $order_item->payment_status === 'paid' ? 'background: #d4edda; color: #155724;' : 
                                  ($order_item->payment_status === 'pending' ? 'background: #fff3cd; color: #856404;' : 'background: #f8d7da; color: #721c24;') ?>">
                            <?= ucfirst($order_item->payment_status) ?>
                        </span>
                    </td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; 
                              <?= $order_item->order_status === 'delivered' ? 'background: #d4edda; color: #155724;' : 
                                  ($order_item->order_status === 'shipped' ? 'background: #d1ecf1; color: #0c5460;' : 
                                  ($order_item->order_status === 'processing' ? 'background: #fff3cd; color: #856404;' : 'background: #f8d7da; color: #721c24;')) ?>">
                            <?= ucfirst($order_item->order_status) ?>
                        </span>
                    </td>
                    <td>
                        <?= date('M d, Y', strtotime($order_item->created_at)) ?><br>
                        <small style="color: #666;"><?= date('g:i A', strtotime($order_item->created_at)) ?></small>
                    </td>
                    <td>
                        <button data-get="admin_order_detail.php?id=<?= $order_item->order_id ?>">View</button>
                        <button onclick="updateOrderStatus(<?= $order_item->order_id ?>, '<?= $order_item->order_status ?>')">Update</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div style="margin-top:20px;text-align:center;">
                <?php
                $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
                $statusParam = $status_filter !== '' ? '&status=' . urlencode($status_filter) : '';
                $paymentParam = $payment_filter !== '' ? '&payment=' . urlencode($payment_filter) : '';
                $sortParam = "&sort=" . urlencode((string)$sort) . "&order=" . urlencode((string)$order);
                $allParams = $searchParam . $statusParam . $paymentParam . $sortParam;
                ?>
                
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= $allParams ?>" class="admin-btn">First</a>
                    <a href="?page=<?= $page - 1 ?><?= $allParams ?>" class="admin-btn">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="admin-btn" style="background:#007cba;color:white;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= $allParams ?>" class="admin-btn"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $allParams ?>" class="admin-btn">Next</a>
                    <a href="?page=<?= $total_pages ?><?= $allParams ?>" class="admin-btn">Last</a>
                <?php endif; ?>
                
                <span style="margin-left:20px;">Page <?= $page ?> of <?= $total_pages ?> (<?= $total_orders ?> orders)</span>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Update Status Modal -->
    <div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; margin: 10% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px;">
            <h3 style="margin: 0 0 15px 0;">Update Order Status</h3>
            <form method="post">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modal_order_id">
                
                <p>
                    <label>New Status:</label><br>
                    <select name="new_status" id="new_status" required class="admin-form-input" style="width: 100%;">
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </p>
                
                <p>
                    <label>Notes (Optional):</label><br>
                    <textarea name="notes" rows="3" placeholder="Add any notes about this status change..." class="admin-form-input" style="width: 100%; resize: vertical;"></textarea>
                </p>
                
                <div style="text-align: right; margin-top: 15px;">
                    <button type="button" onclick="closeModal()" class="admin-btn" style="margin-right: 10px;">Cancel</button>
                    <button type="submit" class="admin-btn">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function updateOrderStatus(orderId, currentStatus) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('new_status').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../../foot.php'; ?>
