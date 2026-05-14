<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Assigned Courses</title>
    <?php include __DIR__.'/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php
            $pageTitle = 'Assigned Course Details';
            $userRole = AuthHelper::user()['role'] ?? 'user';
            $userName = ucfirst($userRole);
            
            include __DIR__.'/includes/dashboard_top_nav.php';
        ?>

        <div class="page-header">
            <h1><i class="fas fa-cube"></i> Assigned Courses</h1>
        </div>

        <div class="table-wrapper">
            <table id="assignedCoursesTable" class="table table-striped table-bordered data-table">
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Instructor ID</th>
                        <th>Instructor Name</th>
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
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/listAssignedCourse.js"></script>
    
</body>

</html>