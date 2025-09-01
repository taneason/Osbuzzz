<?php
require_once '../../base.php';

// Check if user is admin
auth('Admin');

if (is_post()) {
    $id = (int)post('id');
    
    // Check if trying to delete self
    if ($id == $_user->id) {
        temp('error', 'You cannot delete your own account.');
        header("Location: admin_user.php");
        exit;
    }
    
    try {
        // Get user info first to delete photo
        $stmt = $_db->prepare('SELECT photo FROM user WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        // Delete user photo if exists
        if ($user && $user->photo) {
            @unlink(__DIR__ . "/../../images/userAvatar/" . $user->photo);
        }
        
        // Delete user - CASCADE constraints will handle related records
        $stmt = $_db->prepare('DELETE FROM user WHERE id = ?');
        $stmt->execute([$id]);

        temp('info', 'User deleted successfully.');
        
    } catch (Exception $e) {
        temp('error', 'Error deleting user: ' . $e->getMessage());
    }
    
    header("Location: admin_user.php");
    exit;
} else {
    header("Location: admin_user.php");
    exit;
}
?>
