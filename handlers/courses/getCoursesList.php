<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {
    $page = Validator::integer($_GET['page'] ?? null, 'page', 1);
    $limit = Validator::integer($_GET['limit'] ?? 10, 'limit', 1);
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$currentUser = AuthHelper::user();
$courseService = new CourseService($conn);

try {
    if (AuthHelper::isStudent()) {
        // Student: Get enrolled courses
        $result = $courseService->getStudentCourses($currentUser['user_id'], $page, $limit);

        if (! $result['status']) {
            throw new Exception($result['message']);
        }

        echo json_encode(Result::success('Courses fetched', [
            'courses' => $result['data'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'role' => 'student',
        ]));

    } elseif (AuthHelper::isAdmin() || AuthHelper::isInstructor()) {
        // Admin/Instructor: Get all courses
        $result = $course->getCourses($currentUser['user_id'], $currentUser['role'], $page, $limit);

        if (! $result) {
            throw new Exception('Fetch failed');
        }

        echo json_encode(Result::success('Courses fetched', [
            'courses' => $result['data'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'role' => $currentUser['role'],
        ]));

    } else {
        echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    }

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
