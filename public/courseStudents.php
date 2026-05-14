<?php include __DIR__ . '/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Course Students</title>
    <?php include __DIR__ . '/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-content">
        <?php
        $pageTitle = 'Course Students';
        $userRole = AuthHelper::user()['role'] ?? 'user';
        $userName = ucfirst($userRole);
        include __DIR__ . '/includes/dashboard_top_nav.php';
        ?>

        <div class="page-header">
            <h1><i class="fas fa-user-graduate"></i> Students in Course</h1>
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
                        <th>ID</th>
                        <th>Student</th>
                        <th>Instructor</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody"></tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div id="pagination"></div>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/courseStudents.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
