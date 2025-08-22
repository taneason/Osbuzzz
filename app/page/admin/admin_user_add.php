<?php 
require '../../base.php';
auth('Admin');

if (is_post()) {
    $username = req('username');
    $email = req('email');
    $name = req('name');
    $password = req('password');
    $confirm_password = req('confirm_password');

    // --- Validation ---
    if ($username == '') $_err['username'] = 'Username is required';
    else if (strlen($username) < 3) $_err['username'] = 'Username must be at least 3 characters';
    else if (strlen($username) > 50) $_err['username'] = 'Username maximum 50 characters';
    else if (!is_unique($username, 'user', 'username')) $_err['username'] = 'Username already exists';

    if ($email == '') $_err['email'] = 'Email is required';
    else if (!is_email($email)) $_err['email'] = 'Invalid email format';
    else if (strlen($email) > 100) $_err['email'] = 'Email maximum 100 characters';
    else if (!is_unique($email, 'user', 'email')) $_err['email'] = 'Email already exists';

    if ($name == '') $_err['name'] = 'Name is required';
    else if (strlen($name) > 100) $_err['name'] = 'Name maximum 100 characters';

    if ($password == '') $_err['password'] = 'Password is required';
    else if (strlen($password) < 6) $_err['password'] = 'Password must be at least 6 characters';
    else if (strlen($password) > 255) $_err['password'] = 'Password maximum 255 characters';

    if ($confirm_password == '') $_err['confirm_password'] = 'Please confirm password';
    else if ($password !== $confirm_password) $_err['confirm_password'] = 'Passwords do not match';

    // --- Insert into DB ---
    if (!$_err) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stm = $_db->prepare("INSERT INTO user (username, email, name, password, role) 
                              VALUES (?, ?, ?, ?, 'Admin')");
        $stm->execute([$username, $email, $name, $hashed_password]);
        
        temp('info', 'Admin user created successfully.');
        redirect('/page/admin/admin_user.php');
    }
}

include '../../head.php';
?>
<main>
    <h1 class="admin-form-title">Add Admin User</h1>
    <form method="post" class="admin-edit-form" novalidate>
        <fieldset style="border-radius: 10px;">
        <div class="admin-form-grid">
            <!-- User info -->
            <div class="admin-form-row admin-form-row-full">
                <label><b>Username</b></label>
                <?= html_text('username', "maxlength='50' placeholder='Username'"); ?>
                <?= err('username') ?>
            </div>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Email</b></label>
                <?= html_text('email', "type='email' maxlength='100' placeholder='Email'"); ?>
                <?= err('email') ?>
            </div>
            <div class="admin-form-row admin-form-row-full">
                <label><b>Name</b></label>
                <?= html_text('name', "maxlength='100' placeholder='Full Name'"); ?>
                <?= err('name') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Password</b></label>
                <?= html_password('password', "placeholder='Password'"); ?>
                <?= err('password') ?>
            </div>
            <div class="admin-form-row">
                <label><b>Confirm Password</b></label>
                <?= html_password('confirm_password', "placeholder='Confirm Password'"); ?>
                <?= err('confirm_password') ?>
            </div>

            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" name="add_admin" class="admin-btn edit-btn">Create Admin</button>
                <a href="admin_user.php" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
?>
