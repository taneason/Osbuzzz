</main>

    <footer>
        <div class="containers">
            <div class="footer-content">
                <h3>Contact Us</h3>
                <p>Email: Osbuzzz@gmail.com<br><br>Phone: +60 10-2288878<br><br>Address: Malaysia<br><br>
                <div class="ins">
                    <img src="/images/insLogo.png"> <a href="https://www.instagram.com/osbuzzz?igsh=bnhvaDg1eHd5Z2Z0">Osbuzzz</a>
                    </div>
                </p>
            </div>
            <?php if ($_user && $_user->role === 'Admin'): ?>
            <div class="footer-content">
                <h3>Admin Panel</h3>
                <ul class="list">
                    <li><a href="/page/admin/index.php">Dashboard</a></li>
                    <li><a href="/page/admin/admin_product.php">Manage Products</a></li>
                    <li><a href="/page/admin/admin_user.php">Manage Users</a></li>
                    <li><a href="/page/admin/admin_category.php">Manage Categories</a></li>
                    <li><a href="/page/admin/password_reset_management.php">Manage Password Resets</a></li>
                    <li><a href="/page/user/profile.php">My Profile</a></li>
                </ul>
            </div>
            <?php else: ?>
            <div class="footer-content">
                <h3>Quick Links</h3>
                <ul class="list">
                    <li><a href="/">Home</a></li>
                    <?php if (!$_user): ?>
                    <li><a href="/page/shop/search.php">Search</a></li>
                    <li><a href="/page/user/login.php">Login</a></li>
                    <li><a href="/page/user/signup.php">Register</a></li>
                    <?php else: ?>
                    <li><a href="/page/shop/search.php">Search</a></li>
                    <li><a href="/page/user/profile.php">My Profile</a></li>
                    <li><a href="/page/shop/cart.php">My Cart</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!$_user || $_user->role !== 'Admin'): ?>
        <!-- Shopping Categories Section -->
        <div class="containers">
            <div class="footer-content" style="width: 100%; text-align: center;">
                <h3>Shop by Categories</h3>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
                    <?php
                    // Get all active categories for footer
                    $footer_categories = $_db->query('SELECT category_id, category_name FROM category ORDER BY category_name ASC')->fetchAll();
                    foreach ($footer_categories as $footer_cat):
                    ?>
                    <a href="/page/categories/category.php?id=<?= $footer_cat->category_id ?>" style="margin: 5px; text-decoration: none;"><?= htmlspecialchars($footer_cat->category_name) ?></a>
                    <?php endforeach; ?>
                    <a href="/page/shop/sales.php" style="margin: 5px; text-decoration: none; font-weight: bold;">Sales</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="bottom-bar">
            <small><i><p>Copyright © 2025 Osbuzzz - Premium Footwear Store</p></i></small>
            <a href="mailto:Osbuzzz@gmail.com">Osbuzzz@gmail.com</a>
        </div>
    </footer>

</body>
</html>