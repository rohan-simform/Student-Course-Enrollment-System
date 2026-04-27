<?php
require_once __DIR__ . "/../classes/Course.php";
require_once __DIR__ . "/../classes/Enrollment.php";
require_once __DIR__ . "/../helpers/Permission.php";
require_once __DIR__ . "/../helpers/Result.php";
require_once __DIR__ . "/../helpers/Logger.php";
class CourseService{
    private $conn;
    private $course;
    private $enrollment;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->course = new Course($db);
        $this->enrollment = new Enrollment($db);
        Permission::init($this->conn);
    }

    public function addCourse($name, $durationWeeks, $maxSeats){
        if(!Permission::check("course.create")){
            return Result::fail('Unauthorized');
        }
        try{
            $courseId = $this->course->create($name, $durationWeeks, $maxSeats);

            return Result::success("Course created successfully",['course_id' => $courseId]);

        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Course create');
            return Result::fail("Failed to create course");
        } catch(Throwable $e){
            Logger::error($e, 'Course create');
            return Result::fail("Unexpected error");
        }
    }

    public function updateCourse($courseId, $data){
        try{
            if(!Permission::check("course.update", $courseId))
                return Result::fail('Unauthorized');

            if (!$this->course->courseExists($courseId)) 
                return Result::fail("Invalid course");

            $affectedRowCount = $this->course->updateCourse($courseId, $data);
            
            if (isset($data['is_active'])){

                if ($data['is_active'] === 'inactive') {
                    $fromStatus = 'active';
                    $toStatus   = 'course_inactive';
                } else {
                    $fromStatus = 'course_inactive';
                    $toStatus   = 'active';
                } 

                $affectedRowCount += $this->enrollment->updateStatusByCourse($courseId,$fromStatus,$toStatus);
            }

            if($affectedRowCount > 0)
                return Result::success("Course updated successfully");
            else
                return Result::fail("Course not found");
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Course update');
            return Result::fail("Failed to Update Course");
        } catch(Throwable $e){
            Logger::error($e, 'Course update');
            return Result::fail("Unexpected error");
        }
    }

    public function deleteCourse($courseId){
        try{
            if(!Permission::check("course.update", $courseId))
                return Result::fail('Unauthorized');

            if (!$this->course->courseExists($courseId)) 
                return Result::fail("Invalid course");

            $affectedRowCount = $this->course->deleteCourse($courseId);

            $affectedRowCount += $this->enrollment->updateStatusByCourse($courseId,'active','course_inactive');

            if($affectedRowCount > 0)
                return Result::success("Course deactivated successfully");
            else
                return Result::fail("Course not found");
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Course update');
            return Result::fail("Failed to Update Course");
        } catch(Throwable $e){
            Logger::error($e, 'Course update');
            return Result::fail("Unexpected error");
        }
    }

    public function assignCourse($courseId, $instructorId){
        if(!Permission::check("course.assign"))
            return Result::fail('Unauthorized');
        
        try{
            if ($this->course->isAlreadyAssigned($courseId, $instructorId)){
                return Result::fail("Alredy Assigned");
            }
            $this->course->assignCourse($courseId, $instructorId);

            return Result::success("Course Assigned successfully");
            
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Course assign');
            return Result::fail("Failed to assign course");
            exit;
        } catch(Throwable $e){
            Logger::error($e, 'Course assign');
            return Result::fail("Unexpected error");
        }
    }

    public function updateAssignment($courseId, $oldInstructorId, $newInstructorId){
        try{
            if(!Permission::check("course.assign"))
                return Result::fail('Unauthorized');

            if (!$this->course->courseExists($courseId)) 
                return Result::fail("Invalid course");

            if (!$this->course->instructorExists($newInstructorId)) 
                return Result::fail("Invalid instructor");

            if ($this->course->isAlreadyAssigned($courseId, $newInstructorId))
                return Result::fail("Already assigned to selected instructor");

            $affectedRowCount = $this->course->updateAssignment($courseId, $oldInstructorId, $newInstructorId);

            if($affectedRowCount > 0)
                return Result::success("Course Assignment updated successfully");
            else
                return Result::fail("Assignment not found");
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Course Assignment update');
            return Result::fail("Failed to Update Course Assignment");
        } catch(Throwable $e){
            Logger::error($e, 'Course Assignment update');
            return Result::fail("Unexpected error");
        }
    }

    public function removeAssignment($courseId, $instructorId){
        if(!Permission::check("course.assign")){
            return Result::fail('Unauthorized');
        }
        try{
            if (!$this->course->isAlreadyAssigned($courseId, $instructorId)) 
                return Result::fail("Assignment does not Exists");

            $affectedRowCount = $this->course->deleteAssignment($courseId, $instructorId);

            if ($affectedRowCount > 0)
                return Result::success("Assignment removed successfully");
            else
                return Result::fail("Assignment not found");
            
        } catch(mysqli_sql_exception $e){
            Logger::error($e, 'Remove course assign');
            return Result::fail("Failed to Remove Course Assignment");
        } catch(Throwable $e){
            Logger::error($e, 'Course assign');
            return Result::fail("Unexpected error");
        }
    }
}
