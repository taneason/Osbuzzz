<?php
require '../../base.php';

$email = $_SESSION['email'];
if (!$email) {
    redirect('/page/user/signup.php');
}

$error_class_password='';
$error_class_username= '';
$error_class_confirmPass='';
if (is_post()) {
    $password = req('password');
    $username = req('username');
    $confirm_password = req('confirm_password');

    //validate username
    if ($username == ''){
        $_err['username'] = 'Username is required';
        $error_class_username='class ="error"';
    }
    
    else if (!is_unique($username, 'user', 'username')) {
        $_err['username'] = 'Username already exists.';
        $error_class_username='class ="error"';
    }

    else if (strlen($username) > 50) {
        $_err['username'] = 'Maximum length 50';
        $error_class_username='class ="error"';
    }

   //validate password
    if ($password == '') {
        $_err['password'] = "Password is required.";
        $error_class_password='class ="error"';
    } 

    elseif (strlen($password) < 8 || strlen($password) > 100) {
        $_err['password'] = "Password must between 8-100 characters.";
        $error_class_password='class ="error"';
    }   
    
    if ($confirm_password == '') {
        $_err['confirm_password'] = "Please confirm your password.";
        $error_class_confirmPass='class ="error"';
    } 
    elseif ($password !== $confirm_password) {
        $_err['confirm_password'] = "Passwords do not match.";
        $error_class_confirmPass='class ="error"';
    }

    if (!$_err){
        $stm = $_db->prepare('INSERT INTO user
                              (email, password,username)
                              VALUES(?, SHA1(?), ?)');
        $stm->execute([$email, $password , $username]);
        temp('info', 'Register Successful');
        redirect('login.php');
        
    }
}

$_title = 'Sign Up';
include '../../signuphead.php';
?>
      
<div class="pass_body">
    <main>
        <div class="pass_content">
            <img src="/images/logo.png">
            <div class="pass_wrapper">
                <form method="post">
                    <h1>Sign Up</h1>
                    <div class="input_box">
                        <?= html_text('username','maxlength="50" placeholder="Username"'.$error_class_username) ?>
                        <i class='bx bxs-user'></i> 
                        <?= err('username') ?>                   
                    </div>

                    <div class="input_box">
                        <?= html_password('password','maxlength="100" placeholder="Password"'.$error_class_password) ?>
                        <i class='bx bxs-lock-alt'></i> 
                        <?= err('password') ?>                   
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
