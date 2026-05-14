<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';

if (!AuthHelper::isAdmin()) {
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

    $name = Validator::name($_POST['name'] ?? null, 'Course Name');
    $durationWeeks = Validator::integer($_POST['durationWeeks'] ?? null, 'Duration Weeks', 1);
    $maxSeats = Validator::integer($_POST['maxSeats'] ?? null, 'Max Seats', 1);

} catch (Exception $e) {

    echo json_encode(Result::fail(MSG_VALIDATION_FAILED, $e->getMessage()));
    exit;
}

$courseService = new CourseService($conn);

$result = $courseService->addCourse($name, $durationWeeks, $maxSeats);

if ($result['status']) {
    $result['redirect'] = '../public/listCourses.php';
}

echo json_encode($result);