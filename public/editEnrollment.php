<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Enrollment</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-edit"></i> Edit Enrollment</h2>
                <p>Update enrollment details</p>
            </div>

            <form action="../handlers/enrollments/updateEnrollment.php" method="POST" novalidate>
                <input type="hidden" name="id">
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="student_info"><i class="fas fa-graduation-cap"></i> Student</label>
                    <input type="text" name="student_info" id="student_info" disabled readonly>
                </div>

                <div class="form-group">
                    <label for="course_info"><i class="fas fa-book"></i> Course</label>
                    <input type="text" name="course_info" id="course_info" disabled readonly>
                </div>

                <div class="form-group">
                    <label for="instructor_id"><i class="fas fa-chalkboard-user"></i> Instructor *</label>
                    <select name="instructor_id" id="instructor_id" required></select>
                </div>

                <div class="form-group">
                    <label for="status"><i class="fas fa-toggle-on"></i> Status *</label>
                    <select name="status" id="status" required>
                        <option value="requested">Requested</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                        <option value="withdrawn">Withdrawn</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Enrollment</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="/public/js/validator.js"></script>
    <script defer src="./js/editEnrollment.js"></script>

</body>

</html>