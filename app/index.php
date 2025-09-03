<?php
require 'base.php';

include 'head.php';

// Fetch categories from database
$categories = [];
if (isset($_db)) {
    $stmt = $_db->prepare("SELECT * FROM category ORDER BY category_name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch top 5 best selling products
$top_products = [];
if (isset($_db)) {
    $stmt = $_db->prepare("
        SELECT 
            p.product_id,
            p.product_name,
            p.brand,
            p.price,
            p.photo,
            COALESCE(SUM(oi.quantity), 0) as total_sold
        FROM product p
        LEFT JOIN order_items oi ON p.product_id = oi.product_id
        WHERE p.status = 'active'
        GROUP BY p.product_id, p.product_name, p.brand, p.price, p.photo
        ORDER BY total_sold DESC, p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Debug: Uncomment the line below to check if categories are loaded
//echo "<pre>Categories: " . print_r($categories, true) . "</pre>";
?>

<?php if (!$_user ||$_user->role != 'Admin'): ?>
<main>
    <section class="hero">
        <!-- 多图轮播背景 -->
        <div class="hero-background"></div>
        
        <!-- 居中文字内容 - 完全保留您的原始结构 -->
        <div class="hero-content">
            <h1>New Season Collection</h1>
            <p>Discover the latest trends in fashion</p>
            <a href="/page/shop/sales.php" class="cta-button">Shop Now</a>
        </div>
    </section>

    <section class="shop-by-category-section">
        <h2 class="section-title">🛍️ Shop by Category</h2>
        <div class="featured-categories">
            <?php if (empty($categories)): ?>
                <p>No categories found. Database categories count: <?php echo count($categories); ?></p>
                <p>Database connection status: <?php echo isset($_db) ? 'Connected' : 'Not connected'; ?></p>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                <div class="category-card" onclick="location.href='/page/categories/category.php?id=<?php echo $category['category_id']; ?>'">
                    <?php 
                    $banner_src = !empty($category['banner_image']) 
                        ? '/images/banners/' . $category['banner_image']
                        : '/images/default-category.jpg';
                    ?>
                    <img src="<?php echo htmlspecialchars($banner_src); ?>" alt="<?php echo htmlspecialchars($category['category_name']); ?>">
                    <div class="category-overlay">
                        <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                        <?php if (!empty($category['description'])): ?>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Top 5 Sales Products Section -->
    <section class="top-products-section">
        <h2 class="section-title">🔥 Top 5 Best Sellers</h2>
        <div class="top-products-grid">
            <?php if (empty($top_products)): ?>
                <div class="no-products">
                    <p>No products available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($top_products as $product): ?>
                <div class="product-card" onclick="location.href='/page/shop/product_detail.php?id=<?php echo $product['product_id']; ?>'">
                    <div class="product-image">
                        <?php 
                        $product_image = !empty($product['photo']) 
                            ? '/images/Products/' . $product['photo']
                            : '/images/Products/defaultProduct.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($product_image); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="product-badge">
                            <span class="sold-count"><?php echo $product['total_sold']; ?> sold</span>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></p>
                        <div class="product-price">
                            <span class="price">RM<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <button class="quick-view-btn">View Details</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php else: ?>
<main>
    <h1 class="admin-title">Welcome to Admin Dashboard</h1>
    <div class="admin-dashboard">
        <div class="admin-card">
            <a href="/page/admin/admin_user.php">User Management</a>
        </div>
        <div class="admin-card">
            <a href="/page/admin/admin_product.php">Product Management</a>
        </div>
    </div>
</main>
<?php endif ?>  
<?php
include 'foot.php';
