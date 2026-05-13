<?php

require_once __DIR__.'/../config/constants.php';
/**
 * Provides authentication and session helper methods.
 */
class AuthHelper {
    /**
     * Get current authenticated user from session.
     *
     * @return array|null
     */
    public static function user() {
        if (isset($_SESSION['user_id'], $_SESSION['role']) &&
            in_array($_SESSION['role'], ROLES, true)) {
            return [
                'user_id' => (int) $_SESSION['user_id'],
                'role' => $_SESSION['role'],
            ];
        }

        return null;
    }

    /**
     * Check if current user is admin.
     *
     * @return bool
     */
    public static function isAdmin() {
        $user = self::user();

        return $user && $user['role'] === ROLE_ADMIN;
    }

    /**
     * Check if current user is student.
     *
     * @return bool
     */
    public static function isStudent() {
        $user = self::user();

        return $user && $user['role'] === ROLE_STUDENT;
    }

    /**
     * Check if current user is instructor.
     *
     * @return bool
     */
    public static function isInstructor() {
        $user = self::user();

        return $user && $user['role'] === ROLE_INSTRUCTOR;
    }

    /**
     * Get dashboard path based on role.
     *
     * @param  string|null  $role
     * @return string
     */
    public static function dashboardPath($role = null) {
        if ($role === null) {
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
        }

        if ($role === ROLE_STUDENT) {
            return DASHBOARD_STUDENT_ROUTE;
        }

        if ($role === ROLE_INSTRUCTOR) {
            return DASHBOARD_INSTRUCTOR_ROUTE;
        }

        return DASHBOARD_ADMIN_ROUTE;
    }
}
