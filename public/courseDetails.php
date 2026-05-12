<?php include __DIR__ . '/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Course Details</title>
    <?php include __DIR__ . '/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-content">
        <?php
        $pageTitle = 'Course Details';
        $userRole = AuthHelper::user()['role'] ?? 'user';
        $userName = ucfirst($userRole);
        include __DIR__ . '/includes/dashboard_top_nav.php';
        ?>

        <div class="page-header">
            <h1><i class="fas fa-book-open"></i> Course Details</h1>
            <div class="page-header-actions">
                <a href="listCourses.php" id="backBtn" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody id="detailsContainer"></tbody>
            </table>
        </div>
    </div>

    <script defer src="./js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/courseDetails.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
