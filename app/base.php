<?php


// ============================================================================
// PHP Setups
// ============================================================================

// TODO
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function temp($key, $value = null) {
    if ($value !== null){
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
    
}

// Obtain uploaded file --> cast to object
function get_file($key) {
    $f = $_FILES[$key] ?? null;
    
    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

//Sorting Helpers
function usort_link($col, $label, $usort, $uorder, $search = '', $page = 1) {
    $nextOrder = ($usort === $col && $uorder === 'asc') ? 'desc' : 'asc';
    $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
    $pageParam = $page > 1 ? '&page=' . $page : '';
    return "<a href='?usort=$col&uorder=$nextOrder$searchParam$pageParam#users'>$label" . ($usort === $col ? ($uorder === 'asc' ? ' ▲' : ' ▼') : '') . "</a>";
}
// Helper for sort links that preserves search
function sort_link($col, $label, $curSort, $curOrder, $search = '') {
    $nextOrder = ($curSort === $col && $curOrder === 'asc') ? 'desc' : 'asc';
    $arrow = '';
    if ($curSort === $col) $arrow = $curOrder === 'asc' ? ' ▲' : ' ▼';
    $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
    return "<a href='?sort=$col&order=$nextOrder$searchParam'>$label$arrow</a>";
}
// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200) {
    
    $photo = uniqid() . '.jpg';
    
    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Save photo with original format support
function save_photo_with_format($f, $folder, $width = 200, $height = 200) {
    $file_extension = strtolower(pathinfo($f->name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Default to jpg if extension not allowed
    if (!in_array($file_extension, $allowed_extensions)) {
        $file_extension = 'jpg';
    }
    
    $photo = uniqid() . '.' . $file_extension;
    
    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    
    // Determine output format based on extension
    $mime_type = 'image/jpeg'; // default
    switch ($file_extension) {
        case 'png':
            $mime_type = 'image/png';
            break;
        case 'gif':
            $mime_type = 'image/gif';
            break;
        case 'webp':
            $mime_type = 'image/webp';
            break;
        case 'jpg':
        case 'jpeg':
        default:
            $mime_type = 'image/jpeg';
            break;
    }
    
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", $mime_type);

    return $photo;
}

// Is money?
function is_money($value) {
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Is email?
function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}


// Generate <input type='text'>
function html_text($key, $attr = '') {
    $value = encode($_POST[$key] ?? $GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr >";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

//password
function html_password($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name = '$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false) {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    // Priority: $_POST (form submission) > $GLOBALS (existing data) > empty string
    $value = encode($_POST[$key] ?? $GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '') {
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}


// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}


// ============================================================================
// Security
// ============================================================================

// Global user object
$_user = $_SESSION['user'] ?? null;


// Login user
function login($user, $url = null) {
    $_SESSION['user'] = $user;
    
    // If no specific URL provided, redirect based on user role
    if ($url === null) {
        if ($user->role === 'Admin') {
            $url = '/page/admin/index.php';
        } else {
            $url = '/';
        }
    }
    
    redirect($url);
}

// Logout user
function logout($url = '/') {
    unset($_SESSION['user']);
    redirect($url);
}

// Check if current user is banned (real-time check)
function check_user_ban_status() {
    global $_user, $_db;
    
    if ($_user && isset($_user->id)) {
        // Get current status from database
        $stm = $_db->prepare('SELECT status FROM user WHERE id = ?');
        $stm->execute([$_user->id]);
        $current_user = $stm->fetch();
        
        if ($current_user && $current_user->status === 'banned') {
            // Update session user object
            $_user->status = 'banned';
            // Force logout
            unset($_SESSION['user']);
            temp('info', 'Your account has been banned. Please contact administrator.');
            redirect('/page/user/login.php');
        }
    }
}
// Authorization
function auth(...$roles) {
    global $_user;
    if ($_user) {
        check_user_ban_status();

        if ($roles) {
            if (in_array($_user->role, $roles)) {
                return; // OK
            }
        }
        else {
            return; // OK
        }
    }
    
    redirect('/page/user/login.php');
}



// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
$_db = new PDO('mysql:dbname=osbuzz', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);


// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}


function get_categories_for_select() {
    global $_db;
    $stm = $_db->prepare('SELECT category_id, category_name FROM category ORDER BY category_name');
    $stm->execute();
    $result = [];
    foreach ($stm->fetchAll() as $cat) {
        $result[$cat->category_id] = $cat->category_name;
    }
    return $result;
}

$categories = get_categories_for_select();


// Shoe Sizes
$SIZES = [
    '36' => 'EU 36',
    '37' => 'EU 37',
    '38' => 'EU 38',
    '39' => 'EU 39',
    '40' => 'EU 40',
    '41' => 'EU 41',
    '42' => 'EU 42',
    '43' => 'EU 43',
    '44' => 'EU 44',
    '45' => 'EU 45'
];

// ============================================================================
// Cart Functions
// ============================================================================

// Format size display with EU prefix
function format_size($size) {
    if (empty($size)) {
        return '';
    }
    return "EU $size";
}

// Add item to cart
function cart_add_item($product_id, $quantity, $size = '') {
    global $_db, $_user;
    
    if (!$_user) {
        return ['success' => false, 'message' => 'Please login to access cart'];
    }
    
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    
    if ($product_id <= 0 || $quantity <= 0) {
        return ['success' => false, 'message' => 'Invalid product or quantity'];
    }
    
    // Check if product exists
    $stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
    $stm->execute([$product_id]);
    $product = $stm->fetch();
    
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found'];
    }
    
    // Check stock availability
    if ($size) {
        // Check specific size stock
        $stm = $_db->prepare('SELECT SUM(stock) as stock FROM product_variants WHERE product_id = ? AND size = ?');
        $stm->execute([$product_id, $size]);
        $stock_result = $stm->fetch();
        $available_stock = $stock_result ? (int)$stock_result->stock : 0;
        
        // Get current cart quantity for this product and size
        $stm = $_db->prepare('SELECT SUM(quantity) as quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?');
        $stm->execute([$_user->id, $product_id, $size]);
        $cart_result = $stm->fetch();
        $cart_quantity = $cart_result ? (int)$cart_result->quantity : 0;
    } else {
        // Check total stock for product without size
        $stm = $_db->prepare('SELECT SUM(stock) as stock FROM product_variants WHERE product_id = ?');
        $stm->execute([$product_id]);
        $stock_result = $stm->fetch();
        $available_stock = $stock_result ? (int)$stock_result->stock : 0;
        
        // Get current cart quantity for this product (all sizes)
        $stm = $_db->prepare('SELECT SUM(quantity) as quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stm->execute([$_user->id, $product_id]);
        $cart_result = $stm->fetch();
        $cart_quantity = $cart_result ? (int)$cart_result->quantity : 0;
    }
    
    // Check if item already exists in cart
    $stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ?');
    $stm->execute([$_user->id, $product_id, $size]);
    $existing_item = $stm->fetch();
    
    $new_total_quantity = $cart_quantity + $quantity;
    
    // Check if new quantity exceeds available stock
    if ($new_total_quantity > $available_stock) {
        $remaining = $available_stock - $cart_quantity;
        if ($remaining <= 0) {
            return ['success' => false, 'message' => 'This item is out of stock'];
        } else {
            return ['success' => false, 'message' => "Only $remaining item(s) available (you already have $cart_quantity in cart)"];
        }
    }
    
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item->quantity + $quantity;
        $stm = $_db->prepare('UPDATE cart SET quantity = ? WHERE cart_id = ?');
        $stm->execute([$new_quantity, $existing_item->cart_id]);
    } else {
        // Add new item
        $stm = $_db->prepare('INSERT INTO cart (user_id, product_id, quantity, size, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stm->execute([$_user->id, $product_id, $quantity, $size]);
    }
    
    return ['success' => true, 'message' => 'Item added to cart successfully'];
}

// Remove item from cart
function cart_remove_item($cart_id) {
    global $_db, $_user;
    
    if (!$_user) {
        return ['success' => false, 'message' => 'Please login to access cart'];
    }
    
    $cart_id = (int)$cart_id;
    
    if ($cart_id <= 0) {
        return ['success' => false, 'message' => 'Invalid cart item'];
    }
    
    $stm = $_db->prepare('DELETE FROM cart WHERE cart_id = ? AND user_id = ?');
    $stm->execute([$cart_id, $_user->id]);
    
    return ['success' => true, 'message' => 'Item removed from cart'];
}

// Update cart item quantity
function cart_update_quantity($cart_id, $quantity) {
    global $_db, $_user;
    
    if (!$_user) {
        return ['success' => false, 'message' => 'Please login to access cart'];
    }
    
    $cart_id = (int)$cart_id;
    $quantity = (int)$quantity;
    
    if ($cart_id <= 0 || $quantity <= 0) {
        return ['success' => false, 'message' => 'Invalid cart item or quantity'];
    }
    
    // Get cart item details
    $stm = $_db->prepare('SELECT * FROM cart WHERE cart_id = ? AND user_id = ?');
    $stm->execute([$cart_id, $_user->id]);
    $cart_item = $stm->fetch();
    
    if (!$cart_item) {
        return ['success' => false, 'message' => 'Cart item not found'];
    }
    
    // Check stock availability
    if ($cart_item->size) {
        // Check specific size stock
        $stm = $_db->prepare('SELECT SUM(stock) as stock FROM product_variants WHERE product_id = ? AND size = ?');
        $stm->execute([$cart_item->product_id, $cart_item->size]);
        $stock_result = $stm->fetch();
        $available_stock = $stock_result ? (int)$stock_result->stock : 0;
        
        // Get current cart quantity for this product and size (excluding current item)
        $stm = $_db->prepare('SELECT SUM(quantity) as quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND cart_id != ?');
        $stm->execute([$_user->id, $cart_item->product_id, $cart_item->size, $cart_id]);
        $other_cart_quantity = $stm->fetch();
        $other_cart_quantity = $other_cart_quantity ? (int)$other_cart_quantity->quantity : 0;
    } else {
        // Check total stock for product without size
        $stm = $_db->prepare('SELECT SUM(stock) as stock FROM product_variants WHERE product_id = ?');
        $stm->execute([$cart_item->product_id]);
        $stock_result = $stm->fetch();
        $available_stock = $stock_result ? (int)$stock_result->stock : 0;
        
        // Get current cart quantity for this product (excluding current item)
        $stm = $_db->prepare('SELECT SUM(quantity) as quantity FROM cart WHERE user_id = ? AND product_id = ? AND cart_id != ?');
        $stm->execute([$_user->id, $cart_item->product_id, $cart_id]);
        $other_cart_quantity = $stm->fetch();
        $other_cart_quantity = $other_cart_quantity ? (int)$other_cart_quantity->quantity : 0;
    }
    
    $new_total_quantity = $other_cart_quantity + $quantity;
    
    // Check if new quantity exceeds available stock
    if ($new_total_quantity > $available_stock) {
        $max_allowed = $available_stock - $other_cart_quantity;
        if ($max_allowed <= 0) {
            return ['success' => false, 'message' => 'This item is out of stock'];
        } else {
            return ['success' => false, 'message' => "Only $max_allowed item(s) available"];
        }
    }
    
    $stm = $_db->prepare('UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?');
    $stm->execute([$quantity, $cart_id, $_user->id]);
    
    return ['success' => true, 'message' => 'Quantity updated'];
}

// Get cart count for current user
function cart_get_count($user_id = null) {
    global $_db, $_user;
    
    $user_id = $user_id ?? $_user->id ?? 0;
    
    if ($user_id <= 0) {
        return 0;
    }
    
    $stm = $_db->prepare('SELECT SUM(quantity) as total FROM cart WHERE user_id = ?');
    $stm->execute([$user_id]);
    $result = $stm->fetch();
    
    return (int)($result->total ?? 0);
}

// Send order confirmation email (placeholder function)
function send_order_confirmation_email($order_id) {
    global $_db;
    
    try {
        // Get order details with user info
        $stm = $_db->prepare('
            SELECT o.*, u.username, u.email
            FROM orders o
            JOIN user u ON o.user_id = u.id
            WHERE o.order_id = ?
        ');
        $stm->execute([$order_id]);
        $order = $stm->fetch();
        
        if (!$order) {
            return false;
        }
        
        // Get order items
        $stm = $_db->prepare('
            SELECT oi.*, p.photo
            FROM order_items oi
            LEFT JOIN product p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
            ORDER BY oi.order_item_id
        ');
        $stm->execute([$order_id]);
        $order_items = $stm->fetchAll();
        
        $mail = get_mail();
        $mail->addAddress($order->email, $order->username);
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation #' . $order->order_number . ' - Osbuzzz';
        
        // Build order items HTML
        $items_html = '';
        foreach ($order_items as $item) {
            $items_html .= "
                <tr style='border-bottom: 1px solid #eee;'>
                    <td style='padding: 15px 10px; vertical-align: top;'>
                        <strong>" . htmlspecialchars($item->product_name) . "</strong><br>
                        <small style='color: #666;'>Brand: " . htmlspecialchars($item->product_brand) . "</small><br>";
            
            if ($item->size) {
                $items_html .= "<small style='color: #666;'>Size: " . htmlspecialchars($item->size) . "</small><br>";
            }
            
            $items_html .= "<small style='color: #666;'>Price: RM" . number_format($item->price, 2) . "</small>
                    </td>
                    <td style='padding: 15px 10px; text-align: center; vertical-align: top;'>
                        " . $item->quantity . "
                    </td>
                    <td style='padding: 15px 10px; text-align: right; vertical-align: top; font-weight: bold;'>
                        RM" . number_format($item->total_price, 2) . "
                    </td>
                </tr>";
        }
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0;
                    background: linear-gradient(135deg, #D3F4EF 0%, #007cba 100%);
                }
                .container { 
                    max-width: 600px; 
                    margin: 20px auto; 
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,124,186,0.2);
                }
                .header { 
                    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 300;
                }
                .content { 
                    background: white; 
                    padding: 30px; 
                }
                .order-info {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .order-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .order-table th {
                    background: #f8f9fa;
                    padding: 12px 10px;
                    text-align: left;
                    border-bottom: 2px solid #007cba;
                }
                .order-table th:last-child {
                    text-align: right;
                }
                .total-row {
                    background: #f8f9fa;
                    font-weight: bold;
                }
                .grand-total {
                    background: #007cba;
                    color: white;
                    font-size: 18px;
                }
                .success-icon {
                    font-size: 48px;
                    margin-bottom: 15px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='success-icon'>✅</div>
                    <h1>Order Confirmed!</h1>
                    <p style='margin: 0; opacity: 0.9;'>Thank you for your purchase</p>
                </div>
                <div class='content'>
                    <h2>Hello " . htmlspecialchars($order->username) . "!</h2>
                    <p>Your order has been successfully placed and is being processed. Here are your order details:</p>
                    
                    <div class='order-info'>
                        <p><strong>Order Number:</strong> " . htmlspecialchars($order->order_number) . "</p>
                        <p><strong>Order Date:</strong> " . date('F d, Y', strtotime($order->created_at)) . "</p>
                        <p><strong>Payment Method:</strong> " . ucfirst(str_replace('_', ' ', $order->payment_method)) . "</p>
                        <p><strong>Order Status:</strong> " . ucfirst($order->order_status) . "</p>
                    </div>
                    
                    <h3>Items Ordered:</h3>
                    <table class='order-table'>
                        <tr>
                            <th>Product</th>
                            <th style='text-align: center;'>Qty</th>
                            <th style='text-align: right;'>Total</th>
                        </tr>
                        " . $items_html . "
                        <tr class='total-row'>
                            <td colspan='2' style='padding: 15px 10px; text-align: right;'>Subtotal:</td>
                            <td style='padding: 15px 10px; text-align: right;'>RM" . number_format($order->total_amount, 2) . "</td>
                        </tr>
                        <tr class='total-row'>
                            <td colspan='2' style='padding: 15px 10px; text-align: right;'>Shipping:</td>
                            <td style='padding: 15px 10px; text-align: right;'>RM" . number_format($order->shipping_fee, 2) . "</td>
                        </tr>
                        <tr class='total-row'>
                            <td colspan='2' style='padding: 15px 10px; text-align: right;'>Tax:</td>
                            <td style='padding: 15px 10px; text-align: right;'>RM" . number_format($order->tax_amount, 2) . "</td>
                        </tr>";
        
        // Add loyalty discount row if used
        if (isset($order->loyalty_points_used) && $order->loyalty_points_used > 0) {
            $items_html .= "
                        <tr style='color: #28a745; background: #d4edda;'>
                            <td colspan='2' style='padding: 15px 10px; text-align: right;'>🎉 Loyalty Discount (-" . number_format($order->loyalty_points_used) . " points):</td>
                            <td style='padding: 15px 10px; text-align: right; font-weight: bold;'>-RM" . number_format($order->loyalty_discount, 2) . "</td>
                        </tr>";
        }
        
        $items_html .= "
                        <tr class='grand-total'>
                            <td colspan='2' style='padding: 15px 10px; text-align: right;'>Grand Total:</td>
                            <td style='padding: 15px 10px; text-align: right;'>RM" . number_format($order->grand_total, 2) . "</td>
                        </tr>
                    </table>
                    
                    <h3>Shipping Address:</h3>
                    <div class='order-info'>
                        " . nl2br(htmlspecialchars($order->shipping_address)) . "
                    </div>
                    
                    <h3>What's Next?</h3>
                    <ul>
                        <li>Your order is being processed and will be shipped within 1-2 business days</li>
                        <li>You'll receive a tracking number once your order ships</li>
                        <li>Estimated delivery: 3-5 business days</li>
                    </ul>
                    
                    <p>If you have any questions about your order, please contact our customer support.</p>
                    
                    <p style='margin-top: 30px;'>
                        Best regards,<br>
                        <span style='color: #007cba; font-weight: 500;'>The Osbuzzz Team</span>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hello " . $order->username . ",\n\n"
                       . "Your order #" . $order->order_number . " has been confirmed!\n\n"
                       . "Order Date: " . date('F d, Y', strtotime($order->created_at)) . "\n"
                       . "Payment Method: " . ucfirst(str_replace('_', ' ', $order->payment_method)) . "\n"
                       . "Total Amount: RM" . number_format($order->grand_total, 2) . "\n\n"
                       . "Your order is being processed and will be shipped within 1-2 business days.\n\n"
                       . "Thank you for shopping with us!\n\n"
                       . "Best regards,\nThe Osbuzzz Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send order confirmation email: " . $e->getMessage());
        return false;
    }
}

// Clear entire cart
function cart_clear($user_id = null) {
    global $_db, $_user;
    
    $user_id = $user_id ?? $_user->id ?? 0;
    
    if ($user_id <= 0) {
        return ['success' => false, 'message' => 'Please login to access cart'];
    }
    
    $stm = $_db->prepare('DELETE FROM cart WHERE user_id = ?');
    $stm->execute([$user_id]);
    
    return ['success' => true, 'message' => 'Cart cleared successfully'];
}

// Get cart items for current user
function cart_get_items($user_id = null) {
    global $_db, $_user;
    
    $user_id = $user_id ?? $_user->id ?? 0;
    
    if ($user_id <= 0) {
        return [];
    }
    
    $stm = $_db->prepare('
        SELECT c.*, p.product_name as name, p.brand, p.price, p.photo 
        FROM cart c 
        JOIN product p ON c.product_id = p.product_id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC
    ');
    $stm->execute([$user_id]);
    
    return $stm->fetchAll();
}

// Get all categories
function get_all_categories() {
    global $_db;
    
    $stm = $_db->prepare('SELECT * FROM category ORDER BY category_name');
    $stm->execute();
    
    return $stm->fetchAll();
}

// Get category by ID
function get_category_by_id($category_id) {
    global $_db;
    
    $stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
    $stm->execute([$category_id]);
    
    return $stm->fetch();
}

// Get category by slug
function get_category_by_slug($slug) {
    global $_db;
    
    $stm = $_db->prepare('SELECT * FROM category WHERE category_slug = ?');
    $stm->execute([$slug]);
    
    return $stm->fetch();
}

// ============================================================================
// Email Functions
// ============================================================================

// Initialize and return mail object
function get_mail() {
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'taneason0912@gmail.com';
    $m->Password = 'tmdz rbwz tvyt yqbh';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, 'Osbuzzz');

    return $m;
}

// Send password reset email
function send_reset_email($to_email, $user_name, $reset_link) {
    try {
        $mail = get_mail();
        $mail->addAddress($to_email, $user_name);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - Osbuzzz';
        
        // Embed logo image
        $logo_path = __DIR__ . '/images/logo.png';
        if (file_exists($logo_path)) {
            $mail->addEmbeddedImage($logo_path, 'logo', 'logo.png');
            $logo_src = 'cid:logo';
        } else {
            $logo_src = '';
        }
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0;
                    background: linear-gradient(135deg, #D3F4EF 0%, #007cba 100%);
                }
                .container { 
                    max-width: 600px; 
                    margin: 20px auto; 
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,124,186,0.2);
                }
                .header { 
                    background: linear-gradient(135deg, #007cba 0%, #005a8a 100%); 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center;
                    position: relative;
                }
                .header::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: #D3F4EF;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 300;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                }
                .content { 
                    background: white; 
                    padding: 40px 30px; 
                    position: relative;
                }
                .content::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 30px;
                    right: 30px;
                    height: 3px;
                    background: linear-gradient(90deg, #D3F4EF 0%, #007cba 50%, #D3F4EF 100%);
                    border-radius: 2px;
                }
                .content h2 {
                    color: #007cba;
                    margin-top: 20px;
                    margin-bottom: 20px;
                    font-weight: 500;
                }
                .button { 
                    display: inline-block; 
                    padding: 15px 35px; 
                    background: linear-gradient(135deg, #007cba 0%, #005a8a 100%); 
                    color: white !important; 
                    text-decoration: none; 
                    border-radius: 25px; 
                    margin: 25px 0;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    box-shadow: 0 4px 15px rgba(0,124,186,0.3);
                    transition: all 0.3s ease;
                }
                .button:hover {
                    color: white !important;
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(0,124,186,0.4);
                }
                .link-box {
                    word-break: break-all; 
                    background: linear-gradient(135deg, #D3F4EF 0%, #f0f9f7 100%); 
                    padding: 15px; 
                    border-radius: 8px;
                    border-left: 4px solid #007cba;
                    font-family: monospace;
                    font-size: 14px;
                }
                .warning {
                    background: linear-gradient(135deg, #fff3cd 0%, #fef7e0 100%);
                    border-left: 4px solid #007cba;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .footer { 
                    background: #f8f9fa;
                    text-align: center; 
                    padding: 20px; 
                    color: #666; 
                    font-size: 12px;
                    border-top: 1px solid #e9ecef;
                }
                .logo-area {
                    margin-bottom: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 15px;
                }
                .email-logo {
                    width: 40px;
                    height: 40px;
                    object-fit: contain;
                    filter: brightness(0) invert(1);
                }
                .brand-accent {
                    color: #D3F4EF;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo-area'>" . 
                        ($logo_src ? "<img src='" . $logo_src . "' alt='Osbuzzz Logo' class='email-logo'>" : "") . "
                        <h1><span class='brand-accent'>Osbuzzz</span></h1>
                    </div>
                    <p style='margin: 15px 0 0 0; opacity: 0.9; font-size: 16px;'>Password Reset Request</p>
                </div>
                <div class='content'>
                    <h2>👋 Hello " . htmlspecialchars($user_name ?: 'Valued Customer') . "!</h2>
                    <p>We received a request to reset your password for your <strong>Osbuzzz</strong> account.</p>
                    <p>Click the button below to securely reset your password:</p>
                    <p style='text-align: center;'>
                        <a href='" . htmlspecialchars($reset_link) . "' class='button'>🔐 Reset My Password</a>
                    </p>
                    <p>Or copy and paste this secure link into your browser:</p>
                    <div class='link-box'>
                        " . htmlspecialchars($reset_link) . "
                    </div>
                    <div class='warning'>
                        <p><strong>⏰ Important:</strong> This secure link will expire in <strong>5 minutes</strong> for your security.</p>
                    </div>
                    <p>If you didn't request this password reset, please ignore this email. Your password will remain secure and unchanged.</p>
                    <p style='margin-top: 30px;'>
                        Best regards,<br>
                        <span style='color: #007cba; font-weight: 500;'>The Osbuzzz Team</span>
                    </p>
                </div>
                <div class='footer'>
                    <p>🔒 This is a secure automated message. Please do not reply to this email.</p>
                    <p style='margin-top: 10px; color: #007cba;'>Osbuzzz - Your Trusted Sports Footwear Partner</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hello " . ($user_name ?: 'Valued Customer') . ",\n\n"
                       . "We received a request to reset your password for your OSBuzz account.\n\n"
                       . "Please click on the following link to reset your password:\n"
                       . $reset_link . "\n\n"
                       . "This link will expire in 5 minutes.\n\n"
                       . "If you didn't request this password reset, please ignore this email.\n\n"
                       . "Best regards,\nThe OSBuzz Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send reset email: " . $e->getMessage());
        return false;
    }
}

// Auto-cleanup expired password reset tokens
function cleanup_expired_tokens() {
    global $_db;
    
    try {
        $stm = $_db->prepare('DELETE FROM password_resets WHERE expires_at < NOW()');
        $stm->execute();
        $deleted_count = $stm->rowCount();
        
        if ($deleted_count > 0) {
            error_log("Auto-cleanup: Removed $deleted_count expired password reset tokens");
        }
        
        return $deleted_count;
    } catch (Exception $e) {
        error_log("Failed to cleanup expired tokens: " . $e->getMessage());
        return 0;
    }
}

// ============================================================================
// Global Variables - Country/State Data
// ============================================================================

// Malaysia States
$MALAYSIA_STATES = [
    'JHR' => 'Johor',
    'KDH' => 'Kedah',
    'KTN' => 'Kelantan',
    'MLK' => 'Melaka',
    'NSN' => 'Negeri Sembilan',
    'PHG' => 'Pahang',
    'PNG' => 'Pulau Pinang',
    'PRK' => 'Perak',
    'PLS' => 'Perlis',
    'SBH' => 'Sabah',
    'SWK' => 'Sarawak',
    'SGR' => 'Selangor',
    'TRG' => 'Terengganu',
    'WP_KUL' => 'Wilayah Persekutuan Kuala Lumpur',
    'WP_LBN' => 'Wilayah Persekutuan Labuan',
    'WP_PJY' => 'Wilayah Persekutuan Putrajaya'
];

// Function to generate state options for select dropdown
function generate_state_options($selected_state = '') {
    global $MALAYSIA_STATES;

    $options = '<option value="">Choose State</option>';

    foreach ($MALAYSIA_STATES as $code => $name) {
        $selected = ($selected_state == $code) ? 'selected' : '';
        $options .= "<option value=\"$code\" $selected>$name</option>";
    }
    
    return $options;
}

// Function to get state name by code
function get_state_name($state_code) {
    global $MALAYSIA_STATES;
    return $MALAYSIA_STATES[$state_code] ?? $state_code;
}

// ============================================================================
// Validation Functions
// ============================================================================

// Validate Malaysian phone number
function validate_malaysian_phone($phone) {
    // Remove all spaces, hyphens, and plus signs
    $clean_phone = preg_replace('/[\s\-\+\(\)]/', '', $phone);
    
    // Malaysian phone number patterns:
    // Mobile: 01X-XXXXXXX (10-11 digits starting with 01)
    // Landline: 0X-XXXXXXX (9-10 digits starting with 0)
    // International format: +601X-XXXXXXX or 601X-XXXXXXX
    
    // Check for international format (+60 or 60)
    if (preg_match('/^(\+?60)/', $clean_phone)) {
        $clean_phone = preg_replace('/^\+?60/', '0', $clean_phone);
    }
    
    // Validate format
    if (preg_match('/^0\d{8,10}$/', $clean_phone)) {
        // Check specific mobile prefixes (more comprehensive)
        if (preg_match('/^01[0-9]\d{7,8}$/', $clean_phone)) {
            return true; // Mobile number
        }
        // Check landline prefixes (03, 04, 05, 06, 07, 08, 09)
        if (preg_match('/^0[3-9]\d{7,8}$/', $clean_phone)) {
            return true; // Landline number
        }
    }
    
    return false;
}

// Format Malaysian phone number for display
function format_malaysian_phone($phone) {
    // Clean the phone number
    $clean_phone = preg_replace('/[\s\-\+\(\)]/', '', $phone);
    
    // Handle international format
    if (preg_match('/^(\+?60)/', $clean_phone)) {
        $clean_phone = preg_replace('/^\+?60/', '0', $clean_phone);
    }
    
    // Format mobile numbers (01X-XXX-XXXX)
    if (preg_match('/^(01\d)(\d{3,4})(\d{4})$/', $clean_phone, $matches)) {
        return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }
    
    // Format landline numbers (0X-XXXX-XXXX)
    if (preg_match('/^(0[3-9])(\d{4})(\d{4})$/', $clean_phone, $matches)) {
        return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }
    
    // Return original if no pattern matches
    return $phone;
}

// ============================================================================
// LOYALTY POINTS SYSTEM
// ============================================================================

// Get loyalty settings
function get_loyalty_setting($key, $default = null) {
    global $_db;
    $stm = $_db->prepare('SELECT setting_value FROM loyalty_settings WHERE setting_key = ?');
    $stm->execute([$key]);
    $result = $stm->fetchColumn();
    return $result !== false ? $result : $default;
}

// Calculate points earned from purchase amount
function calculate_points_earned($amount) {
    $points_per_ringgit = (int)get_loyalty_setting('points_per_ringgit', 1);
    return floor($amount * $points_per_ringgit);
}

// Calculate discount from points
function calculate_discount_from_points($points) {
    $ratio = (int)get_loyalty_setting('points_to_ringgit_ratio', 100);
    return floor($points / $ratio);
}

// Get user's current loyalty points
function get_user_loyalty_points($user_id) {
    global $_db;
    $stm = $_db->prepare('SELECT loyalty_points FROM user WHERE id = ?');
    $stm->execute([$user_id]);
    return (int)$stm->fetchColumn();
}

// Add loyalty transaction and update user balance
function add_loyalty_transaction($user_id, $points, $type, $description, $order_id = null) {
    global $_db;
    
    try {
        // Check if we're already in a transaction
        $in_transaction = $_db->inTransaction();
        
        if (!$in_transaction) {
            $_db->beginTransaction();
        }
        
        // Insert transaction record
        $stm = $_db->prepare('INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description) VALUES (?, ?, ?, ?, ?)');
        $stm->execute([$user_id, $order_id, $points, $type, $description]);
        
        // Update user's loyalty points balance
        $stm = $_db->prepare('UPDATE user SET loyalty_points = loyalty_points + ? WHERE id = ?');
        $stm->execute([$points, $user_id]);
        
        if (!$in_transaction) {
            $_db->commit();
        }
        return true;
    } catch (Exception $e) {
        if (!$in_transaction && $_db->inTransaction()) {
            $_db->rollBack();
        }
        return false;
    }
}

// Validate points redemption
function validate_points_redemption($user_id, $points_to_redeem, $order_total) {
    $user_points = get_user_loyalty_points($user_id);
    $min_points = (int)get_loyalty_setting('minimum_points_redeem', 100);
    $max_discount_percent = (int)get_loyalty_setting('maximum_discount_percentage', 50);
    
    $errors = [];
    
    if ($points_to_redeem < $min_points) {
        $errors[] = "Minimum $min_points points required for redemption";
    }
    
    if ($points_to_redeem > $user_points) {
        $errors[] = "Insufficient points. You have $user_points points";
    }
    
    $discount = calculate_discount_from_points($points_to_redeem);
    $max_discount = ($order_total * $max_discount_percent) / 100;
    
    if ($discount > $max_discount) {
        $errors[] = "Maximum discount is $max_discount_percent% of order total";
    }
    
    return empty($errors) ? true : $errors;
}

// ============================================================================
// EMAIL VERIFICATION SYSTEM
// ============================================================================

// Generate verification token
function generate_verification_token() {
    return bin2hex(random_bytes(32));
}

// Send verification email
function send_verification_email($email, $token, $type = 'registration', $username = '') {
    $verification_url = get_base_url() . "/page/user/verify_email.php?token=" . urlencode($token);

    $subject = $type === 'registration' ? 'Verify Your Email - Osbuzzz' : 'Verify Your New Email - Osbuzzz';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007cba; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { display: inline-block; background: #007cba; color: white; padding: 12px 24px; 
                     text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { padding: 20px; font-size: 12px; color: #666; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Osbuzzz Email Verification</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($username ?: 'User') . "!</h2>
                <p>Thank you for " . ($type === 'registration' ? 'registering with' : 'updating your email on') . " Osbuzzz.</p>
                <p>Please click the button below to verify your email address:</p>
                <p><a href='$verification_url' class='button'>Verify Email Address</a></p>
                <p>Or copy and paste this link into your browser:</p>
                <p><small>$verification_url</small></p>
                <p><strong>This link will expire in 5 minutes.</strong></p>
                <p>If you didn't request this verification, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Osbuzzz. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        $mail = get_mail();
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email verification sending failed: " . $e->getMessage());
        return false;
    }
}

// Create email verification record
function create_email_verification($user_id, $email, $type = 'registration') {
    global $_db;
    
    $token = generate_verification_token();
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    try {
        // Clean up any existing tokens for this user and type
        $stm = $_db->prepare('DELETE FROM email_verification_logs WHERE user_id = ? AND action_type = ? AND verified_at IS NULL');
        $stm->execute([$user_id, $type]);
        
        // Insert new verification record
        $stm = $_db->prepare('INSERT INTO email_verification_logs (user_id, email, token, action_type, expires_at, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
        $stm->execute([$user_id, $email, $token, $type, $expires, $ip]);
        
        return $token;
    } catch (Exception $e) {
        return false;
    }
}

// Verify email token
function verify_email_token($token) {
    global $_db;
    
    $stm = $_db->prepare('
        SELECT evl.*, u.username 
        FROM email_verification_logs evl 
        JOIN user u ON evl.user_id = u.id 
        WHERE evl.token = ? AND evl.verified_at IS NULL AND evl.expires_at > NOW()
    ');
    $stm->execute([$token]);
    $verification = $stm->fetch();
    
    if (!$verification) {
        return ['success' => false, 'message' => 'Invalid or expired verification token'];
    }
    
    try {
        $_db->beginTransaction();
        
        // Mark verification as completed
        $stm = $_db->prepare('UPDATE email_verification_logs SET verified_at = NOW() WHERE log_id = ?');
        $stm->execute([$verification->log_id]);
        
        if ($verification->action_type === 'registration') {
            // For registration verification
            $stm = $_db->prepare('UPDATE user SET email_verified = 1 WHERE id = ?');
            $stm->execute([$verification->user_id]);
            
            // Award signup bonus points
            $signup_bonus = (int)get_loyalty_setting('signup_bonus_points', 100);
            if ($signup_bonus > 0) {
                add_loyalty_transaction($verification->user_id, $signup_bonus, 'bonus', 'Email verification bonus');
            }
            
        } else if ($verification->action_type === 'email_change') {
            // For email change verification - update the user's email directly
            $stm = $_db->prepare('UPDATE user SET email = ? WHERE id = ?');
            $stm->execute([$verification->email, $verification->user_id]);
            
            // Update session if this is the current user
            global $_user;
            if ($_user && $_user->id == $verification->user_id) {
                refresh_user_session($verification->user_id);
            }
        }
        
        $_db->commit();
        return ['success' => true, 'message' => 'Email verified successfully!', 'type' => $verification->action_type];
        
    } catch (Exception $e) {
        $_db->rollBack();
        return ['success' => false, 'message' => 'Verification failed. Please try again.'];
    }
}

// Refresh user session data from database
function refresh_user_session($user_id) {
    global $_db, $_user;
    
    $stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
    $stm->execute([$user_id]);
    $updated_user = $stm->fetch();
    
    if ($updated_user) {
        $_SESSION['user'] = $updated_user;
        $_user = $updated_user;
    }
}

// Check if email verification is required
function is_email_verification_required() {
    // Email verification is always required for loyalty points system
    return true;
}

// Get base URL for email links
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $base_path = rtrim(str_replace('\\', '/', $script_name), '/');
    
    // Remove '/page' and other deep paths to get to the app root
    $base_path = preg_replace('#/page.*#', '', $base_path);
    
    return $protocol . $host . $base_path;
}

// Cleanup expired unverified users
function cleanup_expired_unverified_users() {
    global $_db;
    
    try {
        // Start transaction
        $_db->beginTransaction();
        
        // Find users whose email verification has expired
        $stm = $_db->prepare('
            SELECT DISTINCT u.id, u.email, u.username 
            FROM user u
            INNER JOIN email_verification_logs evl ON u.id = evl.user_id 
            WHERE u.email_verified = 0 
            AND evl.expires_at < NOW()
            AND evl.verified_at IS NULL
        ');
        $stm->execute();
        $expired_users = $stm->fetchAll();
        
        if ($expired_users) {
            foreach ($expired_users as $user) {
                // Delete loyalty transactions for this user
                $stm = $_db->prepare('DELETE FROM loyalty_transactions WHERE user_id = ?');
                $stm->execute([$user->id]);
                
                // Delete email verification logs for this user
                $stm = $_db->prepare('DELETE FROM email_verification_logs WHERE user_id = ?');
                $stm->execute([$user->id]);
                
                // Delete the user
                $stm = $_db->prepare('DELETE FROM user WHERE id = ?');
                $stm->execute([$user->id]);
                
                // Log the cleanup action
                error_log("Cleaned up expired unverified user: {$user->email} (ID: {$user->id})");
            }
        }
        
        // Also cleanup old expired verification logs (even for verified users)
        $stm = $_db->prepare('DELETE FROM email_verification_logs WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $stm->execute();
        
        $_db->commit();
        return count($expired_users);
        
    } catch (Exception $e) {
        $_db->rollback();
        error_log("Error cleaning up expired users: " . $e->getMessage());
        return false;
    }
}

// Auto cleanup function that can be called on login/registration pages
function auto_cleanup_expired_users() {
    // Run cleanup every time (100% chance)
    cleanup_expired_unverified_users();
}