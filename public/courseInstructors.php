<?php include __DIR__ . '/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Course Instructors</title>
    <?php include __DIR__ . '/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-content">
        <?php
        $pageTitle = 'Course Instructors';
        $userRole = AuthHelper::user()['role'] ?? 'user';
        $userName = ucfirst($userRole);
        include __DIR__ . '/includes/dashboard_top_nav.php';
        ?>

        <div class="page-header">
            <h1 id="courseTitle"><i class="fas fa-chalkboard-user"></i> Course Instructors</h1>
            <div class="page-header-actions">
                <a href="listCourses.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Instructor ID</th>
                        <th>Instructor Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="instructorTableBody"></tbody>
            </table>
        </div>
    </div>

    <script defer src="./js/functions.js"></script>
    <script defer src="./js/courseInstructors.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
