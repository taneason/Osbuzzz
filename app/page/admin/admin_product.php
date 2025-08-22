<?php
require '../../base.php';
auth('Admin');

// Product table sorting
$sort = $_GET['sort'] ?? 'product_id';
$order = $_GET['order'] ?? 'asc';
$allowed = ['product_id','product_name','brand','price','stock','total_stock','category'];
if (!in_array($sort, $allowed)) $sort = 'product_id';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// Search logic
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE p.product_name LIKE ? OR p.brand LIKE ? OR p.category LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; // Products per page
$offset = ($page - 1) * $limit;

// Get total count for pagination
$countSql = "SELECT COUNT(DISTINCT p.product_id) FROM product p LEFT JOIN product_variants v ON p.product_id = v.product_id $where";
$stm = $_db->prepare($countSql);
$stm->execute($params);
$totalProducts = $stm->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

$sql = "SELECT p.product_id, p.product_name, p.brand, p.category, p.price,
           COALESCE(SUM(v.stock),0) AS total_stock
    FROM product p
    LEFT JOIN product_variants v ON p.product_id = v.product_id
    $where
    GROUP BY p.product_id ORDER BY $sort $order
    LIMIT $limit OFFSET $offset";
$stm = $_db->prepare($sql);
$stm->execute($params);
$products = $stm->fetchAll();

include '../../head.php';

?>

<main>
    <section id="products" style="margin-top:40px;">
        <h2>Product Management</h2>
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
            <input type="search" name="search" placeholder="Search by name or brand" value="<?= htmlspecialchars($search) ?>" class="admin-form-input" style="width:220px;">
            <button type="submit" class="admin-btn" style="padding: 4px 16px;">Search</button>
            <?php if($search): ?><a href="admin_product.php" class="admin-btn" style="padding: 4px 16px; text-align:center; line-height:normal;">Clear</a><?php endif; ?>
        </form>
        <p>
            <button data-get="admin_product_add.php">Add Product</button>
        </p>
        <table class="admin-product-table">
            <tr>
                <th><?= sort_link('product_id','ID',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('product_name','Name',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('brand','Brand',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('category','Category',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('price','Price',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('total_stock','Total Stock',$sort,$order,$search,$page) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product->product_id ?></td>
                <td><?= htmlspecialchars($product->product_name) ?></td>
                <td><?= htmlspecialchars($product->brand) ?></td>
                <td><?= htmlspecialchars($product->category) ?></td>
                <td><?= number_format($product->price,2) ?></td>
                <td><?= $product->total_stock ?></td>
                <td style="align-items:center;">
                <button data-get="admin_product_edit.php?id=<?= $product->product_id ?>">Edit</button>
                <button data-get="admin_product_variants.php?id=<?= $product->product_id ?>">Manage Stock</button>
                <button data-confirm ="Are you sure you want to delete this product?" data-post="admin_product_delete.php?id=<?= $product->product_id ?>">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:20px;text-align:center;">
            <?php
            $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
            $sortParam = "&sort=$sort&order=$order";
            ?>
            
            <?php if ($page > 1): ?>
                <a href="?page=1<?= $searchParam . $sortParam ?>" class="admin-btn">First</a>
                <a href="?page=<?= $page - 1 ?><?= $searchParam . $sortParam ?>" class="admin-btn">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="admin-btn" style="background:#007cba;color:white;"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $searchParam . $sortParam ?>" class="admin-btn"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $searchParam . $sortParam ?>" class="admin-btn">Next</a>
                <a href="?page=<?= $totalPages ?><?= $searchParam . $sortParam ?>" class="admin-btn">Last</a>
            <?php endif; ?>
            
            <span style="margin-left:20px;">Page <?= $page ?> of <?= $totalPages ?> (<?= $totalProducts ?> products)</span>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php
include '../../foot.php';