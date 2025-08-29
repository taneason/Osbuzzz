<?php
require '../../base.php';

// Pagination setup
$page = (int)($_GET['page'] ?? 1);
$limit = 12; // 12 products per page
$offset = ($page - 1) * $limit;
if ($page < 1) $page = 1;

// Sorting and filtering
$sort = $_GET['sort'] ?? 'newest';
$category_filter = (int)($_GET['category'] ?? 0);
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build WHERE clause
$where = "WHERE p.status = 'active'";
$params = [];
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
$countSql = "SELECT COUNT(*) FROM product p $where";
$countStm = $_db->prepare($countSql);
$countStm->execute($params);
$totalProducts = $countStm->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Ensure page is within valid range
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// Get products with pagination
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

// Get all categories for filter
$categoriesStm = $_db->query('SELECT * FROM category ORDER BY category_name');
$categories = $categoriesStm->fetchAll();

include '../../head.php';
?>
      
<main>
    <!-- Shop Header -->
    <div class="shop-header" style="background-image: url('/images/banners/allproductBanner.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.7); padding: 60px 20px; min-height: 300px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <h1 style="margin: 0; font-size: 3rem; font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">All Products</h1>
        <p style="margin: 15px auto 0; font-size: 1.2rem; max-width: 600px; opacity: 0.95; line-height: 1.6; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">Discover our complete collection of premium footwear</p>
    </div>

    <!-- Shop Container -->
    <div class="shop-container">
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

        <?php if (!empty($products)): ?>
        <div class="products-count">
            Showing <?= ($offset + 1) ?> - <?= min($offset + $limit, $totalProducts) ?> of <?= $totalProducts ?> product<?= $totalProducts !== 1 ? 's' : '' ?>
        </div>
      
<main>    

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
        <?php if ($product->total_stock <= 0): ?>
        <!-- Out of stock product - not clickable -->
        <div class="product-card out-of-stock-card">
            <div class="product-image">
                <?php 
                $photo_src = $product->main_photo ?: ($product->photo ?: 'defaultProduct.png');
                ?>
                <img src="/images/Products/<?= $photo_src ?>" alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
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
                    <img src="/images/Products/<?= $photo_src ?>" alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
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
    <div class="pagination">
        <div class="pagination-info">
            Page <?= $page ?> of <?= $totalPages ?> (<?= $totalProducts ?> products total)
        </div>
        
        <?php
        $filterParams = '';
        if ($category_filter > 0) $filterParams .= '&category=' . $category_filter;
        if ($sort !== 'newest') $filterParams .= '&sort=' . $sort;
        if ($min_price !== '') $filterParams .= '&min_price=' . $min_price;
        if ($max_price !== '') $filterParams .= '&max_price=' . $max_price;
        ?>
        
        <?php if ($page > 1): ?>
            <a href="?page=1<?= $filterParams ?>">First</a>
            <a href="?page=<?= $page - 1 ?><?= $filterParams ?>">Previous</a>
        <?php else: ?>
            <span class="disabled">First</span>
            <span class="disabled">Previous</span>
        <?php endif; ?>
        
        <?php
        // Show page numbers
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        
        for ($i = $start; $i <= $end; $i++):
        ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?><?= $filterParams ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $filterParams ?>">Next</a>
            <a href="?page=<?= $totalPages ?><?= $filterParams ?>">Last</a>
        <?php else: ?>
            <span class="disabled">Next</span>
            <span class="disabled">Last</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <div class="no-products">
        <h3>No products found</h3>
        <p>Try adjusting your filters or browse different categories.</p>
    </div>
    <?php endif; ?>
    </div>
</main>

<?php

include '../../foot.php';
