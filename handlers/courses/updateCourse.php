<?php

session_start();
require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/CourseService.php';

if (! AuthHelper::user()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/listCourses.php');
    exit;
}

try {
    if (! CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    $data = [];
    $courseId = Validator::integer($_POST['course_id'] ?? null, 'Course ID', 1);

    if (! empty($_POST['name'])) {
        $data['name'] = Validator::name($_POST['name'], 'Course Name');
    }

    if (isset($_POST['duration_weeks']) && $_POST['duration_weeks'] !== '') {
        $data['duration_weeks'] = Validator::integer($_POST['duration_weeks'], 'Duration Weeks', 1);
    }

    if (isset($_POST['max_seats']) && $_POST['max_seats'] !== '') {
        $data['max_seats'] = Validator::integer($_POST['max_seats'], 'Max Seats', 1);
    }

    if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
        $data['is_active'] = Validator::status($_POST['is_active'], 'Course Status');
    }

    if (empty($data)) {
        throw new Exception('No changes submitted');
    }

} catch (Exception $e) {
    $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    echo "<script>alert('error: {$msg}'); window.history.back();</script>";
    exit;
}
$courseService = new CourseService($conn);

$result = $courseService->updateCourse($courseId, $data);

$msg = htmlspecialchars($result['message'], ENT_QUOTES);

if ($result['status']) {
    echo "<script>alert('{$msg}'); window.location='../public/listCourses.php';</script>";
} else {
    echo "<script>alert('Error: {$msg}'); window.history.back();</script>";
}
