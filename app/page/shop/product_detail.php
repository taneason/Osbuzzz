<?php
require '../../base.php';

// Get product ID from URL
$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: shop.php');
    exit;
}

// Get product details
$stm = $_db->prepare('
    SELECT p.*, 
           c.category_name,
           c.category_slug,
           GROUP_CONCAT(DISTINCT pv.size ORDER BY pv.size) as available_sizes,
           SUM(pv.stock) as total_stock
    FROM product p 
    LEFT JOIN category c ON p.category_id = c.category_id
    LEFT JOIN product_variants pv ON p.product_id = pv.product_id 
    WHERE p.product_id = ? AND p.status = \'active\'
    GROUP BY p.product_id
');
$stm->execute([$product_id]);
$product = $stm->fetch();

if (!$product) {
    header('Location: shop.php');
    exit;
}

// Get product photos
$stm = $_db->prepare('
    SELECT photo_filename 
    FROM product_photos 
    WHERE product_id = ? 
    ORDER BY is_main_photo DESC, display_order ASC
');
$stm->execute([$product_id]);
$product_photos = $stm->fetchAll();

// If no photos in product_photos table, use main photo from product table
if (empty($product_photos) && $product->photo) {
    $product_photos = [(object)['photo_filename' => $product->photo]];
}

// Add default photo if no photos at all
if (empty($product_photos)) {
    $product_photos = [(object)['photo_filename' => 'defaultProduct.png']];
}

include '../../head.php';
?>

<main>
    <div class="product-detail-container">
        <!-- Product Images -->
        <div class="product-images">
            <div class="main-image-container">
                <img src="../../images/Products/<?= $product_photos[0]->photo_filename ?>" 
                     alt="<?= htmlspecialchars($product->product_name) ?>" 
                     class="main-image" 
                     id="mainImage"
                     onclick="openImageModal(this.src, '<?= htmlspecialchars($product->product_name) ?>')">
            </div>
            
            <?php if (count($product_photos) > 1): ?>
            <div class="thumbnail-images">
                <?php foreach ($product_photos as $index => $photo): ?>
                <img src="../../images/Products/<?= $photo->photo_filename ?>" 
                     alt="Product photo <?= $index + 1 ?>" 
                     class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                     onclick="changeMainImage(this, <?= $index ?>)">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <div class="breadcrumb">
                <a href="shop.php">Shop</a> > 
                <?php if ($product->category_slug): ?>
                <a href="../categories/category.php?slug=<?= htmlspecialchars($product->category_slug) ?>"><?= htmlspecialchars($product->category_name) ?></a> > 
                <?php endif; ?>
                <?= htmlspecialchars($product->product_name) ?>
            </div>

            <div class="product-brand"><?= htmlspecialchars($product->brand) ?></div>
            <h1 class="product-title"><?= htmlspecialchars($product->product_name) ?></h1>
            <div class="product-price">RM <?= number_format($product->price, 2) ?></div>

            <?php if ($product->description): ?>
            <div class="product-description">
                <?= nl2br(htmlspecialchars($product->description)) ?>
            </div>
            <?php endif; ?>

            <div class="product-options">
                <?php if ($product->available_sizes): ?>
                <div class="option-group">
                    <label class="option-label">Size:</label>
                    <div class="size-options">
                        <?php foreach (explode(',', $product->available_sizes) as $size): ?>
                        <div class="size-option" onclick="selectOption(this, 'size')" data-size="<?= trim($size) ?>">
                            <?= format_size(trim($size)) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="quantity-selector">
                <label class="option-label">Quantity:</label>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                    <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="<?= $product->total_stock ?>">
                    <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                </div>
                <div class="stock-info" id="stockInfo"><?= $product->total_stock ?> in stock</div>
            </div>

            <div class="action-buttons">
                <button class="btn-primary" onclick="addToCart()">Add to Cart</button>
                <button class="btn-secondary" onclick="buyNow()">Buy Now</button>
            </div>

            <div class="product-features">
                <div class="feature-item">
                    <span>✓</span> 30-day return policy
                </div>
                <div class="feature-item">
                    <span>✓</span> Authentic products guaranteed
                </div>
                <div class="feature-item">
                    <span>✓</span> Customer support 24/7
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>
</main>

<script>
let selectedSize = null;
let currentStock = <?= $product->total_stock ?>;
let cartQuantity = 0;

// Load initial cart quantity
window.addEventListener('load', function() {
    updateCartQuantityDisplay();
});

function changeMainImage(thumbnail, index) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = thumbnail.src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
}

function selectOption(element, type) {
    // Remove active class from siblings
    element.parentNode.querySelectorAll(`.${type}-option`).forEach(opt => opt.classList.remove('selected'));
    // Add active class to clicked element
    element.classList.add('selected');
    
    if (type === 'size') {
        selectedSize = element.dataset.size || element.textContent.trim().replace('EU ', '');
        updateStockInfo();
    }
}

function updateStockInfo() {
    if (selectedSize) {
        // Get stock for selected size
        fetch(`stock_api.php?action=get_size_stock&product_id=<?= $product->product_id ?>&size=${selectedSize}`)
        .then(response => response.text())
        .then(text => {
            // Parse response format: SUCCESS:stock:cart_quantity:available_stock or ERROR:message
            const parts = text.trim().split(':');
            
            if (parts[0] === 'SUCCESS' && parts.length >= 4) {
                const stock = parseInt(parts[1]);
                cartQuantity = parseInt(parts[2]);
                currentStock = parseInt(parts[3]);
                
                const stockInfo = document.getElementById('stockInfo');
                const quantityInput = document.getElementById('quantity');
                
                if (currentStock <= 0) {
                    stockInfo.innerHTML = '<span style="color: red;">Out of stock</span>';
                    if (cartQuantity > 0) {
                        stockInfo.innerHTML += `<br><small>You have ${cartQuantity} in cart</small>`;
                    }
                    quantityInput.max = 0;
                    quantityInput.value = 0;
                } else {
                    stockInfo.innerHTML = `${stock} total in stock`;
                    if (cartQuantity > 0) {
                        stockInfo.innerHTML += `<br><small>You have ${cartQuantity} in cart (${currentStock} available to add)</small>`;
                    }
                    quantityInput.max = currentStock;
                    quantityInput.value = Math.min(parseInt(quantityInput.value) || 1, currentStock);
                }
                
                updateAddToCartButton();
            } else {
                console.error('Error fetching stock info:', text);
            }
        })
        .catch(error => {
            console.error('Error fetching stock info:', error);
        });
    } else {
        // Reset to total stock
        fetch(`stock_api.php?action=get_total_stock&product_id=<?= $product->product_id ?>`)
        .then(response => response.text())
        .then(text => {
            // Parse response format: SUCCESS:total_stock:cart_quantity:available_stock or ERROR:message
            const parts = text.trim().split(':');
            
            if (parts[0] === 'SUCCESS' && parts.length >= 4) {
                const totalStock = parseInt(parts[1]);
                cartQuantity = parseInt(parts[2]);
                currentStock = parseInt(parts[3]);
                
                const stockInfo = document.getElementById('stockInfo');
                const quantityInput = document.getElementById('quantity');
                
                if (currentStock <= 0) {
                    stockInfo.innerHTML = '<span style="color: red;">Out of stock</span>';
                    if (cartQuantity > 0) {
                        stockInfo.innerHTML += `<br><small>You have ${cartQuantity} in cart</small>`;
                    }
                    quantityInput.max = 0;
                    quantityInput.value = 0;
                } else {
                    stockInfo.innerHTML = `${totalStock} total in stock`;
                    if (cartQuantity > 0) {
                        stockInfo.innerHTML += `<br><small>You have ${cartQuantity} in cart (${currentStock} available to add)</small>`;
                    }
                    quantityInput.max = currentStock;
                    quantityInput.value = Math.min(parseInt(quantityInput.value) || 1, currentStock);
                }
                
                updateAddToCartButton();
            } else {
                console.error('Error fetching stock info:', text);
            }
        })
        .catch(error => {
            console.error('Error fetching stock info:', error);
        });
    }
}

function updateCartQuantityDisplay() {
    updateStockInfo();
}

function updateAddToCartButton() {
    const addToCartBtn = document.querySelector('.btn-primary');
    const buyNowBtn = document.querySelector('.btn-secondary');
    
    if (currentStock <= 0) {
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Out of Stock';
        addToCartBtn.style.background = '#ccc';
        addToCartBtn.style.cursor = 'not-allowed';
        
        buyNowBtn.disabled = true;
        buyNowBtn.style.background = '#ccc';
        buyNowBtn.style.cursor = 'not-allowed';
    } else {
        addToCartBtn.disabled = false;
        addToCartBtn.textContent = 'Add to Cart';
        addToCartBtn.style.background = '#007cba';
        addToCartBtn.style.cursor = 'pointer';
        
        buyNowBtn.disabled = false;
        buyNowBtn.style.background = '#f5f5f5';
        buyNowBtn.style.cursor = 'pointer';
    }
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value) || 0;
    const maxStock = parseInt(quantityInput.max) || 0;
    
    const newValue = Math.max(1, Math.min(maxStock, currentValue + delta));
    quantityInput.value = newValue;
}

function addToCart() {
    // Check if user is logged in
    <?php if (!$_user): ?>
    alert('Please login to add items to cart');
    window.location.href = '../user/login.php';
    return;
    <?php endif; ?>
    
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    
    // Basic validation
    if (!selectedSize && document.querySelector('.size-option')) {
        alert('Please select a size');
        return;
    }
    
    if (quantity <= 0) {
        alert('Please select a valid quantity');
        return;
    }
    
    if (quantity > currentStock) {
        alert(`Only ${currentStock} item(s) available`);
        return;
    }
    
    // Disable button during request
    const addToCartBtn = document.querySelector('.btn-primary');
    const originalText = addToCartBtn.textContent;
    addToCartBtn.disabled = true;
    addToCartBtn.textContent = 'Adding...';
    
    // Send data to add to cart
    const formData = new FormData();
    formData.append('product_id', <?= $product->product_id ?>);
    formData.append('quantity', quantity);
    formData.append('size', selectedSize || '');
    formData.append('action', 'add_to_cart');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS') {
            // Update stock info and refresh page to show flash message
            updateStockInfo();
            window.location.reload();
        } else {
            alert(parts.slice(1).join(':') || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add item to cart');
    })
    .finally(() => {
        // Re-enable button
        addToCartBtn.disabled = false;
        addToCartBtn.textContent = originalText;
    });
}

function buyNow() {
    // Check if user is logged in
    <?php if (!$_user): ?>
    alert('Please login to purchase items');
    window.location.href = '../user/login.php';
    return;
    <?php endif; ?>
    
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    
    // Basic validation
    if (!selectedSize && document.querySelector('.size-option')) {
        alert('Please select a size');
        return;
    }
    
    if (quantity <= 0) {
        alert('Please select a valid quantity');
        return;
    }
    
    if (quantity > currentStock) {
        alert(`Only ${currentStock} item(s) available`);
        return;
    }
    
    // Direct purchase - clear cart and add item then go to checkout
    const formData = new FormData();
    formData.append('product_id', <?= $product->product_id ?>);
    formData.append('quantity', quantity);
    formData.append('size', selectedSize || '');
    formData.append('action', 'buy_now');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Response text:', text);
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS' && parts.length >= 2) {
            // parts[1] should contain redirect URL
            const redirectUrl = parts[1];
            window.location.href = redirectUrl || 'checkout.php';
        } else {
            alert('Error: ' + parts.slice(1).join(':') || 'Failed to process purchase');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Network error: Failed to process purchase');
    });
}

function updateCartCount() {
    fetch('cart_handler.php?action=get_count')
    .then(response => response.text())
    .then(text => {
        const parts = text.trim().split(':');
        if (parts[0] === 'SUCCESS' && parts.length >= 2) {
            const count = parseInt(parts[1]) || 0;
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = count;
            }
        }
    })
    .catch(error => console.error('Error updating cart count:', error));
}



// Update quantity input validation
document.getElementById('quantity').addEventListener('input', function() {
    const value = parseInt(this.value) || 0;
    const max = parseInt(this.max) || 0;
    
    if (value < 1) this.value = 1;
    if (value > max) this.value = max;
});
</script>

<?php
include '../../foot.php';
?>
