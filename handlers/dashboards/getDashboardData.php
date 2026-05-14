<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(Result::fail(MSG_INVALID_METHOD));
    exit;
}

try {
    $currentUser = AuthHelper::user();

    if (! $currentUser) {
        echo json_encode(Result::fail(MSG_UNAUTHORIZED));
        exit;
    }

    $dashboardData = $user->getDashboardData();

    if (! $dashboardData['status']) {
        echo json_encode(Result::fail($dashboardData['message']));
        exit;
    }

    echo json_encode(Result::success('Dashboard data fetched', $dashboardData));

} catch (Throwable $e) {
    echo json_encode(Result::fail('Server error', $e->getMessage()));
}

exit;
