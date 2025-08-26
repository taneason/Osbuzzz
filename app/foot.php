</main>

    <footer>
        <div class="containers">
            <div class="footer-content">
                <h3>Contact Us</h3>
                <p>Email: Osbuzzz@gmail.com<br><br>Phone: +60 10-2288878<br><br>Address: Malaysia<br><br>
                <div class="ins">
                    <img src="/images/insLogo.png"> <a href="https://www.instagram.com/osbuzzz?igsh=bnhvaDg1eHd5Z2Z0">Osbuzzz
                    </div>
                </p>
            </div>
            <div class="footer-content">
                <h3>Shopping</h3>
                <ul class="list">
                    <li><a href="/page/categories/running.php">Running</a></li>
                    <li><a href="/page/categories/casual.php">Casual</a></li>
                    <li><a href="/page/categories/formal.php">Formal</a></li>
                    <li><a href="/page/categories/basketball.php">Basketball</a></li>
                    <li><a href="/page/categories/other.php">Other</a></li>
                    <li><a href="/page/shop/sales.php">Sales</a></li>
                </ul>
            </div>  
            <div class="footer-content">
                <h3>About</h3>
                <ul class="list">
                    <li><a href="/">Home</a></li>
                    <?php if (!$_user || $_user->role !== 'Admin'): ?>
                    <li><a href="/page/shop/search.php">Search</a></li>
                    <?php endif; ?>
                    <?php if ($_user): ?>
                    <li><a href="/page/user/profile.php">My Profile</a></li>
                    <?php if ($_user->role !== 'Admin'): ?>
                    <li><a href="/page/shop/cart.php">My Cart</a></li>
                    <?php endif; ?>
                    <?php else: ?>
                    <li><a href="/page/user/login.php">Login</a></li>
                    <li><a href="/page/user/signup.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="bottom-bar">
            <small><i><p>Copyright © 2025 Osbuzzz - Premium Footwear Store</p></i></small>
            <a href="mailto:Osbuzzz@gmail.com">Osbuzzz@gmail.com</a>
        </div>
    </footer>
</body>
</html>