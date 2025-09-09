<?php
require '../../base.php';

// Auto cleanup expired unverified users (runs randomly)
auto_cleanup_expired_users();

$error_class_login='';
$error_class_password='';
if (is_post()) {
    $password = req('password');
    $login = req('login');

    //validate
    if ($login == ''){
        $_err['login'] = 'Username / Email is required';
        $error_class_login='class ="error"';
    }

    if ($password == '') {
        $_err['password'] = "Password is required.";
        $error_class_password='class ="error"';
    } 
    
    else if ((!is_exists($login, 'user', 'email')) && (!is_exists($login, 'user', 'username')) ) {
        $_err['password'] = 'Not matched';
        $error_class_login='class ="error"';
        $error_class_password='class ="error"';
        }
    

   

    if (!$_err) {
        if (is_email($login)){

            $stm = $_db->prepare('
            SELECT * FROM user
            WHERE email = ? AND password = SHA1(?)
            ');
            $stm->execute([$login,$password]);
            $u = $stm->fetch();
        }
        else{
            $stm = $_db->prepare('
            SELECT * FROM user
            WHERE username = ? AND password = SHA1(?)
            ');
            $stm->execute([$login,$password]);
            $u = $stm->fetch();
        }
        if ($u) {
            // Check if email is verified
            if (isset($u->email_verified) && $u->email_verified == 0) {
                $_err['password'] = 'Please verify your email address before logging in. Check your email for the verification link.';
                $error_class_login='class ="error"';
                $error_class_password='class ="error"';
            }
            // Check if user is banned
            else if (isset($u->status) && $u->status === 'banned') {
                $_err['password'] = 'Your account has been banned.';
                $error_class_login='class ="error"';
                $error_class_password='class ="error"';
            } 
            else {
                temp('info', 'Login successfully');
                login($u);
            }
        }
        else {
            $_err['password'] = 'Not matched';
            $error_class_login='class ="error"';
            $error_class_password='class ="error"';
        }
    }
}
$_title = 'Log in';
include '../../signuphead.php';
?>

<div class="login_body">
    <main>
        <div class="login_content">
            <img src="/images/logo.png">
            <div class="wrapper">
                <form method="post">
                    <h1>Log in</h1>
                    <div class="input_box">
                        <?= html_text('login','maxlength="100" placeholder="Username / Email"'.$error_class_login) ?>
                        <i class='bx bxs-user'></i>
                        <?= err('login') ?>
                    </div>
                    <div class="input_box">
                        <?= html_password('password','maxlength="100" placeholder="Password"'.$error_class_password) ?>
                        <i class='bx bxs-lock-alt'></i>     
                        <?= err('password') ?>               
                    </div>

                    <div class="forgot">
                        <br>
                        <a href="/page/user/forgot_password.php">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn">Log in</button>

                    <div class="register_link">
                        <p>Don't have an account? 
                        <a href="/page/user/signup.php"> Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include '../../foot.php';
