<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/EnrollmentService.php';

if (!AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {

    if (!CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $id = Validator::integer($_POST['id'] ?? null, 'Enrollment ID', 1);

    $data = [];

    if (isset($_POST['instructor_id']) && $_POST['instructor_id'] !== '') {
        $data['instructor_id'] = Validator::integer($_POST['instructor_id'], 'Instructor ID', 1);
    }

    if (isset($_POST['status']) && $_POST['status'] !== '') {

        $allowed = ['requested', 'active', 'completed', 'rejected', 'withdrawn', 'canceled'];

        if (!in_array($_POST['status'], $allowed, true)) {
            throw new Exception('Invalid Status');
        }

        $data['status'] = $_POST['status'];
    }

    if (empty($data)) {
        throw new Exception('No changes submitted');
    }

} catch (Exception $e) {

    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$service = new EnrollmentService($conn);

echo json_encode($service->updateEnrollment($id, $data));