<?php
require '../../base.php';

// Set JSON content type at the very beginning
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Log the request
// error_log('Payment handler called with action: ' . (post('action') ?? 'none'));

// Ensure user is logged in
if (!$_user) {
    echo json_encode(['success' => false, 'message' => 'Please login to complete order']);
    exit;
}

// Check if checkout data exists in session
if (!isset($_SESSION['checkout_data'])) {
    echo json_encode(['success' => false, 'message' => 'Checkout session expired']);
    exit;
}

$action = post('action');

if ($action == 'process_payment') {
    try {
        $payment_id = post('payment_id');
        $payer_id = post('payer_id');
        $payment_details = post('payment_details');
        
        if (empty($payment_id) || empty($payment_details)) {
            throw new Exception('Invalid payment data');
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
            throw new Exception('Cart is empty');
        }
        
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
            'processing',
            'paid',
            'paypal',
            $payment_id,
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
            
            // Ensure brand is not null - fallback to 'Unknown' if empty
            $brand = !empty($item->brand) ? $item->brand : 'Unknown';
            
            $stm_item->execute([
                $order_id,
                $item->product_id,
                $item->name,
                $brand,
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
            'processing',
            $_user->id,
            'Order created via PayPal payment'
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
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'order_number' => $order_number
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($_db->inTransaction()) {
            $_db->rollback();
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process order: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
