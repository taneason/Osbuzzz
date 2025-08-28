<?php
// Get cart count if user is logged in
$cart_count = 0;
if ($_user) {
    $cart_count = cart_get_count();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osbuzzz</title>
    <link rel="shortcut icon" href="/images/logo.png">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <script>
        function performSearch() {
            const searchTerm = document.getElementById('headerSearch').value.trim();
            if (searchTerm) {
                window.location.href = '/page/shop/search.php?q=' + encodeURIComponent(searchTerm);
            }
        }
        
        // Update cart count dynamically
        function updateHeaderCartCount() {
            <?php if ($_user && $_user->role !== 'Admin'): ?>
            fetch('/page/shop/cart_handler.php?action=get_count')
            .then(response => response.json())
            .then(data => {
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement && data.count !== undefined) {
                    cartCountElement.textContent = data.count;
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
            <?php endif; ?>
        }
        
        // Enter key search
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('headerSearch');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });
            }
        });
    </script>
    <style>
        /* Header Styles */
        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .search-container {
            background: #D3F4EF;
            border-top: 1px solid #e9ecef;
            padding: 10px 0;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 25px;
            padding: 8px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-bar:focus-within {
            border-color: #007cba;
            box-shadow: 0 4px 12px rgba(0,124,186,0.15);
        }
        
        .search-bar input {
            flex: 1;
            border: none;
            background: transparent;
            outline: none;
            padding: 10px;
            font-size: 14px;
        }
        
        .search-bar button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 8px;
            color: #666;
            transition: color 0.3s ease;
        }
        
        .search-bar button:hover {
            color: #007cba;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .cart-link {
            position: relative;
            font-size: 20px;
            text-decoration: none;
            color: #333;
            transition: color 0.3s ease;
        }
        
        .cart-link:hover {
            color: #007cba;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
            padding: 8px 12px;
            border-radius: 20px;
            transition: background 0.3s ease;
        }
        
        .user-profile:hover {
            background: #f5f5f5;
        }
        
        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 8px 0;
            min-width: 150px;
            z-index: 1000;
        }
        
        .user-dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-content a {
            display: block;
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s ease;
        }
        
        .dropdown-content a:hover {
            background: #f5f5f5;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-login, .btn-register {
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-login {
            color: #007cba;
            border: 2px solid #007cba;
            background: transparent;
        }
        
        .btn-login:hover {
            background: #007cba;
            color: white;
        }
        
        .btn-register {
            background: #007cba;
            color: white;
            border: 2px solid #007cba;
        }
        
        .btn-register:hover {
            background: #005a8a;
            border-color: #005a8a;
        }
        
        @media (max-width: 768px) {
            .header-container {
                padding: 10px 15px;
            }
            
            .search-container {
                padding: 8px 15px;
                background-color: aqua;
            }
            
            .search-bar {
                max-width: none;
                margin: 0;
            }
            
            .user-profile span {
                display: none;
            }
            
            .auth-buttons {
                gap: 8px;
            }
            
            .btn-login, .btn-register {
                padding: 6px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div id="info"><?= temp('info') ?></div>
    <header>
        <div class="header-container">
            <div class="header-left">
                <div class="logo">
                    <img src="/images/logo.png" alt="Osbuzzz Logo">
                </div>
                <div class="title">
                    <?php if ($_user && $_user->role === 'Admin'): ?>
                    <a href="/page/admin/index.php">Osbuzzz Admin</a>
                    <?php else: ?>
                    <a href="/">Osbuzzz</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="header-right">
                <?php if ($_user): ?>
                <div class="user-section">
                    <?php if ($_user->role !== 'Admin'): ?>
                    <a href="/page/shop/cart.php" class="cart-link">
                        🛒 <span class="cart-count"><?= $cart_count ?></span>
                    </a>
                    <?php endif; ?>
                    <div class="user-dropdown">
                        <a href="/page/user/profile.php" class="user-profile">
                            <img class="header-avatar" src="<?= $_user->photo ? '/images/userAvatar/' . $_user->photo : '/images/default-avatar.png' ?>" alt="Profile">
                            <span><?= htmlspecialchars($_user->username) ?></span>
                        </a>
                        <div class="dropdown-content">
                            <a href="/page/user/profile.php">Profile</a>
                            <?php if ($_user->role !== 'Admin'): ?>
                            <a href="/page/shop/cart.php">Cart</a>
                            <?php endif; ?>
                            <?php if ($_user->role === 'Admin'): ?>
                            <a href="/page/admin/index.php">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="/page/user/logout.php" data-confirm="Are you sure you want to logout?">Logout</a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons">
                    <a href="/page/user/login.php" class="btn-login">Login</a>
                    <a href="/page/user/signup.php" class="btn-register">Register</a>
                </div>
                <?php endif ?>
            </div>
        </div>
        
        <!-- Search bar in separate row (only for non-admin users) -->
        <?php if (!$_user || $_user->role !== 'Admin'): ?>
        <div class="search-container">
            <div class="search-bar">
                <input type="search" placeholder="Search products..." id="headerSearch">
                <button type="button" onclick="performSearch()">🔍</button>
            </div>
        </div>
        <?php endif; ?>
    </header>

    <nav>
        <?php if ($_user && $_user->role === 'Admin'): ?>
            <a href="/page/admin/index.php">Dashboard</a>
            <a href="/page/admin/admin_user.php">Manage Users</a>
            <a href="/page/admin/admin_product.php">Manage Products</a>
            <a href="/page/admin/admin_category.php">Manage Categories</a>
        <?php else: ?>
        <a href="/">Home</a>
        <a href="/page/shop/sales.php">Sales</a>
        <div class="nav-dropdown">
            <a href="#" class="nav-dropbtn">Categories ▼</a>
            <div class="nav-dropdown-content">
                <?php
                // Get all active categories for navigation
                $nav_categories = $_db->query('SELECT category_id, category_name FROM category ORDER BY category_name ASC')->fetchAll();
                foreach ($nav_categories as $nav_cat):
                ?>
                <a href="/page/categories/category.php?id=<?= $nav_cat->category_id ?>"><?= htmlspecialchars($nav_cat->category_name) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </nav>
