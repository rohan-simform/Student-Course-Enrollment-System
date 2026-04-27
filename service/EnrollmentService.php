<?php

require_once __DIR__ . '/../classes/Enrollment.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../helpers/Permission.php';
require_once __DIR__ . '/../helpers/Result.php';
require_once __DIR__ . '/../helpers/Logger.php';

class EnrollmentService
{
    private $conn;
    private $enrollment;
    private $course;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->enrollment = new Enrollment($db);
        $this->course = new Course($db);
        Permission::init($db);
    }

    public function enrollStudent($studentId, $courseId, $instructorId){
        if (!Permission::check('course.enroll', $courseId))
            return Result::fail('Unauthorized');

        try {
            $course = $this->course->getCourseById($courseId);

            if (empty($course['status']))
                return Result::fail('Course not found');

            $courseData = $course['data'];

            if ($courseData['is_active'] !== 'active')
                return Result::fail('Course is inactive');

            if (!$this->course->hasInstructor($courseId, $instructorId))
                return Result::fail('Instructor is not assigned to this course');

            if ($this->enrollment->hasEnrollmentStatus($studentId, $courseId, ['active', 'completed']))
                return Result::fail('Student is already active or has completed this course');

            $activeCount = $this->enrollment->countActiveByCourse($courseId);

            if ($activeCount >= (int)$courseData['max_seats'])
                return Result::fail('Course seats are full');

            $enrollmentId = $this->enrollment->create(
                $studentId,
                $courseId,
                $instructorId,
                date('Y-m-d')
            );

            return Result::success('Enrollment created successfully', [
                'enrollment_id' => $enrollmentId
            ]);
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment create');
            return Result::fail('Failed to create enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment create');
            return Result::fail('Unexpected error');
        }
    }

    public function cancelEnrollment($id){
        if (!Permission::check('enrollment.cancel', $id))
            return Result::fail('Unauthorized');

        try {
            if (!$this->enrollment->exists($id))
                return Result::fail('Enrollment not found');

            $updated = $this->enrollment->update($id, ['status' => 'canceled']);

            if ($updated > 0)
                return Result::success('Enrollment canceled successfully');

            return Result::fail('No changes made');
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment cancel');
            return Result::fail('Failed to cancel enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment cancel');
            return Result::fail('Unexpected error');
        }
    }

    public function updateEnrollment($id, $data){
        if (!Permission::check('enrollment.update', $id))
            return Result::fail('Unauthorized');

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

            return Result::fail('No changes made');
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment update');
            return Result::fail('Failed to update enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment update');
            return Result::fail('Unexpected error');
        }
    }

    public function completeEnrollment($id){
        if (!Permission::check('enrollment.update', $id))
            return Result::fail('Unauthorized');

        try {
            if (!$this->enrollment->exists($id))
                return Result::fail('Enrollment not found');

            $updated = $this->enrollment->update($id, ['status' => 'completed']);

            if ($updated > 0)
                return Result::success('Enrollment completed successfully');

            return Result::fail('No changes made');
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Enrollment complete');
            return Result::fail('Failed to complete enrollment');
        } catch(Throwable $e){
            Logger::error($e, 'Enrollment complete');
            return Result::fail('Unexpected error');
        }
    }

    public function completeCourse($courseId){
        if (!Permission::check('course.update', $courseId))
            return Result::fail('Unauthorized');

        try {
            $completed = $this->enrollment->updateStatusByCourse($courseId,'active','completed');

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
            return Result::fail('Unexpected error');
        }
    }
}
