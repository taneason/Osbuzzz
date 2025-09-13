<?php
require 'base.php';

// If current user is an admin, redirect to the admin dashboard before any output is sent
if (isset($_user) && $_user && isset($_user->role) && $_user->role === 'Admin') {
    header('Location: /page/admin/index.php');
    exit;
}

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

    <?php if ($_user): ?>
    <!-- Loyalty Points Section -->
    <section style="background: linear-gradient(135deg, #007cba, #0056b3); color: white; padding: 40px 20px; margin: 40px 0; text-align: center; border-radius: 10px;">
        <h2 style="margin-bottom: 15px;">🎁 Your Loyalty Rewards</h2>
        <div style="display: flex; justify-content: center; align-items: center; gap: 30px; flex-wrap: wrap;">
            <div>
                <h3 style="font-size: 2.5em; margin: 0; color: #ffd700;"><?= $_user->loyalty_points ?? 0 ?></h3>
                <p style="margin: 5px 0;">Points Available</p>
            </div>
            <div style="text-align: left;">
                <p style="margin: 5px 0;">✨ Earn points with every purchase</p>
                <p style="margin: 5px 0;">🎯 Redeem for discounts</p>
                <p style="margin: 5px 0;">🎁 Welcome bonus received!</p>
            </div>
            <div>
                <a href="/page/user/loyalty_history.php" style="background: #ffd700; color: #007cba; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">View History</a>
            </div>
        </div>
    </section>
    <?php else: ?>
    <!-- Sign Up for Loyalty -->
    <section style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 40px 20px; margin: 40px 0; text-align: center; border-radius: 10px;">
        <h2 style="margin-bottom: 15px;">🎁 Join Our Loyalty Program</h2>
        <p style="font-size: 1.1em; margin-bottom: 20px;">Sign up now and get <strong><?= get_loyalty_setting('signup_bonus_points', 100) ?> bonus points</strong> to start shopping!</p>
        <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="/page/user/signup.php" style="background: #ffd700; color: #28a745; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Sign Up Now</a>
            <a href="/page/user/login.php" style="background: rgba(255,255,255,0.2); color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Login</a>
        </div>
    </section>
    <?php endif; ?>

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
    <!-- Admin users are redirected to the admin dashboard before rendering the page -->
<?php endif ?>  
<?php
include 'foot.php';
