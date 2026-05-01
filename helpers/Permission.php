<?php

require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../classes/Enrollment.php';

/**
 * Handles role-based permission checks.
 */
class Permission
{
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private static $db;

    /**
     * Initialize permission system with database connection.
     *
     * @param mysqli $db
     * @return void
     */
    public static function init($db)
    {
        self::$db = $db;
    }

    /**
     * Static permission map by role.
     *
     * @var array
     */
    private static $permissions = [
        ROLE_ADMIN => [
            'user.create','user.read','user.update','user.delete',
            'course.create','course.update','course.delete','course.assign','course.enroll',
            'enrollment.update','enrollment.cancel'
        ],

        ROLE_INSTRUCTOR => [
            'user.read','user.update',
            'course.read','course.update','course.enroll',
            'enrollment.update','enrollment.cancel','enrollment.view',
            'instructor.view','student.view','student.delete'
        ],

        ROLE_STUDENT => [
            'user.read','user.update',
            'course.read','enrollment.view','instructor.view'
        ],
    ];

    /**
     * Resource ownership resolvers by role.
     *
     * @var array
     */
    private static $resolvers = [
        ROLE_INSTRUCTOR => [
            'user'        => 'isSelf',
            'course'      => ['model' => 'Course',     'method' => 'hasInstructor'],
            'enrollment'  => ['model' => 'Enrollment', 'method' => 'hasInstructor'],
            'instructor'  => 'isSelf',
            'student'     => ['model' => 'Enrollment', 'method' => 'studentUnderInstructor'],
        ],
        ROLE_STUDENT => [
            'user'        => 'isSelf',
            'course'      => ['model' => 'Enrollment', 'method' => 'studentInCourse'],
            'enrollment'  => ['model' => 'Enrollment', 'method' => 'hasStudent'],
            'instructor'  => ['model' => 'Enrollment', 'method' => 'studentUnderInstructor'],
        ],
    ];

    /**
     * Check if current user has permission.
     *
     * @param string $permission
     * @param int|null $resourceId
     * @return bool
     */
    public static function check($permission, $resourceId = null) {
        $user = AuthHelper::user();
        if (!$user) return false;

        $role = $user['role'];
        $resource = explode('.', $permission)[0];

        if ($role === ROLE_ADMIN)
            return in_array($permission, self::$permissions[ROLE_ADMIN]);

        if (!in_array($permission, self::$permissions[$role] ?? []))
            return false;

        $resolver = self::$resolvers[$role][$resource] ?? null;
        if (!$resolver) return false;

        if ($resolver === 'isSelf')
            return $user['user_id'] === (int) $resourceId;

        $models = [
            'Course'     => new Course(self::$db),
            'Enrollment' => new Enrollment(self::$db),
        ];

        $model  = $models[$resolver['model']];
        $method = $resolver['method'];

        return $model->$method($resourceId, $user['user_id']);
    }
}