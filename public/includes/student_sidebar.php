<?php
/**
 * Student Sidebar Navigation
 */
?>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-book-open"></i> Student Portal
    </div>
    
    <div class="sidebar-nav-container">
        <div class="section-title">Dashboard</div>
        <a href="/public/dashboards/student_dashboard.php" class="nav-link">
            <i class="fas fa-tachometer-alt"></i> My Dashboard
        </a>
        
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
