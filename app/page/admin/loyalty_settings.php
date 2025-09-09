<?php
require '../../base.php';
auth('Admin');

if (is_post()) {
    $settings = [
        'points_per_ringgit' => (int)req('points_per_ringgit'),
        'points_to_ringgit_ratio' => (int)req('points_to_ringgit_ratio'),
        'minimum_points_redeem' => (int)req('minimum_points_redeem'),
        'maximum_discount_percentage' => (int)req('maximum_discount_percentage'),
        'points_expiry_months' => (int)req('points_expiry_months'),
        'signup_bonus_points' => (int)req('signup_bonus_points')
    ];
    
    $errors = [];
    
    // Validation
    if ($settings['points_per_ringgit'] < 1) $errors[] = 'Points per ringgit must be at least 1';
    if ($settings['points_to_ringgit_ratio'] < 1) $errors[] = 'Points to ringgit ratio must be at least 1';
    if ($settings['minimum_points_redeem'] < 1) $errors[] = 'Minimum points redeem must be at least 1';
    if ($settings['maximum_discount_percentage'] < 1 || $settings['maximum_discount_percentage'] > 100) {
        $errors[] = 'Maximum discount percentage must be between 1-100';
    }
    if ($settings['points_expiry_months'] < 0) $errors[] = 'Points expiry months cannot be negative';
    if ($settings['signup_bonus_points'] < 0) $errors[] = 'Signup bonus points cannot be negative';
    
    if (empty($errors)) {
        try {
            $_db->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $stm = $_db->prepare('UPDATE loyalty_settings SET setting_value = ? WHERE setting_key = ?');
                $stm->execute([$value, $key]);
            }
            
            $_db->commit();
            temp('info', 'Loyalty settings updated successfully!');
            redirect('/page/admin/loyalty_settings.php');
            
        } catch (Exception $e) {
            $_db->rollBack();
            temp('error', 'Failed to update settings: ' . $e->getMessage());
        }
    } else {
        temp('error', implode('<br>', $errors));
    }
}

// Get current settings
$stm = $_db->query('SELECT setting_key, setting_value, description FROM loyalty_settings');
$settings = [];
while ($row = $stm->fetch()) {
    $settings[$row->setting_key] = $row;
}

include '../../head.php';
?>

<main style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h1>Loyalty Points Settings</h1>
    
    <form method="post" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: grid; gap: 20px;">
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Points per RM spent:</label>
                <input type="number" name="points_per_ringgit" min="1" 
                       value="<?= $settings['points_per_ringgit']->setting_value ?? 1 ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666;"><?= htmlspecialchars($settings['points_per_ringgit']->description ?? '') ?></small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Points needed for RM1 discount:</label>
                <input type="number" name="points_to_ringgit_ratio" min="1" 
                       value="<?= $settings['points_to_ringgit_ratio']->setting_value ?? 100 ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666;"><?= htmlspecialchars($settings['points_to_ringgit_ratio']->description ?? '') ?></small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Minimum points to redeem:</label>
                <input type="number" name="minimum_points_redeem" min="1" 
                       value="<?= $settings['minimum_points_redeem']->setting_value ?? 100 ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666;"><?= htmlspecialchars($settings['minimum_points_redeem']->description ?? '') ?></small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Maximum discount percentage:</label>
                <input type="number" name="maximum_discount_percentage" min="1" max="100" 
                       value="<?= $settings['maximum_discount_percentage']->setting_value ?? 50 ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666;"><?= htmlspecialchars($settings['maximum_discount_percentage']->description ?? '') ?>%</small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Points expiry (months):</label>
                <input type="number" name="points_expiry_months" min="0" 
                       value="<?= $settings['points_expiry_months']->setting_value ?? 12 ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666;"><?= htmlspecialchars($settings['points_expiry_months']->description ?? '') ?> (0 = never expire)</small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Signup bonus points:</label>
                <input type="number" name="signup_bonus_points" min="0" 
                       value="<?= $settings['signup_bonus_points']->setting_value ?? 100 ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666;"><?= htmlspecialchars($settings['signup_bonus_points']->description ?? '') ?></small>
            </div>
            
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <button type="submit" style="background: #007cba; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-right: 10px;">
                Update Settings
            </button>
            <a href="index.php" style="background: #6c757d; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
                Back to Admin
            </a>
        </div>
    </form>
</main>

<?php include '../../foot.php'; ?>
