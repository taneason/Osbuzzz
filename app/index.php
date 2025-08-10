<?php
require 'base.php';

include 'head.php';
?>

<main>
    <section class="hero">
        <!-- 多图轮播背景 -->
        <div class="hero-background"></div>
        
        <!-- 居中文字内容 - 完全保留您的原始结构 -->
        <div class="hero-content">
            <h1>New Season Collection</h1>
            <p>Discover the latest trends in fashion</p>
            <a href="/page/shop.php" class="cta-button">Shop Now</a>
        </div>
    </section>
</main>

    <section class="featured-categories">
        <div class="category-card" onclick="location.href='/page/men.php'">
            <img src="/images/menu/men.png" alt="Men">
            <div class="category-overlay">
                <h3>Men's</h3>
            </div>
        </div>
        <div class="category-card" onclick="location.href='/page/women.php'">
            <img src="/images/menu/women.png" alt="Women">
            <div class="category-overlay">
                <h3>Women's</h3>
            </div>
        </div>
        <div class="category-card" onclick="location.href='/page/kids.php'">
            <img src="/images/menu/kids.png" alt="Kids">
            <div class="category-overlay">
                <h3>Kids</h3>
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
            <a href="/page/sales.php" class="cta-button">Shop Sale</a>
        </div>
    </div>
</section>
</main>
<?php
include 'foot.php';