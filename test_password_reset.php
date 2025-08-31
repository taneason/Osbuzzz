<?php
// Test the password reset management query
require 'app/base.php';

try {
    echo "Testing password reset management query...\n";
    
    // Test the query from password_reset_management.php
    $stm = $_db->query('
        SELECT 
            pr.*,
            u.username,
            u.email,
            CASE 
                WHEN pr.expires_at < NOW() THEN "Expired"
                ELSE "Active"
            END as status
        FROM password_resets pr
        JOIN user u ON pr.user_id = u.id
        ORDER BY pr.created_at DESC
        LIMIT 5
    ');
    
    $results = $stm->fetchAll();
    echo "Query executed successfully!\n";
    echo "Found " . count($results) . " password reset records.\n";
    
    if (count($results) > 0) {
        echo "Sample data:\n";
        foreach ($results as $result) {
            echo "- User: {$result->username}, Email: {$result->email}, Status: {$result->status}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
