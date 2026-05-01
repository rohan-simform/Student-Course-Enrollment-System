<?php

require_once __DIR__ . '/../classes/Enrollment.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../classes/User.php';

require_once __DIR__ . '/../helpers/Permission.php';
require_once __DIR__ . '/../helpers/Result.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

/**
 * Handles enrollment business logic and permissions.
 */
class EnrollmentService
{
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Enrollment model instance.
     *
     * @var Enrollment
     */
    private $enrollment;

    /**
     * Course model instance.
     *
     * @var Course
     */
    private $course;

    /**
     * User model instance.
     *
     * @var User
     */
    private $user;

    /**
     * Create a new EnrollmentService instance.
     *
     * @param mysqli $db Database connection.
     */
    public function __construct($db)
    {
        $this->conn = $db;
        $this->enrollment = new Enrollment($db);
        $this->course = new Course($db);
        $this->user = new User($db);
        Permission::init($db);
    }

    /**
     * Enroll student directly into a course.
     *
     * @param int $studentId
     * @param int $courseId
     * @param int $instructorId
     * @return array
     */
    public function enrollStudent($studentId, $courseId, $instructorId){
        if (!Permission::check('course.enroll', $courseId))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->user->exists(['id'=>$studentId, 'role'=>ROLE_STUDENT]))
                return Result::fail('Student not found');

            if (!$this->user->exists(['id'=>$instructorId, 'role'=>ROLE_INSTRUCTOR]))
                return Result::fail('Instructor not found');
            
            if (!$this->course->exists($courseId))
                return Result::fail('Course not found');

            if (!$this->course->isActive($courseId))
                return Result::fail('Course is inactive');

            if (!$this->course->hasInstructor($courseId, $instructorId))
                return Result::fail('Instructor is not assigned to this course');

            if ($this->enrollment->hasEnrollmentStatus($studentId, $courseId, [ENROLLMENT_STATUS_ACTIVE, ENROLLMENT_STATUS_COMPLETED]))
                return Result::fail('Student is already active or has completed this course');

            if ($this->course->getAvailableSeats($courseId) <= 0)
                return Result::fail('Course seats are full');

            $enrollmentId = $this->enrollment->create(
                $studentId,
                $courseId,
                $instructorId,
                date('Y-m-d'),
                ENROLLMENT_STATUS_ACTIVE
            );

            return Result::success('Enrollment created successfully', [
                'enrollment_id' => $enrollmentId
            ]);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment create');
            return Result::fail('Failed to create enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment create');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Submit enrollment request by student.
     *
     * @param int $studentId
     * @param int $courseId
     * @param int $instructorId
     * @return array
     */
    public function requestEnrollment($studentId, $courseId, $instructorId){
        $currentUser = AuthHelper::user();

            if (!$currentUser || $currentUser['role'] !== ROLE_STUDENT || $currentUser['user_id'] !== (int)$studentId)
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->user->exists(['id'=>$studentId, 'role'=>ROLE_STUDENT]))
                return Result::fail('Student not found');

            if (!$this->user->exists(['id'=>$instructorId, 'role'=>ROLE_INSTRUCTOR]))
                return Result::fail('Instructor not found');
            
            if (!$this->course->exists($courseId))
                return Result::fail('Course not found');

            if (!$this->course->isActive($courseId))
                return Result::fail('Course is inactive');

            if (!$this->course->hasInstructor($courseId, $instructorId))
                return Result::fail('Instructor is not assigned to this course');

            if ($this->enrollment->hasEnrollmentStatus($studentId, $courseId, [ENROLLMENT_STATUS_ACTIVE, ENROLLMENT_STATUS_COMPLETED]))
                return Result::fail('Student is already active or has completed this course');

            if ($this->enrollment->hasEnrollmentStatus($studentId, $courseId, [ENROLLMENT_STATUS_REQUESTED]))
                return Result::fail('Enrollment request already submitted');

            if ($this->course->getAvailableSeats($courseId) <= 0)
                return Result::fail('Course seats are full');

            $enrollmentId = $this->enrollment->create(
                $studentId,
                $courseId,
                $instructorId,
                date('Y-m-d'),
                ENROLLMENT_STATUS_REQUESTED
            );

            return Result::success('Enrollment Requested successfully', [
                'enrollment_id' => $enrollmentId
            ]);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment Request');
            return Result::fail('Failed to Request enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment Request');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Cancel enrollment.
     *
     * @param int $id
     * @return array
     */
    public function cancelEnrollment($id){
        if (!Permission::check('enrollment.cancel', $id))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->enrollment->exists(['id' => $id]))
                return Result::fail('Enrollment not found');

            $updated = $this->enrollment->update($id, ['status' => ENROLLMENT_STATUS_CANCELED]);

            if ($updated > 0)
                return Result::success('Enrollment canceled successfully');

                return Result::fail(MSG_NO_CHANGES_MADE);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment cancel');
            return Result::fail('Failed to cancel enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment cancel');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Withdraw pending enrollment request.
     *
     * @param int $id
     * @return array
     */
    public function withdrawEnrollment($id){
        $currentUser = AuthHelper::user();

        if (!$currentUser || $currentUser['role'] !== ROLE_STUDENT)
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->enrollment->exists(['id' => $id, 'student_id' => $currentUser['user_id'], 'status' => ENROLLMENT_STATUS_REQUESTED]))
                return Result::fail('Enrollment not found');

            $updated = $this->enrollment->update($id, ['status' => ENROLLMENT_STATUS_WITHDRAWN]);

            if ($updated > 0)
                return Result::success('Enrollment withdrawn successfully');

                return Result::fail(MSG_NO_CHANGES_MADE);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment withdrawn');
            return Result::fail('Failed to withdrawn enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment withdrawn');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Update enrollment details.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateEnrollment($id, $data){
        if (!Permission::check('enrollment.update', $id))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            $enrollment = $this->enrollment->getById($id);

            if (empty($enrollment['status']))
                return Result::fail('Enrollment not found');

            $enrollmentData = $enrollment['data'];

            if (isset($data['instructor_id'])) {
                if (!$this->course->hasInstructor($enrollmentData['course_id'], $data['instructor_id']))
                    return Result::fail('Instructor is not assigned to this course');
            }

            $updated = $this->enrollment->update($id, $data);

            if ($updated > 0)
                return Result::success('Enrollment updated successfully');

                return Result::fail(MSG_NO_CHANGES_MADE);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment update');
            return Result::fail('Failed to update enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment update');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Approve enrollment request.
     *
     * @param int $id
     * @return array
     */
    public function approveEnrollmentRequest($id){
        if (!Permission::check('enrollment.update', $id))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->enrollment->exists(['id' => $id, 'status' => ENROLLMENT_STATUS_REQUESTED]))
                return Result::fail('Enrollment request not found');

            $enrollment = $this->enrollment->getById($id);

            if (empty($enrollment['status']))
                return Result::fail('Enrollment request not found');

            if ($this->course->getAvailableSeats($enrollment['data']['course_id']) <= 0)
                return Result::fail('Course seats are full');

            $updated = $this->enrollment->update($id, ['status' => ENROLLMENT_STATUS_ACTIVE]);

            if ($updated > 0)
                return Result::success('Enrollment approved successfully');

                return Result::fail(MSG_NO_CHANGES_MADE);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment approve');
            return Result::fail('Failed to approve enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment approve');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }
    
    /**
     * Reject enrollment request.
     *
     * @param int $id
     * @return array
     */
    public function rejecteEnrollmentRequest($id){
        if (!Permission::check('enrollment.update', $id))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->enrollment->exists(['id' => $id, 'status' => ENROLLMENT_STATUS_REQUESTED]))
                return Result::fail('Enrollment not found');

            $updated = $this->enrollment->update($id, ['status' => ENROLLMENT_STATUS_REJECTED]);

            if ($updated > 0)
                return Result::success('Enrollment rejected successfully');

                return Result::fail(MSG_NO_CHANGES_MADE);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment rejecte');
            return Result::fail('Failed to rejecte enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment rejecte');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }
    
    /**
     * Complete enrollment.
     *
     * @param int $id
     * @return array
     */
    public function completeEnrollment($id){
        if (!Permission::check('enrollment.update', $id))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            if (!$this->enrollment->exists(['id'=>$id]))
                return Result::fail('Enrollment not found');

            $updated = $this->enrollment->update($id, ['status' => ENROLLMENT_STATUS_COMPLETED]);

            if ($updated > 0)
                return Result::success('Enrollment completed successfully');

                return Result::fail(MSG_NO_CHANGES_MADE);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment complete');
            return Result::fail('Failed to complete enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment complete');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Complete all active enrollments for a course.
     *
     * @param int $courseId
     * @return array
     */
    public function completeCourse($courseId){
        if (!Permission::check('course.update', $courseId))
            return Result::fail(MSG_UNAUTHORIZED);

        try {
            $completed = $this->enrollment->updateStatusByCourse($courseId, ENROLLMENT_STATUS_ACTIVE, ENROLLMENT_STATUS_COMPLETED);

            if ($completed <= 0)
                return Result::fail('Unable to complete course');

            return Result::success('Course completed successfully', [
                'affected_rows' => $completed
            ]);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Course complete');
            return Result::fail('Failed to complete course');
        } catch(Throwable $e){
            Logger::error($e, 'Course complete');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }
}