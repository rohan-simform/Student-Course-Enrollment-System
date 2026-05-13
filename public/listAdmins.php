<?php 

include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin List</title>
    <?php include __DIR__.'/includes/table_head.php'; ?>
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php
            $pageTitle = 'Admin List';
            $userRole = AuthHelper::user()['role'] ?? 'user';
            $userName = ucfirst($userRole);
            include __DIR__.'/includes/dashboard_top_nav.php';
        ?>

        <!-- Table Wrapper -->
        <div class="table-wrapper">
            <table id="adminsTable" class="table table-striped table-bordered data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
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
    <script defer src="./js/functions.js"></script>
    <script defer src="./js/listAdmins.js"></script>
    
</body>

</html>