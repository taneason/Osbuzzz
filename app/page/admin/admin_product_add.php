<?php 
require '../../base.php';
auth('Admin');

if (is_post() && isset($_POST['add_product'])) {
    $name = req('product_name');
    $brand = req('brand');
    $category = req('category');
    $price = req('price');
    $description = req('description');
    $photo = null;

    // --- Validation ---
    if ($name == '') $_err['product_name'] = 'Product name is required';
    if ($brand == '') $_err['brand'] = 'Brand is required';
    if ($category == '') $_err['category'] = 'Category is required';
    if ($price == '') {
        $_err['price'] = 'Price is required';
    } else if(!is_money($price)) {
        $_err['price'] = 'Invalid price format';
    } else if ($price < 0.01) {
        $_err['price'] = 'Price must be greater than 0.01';
    }
    

    // --- Handle photo upload ---
    $f = get_file('photo');
    if (!$f) {
        $_err['photo'] = 'Required';
    }
    else if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }

    // --- Insert into DB ---
    if (!$_err) {
        // Save photo
        $photo = save_photo($f, __DIR__ . '/../../images/Products');

        // 1. Insert into product
        $stm = $_db->prepare("INSERT INTO product (product_name, brand, category, price, description, photo) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stm->execute([$name, $brand, $category, $price, $description, $photo]);
        
        temp('info', 'Product added successfully.');
        redirect('/page/admin/admin_product.php');
    }
}

include '../../head.php';
?>
<main>
    <h1 class="admin-form-title">Add Product</h1>
    <form method="post" enctype="multipart/form-data" class="admin-edit-form" novalidate>
        <fieldset style="border-radius: 10px;">
        <div class="admin-form-grid">
            <!-- Product info -->
            <div class="admin-form-row admin-form-row-full">
                <label><b>Name</b></label>
                <input type="text" name="product_name" placeholder="Name" value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>" />
                <?= err('product_name') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Brand</b></label>
                <input type="text" name="brand" placeholder="Brand" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>" />
                <?= err('brand') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Category</b></label>
                <?= html_select('category', $categories, '-- Select Shoes Type --', "class='admin-form-select'"); ?>
                <?= err('category') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Price</b></label>
                <input type="number" name="price" placeholder="Price" step="0.01" min="0.01" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" />
                <?= err('price') ?>
            </div>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Description</b></label>
                <textarea name="description" placeholder="Description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Photo -->
            <div class="admin-form-row admin-form-row-full admin-form-photo-row">
                <label><b>Photo</b></label>
                <?php html_file('photo', 'image/*'); ?>
                <?= err('photo') ?>
            </div>

            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" name="add_product" class="admin-btn edit-btn">Add</button>
                <a href="admin_product.php" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
