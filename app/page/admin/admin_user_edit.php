<?php 
require '../../base.php';
auth('Admin');

$user_id = req('id');

// Fetch existing user
$stm = $_db->prepare("SELECT * FROM user WHERE id = ?");
$stm->execute([$user_id]);
$user = $stm->fetch();
if (!$user) {
    temp('error', 'User not found.');
    redirect('/page/admin/admin_user.php');
}

if (is_get()) {
    // Set default values for form fields if not already set
    if (!isset($_POST['username'])) $_POST['username'] = $user->username;
    if (!isset($_POST['email'])) $_POST['email'] = $user->email;
    if (!isset($_POST['name'])) $_POST['name'] = $user->name;
    if (!isset($_POST['role'])) $_POST['role'] = $user->role;
}

if (is_post()) {
    $username = req('username');
    $email = req('email');
    $name = req('name');
    $role = req('role');
    $password = req('password');

    // --- Validation ---
    if ($username == '') $_err['username'] = 'Username is required';
    else if (strlen($username) < 3) $_err['username'] = 'Username must be at least 3 characters';
    else if (strlen($username) > 50) $_err['username'] = 'Username maximum 50 characters';
    else {
        $stm = $_db->prepare('SELECT COUNT(*) FROM user WHERE username = ? AND id != ?');
        $stm->execute([$username, $user_id]);
        if ($stm->fetchColumn() > 0) $_err['username'] = 'Username already exists';
    }

    if ($email == '') $_err['email'] = 'Email is required';
    else if (!is_email($email)) $_err['email'] = 'Invalid email format';
    else if (strlen($email) > 100) $_err['email'] = 'Email maximum 100 characters';
    else {
        $stm = $_db->prepare('SELECT COUNT(*) FROM user WHERE email = ? AND id != ?');
        $stm->execute([$email, $user_id]);
        if ($stm->fetchColumn() > 0) $_err['email'] = 'Email already exists';
    }

    if ($name == '') $_err['name'] = 'Name is required';
    else if (strlen($name) > 100) $_err['name'] = 'Name maximum 100 characters';

    if ($role == '') $_err['role'] = 'Role is required';

    // Password validation (only if provided)
    if ($password !== '') {
        if (strlen($password) < 6) $_err['password'] = 'Password must be at least 6 characters';
        else if (strlen($password) > 255) $_err['password'] = 'Password maximum 255 characters';
    }

    // --- Update DB ---
    if (!$_err) {
        if ($password !== '') {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stm = $_db->prepare("UPDATE user SET username = ?, email = ?, name = ?, role = ?, password = ? WHERE id = ?");
            $stm->execute([$username, $email, $name, $role, $hashed_password, $user_id]);
        } else {
            // Update without changing password
            $stm = $_db->prepare("UPDATE user SET username = ?, email = ?, name = ?, role = ? WHERE id = ?");
            $stm->execute([$username, $email, $name, $role, $user_id]);
        }
        
        temp('info', 'User updated successfully.');
        redirect('/page/admin/admin_user.php');
    }
}

$roles = ['Member' => 'Member', 'Admin' => 'Admin'];

include '../../head.php';
?>
<main>
    <h1 class="admin-form-title">Edit User</h1>
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
                <label><b>Role</b></label>
                <?= html_select('role', $roles, '-- Select Role --', "class='admin-form-select'"); ?>
                <?= err('role') ?>
            </div>
            <div class="admin-form-row">
                <label><b>New Password</b> (Leave empty to keep current)</label>
                <?= html_password('password', "placeholder='New Password'"); ?>
                <?= err('password') ?>
            </div>

            <!-- Buttons -->
            <div class="admin-form-row admin-form-row-full admin-form-btn-row">
                <button type="submit" name="edit_user" class="admin-btn edit-btn">Save Changes</button>
                <a href="admin_user.php" class="admin-btn cancel-btn">Cancel</a>
            </div>
        </div>
        </fieldset>
    </form>
</main>

<?php
include '../../foot.php';
?>
