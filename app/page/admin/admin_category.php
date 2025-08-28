<?php
require '../../base.php';
auth('Admin');

// Handle form submission
if ($_POST) {
    $category_id = (int)$_POST['category_id'];
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];
    
    // Handle banner image upload
    $banner_image = null;
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../images/banners/';
        $file_extension = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $banner_image = $category_id . '-' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $banner_image;
            
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path)) {
                // Delete old banner image if exists
                if (!empty($_POST['old_banner'])) {
                    $old_file = $upload_dir . $_POST['old_banner'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
            } else {
                $banner_image = $_POST['old_banner']; // Keep old image if upload failed
            }
        }
    } else {
        $banner_image = $_POST['old_banner']; // Keep old image if no new upload
    }
    
    // Update category
    if ($category_id > 0) {
        $stm = $_db->prepare('UPDATE category SET category_name = ?, description = ?, banner_image = ? WHERE category_id = ?');
        $stm->execute([$category_name, $description, $banner_image, $category_id]);
    } else {
        // Add new category
        $stm = $_db->prepare('INSERT INTO category (category_name, category_slug, description, banner_image) VALUES (?, ?, ?, ?)');
        $slug = strtolower(str_replace(' ', '-', $category_name));
        $stm->execute([$category_name, $slug, $description, $banner_image]);
    }
    
    temp('info', 'Category updated successfully!');
    redirect('/page/admin/admin_category.php');
}

// Category table sorting
$sort = $_GET['sort'] ?? 'category_id';
$order = $_GET['order'] ?? 'asc';
$allowed = ['category_id','category_name','description','created_at'];
if (!in_array($sort, $allowed)) $sort = 'category_id';
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// Search logic
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE category_name LIKE ? OR description LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get all categories with search and sorting
$sql = "SELECT * FROM category $where ORDER BY $sort $order";
$stm = $_db->prepare($sql);
$stm->execute($params);
$categories = $stm->fetchAll();

include '../../head.php';
?>

<main>
    <section id="categories">
        <h1>Category Management</h1>
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
            <input type="search" name="search" placeholder="Search by name or description" value="<?= htmlspecialchars($search) ?>" class="admin-form-input" style="width:300px;">
            <button type="submit" class="admin-btn" style="padding: 4px 16px;">Search</button>
            <?php if($search): ?><a href="admin_category.php" class="admin-btn" style="padding: 4px 16px; text-align:center; line-height:normal;">Clear</a><?php endif; ?>
        </form>
        <p>
            <button data-get="admin_category_add.php">Add Category</button>
        </p>
        <table class="admin-product-table">
            <tr>
                <th>Banner</th>
                <th><?= sort_link('category_id','ID',$sort,$order,$search,1) ?></th>
                <th><?= sort_link('category_name','Name',$sort,$order,$search,1) ?></th>
                <th><?= sort_link('description','Description',$sort,$order,$search,1) ?></th>
                <th><?= sort_link('created_at','Created',$sort,$order,$search,1) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td style="text-align: center;">
                    <?php if ($category->banner_image): ?>
                    <img src="../../images/banners/<?= htmlspecialchars($category->banner_image) ?>" 
                         alt="Banner" 
                         style="width: 60px; height: 30px; object-fit: cover; border-radius: 4px;">
                    <?php else: ?>
                    <span style="color: #999; font-size: 12px;">No banner</span>
                    <?php endif; ?>
                </td>
                <td><?= $category->category_id ?></td>
                <td><?= htmlspecialchars($category->category_name) ?></td>
                <td><?= htmlspecialchars($category->description ?: 'No description') ?></td>
                <td><?= date('Y-m-d', strtotime($category->created_at)) ?></td>
                <td>
                    <button data-get="admin_category_edit.php?id=<?= $category->category_id ?>">Edit</button>
                    <button data-confirm="Are you sure you want to delete this category?" 
                            data-post="admin_category_delete.php?id=<?= $category->category_id ?>">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

<?php
include '../../foot.php';
?>
