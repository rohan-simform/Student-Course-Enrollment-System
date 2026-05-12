<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../helpers/Logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::isStudent()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $page = Validator::integer($_GET['page'] ?? null, 'page', 1);
    $limit = Validator::integer($_GET['limit'] ?? 10, 'limit', 1);
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

try {
    $result = $course->getAvailableCourses($_SESSION['user_id'], $page, $limit);

    if (! $result['status']) {
        throw new Exception($result['message']);
    }

    echo json_encode(Result::success('Available courses fetched', [
        'courses' => $result['data'] ?? [],
        'pagination' => $result['pagination'] ?? [],
        'dashboard' => AuthHelper::dashboardPath(ROLE_STUDENT),
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
