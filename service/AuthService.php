<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../helpers/Result.php';
require_once __DIR__ . '/../helpers/Logger.php';

/**
 * Handles authentication and registration logic.
 */
class AuthService{
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
     * Create a new AuthService instance.
     *
     * @param mysqli $db Database connection.
     */
    public function __construct($db){
        $this->conn    = $db;
        $this->user    = new User($db);
        $this->student = new Student($db);
    }

    /**
     * Authenticate user login.
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login($email, $password, $captcha){
        try {
            // if(!$this->checkCaptcha($captcha))
            //     return Result::fail("Invalid Captcha");

            $foundUser = $this->user->getByEmail($email);

            if (!$foundUser)
                return Result::fail("Email not found");

            // if (!password_verify($password, $foundUser['password']))
            if ($password !== $foundUser['password'])
                return Result::fail("Invalid email or password");

            if ($foundUser['status'] !== USER_STATUS_ACTIVE)
                return Result::fail("Account disabled");

            return Result::success("Login successful", [
                'user_id' => $foundUser['id'],
                'role'    => $foundUser['role']
            ]);

        } catch(Throwable $e){
            Logger::error($e, 'Auth login');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    /**
     * Register a new student account.
     *
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $phone
     * @return array
     */
    public function register($email, $password, $name, $phone){
        try {
            $this->conn->begin_transaction();

            $userId = $this->user->create(ROLE_STUDENT, $email, $password);

            $this->student->create($userId, $name, $phone, date('Y-m-d'));

            $this->conn->commit();

            return Result::success("Registered successfully", ['user_id' => $userId]);

        } catch(mysqli_sql_exception $e){
            $this->conn->rollback();
            Logger::error($e, 'Auth register');
            if ($e->getCode() == 1062)
                return Result::fail(MSG_EMAIL_ALREADY_EXISTS);
            return Result::fail("Failed to register");
        } catch(Throwable $e){
            $this->conn->rollback();
            Logger::error($e, 'Auth register');
            return Result::fail(MSG_UNEXPECTED_ERROR);
        }
    }

    private function checkCaptcha($captcha){
        $code = $_SESSION['captcha'];
        return $captcha === $code;
    }
}