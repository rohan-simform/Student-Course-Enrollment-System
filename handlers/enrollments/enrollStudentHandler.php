<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/EnrollmentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (!AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {

    if (!CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $studentId = $_POST['student_id'] ?? null;
    $assignment = $_POST['assignment'] ?? '';

    if (!$studentId || !$assignment || strpos($assignment, '|') === false) {
        throw new Exception('Invalid input');
    }

    [$courseId, $instructorId] = explode('|', $assignment);

    $studentId = Validator::integer($studentId, 'Student ID', 1);
    $courseId = Validator::integer($courseId, 'Course ID', 1);
    $instructorId = Validator::integer($instructorId, 'Instructor ID', 1);

} catch (Exception $e) {

    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$service = new EnrollmentService($conn);

echo json_encode($service->enrollStudent($studentId, $courseId, $instructorId));