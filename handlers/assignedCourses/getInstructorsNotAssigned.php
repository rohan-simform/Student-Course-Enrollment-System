<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $courseId = Validator::integer($_GET['course_id'] ?? null, 'course_id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

try {
    $allInstructors = $instructor->getOptions();

    $result = $instructor->getOptionsNotAssigned($courseId);

    if (is_array($result) && array_key_exists('status', $result)) {
        if (! $result['status']) {
            echo json_encode(Result::fail($result['message'] ?? 'Failed to fetch instructors'));
            exit;
        }

        echo json_encode(Result::success('Available instructors fetched', $result['data'] ?? []));
        exit;
    }

    echo json_encode(Result::success('Available instructors fetched', $result));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
