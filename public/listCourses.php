<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Courses</title>
    <?php include __DIR__.'/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php
            $pageTitle = 'Course List';
            $userName = 'Admin';
            $userRole = 'Admin';

            include __DIR__.'/includes/dashboard_top_nav.php';
        ?>

        <!-- Table Wrapper -->
        <div class="table-wrapper">
            <table id="coursesTable" class="table table-striped table-bordered data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Available Seats</th>
                        <th>Max Seats</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="/public/js/datatable.js"></script>
    <script defer src="/public/js/functions.js"></script>
    <script defer src="/public/js/listCourses.js"></script>
    
</body>

</html>