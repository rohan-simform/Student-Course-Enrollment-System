<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/EnrollmentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

$currentUser = AuthHelper::user();

if (! $currentUser) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $enrollmentService = new EnrollmentService($conn);
    $result = $enrollmentService->getEnrollmentRequestsTable($currentUser['role'], $currentUser['user_id']);

    if (! $result['status']) {
        throw new Exception($result['message']);
    }

    echo json_encode($result['data']);

} catch (Throwable $e) {
    Logger::error($e, 'Enrollment Requests List');
    echo json_encode(Result::fail(MSG_UNEXPECTED_ERROR));
}

exit;
