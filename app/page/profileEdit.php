<?php
require '../base.php';
auth();


$user = $_user;

if (is_post()) {
    $name = req('name');
    $email = req('email');
    $phone = req('phone');
    $photo = $user->photo;
    $err = [];


    $f = get_file('photo');
    if ($f) {
        if (str_starts_with($f->type, 'image/')) {
            if ($f->size > 1 * 1024 * 1024) {
                $err['photo'] = 'Maximum 1MB';
            } else {
                if ($user->photo) {
                    @unlink("../images/userAvatar/" . $user->photo);
                }
                $photo = save_photo($f, '../images/userAvatar');
            }
        } else {
            $err['photo'] = 'Must be image';
        }
    }

    /*if ($name == '') {
        $err['name'] = 'Name is required';
    } */
    if (strlen($name) > 100) {
        $err['name'] = 'Maximum 100 characters';
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
    if ($phone != ''){
        if (strlen($phone) > 20) {
            $err['phone'] = 'Maximum 20 characters';
        } else if (!preg_match('/^[0-9][0-9\-\+ ]*$/', $phone)) {
            $err['phone'] = 'Invalid phone format';
        } else {
            $stm = $_db->prepare('SELECT COUNT(*) FROM user WHERE phone = ? AND id != ?');
            $stm->execute([$phone, $user->id]);
        if ($stm->fetchColumn() > 0) {
            $err['phone'] = 'This phone number is already registered.';
        }
        }
    }
    if (!$err) {
        $stm = $_db->prepare("UPDATE user SET name=?, email=?, phone=?, photo=? WHERE id=?");
        $stm->execute([$name, $email, $phone, $photo, $user->id]);
        $_user->name = $name;
        $_user->email = $email;
        $_user->phone = $phone;
        $_user->photo = $photo;
        temp('info', 'Profile updated!');
        redirect('/page/profile.php');
    }
} else {
    $name = $user->name;
    $email = $user->email;
    $phone = $user->phone;
    $photo = $user->photo;
    $err = [];
}


// include head and add profile-edit.css
include '../head.php';

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
                <label>Name:</label>
                <?= html_text('name', "maxlength='100'") ?>
                <?php if(isset($err['name'])): ?><span class="err"><?= $err['name'] ?></span><?php endif; ?>
            </div>
            <div class="edit-input-box">
                <label>Email:</label>
                <?= html_text('email', "type='email' maxlength='100'") ?>
                <?php if(isset($err['email'])): ?><span class="err"><?= $err['email'] ?></span><?php endif; ?>
            </div>
            <div class="edit-input-box">
                <label>Phone:</label>
                <?= html_text('phone', "maxlength='20'") ?>
                <?php if(isset($err['phone'])): ?><span class="err"><?= $err['phone'] ?></span><?php endif; ?>
            </div>
            <button class="edit-btn" type="submit">Save</button>
            <a href="/page/profile.php" class="edit-cancel">Cancel</a>
        </form>
    </div>
</main>
<?php include '../foot.php'; ?>
