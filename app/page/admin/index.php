<?php
require '../../base.php';
auth('Admin');

// Get some statistics for dashboard
$stats = [];

// Get total products
$stm = $_db->query("SELECT COUNT(*) as total FROM product");
$stats['products'] = $stm->fetchColumn();

// Get active products
$stm = $_db->query("SELECT COUNT(*) as total FROM product WHERE status = 'active'");
$stats['active_products'] = $stm->fetchColumn();

// Get total users
$stm = $_db->query("SELECT COUNT(*) as total FROM user WHERE role != 'Admin'");
$stats['users'] = $stm->fetchColumn();

// Get total categories
$stm = $_db->query("SELECT COUNT(*) as total FROM category");
$stats['categories'] = $stm->fetchColumn();

// Get low stock products (less than 5 total stock)
$stm = $_db->query("
    SELECT COUNT(DISTINCT p.product_id) as low_stock_count
    FROM product p
    LEFT JOIN product_variants pv ON p.product_id = pv.product_id
    WHERE p.status = 'active'
    GROUP BY p.product_id
    HAVING COALESCE(SUM(pv.stock), 0) < 5
");
$low_stock_products = $stm->fetchAll();
$stats['low_stock'] = count($low_stock_products);

// Get recent products (last 7 days)
$stm = $_db->query("SELECT COUNT(*) as total FROM product WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['recent_products'] = $stm->fetchColumn();

include '../../head.php';
?>

<style>
.admin-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: #D3F4EF;
    border-radius: 8px;
    border: 1px solid #A0E7E2;
}

.dashboard-header h1 {
    margin: 0 0 10px 0;
    font-size: 2rem;
    color: #2c5530;
}

.dashboard-header p {
    margin: 0;
    color: #666;
    font-size: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin: 10px 0 5px 0;
    color: #2c5530;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.quick-actions {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #ddd;
}

.quick-actions h2 {
    margin: 0 0 20px 0;
    color: #2c5530;
    font-size: 1.5rem;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.action-btn {
    display: block;
    padding: 15px;
    text-decoration: none;
    color: #333;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #ddd;
    text-align: center;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #D3F4EF;
    border-color: #A0E7E2;
    color: #2c5530;
}

.action-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 5px 0;
}

.action-desc {
    font-size: 0.85rem;
    color: #666;
    margin: 0;
}

/* Simple status indicators */
.stat-card.products { border-left: 4px solid #28a745; }
.stat-card.users { border-left: 4px solid #007bff; }
.stat-card.categories { border-left: 4px solid #ffc107; }
.stat-card.low-stock { border-left: 4px solid #dc3545; }

@media (max-width: 768px) {
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<main>
    <div class="admin-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($_user->name ?? $_user->username) ?>! Manage your store from here.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card products">
                <div class="stat-number"><?= $stats['products'] ?></div>
                <p class="stat-label">Total Products</p>
            </div>

            <div class="stat-card users">
                <div class="stat-number"><?= $stats['users'] ?></div>
                <p class="stat-label">Registered Users</p>
            </div>

            <div class="stat-card categories">
                <div class="stat-number"><?= $stats['categories'] ?></div>
                <p class="stat-label">Categories</p>
            </div>

            <div class="stat-card low-stock">
                <div class="stat-number"><?= $stats['low_stock'] ?></div>
                <p class="stat-label">Low Stock Items</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="admin_product.php" class="action-btn">
                    <div class="action-title">Manage Products</div>
                    <div class="action-desc">Add, edit, or remove products</div>
                </a>

                <a href="admin_user.php" class="action-btn">
                    <div class="action-title">Manage Users</div>
                    <div class="action-desc">View and manage user accounts</div>
                </a>

                <a href="admin_category.php" class="action-btn">
                    <div class="action-title">Manage Categories</div>
                    <div class="action-desc">Edit categories and banners</div>
                </a>

                <a href="admin_product_add.php" class="action-btn">
                    <div class="action-title">Add New Product</div>
                    <div class="action-desc">Quickly add a new product</div>
                </a>
            </div>
        </div>
    </div>
</main>

<?php
include '../../foot.php';
?>
