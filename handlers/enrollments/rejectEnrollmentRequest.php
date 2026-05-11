<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/EnrollmentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (!AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {

    $id = Validator::integer($_POST['id'] ?? null, 'Enrollment ID', 1);

} catch (Exception $e) {

    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$service = new EnrollmentService($conn);

echo json_encode($service->rejecteEnrollmentRequest($id));