<?php

class CsrfHelper {
    public static function getToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyToken($token) {
        if (empty($_SESSION['csrf_token']) || ! is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function hiddenInput() {
        return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8').'">';
    }

    public static function getRequestToken() {
        return $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }

    public static function verifyRequest() {
        return self::verifyToken(self::getRequestToken());
    }
}
