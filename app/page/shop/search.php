<?php
require '../../base.php';

$search_query = trim($_GET['q'] ?? '');

// Pagination setup
$page = (int)($_GET['page'] ?? 1);
$limit = 12; // 12 products per page
$offset = ($page - 1) * $limit;
if ($page < 1) $page = 1;

// Sorting and filtering
$sort = $_GET['sort'] ?? 'newest';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$category_filter = (int)($_GET['category'] ?? 0);

$products = [];
$totalProducts = 0;
$totalPages = 0;

if ($search_query) {
    // Build WHERE clause
    $where = "WHERE (p.product_name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?) AND p.status = 'active'";
    $params = ["%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%"];
    
    // Add category filter
    if ($category_filter > 0) {
        $where .= ' AND p.category_id = ?';
        $params[] = $category_filter;
    }
    
    // Add price filters
    if ($min_price !== '' && is_numeric($min_price)) {
        $where .= ' AND p.price >= ?';
        $params[] = (float)$min_price;
    }
    if ($max_price !== '' && is_numeric($max_price)) {
        $where .= ' AND p.price <= ?';
        $params[] = (float)$max_price;
    }

    // Build ORDER BY clause
    $orderBy = 'ORDER BY p.created_at DESC';
    switch ($sort) {
        case 'price_low':
            $orderBy = 'ORDER BY p.price ASC';
            break;
        case 'price_high':
            $orderBy = 'ORDER BY p.price DESC';
            break;
        case 'name':
            $orderBy = 'ORDER BY p.product_name ASC';
            break;
        case 'newest':
        default:
            $orderBy = 'ORDER BY p.created_at DESC';
            break;
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(DISTINCT p.product_id) FROM product p LEFT JOIN category c ON p.category_id = c.category_id $where";
    $countStm = $_db->prepare($countSql);
    $countStm->execute($params);
    $totalProducts = $countStm->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);

    // Ensure page is within valid range
    if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

    // Search products with pagination
    $sql = "
        SELECT p.*, 
               pp.photo_filename as main_photo,
               SUM(pv.stock) as total_stock,
               c.category_name
        FROM product p 
        LEFT JOIN product_photos pp ON p.product_id = pp.product_id AND pp.is_main_photo = 1
        LEFT JOIN product_variants pv ON p.product_id = pv.product_id 
        LEFT JOIN category c ON p.category_id = c.category_id
        $where
        GROUP BY p.product_id
        $orderBy
        LIMIT $limit OFFSET $offset
    ";
    
    $stm = $_db->prepare($sql);
    $stm->execute($params);
    $products = $stm->fetchAll();
}

// Get all categories for filter
$categoriesStm = $_db->query('SELECT * FROM category ORDER BY category_name');
$categories = $categoriesStm->fetchAll();

include '../../head.php';
?>

<main style="padding: 20px;">
    <div class="search-results-container" style="max-width: 1200px; margin: 0 auto;">
        <div class="search-header" style="margin-bottom: 30px;">
            <h2>Search Results</h2>
            <?php if ($search_query): ?>
                <p style="color: #666; margin: 10px 0;">
                    <?php if ($totalProducts > 0): ?>
                        Showing <?= ($offset + 1) ?> - <?= min($offset + $limit, $totalProducts) ?> of <?= $totalProducts ?> result(s) for "<strong><?= htmlspecialchars($search_query) ?></strong>"
                    <?php else: ?>
                        0 results found for "<strong><?= htmlspecialchars($search_query) ?></strong>"
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p style="color: #666; margin: 10px 0;">Please enter a search term.</p>
            <?php endif; ?>
        </div>

        <?php if ($search_query): ?>
        <!-- Controls -->
        <div class="shop-controls">
            <div class="filter-group">
                <label for="category-filter">Category:</label>
                <select id="category-filter">
                    <option value="0" <?= $category_filter == 0 ? 'selected' : '' ?>>All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat->category_id ?>" <?= $category_filter == $cat->category_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat->category_name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort-filter">Sort by:</label>
                <select id="sort-filter">
                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name A-Z</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="min-price">Min Price:</label>
                <input type="number" id="min-price" placeholder="0" min="0" step="0.01" value="<?= htmlspecialchars($min_price) ?>">
            </div>
            
            <div class="filter-group">
                <label for="max-price">Max Price:</label>
                <input type="number" id="max-price" placeholder="1000" min="0" step="0.01" value="<?= htmlspecialchars($max_price) ?>">
            </div>
            
            <div class="filter-group">
                <button class="filter-btn">Apply Filters</button>
                <button class="clear-btn">Clear</button>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <?php if ($product->total_stock <= 0): ?>
            <!-- Out of stock product - not clickable -->
            <div class="product-card out-of-stock-card">
                <div class="product-image">
                    <?php 
                    $photo_src = $product->main_photo ?: ($product->photo ?: 'defaultProduct.png');
                    ?>
                    <img src="../../images/Products/<?= $photo_src ?>" alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
                    <div class="out-of-stock-overlay">Out of Stock</div>
                </div>
                <div class="product-info">
                    <span class="brand"><?= htmlspecialchars($product->brand) ?></span>
                    <h3><?= htmlspecialchars($product->product_name) ?></h3>
                    <div class="category-tag" style="font-size: 12px; color: #666; margin: 5px 0;">
                        <?= htmlspecialchars($product->category_name ?? 'Unknown') ?>
                    </div>
                    <div class="price">RM <?= number_format($product->price, 2) ?></div>
                    <div class="stock-status out-of-stock" style="color: #dc3545; font-weight: bold; margin: 5px 0; font-size: 12px;">
                        Out of Stock
                    </div>
                    <button class="shop out-of-stock-btn" style="background: #ccc; cursor: not-allowed; color: #666;" disabled>Out of Stock</button>
                </div>  
            </div>
            <?php else: ?>
            <!-- In stock product - clickable -->
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
                            <?= htmlspecialchars($product->category_name ?? 'Unknown') ?>
                        </div>
                        <div class="price">RM <?= number_format($product->price, 2) ?></div>
                        <button class="shop" onclick="event.preventDefault(); window.location.href='product_detail.php?id=<?= $product->product_id ?>'">Shop</button>
                    </div>  
                </div>
            </a>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 40px; text-align: center; padding: 20px 0;">
            <div class="pagination-info" style="color: #666; margin-bottom: 20px; font-size: 0.9rem;">
                Page <?= $page ?> of <?= $totalPages ?> (<?= $totalProducts ?> products total)
            </div>
            
            <?php
            $filterParams = 'q=' . urlencode($search_query);
            if ($category_filter > 0) $filterParams .= '&category=' . $category_filter;
            if ($sort !== 'newest') $filterParams .= '&sort=' . $sort;
            if ($min_price !== '') $filterParams .= '&min_price=' . $min_price;
            if ($max_price !== '') $filterParams .= '&max_price=' . $max_price;
            ?>
            
            <?php if ($page > 1): ?>
                <a href="?<?= $filterParams ?>&page=1" class="pagination-btn">First</a>
                <a href="?<?= $filterParams ?>&page=<?= $page - 1 ?>" class="pagination-btn">Previous</a>
            <?php else: ?>
                <span class="pagination-btn disabled">First</span>
                <span class="pagination-btn disabled">Previous</span>
            <?php endif; ?>
            
            <?php
            // Show page numbers
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <?php if ($i == $page): ?>
                    <span class="pagination-btn current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?<?= $filterParams ?>&page=<?= $i ?>" class="pagination-btn"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $filterParams ?>&page=<?= $page + 1 ?>" class="pagination-btn">Next</a>
                <a href="?<?= $filterParams ?>&page=<?= $totalPages ?>" class="pagination-btn">Last</a>
            <?php else: ?>
                <span class="pagination-btn disabled">Next</span>
                <span class="pagination-btn disabled">Last</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php elseif ($search_query): ?>
        <div class="no-results" style="text-align: center; padding: 60px 20px; color: #666;">
            <h3>No products found</h3>
            <p>Try adjusting your search terms or browse our categories:</p>
            <div style="margin-top: 20px;">
                <a href="../categories/category.php?id=1" style="margin: 0 10px; color: #007cba;">Running</a>
                <a href="../categories/category.php?id=2" style="margin: 0 10px; color: #007cba;">Casual</a>
                <a href="../categories/category.php?id=3" style="margin: 0 10px; color: #007cba;">Formal</a>
                <a href="../categories/category.php?id=4" style="margin: 0 10px; color: #007cba;">Basketball</a>
                <a href="../categories/category.php?id=5" style="margin: 0 10px; color: #007cba;">Other</a>
            </div>
        </div>
        
        <?php else: ?>
        <div class="search-suggestions" style="text-align: center; padding: 60px 20px; color: #666;">
            <h3>What are you looking for?</h3>
            <p>Search for products by name, brand, or category.</p>
            <div style="margin-top: 30px;">
                <h4>Popular Categories:</h4>
                <div style="margin-top: 15px;">
                    <a href="../categories/category.php?id=1" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Running</a>
                    <a href="../categories/category.php?id=2" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Casual</a>
                    <a href="../categories/category.php?id=3" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Formal</a>
                    <a href="../categories/category.php?id=4" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Basketball</a>
                    <a href="../categories/category.php?id=5" class="category-suggestion" style="display: inline-block; margin: 5px 10px; padding: 8px 16px; background: #f5f5f5; border-radius: 20px; text-decoration: none; color: #333;">Other</a>
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
