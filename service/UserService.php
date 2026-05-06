<?php

require_once __DIR__.'/../classes/User.php';
require_once __DIR__.'/../classes/Student.php';
require_once __DIR__.'/../classes/Instructor.php';
require_once __DIR__.'/../helpers/Permission.php';
require_once __DIR__.'/../service/MailService.php';
require_once __DIR__.'/../helpers/Result.php';
require_once __DIR__.'/../helpers/Logger.php';

/**
 * Handles user business logic and permissions.
 */
class UserService {
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * User model instance.
     *
     * @var User
     */
    private $user;

    /**
     * Student model instance.
     *
     * @var Student
     */
    private $student;

    /**
     * Instructor model instance.
     *
     * @var Instructor
     */
    private $instructor;

    /**
     * Mail service instance.
     *
     * @var MailService
     */
    private $mailService;

    /**
     * Create a new UserService instance.
     *
     * @param  mysqli  $db  Database connection.
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
        $this->student = new Student($db);
        $this->instructor = new Instructor($db);
        $this->mailService = new MailService($db);
        Permission::init($db);
    }

    /**
     * Get users list by role.
     *
     * @param  string|null  $role
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getUsers($role = null, $page = 1, $limit = 10) {
        if (! Permission::check('user.read')) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            if ($role === ROLE_STUDENT) {
                return Result::success('', $this->student->getAll($page, $limit));
            } elseif ($role === ROLE_INSTRUCTOR) {
                return Result::success('', $this->instructor->getAll($page, $limit));
            } else {
                return Result::success('', $this->user->getUsers($role, $page, $limit));
            }
        } catch (Throwable $e) {
            Logger::error($e, 'User getUsers');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Get user profile details.
     *
     * @param  int  $userId
     * @return array
     */
    public function getProfile($userId) {
        if (! Permission::check('user.read', $userId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            $account = $this->user->getById($userId);

            if (! $account) {
                return Result::fail(MSG_USER_NOT_FOUND);
            }

            if ($account['role'] === ROLE_STUDENT) {
                $profile = $this->student->getByUserId($userId);
            } elseif ($account['role'] === ROLE_INSTRUCTOR) {
                $profile = $this->instructor->getByUserId($userId);
            }

            if (! empty($profile)) {
                $account = array_merge($account, $profile);
            }

            return Result::success('', $account);

        } catch (Throwable $e) {
            Logger::error($e, 'User getProfile');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Create new user account.
     *
     * @param  string  $role
     * @param  array  $data
     * @return array
     */
    public function addUser($role, $data) {
        if (! Permission::check('user.create')) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            $this->conn->begin_transaction();

            $userId = $this->user->create($role, $data['email'], $data['password']);

            if ($role === ROLE_STUDENT) {
                $enrolledOn = $data['enrolled_on'] ?? date('Y-m-d');
                $this->student->create($userId, $data['name'], $data['phone'] ?? null, $enrolledOn);
            } elseif ($role === ROLE_INSTRUCTOR) {
                $this->instructor->create($userId, $data['name'], $data['salary'], $data['phone'] ?? null);
            }

            $this->conn->commit();

            $this->mailService->queueWelcomeMail($data['email'], $data['name'], $data['email'], $data['password']);

            return Result::success(MSG_USER_CREATED_SUCCESSFULLY, ['user_id' => $userId]);

        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            Logger::error($e, 'User create');
            if ($e->getCode() == 1062) {
                return Result::fail(MSG_EMAIL_ALREADY_EXISTS);
            }

            return Result::fail(MSG_FAILED_TO_CREATE_USER);
        } catch (Throwable $e) {
            $this->conn->rollback();
            Logger::error($e, 'User create');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Create multiple student accounts in bulk.
     *
     * @param  array  $data
     * @return array
     */
    public function bulkAddStudent($data) {
        try {
            $this->conn->begin_transaction();

            $emails = array_column($data, 'email');
            $names = array_column($data, 'name');
            $phones = array_column($data, 'phone');
            $tempPassword = 'tempPass123';

            $ids = $this->user->bulkCreate(ROLE_STUDENT, $emails, $tempPassword);

            $enrolledOn = date('Y-m-d');
            $this->student->bulkCreate($ids, $names, $phones, $enrolledOn);

            $this->conn->commit();

            $this->mailService->bulkQueueWelcomeMail($data, $tempPassword);

            return Result::success(MSG_USER_CREATED_SUCCESSFULLY, ['ids' => $ids]);

        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            Logger::error($e, 'Student Bulk Create');
            if ($e->getCode() == 1062) {
                return Result::fail(MSG_EMAIL_ALREADY_EXISTS);
            }

            return Result::fail('Failed to Bulk create student');
        } catch (Throwable $e) {
            $this->conn->rollback();
            Logger::error($e, 'Student Bulk Create');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Update user account and profile.
     *
     * @param  int  $userId
     * @param  array  $data
     * @return array
     */
    public function updateUser($userId, $data) {
        if (! Permission::check('user.update', $userId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            if (! $this->user->exists(['id' => $userId])) {
                return Result::fail(MSG_USER_NOT_FOUND);
            }

            $this->conn->begin_transaction();

            $total = 0;

            $accountData = array_intersect_key($data, array_flip(['email', 'password', 'status']));
            if (! empty($accountData)) {
                $total += $this->user->update($userId, $accountData);
            }

            $account = $this->user->getById($userId);
            $role = $account['role'];

            $profileData = array_intersect_key($data, array_flip(['name', 'phone', 'salary', 'status']));

            if (! empty($profileData)) {
                if (isset($profileData['status'])) {
                    $profileData['is_active'] = ($profileData['status'] === 'active') ? 'active' : 'inactive';
                    unset($profileData['status']);
                }

                if ($role === ROLE_STUDENT) {
                    $total += $this->student->update($userId, $profileData);
                } elseif ($role === ROLE_INSTRUCTOR) {
                    $total += $this->instructor->update($userId, $profileData);
                }
            }

            $this->conn->commit();

            if ($total > 0) {
                return Result::success(MSG_USER_UPDATED_SUCCESSFULLY);
            } else {
                return Result::fail(MSG_NO_CHANGES_MADE);
            }

        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            Logger::error($e, 'User update');

            return Result::fail('Failed to update user');
        } catch (Throwable $e) {
            $this->conn->rollback();
            Logger::error($e, 'User update');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Soft delete user account.
     *
     * @param  int  $userId
     * @return array
     */
    public function deleteUser($userId) {
        if (! Permission::check('user.delete', $userId)) {
            return Result::fail(MSG_UNAUTHORIZED);
        }

        try {
            if (! $this->user->exists(['id' => $userId])) {
                return Result::fail(MSG_USER_NOT_FOUND);
            }

            $account = $this->user->getById($userId);
            $role = $account['role'];

            $this->conn->begin_transaction();

            $total = 0;

            $total += $this->user->softDelete($userId);

            if ($role === ROLE_STUDENT) {
                $total += $this->student->softDelete($userId);
            } elseif ($role === ROLE_INSTRUCTOR) {
                $total += $this->instructor->softDelete($userId);
            }

            $this->conn->commit();

            if ($total > 0) {
                return Result::success(MSG_USER_DEACTIVATED_SUCCESSFULLY);
            } else {
                return Result::fail(MSG_USER_NOT_FOUND);
            }

        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            Logger::error($e, 'User delete');

            return Result::fail(MSG_FAILED_TO_DEACTIVATE_USER);
        } catch (Throwable $e) {
            $this->conn->rollback();
            Logger::error($e, 'User delete');

            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }
}
