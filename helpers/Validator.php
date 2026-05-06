<?php

/**
 * Provides reusable input validation methods.
 */
class Validator {
    /**
     * Validate email address.
     *
     * @param  string  $email
     * @return string
     *
     * @throws Exception
     */
    public static function email($email) {
        $email = trim($email);

        if ($email === '') {
            throw new Exception('Email is required');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        return $email;
    }

    /**
     * Validate password.
     *
     * @param  string  $password
     * @return string
     *
     * @throws Exception
     */
    public static function password($password) {
        $password = trim($password);

        if ($password === '') {
            throw new Exception('Password is required');
        }

        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters');
        }

        return $password;
    }

    /**
     * Validate name field.
     *
     * @param  string  $name
     * @param  string  $label
     * @return string
     *
     * @throws Exception
     */
    public static function name($name, $label = 'Name') {
        $name = trim($name);

        if ($name === '') {
            throw new Exception("$label is required");
        }

        if (strlen($name) < 2) {
            throw new Exception("$label must be at least 2 characters");
        }

        return $name;
    }

    /**
     * Validate salary amount.
     *
     * @param  mixed  $salary
     * @return int
     *
     * @throws Exception
     */
    public static function salary($salary) {
        if ($salary === null) {
            throw new Exception('Salary is required');
        }

        if (! is_numeric($salary) || $salary < 0) {
            throw new Exception('Invalid salary');
        }

        return (int) $salary;
    }

    /**
     * Validate phone number.
     *
     * @param  string|null  $phone
     * @return string|null
     *
     * @throws Exception
     */
    public static function phone($phone) {
        if (empty($phone)) {
            return null;
        }

        $phone = trim($phone);

        if (! preg_match('/^[0-9]{10}$/', $phone)) {
            throw new Exception('Invalid phone number');
        }

        return $phone;
    }

    /**
     * Validate integer value.
     *
     * @param  mixed  $value
     * @param  string  $label
     * @param  int  $min
     * @return int
     *
     * @throws Exception
     */
    public static function integer($value, $label = 'Value', $min = 0) {
        if ($value === null || $value === '') {
            throw new Exception("$label is required");
        }

        if (! is_numeric($value)) {
            throw new Exception("$label must be a number");
        }

        $value = (int) $value;

        if ($value < $min) {
            throw new Exception("$label must be at least $min");
        }

        return $value;
    }

    /**
     * Validate status value.
     *
     * @param  string  $value
     * @param  string  $label
     * @return string
     *
     * @throws Exception
     */
    public static function status($value, $label = 'Status') {
        $allowed = ['active', 'inactive', 'disabled'];

        if (! in_array($value, $allowed, true)) {
            throw new Exception("Invalid $label");
        }

        return $value;
    }

    /**
     * Validate user role.
     *
     * @param  string  $value
     * @return string
     *
     * @throws Exception
     */
    public static function role($value) {
        $allowed = ROLES;

        if (! in_array($value, $allowed, true)) {
            throw new Exception('Invalid role');
        }

        return $value;
    }
}
