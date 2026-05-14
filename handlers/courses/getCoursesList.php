<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => MSG_INVALID_METHOD]);
    exit;
}

if (!AuthHelper::user()) {
    echo json_encode(['error' => MSG_UNAUTHORIZED]);
    exit;
}

$courseService = new CourseService($conn);
$currentUser = AuthHelper::user();

try {
    $result = $courseService->getCoursesTable($currentUser['user_id'], $currentUser['role']);
    
    if ($result['status']) {
        echo json_encode($result['data']);
    } else {
        echo json_encode(['error' => $result['message']]);
    }
} catch (Throwable $e) {
    Logger::error($e, 'Courses Table');
    echo json_encode(['error' => MSG_UNEXPECTED_ERROR]);
}

exit;
