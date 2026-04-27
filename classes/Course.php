<?php
require_once __DIR__ . '/../helpers/QueryHelper.php';
class Course{
    private $conn;

    public function __construct($db){
        $this->conn = $db; 
    }

    public function getCourses($userId = null, $role = null, $page = 1, $limit = 10){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        // default / admin = all courses
        $countQuery = "select count(*) as total from courses";
        $query = "select 
                    c.id as course_id,
                    c.name as course_name,
                    c.duration_weeks,
                    c.max_seats,
                    count(e.id) as filled_seats,
                    c.max_seats - count(e.id) as available_seats,
                    c.is_active as course_status 
                from courses c
                left join enrollments e on e.course_id = c.id and e.status in ('active', 'course_inactive')
                group by c.id, c.name, c.duration_weeks, c.max_seats, c.is_active
                limit ? offset ?";
        $countParams = [];
        $dataParams = [$limit, $offset];
        $countTypes = "";
        $dataTypes = "ii";

        if ($role === 'student') {
            $countQuery = " select count(*) as total from enrollments where student_id = ?";
            $query = "
                select
                    c.id as course_id,
                    c.name as course_name,
                    c.duration_weeks,
                    i.name as instructor_name,
                    e.status
                from courses c
                join enrollments e on e.course_id = c.id and c.is_active = 'active'
                join instructors i on e.instructor_id = i.user_id
                where e.student_id = ?
                limit ? offset ?";

            $countParams = [$userId];
            $dataParams = [$userId, $limit, $offset];
            $countTypes = "i";
            $dataTypes = "iii";

        } elseif ($role === 'instructor') {
            $countQuery = "select count(*) as total from courses_instructors where instructor_id = ?";
            $query = "
                select
                    c.id as course_id,
                    c.name as course_name,
                    c.duration_weeks,
                    c.max_seats,
                    count(e.id) as filled_seats,
                    c.max_seats - count(e.id) as available_seats,
                    c.is_active as course_status
                from courses c
                join courses_instructors ci
                    on ci.course_id = c.id
                left join enrollments e
                    on e.course_id = c.id
                    and e.status = 'active'
                where ci.instructor_id = ?
                group by c.id, c.name, c.duration_weeks, c.max_seats, c.is_active
                limit ? offset ?";

            $countParams = [$userId];
            $dataParams = [$userId, $limit, $offset];
            $countTypes = "i";
            $dataTypes = "iii";

        } elseif ($role === 'admin' || $role === null) {
            //Default Query
        } else {
            return ["status" => false, "message" => "invalid role", "data" => []];
        }

        // count query
        $stmt = $this->conn->prepare($countQuery);

        if (!empty($countParams)) {
            $stmt->bind_param($countTypes, ...$countParams);
        }

        $stmt->execute();
        $countResult = $stmt->get_result();
        $total = $countResult->fetch_assoc()['total'];

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($dataTypes, ...$dataParams);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        return [
            "status" => true,
            "data" => $data,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total_rows" => (int)$total,
                "total_pages" => ceil($total / $limit)
            ]
        ];
    }

    public function getCourseById($courseId){
        $courseId = (int)$courseId;

        if ($courseId <= 0) return ["status" => false,"message" => "Invalid course ID","data" => null];

        $query = "select id,name,duration_weeks,max_seats,is_active from courses where id = ? limit 1 ";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) return ["status" => false,"message" => "Failed to prepare query","data" => null];

        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (!$data) return ["status" => false,"message" => "Course not found","data" => null];

        return ["status" => true,"data" => $data];
    }

    public function getAssignedCourses(){
        $query = "select 
                    c.id as course_id,
                    c.name as course_name,
                    i.user_id as instructor_id,
                    i.name as instructor_name
                from courses_instructors ci
                join courses c ON ci.course_id = c.id
                join instructors i on ci.instructor_id = i.user_id
                order by c.id desc";

        $result = $this->conn->query($query);

        return [
            "status" => true,
            "data" => $result->fetch_all(MYSQLI_ASSOC)
        ];
    }

    public function getOptions(){
        $query = "select id, name from courses";
        $result = $this->conn->query($query);

        return ["status" => true,"data" => $result->fetch_all(MYSQLI_ASSOC)];
    }

    public function create($name, $durationWeeks, $maxSeats){
        $query = "insert into courses (name, duration_weeks, max_seats) values (?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) throw new Exception("Failed to prepare query");

        $stmt->bind_param("sii", $name, $durationWeeks, $maxSeats);

        if (!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error,$this->conn->errno);

        return $this->conn->insert_id;
    }

    public function assignCourse($courseId, $instructorId){
        $query = "insert into courses_instructors (course_id, instructor_id) values (? , ?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) throw new Exception("Failed to prepare query");
        
        $stmt->bind_param("ii", $courseId, $instructorId);

        if(!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error,$this->conn->errno);
    }

    public function updateCourse($courseId, $data){
        $queryData = QueryHelper::buildUpdateQuery(
            "courses",
            $data,
            [
                "name" => "s",
                "duration_weeks" => "i",
                "max_seats" => "i",
                "is_active" =>  "s"
            ],
            ["id" => ["value" => $courseId,"type" => "i"]]
        );
        
        return QueryHelper::execute($this->conn, $queryData);

        if ($affected > 0) {
            return ["status" => true, "message" => "Course Updated successfully"];
        } else {
            return ["status" => false, "message" => "No changes made"];
        }
    }

    public function updateAssignment($courseId, $oldInstructorId, $newInstructorId){
        $queryData = QueryHelper::buildUpdateQuery(
            "courses_instructors",
            ["instructor_id" => $newInstructorId],
            ["instructor_id" => "i"],
            [
                "course_id"     => ["value" => $courseId,       "type" => "i"],
                "instructor_id" => ["value" => $oldInstructorId, "type" => "i"]
            ]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    public function deleteAssignment($courseId, $instructorId){
        $query = "delete from courses_instructors where course_id = ? and instructor_id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) throw new Exception("Failed to prepare query");

        $stmt->bind_param("ii", $courseId, $instructorId);

        if(!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error,$this->conn->errno);

        return $stmt->affected_rows; 
    }

    public function deleteCourse($courseId){

        $queryData = QueryHelper::buildUpdateQuery(
            "courses",
            ["is_active" => "inactive"],
            ["is_active" => "s"],
            ["id" => ["value" => $courseId, "type" => "i"]]
        );

        return QueryHelper::execute($this->conn, $queryData);

    }

    public function courseExists($courseId){
        $query = "select 1 from courses where id = ? limit 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function instructorExists($instructorId){
        $query = "select 1 from instructors where user_id = ? limit 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function isAlreadyAssigned($courseId, $instructorId){
        $query = "select 1 from courses_instructors where course_id = ? and instructor_id = ? limit 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function hasInstructor($courseId, $instructorId){
        $query = "select 1 from courses_instructors where course_id = ? and instructor_id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function exists($courseId){
        $query = "select 1 from courses where id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function isActive($courseId){
        $query = "select 1 from courses where id = ? and is_active = 'active' limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }
}
