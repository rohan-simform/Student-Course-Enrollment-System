<?php
    session_start();
    header('Content-Type: application/javascript');
    require_once __DIR__ . '/../../helpers/CsrfHelper.php';
?>

window.APP = {
    baseUrl: "<?= 'http://' . $_SERVER['HTTP_HOST'] . '/handlers/' ?>",
    csrfToken: <?= json_encode(CsrfHelper::getToken()) ?>
};