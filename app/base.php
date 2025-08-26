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
function usort_link($col, $label, $usort, $uorder, $search = '') {
    $nextOrder = ($usort === $col && $uorder === 'asc') ? 'desc' : 'asc';
    $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
    return "<a href='?usort=$col&uorder=$nextOrder$searchParam#users'>$label" . ($usort === $col ? ($uorder === 'asc' ? ' ▲' : ' ▼') : '') . "</a>";
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
    $value = encode($GLOBALS[$key] ?? '');
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
function login($user, $url = '/') {
    $_SESSION['user'] = $user;
    redirect($url);
}

// Logout user
function logout($url = '/') {
    unset($_SESSION['user']);
    redirect($url);
}

// Authorization
function auth(...$roles) {
    global $_user;
    if ($_user) {
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

$categories = [
        'Running' => 'Running Shoes',
        'Basketball' => 'Basketball Shoes',
        'Casual' => 'Casual Shoes',
        'Formal' => 'Formal Shoes',
        'Other' => 'Other'
    ];


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
        SELECT c.*, p.product_name as name, p.price, p.photo 
        FROM cart c 
        JOIN product p ON c.product_id = p.product_id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC
    ');
    $stm->execute([$user_id]);
    
    return $stm->fetchAll();
}