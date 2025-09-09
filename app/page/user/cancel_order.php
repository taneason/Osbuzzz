<?php
require '../../base.php';

// Set JSON content type
header('Content-Type: application/json');

// Ensure user is logged in
if (!$_user) {
    echo json_encode(['success' => false, 'message' => 'Please login to cancel order']);
    exit;
}

if (is_post()) {
    $order_id = req('order_id');
    
    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit;
    }
    
    try {
        // Get order details - ensure user can only cancel their own orders
        $stm = $_db->prepare('
            SELECT * FROM orders 
            WHERE order_id = ? AND user_id = ? AND order_status IN ("pending", "processing")
        ');
        $stm->execute([$order_id, $_user->id]);
        $order = $stm->fetch();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled. Orders can only be cancelled before shipping.']);
            exit;
        }
        
        // Check if order has been shipped
        if (in_array($order->order_status, ['shipped', 'delivered', 'cancelled'])) {
            echo json_encode(['success' => false, 'message' => 'This order cannot be cancelled as it has already been ' . $order->order_status . '.']);
            exit;
        }
        
        $_db->beginTransaction();
        
        // Store original status for history
        $original_status = $order->order_status;
        $original_payment_status = $order->payment_status;
        
        // Update order status to cancelled
        $new_payment_status = 'cancelled';
        if ($order->payment_status === 'paid' && $order->payment_method !== 'cash_on_delivery') {
            // For paid orders, mark as refund pending
            $new_payment_status = 'refunded';
        }
        
        $stm = $_db->prepare('
            UPDATE orders 
            SET order_status = "cancelled", payment_status = ?
            WHERE order_id = ?
        ');
        $stm->execute([$new_payment_status, $order_id]);
        
        // Restore product stock
        $stm = $_db->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $stm->execute([$order_id]);
        $order_items = $stm->fetchAll();
        
        foreach ($order_items as $item) {
            if (!empty($item->size)) {
                $stm_stock = $_db->prepare('
                    UPDATE product_variants 
                    SET stock = stock + ? 
                    WHERE product_id = ? AND size = ?
                ');
                $stm_stock->execute([$item->quantity, $item->product_id, $item->size]);
            }
        }
        
        // Handle loyalty points refund/reversal
        // 1. If user used loyalty points for discount, refund those points
        if (isset($order->loyalty_points_used) && $order->loyalty_points_used > 0) {
            // Refund the loyalty points that were used
            $stm_refund = $_db->prepare('UPDATE user SET loyalty_points = loyalty_points + ? WHERE id = ?');
            $stm_refund->execute([$order->loyalty_points_used, $_user->id]);
            
            // Record loyalty transaction for points refund
            $stm_transaction = $_db->prepare('
                INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stm_transaction->execute([
                $_user->id, 
                $order_id,
                $order->loyalty_points_used, 
                'refund', 
                "Points refunded from cancelled order #{$order->order_number}"
            ]);
        }
        
        // 2. Remove earned points from this order to prevent point farming
        // Find all earned points transactions for this order
        $stm_earned = $_db->prepare('
            SELECT * FROM loyalty_transactions 
            WHERE user_id = ? AND order_id = ? AND transaction_type = "earned"
        ');
        $stm_earned->execute([$_user->id, $order_id]);
        $earned_transactions = $stm_earned->fetchAll();
        
        foreach ($earned_transactions as $transaction) {
            // Deduct the earned points back
            $stm_deduct = $_db->prepare('UPDATE user SET loyalty_points = loyalty_points - ? WHERE id = ?');
            $stm_deduct->execute([$transaction->points, $_user->id]);
            
            // Record loyalty transaction for points removal
            $stm_reverse = $_db->prepare('
                INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stm_reverse->execute([
                $_user->id, 
                $order_id,
                -$transaction->points, 
                'expired', 
                "Points removed from cancelled order #{$order->order_number}"
            ]);
        }
        
        // Add status history
        $stm = $_db->prepare('
            INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stm->execute([
            $order_id,
            $original_status,
            'cancelled',
            $_user->id,
            'Order cancelled by customer'
        ]);
        
        $_db->commit();
        
        $success_message = 'Order cancelled successfully. Stock has been restored.';
        if ($order->payment_status === 'paid' && $order->payment_method !== 'cash_on_delivery') {
            $success_message .= ' A refund will be processed within 3-5 business days.';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $success_message
        ]);
        
    } catch (Exception $e) {
        if ($_db->inTransaction()) {
            $_db->rollback();
        }
        
        error_log('Order cancellation error: ' . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Failed to cancel order: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
