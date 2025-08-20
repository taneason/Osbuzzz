<?php
require '../../base.php';
auth('Admin');

// Get product ID from URL
if (is_get()) {
    $product_id = req('id');

    // Fetch existing product data
    $stm = $_db->prepare("SELECT * FROM product WHERE product_id = ?");
    $stm->execute([$product_id]);
    $product = $stm->fetch();

    // If product not found, redirect back
    if (!$product) {
        temp('error', 'Product not found.');
        redirect('/page/admin/admin_product.php');
    }
    extract((array)$product);
    $_SESSION['photo'] = $product->photo;

}



if (is_post()) {
    $product_id = req('id');
    $name = req('product_name');
    $brand = req('brand');
    $category = req('category');
    $price = req('price');
    $description = req('description');
    $f = get_file('photo');
    $photo = $_SESSION['photo']; // Keep existing photo by default

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

    // --- Handle photo upload if new photo provided ---
    
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }

    // --- Update DB ---
    if (!$_err) {
        if ($f) {
            // Save new photo
            unlink("../../images/Products/$photo");
            $photo = save_photo($f, '../../images/Products');

        }
        $stm = $_db->prepare('UPDATE product 
                             SET product_name = ?, brand = ?, category = ?, 
                                 price = ?, description = ?, photo = ?
                             WHERE product_id = ?');
        $stm->execute([$name, $brand, $category, $price, $description, $photo, $product_id]);

        temp('info', 'Product updated successfully.');
        redirect('/page/admin/admin_product.php');
    }
}


include '../../head.php';
?>
<main>
    <h1 class="admin-form-title">Edit Product</h1>
    <form method="post" enctype="multipart/form-data" class="admin-edit-form" novalidate>
        <fieldset style="border-radius: 10px;">
        <div class="admin-form-grid">
            <!-- Product info -->
            <div class="admin-form-row admin-form-row-full">
                <label><b>Name</b></label>
                <?= html_text('product_name', "maxlength='100' placeholder='Product Name'"); ?>
                <?= err('product_name') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Brand</b></label>
                <?= html_text('brand', "maxlength='100' placeholder='Brand'"); ?>
                <?= err('brand') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Category</b></label>
                <?= html_select('category', $categories, '-- Select Shoes Type --', "class='admin-form-select'"); ?>
                <?= err('category') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Price</b></label>
                <?= html_text('price', "placeholder='Price' step='0.01' min='0.01'"); ?>
                <?= err('price') ?>
            </div>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Description</b></label>
                <?= html_textarea('description', "placeholder='Description'"); ?>
            </div>

            <!-- Current Photo Preview -->
            <?php if ($_SESSION['photo']): ?>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Current Photo</b></label>
                <img src="../../images/Products/<?=$_SESSION['photo']?>" 
                alt="Current product photo" style="max-width: 200px; max-height: 200px;">
            </div>
            <?php endif; ?>

            <!-- Photo Upload -->
            <div class="admin-form-row admin-form-row-full admin-form-photo-row">
                <label><b>New Photo</b> (Leave empty to keep current)</label>
                <?= html_file('photo', 'image/*'); ?>
                <?= err('photo') ?>
            </div>

            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" class="admin-btn edit-btn">Save Changes</button>
                <a href="admin_product.php" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';