<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

if (! AuthHelper::user()) {
    echo json_encode(Result::fail(MSG_UNAUTHORIZED));
    exit;
}

try {
    $path = AuthHelper::dashboardPath();

    echo json_encode(Result::success('Dashboard path fetched', [
        'dashboard' => $path,
    ]));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
