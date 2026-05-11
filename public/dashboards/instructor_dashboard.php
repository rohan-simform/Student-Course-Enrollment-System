<?php 
include __DIR__ . '/../includes/init.php';
if (!AuthHelper::isInstructor()) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Instructor Dashboard</title>
    <?php include __DIR__ . '/../includes/dashboard_head.php'; ?>
    <!-- Instructor Dashboard CSS -->
    <link rel="stylesheet" href="/public/css/dashboards/instructor_dashboard.css">
    <!-- Dashboard JS -->
    <script src="/public/js/dashboards.js"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-chalkboard-user"></i> Instructor Portal
        </div>
        
        <div class="sidebar-nav-container">
            <div class="section-title">Teaching</div>
            <a href="/public/listCourses.php" class="nav-link">
                <i class="fas fa-book"></i> My Courses
            </a>
            <a href="/public/listEnrollments.php" class="nav-link">
                <i class="fas fa-users"></i> Enrolled Students
            </a>
            <a href="/public/enrollmentRequests.php" class="nav-link">
                <i class="fas fa-inbox"></i> Enrollment Requests
            </a>
            
            <div class="section-title">Actions</div>
            <a href="/public/enrollStudent.php" class="nav-link">
                <i class="fas fa-user-plus"></i> Enroll Student
            </a>
            
            <div class="section-title">Account</div>
            <a href="/public/profile.php" class="nav-link">
                <i class="fas fa-user-cog"></i> Edit Profile
            </a>
            <a href="/handlers/auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <?php
        $pageTitle = 'Instructor Dashboard';
        $userName = 'Instructor';
        $userRole = 'Instructor';
        include __DIR__ . '/../includes/dashboard_top_nav.php';
        ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1><i class="fas fa-graduation-cap"></i> Welcome Back, Instructor!</h1>
            <p>Manage your courses and enrolled students effectively</p>
        </div>

        <!-- Main Dashboard Content -->
        <div class="row">
            <!-- My Courses Section -->
            <div class="col-lg-8 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-book-open"></i> My Courses
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Courses you are teaching</p>
                        <hr>
                        <a href="../listCourses.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View All Courses
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="col-lg-4 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <a href="../enrollStudent.php" class="quick-action-btn btn btn-primary btn-sm" style="display: block; margin-bottom: 10px;">
                            <i class="fas fa-user-plus"></i> Enroll Student
                        </a>
                        <a href="../listEnrollments.php" class="quick-action-btn btn btn-info btn-sm" style="display: block;">
                            <i class="fas fa-users"></i> View Students
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students & Requests Section -->
        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i> Enrolled Students
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage students enrolled in your courses</p>
                        <hr>
                        <a href="../listEnrollments.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View All Students
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-inbox"></i> Enrollment Requests
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage pending enrollment requests</p>
                        <hr>
                        <a href="../enrollmentRequests.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-check"></i> Review Requests <span class="badge bg-danger instructor-requests-badge">3</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teaching Statistics -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Teaching Statistics
                    </div>
                    <div class="card-body">
                        <div class="instructor-stats">
                            <div class="stat-box">
                                <i class="fas fa-book"></i>
                                <h4 id="instructor-active-courses">8</h4>
                                <p>Active Courses</p>
                            </div>
                            <div class="stat-box">
                                <i class="fas fa-users"></i>
                                <h4 id="instructor-total-students">245</h4>
                                <p>Total Students</p>
                            </div>
                            <div class="stat-box">
                                <i class="fas fa-inbox"></i>
                                <h4 id="instructor-pending-requests">3</h4>
                                <p>Pending Requests</p>
                            </div>
                            <div class="stat-box">
                                <i class="fas fa-list-check"></i>
                                <h4 id="instructor-total-enrollments">156</h4>
                                <p>Total Enrollments</p>
                            </div>
                        </div>
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
