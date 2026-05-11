<?php

session_start();
header('Content-Type: application/json');
require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {
    if (! CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $targetUserId = Validator::integer($_POST['user_id'] ?? $_SESSION['user_id'], 'User ID', 1);

    $data = [];

    if (! empty($_POST['email'])) {
        $data['email'] = Validator::email($_POST['email']);
    }

    if (! empty($_POST['name'])) {
        $data['name'] = Validator::name($_POST['name']);
    }

    if (isset($_POST['phone'])) {
        $data['phone'] = Validator::phone($_POST['phone']);
    }

    if (! empty($_POST['password'])) {
        $data['password'] = Validator::password($_POST['password']);
    }

    if (isset($_POST['status']) && $_POST['status'] !== '') {
        $data['status'] = Validator::status($_POST['status']);
    }

    if (isset($_POST['salary']) && $_POST['salary'] !== '') {
        $data['salary'] = Validator::salary($_POST['salary']);
    }

    if (empty($data)) {
        throw new Exception('No changes submitted');
    }

} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$userService = new UserService($conn);

$result = $userService->updateUser($targetUserId, $data);

echo json_encode($result);