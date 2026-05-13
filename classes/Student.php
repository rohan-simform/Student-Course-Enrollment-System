<?php

require_once __DIR__.'/../helpers/QueryHelper.php';

/**
 * Handles student-related database operations.
 */
class Student {
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Create a new Student instance.
     *
     * @param  mysqli  $db  Database connection.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get paginated student list.
     *
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getAll($page = 1, $limit = 10) {
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);
        $offset = ($page - 1) * $limit;

        $countResult = $this->conn->query("select count(*) as total from users where role = 'student'");
        $total = $countResult->fetch_assoc()['total'];

        $stmt = $this->conn->prepare("
            select u.id, u.email, u.role, u.status, s.name, s.phone, s.enrolled_on
            from users u
            join students s on u.id = s.user_id
            where u.role = 'student'
            limit ? offset ?");

        if (! $stmt) {
            return ['status' => false, 'message' => 'Query prepare failed', 'data' => []];
        }

        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_rows' => (int) $total,
                'total_pages' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * Get student by user ID.
     *
     * @param  int  $userId
     * @return array|null
     */
    public function getByUserId($userId) {
        $stmt = $this->conn->prepare('select name, phone, enrolled_on from students where user_id = ? limit 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Create student record.
     *
     * @param  int  $userId
     * @param  string  $name
     * @param  string  $phone
     * @param  string  $enrolledOn
     * @return void
     */
    public function create($userId, $name, $phone, $enrolledOn) {
        $stmt = $this->conn->prepare('insert into students (user_id, name, phone, enrolled_on) values (?, ?, ?, ?)');
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('isss', $userId, $name, $phone, $enrolledOn);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }

    /**
     * Create multiple student records.
     *
     * @param  array  $userIds
     * @param  array  $names
     * @param  array  $phones
     * @param  string  $enrolledOn
     * @return void
     */
    public function bulkCreate($userIds, $names, $phones, $enrolledOn) {
        $query = 'insert into students (user_id, name, phone, enrolled_on) values ';

        $placeholders = [];
        $types = '';
        $values = [];

        $count = count($userIds);

        for ($i = 0; $i < $count; $i++) {
            $placeholders[] = '(?, ?, ?, ?)';
            $types .= 'isss';

            $values[] = $userIds[$i];
            $values[] = $names[$i];
            $values[] = $phones[$i];
            $values[] = $enrolledOn;
        }

        $query .= implode(', ', $placeholders);

        $stmt = $this->conn->prepare($query);

        if (! $stmt) {
            throw new Exception('Failed to prepare query');
        }

        $stmt->bind_param($types, ...$values);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }

    /**
     * Update student data.
     *
     * @param  int  $userId
     * @param  array  $data
     * @return mixed
     */
    public function update($userId, $data) {
        $queryData = QueryHelper::buildUpdateQuery(
            'students',
            $data,
            [
                'name' => 's',
                'phone' => 's',
                'is_active' => 's',
            ],
            ['user_id' => ['value' => $userId, 'type' => 'i']]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    /**
     * Soft delete student.
     *
     * @param  int  $userId
     * @return mixed
     */
    public function softDelete($userId) {
        $queryData = QueryHelper::buildUpdateQuery(
            'students',
            ['is_active' => 'inactive'],
            ['is_active' => 's'],
            ['user_id' => ['value' => $userId, 'type' => 'i']]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    /**
     * Check if student belongs to instructor.
     *
     * @param  int  $studentId
     * @param  int  $instructorId
     * @return bool
     */
    public function studentUnderInstructor($studentId, $instructorId) {
        $stmt = $this->conn->prepare('
            select 1 from enrollments
            where student_id = ? and instructor_id = ?
            limit 1');

        $stmt->bind_param('ii', $studentId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }
}
