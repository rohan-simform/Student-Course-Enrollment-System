<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Instructor List</title>
    <?php include __DIR__.'/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php
            $pageTitle = 'Instructor List';
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
                        <th>ID</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="instructorTableBody"></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div id="pagination"></div>
        </div>
    </div>

    <script defer src="/public/js/config.php"></script>
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/dashboardRedirect.js"></script>
    <script defer src="./js/listInstructors.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>