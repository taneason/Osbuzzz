<?php
require '../../base.php';

auth();

// Handle form submission
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
    if ($address_name == '') $_err['address_name'] = 'Address name is required';
    if ($first_name == '') $_err['first_name'] = 'First name is required';
    if ($last_name == '') $_err['last_name'] = 'Last name is required';
    if ($address_line_1 == '') $_err['address_line_1'] = 'Address is required';
    if ($city == '') $_err['city'] = 'City is required';
    if ($state == '') $_err['state'] = 'State is required';
    if ($postal_code == '') $_err['postal_code'] = 'Postal code is required';
    if ($phone == '') $_err['phone'] = 'Phone number is required';
    
    if (!$_err) {
        // If this is set as default, remove default from other addresses
        if ($is_default) {
            $stm = $_db->prepare('UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?');
            $stm->execute([$_user->id]);
        }
        
        // Insert new address
        $stm = $_db->prepare('
            INSERT INTO customer_addresses (
                user_id, address_name, first_name, last_name, company,
                address_line_1, address_line_2, city, state, postal_code, phone, is_default
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stm->execute([
            $_user->id, $address_name, $first_name, $last_name, $company,
            $address_line_1, $address_line_2, $city, $state, $postal_code, $phone, $is_default
        ]);
        
        temp('success', 'Address added successfully');
        redirect('/page/user/addresses.php');
    }
}

$_title = 'Add New Address';
include '../../head.php';
?>
<div>
<main class="add-address-page">
    <div class="container">
        <div class="page-header">
            <div class="header-left">
                <a href="addresses.php" class="btn btn-back">← Back to Addresses</a>
            </div>
            <h1>Add New Address</h1>
            <div class="header-right"></div>
        </div>
        
        <div class="address-form">
            <form method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="address_name">Address Name <span class="required">*</span></label>
                        <?= html_text('address_name', 'placeholder="e.g. Home, Office"') ?>
                        <?= err('address_name') ?>
                        <small class="form-hint">Give this address a name to easily identify it</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <?= html_text('first_name', 'placeholder="First Name"') ?>
                        <?= err('first_name') ?>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <?= html_text('last_name', 'placeholder="Last Name"') ?>
                        <?= err('last_name') ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="company">Company (Optional)</label>
                    <?= html_text('company', 'placeholder="Company Name"') ?>
                </div>
                
                <div class="form-group">
                    <label for="address_line_1">Address Line 1 <span class="required">*</span></label>
                    <?= html_text('address_line_1', 'placeholder="Street Address"') ?>
                    <?= err('address_line_1') ?>
                </div>
                
                <div class="form-group">
                    <label for="address_line_2">Address Line 2 (Optional)</label>
                    <?= html_text('address_line_2', 'placeholder="Apartment, unit, etc."') ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City <span class="required">*</span></label>
                        <?= html_text('city', 'placeholder="City"') ?>
                        <?= err('city') ?>
                    </div>
                    <div class="form-group">
                        <label for="state">Negeri <span class="required">*</span></label>
                        <select id="state" name="state" class="<?= isset($_err['state']) ? 'error' : '' ?>">
                            <?= generate_state_options(post('state')) ?>
                        </select>
                        <?= err('state') ?>
                    </div>
                    <div class="form-group">
                        <label for="postal_code">Postal Code <span class="required">*</span></label>
                        <?= html_text('postal_code', 'placeholder="12345"') ?>
                        <?= err('postal_code') ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <?= html_text('phone', 'placeholder="01X-XXX-XXXX"') ?>
                    <?= err('phone') ?>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_default" value="1"> 
                        <span class="checkmark"></span>
                        Set as default address
                    </label>
                    <small class="form-hint">This address will be used as default for checkout</small>
                </div>
                
                <div class="form-actions">
                    <a href="addresses.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</main>

</div>

<style>
/* ===== RESET & BASE STYLES ===== */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
}

/* ===== PAGE LAYOUT ===== */
.add-address-page {
    padding: 30px 0 50px 0;
    min-height: 100vh;
    background-color: #f8f9fa;
}

.spacer {
    height: 200px; /* Extra space to prevent footer overlap */
}

.container {
    max-width: 700px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ===== PAGE HEADER ===== */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 3px solid #3498db;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0;
    text-align: center;
    flex-grow: 1;
    font-size: 28px;
}

.header-left, 
.header-right {
    flex: 0 0 auto;
    width: 150px;
}



/* ===== BUTTONS ===== */
.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    min-width: 120px;
    text-align: center;
}

.btn-back {
    background: #6c757d;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-width: auto;
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-1px);
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

/* ===== FORM STYLES ===== */
.address-form {
    background: #fff;
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    margin-bottom: 60px;
    border: 1px solid #e9ecef;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.form-group {
    margin-bottom: 30px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

/* Red asterisk for required fields */
.form-group label .required {
    color: #e74c3c;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    padding: 15px 18px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-hint {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 12px;
    font-style: italic;
}

/* ===== CHECKBOX STYLES ===== */
.checkbox-group {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    margin-bottom: 30px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 10px;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0 10px 0 0;
    transform: scale(1.2);
}

/* ===== FORM ACTIONS ===== */
.form-actions {
    display: flex;
    gap: 20px;
    justify-content: flex-end;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

/* ===== ERROR STYLES ===== */
.error {
    color: #e74c3c;
    font-size: 14px;
    margin-top: 5px;
    font-weight: 500;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    .add-address-page {
        padding: 20px 0 30px 0;
    }
    
    .spacer {
        height: 250px; /* More space on mobile */
    }
    
    .container {
        padding: 0 15px;
    }
    
    .page-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
        padding-bottom: 25px;
    }
    
    .page-header h1 {
        font-size: 24px;
    }
    
    .header-left, 
    .header-right {
        width: auto;
    }
    
    .header-left {
        order: -1;
    }
    
    .address-form {
        padding: 30px 20px;
        margin-bottom: 40px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .checkbox-group {
        padding: 20px 15px;
    }
}
</style>

<?php
include '../../foot.php';
?>
