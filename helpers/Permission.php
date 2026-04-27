<?php

require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../classes/Enrollment.php';

class Permission
{
    private static $db;

    public static function init($db)
    {
        self::$db = $db;
    }

    private static $permissions = [
        'admin' => [
            'user.create',
            'user.read',
            'user.update',
            'user.delete',
            'course.create',
            'course.update',
            'course.delete',
            'course.assign',
            'course.enroll',
            'enrollment.update',
            'enrollment.cancel'
        ],

        'instructor' => [
            'user.read',
            'user.update',
            'course.read',
            'course.update',
            'course.enroll',
            'enrollment.update',
            'enrollment.cancel',
            'enrollment.view',
            'instructor.view',
            'student.view',
            'student.delete'
        ],

        'student' => [
            'user.read',
            'user.update',
            'course.read',
            'enrollment.view',
            'instructor.view'
        ],
    ];

    private static $resolvers = [
        'instructor' => [
            'user'        => 'isSelf',
            'course'      => ['model' => 'Course',     'method' => 'hasInstructor'],
            'enrollment'  => ['model' => 'Enrollment', 'method' => 'hasInstructor'],
            'instructor'  => 'isSelf',
            'student'     => ['model' => 'Enrollment', 'method' => 'studentUnderInstructor'],
        ],
        'student' => [
            'user'        => 'isSelf',
            'course'      => ['model' => 'Enrollment', 'method' => 'studentInCourse'],
            'enrollment'  => ['model' => 'Enrollment', 'method' => 'hasStudent'],
            'instructor'  => ['model' => 'Enrollment', 'method' => 'studentUnderInstructor'],
        ],
    ];

    public static function check($permission, $resourceId = null) {
        $user = AuthHelper::user();
        if (!$user) return false;

        $role = $user['role'];
        $resource = explode('.', $permission)[0];

        if ($role === 'admin')
            return in_array($permission, self::$permissions['admin']);

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
