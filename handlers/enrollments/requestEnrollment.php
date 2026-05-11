<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/EnrollmentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (!AuthHelper::isStudent()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {

    $courseId = Validator::integer($_POST['course_id'] ?? null, 'Course ID', 1);
    $instructorId = Validator::integer($_POST['instructor_id'] ?? null, 'Instructor ID', 1);

} catch (Exception $e) {

    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$service = new EnrollmentService($conn);

echo json_encode(
    $service->requestEnrollment($_SESSION['user_id'], $courseId, $instructorId)
);