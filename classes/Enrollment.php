<?php 
require_once __DIR__ . '/../helpers/QueryHelper.php';

class Enrollment{

    private $conn;

    public function __construct($db){
        $this->conn=$db;
    }

    public function getAll($page = 1, $limit = 10){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $countQuery = "select count(*) as total from enrollments";
        $result = $this->conn->query($countQuery);
        $total = $result->fetch_assoc()['total'];

        $query = "
            select
                e.id,
                e.student_id,
                s.name as student_name,
                e.course_id,
                c.name as course_name,
                e.instructor_id,
                i.name as instructor_name,
                e.enrolled_date,
                e.status
            from enrollments e
            join students s on e.student_id = s.user_id
            join courses c on e.course_id = c.id
            join instructors i on e.instructor_id = i.user_id
            limit ? offset ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

    public function getById($id){
        $query = "
            select 
                e.id,
                e.student_id,
                s.name as student_name,
                e.course_id,
                c.name as course_name,
                e.instructor_id,
                i.name as instructor_name,
                e.enrolled_date,
                e.status
            from enrollments e
            join students s on e.student_id = s.user_id
            join courses c on e.course_id = c.id
            join instructors i on e.instructor_id = i.user_id
            where e.id = ?
            limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            return ["status" => true, "data" => $result];
        }

        return ["status" => false, "message" => "Enrollment not found"];
    }

    public function getByStudent($studentId, $page = 1, $limit = 10){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        // Count Query
        $countQuery = "select count(*) as total from enrollments where student_id = ?";
        $stmt = $this->conn->prepare($countQuery);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        $total = $stmt->get_result()->fetch_assoc()['total'];

        // Data Query
        $query = "
            select 
                e.id,
                e.student_id,
                e.course_id,
                c.name as course_name,
                e.instructor_id,
                i.name as instructor_name,
                e.enrolled_date,
                e.status
            from enrollments e
            join courses c on e.course_id = c.id
            join instructors i on e.instructor_id = i.user_id
            where e.student_id = ?
            limit ? offset ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $studentId, $limit, $offset);

        if ($stmt->execute()) {
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

        return ["status" => false, "message" => "Execution failed: " . $stmt->error];
    }

    public function getByCourse($courseId, $page = 1, $limit = 10){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        // Count Query
        $countQuery = "select count(*) as total from enrollments where course_id = ?";
        $stmt = $this->conn->prepare($countQuery);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        $total = $stmt->get_result()->fetch_assoc()['total'];

        // Data Query
        $query = "
            select 
                e.id,
                e.student_id,
                s.name as student_name,
                e.course_id,
                c.name as course_name,
                e.instructor_id,
                i.name as instructor_name,
                e.enrolled_date,
                e.status
            from enrollments e
            join students s on e.student_id = s.user_id
            join instructors i on e.instructor_id = i.user_id
            join courses c on e.course_id = c.id
            where e.course_id = ?
            limit ? offset ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $courseId, $limit, $offset);

        if ($stmt->execute()) {
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

        return ["status" => false, "message" => "Execution failed: " . $stmt->error];
    }

    public function getByInstructor($instructorId, $page = 1, $limit = 10){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $countQuery = "select count(*) as total from enrollments where instructor_id = ?";
        $stmt = $this->conn->prepare($countQuery);
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();

        $total = $stmt->get_result()->fetch_assoc()['total'];

        $query = "
            select
                e.id,
                e.student_id,
                s.name as student_name,
                e.course_id,
                c.name as course_name,
                e.instructor_id,
                i.name as instructor_name,
                e.enrolled_date,
                e.status
            from enrollments e
            join students s on e.student_id = s.user_id
            join courses c on e.course_id = c.id
            join instructors i on e.instructor_id = i.user_id
            where e.instructor_id = ?
            limit ? offset ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $instructorId, $limit, $offset);

        if ($stmt->execute()) {
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

        return ["status" => false, "message" => "Execution failed: " . $stmt->error];
    }

    public function countActiveByCourse($courseId){
        $query = "
            select count(*) as total
            from enrollments
            where course_id = ? and status = 'active'";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        return (int)$result['total'];
    }

    public function getByStudentAndCourse($studentId, $courseId){
        $query = "
            select *
            from enrollments
            where student_id = ? and course_id = ?
            limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $studentId, $courseId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            return ["status" => true, "data" => $result];
        }

        return ["status" => false, "message" => "Enrollment not found"];
    }

    public function hasEnrollmentStatus($studentId, $courseId, $statuses){
        $statuses = (array)$statuses;

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $types = "ii" . str_repeat("s", count($statuses));

        $query = "
            select 1
            from enrollments
            where student_id = ?
            and course_id = ?
            and status in ($placeholders)
            limit 1";

        $stmt = $this->conn->prepare($query);

        $params = [$studentId, $courseId, ...$statuses];

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function create($studentId, $courseId, $instructorId, $enrolledDate){
        $query = "insert into enrollments (student_id, course_id, instructor_id, enrolled_date) values (?,?,?,?)";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) throw new Exception("Failed to prepare query");

        $stmt->bind_param("iiis", $studentId, $courseId, $instructorId, $enrolledDate);

        if (!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);

        return $this->conn->insert_id;
    }

    public function update($id, $data){
        $queryData = QueryHelper::buildUpdateQuery(
            "enrollments",
            $data,
            [
                "student_id" => "i",
                "course_id" => "i",
                "instructor_id" => "i",
                "enrolled_date" =>  "s",
                "status" =>  "s"
            ],
            ["id" => ["value" => $id,"type" => "i"]]
        );
        
        return QueryHelper::execute($this->conn, $queryData);
    }

    public function updateStatusByCourse($courseId, $fromStatus, $tostatus){
        $queryData = QueryHelper::buildUpdateQuery(
            "enrollments",
            ["status" => $tostatus],
            ["status" => "s"],
            [
                "course_id" => ["value" => $courseId, "type" => "i"],
                "status"    => ["value" => $fromStatus,   "type" => "s"]
            ]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    public function hasInstructor($enrollmentId, $instructorId){
        $query = "select 1 from enrollments where id = ? and instructor_id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $enrollmentId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function hasStudent($enrollmentId, $studentId){
        $query = "select 1 from enrollments where id = ? and student_id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $enrollmentId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function exists($id){
        $query = "select 1 from enrollments where id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function studentInCourse($courseId, $studentId){
        $query = "select 1 from enrollments where course_id = ? and student_id = ? and status = 'active' limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $courseId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function studentUnderInstructor($instructorId, $studentId){
        $query = "select 1 from enrollments where instructor_id = ? and student_id = ? and status = 'active' limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }
}
