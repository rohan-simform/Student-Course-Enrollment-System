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
            $userName = 'Admin';
            $userRole = 'Admin';
            
            include __DIR__.'/includes/dashboard_top_nav.php';
        ?>

        <!-- Search & Filter Bar -->
        <div class="search-filter-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search here...">
            </div>
        </div>

        <!-- Table Wrapper -->
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Instructor ID</th>
                        <th>Instructor Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="courseTableBody"></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div id="pagination"></div>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/listAssignedCourse.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>