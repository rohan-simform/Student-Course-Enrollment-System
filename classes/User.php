<?php

require_once __DIR__.'/../helpers/QueryHelper.php';

/**
 * Handles user-related database operations.
 */
class User {
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Create a new User instance.
     *
     * @param  mysqli  $db  Database connection.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get paginated users by role.
     *
     * @param  string|null  $role
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getUsers($role = null, $page = 1, $limit = 10) {
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);
        $offset = ($page - 1) * $limit;

        if ($role === ROLE_STUDENT) {
            $countQuery = "select count(*) as total from users where role = 'student'           ";
            $query = "
                select u.id,u.email,u.role,u.status,s.name,s.phone,s.enrolled_on 
                from users u
                join students s on u.id = s.user_id
                where u.role = 'student'
                limit ? offset ?";
        } elseif ($role === ROLE_INSTRUCTOR) {
            $countQuery = "select count(*) as total from users where role = 'instructor'";
            $query = "
                select u.id,u.email,u.role,u.status,i.name,i.phone,i.salary
                from users u
                join instructors i on u.id = i.user_id
                where u.role = 'instructor'
                limit ? offset ?";
        } elseif ($role === ROLE_ADMIN) {
            $countQuery = "select count(*) as total from users where role = 'admin'";
            $query = "select u.id,u.email,u.role,u.status from users u 
                where u.role = 'admin' limit ? offset ?";
        } elseif ($role === null) {
            $countQuery = 'select count(*) as total from users';
            $query = 'select id, email, role, status from users limit ? offset ?';
        } else {
            return ['status' => false, 'message' => 'Invalid role', 'data' => []];
        }

        $countResult = $this->conn->query($countQuery);
        $total = $countResult->fetch_assoc()['total'];

        $stmt = $this->conn->prepare($query);
        if (! $stmt) {
            return ['status' => false, 'message' => 'Query prepare failed', 'data' => []];
        }

        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

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
     * Get user by ID.
     *
     * @param  int  $userId
     * @return array|null
     */
    public function getById($userId) {
        $stmt = $this->conn->prepare('select id, role, email, status from users where id = ? limit 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get user by email.
     *
     * @param  string  $email
     * @return array|null
     */
    public function getByEmail($email) {
        $stmt = $this->conn->prepare('select id, role, email, password, status from users where email = ? limit 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Create user record.
     *
     * @param  string  $role
     * @param  string  $email
     * @param  string  $password
     * @return int
     */
    public function create($role, $email, $password) {
        $stmt = $this->conn->prepare('insert into users (role, email, password) values (?, ?, ?)');
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('sss', $role, $email, $password);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }

        return $this->conn->insert_id;
    }

    /**
     * Create multiple user records and return inserted IDs.
     *
     * @param  string  $role
     * @param  array  $emails
     * @param  string  $password
     * @return array
     */
    public function bulkCreate($role, $emails, $password) {
        $query = 'insert into users (role, email, password) values ';

        $placeholders = [];
        $types = '';
        $values = [];

        foreach ($emails as $email) {
            $placeholders[] = '(?, ?, ?)';
            $types .= 'sss';

            $values[] = $role;
            $values[] = $email;
            $values[] = $password;
        }

        $query .= implode(', ', $placeholders);

        $stmt = $this->conn->prepare($query);

        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param($types, ...$values);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }

        $firstId = $this->conn->insert_id;
        $count = $stmt->affected_rows;

        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $firstId + $i;
        }

        return $ids;
    }

    /**
     * Update user data.
     *
     * @param  int  $userId
     * @param  array  $data
     * @return mixed
     */
    public function update($userId, $data) {
        $queryData = QueryHelper::buildUpdateQuery(
            'users',
            $data,
            [
                'email' => 's',
                'password' => 's',
                'status' => 's',
            ],
            ['id' => ['value' => $userId, 'type' => 'i']]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    /**
     * Soft delete user.
     *
     * @param  int  $userId
     * @return mixed
     */
    public function softDelete($userId) {
        $queryData = QueryHelper::buildUpdateQuery(
            'users',
            ['status' => 'disabled'],
            ['status' => 's'],
            ['id' => ['value' => $userId, 'type' => 'i']]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    /**
     * Check if user exists by conditions.
     *
     * @param  array  $conditions
     * @return bool
     */
    public function exists($conditions = []) {
        $allowedFields = ['id', 'role', 'email', 'status'];
        $clauses = [];
        $types = '';
        $params = [];

        foreach ($conditions as $key => $value) {
            if (in_array($key, $allowedFields, true)) {
                $clauses[] = "$key = ?";
                $types .= is_int($value) ? 'i' : 's';
                $params[] = $value;
            }
        }

        if (empty($clauses)) {
            return false;
        }

        $query = 'select 1 from users where '.implode(' and ', $clauses).' limit 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if user has given role.
     *
     * @param  int  $userId
     * @param  string  $role
     * @return bool
     */
    public function isRole($userId, $role) {
        $stmt = $this->conn->prepare('select 1 from users where id = ? and role = ? limit 1');
        $stmt->bind_param('is', $userId, $role);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if user account is active.
     *
     * @param  int  $userId
     * @return bool
     */
    public function isActive($userId) {
        $stmt = $this->conn->prepare("select 1 from users where id = ? and status = 'active' limit 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Get user's name based on their role.
     *
     * @param  int  $userId
     * @param  string  $role
     * @return string|null
     */
    private function getUserName($userId, $role) {
        if ($role === ROLE_STUDENT) {
            $stmt = $this->conn->prepare('select name from students where user_id = ? limit 1');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['name'] ?? 'Student';
        } elseif ($role === ROLE_INSTRUCTOR) {
            $stmt = $this->conn->prepare('select name from instructors where user_id = ? limit 1');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['name'] ?? 'Instructor';
        } else {
            // Admin - use email or generic name
            $stmt = $this->conn->prepare('select email from users where id = ? limit 1');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['email'] ? explode('@', $result['email'])[0] : 'Admin';
        }
    }

    /**
     * Get dashboard data based on current user's role.
     * Uses AuthHelper to get current authenticated user information.
     *
     * @return array
     */
    public function getDashboardData() {
        // Get current authenticated user
        require_once __DIR__.'/../helpers/AuthHelper.php';
        $currentUser = AuthHelper::user();

        if (! $currentUser) {
            return ['status' => false, 'message' => 'User not authenticated', 'data' => null];
        }

        $userId = $currentUser['user_id'];
        $role = $currentUser['role'];

        switch ($role) {
            case ROLE_ADMIN:
                return $this->getAdminDashboard($userId);
            case ROLE_INSTRUCTOR:
                return $this->getInstructorDashboard($userId);
            case ROLE_STUDENT:
                return $this->getStudentDashboard($userId);
            default:
                return ['status' => false, 'message' => 'Invalid role', 'data' => null];
        }
    }

    /**
     * Get admin dashboard data.
     *
     * @param  int  $userId
     * @return array
     */
    private function getAdminDashboard($userId) {
        $userName = $this->getUserName($userId, ROLE_ADMIN);
        // Request count (pending enrollments)
        $requestQuery = 'select count(*) as count from enrollments where status = ?';
        $stmt = $this->conn->prepare($requestQuery);
        $status = ENROLLMENT_STATUS_REQUESTED;
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $requestCount = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Total students
        $studentQuery = 'select count(*) as count from users where role = ?';
        $stmt = $this->conn->prepare($studentQuery);
        $role = ROLE_STUDENT;
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $totalStudents = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Active courses
        $courseQuery = 'select count(*) as count from courses where is_active = ?';
        $stmt = $this->conn->prepare($courseQuery);
        $active = COURSE_STATUS_ACTIVE;
        $stmt->bind_param('s', $active);
        $stmt->execute();
        $activeCourses = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Total instructors
        $instructorQuery = 'select count(*) as count from users where role = ?';
        $stmt = $this->conn->prepare($instructorQuery);
        $role = ROLE_INSTRUCTOR;
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $instructors = (int) $stmt->get_result()->fetch_assoc()['count'];

        return [
            'status' => true,
            'role' => ROLE_ADMIN,
            'userName' => $userName,
            'requestCount' => $requestCount,
            'totalStudents' => $totalStudents,
            'activeCourses' => $activeCourses,
            'instructors' => $instructors,
            'pendingRequests' => $requestCount,
        ];
    }

    /**
     * Get instructor dashboard data.
     *
     * @param  int  $userId
     * @return array
     */
    private function getInstructorDashboard($userId) {
        $userName = $this->getUserName($userId, ROLE_INSTRUCTOR);
        // Request count (pending enrollments for this instructor)
        $requestQuery = 'select count(*) as count from enrollments where status = ? and instructor_id = ?';
        $stmt = $this->conn->prepare($requestQuery);
        $status = ENROLLMENT_STATUS_REQUESTED;
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        $requestCount = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Active courses for this instructor
        $courseQuery = '
            select count(*) as count from courses c
            join courses_instructors ci on c.id = ci.course_id
            where ci.instructor_id = ? and c.is_active = ?
        ';
        $stmt = $this->conn->prepare($courseQuery);
        $active = COURSE_STATUS_ACTIVE;
        $stmt->bind_param('is', $userId, $active);
        $stmt->execute();
        $activeCourses = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Total distinct students with active/completed enrollments in this instructor's courses
        $studentQuery = '
            select count(distinct e.student_id) as count from enrollments e
            where e.instructor_id = ? and e.status in (?, ?)
        ';
        $stmt = $this->conn->prepare($studentQuery);
        $activeStatus = ENROLLMENT_STATUS_ACTIVE;
        $completedStatus = ENROLLMENT_STATUS_COMPLETED;
        $stmt->bind_param('iss', $userId, $activeStatus, $completedStatus);
        $stmt->execute();
        $totalStudents = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Total enrollments (active + completed)
        $enrollmentQuery = '
            select count(*) as count from enrollments e
            where e.instructor_id = ? and e.status in (?, ?)
        ';
        $stmt = $this->conn->prepare($enrollmentQuery);
        $stmt->bind_param('iss', $userId, $activeStatus, $completedStatus);
        $stmt->execute();
        $totalEnrollments = (int) $stmt->get_result()->fetch_assoc()['count'];

        return [
            'status' => true,
            'role' => ROLE_INSTRUCTOR,
            'userName' => $userName,
            'requestCount' => $requestCount,
            'activeCourses' => $activeCourses,
            'totalStudents' => $totalStudents,
            'pendingRequests' => $requestCount,
            'totalEnrollments' => $totalEnrollments,
        ];
    }

    /**
     * Get student dashboard data.
     *
     * @param  int  $userId
     * @return array
     */
    private function getStudentDashboard($userId) {
        $userName = $this->getUserName($userId, ROLE_STUDENT);
        // Request count (pending enrollments for this student)
        $requestQuery = 'select count(*) as count from enrollments where status = ? and student_id = ?';
        $stmt = $this->conn->prepare($requestQuery);
        $status = ENROLLMENT_STATUS_REQUESTED;
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        $requestCount = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Active courses
        $activeQuery = 'select count(*) as count from enrollments where status = ? and student_id = ?';
        $stmt = $this->conn->prepare($activeQuery);
        $status = ENROLLMENT_STATUS_ACTIVE;
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        $activeCourses = (int) $stmt->get_result()->fetch_assoc()['count'];

        // Completed courses
        $completedQuery = 'select count(*) as count from enrollments where status = ? and student_id = ?';
        $stmt = $this->conn->prepare($completedQuery);
        $status = ENROLLMENT_STATUS_COMPLETED;
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        $completed = (int) $stmt->get_result()->fetch_assoc()['count'];

        // In progress (same as active)
        $inProgress = $activeCourses;

        // Pending (requested)
        $pending = $requestCount;

        // Total enrollments (all statuses)
        $totalQuery = 'select count(*) as count from enrollments where student_id = ?';
        $stmt = $this->conn->prepare($totalQuery);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $totalEnrollments = (int) $stmt->get_result()->fetch_assoc()['count'];

        return [
            'status' => true,
            'role' => ROLE_STUDENT,
            'userName' => $userName,
            'requestCount' => $requestCount,
            'learningProgress' => [
                'activeCourses' => $activeCourses,
                'completed' => $completed,
                'overallProgress' => [
                    'completed' => $completed,
                    'total' => $totalEnrollments,
                ],
                'totalEnrollments' => $totalEnrollments,
            ],
            'activeCourses' => $activeCourses,
            'completed' => $completed,
            'inProgress' => $inProgress,
            'pending' => $pending,
        ];
    }
}
