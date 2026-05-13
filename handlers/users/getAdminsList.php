<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => MSG_INVALID_METHOD]);
    exit;
}

if (!AuthHelper::isAdmin()) {
    echo json_encode(['error' => MSG_UNAUTHORIZED]);
    exit;
}

$userService = new UserService($conn);

try {
    $result = $userService->getUsersTable(ROLE_ADMIN);
    if ($result['status']) {
        echo json_encode($result['data']);
    } else {
        echo json_encode(['error' => $result['message']]);
    }
} catch (Throwable $e) {
    Logger::error($e, 'Admins Table');
    echo json_encode(['error' => MSG_UNEXPECTED_ERROR]);
}

exit;