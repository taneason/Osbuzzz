<?php
require '../../base.php';
auth('Admin');

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
        $banner_image = null;
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../images/banners/';
            $file_extension = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $banner_image = time() . '.' . $file_extension;
                $upload_path = $upload_dir . $banner_image;
                move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path);
            }
        }
        
        // Add new category
        $stm = $_db->prepare('INSERT INTO category (category_name, category_slug, description, banner_image) VALUES (?, ?, ?, ?)');
        $slug = strtolower(str_replace(' ', '-', $category_name));
        $stm->execute([$category_name, $slug, $description, $banner_image]);
        
        temp('info', 'Category added successfully!');
        redirect('/page/admin/admin_category.php');
    }
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
    
    <h1 class="admin-form-title">Add Category</h1>
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
                <input type="file" name="banner_image" accept="image/*" class="admin-form-input">
                <small style="color: #666;">Supported formats: JPG, PNG, GIF, WebP, SVG</small>
            </div>
            
            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" class="admin-btn edit-btn">Add</button>
                <a href="admin_category.php" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
?>
