<?php
// Simple test script to check user table structure
require 'app/base.php';

try {
    echo "Testing user table structure...\n";
    $stm = $_db->query("DESCRIBE user");
    $columns = $stm->fetchAll(PDO::FETCH_COLUMN);
    
    echo "User table columns:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\nTesting user data...\n";
    $stm = $_db->query("SELECT id, username, email, name FROM user LIMIT 3");
    $users = $stm->fetchAll();
    
    echo "Sample user data:\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}, Username: {$user->username}, Email: {$user->email}, Name: '{$user->name}'\n";
    }
    
    echo "\nTesting password_resets join...\n";
    $stm = $_db->query("
        SELECT pr.id, pr.user_id, u.username, u.name, u.email
        FROM password_resets pr
        JOIN user u ON pr.user_id = u.id
        LIMIT 3
    ");
    $result = $stm->fetchAll();
    
    echo "Found " . count($result) . " password reset records with user info.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
