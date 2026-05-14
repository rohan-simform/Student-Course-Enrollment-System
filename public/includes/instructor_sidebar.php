<?php
/**
 * Instructor Sidebar Navigation
 */
?>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-chalkboard-user"></i> Instructor Portal
    </div>
    
    <div class="sidebar-nav-container">
        <div class="section-title">Dashboard</div>
        <a href="/public/dashboards/instructor_dashboard.php" class="nav-link">
            <i class="fas fa-tachometer-alt"></i> My Dashboard
        </a>
        
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
