<?php
require '../../base.php';
auth('Admin');

$product_id = req('id');
// --- Sorting Logic ---
$sort = $_GET['sort'] ?? 'size';
$order = $_GET['order'] ?? 'asc';
$allowedSort = ['size', 'stock'];
if (!in_array($sort, $allowedSort)) $sort = 'size';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// --- Fetch Variants ---
$stm = $_db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY $sort $order");
$stm->execute([$product_id]);
$variants = $stm->fetchAll();


include '../../head.php';
?>
<main>
    <section id="products" style="margin-top:40px;">
    <h2>Product Stock</h2>
    <p>
        <button data-get="variantAdd.php?id=<?= $product_id ?>">Add Size</button>
        <button data-get="admin_product.php">Back to Product List</button>
    </p>
    <table class="admin-product-table">
            <tr>
                <th><?= sort_link('size', 'Size', $sort, $order) ?></th>
                <th><?= sort_link('stock', 'Stock', $sort, $order) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($variants as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v->size) ?></td>
                    <td><?= htmlspecialchars($v->stock) ?></td>
                <td style="align-items:center;">
                    <button data-get="variantEdit.php?id=<?= $product_id ?>&vid=<?= $v->variant_id ?>">Edit</button>
                    <button data-confirm ="Are you sure you want to delete this variant?" data-post="variantDelete.php?vid=<?= $v->variant_id ?>">Delete</button>
                </td>
                </tr>
            <?php endforeach ?>
    </table>
    </section>
</main>
<?php
include '../../foot.php';
?>