<?php
require '../../base.php';

// Ensure user is logged in for cart operations
if (!$_user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to access cart']);
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
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    case 'remove_from_cart':
        $cart_id = post('cart_id', 0);
        
        $result = cart_remove_item($cart_id);
        if ($result['success']) {
            temp('info', $result['message']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    case 'update_quantity':
        $cart_id = post('cart_id', 0);
        $quantity = post('quantity', 1);
        
        $result = cart_update_quantity($cart_id, $quantity);
        if ($result['success']) {
            temp('info', $result['message']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    case 'get_count':
        $count = cart_get_count();
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        break;
        
    case 'remove_multiple':
        $cart_ids_json = post('cart_ids');
        $cart_ids = json_decode($cart_ids_json, true);
        
        if (!is_array($cart_ids) || empty($cart_ids)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No items selected']);
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
            $response = ['success' => true, 'message' => "$removed_count item(s) removed from cart"];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove items from cart'];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
        
    case 'clear_cart':
        $result = cart_clear();
        if ($result['success']) {
            temp('info', $result['message']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
