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
           GROUP_CONCAT(DISTINCT pv.size ORDER BY pv.size) as available_sizes,
           SUM(pv.stock) as total_stock
    FROM product p 
    LEFT JOIN product_variants pv ON p.product_id = pv.product_id 
    WHERE p.product_id = ?
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

<style>
.product-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 40px;
}

.product-images {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.main-image-container {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.main-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
    cursor: zoom-in;
}

.thumbnail-images {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: #007cba;
    transform: scale(1.05);
}

.product-info {
    padding: 20px 0;
}

.breadcrumb {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #007cba;
    text-decoration: none;
}

.product-brand {
    color: #666;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.product-title {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    line-height: 1.2;
}

.product-price {
    font-size: 28px;
    font-weight: bold;
    color: #007cba;
    margin-bottom: 20px;
}

.product-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 30px;
    font-size: 16px;
}

.product-options {
    margin-bottom: 30px;
}

.option-group {
    margin-bottom: 20px;
}

.option-label {
    display: block;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.size-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.size-option {
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.size-option:hover,
.size-option.selected {
    border-color: #007cba;
    background: #007cba;
    color: white;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
}

.quantity-btn {
    background: #f5f5f5;
    border: none;
    width: 40px;
    height: 40px;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.3s ease;
}

.quantity-btn:hover {
    background: #e0e0e0;
}

.quantity-input {
    border: none;
    width: 60px;
    height: 40px;
    text-align: center;
    font-size: 16px;
}

.stock-info {
    color: #666;
    font-size: 14px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.btn-primary,
.btn-secondary {
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
}

.btn-primary {
    background: #007cba;
    color: white;
}

.btn-primary:hover {
    background: #005a8a;
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
    border: 2px solid #e0e0e0;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.product-features {
    border-top: 1px solid #e0e0e0;
    padding-top: 20px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    color: #666;
}

/* Image Modal */
.image-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
}

.image-modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.image-modal-close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1001;
    transition: color 0.3s;
}

.image-modal-close:hover {
    color: #ccc;
}

@media (max-width: 768px) {
    .product-detail-container {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 15px;
    }
    
    .main-image {
        height: 300px;
    }
    
    .product-title {
        font-size: 24px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

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
                <a href="<?= strtolower($product->category) ?>.php"><?= htmlspecialchars($product->category) ?></a> > 
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
                    <span>✓</span> Free shipping on orders over RM 200
                </div>
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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentStock = data.available_stock;
                cartQuantity = data.cart_quantity;
                
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
                    stockInfo.innerHTML = `${data.stock} total in stock`;
                    if (cartQuantity > 0) {
                        stockInfo.innerHTML += `<br><small>You have ${cartQuantity} in cart (${currentStock} available to add)</small>`;
                    }
                    quantityInput.max = currentStock;
                    quantityInput.value = Math.min(parseInt(quantityInput.value) || 1, currentStock);
                }
                
                updateAddToCartButton();
            }
        })
        .catch(error => {
            console.error('Error fetching stock info:', error);
        });
    } else {
        // Reset to total stock
        fetch(`stock_api.php?action=get_total_stock&product_id=<?= $product->product_id ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentStock = data.available_stock;
                cartQuantity = data.cart_quantity;
                
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
                    stockInfo.innerHTML = `${data.total_stock} total in stock`;
                    if (cartQuantity > 0) {
                        stockInfo.innerHTML += `<br><small>You have ${cartQuantity} in cart (${currentStock} available to add)</small>`;
                    }
                    quantityInput.max = currentStock;
                    quantityInput.value = Math.min(parseInt(quantityInput.value) || 1, currentStock);
                }
                
                updateAddToCartButton();
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update stock info and refresh page to show flash message
            updateStockInfo();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to add item to cart');
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
    
    // Add to cart first, then redirect to cart
    const formData = new FormData();
    formData.append('product_id', <?= $product->product_id ?>);
    formData.append('quantity', quantity);
    formData.append('size', selectedSize || '');
    formData.append('action', 'add_to_cart');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to cart page
            window.location.href = 'cart.php';
        } else {
            alert(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add item to cart');
    });
}

function updateCartCount() {
    fetch('cart_handler.php?action=get_count')
    .then(response => response.json())
    .then(data => {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount && data.count !== undefined) {
            cartCount.textContent = data.count;
        }
    })
    .catch(error => console.error('Error updating cart count:', error));
}

function openImageModal(imageSrc, productName) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.style.display = 'block';
    modalImg.src = imageSrc;
    
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC key to close modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

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
