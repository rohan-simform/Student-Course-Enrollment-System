<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../helpers/Result.php';
require_once __DIR__ . '/../helpers/Logger.php';

class AuthService{
    private $conn;
    private $user;
    private $student;

    public function __construct($db){
        $this->conn    = $db;
        $this->user    = new User($db);
        $this->student = new Student($db);
    }

    public function login($email, $password){
        try {
            $foundUser = $this->user->getByEmail($email);

            if (!$foundUser)
                return Result::fail("Email not found");

            // if (!password_verify($password, $foundUser['password']))
            if ($password !== $foundUser['password'])
                return Result::fail("Invalid email or password");

            if ($foundUser['status'] !== 'active')
                return Result::fail("Account disabled");

            return Result::success("Login successful", [
                'user_id' => $foundUser['id'],
                'role'    => $foundUser['role']
            ]);

        } catch(Throwable $e){
            Logger::error($e, 'Auth login');
            return Result::fail("Unexpected error");
        }
    }

    public function register($email, $password, $name, $phone){
        try {
            $this->conn->begin_transaction();

            $userId = $this->user->create('student', $email, $password);

            $this->student->create($userId, $name, $phone, date('Y-m-d'));

            $this->conn->commit();

            return Result::success("Registered successfully", ['user_id' => $userId]);

        } catch(mysqli_sql_exception $e){
            $this->conn->rollback();
            Logger::error($e, 'Auth register');
            if ($e->getCode() == 1062)
                return Result::fail("Email already exists");
            return Result::fail("Failed to register");
        } catch(Throwable $e){
            $this->conn->rollback();
            Logger::error($e, 'Auth register');
            return Result::fail("Unexpected error");
        }
    }
}