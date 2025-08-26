<?php
require '../../base.php';

// Set content type to JSON
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$product_id = (int)($_GET['product_id'] ?? 0);

switch ($action) {
    case 'get_size_stock':
        $size = $_GET['size'] ?? '';
        
        if (!$product_id || !$size) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }
        
        // Get stock for specific product and size
        $stm = $_db->prepare('SELECT SUM(stock) as stock FROM product_variants WHERE product_id = ? AND size = ?');
        $stm->execute([$product_id, $size]);
        $result = $stm->fetch();
        
        $stock = $result ? (int)$result->stock : 0;
        
        // Get current cart quantity for this product and size
        $cart_quantity = 0;
        if ($_user) {
            $stm = $_db->prepare('SELECT SUM(quantity) as quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?');
            $stm->execute([$_user->id, $product_id, $size]);
            $cart_result = $stm->fetch();
            $cart_quantity = $cart_result ? (int)$cart_result->quantity : 0;
        }
        
        $available_stock = max(0, $stock - $cart_quantity);
        
        echo json_encode([
            'success' => true,
            'stock' => $stock,
            'cart_quantity' => $cart_quantity,
            'available_stock' => $available_stock
        ]);
        break;
        
    case 'get_total_stock':
        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Missing product ID']);
            exit;
        }
        
        // Get total stock for product
        $stm = $_db->prepare('SELECT SUM(stock) as total_stock FROM product_variants WHERE product_id = ?');
        $stm->execute([$product_id]);
        $result = $stm->fetch();
        
        $total_stock = $result ? (int)$result->total_stock : 0;
        
        // Get current cart quantity for this product (all sizes)
        $cart_quantity = 0;
        if ($_user) {
            $stm = $_db->prepare('SELECT SUM(quantity) as quantity FROM cart WHERE user_id = ? AND product_id = ?');
            $stm->execute([$_user->id, $product_id]);
            $cart_result = $stm->fetch();
            $cart_quantity = $cart_result ? (int)$cart_result->quantity : 0;
        }
        
        $available_stock = max(0, $total_stock - $cart_quantity);
        
        echo json_encode([
            'success' => true,
            'total_stock' => $total_stock,
            'cart_quantity' => $cart_quantity,
            'available_stock' => $available_stock
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
