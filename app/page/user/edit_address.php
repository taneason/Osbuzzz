<?php
require '../../base.php';

auth();

$address_id = get('id');

if (!$address_id) {
    temp('error', 'Invalid address ID');
    redirect('/page/user/addresses.php');
}

// Get the address to edit
$stm = $_db->prepare('SELECT * FROM customer_addresses WHERE address_id = ? AND user_id = ?');
$stm->execute([$address_id, $_user->id]);
$address = $stm->fetch();

if (!$address) {
    temp('error', 'Address not found');
    redirect('/page/user/addresses.php');
}

if (is_post()) {
    $address_name = req('address_name');
    $first_name = req('first_name');
    $last_name = req('last_name');
    $company = req('company');
    $address_line_1 = req('address_line_1');
    $address_line_2 = req('address_line_2');
    $city = req('city');
    $state = req('state');
    $postal_code = req('postal_code');
    $phone = req('phone');
    $is_default = req('is_default') ? 1 : 0;

    // Validation
    if ($address_name == '') {
        $_err['address_name'] = 'Address name is required';
    }

    if ($first_name == '') {
        $_err['first_name'] = 'First name is required';
    }

    if ($last_name == '') {
        $_err['last_name'] = 'Last name is required';
    }

    if ($address_line_1 == '') {
        $_err['address_line_1'] = 'Address line 1 is required';
    }

    if ($city == '') {
        $_err['city'] = 'City is required';
    }

    if ($state == '') {
        $_err['state'] = 'State is required';
    }

    if ($postal_code == '') {
        $_err['postal_code'] = 'Postal code is required';
    }

    if ($phone == '') {
        $_err['phone'] = 'Phone number is required';
    } else if (!validate_malaysian_phone($phone)) {
        $_err['phone'] = 'Please enter a valid Malaysian phone number (e.g., 012-345-6789)';
    }

    // If no errors, update the address
    if (!$_err) {
        try {
            $_db->beginTransaction();

            // If setting as default, remove default from other addresses first
            if ($is_default) {
                $stm = $_db->prepare('UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?');
                $stm->execute([$_user->id]);
            }

            // Update the address
            $stm = $_db->prepare('
                UPDATE customer_addresses 
                SET address_name = ?, first_name = ?, last_name = ?, company = ?, 
                    address_line_1 = ?, address_line_2 = ?, city = ?, state = ?, 
                    postal_code = ?, phone = ?, is_default = ?, updated_at = NOW()
                WHERE address_id = ? AND user_id = ?
            ');
            
            $stm->execute([
                $address_name, $first_name, $last_name, $company,
                $address_line_1, $address_line_2, $city, $state,
                $postal_code, $phone, $is_default, $address_id, $_user->id
            ]);

            $_db->commit();
            temp('success', 'Address updated successfully');
            redirect('/page/user/addresses.php');

        } catch (Exception $e) {
            $_db->rollBack();
            temp('error', 'Failed to update address. Please try again.');
        }
    }
} else {
    // Pre-fill form with existing data
    $address_name = $address->address_name;
    $first_name = $address->first_name;
    $last_name = $address->last_name;
    $company = $address->company;
    $address_line_1 = $address->address_line_1;
    $address_line_2 = $address->address_line_2;
    $city = $address->city;
    $state = $address->state;
    $postal_code = $address->postal_code;
    $phone = $address->phone;
    $is_default = $address->is_default;
}

$_title = 'Edit Address';
include '../../head.php';
?>
<div>
<main class="edit-address-page">
    <div class="container">
        <div class="page-header">
            <h1>Edit Address</h1>
            <div class="header-actions">
                <a href="addresses.php" class="btn btn-secondary">← Back to Addresses</a>
            </div>
        </div>

        <div class="form-container">
            <form method="post" class="address-form">
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_name">Address Name <span class="required">*</span></label>
                        <input type="text" id="address_name" name="address_name" 
                               value="<?= encode($address_name) ?>" 
                               placeholder="e.g., Home, Office, Billing Address"
                               class="<?= isset($_err['address_name']) ? 'error' : '' ?>">
                        <?php if (isset($_err['address_name'])): ?>
                            <span class="error-message"><?= $_err['address_name'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?= encode($first_name) ?>"
                               class="<?= isset($_err['first_name']) ? 'error' : '' ?>">
                        <?php if (isset($_err['first_name'])): ?>
                            <span class="error-message"><?= $_err['first_name'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?= encode($last_name) ?>"
                               class="<?= isset($_err['last_name']) ? 'error' : '' ?>">
                        <?php if (isset($_err['last_name'])): ?>
                            <span class="error-message"><?= $_err['last_name'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="company">Company (Optional)</label>
                        <input type="text" id="company" name="company" 
                               value="<?= encode($company) ?>" 
                               placeholder="Company name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_line_1">Address Line 1 <span class="required">*</span></label>
                        <input type="text" id="address_line_1" name="address_line_1" 
                               value="<?= encode($address_line_1) ?>" 
                               placeholder="Street address, P.O. box, apartment, suite, unit, building, floor, etc."
                               class="<?= isset($_err['address_line_1']) ? 'error' : '' ?>">
                        <?php if (isset($_err['address_line_1'])): ?>
                            <span class="error-message"><?= $_err['address_line_1'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_line_2">Address Line 2 (Optional)</label>
                        <input type="text" id="address_line_2" name="address_line_2" 
                               value="<?= encode($address_line_2) ?>" 
                               placeholder="Apartment, suite, unit, building, floor, etc.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" 
                               value="<?= encode($city) ?>"
                               class="<?= isset($_err['city']) ? 'error' : '' ?>">
                        <?php if (isset($_err['city'])): ?>
                            <span class="error-message"><?= $_err['city'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="state">Negeri <span class="required">*</span></label>
                        <select id="state" name="state" class="<?= isset($_err['state']) ? 'error' : '' ?>">
                            <?= generate_state_options($state) ?>
                        </select>
                        <?php if (isset($_err['state'])): ?>
                            <span class="error-message"><?= $_err['state'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code <span class="required">*</span></label>
                        <input type="text" id="postal_code" name="postal_code" 
                               value="<?= encode($postal_code) ?>"
                               class="<?= isset($_err['postal_code']) ? 'error' : '' ?>">
                        <?php if (isset($_err['postal_code'])): ?>
                            <span class="error-message"><?= $_err['postal_code'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?= encode($phone) ?>"
                               placeholder="012-345-6789 or 03-1234-5678"
                               class="<?= isset($_err['phone']) ? 'error' : '' ?>">
                        <?php if (isset($_err['phone'])): ?>
                            <span class="error-message"><?= $_err['phone'] ?></span>
                        <?php endif; ?>
                        <small class="form-hint">Enter Malaysian phone number (mobile: 01X-XXX-XXXX, landline: 0X-XXXX-XXXX)</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_default" value="1" <?= $is_default ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Set as default address
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Address</button>
                    <a href="addresses.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Spacer div to prevent footer overlap -->
        <div style="height: 120px;"></div>
    </div>
</main>
</div>
}
</style>

<script>
// Auto-format Malaysian phone number as user types
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove all non-digits
            
            // Format mobile numbers (01X-XXX-XXXX)
            if (value.startsWith('01') && value.length >= 3) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                } else if (value.length <= 10) {
                    value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
                } else {
                    value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7, 11);
                }
            }
            // Format landline numbers (0X-XXXX-XXXX)
            else if (value.startsWith('0') && value.length >= 2) {
                if (value.length <= 2) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 2) + '-' + value.slice(2);
                } else if (value.length <= 10) {
                    value = value.slice(0, 2) + '-' + value.slice(2, 6) + '-' + value.slice(6);
                }
            }
            
            e.target.value = value;
        });
        
        // Handle paste events
        phoneInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                phoneInput.dispatchEvent(new Event('input'));
            }, 10);
        });
    }
});
</script>

<?php include '../../foot.php'; ?>

<style>
.edit-address-page {
    padding: 20px 0 100px 0;
    min-height: calc(100vh - 200px);
}

.edit-address-page .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.edit-address-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.edit-address-page .page-header h1 {
    color: #2c3e50;
    margin: 0;
    font-size: 28px;
}

.edit-address-page .form-container {
    background: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.edit-address-page .address-form {
    max-width: 100%;
}

.edit-address-page .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.edit-address-page .form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.edit-address-page .form-group.full-width {
    flex: 1 1 100%;
}

.edit-address-page .form-group label {
    font-weight: bold;
    margin-bottom: 8px;
    color: #2c3e50;
    font-size: 14px;
}

/* Red asterisk for required fields */
.edit-address-page .form-group label .required {
    color: #e74c3c;
    font-weight: bold;
}

.edit-address-page .form-group input,
.edit-address-page .form-group select {
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s, box-shadow 0.3s;
    background-color: #fff;
}

.edit-address-page .form-group input:focus,
.edit-address-page .form-group select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.edit-address-page .form-group input.error,
.edit-address-page .form-group select.error {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.edit-address-page .error-message {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    font-weight: 500;
}

.edit-address-page .checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: normal !important;
    margin-bottom: 0 !important;
}

.edit-address-page .checkbox-label input[type="checkbox"] {
    margin-right: 10px;
    margin-bottom: 0;
    transform: scale(1.2);
}

.edit-address-page .form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

.edit-address-page .btn {
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    font-weight: bold;
    text-align: center;
}

.edit-address-page .btn-primary {
    background: #3498db;
    color: white;
}

.edit-address-page .btn-primary:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.edit-address-page .btn-secondary {
    background: #95a5a6;
    color: white;
}

.edit-address-page .btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .edit-address-page .form-container {
        padding: 25px 20px;
    }
    
    .edit-address-page .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .edit-address-page .form-group {
        margin-bottom: 20px;
    }
    
    .edit-address-page .form-actions {
        flex-direction: column;
    }
    
    .edit-address-page .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .edit-address-page .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .edit-address-page .header-actions {
        width: 100%;
    }
}
</style>
