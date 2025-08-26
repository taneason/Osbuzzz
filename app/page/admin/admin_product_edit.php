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
    
    // Fetch all photos for this product
    $stm = $_db->prepare("SELECT * FROM product_photos WHERE product_id = ? ORDER BY is_main_photo DESC, display_order ASC");
    $stm->execute([$product_id]);
    $product_photos = $stm->fetchAll();
    
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

    // --- Handle main photo upload if new photo provided ---
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }
    
    // --- Handle additional photos ---
    $additional_photos = [];
    if (isset($_FILES['additional_photos']) && is_array($_FILES['additional_photos']['name'])) {
        $additional_files = $_FILES['additional_photos'];
        for ($i = 0; $i < count($additional_files['name']); $i++) {
            if (!empty($additional_files['name'][$i])) {
                $file = (object)[
                    'name' => $additional_files['name'][$i],
                    'type' => $additional_files['type'][$i],
                    'tmp_name' => $additional_files['tmp_name'][$i],
                    'error' => $additional_files['error'][$i],
                    'size' => $additional_files['size'][$i]
                ];
                
                if (!str_starts_with($file->type, 'image/')) {
                    $_err['additional_photos'] = 'All additional photos must be images';
                    break;
                }
                else if ($file->size > 1 * 1024 * 1024) {
                    $_err['additional_photos'] = 'Additional photos maximum 1MB each';
                    break;
                }
                else {
                    $additional_photos[] = $file;
                }
            }
        }
    }
    
    // --- Handle photo deletion ---
    $photos_to_delete = req('delete_photos');
    if ($photos_to_delete && is_array($photos_to_delete)) {
        // We'll process deletions in the update section
    }

    // --- Update DB ---
    if (!$_err) {
        try {
            $_db->beginTransaction();
            
            // Update main photo if provided
            if ($f) {
                // Delete old main photo file
                if ($photo && file_exists("../../images/Products/$photo")) {
                    unlink("../../images/Products/$photo");
                }
                $photo = save_photo($f, '../../images/Products');
                
                // Update main photo in product_photos table
                $stm = $_db->prepare('UPDATE product_photos SET photo_filename = ? WHERE product_id = ? AND is_main_photo = 1');
                $stm->execute([$photo, $product_id]);
            }
            
            // Update product info
            $stm = $_db->prepare('UPDATE product 
                                 SET product_name = ?, brand = ?, category = ?, 
                                     price = ?, description = ?, photo = ?
                                 WHERE product_id = ?');
            $stm->execute([$name, $brand, $category, $price, $description, $photo, $product_id]);
            
            // Delete selected photos
            if ($photos_to_delete && is_array($photos_to_delete)) {
                foreach ($photos_to_delete as $photo_id) {
                    // Get photo filename before deleting
                    $stm = $_db->prepare('SELECT photo_filename FROM product_photos WHERE photo_id = ? AND product_id = ? AND is_main_photo = 0');
                    $stm->execute([$photo_id, $product_id]);
                    $photo_to_delete = $stm->fetch();
                    
                    if ($photo_to_delete) {
                        // Delete file
                        if (file_exists("../../images/Products/" . $photo_to_delete->photo_filename)) {
                            unlink("../../images/Products/" . $photo_to_delete->photo_filename);
                        }
                        
                        // Delete from database
                        $stm = $_db->prepare('DELETE FROM product_photos WHERE photo_id = ? AND product_id = ? AND is_main_photo = 0');
                        $stm->execute([$photo_id, $product_id]);
                    }
                }
            }
            
            // Add new additional photos
            if (!empty($additional_photos)) {
                // Get current max display order
                $stm = $_db->prepare('SELECT COALESCE(MAX(display_order), 0) as max_order FROM product_photos WHERE product_id = ?');
                $stm->execute([$product_id]);
                $max_order = $stm->fetch()->max_order;
                
                $order = $max_order + 1;
                foreach ($additional_photos as $photo_file) {
                    $photo_filename = save_photo($photo_file, __DIR__ . '/../../images/Products');
                    $stm = $_db->prepare("INSERT INTO product_photos (product_id, photo_filename, is_main_photo, display_order) 
                                          VALUES (?, ?, 0, ?)");
                    $stm->execute([$product_id, $photo_filename, $order]);
                    $order++;
                }
            }
            
            $_db->commit();
            temp('info', 'Product updated successfully.');
            redirect('/page/admin/admin_product.php');
            
        } catch (Exception $e) {
            $_db->rollBack();
            $_err['general'] = 'Error updating product: ' . $e->getMessage();
        }
    }
}


include '../../head.php';
?>
<main>
    <?php if (isset($_err['general'])): ?>
        <div style="color: red; margin-bottom: 20px; padding: 10px; border: 1px solid red; border-radius: 5px;">
            <?= $_err['general'] ?>
        </div>
    <?php endif; ?>
    
    <h1 class="admin-form-title">Edit Product</h1>
    <form method="post" enctype="multipart/form-data" class="admin-edit-form" novalidate>
        <input type="hidden" name="id" value="<?= $product_id ?>">
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

            <!-- Photos Section -->
            <div class="admin-form-row admin-form-row-full">
                <h3 class="admin-form-subtitle">Product Photos</h3>
            </div>

            <!-- Current Photos Display -->
            <?php if (!empty($product_photos)): ?>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Current Photos</b></label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 10px;">
                    <?php foreach ($product_photos as $photo): ?>
                    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; text-align: center;">
                        <img src="../../images/Products/<?= $photo->photo_filename ?>" 
                             alt="Product photo" 
                             style="max-width: 100%; max-height: 150px; object-fit: cover; border-radius: 4px;">
                        <div style="margin-top: 8px;">
                            <?php if ($photo->is_main_photo): ?>
                                <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8em;">Main Photo</span>
                            <?php else: ?>
                                <label style="font-size: 0.9em;">
                                    <input type="checkbox" name="delete_photos[]" value="<?= $photo->photo_id ?>">
                                    Delete this photo
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- New Main Photo Upload -->
            <div class="admin-form-row admin-form-row-full admin-form-photo-row">
                <label><b>Update Main Photo</b> (Leave empty to keep current)</label>
                <?= html_file('photo', 'image/*'); ?>
                <?= err('photo') ?>
                <small style="color: #666; font-size: 0.9em;">This will replace the current main photo</small>
            </div>

            <!-- Additional Photos Upload -->
            <div class="admin-form-row admin-form-row-full admin-form-photo-row">
                <label><b>Add More Photos</b></label>
                <input type="file" name="additional_photos[]" accept="image/*" multiple style="padding: 6px;">
                <?= err('additional_photos') ?>
                <small style="color: #666; font-size: 0.9em;">Select multiple photos to add (Ctrl+Click). Maximum 1MB each.</small>
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