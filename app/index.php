<?php
require 'base.php';

include 'head.php';
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
</main>

    <section class="featured-categories">
        <div class="category-card" onclick="location.href='/page/categories/running.php'">
            <img src="/images/menu/men.png" alt="Running">
            <div class="category-overlay">
                <h3>Running</h3>
            </div>
        </div>
        <div class="category-card" onclick="location.href='/page/categories/casual.php'">
            <img src="/images/menu/women.png" alt="Casual">
            <div class="category-overlay">
                <h3>Casual</h3>
            </div>
        </div>
        <div class="category-card" onclick="location.href='/page/categories/basketball.php'">
            <img src="/images/menu/kids.png" alt="Basketball">
            <div class="category-overlay">
                <h3>Basketball</h3>
            </div>
        </div>
    </section>
    

    <section class="promo-banner">
    <div class="promo-container">
        <div class="promo-photo">
            <img src="/images/menu/sales.png" alt="Summersales">
        </div>
        <div class="promo-content">
            <h2>Summer Sale</h2>
            <p>30% Off For Selected Items</p>
            <a href="/page/shop/sales.php" class="cta-button">Shop Sale</a>
        </div>
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
