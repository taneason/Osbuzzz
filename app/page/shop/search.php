<?php
require '../../base.php';

$search_query = trim($_GET['q'] ?? '');

if ($search_query) {
    // Search products
    $stm = $_db->prepare('
        SELECT p.*, 
               pp.photo_filename as main_photo,
               SUM(pv.stock) as total_stock
        FROM product p 
        LEFT JOIN product_photos pp ON p.product_id = pp.product_id AND pp.is_main_photo = 1
        LEFT JOIN product_variants pv ON p.product_id = pv.product_id 
        WHERE p.product_name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR p.category LIKE ?
        GROUP BY p.product_id
        ORDER BY p.created_at DESC
    ');
    
    $search_term = "%$search_query%";
    $stm->execute([$search_term, $search_term, $search_term, $search_term]);
    $products = $stm->fetchAll();
} else {
    $products = [];
}

include '../../head.php';
?>

<main style="padding: 20px;">
    <div class="search-results-container" style="max-width: 1200px; margin: 0 auto;">
        <div class="search-header" style="margin-bottom: 30px;">
            <h2>Search Results</h2>
            <?php if ($search_query): ?>
                <p style="color: #666; margin: 10px 0;">
                    <?= count($products) ?> result(s) found for "<strong><?= htmlspecialchars($search_query) ?></strong>"
                </p>
            <?php else: ?>
                <p style="color: #666; margin: 10px 0;">Please enter a search term.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <a href="product_detail.php?id=<?= $product->product_id ?>" class="product-card-link">
                <div class="product-card">
                    <div class="product-image">
                        <?php 
                        $photo_src = $product->main_photo ?: ($product->photo ?: 'defaultProduct.png');
                        ?>
                        <img src="../../images/Products/<?= $photo_src ?>" alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
                    </div>
                    <div class="product-info">
                        <span class="brand"><?= htmlspecialchars($product->brand) ?></span>
                        <h3><?= htmlspecialchars($product->product_name) ?></h3>
                        <div class="category-tag" style="font-size: 12px; color: #666; margin: 5px 0;">
                            <?= htmlspecialchars($product->category) ?>
                        </div>
                        <div class="price">RM <?= number_format($product->price, 2) ?></div>
                        <button class="shop" onclick="event.preventDefault(); window.location.href='product_detail.php?id=<?= $product->product_id ?>'">Shop</button>
                    </div>  
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <?php elseif ($search_query): ?>
        <div class="no-results" style="text-align: center; padding: 60px 20px; color: #666;">
            <h3>No products found</h3>
            <p>Try adjusting your search terms or browse our categories:</p>
            <div style="margin-top: 20px;">
                <a href="../categories/running.php" style="margin: 0 10px; color: #007cba;">Running</a>
                <a href="../categories/casual.php" style="margin: 0 10px; color: #007cba;">Casual</a>
                <a href="../categories/formal.php" style="margin: 0 10px; color: #007cba;">Formal</a>
                <a href="../categories/basketball.php" style="margin: 0 10px; color: #007cba;">Basketball</a>
                <a href="../categories/other.php" style="margin: 0 10px; color: #007cba;">Other</a>
            </div>
        </div>
        
        <?php else: ?>
        <div class="search-suggestions" style="text-align: center; padding: 60px 20px; color: #666;">
            <h3>What are you looking for?</h3>
            <p>Search for products by name, brand, or category.</p>
            <div style="margin-top: 30px;">
                <h4>Popular Categories:</h4>
                <div style="margin-top: 15px;">
                    <a href="../categories/running.php" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Running</a>
                    <a href="../categories/casual.php" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Casual</a>
                    <a href="../categories/formal.php" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Formal</a>
                    <a href="../categories/basketball.php" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Basketball</a>
                    <a href="../categories/other.php" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Other</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<style>
.category-suggestion:hover {
    background: #007cba !important;
    color: white !important;
}
</style>

<?php
include '../../foot.php';
?>
