<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';
require_once __DIR__.'/../../helpers/Logger.php';

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
    $courseService = new CourseService($conn);
    $result = $courseService->getCourseInstructorsTable($courseId);

    if (! $result['status']) {
        throw new Exception($result['message']);
    }

    echo json_encode($result['data']);

} catch (Throwable $e) {
    Logger::error($e, 'Course Instructors List');
    echo json_encode(Result::fail(MSG_UNEXPECTED_ERROR));
}

exit;
