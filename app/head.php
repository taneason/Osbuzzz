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
</head>
<body>
    <div id="info"><?= temp('info') ?></div>
    <header>
    <h1>
        <div class="logo">
            <img src="/images/logo.png">
        </div>
        <div class="title">
            <a href="/">Osbuzzz</a>
        </div>
        <?php if ($_user): ?>
        <div class="login_register">
            <nav>
                <a href="/page/profile.php"><?= $_user->username ?></a>
                |
                <a href="/page/logout.php" data-confirm="Are you sure you want to logout?">Logout</a>
                |
                <a href="/page/cart.php" class="cart-icon">🛒</a> <!-- 新增购物车图标 -->
            </nav>
        </div>
        <a href="/page/profile.php"><img class="header-avatar" src="<?= $_user->photo ? '/photos/' . $_user->photo : '/images/default-avatar.png' ?>"></a>
        <?php else: ?>
        <div class="login_register">
            <nav>
                <a href="/page/login.php">Login</a>
                |
                <a href="/page/signup.php">Register</a>
            </nav>
        </div>
        <?php endif ?>
    </h1>
</header>

    <nav>
        <a href="/">Home</a>
        <a href="/page/sales.php">Sales</a>
        <a href="/page/men.php">Men</a>
        <a href="/page/women.php">Women</a>
        <a href="/page/kids.php">Kids</a>
        <?php if ($_user && $_user->role === 'Admin'): ?>
            <a href="/page/admin.php">Admin</a>
        <?php endif; ?>
    </nav>
