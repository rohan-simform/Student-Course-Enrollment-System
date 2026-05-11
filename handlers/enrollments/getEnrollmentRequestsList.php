<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {
    $page = Validator::integer($_GET['page'] ?? null, 'page', 1);
    $limit = Validator::integer($_GET['limit'] ?? 10, 'limit', 1);
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$currentUser = AuthHelper::user();

if (! $currentUser) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $role = $currentUser['role'];
    $userId = $currentUser['user_id'];

    if ($role === ROLE_ADMIN) {
        $result = $enrollment->getEnrollments(null, null, $page, $limit, 'requested');
    } elseif ($role === ROLE_INSTRUCTOR) {
        $result = $enrollment->getEnrollments('instructor_id', $userId, $page, $limit, 'requested');
    } else {
        $result = $enrollment->getEnrollments('student_id', $userId, $page, $limit, 'requested');
    }

    echo json_encode(Result::success('Enrollment requests fetched', [
        'requests' => $result['data'] ?? [],
        'pagination' => $result['pagination'] ?? [],
        'role' => $role,
        'dashboard' => AuthHelper::dashboardPath($role),
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
