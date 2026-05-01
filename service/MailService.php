<?php

require_once __DIR__ . '/../classes/EmailQueue.php';
require_once __DIR__ . '/../helpers/Logger.php';

/**
 * Handles email queue operations.
 */
class MailService{

    /**
     * Email queue model instance.
     *
     * @var EmailQueue
     */
    private $queue;

    /**
     * Create a new MailService instance.
     *
     * @param mysqli $db Database connection.
     */
    public function __construct($db) {
        $this->queue = new EmailQueue($db);
    }

    /**
     * Queue welcome email for a single user.
     *
     * @param string $email
     * @param string $name
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function queueWelcomeMail($email, $name, $username, $password) {
        try {
            $subject = "Welcome to Enrollment System";

            $body = "
                <h2>Hello {$name},</h2>

                <p>Your registration has been completed by admin.</p>

                <p><b>Login Credentials:</b></p>

                <p>
                    Username: {$username}<br>
                    Password: {$password}
                </p>

                <p>Please login and change password after first login.</p>

                <br>
                <p>Thank you.</p>
            ";

            $this->queue->create($email, $subject, $body);

            Logger::mail("Queued welcome mail for {$email}");

            return true;

        } catch (Throwable $e) {
            Logger::error($e, 'MAIL_QUEUE');
            return false;
        }
    }

    /**
     * Queue welcome emails in bulk.
     *
     * @param array $users
     * @param string $password
     * @return void
     */
    public function bulkQueueWelcomeMail($users, $password) {
        try {
            $rows = [];

            foreach ($users as $user) {
                $rows[] = [
                    'recipient' => $user['email'],
                    'subject'   => 'Welcome to Enrollment System',
                    'body'      => "
                        <h2>Hello {$user['name']},</h2>

                        <p>Your registration has been completed by admin.</p>

                        <p>
                            Username: {$user['email']}<br>
                            Password: {$password}
                        </p>

                        <p>Please change password after login.</p>
                    "
                ];
            }

            $this->queue->bulkCreate($rows);
            Logger::mail("Bulk queued " . count($rows) . " emails");

        } catch (Throwable $e) {
            Logger::error($e, 'MAIL_BULK_QUEUE');
        }
    }
}