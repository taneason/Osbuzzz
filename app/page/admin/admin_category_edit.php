<?php
require '../../base.php';
auth('Admin');

$category_id = (int)($_GET['id'] ?? 0);

if (!$category_id) {
    redirect('/page/admin/admin_category.php');
}

// Get category details
$category = get_category_by_id($category_id);

if (!$category) {
    redirect('/page/admin/admin_category.php');
}

$_err = [];

if (is_post()) {
    $category_name = post('category_name');
    $description = post('description');
    
    // Validation
    if (!$category_name) {
        $_err['category_name'] = 'Category name is required';
    }
    
    if (!$_err) {
        // Handle banner image upload
        $banner_image = $category->banner_image; // Keep existing by default
        
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../images/banners/';
            $file_extension = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                // Delete old banner if exists
                if ($category->banner_image) {
                    $old_file = $upload_dir . $category->banner_image;
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                
                $banner_image = $category_id . '-' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $banner_image;
                move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path);
            }
        }
        
        // Update category
        $stm = $_db->prepare('UPDATE category SET category_name = ?, description = ?, banner_image = ? WHERE category_id = ?');
        $stm->execute([$category_name, $description, $banner_image, $category_id]);
        
        temp('info', 'Category updated successfully!');
        redirect('/page/admin/admin_category.php');
    }
} else {
    // Pre-fill form with existing data
    $_POST['category_name'] = $category->category_name;
    $_POST['description'] = $category->description;
}

include '../../head.php';
?>

<main>
    <?php if ($_err): ?>
        <div style="color: red; margin-bottom: 20px; padding: 10px; border: 1px solid red; border-radius: 5px;">
            <?php foreach ($_err as $error): ?>
                <div><?= $error ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <h1 class="admin-form-title">Edit Category</h1>
    <form method="post" enctype="multipart/form-data" class="admin-edit-form" novalidate>
        <fieldset style="border-radius: 10px;">
        <div class="admin-form-grid">
            <!-- Category info -->
            <div class="admin-form-row admin-form-row-full">
                <label><b>Category Name</b></label>
                <?= html_text('category_name', 'maxlength="50" placeholder="Category Name" class="admin-form-input" required') ?>
            </div>
            
            <div class="admin-form-row admin-form-row-full">
                <label><b>Description</b></label>
                <textarea name="description" class="admin-form-input" rows="3" 
                          placeholder="Category description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="admin-form-row admin-form-row-full">
                <label><b>Banner Image</b></label>
                <?php if ($category->banner_image): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../../images/banners/<?= htmlspecialchars($category->banner_image) ?>" 
                         alt="Current banner" style="max-width: 200px; height: auto; border-radius: 4px;">
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">
                        Current: <?= htmlspecialchars($category->banner_image) ?>
                    </div>
                </div>
                <?php endif; ?>
                <input type="file" name="banner_image" accept="image/*" class="admin-form-input">
                <small style="color: #666;">Supported formats: JPG, PNG, GIF, WebP, SVG. Leave empty to keep current image.</small>
            </div>
            
            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" class="admin-btn edit-btn">Save Changes</button>
                <a href="admin_category.php" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
?>
