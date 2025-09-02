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
        <h2 class="section-title">Shop by Category</h2>
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
