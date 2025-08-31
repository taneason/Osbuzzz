<?php
require '../../base.php';

// Redirect to login if not logged in
auth();

// Check if checkout data exists in session
if (!isset($_SESSION['checkout_data'])) {
    temp('error', 'Checkout session expired. Please try again.');
    redirect('/page/shop/cart.php');
}

$checkout_data = $_SESSION['checkout_data'];
$cart_items = cart_get_items();

if (empty($cart_items)) {
    temp('error', 'Your cart is empty');
    redirect('/page/shop/cart.php');
}

try {
    // Start transaction
    $_db->beginTransaction();
    
    // Generate unique order number
    $order_number = 'OSB' . date('Y') . sprintf('%06d', rand(1, 999999));
    
    // Check if order number already exists (very unlikely but safety first)
    $stm = $_db->prepare('SELECT COUNT(*) FROM orders WHERE order_number = ?');
    $stm->execute([$order_number]);
    while ($stm->fetchColumn() > 0) {
        $order_number = 'OSB' . date('Y') . sprintf('%06d', rand(1, 999999));
        $stm->execute([$order_number]);
    }
    
    // Prepare shipping address
    $shipping_address = $checkout_data['first_name'] . ' ' . $checkout_data['last_name'] . "\n";
    if (!empty($checkout_data['company'])) {
        $shipping_address .= $checkout_data['company'] . "\n";
    }
    $shipping_address .= $checkout_data['address_line_1'] . "\n";
    if (!empty($checkout_data['address_line_2'])) {
        $shipping_address .= $checkout_data['address_line_2'] . "\n";
    }
    $shipping_address .= $checkout_data['city'] . ', ' . $checkout_data['state'] . ' ' . $checkout_data['postal_code'] . "\n";
    $shipping_address .= 'Phone: ' . $checkout_data['phone'];
    
    // Create order record
    $stm = $_db->prepare('
        INSERT INTO orders (
            user_id, order_number, total_amount, shipping_fee, tax_amount, grand_total,
            order_status, payment_status, payment_method, payment_id,
            shipping_address, customer_notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $stm->execute([
        $_user->id,
        $order_number,
        $checkout_data['subtotal'],
        $checkout_data['shipping_fee'],
        $checkout_data['tax_amount'],
        $checkout_data['grand_total'],
        'pending',
        'pending',
        'cash_on_delivery',
        null,
        $shipping_address,
        $checkout_data['customer_notes'] ?? null
    ]);
    
    $order_id = $_db->lastInsertId();
    
    // Create order items
    $stm_item = $_db->prepare('
        INSERT INTO order_items (
            order_id, product_id, product_name, product_brand, size, price, quantity, total_price
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    foreach ($cart_items as $item) {
        $total_price = $item->price * $item->quantity;
        
        $stm_item->execute([
            $order_id,
            $item->product_id,
            $item->name,
            $item->brand,
            $item->size,
            $item->price,
            $item->quantity,
            $total_price
        ]);
        
        // Update product stock (reduce inventory)
        if (!empty($item->size)) {
            $stm_stock = $_db->prepare('
                UPDATE product_variants 
                SET stock = stock - ? 
                WHERE product_id = ? AND size = ? AND stock >= ?
            ');
            $stm_stock->execute([$item->quantity, $item->product_id, $item->size, $item->quantity]);
            
            if ($stm_stock->rowCount() == 0) {
                throw new Exception("Insufficient stock for {$item->name} - Size {$item->size}");
            }
        }
    }
    
    // Create order status history
    $stm_history = $_db->prepare('
        INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stm_history->execute([
        $order_id,
        null,
        'pending',
        $_user->id,
        'Order created with Cash on Delivery payment'
    ]);
    
    // Clear cart
    cart_clear();
    
    // Clear checkout session data
    unset($_SESSION['checkout_data']);
    
    // Commit transaction
    $_db->commit();
    
    // Send order confirmation email
    if (send_order_confirmation_email($order_id)) {
        error_log("Order confirmation email sent successfully for order ID: $order_id");
    } else {
        error_log("Failed to send order confirmation email for order ID: $order_id");
    }
    
    // Set success message
    temp('success', 'Order placed successfully! You will pay when your order is delivered.');
    
    // Redirect to success page
    redirect("payment_success.php?order_id=$order_id");
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($_db->inTransaction()) {
        $_db->rollback();
    }
    
    temp('error', 'Failed to process order: ' . $e->getMessage());
    redirect('/page/shop/checkout.php');
}
?>
