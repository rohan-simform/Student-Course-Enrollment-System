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

    $role = $_POST['role'] ?? '';

    $data = [];

    $data['email'] = Validator::email($_POST['email'] ?? null);
    // $data['password'] = Validator::password($_POST['password'] ?? null);
    $data['password'] = $_POST['password'];

    if ($role === ROLE_STUDENT || $role === ROLE_INSTRUCTOR) {
        $data['name'] = Validator::name($_POST['name'] ?? null);
        $data['phone'] = Validator::phone($_POST['phone'] ?? null);
    }

    if ($role === ROLE_INSTRUCTOR) {
        $data['salary'] = Validator::salary($_POST['salary'] ?? null);
    }

} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$userService = new UserService($conn);

$result = $userService->addUser($role, $data);

if ($result['status']) {
    if ($role === ROLE_ADMIN) {
        $result['redirect'] = '../public/listAdmins.php';
    } elseif ($role === ROLE_INSTRUCTOR) {
        $result['redirect'] = '../public/listInstructors.php';
    } elseif ($role === ROLE_STUDENT) {
        $result['redirect'] = '../public/listStudents.php';
    } else {
        $result['redirect'] = AuthHelper::dashboardPath($role);
    }
}

echo json_encode($result);