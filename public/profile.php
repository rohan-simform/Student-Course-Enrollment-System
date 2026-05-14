<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Profile</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-user-circle"></i> My Profile</h2>
                <p>Edit your profile information</p>
            </div>

            <form id="profileForm" action="../handlers/users/updateUser.php" method="POST" autocomplete="off" novalidate>
                <?= CsrfHelper::hiddenInput() ?>
                <input type="hidden" name="user_id">

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" id="email" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current password" autocomplete="new-password">
                </div>

                <div class="form-group" id="nameField" style="display:none;">
                    <label for="name"><i class="fas fa-user"></i> Name</label>
                    <input type="text" name="name" id="name" autocomplete="off">
                </div>

                <div class="form-group" id="phoneField" style="display:none;">
                    <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" name="phone" id="phone" autocomplete="off">
                </div>

                <div class="form-group" id="salaryField" style="display:none;">
                    <label for="salary"><i class="fas fa-dollar-sign"></i> Salary</label>
                    <input type="number" name="salary" id="salary" disabled readonly>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Profile</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="./js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/profile.js"></script>

</body>

</html>
