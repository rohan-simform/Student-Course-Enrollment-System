<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::isAdmin() && ! AuthHelper::isInstructor()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

$courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

if ($courseId <= 0) {
    echo json_encode(Result::fail('Invalid course ID'));
    exit;
}

if (AuthHelper::isInstructor() && ! $course->hasInstructor($courseId, $_SESSION['user_id'])) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $result = $instructor->getInstructorsByCourse($courseId);

    echo json_encode(Result::success('Course instructors fetched', [
        'instructors' => $result['data'] ?? [],
        'courseId' => $courseId,
        'isAdmin' => AuthHelper::isAdmin(),
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
