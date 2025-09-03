<?php
require '../../base.php';

// Redirect to login if not logged in
if (!$_user) {
    header('Location: ../user/login.php');
    exit;
}

// Handle form submissions for selective checkout
if (is_post()) {
    $action = req('action');
    
    if ($action === 'checkout_selected') {
        $selected_items = req('selected_items');
        
        if (empty($selected_items)) {
            temp('error', 'Please select items to checkout');
        } else {
            // Store selected items in session for checkout
            $_SESSION['selected_cart_items'] = $selected_items;
            redirect('checkout.php');
        }
    }
}

// Get search query
$search = get('search', '');

// Get cart items for current user
$cart_items = cart_get_items();

// Filter cart items based on search
if ($search) {
    $cart_items = array_filter($cart_items, function($item) use ($search) {
        return stripos($item->name, $search) !== false || 
               stripos($item->brand, $search) !== false;
    });
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item->price * $item->quantity;
}

// Calculate selected total (for display)
$selected_total = 0;

include '../../head.php';
?>

<main>
    <div class="cart-container">
        <div class="cart-header">
            <h1>🛒 Shopping Cart</h1>
            
            <!-- Search Box -->
            <div class="cart-search">
                <form method="get" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="search" value="<?= encode($search) ?>" placeholder="Search cart items..." class="search-input">
                        <button type="submit" class="search-btn">🔍</button>
                        <?php if ($search): ?>
                            <a href="cart.php" class="clear-search">✕</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

       
        
        <?php if (count($cart_items) == 0): ?>
            <div class="empty-cart">
                <?php if ($search): ?>
                    <p>No items found matching "<?= encode($search) ?>"</p>
                    <a href="cart.php" class="btn btn-secondary">View All Items</a>
                <?php else: ?>
                    <p>Your cart is empty</p>
                <?php endif; ?>
                <a href="/page/categories/category.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="post" id="cart-form">
                <input type="hidden" name="action" value="checkout_selected">
                
                <!-- Cart Controls -->
                <div class="cart-controls">
                    <div class="select-controls">
                        <label class="select-all-label">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                            <span class="checkmark"></span>
                            Select All (<?= count($cart_items) ?> items)
                        </label>
                    </div>
                    
                    <div class="bulk-actions">
                        <button type="button" class="btn btn-outline" onclick="removeSelected()">Remove Selected</button>
                        <button type="button" class="btn btn-secondary" onclick="clearCart()">Clear All</button>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-cart-id="<?= $item->cart_id ?>">
                            <!-- Item Selection Checkbox -->
                            <div class="item-select">
                                <label class="item-checkbox">
                                    <input type="checkbox" name="selected_items[]" value="<?= $item->cart_id ?>" class="item-check" onchange="updateSelectedTotal()">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            
                            <!-- Item Image -->
                            <div class="item-image">
                                <img src="../../images/Products/<?= $item->photo ?>" alt="<?= encode($item->name) ?>">
                            </div>
                            
                            <!-- Item Details -->
                            <div class="item-details">
                                <h3><?= encode($item->name) ?></h3>
                                <p class="brand">Brand: <?= encode($item->brand) ?></p>
                                <?php if ($item->size): ?>
                                    <p class="size">Size: <?= format_size($item->size) ?></p>
                                <?php endif; ?>
                                <p class="price">RM<?= number_format($item->price, 2) ?></p>
                            </div>
                            
                            <!-- Quantity Controls -->
                            <div class="item-quantity">
                                <button type="button" class="qty-btn" onclick="updateQuantity(<?= $item->cart_id ?>, <?= $item->quantity - 1 ?>)">-</button>
                                <span class="quantity"><?= $item->quantity ?></span>
                                <button type="button" class="qty-btn" onclick="updateQuantity(<?= $item->cart_id ?>, <?= $item->quantity + 1 ?>)">+</button>
                            </div>
                            
                            <!-- Item Total -->
                            <div class="item-total">
                                <span class="item-total-price" data-price="<?= $item->price * $item->quantity ?>">
                                    RM<?= number_format($item->price * $item->quantity, 2) ?>
                                </span>
                            </div>
                            
                            <!-- Remove Button -->
                            <div class="item-actions">
                                <button type="button" class="remove-btn" onclick="removeFromCart(<?= $item->cart_id ?>)" title="Remove item">🗑️</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Total Items:</span>
                        <span id="total-items"><?= count($cart_items) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Selected Items:</span>
                        <span id="selected-count">0</span>
                    </div>
                    <div class="summary-row">
                        <span>Cart Total:</span>
                        <span>RM<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Selected Total:</span>
                        <span id="selected-total">RM0.00</span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="cart-actions">
                        <button type="submit" class="btn btn-primary checkout-btn" id="checkout-selected" disabled>
                            Checkout Selected Items
                        </button>
                        <a href="checkout.php" class="btn btn-outline">Checkout All</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<style>
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.cart-header h1 {
    color: #2c3e50;
    margin: 0;
}

.cart-search {
    flex: 1;
    max-width: 400px;
}

.search-form {
    width: 100%;
}

.search-input-group {
    display: flex;
    position: relative;
    width: 100%;
}

.search-input {
    flex: 1;
    padding: 10px 45px 10px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 25px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s;
}

.search-input:focus {
    border-color: #3498db;
}

.search-btn {
    position: absolute;
    right: 35px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #666;
}

.clear-search {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: #e74c3c;
    color: white;
    border: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.empty-cart {
    text-align: center;
    padding: 50px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-cart p {
    font-size: 18px;
    color: #666;
    margin-bottom: 20px;
}

.cart-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-wrap: wrap;
    gap: 15px;
}

.select-controls {
    display: flex;
    align-items: center;
}

.select-all-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
    color: #2c3e50;
}

.bulk-actions {
    display: flex;
    gap: 10px;
}

.cart-items {
    margin-bottom: 30px;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.cart-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.item-select {
    margin-right: 15px;
}

.item-checkbox,
.select-all-label {
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.item-checkbox input[type="checkbox"],
.select-all-label input[type="checkbox"] {
    opacity: 0;
    position: absolute;
    width: 0;
    height: 0;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #3498db;
    border-radius: 3px;
    background: white;
    position: relative;
    transition: all 0.3s;
}

.item-checkbox input[type="checkbox"]:checked + .checkmark,
.select-all-label input[type="checkbox"]:checked + .checkmark {
    background: #3498db;
    border-color: #3498db;
}

.item-checkbox input[type="checkbox"]:checked + .checkmark::after,
.select-all-label input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    color: white;
    font-size: 12px;
    font-weight: bold;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.item-image {
    width: 80px;
    height: 80px;
    margin-right: 20px;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.item-details {
    flex: 1;
    margin-right: 20px;
}

.item-details h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #2c3e50;
    font-weight: 600;
}

.item-details .brand {
    color: #666;
    margin: 4px 0;
    font-size: 14px;
}

.item-details .size {
    color: #666;
    margin: 4px 0;
    font-size: 14px;
}

.item-details .price {
    font-weight: bold;
    color: #e74c3c;
    margin: 4px 0;
    font-size: 15px;
}

.item-quantity {
    display: flex;
    align-items: center;
    margin-right: 20px;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 5px;
}

.qty-btn {
    background: #3498db;
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background 0.3s;
}

.qty-btn:hover {
    background: #2980b9;
}

.quantity {
    margin: 0 15px;
    font-weight: bold;
    min-width: 25px;
    text-align: center;
    color: #2c3e50;
}

.item-total {
    font-weight: bold;
    font-size: 16px;
    margin-right: 20px;
    min-width: 90px;
    text-align: right;
    color: #2c3e50;
}

.item-actions {
    margin-left: 10px;
}

.remove-btn {
    background: #e74c3c;
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-btn:hover {
    background: #c0392b;
}

.cart-summary {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-top: 4px solid #3498db;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.summary-row.total-row {
    border-top: 2px solid #e1e8ed;
    margin-top: 10px;
    padding-top: 15px;
    font-weight: bold;
    font-size: 18px;
    color: #2c3e50;
}

.cart-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    text-align: center;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-primary:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.checkout-btn {
    font-size: 16px;
    padding: 15px 30px;
    border-radius: 8px;
}

@media (max-width: 992px) {
    .cart-item {
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .item-details {
        flex: 1 1 100%;
        margin-right: 0;
        order: 2;
    }
    
    .item-quantity,
    .item-total {
        margin-right: 0;
    }
}

@media (max-width: 768px) {
    .cart-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .cart-search {
        max-width: none;
    }
    
    .cart-controls {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .bulk-actions {
        justify-content: center;
    }
    
    .cart-item {
        padding: 15px;
        text-align: center;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
        margin: 0 auto 10px;
    }
    
    .item-details,
    .item-quantity,
    .item-total,
    .item-actions {
        margin: 10px 0;
    }
    
    .cart-actions {
        flex-direction: column;
    }
    
    .summary-row {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .cart-container {
        padding: 10px;
    }
    
    .cart-item {
        padding: 10px;
    }
    
    .btn {
        padding: 10px 16px;
        font-size: 13px;
    }
}
</style>

<script>
// Global variables
let selectedItems = new Set();

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-check');
    
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        if (selectAllCheckbox.checked) {
            selectedItems.add(checkbox.value);
        } else {
            selectedItems.delete(checkbox.value);
        }
    });
    
    updateSelectedTotal();
}

function updateSelectedTotal() {
    const itemCheckboxes = document.querySelectorAll('.item-check:checked');
    const selectAllCheckbox = document.getElementById('select-all');
    const selectedCountEl = document.getElementById('selected-count');
    const selectedTotalEl = document.getElementById('selected-total');
    const checkoutBtn = document.getElementById('checkout-selected');
    
    let total = 0;
    let selectedCount = 0;
    
    selectedItems.clear();
    
    itemCheckboxes.forEach(checkbox => {
        selectedItems.add(checkbox.value);
        selectedCount++;
        
        const cartItem = checkbox.closest('.cart-item');
        const priceEl = cartItem.querySelector('.item-total-price');
        const price = parseFloat(priceEl.getAttribute('data-price'));
        total += price;
    });
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.item-check');
    selectAllCheckbox.checked = selectedCount === allCheckboxes.length;
    selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < allCheckboxes.length;
    
    // Update display
    selectedCountEl.textContent = selectedCount;
    selectedTotalEl.textContent = 'RM' + total.toFixed(2);
    
    // Enable/disable checkout button
    checkoutBtn.disabled = selectedCount === 0;
}

function updateQuantity(cartId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(cartId);
        return;
    }
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('quantity', newQuantity);
    formData.append('action', 'update_quantity');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS') {
            location.reload(); // Refresh page to show updated quantities and totals
        } else {
            alert(parts.slice(1).join(':') || 'Failed to update quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update quantity');
    });
}

function removeFromCart(cartId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('action', 'remove_from_cart');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS') {
            location.reload(); // Refresh page to show flash message
        } else {
            alert(parts.slice(1).join(':') || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove item');
    });
}

function removeSelected() {
    const selectedCheckboxes = document.querySelectorAll('.item-check:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select items to remove');
        return;
    }
    
    if (!confirm(`Are you sure you want to remove ${selectedCheckboxes.length} selected item(s)?`)) {
        return;
    }
    
    const cartIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    const formData = new FormData();
    formData.append('action', 'remove_multiple');
    formData.append('cart_ids', cartIds.join(','));  // Use comma-separated string instead of JSON
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS') {
            location.reload();
        } else {
            alert(parts.slice(1).join(':') || 'Failed to remove items');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove items');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'clear_cart');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS') {
            location.reload(); // Refresh page to show flash message
        } else {
            alert(parts.slice(1).join(':') || 'Failed to clear cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to clear cart');
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to item checkboxes
    const itemCheckboxes = document.querySelectorAll('.item-check');
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedTotal);
    });
    
    // Initial update
    updateSelectedTotal();
});
</script>

<?php
include '../../foot.php';
?>
