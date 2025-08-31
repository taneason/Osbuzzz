<?php
require 'app/base.php';

// This is a test script to verify order confirmation email functionality
// Usage: http://localhost/Osbuzzz/test_order_email.php?order_id=YOUR_ORDER_ID

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die('Please provide an order_id parameter. Example: test_order_email.php?order_id=1');
}

echo "<h1>Testing Order Confirmation Email</h1>";
echo "<p>Testing email for Order ID: $order_id</p>";

$result = send_order_confirmation_email($order_id);

if ($result) {
    echo "<p style='color: green;'>✅ Email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send email. Check error logs for details.</p>";
}

echo "<p><a href='app/page/user/orders.php'>Back to Orders</a></p>";
?>
