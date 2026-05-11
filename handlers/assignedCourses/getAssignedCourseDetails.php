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
    $courseId = Validator::integer($_GET['course_id'] ?? null, 'course_id');
    $instructorId = Validator::integer($_GET['instructor_id'] ?? null, 'instructor_id');
} catch (Exception $e) {
    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

try {
    $courses = $course->getOptions()['data'];
    $instructors = $instructor->getOptions();

    // Find the selected course
    $selectedCourse = null;
    foreach ($courses as $row) {
        if ($row['id'] == $courseId) {
            $selectedCourse = $row;
            break;
        }
    }

    if (! $selectedCourse) {
        echo json_encode(Result::fail('Course not found'));
        exit;
    }

    echo json_encode(Result::success('Assignment details fetched', [
        'courses' => $courses,
        'instructors' => $instructors,
        'courseId' => $courseId,
        'instructorId' => $instructorId,
        'selectedCourse' => $selectedCourse,
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
