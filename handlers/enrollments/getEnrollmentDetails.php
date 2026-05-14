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
    $enrollmentId = Validator::integer($_GET['id'] ?? null, 'id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

try {
    $result = $enrollment->getById($enrollmentId);

    if (! $result['status']) {
        echo json_encode(Result::fail('Enrollment not found'));
        exit;
    }

    $data = $result['data'];

    // Instructor can only edit their own enrollments
    if (AuthHelper::isInstructor() && $data['instructor_id'] !== $_SESSION['user_id']) {
        echo json_encode(Result::fail(MSG_UNAUTHORIZED));
        exit;
    }

    // Get course instructors for dropdown
    $instructors = $instructor->getOptions();
    $courseInstructors = [];

    foreach ($instructors as $row) {
        if ($course->hasInstructor($data['course_id'], $row['id'])) {
            $courseInstructors[] = $row;
        }
    }

    echo json_encode(Result::success('Enrollment details fetched', [
        'enrollment' => $data,
        'courseInstructors' => $courseInstructors,
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
