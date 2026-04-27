<?php
require_once __DIR__ . '/../helpers/QueryHelper.php';

class User{
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function getUsers($role = null, $page = 1, $limit = 10){
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        if ($role === 'student') {
            $countQuery = "select count(*) as total from users where role = 'student'           ";
            $query = "
                select u.id,u.email,u.role,u.status,s.name,s.phone,s.enrolled_on 
                from users u
                join students s on u.id = s.user_id
                where u.role = 'student'
                limit ? offset ?";
        } elseif ($role === 'instructor') {
            $countQuery = "select count(*) as total from users where role = 'instructor'";
            $query = "
                select u.id,u.email,u.role,u.status,i.name,i.phone,i.salary
                from users u
                join instructors i on u.id = i.user_id
                where u.role = 'instructor'
                limit ? offset ?";
        } elseif ($role === 'admin') {
            $countQuery = "select count(*) as total from users where role = 'admin'";
            $query = "select u.id,u.email,u.role,u.status from users u 
                where u.role = 'admin' limit ? offset ?";
        } elseif ($role === null) {
            $countQuery = "select count(*) as total from users";
            $query = "select id, email, role, status from users limit ? offset ?";
        } else {
            return ["status" => false,"message" => "Invalid role","data" => []];
        }

        $countResult = $this->conn->query($countQuery);
        $total = $countResult->fetch_assoc()['total'];

        $stmt = $this->conn->prepare($query);
        if (!$stmt) return ["status"=>false,"message"=>"Query prepare failed","data"=>[]];
        
        $stmt->bind_param("ii", $limit, $offset);
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

    public function getById($userId){
        $stmt = $this->conn->prepare("select id, role, email, status from users where id = ? limit 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByEmail($email){
        $stmt = $this->conn->prepare("select id, role, email, password, status from users where email = ? limit 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function create($role, $email, $password){
        $stmt = $this->conn->prepare("insert into users (role, email, password) values (?, ?, ?)");
        if (!$stmt) throw new Exception("Failed to prepare query");

        $stmt->bind_param("sss", $role, $email, $password);

        if (!$stmt->execute())
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);

        return $this->conn->insert_id;
    }

    public function update($userId, $data){
        $queryData = QueryHelper::buildUpdateQuery(
            "users",
            $data,
            [
                "email"    => "s",
                "password" => "s",
                "status"   => "s"
            ],
            ["id" => ["value" => $userId, "type" => "i"]]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    public function softDelete($userId){
        $queryData = QueryHelper::buildUpdateQuery(
            "users",
            ["status" => "disabled"],
            ["status" => "s"],
            ["id" => ["value" => $userId, "type" => "i"]]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    public function exists($userId){
        $stmt = $this->conn->prepare("select 1 from users where id = ? limit 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function isRole($userId, $role){
        $stmt = $this->conn->prepare("select 1 from users where id = ? and role = ? limit 1");
        $stmt->bind_param("is", $userId, $role);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function isActive($userId){
        $stmt = $this->conn->prepare("select 1 from users where id = ? and status = 'active' limit 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }
}