<?php
require '../../base.php';

auth();

// Get user's saved addresses
$stm = $_db->prepare('SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$stm->execute([$_user->id]);
$addresses = $stm->fetchAll();

// Handle actions
if (is_post()) {
    $action = req('action');
    
    if ($action == 'delete_address') {
        $address_id = req('address_id');
        
        $stm = $_db->prepare('DELETE FROM customer_addresses WHERE address_id = ? AND user_id = ?');
        $stm->execute([$address_id, $_user->id]);
        
        temp('success', 'Address deleted successfully');
        redirect('/page/user/addresses.php');
    }
    
    if ($action == 'set_default') {
        $address_id = req('address_id');
        
        // Remove default from all addresses
        $stm = $_db->prepare('UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?');
        $stm->execute([$_user->id]);
        
        // Set new default
        $stm = $_db->prepare('UPDATE customer_addresses SET is_default = 1 WHERE address_id = ? AND user_id = ?');
        $stm->execute([$address_id, $_user->id]);
        
        temp('success', 'Default address updated');
        redirect('/page/user/addresses.php');
    }
}

$_title = 'My Addresses';
include '../../head.php';
?>

<main class="addresses-page">
    <div class="container">
        <div class="page-header">
            <h1>My Addresses</h1>
            <div class="header-actions">
                <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
                <a href="add_address.php" class="btn btn-primary">+ Add New Address</a>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (temp('success')): ?>
            <div class="alert alert-success">
                <i>✓</i> <?= temp('success') ?>
            </div>
        <?php endif; ?>
        
        <!-- Addresses List -->
        <div class="addresses-list">
            <?php if (empty($addresses)): ?>
                <div class="empty-addresses">
                    <div class="empty-icon">📍</div>
                    <h3>No addresses found</h3>
                    <p>You haven't added any addresses yet.</p>
                    <p>Add your first address to make checkout faster and easier!</p>
                    <a href="add_address.php" class="btn btn-primary btn-lg">Add Your First Address</a>
                </div>
            <?php else: ?>
                <div class="addresses-grid">
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-card <?= $address->is_default ? 'default' : '' ?>">
                            <div class="address-header">
                                <h3><?= encode($address->address_name) ?></h3>
                                <?php if ($address->is_default): ?>
                                    <span class="default-badge">
                                        <i>⭐</i> Default
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="address-details">
                                <div class="contact-info">
                                    <strong><?= encode($address->first_name . ' ' . $address->last_name) ?></strong>
                                    <?php if ($address->company): ?>
                                        <div class="company"><?= encode($address->company) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="address-info">
                                    <div><?= encode($address->address_line_1) ?></div>
                                    <?php if ($address->address_line_2): ?>
                                        <div><?= encode($address->address_line_2) ?></div>
                                    <?php endif; ?>
                                    <div><?= encode($address->city . ', ' . get_state_name($address->state) . ' ' . $address->postal_code) ?></div>
                                </div>
                                
                                <div class="contact-details">
                                    <div class="phone">📞 <?= encode($address->phone) ?></div>
                                </div>
                            </div>
                            
                            <div class="address-actions">
                                <a href="edit_address.php?id=<?= $address->address_id ?>" class="btn btn-sm btn-primary" title="Edit address">
                                    ✏️ Edit
                                </a>
                                
                                <?php if (!$address->is_default): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="set_default">
                                        <input type="hidden" name="address_id" value="<?= $address->address_id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline" title="Set as default address">
                                            Set as Default
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                    <input type="hidden" name="action" value="delete_address">
                                    <input type="hidden" name="address_id" value="<?= $address->address_id ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete address">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.addresses-page {
    padding: 20px 0 100px 0;
    min-height: calc(100vh - 200px);
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #3498db;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0;
    font-size: 32px;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.address-card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-left: 5px solid #ddd;
    transition: all 0.3s ease;
    position: relative;
}

.address-card.default {
    border-left-color: #3498db;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.address-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.address-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f2f6;
}

.address-header h3 {
    color: #2c3e50;
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.default-badge {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 5px;
}

.address-details {
    margin-bottom: 20px;
}

.contact-info {
    margin-bottom: 15px;
}

.contact-info strong {
    color: #2c3e50;
    font-size: 16px;
    display: block;
    margin-bottom: 5px;
}

.company {
    color: #7f8c8d;
    font-style: italic;
    font-size: 14px;
}

.address-info {
    margin-bottom: 15px;
    line-height: 1.6;
    color: #5a6c7d;
}

.address-info div {
    margin-bottom: 3px;
}

.contact-details .phone {
    color: #27ae60;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

.address-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 15px;
    border-top: 1px solid #f1f2f6;
}

.empty-addresses {
    text-align: center;
    padding: 80px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-addresses h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 24px;
}

.empty-addresses p {
    color: #7f8c8d;
    margin: 10px 0;
    font-size: 16px;
    line-height: 1.6;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2980b9, #1f618d);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 16px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .addresses-page {
        padding: 20px 0 120px 0;
    }
    
    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
        text-align: center;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .addresses-grid {
        grid-template-columns: 1fr;
    }
    
    .address-card .address-actions {
        flex-direction: column;
    }
    
    .empty-addresses {
        padding: 60px 15px;
    }
}
</style>

<?php
include '../../foot.php';
?>
