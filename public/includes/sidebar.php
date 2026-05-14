<?php
/**
 * Dynamic Sidebar Loader
 * Includes the appropriate sidebar based on user role
 * Requires init.php to be included first for proper session/config setup
 */

// Ensure init.php has been included to provide AuthHelper
// (it's included at the top of each page via init.php)

// Check user role using AuthHelper methods (now available from config/init.php)
if (AuthHelper::isStudent()) {
    include __DIR__ . '/student_sidebar.php';
} elseif (AuthHelper::isInstructor()) {
    include __DIR__ . '/instructor_sidebar.php';
} elseif (AuthHelper::isAdmin()) {
    include __DIR__ . '/admin_sidebar.php';
} else {
    // Default to admin sidebar if no recognized role
    include __DIR__ . '/admin_sidebar.php';
}
?>
