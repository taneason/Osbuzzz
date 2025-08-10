
<?php
require '../base.php';
auth('Admin');



if (is_post() && isset($_POST['add_product'])) {
    $name = req('product_name');
    $brand = req('brand');
    $category = req('category');
    $size = req('size');
    $color = req('color');
    $price = req('price');
    $stock = req('stock');
    $description = req('description');
    $photo = null;
    $err = [];

    // Handle photo upload
    $f = get_file('photo');
    if (!$f) {
        $err['photo'] = 'Required';
    }
    else if (!str_starts_with($f->type, 'image/')) {
        $err['photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $err['photo'] = 'Maximum 1MB';
    }

    // Validate inputs
    if ($name == '') {
        $err['product_name'] = 'Product name is required';
    }
    if ($brand == '') {
        $err['brand'] = 'Brand is required';
    }
    if ($price == '') {
        $err['price'] = 'Price is required';
    }
    else if(!is_money($price)) {
        $err['price'] = 'Invalid price format';
    }
    else if ($price < 0.01 ) {
        $err['price'] = 'Price must be greater than 0.01';
    }
    if ($stock < 0) {
        $err['stock'] = 'Stock cannot be negative';
    }

    // If no errors, insert product into database
    if (!$err) {

    $photo = save_photo($f, __DIR__ . '/../images/Products');

        $stm = $_db->prepare("INSERT INTO product (product_name, brand, category, size, color, price, stock, description, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stm->execute([$name, $brand, $category, $size, $color, $price, $stock, $description, $photo]);
        temp('info', 'Product added successfully.');
        redirect('/page/admin.php#products');
    }
}

if (is_post() && isset($_POST['edit_product_id'])) {
    $id = req('edit_product_id');
    $name = req('edit_product_name');
    $brand = req('edit_brand');
    $price = req('edit_price');
    $stock = req('edit_stock');
    $f = get_file('edit_photo');
    $err = [];


    // Always get old photo from DB
    $stm = $_db->prepare("SELECT photo FROM product WHERE product_id = ?");
    $stm->execute([$id]);
    $oldPhoto = $stm->fetchColumn();
    $photo = $oldPhoto;

    // Validate inputs
    if ($name == '') {
        $err['product_name'] = 'Product name is required';
    }
    if ($brand == '') {
        $err['brand'] = 'Brand is required';
    }
    if ($price == '') {
        $err['price'] = 'Price is required';
    }
    else if(!is_money($price)) {
        $err['price'] = 'Invalid price format';
    }
    else if ($price < 0.01 ) {
        $err['price'] = 'Price must be greater than 0.01';
    }
    if ($stock < 0) {
        $err['stock'] = 'Stock cannot be negative';
    }

    if ($f) {
        if(!str_starts_with($f->type,'image/')){
            $err['edit_photo'] = 'Must be image';
        }
        else if($f->size > 1 * 1024 * 1024){
            $err['edit_photo'] = 'Maximum 1MB';
        } else {
            // Delete old photo if exists
            if ($oldPhoto) {
                $oldPhotoPath = realpath(__DIR__ . '/../images/Products/' . $oldPhoto);
                $productsDir = realpath(__DIR__ . '/../images/Products/');
                if ($oldPhotoPath && strpos($oldPhotoPath, $productsDir) === 0 && file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                }
            }
            $photo = save_photo($f, __DIR__ . '/../images/Products');
        }
    }

    // If no errors, update product in database
    if (!$err) {
        $stm = $_db->prepare("UPDATE product SET product_name = ?, brand = ?, price = ?, stock = ?, description = ?, photo = ? WHERE product_id = ?");
        $stm->execute([$name, $brand, $price, $stock, $description, $photo, $id]);
        temp('info', 'Product updated successfully.');
        redirect('/page/admin.php#products');
    }
}

// Handle delete product
if (isset($_POST['delete_product_id'])) {
    $pid = req('delete_product_id');
    // Delete photo
    $stm = $_db->prepare('SELECT photo FROM product WHERE product_id = ?');
    $stm->execute([$pid]);
    $photo = $stm->fetchColumn();
    if ($photo) {
        $photoPath = realpath(__DIR__ . '/../images/Products/' . $photo);
        $productsDir = realpath(__DIR__ . '/../images/Products/');
        if ($photoPath && strpos($photoPath, $productsDir) === 0 && file_exists($photoPath)) {
            @unlink($photoPath);
        }
    }
    $stm = $_db->prepare('DELETE FROM product WHERE product_id = ?');
    $stm->execute([$pid]);
    temp('info', 'Product deleted successfully.');
    redirect('/page/admin.php#products');
}


// User table sorting
$usort = $_GET['usort'] ?? 'id';
$uorder = $_GET['uorder'] ?? 'asc';
$uallowed = ['id','username','email','name','role'];
if (!in_array($usort, $uallowed)) $usort = 'id';
$uorder = strtolower($uorder) === 'desc' ? 'desc' : 'asc';
$users = $_db->query("SELECT * FROM user ORDER BY $usort $uorder")->fetchAll();

// Product table sorting
$sort = $_GET['sort'] ?? 'product_id';
$order = $_GET['order'] ?? 'asc';
$allowed = ['product_id','product_name','brand','price','stock'];
if (!in_array($sort, $allowed)) $sort = 'product_id';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';
$products = $_db->query("SELECT * FROM product ORDER BY $sort $order")->fetchAll();
$editId = $_GET['edit'] ?? null;

include '../head.php';
?>
<main>
    <h1>Admin Dashboard</h1>
    <p>Welcome, Admin <?= htmlspecialchars($_user->username) ?>!</p>

    <section id="users">
        <h2>User Management</h2>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th><?= usort_link('id','ID',$usort,$uorder) ?></th>
                <th><?= usort_link('username','Username',$usort,$uorder) ?></th>
                <th><?= usort_link('email','Email',$usort,$uorder) ?></th>
                <th><?= usort_link('name','Name',$usort,$uorder) ?></th>
                <th><?= usort_link('role','Role',$usort,$uorder) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td><?= htmlspecialchars($user->username) ?></td>
                <td><?= htmlspecialchars($user->email) ?></td>
                <td><?= htmlspecialchars($user->name) ?></td>
                <td><?= htmlspecialchars($user->role) ?></td>
                <td><!-- Extend: Edit/Delete/Set as Admin --></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section id="products" style="margin-top:40px;">
        <h2>Product Management</h2>
        <!-- Add Product Form -->
        <form method="post" enctype="multipart/form-data" style="margin-bottom:20px;">
            <fieldset>
                <legend>Add Product</legend>
                <?php html_text('product_name', "placeholder='Name' required"); ?>
                <?php html_text('brand', "placeholder='Brand' required"); ?>
                <?php html_text('category', "placeholder='Category' value='Shoes'"); ?>
                <?php html_text('size', "placeholder='Size' required data-upper" ); ?>
                <?php html_text('color', "placeholder='Color' required"); ?>
                <?php html_number('price', '', '', '0.01', "placeholder='Price' step='0.01' required"); ?>
                <?php html_number('stock', '', '', '', "placeholder='Stock' required"); ?>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <label for="photo" style="margin-bottom:0;white-space:nowrap;"><b>Photo:</b></label>
                <?php html_file('photo', 'image/*'); ?>
                <?= err('photo') ?>
            </div>
                <?php html_text('description', "placeholder='Description'"); ?>
                <button type="submit" name="add_product">Add</button>
            </fieldset>
        </form>

        <table class="admin-product-table">
            <tr>
                <th><?= sort_link('product_id','ID',$sort,$order) ?></th>
                <th><?= sort_link('product_name','Name',$sort,$order) ?></th>
                <th><?= sort_link('brand','Brand',$sort,$order) ?></th>
                <th><?= sort_link('price','Price',$sort,$order) ?></th>
                <th><?= sort_link('stock','Stock',$sort,$order) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($products as $product): ?>
            <tr>
            <?php if ($editId == $product->product_id): ?>
                <form method="post" enctype="multipart/form-data">
                <td><?= $product->product_id ?><input type="hidden" name="edit_product_id" value="<?= $product->product_id ?>"></td>
                <td><input type="text" name="edit_product_name" value="<?= htmlspecialchars($product->product_name) ?>" required></td>
                <td><input type="text" name="edit_brand" value="<?= htmlspecialchars($product->brand) ?>" required></td>
                <td><input type="number" name="edit_price" value="<?= $product->price ?>" step="0.01" required></td>
                <td><input type="number" name="edit_stock" value="<?= $product->stock ?>" required></td>
                <td class="edit-actions">
                    <div class="edit-form-fields">
                        <label><b>Photo:</b> <?php html_file('edit_photo', 'image/*'); ?></label>
                        <?php html_text('edit_category', "value='".encode($product->category)."' placeholder='Category'"); ?>
                        <?php html_text('edit_size', "value='".encode($product->size)."' placeholder='Size'"); ?>
                        <?php html_text('edit_color', "value='".encode($product->color)."' placeholder='Color'"); ?>
                        <?php html_text('edit_description', "value='".encode($product->description)."' placeholder='Description'"); ?>
                    </div>
                    <button type="submit">Save</button>
                    <button type="button" class="admin-btn cancel-btn" onclick="window.location.href='?sort=<?= $sort ?>&order=<?= $order ?>#products'">Cancel</button>
                </td>
                </form>
            <?php else: ?>
                <td><?= $product->product_id ?></td>
                <td><?= htmlspecialchars($product->product_name) ?></td>
                <td><?= htmlspecialchars($product->brand) ?></td>
                <td><?= number_format($product->price,2) ?></td>
                <td><?= $product->stock ?></td>
                <td style="display:flex;gap:8px;justify-content:center;align-items:center;">
                    <form method="get" style="display:inline;">
                        <input type="hidden" name="sort" value="<?= $sort ?>">
                        <input type="hidden" name="order" value="<?= $order ?>">
                        <input type="hidden" name="edit" value="<?= $product->product_id ?>">
                        <button type="submit" class="admin-btn edit-btn">Edit</button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        <input type="hidden" name="delete_product_id" value="<?= $product->product_id ?>">
                        <button type="submit" class="admin-btn delete-btn">Delete</button>
                    </form>
                </td>
            <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
<?php
include '../foot.php';
