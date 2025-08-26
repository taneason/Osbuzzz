<?php
require '../../base.php';

// Ensure user is logged in for cart operations
if (!$_user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to access cart']);
    exit;
}

// Get action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $size = $_POST['size'] ?? '';
        
        $result = cart_add_item($product_id, $quantity, $size);
        if ($result['success']) {
            temp('info', $result['message']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    case 'remove_from_cart':
        $cart_id = $_POST['cart_id'] ?? 0;
        
        $result = cart_remove_item($cart_id);
        if ($result['success']) {
            temp('info', $result['message']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        break;
        
    case 'update_quantity':
        $cart_id = $_POST['cart_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
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
