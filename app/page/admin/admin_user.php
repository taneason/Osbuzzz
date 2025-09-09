<?php
require '../../base.php';
auth('Admin');

// User table sorting
$usort = $_GET['usort'] ?? 'id';
$uorder = $_GET['uorder'] ?? 'asc';
$uallowed = ['id','username','email','role','created_at','loyalty_points'];
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
    $where = "WHERE username LIKE ? OR email LIKE ? OR role LIKE ?";
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

// Main query with pagination - include loyalty points calculation
$sql = "SELECT u.*, COALESCE(SUM(lt.points), 0) as loyalty_points 
        FROM user u 
        LEFT JOIN loyalty_transactions lt ON u.id = lt.user_id 
        $where 
        GROUP BY u.id 
        ORDER BY $usort $uorder 
        LIMIT $limit OFFSET $offset";
$stm = $_db->prepare($sql);
$stm->execute($params);
$users = $stm->fetchAll();

include '../../head.php';
?>

<main>
    <section id="users">
        <h1>User Management</h1>
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
            <input type="search" name="search" placeholder="Search by username, email or role" value="<?= htmlspecialchars($search) ?>" class="admin-form-input" style="width:300px;">
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
                <th>Email Verified</th>
                <th><?= usort_link('role','Role',$usort,$uorder,$search,$page) ?></th>
                <th><?= usort_link('loyalty_points','Loyalty Points',$usort,$uorder,$search,$page) ?></th>
                <th><?= usort_link('created_at','Registration Date',$usort,$uorder,$search,$page) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td><?= htmlspecialchars($user->username) ?></td>
                <td><?= htmlspecialchars($user->email) ?></td>
                <td>
                    <?php if (isset($user->email_verified) && $user->email_verified == 1): ?>
                        <span style="color: #28a745; font-weight: bold;">✓ Verified</span>
                    <?php else: ?>
                        <span style="color: #dc3545; font-weight: bold;">✗ Not Verified</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user->role) ?></td>
                <td>
                    <span style="color: #007bff; font-weight: bold;"><?= number_format($user->loyalty_points, 2) ?> pts</span>
                </td>
                <td><?= date('Y-m-d H:i', strtotime($user->created_at)) ?></td>
                <td>
                    <?php if($user->id !== $_user->id): ?>
                        <button onclick="toggleUserStatus(<?= $user->id ?>, '<?= htmlspecialchars($user->username) ?>')"><?= isset($user->status) && $user->status === 'banned' ? 'Activate' : 'Ban' ?></button>
                        <button onclick="deleteUser(<?= $user->id ?>, '<?= htmlspecialchars($user->username) ?>')">Delete</button>
                    <?php else: ?>
                        <span style="color: #999;">Current User</span>
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


<script>
// Toggle user status (ban/activate)
function toggleUserStatus(userId, username) {
    const confirmMsg = `Are you sure you want to ban/activate ${username}?`;
    
    if (confirm(confirmMsg)) {
        const formData = new FormData();
        formData.append('user_id', userId);
        
        fetch('admin_user_toggle_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User status updated successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to update user status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update user status');
        });
    }
}

// Delete user
function deleteUser(userId, username) {
    const confirmMsg = `Are you sure you want to delete user '${username}'? This action cannot be undone.`;
    
    if (confirm(confirmMsg)) {
        const formData = new FormData();
        formData.append('id', userId);
        
        fetch('admin_user_delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                alert('User deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete user');
        });
    }
}
</script>

<?php
include '../../foot.php';