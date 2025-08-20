<?php
require '../../base.php';
auth('Admin');

// Product table sorting
$sort = $_GET['sort'] ?? 'product_id';
$order = $_GET['order'] ?? 'asc';
$allowed = ['product_id','product_name','brand','price','stock','total_stock'];
if (!in_array($sort, $allowed)) $sort = 'product_id';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// Search logic
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE p.product_name LIKE ? OR p.brand LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = "SELECT p.product_id, p.product_name, p.brand, p.price,
           COALESCE(SUM(v.stock),0) AS total_stock
    FROM product p
    LEFT JOIN product_variants v ON p.product_id = v.product_id
    $where
    GROUP BY p.product_id ORDER BY $sort $order";
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
            <button type="submit">Search</button>
            <?php if($search): ?><a href="admin_product.php" class="admin-btn" style="margin-left:8px;">Clear</a><?php endif; ?>
        </form>
        <p>
            <button data-get="admin_product_add.php">Add Product</button>
        </p>
        <table class="admin-product-table">
            <tr>
                <th><?= sort_link('product_id','ID',$sort,$order,$search) ?></th>
                <th><?= sort_link('product_name','Name',$sort,$order,$search) ?></th>
                <th><?= sort_link('brand','Brand',$sort,$order,$search) ?></th>
                <th><?= sort_link('price','Price',$sort,$order,$search) ?></th>
                <th><?= sort_link('total_stock','Total Stock',$sort,$order,$search) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product->product_id ?></td>
                <td><?= htmlspecialchars($product->product_name) ?></td>
                <td><?= htmlspecialchars($product->brand) ?></td>
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
    </section>
</main>

<?php
include '../../foot.php';