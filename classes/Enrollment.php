<?php 
require_once __DIR__ . '/../helpers/QueryHelper.php';

/**
 * Handles enrollment-related database operations.
 */
class Enrollment{

    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Create a new Enrollment instance.
     *
     * @param mysqli $db Database connection.
     */
    public function __construct($db){
        $this->conn=$db;
    }

    /**
     * Get paginated enrollments with optional filters.
     *
     * @param string|null $filterBy
     * @param int|null $filterId
     * @param int $page
     * @param int $limit
     * @param string|string[]|null $status
     * @param boolean $excludeStatus
     * @return array
     */
    public function getEnrollments($filterBy = null, $filterId = null, $page = 1, $limit = 10, $status = null, $excludeStatus = false){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $allowedFilters = ['student_id', 'course_id', 'instructor_id'];

        $useFilter = isset($filterId) && isset($filterBy) && in_array($filterBy, $allowedFilters, true);

        // 👇 UPDATED STATUS HANDLING
        $useStatus = !empty($status);
        $isArrayStatus = is_array($status);

        $countQuery = "select count(*) as total from enrollments";
        $conditions = [];
        $countParams = [];
        $countTypes = '';

        if ($useFilter) {
            $conditions[] = "$filterBy = ?";
            $countParams[] = $filterId;
            $countTypes .= 'i';
        }

        if ($useStatus) {
            if ($isArrayStatus) {
                $placeholders = implode(',', array_fill(0, count($status), '?'));
                $operator = $excludeStatus ? 'NOT IN' : 'IN';

                $conditions[] = "status $operator ($placeholders)";
                foreach ($status as $s) {
                    $countParams[] = $s;
                    $countTypes .= 's';
                }
            } else {
                $operator = $excludeStatus ? '<>' : '=';
                $conditions[] = "status $operator ?";
                $countParams[] = $status;
                $countTypes .= 's';
            }
        }

        if (!empty($conditions)) {
            $countQuery .= " WHERE " . implode(" and ", $conditions);
        }

        $stmt = $this->conn->prepare($countQuery);
        if (!empty($countParams)) {
            $stmt->bind_param($countTypes, ...$countParams);
        }

        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        /*
        |-------------------------
        | MAIN QUERY
        |-------------------------
        */
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
        ";

        $queryConditions = [];
        $queryParams = [];
        $queryTypes = '';

        if ($useFilter) {
            $queryConditions[] = "e.$filterBy = ?";
            $queryParams[] = $filterId;
            $queryTypes .= 'i';
        }

        if ($useStatus) {
            if ($isArrayStatus) {
                $placeholders = implode(',', array_fill(0, count($status), '?'));
                $operator = $excludeStatus ? 'NOT IN' : 'IN';

                $queryConditions[] = "e.status $operator ($placeholders)";
                foreach ($status as $s) {
                    $queryParams[] = $s;
                    $queryTypes .= 's';
                }
            } else {
                $operator = $excludeStatus ? '<>' : '=';
                $queryConditions[] = "e.status $operator ?";
                $queryParams[] = $status;
                $queryTypes .= 's';
            }
        }

        if (!empty($queryConditions)) {
            $query .= " WHERE " . implode(" and ", $queryConditions);
        }

        $query .= " order by e.enrolled_date desc, e.id desc";
        $query .= " limit ? offset ?";

        $stmt = $this->conn->prepare($query);

        $queryParams[] = $limit;
        $queryParams[] = $offset;
        $queryTypes .= 'ii';

        $stmt->bind_param($queryTypes, ...$queryParams);

        if ($stmt->execute()) {
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return [
                "status" => true,
                "data" => $data,
                "pagination" => [
                    "page" => $page,
                    "limit" => $limit,
                    "total_rows" => (int)$total,
                    "total_pages" => $limit > 0 ? ceil($total / $limit) : 0
                ]
            ];
        }

        return [
            "status" => false,
            "message" => "Execution failed: " . $stmt->error
        ];
    }

    /**
     * Get enrollment by student and course.
     *
     * @param int $studentId
     * @param int $courseId
     * @return array
     */
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

    /**
     * Get enrollment by ID.
     *
     * @param int $id
     * @return array
     */
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

    /**
     * Count active enrollments by course.
     *
     * @param int $courseId
     * @return int
     */
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

    /**
     * Check if enrollment exists with given statuses.
     *
     * @param int $studentId
     * @param int $courseId
     * @param array|string $statuses
     * @return bool
     */
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

    /**
     * Check if enrollment exists by conditions.
     *
     * @param array $condition
     * @return bool
     */
    public function exists($condition = []){
        $allowedFields = ['id', 'student_id', 'course_id', 'instructor_id', 'status'];
        $clauses = [];
        $types   = '';
        $params  = [];

        foreach($condition as $key => $value){
            if(in_array($key, $allowedFields, true)){
                $clauses[] = "{$key} = ?";
                $types .= is_int($value) ? 'i' : 's';
                $params[] = $value;
            }
        }
        
        if(empty($clauses)) return false;

        $query = "select 1 from enrollments where " . implode(" and ", $clauses) ." limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if instructor owns enrollment.
     *
     * @param int $enrollmentId
     * @param int $instructorId
     * @return bool
     */
    public function hasInstructor($enrollmentId, $instructorId){
        $query = "select 1 from enrollments where id = ? and instructor_id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $enrollmentId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if student owns enrollment.
     *
     * @param int $enrollmentId
     * @param int $studentId
     * @return bool
     */
    public function hasStudent($enrollmentId, $studentId){
        $query = "select 1 from enrollments where id = ? and student_id = ? limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $enrollmentId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if student already belongs to course.
     *
     * @param int $courseId
     * @param int $studentId
     * @return bool
     */
    public function studentInCourse($courseId, $studentId){
        $query = "
            select 1
            from enrollments
            where course_id = ?
            and student_id = ?
            and status in ('active', 'completed', 'requested', 'withdrawn', 'rejected', 'course_inactive')
            limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $courseId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if student is under instructor.
     *
     * @param int $instructorId
     * @param int $studentId
     * @return bool
     */
    public function studentUnderInstructor($instructorId, $studentId){
        $query = "
            select 1
            from enrollments
            where instructor_id = ?
            and student_id = ?
            limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Create enrollment.
     *
     * @param int $studentId
     * @param int $courseId
     * @param int $instructorId
     * @param string $enrolledDate
     * @param string $status
     * @return int
     */
    public function create($studentId, $courseId, $instructorId, $enrolledDate, $status){
        $query = "insert into enrollments (student_id, course_id, instructor_id, enrolled_date, status) values (?,?,?,?,?)";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->conn->error);

        $stmt->bind_param("iiiss", $studentId, $courseId, $instructorId, $enrolledDate, $status);

        if (!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);

        return $this->conn->insert_id;
    }

    /**
     * Update enrollment data.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
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

    /**
     * Update enrollment status by course.
     *
     * @param int $courseId
     * @param string $fromStatus
     * @param string $tostatus
     * @return mixed
     */
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
}