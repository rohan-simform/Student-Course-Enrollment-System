<?php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../classes/EmailQueue.php';
require_once __DIR__ . '/../helpers/MailHelper.php';
require_once __DIR__ . '/../helpers/Logger.php';

$queue = new EmailQueue($conn);
$mail  = new MailHelper();
while (true) {
    try {
        $job = $queue->getNextPending();

        if (!$job){
            sleep(5);
            continue;
        }

        $queue->markProcessing($job['id']);

        $start = time();
        $result = $mail->sendMail($job['recipient'],$job['subject'],$job['body']);

        $processedAt = date('Y-m-d H:i:s');

        if ($result === true) {
            $queue->markSent($job['id']);

            Logger::mail(
                "job_id: {$job['id']} | status: success | to: {$job['recipient']}" . PHP_EOL .
                "queued_at: {$job['created_at']} | processed_at: {$processedAt}" . PHP_EOL .
                "duration: {time() - $start}s"
            );

        } else {
            $queue->markFailed($job['id'], $result);

            Logger::mail(
                "job_id: {$job['id']} | status: failed | to: {$job['recipient']}" . PHP_EOL .
                "queued_at: {$job['created_at']} | processed_at: {$processedAt}" . PHP_EOL .
                "error: {$result} | duration: {$duration}s"
            );
        }

    } catch (Throwable $e) {
        Logger::error($e, 'MAIL_WORKER');
    }
}