<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Available Courses</title>
    <?php include __DIR__.'/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php
            $pageTitle = 'Available Courses';
            $userRole = AuthHelper::user()['role'] ?? 'user';
            $userName = ucfirst($userRole);
            include __DIR__.'/includes/dashboard_top_nav.php';
        ?>

        <div class="page-header">
            <h1><i class="fas fa-book"></i> Available Courses</h1>
        </div>

        <div class="table-wrapper">
            <table id="availableCoursesTable" class="table table-striped table-bordered data-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Duration</th>
                        <th>Available Seats</th>
                        <th>Max Seats</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="/public/js/datatable.js"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/availableCourses.js"></script>
    
</body>

</html>