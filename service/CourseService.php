<?php

require_once __DIR__.'/../classes/Course.php';
require_once __DIR__.'/../classes/Enrollment.php';
require_once __DIR__.'/../classes/Instructor.php';

require_once __DIR__.'/../helpers/Permission.php';
require_once __DIR__.'/../helpers/Result.php';
require_once __DIR__.'/../helpers/Logger.php';
require_once __DIR__.'/../helpers/AuthHelper.php';

/**
 * Handles course business logic and permissions.
 */
class CourseService {
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Course model instance.
     *
     * @var Course
     */
    private $course;

    /**
     * Enrollment model instance.
     *
     * @var Enrollment
     */
    private $enrollment;

    /**
     * Instructor model instance.
     *
     * @var Instructor
     */
    private $instructor;

    /**
     * Create a new CourseService instance.
     *
     * @param  mysqli  $db  Database connection.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->course = new Course($db);
        $this->enrollment = new Enrollment($db);
        $this->instructor = new Instructor($db);
        Permission::init($this->conn);
    }

    /**
     * Create a new course.
     *
     * @param  string  $name
     * @param  int  $durationWeeks
     * @param  int  $maxSeats
     * @return array
     */
    public function addCourse($name, $durationWeeks, $maxSeats) {
        if (! Permission::check('course.create')) {
            return Result::fail(MSG_UNAUTHORIZED);
        }
        try {
            $courseId = $this->course->create($name, $durationWeeks, $maxSeats);

            return Result::success('Course created successfully', ['course_id' => $courseId]);

        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Course create');

            return Result::fail('Failed to create course');
        } catch (Throwable $e) {
            Logger::error($e, 'Course create');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Update course data.
     *
     * @param  int  $courseId
     * @param  array  $data
     * @return array
     */
    public function updateCourse($courseId, $data) {
        try {
            if (! Permission::check('course.update', $courseId)) {
                return Result::fail(MSG_UNAUTHORIZED);
            }

            if (! $this->course->exists($courseId)) {
                return Result::fail('Invalid course');
            }

            $affectedRowCount = $this->course->updateCourse($courseId, $data);

            if (isset($data['is_active'])) {

                if ($data['is_active'] === COURSE_STATUS_INACTIVE) {
                    $fromStatus = COURSE_STATUS_ACTIVE;
                    $toStatus = ENROLLMENT_STATUS_COURSE_INACTIVE;
                } else {
                    $fromStatus = ENROLLMENT_STATUS_COURSE_INACTIVE;
                    $toStatus = COURSE_STATUS_ACTIVE;
                }

                $affectedRowCount += $this->enrollment->updateStatusByCourse($courseId, $fromStatus, $toStatus);
            }

            if ($affectedRowCount > 0) {
                return Result::success('Course updated successfully');
            } else {
                return Result::fail('Course not found');
            }
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Course update');

            return Result::fail('Failed to Update Course');
        } catch (Throwable $e) {
            Logger::error($e, 'Course update');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Deactivate a course.
     *
     * @param  int  $courseId
     * @return array
     */
    public function deleteCourse($courseId) {
        try {
            if (! Permission::check('course.delete', $courseId)) {
                return Result::fail(MSG_UNAUTHORIZED);
            }

            if (! $this->course->exists($courseId)) {
                return Result::fail('Invalid course');
            }

            $affectedRowCount = $this->course->deleteCourse($courseId);

            $affectedRowCount += $this->enrollment->updateStatusByCourse($courseId, COURSE_STATUS_ACTIVE, ENROLLMENT_STATUS_COURSE_INACTIVE);

            if ($affectedRowCount > 0) {
                return Result::success('Course deactivated successfully');
            } else {
                return Result::fail('Course not found');
            }
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Course update');

            return Result::fail('Failed to Update Course');
        } catch (Throwable $e) {
            Logger::error($e, 'Course update');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Assign course to instructor.
     *
     * @param  int  $courseId
     * @param  int  $instructorId
     * @return array
     */
    public function assignCourse($courseId, $instructorId) {
        if (! Permission::check('course.assign')) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            if ($this->course->isAlreadyAssigned($courseId, $instructorId)) {
                return Result::fail('Alredy Assigned');
            }
            $this->course->assignCourse($courseId, $instructorId);

            return Result::success('Course Assigned successfully');

        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Course assign');

            return Result::fail('Failed to assign course');
            exit;
        } catch (Throwable $e) {
            Logger::error($e, 'Course assign');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Update course assignment.
     *
     * @param  int  $courseId
     * @param  int  $oldInstructorId
     * @param  int  $newInstructorId
     * @return array
     */
    public function updateAssignment($courseId, $oldInstructorId, $newInstructorId) {
        try {
            if (! Permission::check('course.assign')) {
                return Result::fail(MSG_UNAUTHORIZED);
            }

            if (! $this->course->exists($courseId)) {
                return Result::fail('Invalid course');
            }

            if (! $this->instructor->exists($newInstructorId)) {
                return Result::fail('Invalid instructor');
            }

            if ($this->course->isAlreadyAssigned($courseId, $newInstructorId)) {
                return Result::fail('Already assigned to selected instructor');
            }

            $affectedRowCount = $this->course->updateAssignment($courseId, $oldInstructorId, $newInstructorId);

            if ($affectedRowCount > 0) {
                return Result::success('Course Assignment updated successfully');
            } else {
                return Result::fail('Assignment not found');
            }
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Course Assignment update');

            return Result::fail('Failed to Update Course Assignment');
        } catch (Throwable $e) {
            Logger::error($e, 'Course Assignment update');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Remove course assignment.
     *
     * @param  int  $courseId
     * @param  int  $instructorId
     * @return array
     */
    public function removeAssignment($courseId, $instructorId) {
        if (! Permission::check('course.assign')) {
            return Result::fail(MSG_UNAUTHORIZED);
        }
        try {
            if (! $this->course->isAlreadyAssigned($courseId, $instructorId)) {
                return Result::fail('Assignment does not Exists');
            }

            $affectedRowCount = $this->course->deleteAssignment($courseId, $instructorId);

            if ($affectedRowCount > 0) {
                return Result::success('Assignment removed successfully');
            } else {
                return Result::fail('Assignment not found');
            }

        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Remove course assign');

            return Result::fail('Failed to Remove Course Assignment');
        } catch (Throwable $e) {
            Logger::error($e, 'Course assign');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Get student enrolled courses.
     *
     * @param  int  $studentId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getStudentCourses($studentId, $page = 1, $limit = 10) {
        if (! Permission::check('user.read', $studentId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            return $this->course->getStudentCourses($studentId, $page, $limit);
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Student courses list');

            return Result::fail('Failed to load courses');
        } catch (Throwable $e) {
            Logger::error($e, 'Student courses list');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Get available courses for student.
     *
     * @param  int  $studentId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getAvailableCourses($studentId, $page = 1, $limit = 10) {
        if (! Permission::check('user.read', $studentId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            return $this->course->getAvailableCourses($studentId, $page, $limit);
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Available courses list');

            return Result::fail('Failed to load available courses');
        } catch (Throwable $e) {
            Logger::error($e, 'Available courses list');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Get enrolled course details for student.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return array
     */
    public function getStudentCourseDetails($studentId, $courseId) {
        if (! Permission::check('user.read', $studentId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            $result = $this->course->getStudentCourseDetails($studentId, $courseId);

            if ($result['status']) {
                return $result;
            }

            return Result::fail('Course not found');
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Student course details');

            return Result::fail('Failed to load course details');
        } catch (Throwable $e) {
            Logger::error($e, 'Student course details');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Get available course details for student.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return array
     */
    public function getAvailableCourseDetails($studentId, $courseId) {
        if (! Permission::check('user.read', $studentId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            $result = $this->course->getAvailableCourseDetails($studentId, $courseId);

            if ($result['status']) {
                return $result;
            }

            return Result::fail('Course not found');
        } catch (mysqli_sql_exception $e) {
            Logger::error($e, 'Available course details');

            return Result::fail('Failed to load course details');
        } catch (Throwable $e) {
            Logger::error($e, 'Available course details');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }
}
