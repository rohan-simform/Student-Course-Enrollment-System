<?php

class Result{

    public static function success($message = '', $data = null){
        return [
            'status' => true,
            'message' => $message,
            'data' => $data,
            'error' => []
        ];
    }

    public static function fail($message = '', $error = null){
        return [
            'status' => false,
            'message' => $message,
            'data' => null,
            'error' => $error
        ];
    }
}