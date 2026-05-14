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
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'];

    if ($password === '') {
        throw new Exception('Password is required');
    }

} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$authService = new AuthService($conn);

$result = $authService->login($email, $password, $captcha);

if ($result['status']) 
    $result['redirect'] = AuthHelper::dashboardPath($result['data']['role']);


echo json_encode($result);