<?php
require '../../base.php';

auth();
if(is_get()){
    $stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
    $stm->execute([$_user->id]);
    $u = $stm->fetch();
    if (!$u) {
        redirect('/');
    }

    extract((array) $u);
    $_SESSION['photo'] = $u -> photo;
}


include '../../head.php';
?>


<main class="profile-edit-body">
    <div class="profile-edit-wrapper">
        <h1>My Profile</h1>
        <div class="profile-edit-avatar">
            <img src="<?= $_user->photo ? '/images/userAvatar/' . $_user->photo : '/images/default-avatar.png' ?>" alt="User Avatar">
        </div>
        <div class="profile-edit-form">
            <div class="edit-input-box">
                <label>Email:</label>
                <div><?= $_user->email ? encode($_user->email) : '<span class="profile-empty">Not set</span>' ?></div>
            </div>
            <div class="edit-input-box">
                <label>Password:</label>
                <div>
                    ********
                    <a href="/page/user/changePass.php" style="font-size:0.95em;margin-left:10px;color:#3793af;text-decoration:underline;">Change</a>
                </div>
            </div>
            <div class="edit-input-box">
                <label>Shipping Addresses:</label>
                <div>
                    <a href="/page/user/addresses.php" style="color:#3793af;text-decoration:underline;">Manage Addresses</a>
                    <span style="font-size:0.9em;color:#666;margin-left:10px;">Add multiple shipping addresses</span>
                </div>
            </div>
            <div class="edit-input-box">
                <label>My Orders:</label>
                <div>
                    <a href="/page/user/orders.php" style="color:#3793af;text-decoration:underline;">View Order History</a>
                    <span style="font-size:0.9em;color:#666;margin-left:10px;">Track your purchases</span>
                </div>
            </div>
            <form id="changeProfileForm" action="/page/user/profileEdit.php" method="get" style="margin-top:24px;">
                <button type="submit" class="edit-btn">Edit Profile</button>
            </form>
        </div>
    </div>
</main>



<?php
include '../../foot.php';
