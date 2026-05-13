<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enrollments</title>
    <?php include __DIR__.'/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php
            $pageTitle = 'Enrollments';
            $userRole = AuthHelper::user()['role'] ?? 'user';
            $userName = ucfirst($userRole);
            include __DIR__.'/includes/dashboard_top_nav.php';
        ?>

        <!-- Table Wrapper -->
        <div class="table-wrapper">
            <table id="enrollmentsTable" class="table table-striped table-bordered data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Date</th>
                        <th>Status</th>
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
    <script defer src="/public/js/listEnrollments.js"></script>
    
</body>

</html>