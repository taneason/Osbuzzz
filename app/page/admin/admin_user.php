<?php
require '../../base.php';
auth('Admin');

// User table sorting
$usort = $_GET['usort'] ?? 'id';
$uorder = $_GET['uorder'] ?? 'asc';
$uallowed = ['id','username','email','name','role'];
if (!in_array($usort, $uallowed)) $usort = 'id';
$uorder = strtolower($uorder) === 'desc' ? 'desc' : 'asc';

// Pagination setup
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;
if ($page < 1) $page = 1;

// Search logic
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE username LIKE ? OR email LIKE ? OR name LIKE ? OR role LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM user $where";
$countStm = $_db->prepare($countSql);
$countStm->execute($params);
$totalUsers = $countStm->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Ensure page is within valid range
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// Main query with pagination
$sql = "SELECT * FROM user $where ORDER BY $usort $uorder LIMIT $limit OFFSET $offset";
$stm = $_db->prepare($sql);
$stm->execute($params);
$users = $stm->fetchAll();


include '../../head.php';
?>

<main>
    <section id="users">
        <h1>User Management</h1>
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
            <input type="search" name="search" placeholder="Search by username, email, name or role" value="<?= htmlspecialchars($search) ?>" class="admin-form-input" style="width:300px;">
            <button type="submit" class="admin-btn" style="padding: 4px 16px;">Search</button>
            <?php if($search): ?><a href="admin_user.php" class="admin-btn" style="padding: 4px 16px; text-align:center; line-height:normal;">Clear</a><?php endif; ?>
        </form>
        <p>
            <button data-get="admin_user_add.php">Add Admin</button>
        </p>
        <table class="admin-product-table">
            <tr>
                <th><?= usort_link('id','ID',$usort,$uorder,$search,$page) ?></th>
                <th><?= usort_link('username','Username',$usort,$uorder,$search,$page) ?></th>
                <th><?= usort_link('email','Email',$usort,$uorder,$search,$page) ?></th>
                <th><?= usort_link('name','Name',$usort,$uorder,$search,$page) ?></th>
                <th><?= usort_link('role','Role',$usort,$uorder,$search,$page) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td><?= htmlspecialchars($user->username) ?></td>
                <td><?= htmlspecialchars($user->email) ?></td>
                <td><?= htmlspecialchars($user->name) ?></td>
                <td><?= htmlspecialchars($user->role) ?></td>
                <td style="align-items:center;">
                    <?php if($user->id !== $_user->id): ?>
                        <button data-confirm="Are you sure you want to delete this user?" data-post="admin_user_delete.php?id=<?= $user->id ?>">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:20px;text-align:center;">
            <?php
            $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
            $sortParam = "&usort=$usort&uorder=$uorder";
            ?>
            
            <?php if ($page > 1): ?>
                <a href="?page=1<?= $searchParam . $sortParam ?>" class="admin-btn">First</a>
                <a href="?page=<?= $page - 1 ?><?= $searchParam . $sortParam ?>" class="admin-btn">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="admin-btn" style="background:#007cba;color:white;"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $searchParam . $sortParam ?>" class="admin-btn"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $searchParam . $sortParam ?>" class="admin-btn">Next</a>
                <a href="?page=<?= $totalPages ?><?= $searchParam . $sortParam ?>" class="admin-btn">Last</a>
            <?php endif; ?>
            
            <span style="margin-left:20px;">Page <?= $page ?> of <?= $totalPages ?> (<?= $totalUsers ?> users)</span>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php
include '../../foot.php';