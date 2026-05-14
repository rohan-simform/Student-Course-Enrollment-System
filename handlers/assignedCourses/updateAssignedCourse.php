<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../service/CourseService.php';

if (!AuthHelper::isAdmin()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {

    $courseId = Validator::integer($_POST['course_id'] ?? null,'Course ID',1);

    $instructorId = Validator::integer($_POST['instructor_id'] ?? null,'Instructor ID',1);

    $courseService = new CourseService($conn);

    $result = $courseService->removeAssignment($courseId,$instructorId);

} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED,$e->getMessage()));
    exit;
}

echo json_encode($result);