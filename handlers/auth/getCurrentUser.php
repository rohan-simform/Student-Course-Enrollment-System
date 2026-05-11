<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {
    $user = AuthHelper::user();

    if (! $user) {
        echo json_encode(Result::fail('User not authenticated'));
        exit;
    }

    echo json_encode(Result::success('Current user fetched', [
        'user_id' => $user['user_id'],
        'role' => $user['role'],
        'dashboard' => AuthHelper::dashboardPath($user['role']),
        'isAdmin' => AuthHelper::isAdmin(),
        'isStudent' => AuthHelper::isStudent(),
        'isInstructor' => AuthHelper::isInstructor(),
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
