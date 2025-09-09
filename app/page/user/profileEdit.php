<?php
require '../../base.php';
auth();


$user = $_user;

if (is_post()) {
    $email = req('email');
    $photo = $user->photo;
    $err = [];


    $f = get_file('photo');
    if ($f) {
        if (str_starts_with($f->type, 'image/')) {
            if ($f->size > 1 * 1024 * 1024) {
                $err['photo'] = 'Maximum 1MB';
            } else {
                if ($user->photo) {
                    @unlink(__DIR__ . "/../../images/userAvatar/" . $user->photo);
                }
                $photo = save_photo($f, __DIR__ . '/../../images/userAvatar');
            }
        } else {
            $err['photo'] = 'Must be image';
        }
    }

    if ($email == '') {
        $err['email'] = 'Email is required';
    } else if (strlen($email) > 100) {
        $err['email'] = 'Maximum 100 characters';
    } else if (!is_email($email)) {
        $err['email'] = 'Invalid email';
    } else {
        $stm = $_db->prepare('SELECT COUNT(*) FROM user WHERE email = ? AND id != ?');
        $stm->execute([$email, $user->id]);
        if ($stm->fetchColumn() > 0) {
            $err['email'] = 'This email is already registered.';
        }
    }
    /*if ($phone == '') {
        $err['phone'] = 'Phone number is required';
    }*/ 
    
    if (!$err) {
        // Check if email has changed
        if ($email !== $user->email) {
            // Email changed - require verification
            $token = create_email_verification($user->id, $email, 'email_change');
            if ($token) {
                // Send verification email
                if (send_verification_email($email, $token, 'email_change', $user->username)) {
                    // Store the new email temporarily (not in main email field yet)
                    $stm = $_db->prepare("UPDATE user SET photo=? WHERE id=?");
                    $stm->execute([$photo, $user->id]);
                    $_user->photo = $photo;
                    
                    temp('info', 'Verification email sent to your new email address. Please verify it within 5 minutes to complete the change.');
                    redirect('/page/user/profile.php');
                } else {
                    $err['email'] = 'Failed to send verification email. Please try again.';
                }
            } else {
                $err['email'] = 'Failed to create verification token. Please try again.';
            }
        } else {
            // Email not changed - update only photo
            $stm = $_db->prepare("UPDATE user SET photo=? WHERE id=?");
            $stm->execute([$photo, $user->id]);
            $_user->photo = $photo;
            temp('info', 'Profile updated!');
            redirect('/page/user/profile.php');
        }
    }
} else {
    $email = $user->email;
    $photo = $user->photo;
    $err = [];
}


// include head and add profile-edit.css
include '../../head.php';

?>
<main class="profile-edit-body">
    <div class="profile-edit-wrapper">
        <h1>Edit Profile</h1>
        <form class="profile-edit-form" method="post" enctype="multipart/form-data">
            <div class="profile-edit-avatar">
                <img src="<?= $photo ? '/images/userAvatar/' . $photo : '/images/default-avatar.png' ?>" alt="Current Avatar">
            </div>
            <div class="edit-input-box">
                <label>New Avatar:
                    <?= html_file('photo', 'image/*') ?>
                </label>
                <?php if(isset($err['photo'])): ?><span class="err"><?= $err['photo'] ?></span><?php endif; ?>
            </div>
            <div class="edit-input-box">
                <label>Email:</label>
                <?= html_text('email', "type='email' maxlength='100'") ?>
                <?php if(isset($err['email'])): ?><span class="err"><?= $err['email'] ?></span><?php endif; ?>
            </div>
            <button class="edit-btn" type="submit">Save</button>
            <a href="/page/user/profile.php" class="edit-cancel">Cancel</a>
        </form>
    </div>
</main>
<?php include '../../foot.php'; ?>
