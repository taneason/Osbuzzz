<?php
require '../../base.php';

include '../../head.php';
?>
      
<main>    

    <div class="products-grid">
        <?php
        global $_db;
        $products = $_db->query("SELECT * FROM product ORDER BY created_at DESC")->fetchAll();
        foreach ($products as $product):
        ?>
        <a href="product_detail.php?id=<?= $product->product_id ?>" class="product-card-link">
            <div class="product-card">
                <div class="product-image">
                    <?php
                    // Use product photo if available, otherwise fallback to default
                    $imgPath = !empty($product->photo) ? "/images/Products/" . htmlspecialchars($product->photo) : "/images/Products/defaultProduct.png";
                    ?>
                    <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
                </div>
                <div class="product-info">
                    <span class="brand"><?= htmlspecialchars($product->brand) ?></span>
                    <h3><?= htmlspecialchars($product->product_name) ?></h3>
                    <div class="price">RM <?= number_format($product->price, 2) ?></div>
                    <button class="shop" onclick="event.preventDefault(); window.location.href='product_detail.php?id=<?= $product->product_id ?>'">Shop</button>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</main>
<?php

include '../../foot.php';
