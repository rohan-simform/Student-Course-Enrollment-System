<?php
/**
 * Admin Sidebar Navigation
 * Use this in all admin list/table pages
 */
?>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-graduation-cap"></i> Admin Portal
    </div>
    
    <div class="sidebar-nav-container">
        <div class="section-title">Dashboard</div>
        <a href="/public/dashboards/admin_dashboard.php" class="nav-link">
            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
        </a>
        
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
