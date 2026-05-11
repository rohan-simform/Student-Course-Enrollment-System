<?php 
include __DIR__ . '/../includes/init.php';
if (!AuthHelper::isStudent()) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <?php include __DIR__ . '/../includes/dashboard_head.php'; ?>
    <!-- Student Dashboard CSS -->
    <link rel="stylesheet" href="/public/css/dashboards/student_dashboard.css">
    <!-- Dashboard JS -->
    <script src="/public/js/dashboards.js"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-book-open"></i> Student Portal
        </div>
        
        <div class="sidebar-nav-container">
            <div class="section-title">My Learning</div>
            <a href="/public/listCourses.php" class="nav-link">
                <i class="fas fa-book"></i> My Courses
            </a>
            <a href="/public/listEnrollments.php" class="nav-link">
                <i class="fas fa-clipboard-list"></i> My Enrollments
            </a>
            <a href="/public/enrollmentRequests.php" class="nav-link">
                <i class="fas fa-inbox"></i> My Requests
            </a>
            <a href="/public/availableCourses.php" class="nav-link">
                <i class="fas fa-search"></i> Available Courses
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
        $pageTitle = 'Student Dashboard';
        $userName = 'Student';
        $userRole = 'Student';
        include __DIR__ . '/../includes/dashboard_top_nav.php';
        ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1><i class="fas fa-graduation-cap"></i> Welcome Back, Student!</h1>
            <p>Continue your learning journey and explore new courses</p>
        </div>

        <!-- Main Dashboard Content -->
        <div class="row">
            <!-- My Courses Section -->
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-book"></i> My Courses
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Courses you are currently enrolled in</p>
                        <hr>
                        <a href="../listCourses.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Courses
                        </a>
                        <a href="../availableCourses.php" class="quick-action-btn btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Browse More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enrollments Section -->
            <div class="col-lg-6 col-md-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-list-check"></i> Enrollment Status
                    </div>
                    <div class="card-body">
                        <p class="text-muted">View and manage your enrollments</p>
                        <hr>
                        <a href="../listEnrollments.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-eye"></i> View Enrollments
                        </a>
                        <a href="../enrollmentRequests.php" class="quick-action-btn btn-info-custom">
                            <i class="fas fa-inbox"></i> My Requests <span class="badge bg-warning student-requests-badge">2</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Learning Progress Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Learning Progress
                    </div>
                    <div class="card-body">
                        <div class="progress-stats">
                            <div class="progress-stats-item">
                                <div class="stat-number" id="student-lp-active-courses">5</div>
                                <div class="stat-label">Active Courses</div>
                            </div>
                            <div class="progress-stats-item">
                                <div class="stat-number" id="student-lp-completed">12</div>
                                <div class="stat-label">Completed Courses</div>
                            </div>
                            <div class="progress-stats-item">
                                <div class="stat-number" id="student-overall-progress">78%</div>
                                <div class="stat-label">Overall Progress</div>
                            </div>
                            <div class="progress-stats-item">
                                <div class="stat-number" id="student-lp-total">23</div>
                                <div class="stat-label">Total Courses Attempted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card">
                    <div class="card-body stats-card">
                        <i class="fas fa-book" style="color: #667eea;"></i>
                        <h5 id="student-active-courses">5</h5>
                        <small>Active Courses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card">
                    <div class="card-body stats-card">
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        <h5 id="student-completed">12</h5>
                        <small>Completed Courses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card">
                    <div class="card-body stats-card">
                        <i class="fas fa-spinner" style="color: #ffc107;"></i>
                        <h5 id="student-in-progress">6</h5>
                        <small>In Progress</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="dashboard-card">
                    <div class="card-body stats-card">
                        <i class="fas fa-inbox" style="color: #17a2b8;"></i>
                        <h5 id="student-pending">2</h5>
                        <small>Pending Requests</small>
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
