<?php

/**
 * Provides standardized response structures.
 */
class Result {
    /**
     * Create success response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return array
     */
    public static function success($message = '', $data = null) {
        return [
            'status' => true,
            'message' => $message,
            'data' => $data,
            'error' => [],
        ];
    }

    /**
     * Create failure response.
     *
     * @param  string  $message
     * @param  mixed  $error
     * @return array
     */
    public static function fail($message = '', $error = null) {
        return [
            'status' => false,
            'message' => $message,
            'data' => null,
            'error' => $error,
        ];
    }
}
