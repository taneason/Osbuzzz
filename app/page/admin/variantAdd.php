<?php 
require '../../base.php';
auth('Admin');

if (is_post()) {
    $product_id = req('id');
    $size = req('size');
    //$color = req('color');
    $stock = req('stock');

    // --- Validation ---
    if ($stock < 0) $_err['stock'] = 'Stock cannot be negative';
    if ($size == '') $_err['size'] = 'Size is required';
    // if ($color == '') $_err['color'] = 'Color is required';

    

    // --- Insert into DB ---
    if (!$_err) {
        $stm = $_db->prepare("INSERT INTO product_variants (product_id, size, stock) 
                               VALUES (?, ?, ?)");
        $stm->execute([$product_id, $size, $stock]);
        temp('info', 'Variant added successfully.');
        redirect('/page/admin/admin_product_variants.php?id=' . $product_id);

    }
}

include '../../head.php';
?>
<main>
    <h1 class="admin-form-title">Add Product Variant</h1>
    <form method="post" enctype="multipart/form-data" class="admin-edit-form" novalidate>
        <fieldset style="border-radius: 10px;">
        <div class="admin-form-grid">
            <!-- Variant info -->
            
            <div class="admin-form-row">
                <label><b>Size</b></label>
                <?=html_select('size', $SIZES,'-- Select Size --'); ?>
                <?= err('size') ?>
            </div>
            
            <div class="admin-form-row">
                <label><b>Stock</b></label>
                <?=html_number('stock', '', '', '', "placeholder='Stock'"); ?>
                <?= err('stock') ?>
            </div>
            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" name="add_product" class="admin-btn edit-btn">Add</button>
                <a href="admin_product_variants.php?id=<?= htmlspecialchars(req('id')) ?>" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
