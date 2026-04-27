<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/Instructor.php';
require_once __DIR__ . '/../helpers/Permission.php';
require_once __DIR__ . '/../helpers/Result.php';
require_once __DIR__ . '/../helpers/Logger.php';

class UserService{
    private $conn;
    private $user;
    private $student;
    private $instructor;

    public function __construct($db){
        $this->conn       = $db;
        $this->user       = new User($db);
        $this->student    = new Student($db);
        $this->instructor = new Instructor($db);
        Permission::init($db);
    }

    public function getUsers($role = null, $page = 1, $limit = 10){
        if (!Permission::check("user.read"))
            return Result::fail("Unauthorized");

        try {
            if ($role === 'student') {
                return $this->student->getAll($page, $limit);
            } elseif ($role === 'instructor') {
                return $this->instructor->getAll($page, $limit);
            } else {
                return $this->user->getUsers($role, $page, $limit);
            }
        } catch(Throwable $e){
            Logger::error($e, 'User getUsers');
            return Result::fail("Unexpected error");
        }
    }

    public function getProfile($userId){
        if (!Permission::check("user.read", $userId))
            return Result::fail("Unauthorized");

        try {
            $account = $this->user->getById($userId);

            if (!$account)
                return Result::fail("User not found");

            if ($account['role'] === 'student') {
                $profile = $this->student->getByUserId($userId);
            } elseif ($account['role'] === 'instructor') {
                $profile = $this->instructor->getByUserId($userId);
            }

            if (!empty($profile)) {
                $account = array_merge($account, $profile);
            }

            return Result::success("", $account);

        } catch(Throwable $e){
            Logger::error($e, 'User getProfile');
            return Result::fail("Unexpected error");
        }
    }

    public function addUser($role, $data){
        if (!Permission::check("user.create"))
            return Result::fail("Unauthorized");

        try {
            $this->conn->begin_transaction();

            $userId = $this->user->create($role, $data['email'], $data['password']);

            if ($role === 'student') {
                $enrolledOn = $data['enrolled_on'] ?? date('Y-m-d');
                $this->student->create($userId, $data['name'], $data['phone'] ?? null, $enrolledOn);
            } elseif ($role === 'instructor') {
                $this->instructor->create($userId, $data['name'], $data['salary'], $data['phone'] ?? null);
            }

            $this->conn->commit();

            return Result::success("User created successfully", ['user_id' => $userId]);

        } catch(mysqli_sql_exception $e){
            $this->conn->rollback();
            Logger::error($e, 'User create');
            if ($e->getCode() == 1062)
                return Result::fail("Email already exists");
            return Result::fail("Failed to create user");
        } catch(Throwable $e){
            $this->conn->rollback();
            Logger::error($e, 'User create');
            return Result::fail("Unexpected error");
        }
    }

    public function updateUser($userId, $data){
        if (!Permission::check("user.update", $userId))
            return Result::fail("Unauthorized");

        try {
            if (!$this->user->exists($userId))
                return Result::fail("User not found");

            $this->conn->begin_transaction();

            $total = 0;

            $accountData = array_intersect_key($data, array_flip(['email', 'password', 'status']));
            if (!empty($accountData))
                $total += $this->user->update($userId, $accountData);

            $account = $this->user->getById($userId);
            $role = $account['role'];

            $profileData = array_intersect_key($data, array_flip(['name', 'phone', 'salary', 'status']));

            if (!empty($profileData)) {
                if (isset($profileData['status'])) {
                    $profileData['is_active'] = ($profileData['status'] === 'active') ? 'active' : 'inactive';
                    unset($profileData['status']);
                }

                if ($role === 'student') {
                    $total += $this->student->update($userId, $profileData);
                } elseif ($role === 'instructor') {
                    $total += $this->instructor->update($userId, $profileData);
                }
            }

            $this->conn->commit();

            if ($total > 0)
                return Result::success("User updated successfully");
            else
                return Result::fail("No changes made");

        } catch(mysqli_sql_exception $e){
            $this->conn->rollback();
            Logger::error($e, 'User update');
            return Result::fail("Failed to update user");
        } catch(Throwable $e){
            $this->conn->rollback();
            Logger::error($e, 'User update');
            return Result::fail("Unexpected error");
        }
    }

    public function deleteUser($userId){
        if (!Permission::check("user.delete", $userId))
            return Result::fail("Unauthorized");

        try {
            if (!$this->user->exists($userId))
                return Result::fail("User not found");

            $account = $this->user->getById($userId);
            $role = $account['role'];

            $this->conn->begin_transaction();

            $total = 0;

            $total += $this->user->softDelete($userId);

            if ($role === 'student') {
                $total += $this->student->softDelete($userId);
            } elseif ($role === 'instructor') {
                $total += $this->instructor->softDelete($userId);
            }

            $this->conn->commit();

            if ($total > 0)
                return Result::success("User deactivated successfully");
            else
                return Result::fail("User not found");

        } catch(mysqli_sql_exception $e){
            $this->conn->rollback();
            Logger::error($e, 'User delete');
            return Result::fail("Failed to deactivate user");
        } catch(Throwable $e){
            $this->conn->rollback();
            Logger::error($e, 'User delete');
            return Result::fail("Unexpected error");
        }
    }
}