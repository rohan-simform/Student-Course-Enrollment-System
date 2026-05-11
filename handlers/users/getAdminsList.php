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
    $page = Validator::integer($_GET['page'] ?? 1, 'page', 1);
    $limit = Validator::integer($_GET['limit'] ?? 10, 'limit', 1);
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$userService = new UserService($conn);

try {
    $result = $userService->getUsers('admin', $page, $limit);

    if (! $result) {
        throw new Exception('Fetch failed');
    }

    echo json_encode(Result::success('Admins fetched', [
        'admins' => $result['data']['data'] ?? [],
        'pagination' => $result['data']['pagination'] ?? [],
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
