<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';

if (!AuthHelper::isAdmin()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {

    if (!CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $courseId = Validator::integer($_POST['course_id'] ?? null, 'Course ID', 1);

} catch (Exception $e) {

    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$courseService = new CourseService($conn);

echo json_encode($courseService->deleteCourse($courseId));