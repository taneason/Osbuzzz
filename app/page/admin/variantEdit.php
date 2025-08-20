<?php 
require '../../base.php';
auth('Admin');

if (is_get()) {
    $variant_id = req('vid');
    $product_id = req('id');
    // Fetch existing product data
    $stm = $_db->prepare("SELECT * FROM product_variants WHERE variant_id = ?");
    $stm->execute([$variant_id]);
    $variant = $stm->fetch();

    // If variant not found, redirect back
    if (!$variant) {
        temp('error', 'Variant not found.');
        redirect("/page/admin/admin_product_variants.php?id=$product_id");
    }
    extract((array)$variant);

    
}

if (is_post()) {
    $size = req('size');
    $stock = req('stock');
    $variant_id = req('vid');
    $product_id = req('id');
    // --- Validation ---
    if ($stock < 0) $_err['stock'] = 'Stock cannot be negative';
    if ($size == '') $_err['size'] = 'Size is required';

    // --- Update DB ---
    if (!$_err) {
        $stm = $_db->prepare("UPDATE product_variants SET size = ?, stock = ? WHERE variant_id = ?");
        $stm->execute([$size, $stock, $variant_id]);
        temp('info', 'Variant updated successfully.');
        redirect("/page/admin/admin_product_variants.php?id=$product_id");
    }
} 

include '../../head.php';
?>
<main>
    <h1 class="admin-form-title">Edit Stock</h1>
    <form method="post" enctype="multipart/form-data" class="admin-edit-form" novalidate>
        <fieldset style="border-radius: 10px;">
        <div class="admin-form-grid">
            <!-- Variant info -->
            <div class="admin-form-row admin-form-row-full">
                <h3 class="admin-form-subtitle ">Stock</h3> 
            </div>

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
                <button type="submit" class="admin-btn edit-btn">Save</button>
                <a href="admin_product_variants.php?id=<?= htmlspecialchars($product_id) ?>" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
