<?php
require '../../base.php';
auth();

$error_class_password='';
$error_class_newPass= '';
$error_class_confirmPass='';
if (is_post()) {
    $password     = req('password');
    $new_password = req('new_password');
    $confirm      = req('confirm_password');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Password is required.';
        $error_class_password='class ="error"';
    }
    else if (strlen($password) < 8 || strlen($password) > 100) {
        $_err['password'] = 'Between 8-100 characters';
        $error_class_password='class ="error"';
    }
    else {
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM user
            WHERE password = SHA1(?) and username = ?
        ');
        $stm->execute([$password, $_user->username]);
        
        if ($stm->fetchColumn() == 0) {
            $_err['password'] = 'Password not matched';
            $error_class_password='class ="error"';
        }
    }

    // Validate: new_password
    if ($new_password == '') {
        $_err['new_password'] = 'New password required';
        $error_class_newPass= 'class = "error"';
    }
    else if (strlen($new_password) < 8 || strlen($new_password) > 100) {
        $_err['new_password'] = 'Between 8-100 characters';
        $error_class_newPass= 'class = "error"';

    }

    // Validate: confirm
    if ($confirm == '') {
        $_err['confirm_password'] = 'Please confirm your password.';
        $error_class_confirmPass= 'class = "error"';
    }
    else if (strlen($confirm) < 8 || strlen($confirm) > 100) {
        $_err['confirm_password'] = 'Between 8-100 characters';
        $error_class_confirmPass= 'class = "error"';
    }
    else if ($confirm != $new_password) {
        $_err['confirm_password'] = 'Passwords do not match.';
        $error_class_confirmPass= 'class = "error"';
    }

    // DB operation
    if (!$_err) {

        // Update user (password)
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE username = ?
        ');
        $stm->execute([$new_password,$_user -> username]);

        temp('info', 'Record updated');
        redirect('/');
    }
}

$_title = 'Change Password';
include '../../signuphead.php';
?>
      
<div class="pass_body">
    <main>
        <div class="pass_content">
            <img src="/images/logo.png">
            <div class="pass_wrapper">
                <form method="post">
                    <h1>Change Password</h1>

                    <div class="input_box">
                        <?= html_password('password','maxlength="100" placeholder="Password"'.$error_class_password) ?>
                        <i class='bx bxs-lock-alt'></i> 
                        <?= err('password') ?>                   
                    </div>

                    <div class="input_box">
                        <?= html_password('new_password','maxlength="50" placeholder="New Password"'.$error_class_newPass) ?>
                        <i class='bx bxs-lock-alt'></i> 
                        <?= err('new_password') ?>                   
                    </div>

                    <div class="input_box">
                        <?= html_password('confirm_password', 'maxlength="100" placeholder="Confirm password"'.$error_class_confirmPass) ?>
                        <i class='bx bxs-lock-alt'></i>   
                        <?= err('confirm_password') ?>                
                    </div>

                    <button class="btn">Next</button>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include '../../foot.php';
