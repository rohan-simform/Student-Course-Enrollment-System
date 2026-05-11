<?php session_start();
require_once __DIR__.'/../helpers/CsrfHelper.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <?php include __DIR__.'/includes/form_head.php'; ?>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="width: 100%; max-width: 500px; padding: 20px;">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                <p>Register to get started</p>
            </div>

            <form id="registerForm" action="../handlers/auth/register.php" method="POST" novalidate>
                <?= CsrfHelper::hiddenInput() ?>

                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" name="phone" id="phone">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit" style="flex: 1;"><i class="fas fa-check"></i> Register</button>
                </div>

                <div style="text-align: center; margin-top: 15px;">
                    <span style="color: #718096;">Already have an account? </span>
                    <a href="index.php" style="color: #667eea; font-weight: 600; text-decoration: none;">Login</a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="./config.php"></script>
    <script defer src="./js/validator.js"></script>
    <script defer src="./js/register.js"></script>
</body>
</html>