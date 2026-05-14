<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::isAdmin()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $assigned = $course->getAssignedCourses()['data'];

    if (! $assigned) {
        throw new Exception('Fetch failed');
    }

    echo json_encode(Result::success('Assigned courses fetched', [
        'courses' => $assigned ?? [],
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
