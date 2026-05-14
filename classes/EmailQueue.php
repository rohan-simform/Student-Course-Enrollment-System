<?php

/**
 * Handles email queue database operations.
 */
class EmailQueue {
    /**
     * Database connection instance.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Create a new EmailQueue instance.
     *
     * @param  mysqli  $db  Database connection.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Insert single email into queue.
     *
     * @param  string  $recipient
     * @param  string  $subject
     * @param  string  $body
     * @return int
     */
    public function create($recipient, $subject, $body) {
        $stmt = $this->conn->prepare('insert into email_queue (recipient, subject, body) values (?, ?, ?)');

        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('sss', $recipient, $subject, $body);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }

        return $this->conn->insert_id;
    }

    /**
     * Insert multiple emails into queue.
     *
     * @param  array  $rows
     * @return void
     */
    public function bulkCreate($rows) {
        if (! $rows) {
            return;
        }

        $values = [];
        $params = [];
        $types = '';

        foreach ($rows as $row) {
            $values[] = '(?, ?, ?)';
            $types .= 'sss';

            $params[] = $row['recipient'];
            $params[] = $row['subject'];
            $params[] = $row['body'];
        }

        $sql = 'insert into email_queue (recipient, subject, body) values '.implode(',', $values);
        $stmt = $this->conn->prepare($sql);

        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param($types, ...$params);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }

    /**
     * Get next pending email.
     *
     * @return array|null
     */
    public function getNextPending() {
        $query = "select * from email_queue where status = 'pending' order by id asc limit 1";

        $result = $this->conn->query($query);

        return $result->fetch_assoc();
    }

    /**
     * Mark email as processing.
     *
     * @param  int  $id
     * @return void
     */
    public function markProcessing($id) {
        $stmt = $this->conn->prepare("update email_queue set status = 'processing' where id = ?");
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }

    /**
     * Mark email as sent.
     *
     * @param  int  $id
     * @return void
     */
    public function markSent($id) {
        $stmt = $this->conn->prepare("update email_queue set status = 'sent' where id = ?");
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }

    /**
     * Mark email as failed.
     *
     * @param  int  $id
     * @param  string  $error
     * @return void
     */
    public function markFailed($id, $error) {
        $stmt = $this->conn->prepare("update email_queue set status = 'failed', attempts = attempts + 1, error_message = ? where id = ?");
        if (! $stmt) {
            throw new Exception('Prepare failed: '.$this->conn->error);
        }

        $stmt->bind_param('si', $error, $id);

        if (! $stmt->execute()) {
            throw new mysqli_sql_exception($this->conn->error, $this->conn->errno);
        }
    }
}
