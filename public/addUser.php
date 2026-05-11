<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Create User</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-user-plus"></i> Create User</h2>
                <p>Add a new user to the system</p>
            </div>

            <form action="../handlers/addUser.php" method="POST" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="role"><i class="fas fa-shield-alt"></i> Role *</label>
                    <select name="role" id="role" required onchange="toggleFields()">
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="instructor">Instructor</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group" id="nameField" style="display:none;">
                    <label for="name"><i class="fas fa-user"></i> Name</label>
                    <input type="text" name="name" id="name">
                </div>

                <div class="form-group" id="phoneField" style="display:none;">
                    <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" name="phone" id="phone">
                </div>

                <div class="form-group" id="salaryField" style="display:none;">
                    <label for="salary"><i class="fas fa-dollar-sign"></i> Salary</label>
                    <input type="number" name="salary" id="salary" step="0.01" min="0">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Create User</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="/public/js/functions.js"></script>
    <script defer src="/public/js/validator.js"></script>
    <script defer src="/public/js/addUser.js"></script>
</body>

</html>