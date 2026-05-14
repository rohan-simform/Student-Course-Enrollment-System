<?php

session_start();
header('Content-Type: application/json');
require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/AuthService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {
    if (! CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $email = Validator::email($_POST['email'] ?? null);
    $password = Validator::password($_POST['password'] ?? null);
    $name = Validator::name($_POST['name'] ?? null);
    $phone = Validator::phone($_POST['phone'] ?? null);
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$authService = new AuthService($conn);

$result = $authService->register($email, $password, $name, $phone);

echo json_encode($result);