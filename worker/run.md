docker exec php-php84-1 php /var/www/html/worker/emailWorker.php >> /home/rohan.rohit@simform.dom/my/PHP/logs/mail_worker.log 2>&1 &

docker exec php-php84-1 ps aux | grep emailWorker

docker exec php-php84-1 kill <>