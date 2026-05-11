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

if (! in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_INSTRUCTOR])) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    // Get all students for dropdown
    $studentsResult = $user->getUsers(ROLE_STUDENT, 1, 10000);
    $students = $studentsResult['data'] ?? [];

    // Get courses available for assignment based on role
    if ($_SESSION['role'] === ROLE_ADMIN) {
        // Admin sees all assigned courses
        $assignmentsResult = $course->getAssignedCourses();
        $assignments = $assignmentsResult['data'] ?? [];
    } else {
        // Instructor sees only their assigned courses
        $coursesResult = $course->getCourses($_SESSION['user_id'], ROLE_INSTRUCTOR, 1, 10000);
        $myCourses = $coursesResult['data'] ?? [];

        $assignments = [];
        foreach ($myCourses as $c) {
            $assignments[] = [
                'course_id' => $c['course_id'],
                'course_name' => $c['course_name'],
                'instructor_id' => $_SESSION['user_id'],
                'instructor_name' => 'My Course',
            ];
        }
    }

    echo json_encode(Result::success('Enroll data fetched', [
        'students' => $students,
        'courses' => $assignments,
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
