<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $userId = Validator::integer($_GET['user_id'] ?? null, 'user_id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

// Student can only view their own profile, Admin can view any student
if (AuthHelper::isStudent() && $userId !== $_SESSION['user_id']) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

$userService = new UserService($conn);

try {
    $result = $userService->getProfile($userId);

    if (! $result['status'] || $result['data']['role'] !== ROLE_STUDENT) {
        echo json_encode(Result::fail('Student not found'));
        exit;
    }

    echo json_encode(Result::success('Student details fetched', $result['data']));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
