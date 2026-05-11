<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Instructor</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-chalkboard-user"></i> Edit Instructor</h2>
                <p>Update instructor information</p>
            </div>

            <form action="../handlers/users/updateUser.php" method="POST" autocomplete="off" novalidate>
                <input type="hidden" name="user_id">
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" id="email" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Name</label>
                    <input type="text" name="name" id="name" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" name="phone" id="phone" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="salary"><i class="fas fa-dollar-sign"></i> Salary</label>
                    <input type="number" name="salary" id="salary" step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="status"><i class="fas fa-toggle-on"></i> Status</label>
                    <select name="status" id="status">
                        <option value="active">Active</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Instructor</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="./js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/validator.js"></script>
    <script defer src="./js/editInstructor.js"></script>

</body>

</html>