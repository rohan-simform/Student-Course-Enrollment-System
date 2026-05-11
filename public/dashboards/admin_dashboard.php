<?php 
include __DIR__ . '/../includes/init.php';
if (!AuthHelper::isAdmin()) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <?php include __DIR__ . '/../includes/dashboard_head.php'; ?>
    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="/public/css/dashboards/admin_dashboard.css">
    <!-- Dashboard JS -->
    <script src="/public/js/dashboards.js"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-graduation-cap"></i> Admin Portal
        </div>
        
        <div class="sidebar-nav-container">
            <div class="section-title">Management</div>
            <a href="/public/listAdmins.php" class="nav-link">
                <i class="fas fa-user-shield"></i> Admins
            </a>
            <a href="/public/listStudents.php" class="nav-link">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <a href="/public/listInstructors.php" class="nav-link">
                <i class="fas fa-chalkboard-user"></i> Instructors
            </a>
            
            <div class="section-title">Courses</div>
            <a href="/public/listCourses.php" class="nav-link">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="/public/listAssignedCourse.php" class="nav-link">
                <i class="fas fa-link"></i> Assigned Courses
            </a>
            
            <div class="section-title">Enrollment</div>
            <a href="/public/listEnrollments.php" class="nav-link">
                <i class="fas fa-clipboard-list"></i> Enrollments
            </a>
            <a href="/public/enrollmentRequests.php" class="nav-link">
                <i class="fas fa-inbox"></i> Requests
            </a>
            
            <div class="section-title">Actions</div>
            <a href="/public/addUser.php" class="nav-link">
                <i class="fas fa-user-plus"></i> Add User
            </a>
            <a href="/public/addCourse.php" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add Course
            </a>
            <a href="/public/assignCourse.php" class="nav-link">
                <i class="fas fa-dolly"></i> Assign Course
            </a>
            <a href="/public/enrollStudent.php" class="nav-link">
                <i class="fas fa-edit"></i> Enroll Student
            </a>
            <a href="/public/bulkUpload.php" class="nav-link">
                <i class="fas fa-cloud-upload-alt"></i> Bulk Upload
            </a>
            
            <div class="section-title">Account</div>
            <a href="/public/profile.php" class="nav-link">
                <i class="fas fa-user-cog"></i> Profile
            </a>
            <a href="/handlers/auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <?php
        $pageTitle = 'Admin Dashboard';
        $userName = 'Admin';
        $userRole = 'Admin';
        include __DIR__ . '/../includes/dashboard_top_nav.php';
        ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1><i class="fas fa-dashboard"></i> Welcome Back, Admin!</h1>
            <p>Manage your enrollment system efficiently from your dashboard</p>
        </div>

        <!-- Main Dashboard Content -->
        <div class="row">
            <!-- User Management Section -->
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i> User Management
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage all users in the system</p>
                        <hr>
                        <a href="../listAdmins.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Admins
                        </a>
                        <a href="../listStudents.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Students
                        </a>
                        <a href="../listInstructors.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Instructors
                        </a>
                        <hr>
                        <a href="../addUser.php" class="quick-action-btn btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add User
                        </a>
                    </div>
                </div>
            </div>

            <!-- Course Management Section -->
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-book-open"></i> Course Management
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage courses and assignments</p>
                        <hr>
                        <a href="../listCourses.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Courses
                        </a>
                        <a href="../listAssignedCourse.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> Assigned Courses
                        </a>
                        <hr>
                        <a href="../addCourse.php" class="quick-action-btn btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Course
                        </a>
                        <a href="../assignCourse.php" class="quick-action-btn btn btn-success btn-sm">
                            <i class="fas fa-link"></i> Assign
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Section -->
        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-file-alt"></i> Enrollment Management
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Handle student enrollments and requests</p>
                        <hr>
                        <a href="../listEnrollments.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Enrollments
                        </a>
                        <a href="../enrollmentRequests.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-inbox"></i> Requests <span class="badge bg-danger admin-request-count">3</span>
                        </a>
                        <hr>
                        <a href="../enrollStudent.php" class="quick-action-btn btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Enroll Student
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bulk Operations Section -->
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-cogs"></i> Bulk Operations
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Perform bulk actions on the system</p>
                        <hr>
                        <a href="../bulkUpload.php" class="quick-action-btn btn btn-warning btn-sm">
                            <i class="fas fa-cloud-upload-alt"></i> Bulk Create Students
                        </a>
                        <hr>
                        <small class="text-secondary">
                            <i class="fas fa-info-circle"></i> Upload CSV files to create multiple students at once
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section (Optional) -->
        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-body">
                        <i class="fas fa-users" style="font-size: 32px; color: #667eea; margin-bottom: 10px;"></i>
                        <h5 id="admin-total-students">250</h5>
                        <small class="text-muted">Total Students</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-body">
                        <i class="fas fa-book" style="font-size: 32px; color: #764ba2; margin-bottom: 10px;"></i>
                        <h5 id="admin-active-courses">45</h5>
                        <small class="text-muted">Active Courses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-user" style="font-size: 32px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h5 id="admin-instructors">120</h5>
                        <small class="text-muted">Total Instructors</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card text-center">
                    <div class="card-body">
                        <i class="fas fa-inbox" style="font-size: 32px; color: #ffc107; margin-bottom: 10px;"></i>
                        <h5 id="admin-pending-requests">8</h5>
                        <small class="text-muted">Pending Requests</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load dashboard data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });
    </script>
</body>
</html>
