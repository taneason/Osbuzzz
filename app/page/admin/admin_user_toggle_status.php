<?php
require_once '../../base.php';

// Check if user is admin
if (!$_user || $_user->role !== 'Admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = (int)$_POST['user_id'];
        
        // Prevent admin from banning themselves
        if ($user_id == $_user->id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Cannot ban/activate yourself']);
            exit;
        }
        
        // Get current user status
        $stmt = $_db->prepare("SELECT status FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        // Toggle status
        $new_status = ($user->status === 'active') ? 'banned' : 'active';
        
        $stmt = $_db->prepare("UPDATE user SET status = ? WHERE id = ?");
        $result = $stmt->execute([$new_status, $user_id]);
        
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'User status updated successfully',
                'new_status' => $new_status
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
        }
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
