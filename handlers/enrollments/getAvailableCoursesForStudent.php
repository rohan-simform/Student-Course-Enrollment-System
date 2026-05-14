<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

if (! in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_INSTRUCTOR])) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $studentId = Validator::integer($_GET['student_id'] ?? null, 'student_id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

try {
    $result = $course->getEnrollOptions($studentId);

    // For instructors, filter by their own courses
    if ($_SESSION['role'] === ROLE_INSTRUCTOR) {
        $filtered = array_filter($result['data'], function ($course) {
            return $course['instructor_id'] === $_SESSION['user_id'];
        });
        $result['data'] = array_values($filtered);
    }

    echo json_encode(Result::success('Available courses fetched', $result['data']));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
