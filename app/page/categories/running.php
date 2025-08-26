<?php
require '../../base.php';

// Get running products from database
$stm = $_db->prepare('
    SELECT p.*, 
           pp.photo_filename as main_photo,
           SUM(pv.stock) as total_stock
    FROM product p 
    LEFT JOIN product_photos pp ON p.product_id = pp.product_id AND pp.is_main_photo = 1
    LEFT JOIN product_variants pv ON p.product_id = pv.product_id 
    WHERE p.category = ? 
    GROUP BY p.product_id
    ORDER BY p.created_at DESC
');
$stm->execute(['Running']);
$products = $stm->fetchAll();

include '../../head.php';
?>
      
<main>    

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
        <a href="../shop/product_detail.php?id=<?= $product->product_id ?>" class="product-card-link">
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
                    <div class="price">RM <?= number_format($product->price, 2) ?></div>
                    <button class="shop" onclick="event.preventDefault(); window.location.href='../shop/product_detail.php?id=<?= $product->product_id ?>'">Shop</button>
                </div>  
            </div>
        </a>
        <?php endforeach; ?>
        
        <?php if (empty($products)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
            <h3>No running products available at the moment.</h3>
            <p>Please check back later or browse other categories.</p>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php

include '../../foot.php';

