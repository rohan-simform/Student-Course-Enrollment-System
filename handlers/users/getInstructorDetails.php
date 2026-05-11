<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::isAdmin()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $userId = Validator::integer($_GET['user_id'] ?? null, 'user_id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$userService = new UserService($conn);

try {
    $result = $userService->getProfile($userId);

    if (! $result['status'] || $result['data']['role'] !== ROLE_INSTRUCTOR) {
        echo json_encode(Result::fail('Instructor not found'));
        exit;
    }

    echo json_encode(Result::success('Instructor details fetched', $result['data']));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
