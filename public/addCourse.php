<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Create Course</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="form-wrapper" style="margin-left: 280px; padding: 30px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-book-plus"></i> Create Course</h2>
                <p>Add a new course to the system</p>
            </div>

            <form action="../handlers/addCourse.php" method="POST" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="name"><i class="fas fa-book"></i> Course Name *</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="durationWeeks"><i class="fas fa-hourglass-end"></i> Number of Weeks *</label>
                    <input type="number" name="durationWeeks" id="durationWeeks" min="1" required>
                </div>

                <div class="form-group">
                    <label for="maxSeats"><i class="fas fa-users"></i> Maximum Seats</label>
                    <input type="number" name="maxSeats" id="maxSeats" min="1">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-plus"></i> Create Course</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="./js/functions.js"></script>
    <script defer src="./js/validator.js"></script>
    <script defer src="./js/addCourse.js"></script>

</body>

</html>