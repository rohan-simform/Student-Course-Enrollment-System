<?php
class Logger
{
    private static $logFile = __DIR__ . '/../logs/app.log';

    public static function error($e, $context = '')
    {
        self::write('ERROR', $e, $context);
    }

    public static function info($message, $context = '')
    {
        self::write('INFO', $message, $context);
    }

    public static function warning($message, $context = '')
    {
        self::write('WARNING', $message, $context);
    }

    private static function write($level, $data, $context = '')
    {
        $time = date('Y-m-d H:i:s');

        if ($data instanceof Throwable) {
            $message = $data->getMessage();
            $file    = $data->getFile();
            $line    = $data->getLine();
            $trace   = $data->getTraceAsString();

            $log = "[$time] [$level] [$context] $message in $file:$line" . PHP_EOL .
                   $trace . PHP_EOL .
                   str_repeat('-', 120) . PHP_EOL;

        } else {
            $log = "[$time] [$level] [$context] $data" . PHP_EOL .
                   str_repeat('-', 120) . PHP_EOL;
        }

        file_put_contents(self::$logFile, $log, FILE_APPEND);
    }
}