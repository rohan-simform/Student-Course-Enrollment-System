<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../helpers/Logger.php';
require_once __DIR__.'/../../service/CourseService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::isStudent()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $courseService = new CourseService($conn);
    $result = $courseService->getAvailableCoursesTable($_SESSION['user_id']);

    if (! $result['status']) {
        throw new Exception($result['message']);
    }

    echo json_encode($result['data']);

} catch (Throwable $e) {
    Logger::error($e, 'Available Courses List');
    echo json_encode(Result::fail(MSG_UNEXPECTED_ERROR));
}

exit;
