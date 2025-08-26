<?php
require '../../base.php';

// Redirect to login if not logged in
if (!$_user) {
    header('Location: ../user/login.php');
    exit;
}

// Get cart items for current user
$cart_items = cart_get_items();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item->price * $item->quantity;
}

include '../../head.php';
?>

<main>
    <div class="cart-container">
        <h1>Shopping Cart</h1>
        
        <?php if (count($cart_items) == 0): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="../categories/shop.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-cart-id="<?= $item->cart_id ?>">
                        <div class="item-image">
                            <img src="../../images/Products/<?= $item->photo ?>" alt="<?= $item->name ?>">
                        </div>
                        <div class="item-details">
                            <h3><?= $item->name ?></h3>
                            <?php if ($item->size): ?>
                                <p class="size">Size: <?= format_size($item->size) ?></p>
                            <?php endif; ?>
                            <p class="price">$<?= number_format($item->price, 2) ?></p>
                        </div>
                        <div class="item-quantity">
                            <button class="qty-btn" onclick="updateQuantity(<?= $item->cart_id ?>, <?= $item->quantity - 1 ?>)">-</button>
                            <span class="quantity"><?= $item->quantity ?></span>
                            <button class="qty-btn" onclick="updateQuantity(<?= $item->cart_id ?>, <?= $item->quantity + 1 ?>)">+</button>
                        </div>
                        <div class="item-total">
                            $<?= number_format($item->price * $item->quantity, 2) ?>
                        </div>
                        <div class="item-actions">
                            <button class="remove-btn" onclick="removeFromCart(<?= $item->cart_id ?>)">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="total">
                    <h3>Total: $<?= number_format($total, 2) ?></h3>
                </div>
                <div class="cart-actions">
                    <button class="btn btn-secondary" onclick="clearCart()">Clear Cart</button>
                    <button class="btn btn-primary">Checkout</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.cart-container h1 {
    text-align: center;
    margin-bottom: 30px;
}

.empty-cart {
    text-align: center;
    padding: 50px;
}

.empty-cart p {
    font-size: 18px;
    color: #666;
    margin-bottom: 20px;
}

.cart-items {
    margin-bottom: 30px;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 20px;
    border: 1px solid #ddd;
    margin-bottom: 15px;
    border-radius: 8px;
    background: #f9f9f9;
}

.item-image {
    width: 100px;
    height: 100px;
    margin-right: 20px;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.item-details {
    flex: 1;
    margin-right: 20px;
}

.item-details h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.item-details .size {
    color: #666;
    margin: 5px 0;
}

.item-details .price {
    font-weight: bold;
    color: #2c3e50;
}

.item-quantity {
    display: flex;
    align-items: center;
    margin-right: 20px;
}

.qty-btn {
    background: #3498db;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.qty-btn:hover {
    background: #2980b9;
}

.quantity {
    margin: 0 15px;
    font-weight: bold;
    min-width: 20px;
    text-align: center;
}

.item-total {
    font-weight: bold;
    font-size: 18px;
    margin-right: 20px;
    min-width: 80px;
    text-align: right;
}

.item-actions {
    margin-left: 20px;
}

.remove-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}

.remove-btn:hover {
    background: #c0392b;
}

.cart-summary {
    text-align: right;
    padding: 20px;
    border-top: 2px solid #3498db;
}

.total {
    margin-bottom: 20px;
}

.total h3 {
    color: #2c3e50;
    font-size: 24px;
}

.cart-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        text-align: center;
    }
    
    .item-image {
        margin: 0 0 15px 0;
    }
    
    .item-details, .item-quantity, .item-total, .item-actions {
        margin: 10px 0;
    }
    
    .cart-actions {
        flex-direction: column;
    }
}
</style>

<script>
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh page to show updated quantities and totals
        } else {
            alert(data.message || 'Failed to update quantity');
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh page to show flash message
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove item');
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh page to show flash message
        } else {
            alert(data.message || 'Failed to clear cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to clear cart');
    });
}
</script>

<?php
include '../../foot.php';
?>
