<?php
require '../../base.php';

// Redirect to login if not logged in
auth();

// Check if selective checkout items are specified
$selected_cart_ids = $_SESSION['selected_cart_items'] ?? null;
$buy_now_item = $_SESSION['buy_now_item'] ?? null;

// Check if we have checkout data from previous submission (form resubmission)
$existing_checkout_data = $_SESSION['checkout_data'] ?? null;
if ($existing_checkout_data && isset($existing_checkout_data['cart_items'])) {
    // Use cart items from previous checkout data to maintain consistency
    $cart_items = $existing_checkout_data['cart_items'];
    $is_buy_now = isset($_SESSION['buy_now_item']);
    $is_selective_checkout = $existing_checkout_data['is_selective_checkout'] ?? false;
} else {
    // Get cart items for current user (first time loading checkout)
    if ($buy_now_item) {
        // Handle buy now item (single item checkout without affecting cart)
        $cart_items = [(object) [
            'cart_id' => null, // No cart_id for buy now items
            'product_id' => $buy_now_item['product_id'],
            'quantity' => $buy_now_item['quantity'],
            'size' => $buy_now_item['size'],
            'name' => $buy_now_item['name'],
            'price' => $buy_now_item['price'],
            'photo' => $buy_now_item['photo'],
            'brand' => $buy_now_item['brand']
        ]];
        
        // Mark this as a buy now checkout
        $is_buy_now = true;
        $is_selective_checkout = false;
    } else if ($selected_cart_ids) {
        // Get only selected items
        $placeholders = str_repeat('?,', count($selected_cart_ids) - 1) . '?';
        $stm = $_db->prepare("
            SELECT c.cart_id, c.product_id, c.quantity, c.size,
                   p.product_name as name, p.price, 
                   p.photo, p.brand
            FROM cart c
            JOIN product p ON c.product_id = p.product_id
            WHERE c.cart_id IN ($placeholders) AND c.user_id = ?
            ORDER BY c.cart_id
        ");
        $params = array_merge($selected_cart_ids, [$_user->id]);
        $stm->execute($params);
        $cart_items = $stm->fetchAll();
        
        $is_buy_now = false;
        $is_selective_checkout = true;
    } else {
        // Get all cart items (normal checkout)
        $cart_items = cart_get_items();
        $is_buy_now = false;
        $is_selective_checkout = false;
    }
}

// Redirect if cart is empty (but not for buy now orders)
if (count($cart_items) == 0 && !isset($_SESSION['buy_now_item'])) {
    temp('error', 'Your cart is empty');
    redirect('/page/shop/cart.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item->price * $item->quantity;
}

$shipping_fee = 10.00; // Fixed shipping fee (RM)
$tax_rate = 0.06; // 6% tax
$tax_amount = $subtotal * $tax_rate;
$grand_total = $subtotal + $shipping_fee + $tax_amount;

// Get user's saved addresses
$stm = $_db->prepare('SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$stm->execute([$_user->id]);
$saved_addresses = $stm->fetchAll();

// Pre-fill with user's default information if no saved addresses
$default_data = [
    'first_name' => explode(' ', $_user->username)[0] ?? '',
    'last_name' => implode(' ', array_slice(explode(' ', $_user->username), 1)) ?: '',
    'address_line_1' => $_user->address ?? '',
    'phone' => $_user->phone ?? ''
];


// Error handling variables
$error_class_first_name = '';
$error_class_last_name = '';
$error_class_address = '';
$error_class_city = '';
$error_class_state = '';
$error_class_postal_code = '';
$error_class_phone = '';

if (is_post()) {
    $use_saved_address = req('use_saved_address');
    
    if ($use_saved_address) {
        // Using saved address
        $stm = $_db->prepare('SELECT * FROM customer_addresses WHERE address_id = ? AND user_id = ?');
        $stm->execute([$use_saved_address, $_user->id]);
        $saved_address = $stm->fetch();
        
        if ($saved_address) {
            $first_name = $saved_address->first_name;
            $last_name = $saved_address->last_name;
            $company = $saved_address->company;
            $address_line_1 = $saved_address->address_line_1;
            $address_line_2 = $saved_address->address_line_2;
            $city = $saved_address->city;
            $state = $saved_address->state;
            $postal_code = $saved_address->postal_code;
            $phone = $saved_address->phone;
            $customer_notes = req('customer_notes');
            $payment_method = req('payment_method');
            
            // Validate payment method is selected
            if (!$payment_method) {
                $_err['payment_method'] = 'Please select a payment method';
            }
        } else {
            $_err['address'] = 'Invalid address selected';
        }
    } else {
        // Using new address - get from form
        $first_name = req('first_name');
        $last_name = req('last_name');
        $company = req('company');
        $address_line_1 = req('address_line_1');
        $address_line_2 = req('address_line_2');
        $city = req('city');
        $state = req('state');
        $postal_code = req('postal_code');
        $phone = req('phone');
        $customer_notes = req('customer_notes');
        $payment_method = req('payment_method');
        
        // Validation for new address
        if ($first_name == '') {
            $_err['first_name'] = 'First name is required';
            $error_class_first_name = 'class="error"';
        }
        
        if ($last_name == '') {
            $_err['last_name'] = 'Last name is required';
            $error_class_last_name = 'class="error"';
        }
        
        if ($address_line_1 == '') {
            $_err['address_line_1'] = 'Address is required';
            $error_class_address = 'class="error"';
        }
        
        if ($city == '') {
            $_err['city'] = 'City is required';
            $error_class_city = 'class="error"';
        }
        
        if ($state == '') {
            $_err['state'] = 'State is required';
            $error_class_state = 'class="error"';
        }
        
        if ($postal_code == '') {
            $_err['postal_code'] = 'Postal code is required';
            $error_class_postal_code = 'class="error"';
        }
        
        if ($phone == '') {
            $_err['phone'] = 'Phone number is required';
            $error_class_phone = 'class="error"';
        }
    }
    
    if (!$_err) {
        
        // Store checkout data in session for payment processing
        $_SESSION['checkout_data'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'company' => $company,
            'address_line_1' => $address_line_1,
            'address_line_2' => $address_line_2,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postal_code,
            'phone' => $phone,
            'customer_notes' => $customer_notes,
            'payment_method' => $payment_method,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'tax_amount' => $tax_amount,
            'grand_total' => $grand_total,
            'is_selective_checkout' => $is_selective_checkout,
            'cart_items' => $cart_items  // Save the actual cart items being checked out
        ];
        
        // Redirect to payment processing
        if ($payment_method == 'paypal') {
            redirect('/page/shop/payment_paypal.php');
        } elseif ($payment_method == 'cash_on_delivery') {
            redirect('/page/shop/payment_cod.php');
        } else {
            // Default fallback - shouldn't happen with current payment methods
            redirect('/page/shop/payment_success.php');
        }
    }
}

$_title = 'Checkout';
include '../../head.php';
?>

<main>
    <div class="checkout-container">
        <h1>Checkout</h1>
        
        <!-- Debug errors -->
        <?php if (!empty($_err)): ?>
            <div style="color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; border: 1px solid red;">
                <strong>Errors:</strong>
                <ul>
                    <?php foreach ($_err as $key => $error): ?>
                        <li><?= $key ?>: <?= encode($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="checkout-layout">
            <!-- Left side - Shipping & Payment Form -->
            <div class="checkout-form">
                <form method="post" id="checkoutForm">
                    <!-- Saved Addresses Selection -->
                    <?php if (!empty($saved_addresses)): ?>
                    <div class="form-section">
                        <h2>Select Shipping Address</h2>
                        
                        <div class="address-selection">
                            <?php foreach ($saved_addresses as $index => $address): ?>
                                <div class="address-option">
                                    <input type="radio" id="address_<?= $address->address_id ?>" name="selected_address" value="<?= $address->address_id ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                    <label for="address_<?= $address->address_id ?>">
                                        <div class="address-card-mini">
                                            <div class="address-name"><?= encode($address->address_name) ?></div>
                                            <div class="address-text">
                                                <?= encode($address->first_name . ' ' . $address->last_name) ?><br>
                                                <?= encode($address->address_line_1) ?><br>
                                                <?= encode($address->city . ', ' . get_state_name($address->state) . ' ' . $address->postal_code) ?><br>
                                                Phone: <?= encode($address->phone) ?>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="address-option">
                                <input type="radio" id="new_address" name="selected_address" value="new">
                                <label for="new_address">
                                    <div class="address-card-mini new-address">
                                        <div class="address-name">+ Use New Address</div>
                                        <div class="address-text">Enter a new shipping address</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Shipping Information Form -->
                    <div class="form-section" id="shippingForm" <?= !empty($saved_addresses) ? 'style="display: none;"' : '' ?>>
                        <h2>Shipping Information</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <?= html_text('first_name', 'placeholder="First Name" '.$error_class_first_name) ?>
                                <?= err('first_name') ?>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <?= html_text('last_name', 'placeholder="Last Name" '.$error_class_last_name) ?>
                                <?= err('last_name') ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="company">Company (Optional)</label>
                            <?= html_text('company', 'placeholder="Company Name"') ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="address_line_1">Address Line 1 <span class="required">*</span></label>
                            <?= html_text('address_line_1', 'placeholder="Street Address" '.$error_class_address) ?>
                            <?= err('address_line_1') ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="address_line_2">Address Line 2 (Optional)</label>
                            <?= html_text('address_line_2', 'placeholder="Apartment, unit, etc."') ?>
                        </div>
                        
                        <div class="form-row address-row">
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <?= html_text('city', 'placeholder="City" '.$error_class_city) ?>
                                <?= err('city') ?>
                            </div>
                            <div class="form-group" style="margin-left: 10px;">
                                <label for="state">State <span class="required">*</span></label>
                                <select id="state" name="state" class="<?= isset($_err['state']) ? 'error' : '' ?>">
                                    <?= generate_state_options($state ?? '') ?>
                                </select>
                                <?= err('state') ?>
                            </div>
                            <div class="form-group">
                                <label for="postal_code">Postal Code <span class="required">*</span></label>
                                <?= html_text('postal_code', 'placeholder="12345" '.$error_class_postal_code) ?>
                                <?= err('postal_code') ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <?= html_text('phone', 'placeholder="01X-XXX-XXXX" '.$error_class_phone) ?>
                            <?= err('phone') ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_notes">Order Notes (Optional)</label>
                            <?= html_textarea('customer_notes', 'rows="3" placeholder="Special instructions for delivery"') ?>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="form-section">
                        <h2>Payment Method</h2>
                        
                        <div class="payment-methods">
                            <div class="payment-option">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" checked>
                                <label for="paypal">
                                    <span class="payment-icon">💳</span>
                                    <span class="payment-text">
                                        <strong>PayPal</strong><br>
                                        <small>Pay securely with PayPal</small>
                                    </span>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="cod" name="payment_method" value="cash_on_delivery">
                                <label for="cod">
                                    <span class="payment-icon">💵</span>
                                    <span class="payment-text">
                                        <strong>Cash on Delivery</strong><br>
                                        <small>Pay when you receive your order</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">
                        Place Order - RM<?= number_format($grand_total, 2) ?>
                    </button>
                </form>
            </div>
            
            <!-- Right side - Order Summary -->
            <div class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../../images/Products/<?= $item->photo ?>" alt="<?= $item->name ?>">
                                <span class="item-quantity"><?= $item->quantity ?></span>
                            </div>
                            <div class="item-details">
                                <h4><?= $item->name ?></h4>
                                <?php if ($item->size): ?>
                                    <p class="item-size">Size: <?= format_size($item->size) ?></p>
                                <?php endif; ?>
                                <p class="item-price">RM<?= number_format($item->price * $item->quantity, 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>RM<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>RM<?= number_format($shipping_fee, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Tax (6%):</span>
                        <span>RM<?= number_format($tax_amount, 2) ?></span>
                    </div>
                    <div class="total-row total-grand">
                        <span><strong>Total:</strong></span>
                        <span><strong>RM<?= number_format($grand_total, 2) ?></strong></span>
                    </div>
                </div>
                
                <div class="security-info">
                    <p><i class="icon-lock"></i> Your order is secured with SSL encryption</p>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.checkout-container h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    margin-top: 20px;
}

.checkout-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 40px;
}

.form-section h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

/* Special layout for city/state/postal row */
.form-row.address-row {
    grid-template-columns: 2fr 1.5fr 1fr;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #2c3e50;
}

/* Red asterisk for required fields */
.form-group label .required {
    color: #e74c3c;
    font-weight: bold;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

.form-group input.error,
.form-group textarea.error {
    border-color: #e74c3c;
    background-color: #fdf2f2;
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.payment-option {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s;
}

.payment-option:hover {
    border-color: #3498db;
    background-color: #f8f9fa;
}

.payment-option input[type="radio"] {
    display: none;
}

.payment-option input[type="radio"]:checked + label {
    color: #3498db;
}

.payment-option input[type="radio"]:checked {
    background-color: #e3f2fd;
}

.payment-option label {
    display: flex;
    align-items: center;
    cursor: pointer;
    width: 100%;
}

.payment-icon {
    font-size: 24px;
    margin-right: 15px;
}

.payment-text {
    flex: 1;
}

.payment-text small {
    color: #666;
}

.btn-large {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    font-weight: bold;
    margin-top: 20px;
}

.order-summary {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
    height: fit-content;
}

.order-summary h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.order-items {
    margin-bottom: 30px;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    position: relative;
    width: 60px;
    height: 60px;
    margin-right: 15px;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.item-quantity {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #3498db;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.item-details h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 14px;
}

.item-size {
    margin: 0 0 5px 0;
    color: #666;
    font-size: 12px;
}

.item-price {
    margin: 0;
    font-weight: bold;
    color: #2c3e50;
    font-size: 14px;
}

.order-totals {
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #2c3e50;
}

.total-grand {
    border-top: 2px solid #3498db;
    padding-top: 15px;
    margin-top: 15px;
    font-size: 18px;
}

.security-info {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
    text-align: center;
    color: #666;
    font-size: 14px;
}

.icon-lock:before {
    content: "🔒";
    margin-right: 5px;
}

.address-selection {
    display: grid;
    gap: 15px;
    margin-bottom: 20px;
}

.address-option {
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s;
}

.address-option:hover {
    border-color: #3498db;
}

.address-option input[type="radio"] {
    display: none;
}

.address-option input[type="radio"]:checked + label {
    background: #e3f2fd;
    border-color: #3498db;
}

.address-option input[type="radio"]:checked + label .address-card-mini {
    border-left: 4px solid #3498db;
}

.address-option label {
    display: block;
    cursor: pointer;
    padding: 0;
    margin: 0;
}

.address-card-mini {
    padding: 20px;
    transition: all 0.3s;
}

.address-card-mini.new-address {
    text-align: center;
    border: 2px dashed #ddd;
    color: #3498db;
}

.address-name {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 16px;
}

.address-text {
    color: #666;
    line-height: 1.5;
    font-size: 14px;
}

.new-address .address-name {
    color: #3498db;
}

.new-address .address-text {
    color: #3498db;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-row.address-row {
        grid-template-columns: 1fr;
    }
    
    .checkout-form,
    .order-summary {
        padding: 20px;
    }
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.error {
    color: #e74c3c;
    font-size: 14px;
    margin-top: 5px;
}
</style>

<script>
// Handle address selection
document.addEventListener('DOMContentLoaded', function() {
    const addressRadios = document.querySelectorAll('input[name="selected_address"]');
    const shippingForm = document.getElementById('shippingForm');
    
    if (addressRadios.length > 0) {
        addressRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'new') {
                    shippingForm.style.display = 'block';
                    shippingForm.scrollIntoView({ behavior: 'smooth' });
                } else {
                    shippingForm.style.display = 'none';
                }
            });
        });
    }
    
    // Handle form submission
    const checkoutForm = document.getElementById('checkoutForm');
    checkoutForm.addEventListener('submit', function(e) {
        // Debug: check payment method selection
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        console.log('Selected payment method:', selectedPayment ? selectedPayment.value : 'none');
        
        const selectedAddress = document.querySelector('input[name="selected_address"]:checked');
        
        if (selectedAddress && selectedAddress.value !== 'new') {
            // If using saved address, populate hidden fields with address data
            const addressId = selectedAddress.value;
            
            // Create hidden input to indicate we're using a saved address
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'use_saved_address';
            hiddenInput.value = addressId;
            
            this.appendChild(hiddenInput);
        }
    });
    
    // Add payment method change listeners for debugging
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Payment method changed to:', this.value);
        });
    });
});
</script>

<?php
include '../../foot.php';
?>
