<?php 
require '../../base.php';
auth('Admin');

if (is_post() && isset($_POST['add_product'])) {
    $name = req('product_name');
    $brand = req('brand');
    $category_id = req('category');
    $price = req('price');
    $description = req('description');
    $status = req('status');
    $photo = null;

    // --- Validation ---
    if ($name == '') $_err['product_name'] = 'Product name is required';
    if ($brand == '') $_err['brand'] = 'Brand is required';
    if ($category_id == '') $_err['category'] = 'Category is required';
    if ($status == '') $_err['status'] = 'Status is required';
    else if (!in_array($status, ['active', 'inactive'])) $_err['status'] = 'Invalid status';
    if ($price == '') {
        $_err['price'] = 'Price is required';
    } else if(!is_money($price)) {
        $_err['price'] = 'Invalid price format';
    } else if ($price < 0.01) {
        $_err['price'] = 'Price must be greater than 0.01';
    }
    

    // --- Handle photo upload ---
    $main_photo = null;
    $additional_photos = [];
    
    // Handle main photo
    $f = get_file('photo');
    if (!$f) {
        $_err['photo'] = 'Main photo is required';
    }
    else if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Main photo must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Main photo maximum 1MB';
    }
    else {
        // Check file extension
        $file_extension = strtolower(pathinfo($f->name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed_extensions)) {
            $_err['photo'] = 'Main photo must be JPG, PNG, GIF, or WebP format';
        } else {
            $main_photo = $f;
        }
    }
    
    // Handle additional photos
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
                    // Check file extension
                    $file_extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($file_extension, $allowed_extensions)) {
                        $_err['additional_photos'] = 'Additional photos must be JPG, PNG, GIF, or WebP format';
                        break;
                    } else {
                        $additional_photos[] = $file;
                    }
                }
            }
        }
    }

    // --- Insert into DB ---
    if (!$_err) {
        try {
            $_db->beginTransaction();
            
            // Save main photo
            $main_photo_filename = save_photo_with_format($main_photo, __DIR__ . '/../../images/Products');

            // 1. Insert into product
            $stm = $_db->prepare("INSERT INTO product (product_name, brand, category_id, price, description, photo, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stm->execute([$name, $brand, $category_id, $price, $description, $main_photo_filename, $status]);
            
            $product_id = $_db->lastInsertId();
            
            // 2. Insert main photo into product_photos
            $stm = $_db->prepare("INSERT INTO product_photos (product_id, photo_filename, is_main_photo, display_order) 
                                  VALUES (?, ?, 1, 0)");
            $stm->execute([$product_id, $main_photo_filename]);
            
            // 3. Insert additional photos
            $order = 1;
            foreach ($additional_photos as $photo) {
                $photo_filename = save_photo_with_format($photo, __DIR__ . '/../../images/Products');
                $stm = $_db->prepare("INSERT INTO product_photos (product_id, photo_filename, is_main_photo, display_order) 
                                      VALUES (?, ?, 0, ?)");
                $stm->execute([$product_id, $photo_filename, $order]);
                $order++;
            }
            
            $_db->commit();
            temp('info', 'Product added successfully with ' . (count($additional_photos) + 1) . ' photos.');
            redirect('/page/admin/admin_product.php');
            
        } catch (Exception $e) {
            $_db->rollBack();
            $_err['general'] = 'Error adding product: ' . $e->getMessage();
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
            <div class="admin-form-row">
                <label><b>Status</b></label>
                <select name="status" class="admin-form-select">
                    <option value="">-- Select Status --</option>
                    <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active (Visible to users)</option>
                    <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive (Hidden from users)</option>
                </select>
                <?= err('status') ?>
            </div>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Description</b></label>
                <textarea name="description" placeholder="Description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Photos Section -->
            <div class="admin-form-row admin-form-row-full">
                <h3 class="admin-form-subtitle">Product Photos</h3>
            </div>
            
            <!-- Main Photo -->
            <div class="admin-form-row admin-form-row-full admin-form-photo-row">
                <label><b>Main Photo *</b></label>
                <?php html_file('photo', 'image/*'); ?>
                <?= err('photo') ?>
                <small style="color: #666; font-size: 0.9em;">Supported formats: JPG, PNG, GIF, WebP. This will be the main product photo displayed in lists</small>
            </div>

            <!-- Additional Photos -->
            <div class="admin-form-row admin-form-row-full admin-form-photo-row">
                <label><b>Additional Photos (Optional)</b></label>
                <input type="file" name="additional_photos[]" accept="image/*" multiple style="padding: 6px;">
                <?= err('additional_photos') ?>
                <small style="color: #666; font-size: 0.9em;">Supported formats: JPG, PNG, GIF, WebP. You can select multiple photos (Ctrl+Click). Maximum 1MB each.</small>
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
