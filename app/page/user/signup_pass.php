<?php
require '../../base.php';

// Auto cleanup expired unverified users (runs randomly)
auto_cleanup_expired_users();

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
        try {
            $_db->beginTransaction();
            
            // Insert user
            $stm = $_db->prepare('INSERT INTO user
                                  (email, password, username, photo, created_at, email_verified)
                                  VALUES(?, SHA1(?), ?, "", NOW(), ?)');
            
            $email_verification_required = is_email_verification_required();
            $email_verified = $email_verification_required ? 0 : 1;
            
            $stm->execute([$email, $password, $username, $email_verified]);
            $user_id = $_db->lastInsertId();
            
            if ($email_verification_required) {
                // Create email verification
                $token = create_email_verification($user_id, $email, 'registration');
                if ($token) {
                    // Send verification email
                    $email_sent = send_verification_email($email, $token, 'registration', $username);
                    if (!$email_sent) {
                        throw new Exception('Failed to send verification email');
                    }
                }
            } else {
                // If email verification not required, award points immediately
                $signup_bonus = (int)get_loyalty_setting('signup_bonus_points', 100);
                if ($signup_bonus > 0) {
                    add_loyalty_transaction($user_id, $signup_bonus, 'bonus', 'Welcome bonus');
                }
            }
            
            $_db->commit();
            
            if ($email_verification_required) {
                temp('info', 'Registration successful! Please check your email to verify your account.');
                redirect('/page/user/signup_success.php');
            } else {
                temp('info', 'Registration successful! Welcome to OSBuzz!');
                redirect('login.php');
            }
            
        } catch (Exception $e) {
            $_db->rollBack();
            $_err['general'] = 'Registration failed: ' . $e->getMessage();
        }
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
