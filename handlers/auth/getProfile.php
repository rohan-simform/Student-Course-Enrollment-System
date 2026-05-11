<?php

session_start();
header('Content-Type: application/json');
require_once __DIR__.'/../../helpers/AuthHelper.php';
require_once __DIR__.'/../../service/UserService.php';
require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

$currentUser = AuthHelper::user();

if (! $currentUser) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

$userService = new UserService($conn);
$result = $userService->getProfile($currentUser['user_id']);

echo json_encode($result);
