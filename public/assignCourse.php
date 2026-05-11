<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Assign Course</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-tasks"></i> Assign Course</h2>
                <p>Assign a course to an instructor</p>
            </div>

            <form id="courseForm" action="../handlers/assignedCourses/assignCourse.php" method="POST" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="course_id"><i class="fas fa-book"></i> Course *</label>
                    <select name="course_id" id="course_id" required></select>
                </div>

                <div class="form-group">
                    <label for="instructor_id"><i class="fas fa-chalkboard-user"></i> Instructor *</label>
                    <select name="instructor_id" id="instructor_id" required></select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Assign Course</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/assignCourse.js"></script>

</body>

</html>