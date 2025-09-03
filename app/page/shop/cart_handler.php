<?php
require '../../base.php';

// Ensure user is logged in for cart operations
if (!$_user) {
    header('Content-Type: text/plain');
    echo "ERROR:Please login to access cart";
    exit;
}

// Get action
$action = post('action') ?? get('action');

switch ($action) {
    case 'add_to_cart':
        $product_id = post('product_id', 0);
        $quantity = post('quantity', 1);
        $size = post('size');
        
        $result = cart_add_item($product_id, $quantity, $size);
        if ($result['success']) {
            temp('info', $result['message']);
            echo "SUCCESS:" . $result['message'];
        } else {
            echo "ERROR:" . $result['message'];
        }
        break;
        
    case 'remove_from_cart':
        $cart_id = post('cart_id', 0);
        
        $result = cart_remove_item($cart_id);
        if ($result['success']) {
            temp('info', $result['message']);
            echo "SUCCESS:" . $result['message'];
        } else {
            echo "ERROR:" . $result['message'];
        }
        break;
        
    case 'update_quantity':
        $cart_id = post('cart_id', 0);
        $quantity = post('quantity', 1);
        
        $result = cart_update_quantity($cart_id, $quantity);
        if ($result['success']) {
            temp('info', $result['message']);
            echo "SUCCESS:" . $result['message'];
        } else {
            echo "ERROR:" . $result['message'];
        }
        break;
        
    case 'get_count':
        $count = cart_get_count();
        echo "SUCCESS:$count";
        break;
        
    case 'remove_multiple':
        // Parse cart IDs from comma-separated string instead of JSON
        $cart_ids_string = post('cart_ids');
        $cart_ids = array_filter(array_map('intval', explode(',', $cart_ids_string)));
        
        if (empty($cart_ids)) {
            echo "ERROR:No items selected";
            exit;
        }
        
        $removed_count = 0;
        foreach ($cart_ids as $cart_id) {
            $result = cart_remove_item($cart_id);
            if ($result['success']) {
                $removed_count++;
            }
        }
        
        if ($removed_count > 0) {
            temp('success', "$removed_count item(s) removed from cart");
            echo "SUCCESS:$removed_count item(s) removed from cart";
        } else {
            echo "ERROR:Failed to remove items from cart";
        }
        break;
        
    case 'clear_cart':
        $result = cart_clear();
        if ($result['success']) {
            temp('info', $result['message']);
            echo "SUCCESS:" . $result['message'];
        } else {
            echo "ERROR:" . $result['message'];
        }
        break;
        
    case 'buy_now':
        $product_id = post('product_id', 0);
        $quantity = post('quantity', 1);
        $size = post('size');
        
        // Validate input
        if ($product_id <= 0) {
            echo "ERROR:Invalid product ID";
            exit;
        }
        
        if ($quantity <= 0) {
            echo "ERROR:Invalid quantity";
            exit;
        }
        
        // Validate product and stock
        try {
            $stm = $_db->prepare("SELECT * FROM product WHERE product_id = ?");
            $stm->execute([$product_id]);
            $product = $stm->fetch();
            
            if (!$product) {
                echo "ERROR:Product not found";
                exit;
            }
            
            // Check stock for size if specified
            if ($size) {
                $stm = $_db->prepare("SELECT stock FROM product_variants WHERE product_id = ? AND size = ?");
                $stm->execute([$product_id, $size]);
                $variant = $stm->fetch();
                $stock = $variant ? $variant->stock : 0;
            } else {
                $stock = $product->stock;
            }
            
            if ($quantity > $stock) {
                echo "ERROR:Only $stock item(s) available";
                exit;
            }
            
            // Store buy now item in session for checkout
            $_SESSION['buy_now_item'] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'size' => $size,
                'name' => $product->product_name,
                'price' => $product->price,
                'photo' => $product->photo,
                'brand' => $product->brand
            ];
            
            temp('info', 'Redirecting to checkout...');
            echo "SUCCESS:checkout.php";
            
        } catch (Exception $e) {
            echo "ERROR:Database error: " . $e->getMessage();
            exit;
        }
        break;
        
    default:
        echo "ERROR:Invalid action";
        break;
}
?>
