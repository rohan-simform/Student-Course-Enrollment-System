$(document).ready(function () {

    const $form = $('#loginForm');
    const $submitBtn = $form.find('button[type="submit"]');
    const $captchaImg = $('#captcha-img');
    const $refreshCaptchaBtn = $('#refreshCaptchaBtn');

    $refreshCaptchaBtn.on('click', function () {
        refreshCaptcha();
    });

    $form.on('submit', function (e) {

        e.preventDefault();

        const $this = $(this);

        try {
            Validator.email($('#email').val());
            Validator.password($('#password').val());
        } catch (err) {
            alert(err.message);
            return;
        }

        $submitBtn.prop('disabled', true);

        $.ajax({
            url: $this.attr('action'),
            type: 'POST',
            data: $this.serialize(),
            dataType: 'json',

            success: function (response) {

                let message = response.message;

                if (!response.status && response.error) {
                    message = response.error;
                }

                alert(message);

                if (!response.status) {
                    refreshCaptcha();
                    return;
                }

                if (response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }

                window.location.href = 'index.php';
            },

            error: function (xhr) {

                let message = 'Something went wrong';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
                refreshCaptcha();
            },

            complete: function () {
                $submitBtn.prop('disabled', false);
            }
        });
    });

    function refreshCaptcha() {
        $captchaImg.attr('src', '/handlers/auth/captcha.php?' + Date.now());
        $('#captcha').val('');
    }
});