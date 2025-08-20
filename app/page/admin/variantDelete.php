<?php
require '../../base.php';
auth('Admin');


/*if (is_post()) {
    $id = req('vid');
    $product_id = req('id');
    $stm = $_db->prepare('DELETE FROM product_variants WHERE variant_id = ?');
    $stm->execute([$id]);

    temp('info', 'Variant deleted successfully.');
    redirect("admin_product_variants.php?id=$product_id");
}
*/
if (is_post()) {
    $id = req('vid');
    $stm = $_db->prepare('SELECT product_id FROM product_variants WHERE variant_id = ?');
    $stm->execute([$id]);
    $variant = $stm->fetch();
    $product_id = $variant ? $variant->product_id : '';

    $stm = $_db->prepare('DELETE FROM product_variants WHERE variant_id = ?');
    $stm->execute([$id]);

    temp('info', 'Variant deleted successfully.');
    redirect("admin_product_variants.php?id=" . $product_id);
} else {
    redirect("admin_product_variants.php");
}
?>
