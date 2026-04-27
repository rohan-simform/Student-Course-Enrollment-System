<?php
class AuthHelper {
    public static function user(){
        if (isset($_SESSION['user_id'], $_SESSION['role']) && 
            in_array($_SESSION['role'], ['admin', 'student', 'instructor'], true)) {
            return [
                "user_id" => (int)$_SESSION['user_id'],
                "role" => $_SESSION['role']
            ];
        }
        return null;
    }
    public static function isAdmin(){
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }
    public static function isStudent(){
        $user = self::user();
        return $user && $user['role'] === 'student';
    }
    public static function isInstructor(){
        $user = self::user();
        return $user && $user['role'] === 'instructor';
    }

    public static function dashboardPath($role = null)
    {
        if ($role === null) {
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
        }

        if ($role === 'student') {
            return '/public/dashboards/student_dashboard.php';
        }

        if ($role === 'instructor') {
            return '/public/dashboards/instructor_dashboard.php';
        }

        return '/public/dashboards/admin_dashboard.php';
    }
}
