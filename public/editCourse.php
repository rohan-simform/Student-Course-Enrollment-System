<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Course</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-book-edit"></i> Edit Course</h2>
                <p>Update course details</p>
            </div>

            <form action="../handlers/updateCourse.php" method="POST" novalidate>
                <input type="hidden" name="course_id">
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="name"><i class="fas fa-book"></i> Course Name *</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="duration_weeks"><i class="fas fa-hourglass-end"></i> Duration (Weeks) *</label>
                    <input type="number" name="duration_weeks" id="duration_weeks" min="1" required>
                </div>

                <div class="form-group">
                    <label for="max_seats"><i class="fas fa-users"></i> Maximum Seats</label>
                    <input type="number" name="max_seats" id="max_seats" min="1">
                </div>

                <div class="form-group">
                    <label for="is_active"><i class="fas fa-toggle-on"></i> Status</label>
                    <select name="is_active" id="is_active">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Course</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="/public/js/validator.js"></script>
    <script defer src="./js/editCourse.js"></script>

</body>

</html>