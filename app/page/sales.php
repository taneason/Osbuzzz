<?php
require '../base.php';

include '../head.php';
?>
      
<main>    
    <button data-get="/">Index</button>
    <button data-get="men.php">Men</button>
    <button data-get="women.php">Women</button>
    <button data-get="kids.php">kids</button>
    <button data-get>Reload</button>
    <span data-get="https://www.tarc.edu.my">TAR UMT</span>

    <div class="products-grid">
        <?php
        global $_db;
        $products = $_db->query("SELECT * FROM product ORDER BY created_at DESC")->fetchAll();
        foreach ($products as $product):
        ?>
        <div class="product-card">
            <div class="product-image">
                <?php
                // Use product photo if available, otherwise fallback to default
                $imgPath = !empty($product->photo) ? "/images/Products/" . htmlspecialchars($product->photo) : "/images/Products/defaultProduct.png";
                ?>
                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($product->product_name) ?>" loading="lazy">
                <button class="favourite-btn">♡</button>
            </div>
            <div class="product-info">
                <span class="brand"><?= htmlspecialchars($product->brand) ?></span>
                <h3><?= htmlspecialchars($product->product_name) ?></h3>
                <div class="price">RM <?= number_format($product->price, 2) ?></div>
                <button class="shop">Shop</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php

include '../foot.php';