<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enroll Student</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-user-check"></i> Enroll Student</h2>
                <p>Enroll a student in a course</p>
            </div>

            <form action="../handlers/enrollStudentHandler.php" method="POST" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="student_id"><i class="fas fa-graduation-cap"></i> Student *</label>
                    <select name="student_id" id="student_id" required></select>
                </div>

                <div class="form-group">
                    <label for="assignment"><i class="fas fa-book"></i> Course *</label>
                    <select name="assignment" id="assignment" required></select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Enroll</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/enrollStudent.js"></script>

</body>

</html>
