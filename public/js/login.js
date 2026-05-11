$(document).ready(function () {

    const $form = $('#loginForm');
    const $submitBtn = $form.find('button[type="submit"]');
    const $captchaImg = $('#captcha-img');
    const $refreshCaptchaBtn = $('#refreshCaptchaBtn');

    // Refresh captcha button click
    $refreshCaptchaBtn.on('click', function () {
        refreshCaptcha();
    });

    $form.on('submit', function (e) {

        e.preventDefault();

        const formData = new FormData(this);

        const email = $('#email').val().trim();
        const password = $('#password').val().trim();
        const captcha = $('#captcha').val().trim();

        if (email === '') {
            alert('Email is required');
            return;
        }

        if (password === '') {
            alert('Password is required');
            return;
        }

        // if (captcha === '') {
        //     alert('Captcha is required');
        //     return;
        // }

        $submitBtn.prop('disabled', true);
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Logging in...');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (result) {

                console.log(result);

                if (!result.status) {

                    alert(result.message || 'Login failed');

                    refreshCaptcha();

                    return;
                }

                if (result.redirect) {
                    window.location.href = result.redirect;
                    return;
                }
                window.location.reload();
            },

            error: function (xhr, status, error) {

                console.error(error);

                alert('Something went wrong. Please try again.');

                refreshCaptcha();
            },

            complete: function () {
                $submitBtn.prop('disabled', false);
                $submitBtn.html('<i class="fas fa-sign-in-alt"></i> Login');
            }
        });
    });

    function refreshCaptcha() {
        $captchaImg.attr('src','/handlers/auth/captcha.php?' + Date.now());
        $('#captcha').val('');
    }
});