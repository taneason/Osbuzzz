<?php
require '../../base.php';
auth('Admin');

// Product table sorting
$sort = $_GET['sort'] ?? 'product_id';
$order = $_GET['order'] ?? 'asc';
$allowed = ['product_id','product_name','brand','price','stock','total_stock','category','status'];
if (!in_array($sort, $allowed)) $sort = 'product_id';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// Search logic
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE p.product_name LIKE ? OR p.brand LIKE ? OR c.category_name LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; // Products per page
$offset = ($page - 1) * $limit;

// Get total count for pagination
$countSql = "SELECT COUNT(DISTINCT p.product_id) FROM product p LEFT JOIN product_variants v ON p.product_id = v.product_id LEFT JOIN category c ON p.category_id = c.category_id $where";
$stm = $_db->prepare($countSql);
$stm->execute($params);
$totalProducts = $stm->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Build ORDER BY clause with proper field mapping
$orderByField = $sort;
if ($sort === 'category') {
    $orderByField = 'c.category_name';
} elseif ($sort === 'status') {
    $orderByField = 'p.status';
} elseif (in_array($sort, ['product_id', 'product_name', 'brand', 'price'])) {
    $orderByField = 'p.' . $sort;
}

$sql = "SELECT p.product_id, p.product_name, p.brand, p.price, p.photo, p.status,
           c.category_name as category,
           COALESCE(SUM(v.stock),0) AS total_stock,
           pp.photo_filename as main_photo
    FROM product p
    LEFT JOIN product_variants v ON p.product_id = v.product_id
    LEFT JOIN product_photos pp ON p.product_id = pp.product_id AND pp.is_main_photo = 1
    LEFT JOIN category c ON p.category_id = c.category_id
    $where
    GROUP BY p.product_id ORDER BY $orderByField $order
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
                <th>Photo</th>
                <th><?= sort_link('product_id','ID',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('product_name','Name',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('brand','Brand',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('category','Category',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('status','Status',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('price','Price',$sort,$order,$search,$page) ?></th>
                <th><?= sort_link('total_stock','Total Stock',$sort,$order,$search,$page) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($products as $product): ?>
            <tr>
                <td style="text-align: center;">
                    <?php   
                    $photo_src = null;
                    if ($product->main_photo) {
                        $photo_src = $product->main_photo;
                    } elseif ($product->photo) {
                        $photo_src = $product->photo;
                    } else {
                        $photo_src = 'defaultProduct.png';
                    }
                    ?>
                    <img src="../../images/Products/<?= $photo_src ?>" 
                         alt="Product photo" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;"
                         onclick='openImageModal("../../images/Products/<?= $photo_src ?>", "<?= htmlspecialchars($product->product_name) ?>")'>
                </td>
                <td><?= $product->product_id ?></td>
                <td><?= htmlspecialchars($product->product_name) ?></td>
                <td><?= htmlspecialchars($product->brand) ?></td>
                <td><?= htmlspecialchars($product->category) ?></td>
                <td>
                    <span class="status-badge status-<?= $product->status ?>" 
                          style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; 
                                 <?= $product->status === 'active' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' ?>">
                        <?= ucfirst($product->status) ?>
                    </span>
                </td>
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
    
    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
        <div class="image-modal-caption" id="modalCaption"></div>
    </div>
</main>

<style>
.image-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
}

.image-modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 80%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.image-modal-close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1001;
    transition: color 0.3s;
}

.image-modal-close:hover {
    color: #ccc;
}

.image-modal-caption {
    margin: auto;
    display: block;
    width: 80%;
    max-width: 700px;
    text-align: center;
    color: white;
    padding: 20px 0;
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 16px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 4px;
    padding: 10px 20px;
}

.image-modal-content,
.image-modal-caption {
    animation: zoom 0.3s ease;
}

@keyframes zoom {
    from { transform: translate(-50%, -50%) scale(0.7); opacity: 0; }
    to { transform: translate(-50%, -50%) scale(1); opacity: 1; }
}
</style>



<?php
include '../../foot.php';