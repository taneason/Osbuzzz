<?php
require '../../base.php';
auth('Admin');

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    redirect('/page/admin/admin_category.php');
}

// Check if category exists
$stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
$stm->execute([$id]);
$category = $stm->fetch();

if (!$category) {
    temp('info', 'Category not found.');
    redirect('/page/admin/admin_category.php');
}

// Check if there are products in this category
$stm = $_db->prepare('SELECT COUNT(*) as count FROM product WHERE category_id = ?');
$stm->execute([$id]);
$productCount = $stm->fetchColumn();

if ($productCount > 0) {
    temp('info', "Cannot delete category '{$category->category_name}' because it has {$productCount} product(s). Please move or delete the products first.");
    redirect('/page/admin/admin_category.php');
}

// Delete banner image if exists
if ($category->banner_image) {
    $banner_file = '../../images/banners/' . $category->banner_image;
    if (file_exists($banner_file)) {
        unlink($banner_file);
    }
}

// Delete category
$stm = $_db->prepare('DELETE FROM category WHERE category_id = ?');
$stm->execute([$id]);

temp('info', "Category '{$category->category_name}' deleted successfully!");
redirect('/page/admin/admin_category.php');
?>
