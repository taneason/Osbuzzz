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

// Get items for checkout - check if it's a buy now order or regular cart
if (isset($_SESSION['buy_now_item'])) {
    // Handle buy now item
    $buy_now_item = $_SESSION['buy_now_item'];
    $cart_items = [(object) [
        'cart_id' => null,
        'product_id' => $buy_now_item['product_id'],
        'quantity' => $buy_now_item['quantity'],
        'size' => $buy_now_item['size'],
        'name' => $buy_now_item['name'],
        'price' => $buy_now_item['price'],
        'photo' => $buy_now_item['photo'],
        'brand' => $buy_now_item['brand']
    ]];
    $is_buy_now = true;
} else {
    // Use cart items from checkout_data (already filtered for selective checkout)
    $cart_items = $checkout_data['cart_items'] ?? cart_get_items();
    $is_buy_now = false;
}

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
    
    // Remove items from cart after successful order
    if (!$is_buy_now) {
        // Get checkout configuration
        $checkout_data = $_SESSION['checkout_data'] ?? [];
        $is_selective_checkout = $checkout_data['is_selective_checkout'] ?? false;
        
        if ($is_selective_checkout) {
            // For selective checkout, remove only the selected items
            foreach ($cart_items as $item) {
                if ($item->cart_id) {  // Only remove items that have cart_id (from actual cart)
                    cart_remove_item($item->cart_id);
                }
            }
        } else {
            // For full cart checkout, remove all cart items
            foreach ($cart_items as $item) {
                if ($item->cart_id) {
                    cart_remove_item($item->cart_id);
                }
            }
        }
    }
    
    // Clear checkout session data
    unset($_SESSION['checkout_data']);
    
    // Clear selected cart items from session
    if (isset($_SESSION['selected_cart_items'])) {
        unset($_SESSION['selected_cart_items']);
    }
    
    // Clear buy now item from session if it exists
    if (isset($_SESSION['buy_now_item'])) {
        unset($_SESSION['buy_now_item']);
    }
    
    // Commit transaction
    $_db->commit();
    
    // Send order confirmation email
    send_order_confirmation_email($order_id);
    
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
