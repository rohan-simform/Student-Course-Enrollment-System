<?php
session_start();
require_once __DIR__.'/../helpers/CsrfHelper.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enrollment System</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
    <link href="/public/css/forms/login.css" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="logo-section">
            <h1><i class="fas fa-graduation-cap"></i></h1>
            <h1>Enrollment System</h1>
            <p>Sign in to your account</p>
        </div>

        <div class="form-container">
            <form id="loginForm" action="../handlers/auth/login.php" method="POST" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" id="email" required placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" name="password" id="password" required placeholder="Enter your password">
                </div>

                <div class="form-group">
                    <label for="captcha"><i class="fas fa-shield-alt"></i> CAPTCHA Code *</label>
                    <div class="captcha-section">
                        <img src="/handlers/auth/captcha.php" id="captcha-img" alt="CAPTCHA" height="45">
                        <button type="button" class="captcha-refresh-btn" id="refreshCaptchaBtn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <input type="text" name="captcha" id="captcha" required placeholder="Enter code above">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-sign-in-alt"></i> Login</button>
                </div>

                <div style="text-align: center; margin-top: 15px;">
                    <span style="color: #718096;">Don't have an account? </span>
                    <a href="register.php" style="color: #667eea; font-weight: 600; text-decoration: none;">Register here</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="./config.php"></script>
    <script defer src="./js/validator.js"></script>
    <script defer src="./js/login.js"></script>
</body>
</html>

