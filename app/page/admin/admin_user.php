<?php
require '../../base.php';
auth('Admin');

// User table sorting
$usort = $_GET['usort'] ?? 'id';
$uorder = $_GET['uorder'] ?? 'asc';
$uallowed = ['id','username','email','name','role'];
if (!in_array($usort, $uallowed)) $usort = 'id';
$uorder = strtolower($uorder) === 'desc' ? 'desc' : 'asc';
$users = $_db->query("SELECT * FROM user ORDER BY $usort $uorder")->fetchAll();

include '../../head.php';
?>

<main>

    <section id="users">
        <h1>User Management</h1>
        <table class="admin-product-table">
            <tr>
                <th><?= usort_link('id','ID',$usort,$uorder) ?></th>
                <th><?= usort_link('username','Username',$usort,$uorder) ?></th>
                <th><?= usort_link('email','Email',$usort,$uorder) ?></th>
                <th><?= usort_link('name','Name',$usort,$uorder) ?></th>
                <th><?= usort_link('role','Role',$usort,$uorder) ?></th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td><?= htmlspecialchars($user->username) ?></td>
                <td><?= htmlspecialchars($user->email) ?></td>
                <td><?= htmlspecialchars($user->name) ?></td>
                <td><?= htmlspecialchars($user->role) ?></td>
                <td><!-- Extend: Edit/Delete/Set as Admin --></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>

<?php
include '../../foot.php';