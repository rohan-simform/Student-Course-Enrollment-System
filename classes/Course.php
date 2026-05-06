<?php

require_once __DIR__.'/../helpers/QueryHelper.php';

/**
 * Handles course-related database operations.
 */
class Course {
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Create a new Course instance.
     *
     * @param  mysqli  $db  Database connection.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get paginated courses based on user role.
     *
     * @param  int|null  $userId  User ID.
     * @param  string|null  $role  User role.
     * @param  int  $page  Page number.
     * @param  int  $limit  Records per page.
     * @return array
     */
    public function getCourses($userId = null, $role = null, $page = 1, $limit = 10) {
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);
        $offset = ($page - 1) * $limit;

        // default / admin = all courses
        $countQuery = 'select count(*) as total from courses';
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
        $countTypes = '';
        $dataTypes = 'ii';

        if ($role === ROLE_STUDENT) {
            $countQuery = ' select count(*) as total from enrollments where student_id = ?';
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
            $countTypes = 'i';
            $dataTypes = 'iii';

        } elseif ($role === ROLE_INSTRUCTOR) {
            $countQuery = 'select count(*) as total from courses_instructors where instructor_id = ?';
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
            $countTypes = 'i';
            $dataTypes = 'iii';

        } elseif ($role === ROLE_ADMIN || $role === null) {
            // Default Query
        } else {
            return ['status' => false, 'message' => 'invalid role', 'data' => []];
        }

        // count query
        $stmt = $this->conn->prepare($countQuery);

        if (! empty($countParams)) {
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
            'status' => true,
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
     * Get course by ID.
     *
     * @param  int  $courseId
     * @return array
     */
    public function getCourseById($courseId) {
        $courseId = (int) $courseId;

        if ($courseId <= 0) {
            return ['status' => false, 'message' => 'Invalid course ID', 'data' => null];
        }

        $query = 'select id,name,duration_weeks,max_seats,is_active from courses where id = ? limit 1 ';

        $stmt = $this->conn->prepare($query);

        if (! $stmt) {
            return ['status' => false, 'message' => 'Failed to prepare query', 'data' => null];
        }

        $stmt->bind_param('i', $courseId);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (! $data) {
            return ['status' => false, 'message' => 'Course not found', 'data' => null];
        }

        return ['status' => true, 'data' => $data];
    }

    /**
     * Get paginated student enrolled courses.
     *
     * @param  int  $studentId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getStudentCourses($studentId, $page = 1, $limit = 10) {
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);
        $offset = ($page - 1) * $limit;

        $countQuery = 'select count(*) as total from enrollments where student_id = ?';
        $stmt = $this->conn->prepare($countQuery);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();

        $total = $stmt->get_result()->fetch_assoc()['total'];

        $query = '
            select
                c.id as course_id,
                c.name as course_name,
                c.duration_weeks,
                c.max_seats,
                c.is_active as course_status,
                e.id as enrollment_id,
                e.enrolled_date,
                e.status as enrollment_status,
                i.user_id as instructor_id,
                i.name as instructor_name
            from enrollments e
            join courses c on e.course_id = c.id
            join instructors i on e.instructor_id = i.user_id
            where e.student_id = ?
            order by e.enrolled_date desc, e.id desc
            limit ? offset ?';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $studentId, $limit, $offset);
        $stmt->execute();

        return [
            'status' => true,
            'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_rows' => (int) $total,
                'total_pages' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * Get available courses for student enrollment.
     *
     * @param  int  $studentId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getAvailableCourses($studentId, $page = 1, $limit = 10) {
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);
        $offset = ($page - 1) * $limit;

        $countQuery = "
            select count(*) as total
            from (
                select c.id
                from courses c
                join courses_instructors ci on ci.course_id = c.id
                where c.is_active = 'active'
                and not exists (
                    select 1
                    from enrollments e
                    where e.course_id = c.id
                    and e.student_id = ?
                    and e.status in ('requested', 'active', 'completed', 'course_inactive')
                )
                group by c.id
            ) available_courses";

        $stmt = $this->conn->prepare($countQuery);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();

        $total = $stmt->get_result()->fetch_assoc()['total'];

        $query = "
            select
                c.id as course_id,
                c.name as course_name,
                c.duration_weeks,
                c.max_seats,
                c.is_active as course_status,
                count(case when e.status in ('active', 'course_inactive') then e.id end) as filled_seats,
                c.max_seats - count(case when e.status in ('active', 'course_inactive') then e.id end) as available_seats,
                i.user_id as instructor_id,
                i.name as instructor_name
            from courses c
            join courses_instructors ci on ci.course_id = c.id
            join instructors i on ci.instructor_id = i.user_id
            left join enrollments e on e.course_id = c.id
            where c.is_active = 'active'
            and not exists (
                select 1
                from enrollments se
                where se.course_id = c.id
                and se.student_id = ?
                and se.status in ('requested', 'active', 'completed', 'course_inactive')
            )
            group by c.id, c.name, c.duration_weeks, c.max_seats, c.is_active, i.user_id, i.name
            order by c.id desc, i.name asc
            limit ? offset ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $studentId, $limit, $offset);
        $stmt->execute();

        return [
            'status' => true,
            'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_rows' => (int) $total,
                'total_pages' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * Get enrolled course details for student.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return array
     */
    public function getStudentCourseDetails($studentId, $courseId) {
        $query = '
            select
                c.id as course_id,
                c.name as course_name,
                c.duration_weeks,
                c.max_seats,
                c.is_active as course_status,
                e.id as enrollment_id,
                e.enrolled_date,
                e.status as enrollment_status,
                i.user_id as instructor_id,
                i.name as instructor_name
            from enrollments e
            join courses c on e.course_id = c.id
            join instructors i on e.instructor_id = i.user_id
            where e.student_id = ?
            and c.id = ?
            limit 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $studentId, $courseId);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_assoc();

        if (! $data) {
            return ['status' => false, 'message' => 'Course not found', 'data' => null];
        }

        return ['status' => true, 'data' => $data];
    }

    /**
     * Get available course details for student.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return array
     */
    public function getAvailableCourseDetails($studentId, $courseId) {
        $query = "
            select
                c.id as course_id,
                c.name as course_name,
                c.duration_weeks,
                c.max_seats,
                c.is_active as course_status,
                count(case when e.status in ('active', 'course_inactive') then e.id end) as filled_seats,
                c.max_seats - count(case when e.status in ('active', 'course_inactive') then e.id end) as available_seats,
                i.user_id as instructor_id,
                i.name as instructor_name
            from courses c
            join courses_instructors ci on ci.course_id = c.id
            join instructors i on ci.instructor_id = i.user_id
            left join enrollments e on e.course_id = c.id
            where c.id = ?
            and c.is_active = 'active'
            and not exists (
                select 1
                from enrollments se
                where se.course_id = c.id
                and se.student_id = ?
                and se.status in ('requested', 'active', 'completed', 'course_inactive')
            )
            group by c.id, c.name, c.duration_weeks, c.max_seats, c.is_active, i.user_id, i.name
            order by i.name asc
            limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $courseId, $studentId);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_assoc();

        if (! $data) {
            return ['status' => false, 'message' => 'Course not found', 'data' => null];
        }

        return ['status' => true, 'data' => $data];
    }

    /**
     * Get assigned courses with instructors.
     *
     * @return array
     */
    public function getAssignedCourses() {
        $query = 'select 
                    c.id as course_id,
                    c.name as course_name,
                    i.user_id as instructor_id,
                    i.name as instructor_name
                from courses_instructors ci
                join courses c ON ci.course_id = c.id
                join instructors i on ci.instructor_id = i.user_id
                order by c.id desc';

        $result = $this->conn->query($query);

        return [
            'status' => true,
            'data' => $result->fetch_all(MYSQLI_ASSOC),
        ];
    }

    /**
     * Get course options list.
     *
     * @return array
     */
    public function getOptions() {
        $query = 'select id, name from courses';
        $result = $this->conn->query($query);

        return ['status' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }

    public function getEnrollOptions($studentId) {
        $query = "select 
                    c.id as course_id,
                    c.name as course_name,
                    i.user_id as instructor_id,
                    i.name as instructor_name
                from courses_instructors ci
                join courses c ON ci.course_id = c.id
                join instructors i on ci.instructor_id = i.user_id
                left join enrollments e on e.course_id = c.id and e.instructor_id = i.user_id
                and e.student_id = ?
                where e.id is NULL or e.status not in ('active','completed')";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $studentId);
        $stmt->execute();

        return ['status' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)];
    }

    /**
     * Get available seats for a course.
     *
     * @param  int  $courseId
     * @return int
     */
    public function getAvailableSeats($courseId) {
        $query = "select c.max_seats - count(e.id) as available_seats
                from courses c
                left join enrollments e on e.course_id = c.id and e.status in ('active', 'course_inactive')
                where c.id = ?
                group by c.id, c.max_seats";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return isset($result['available_seats']) ? (int) $result['available_seats'] : 0;
    }

    /**
     * Create a course.
     *
     * @param  string  $name
     * @param  int  $durationWeeks
     * @param  int  $maxSeats
     * @return int
     */
    public function create($name, $durationWeeks, $maxSeats) {
        $query = 'insert into courses (name, duration_weeks, max_seats) values (?, ?, ?)';

        $stmt = $this->conn->prepare($query);
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('sii', $name, $durationWeeks, $maxSeats);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }

        return $this->conn->insert_id;
    }

    /**
     * Assign course to instructor.
     *
     * @param  int  $courseId
     * @param  int  $instructorId
     * @return void
     */
    public function assignCourse($courseId, $instructorId) {
        $query = 'insert into courses_instructors (course_id, instructor_id) values (? , ?)';

        $stmt = $this->conn->prepare($query);
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('ii', $courseId, $instructorId);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }

    /**
     * Update course data.
     *
     * @param  int  $courseId
     * @param  array  $data
     * @return mixed
     */
    public function updateCourse($courseId, $data) {
        $queryData = QueryHelper::buildUpdateQuery(
            'courses',
            $data,
            [
                'name' => 's',
                'duration_weeks' => 'i',
                'max_seats' => 'i',
                'is_active' => 's',
            ],
            ['id' => ['value' => $courseId, 'type' => 'i']]
        );

        return QueryHelper::execute($this->conn, $queryData);

        if ($affected > 0) {
            return ['status' => true, 'message' => 'Course Updated successfully'];
        } else {
            return ['status' => false, 'message' => MSG_NO_CHANGES_MADE];
        }
    }

    /**
     * Update course assignment.
     *
     * @param  int  $courseId
     * @param  int  $oldInstructorId
     * @param  int  $newInstructorId
     * @return mixed
     */
    public function updateAssignment($courseId, $oldInstructorId, $newInstructorId) {
        $queryData = QueryHelper::buildUpdateQuery(
            'courses_instructors',
            ['instructor_id' => $newInstructorId],
            ['instructor_id' => 'i'],
            [
                'course_id' => ['value' => $courseId,       'type' => 'i'],
                'instructor_id' => ['value' => $oldInstructorId, 'type' => 'i'],
            ]
        );

        return QueryHelper::execute($this->conn, $queryData);
    }

    /**
     * Delete course assignment.
     *
     * @param  int  $courseId
     * @param  int  $instructorId
     * @return int
     */
    public function deleteAssignment($courseId, $instructorId) {
        $query = 'delete from courses_instructors where course_id = ? and instructor_id = ? limit 1';

        $stmt = $this->conn->prepare($query);
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('ii', $courseId, $instructorId);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }

        return $stmt->affected_rows;
    }

    /**
     * Soft delete course.
     *
     * @param  int  $courseId
     * @return mixed
     */
    public function deleteCourse($courseId) {

        $queryData = QueryHelper::buildUpdateQuery(
            'courses',
            ['is_active' => 'inactive'],
            ['is_active' => 's'],
            ['id' => ['value' => $courseId, 'type' => 'i']]
        );

        return QueryHelper::execute($this->conn, $queryData);

    }

    /**
     * Check if course already assigned.
     *
     * @param  int  $courseId
     * @param  int  $instructorId
     * @return bool
     */
    public function isAlreadyAssigned($courseId, $instructorId) {
        $query = 'select 1 from courses_instructors where course_id = ? and instructor_id = ? limit 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $courseId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if instructor assigned to course.
     *
     * @param  int  $courseId
     * @param  int  $instructorId
     * @return bool
     */
    public function hasInstructor($courseId, $instructorId) {
        $query = 'select 1 from courses_instructors where course_id = ? and instructor_id = ? limit 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $courseId, $instructorId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if course exists.
     *
     * @param  int  $courseId
     * @return bool
     */
    public function exists($courseId) {
        $query = 'select 1 from courses where id = ? limit 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $courseId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if course is active.
     *
     * @param  int  $courseId
     * @return bool
     */
    public function isActive($courseId) {
        $query = "select 1 from courses where id = ? and is_active = 'active' limit 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $courseId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }
}
