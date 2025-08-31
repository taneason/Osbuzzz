<?php
// Test admin orders query
require 'app/base.php';

try {
    echo "Testing admin orders query...\n";
    
    // Simulate the conditions from admin_orders.php
    $status_filter = '';
    $payment_filter = '';
    $search = '';
    $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
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
    
    // Test the fixed query
    $sql = "
        SELECT o.*, u.username, u.email,
               COUNT(oi.order_item_id) as item_count,
               SUM(oi.quantity) as total_quantity
        FROM orders o
        JOIN user u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        $where_clause
        GROUP BY o.order_id
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ";

    $stm = $_db->prepare($sql);
    $stm->execute($params);
    $orders = $stm->fetchAll();
    
    echo "Query executed successfully!\n";
    echo "Found " . count($orders) . " orders.\n";
    echo "LIMIT: $limit, OFFSET: $offset\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
