<?php

session_start();

header('Content-Type: application/json');

require_once __DIR__.'/../../config/init.php';
require_once __DIR__.'/../../service/UserService.php';

function getColumnIndex($columnName, $header) {

    return array_search(
        strtolower($columnName),
        array_map('strtolower', $header)
    );
}

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception(MSG_INVALID_METHOD);
    }

    if (! CsrfHelper::verifyRequest()) {
        throw new Exception(MSG_INVALID_CSRF);
    }

    if (! isset($_FILES['csv_file'])) {
        throw new Exception('CSV file missing');
    }

    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }

    $extension = strtolower(
        pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION)
    );

    if ($extension !== 'csv') {
        throw new Exception('Only CSV files are allowed');
    }

    $csv = fopen($_FILES['csv_file']['tmp_name'], 'r');

    if (! $csv) {
        throw new Exception('Unable to read uploaded file');
    }

    $header = fgetcsv($csv, 0, ',', '"', '\\');

    if (! $header) {
        throw new Exception('CSV file is empty');
    }

    $emailIndex = getColumnIndex('email', $header);
    $nameIndex = getColumnIndex('name', $header);
    $phoneIndex = getColumnIndex('phone', $header);

    if (
        $emailIndex === false ||
        $nameIndex === false ||
        $phoneIndex === false
    ) {
        throw new Exception(
            'CSV must contain email, name and phone columns'
        );
    }

    $validRows = [];
    $failedRows = [];
    $rowNumber = 1;
    $seenEmails = [];

    $userService = new UserService($conn);

    while (($row = fgetcsv($csv, 0, ',', '"', '\\')) !== false) {

        $rowNumber++;

        $email = trim($row[$emailIndex] ?? '');
        $name = trim($row[$nameIndex] ?? '');
        $phone = trim($row[$phoneIndex] ?? '');

        // EMAIL
        try {

            $email = Validator::email($email);

            $lowerEmail = strtolower($email);

            if (isset($seenEmails[$lowerEmail])) {
                throw new Exception('Duplicate email in CSV');
            }

            $seenEmails[$lowerEmail] = true;

            if (! is_null($user->getByEmail($email))) {
                throw new Exception('Email already registered');
            }

        } catch (Exception $e) {

            $failedRows[] = [
                'row' => $rowNumber,
                'column' => 'email',
                'error' => $e->getMessage(),
            ];
        }

        // NAME
        try {

            $name = Validator::name($name);

        } catch (Exception $e) {

            $failedRows[] = [
                'row' => $rowNumber,
                'column' => 'name',
                'error' => $e->getMessage(),
            ];
        }

        // PHONE
        try {

            $phone = Validator::phone($phone);

        } catch (Exception $e) {

            $failedRows[] = [
                'row' => $rowNumber,
                'column' => 'phone',
                'error' => $e->getMessage(),
            ];
        }

        // ADD ONLY IF CURRENT ROW HAS NO ERRORS
        $currentRowErrors = array_filter(
            $failedRows,
            fn ($error) => $error['row'] === $rowNumber
        );

        if (empty($currentRowErrors)) {

            $validRows[] = [
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
            ];
        }
    }

    fclose($csv);

    // STOP ENTIRE INSERT IF ANY ERRORS EXIST
    if (! empty($failedRows)) {

        echo json_encode([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $failedRows,
        ]);

        exit;
    }

    if (empty($validRows)) {
        throw new Exception('No valid rows found');
    }

    $result = $userService->bulkAddStudent($validRows);

    if ($result['status']) {

        $result['message'] =
            'Successfully created '.count($validRows).' students';

        $result['redirect'] = '../public/listStudents.php';
    }

    echo json_encode($result);

} catch (Exception $e) {

    echo json_encode(
        Result::fail(
            MSG_VALIDATION_FAILED,
            $e->getMessage()
        )
    );
}
