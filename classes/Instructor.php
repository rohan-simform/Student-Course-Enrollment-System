<?php
require_once __DIR__ . '/../helpers/QueryHelper.php';

class Instructor{
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function getAll($page = 1, $limit = 10){
        $page   = max(1, (int)$page);
        $limit  = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $countResult = $this->conn->query("select count(*) as total from users where role = 'instructor'");
        $total = $countResult->fetch_assoc()['total'];

        $stmt = $this->conn->prepare("
            select u.id, u.email, u.role, u.status, i.name, i.phone, i.salary
            from users u
            join instructors i on u.id = i.user_id
            where u.role = 'instructor'
            limit ? offset ?");

        if (!$stmt) return ["status" => false, "message" => "Query prepare failed", "data" => []];

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            "status" => true,
            "data"   => $data,
            "pagination" => [
                "page"        => $page,
                "limit"       => $limit,
                "total_rows"  => (int)$total,
                "total_pages" => ceil($total / $limit)
            ]
        ];
    }

    public function getInstructorsByCourse($courseId){
        $query = "select
                    i.user_id as instructor_id,
                    i.name as instructor_name
                from courses_instructors ci
                join instructors i on ci.instructor_id = i.user_id
                where ci.course_id = ?
                order by i.name asc";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return ["status" => false, "message" => "Failed to prepare query", "data" => []];
        }

        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        return [
            "status" => true,
            "data" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        ];
    }

    public function getByUserId($userId){
        $stmt = $this->conn->prepare("select name, phone, salary from instructors where user_id = ? limit 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getOptions(){
        $result = $this->conn->query("select user_id as id, name from instructors");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($userId, $name, $salary, $phone){
        $stmt = $this->conn->prepare("insert into instructors (user_id, name, salary, phone) values (?, ?, ?, ?)");
        if (!$stmt) throw new Exception("Failed to prepare query");

        $stmt->bind_param("isis", $userId, $name, $salary, $phone);

        if (!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
    }

    public function update($userId, $data){
        $queryData = QueryHelper::buildUpdateQuery(
            "instructors",
            $data,
            [
                "name"      => "s",
                "phone"     => "s",
                "salary"    => "i",
                "is_active" => "s"
            ],
            ["user_id" => ["value" => $userId, "type" => "i"]]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    public function softDelete($userId){
        $queryData = QueryHelper::buildUpdateQuery(
            "instructors",
            ["is_active" => "inactive"],
            ["is_active" => "s"],
            ["user_id" => ["value" => $userId, "type" => "i"]]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }
}