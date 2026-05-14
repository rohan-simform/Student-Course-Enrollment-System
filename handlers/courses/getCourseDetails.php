<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

$courseId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0);
$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : 0;


if ($courseId <= 0) {
    echo json_encode(Result::fail('Invalid course ID'));
    exit;
}

// Check if this is for students viewing details or admins/instructors editing
if (AuthHelper::isStudent()) {
    // Student viewing course details
    $userId = $_SESSION['user_id'];
    $backPath = 'availableCourses.php';

    try {
        // Try to get as enrolled course first
        $result = $course->getStudentCourseDetails($enrollmentId ,$userId, $courseId);

        if ($result['status']) {
            $backPath = 'listCourses.php';
        } else {
            // Fall back to available course
            $result = $course->getAvailableCourseDetails($userId, $courseId);
            $backPath = 'availableCourses.php';
        }

        if (! $result['status']) {
            throw new Exception($result['message']);
        }

        echo json_encode(Result::success('Course details fetched', [
            'course' => $result['data'],
            'backPath' => $backPath,
        ]));
        exit;
    } catch (Throwable $e) {
        echo json_encode(Result::fail('Server error', $e->getMessage()));
        exit;
    }
}

// Admin/Instructor editing course
if (! AuthHelper::isAdmin() && ! AuthHelper::isInstructor()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

// Instructor can only edit their own courses
if (AuthHelper::isInstructor() && ! $course->hasInstructor($courseId, $_SESSION['user_id'])) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $result = $course->getCourseById($courseId);

    if (! $result['status']) {
        echo json_encode(Result::fail('Course not found'));
        exit;
    }

    echo json_encode(Result::success('Course details fetched', $result['data']));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
