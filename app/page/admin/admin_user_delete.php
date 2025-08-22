<?php
require '../../base.php';
auth('Admin');

if (is_post()) {
    $id = req('id');
    
    // Check if trying to delete self
    if ($id == $_user->id) {
        temp('error', 'You cannot delete your own account.');
        redirect('/page/admin/admin_user.php');
    }
    
    // Get user info first to delete photo
    $stm = $_db->prepare('SELECT photo FROM user WHERE id = ?');
    $stm->execute([$id]);
    $user = $stm->fetch();
    
    // Delete user photo if exists
    if ($user && $user->photo) {
        @unlink("../../images/userAvatar/" . $user->photo);
    }
    
    // Delete user
    $stm = $_db->prepare('DELETE FROM user WHERE id = ?');
    $stm->execute([$id]);

    temp('info', 'User deleted successfully.');
    redirect('/page/admin/admin_user.php');
} else {
    redirect('/page/admin/admin_user.php');
}
?>
