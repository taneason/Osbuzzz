<?php
require '../base.php';


$error_class = '';
if (is_post()) {
    $email = req('email');

    //validate email
    if ($email == ''){
        $_err['email'] = 'Required';
        $error_class = 'class="error"';
        
    }
    
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid Format.';
        $error_class = 'class="error"';

    }
    else if (!is_unique($email, 'user', 'email')) {
        $_err['email'] = 'This Email already have an account.';
        $error_class = 'class="error"';

    }

    if (!$_err){
        $_SESSION['email'] = $email;
        redirect('/page/signup_pass.php');
    }
}
$_title = 'Sign Up';
include '../signuphead.php';
?>
<div class="signup_body">
    <main>
        <div class="signup_content">
            <img src="/images/logo.png">
            <div class="signup_wrapper">
                <form method="post">
                    <h1>Sign Up</h1>
                    <div class="signup_input_box">
                        <?= html_text('email','maxlength="100" placeholder="Email"'.$error_class) ?>
                        <i class='bx bxs-user'></i>
                    </div>
                    <?= err('email') ?>

                    <button class=btn >Next</button>

                    <div class="login_link">
                        <p>Have an account? 
                        <a href="/page/login.php"> Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include '../foot.php';