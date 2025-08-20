<?php
require '../../base.php';
auth('Admin');


if (is_post()) {
    $id = req('id');

    // Delete photo
    $stm = $_db->prepare('SELECT photo FROM product WHERE product_id = ?');
    $stm->execute([$id]);
    $photo = $stm->fetchColumn();
    unlink("../../images/Products/$photo");

    $stm = $_db->prepare('DELETE FROM product_variants WHERE product_id = ?');
    $stm->execute([$id]);
    
    $stm = $_db->prepare('DELETE FROM product WHERE product_id = ?');
    $stm->execute([$id]);
    temp('info', 'Product deleted successfully.');
}

redirect('admin_product.php');

?>
