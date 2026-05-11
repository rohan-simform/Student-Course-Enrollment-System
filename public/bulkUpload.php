<?php include __DIR__.'/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Bulk Upload Students</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
    <link href="/public/css/forms/bulkUpload.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__.'/includes/sidebar.php'; ?>

    <div class="form-wrapper">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-upload"></i> Bulk Upload Students</h2>
                <p>Upload a CSV file to create multiple students at once</p>
            </div>

            <form method="POST" enctype="multipart/form-data" action="../handlers/users/bulkStudentCreate.php" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group file-upload-wrapper">
                    <label for="csv_file"><i class="fas fa-file-csv"></i> CSV File *</label>
                    <div class="file-upload-area">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-content">
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <div class="file-help-text">
                                Click to browse and select your CSV file, or drag and drop it here
                            </div>
                            <div class="file-name-display" id="fileNameDisplay"></div>
                        </div>
                    </div>
                </div>

                <div class="csv-example">
                    <div class="csv-example-title"><i class="fas fa-info-circle"></i> CSV Format Example:</div>
                    <code>name,email,phone
                        John Doe,john@example.com,9876543210
                        Jane Smith,jane@example.com,9876543211
                        Bob Johnson,bob@example.com,9876543212</code>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="submitBtn" disabled><i class="fas fa-cloud-upload-alt"></i> Upload</button>
                    <a href="<?= AuthHelper::dashboardPath() ?>" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="/public/js/bulkUpload.js"></script>
    <script defer src="/public/js/functions.js"></script>
</body>

</html>