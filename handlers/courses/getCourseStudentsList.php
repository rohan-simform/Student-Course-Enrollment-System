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
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

if ($courseId <= 0) {
    echo json_encode(Result::fail('Invalid course ID'));
    exit;
}

if (AuthHelper::isInstructor() && ! $course->hasInstructor($courseId, $_SESSION['user_id'])) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $result = $enrollment->getEnrollments('course_id', $courseId, $page, $limit);

    echo json_encode(Result::success('Course students fetched', [
        'students' => $result['data'] ?? [],
        'pagination' => $result['pagination'] ?? [],
        'courseId' => $courseId,
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
