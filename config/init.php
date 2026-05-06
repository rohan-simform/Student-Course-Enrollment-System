<?php

require_once __DIR__.'/constants.php';
require_once __DIR__.'/../classes/Database.php';
require_once __DIR__.'/../classes/User.php';
require_once __DIR__.'/../classes/Instructor.php';
require_once __DIR__.'/../classes/Student.php';
require_once __DIR__.'/../classes/Course.php';
require_once __DIR__.'/../classes/Enrollment.php';

require_once __DIR__.'/../helpers/Validator.php';
require_once __DIR__.'/../helpers/CsrfHelper.php';
require_once __DIR__.'/../helpers/AuthHelper.php';
require_once __DIR__.'/../helpers/Result.php';

// use classes\Course;

$db = new Database;
$conn = $db->connect();

$user = new User($conn);
$instructor = new Instructor($conn);
$student = new Student($conn);
$course = new Course($conn);
$enrollment = new Enrollment($conn);
