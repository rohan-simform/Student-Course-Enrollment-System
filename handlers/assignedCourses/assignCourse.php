<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::isAdmin()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    if (! CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $courseId = Validator::integer($_POST['course_id'] ?? null, 'Course id');
    $instructorId = Validator::integer($_POST['instructor_id'] ?? null, 'Instructor id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$courseService = new CourseService($conn);
$result = $courseService->assignCourse($courseId, $instructorId);

// if ($result['status']) {
//     echo "<script>alert('".$result['message']."'); window.location='../public/listAssignedCourse.php';</script>";
// } else {
//     echo "<script>alert('".$result['message']."'); window.history.back();</script>";
// }
echo json_encode($result);