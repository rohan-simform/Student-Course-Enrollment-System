<?php

/**
 * Handles application and mail logging.
 */
class Logger {
    /**
     * Application log file path.
     *
     * @var string
     */
    private static $logFile = __DIR__.'/../logs/app.log';

    /**
     * Mail log file path.
     *
     * @var string
     */
    private static $mailFile = __DIR__.'/../logs/mail.log';

    /**
     * Write error log entry.
     *
     * @param  Throwable|string  $e
     * @param  string  $context
     * @return void
     */
    public static function error($e, $context = '') {
        self::write('ERROR', $e, $context, self::$logFile);
    }

    /**
     * Write info log entry.
     *
     * @param  string  $message
     * @param  string  $context
     * @return void
     */
    public static function info($message, $context = '') {
        self::write('INFO', $message, $context, self::$logFile);
    }

    /**
     * Write warning log entry.
     *
     * @param  string  $message
     * @param  string  $context
     * @return void
     */
    public static function warning($message, $context = '') {
        self::write('WARNING', $message, $context, self::$logFile);
    }

    /**
     * Write mail log entry.
     *
     * @param  string  $message
     * @param  string  $context
     * @return void
     */
    public static function mail($message, $context = 'MAIL') {
        self::write('MAIL', $message, $context, self::$mailFile);
    }

    /**
     * Write formatted log entry to file.
     *
     * @param  string  $level
     * @param  mixed  $data
     * @param  string  $context
     * @param  string  $file
     * @return void
     */
    private static function write($level, $data, $context, $file) {
        $time = date('Y-m-d H:i:s');

        if ($data instanceof Throwable) {

            $message = $data->getMessage();
            $srcFile = $data->getFile();
            $line = $data->getLine();
            $trace = $data->getTraceAsString();

            $log = "[$time] [$level] [$context] $message in $srcFile:$line".PHP_EOL.
                   $trace.PHP_EOL.
                   str_repeat('-', 120).PHP_EOL;

        } else {
            $log = "[$time] [$level] [$context] $data".PHP_EOL.
                   str_repeat('-', 120).PHP_EOL;
        }

        file_put_contents($file, $log, FILE_APPEND);
    }
}
