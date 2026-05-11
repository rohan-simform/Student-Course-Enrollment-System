$(document).ready(function () {

    $('#registerForm').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.name($('#name').val());
            Validator.email($('#email').val());
            Validator.password($('#password').val());
            Validator.phone($('#phone').val());

        } catch (error) {

            alert(error.message);
            return;
        }

        $submitBtn.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',

            success: function (response) {

                alert(response.message);

                if (response.status) {
                    window.location.href = 'index.php';
                }
            },

            error: function (xhr) {

                let message = 'Something went wrong';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            },

            complete: function () {

                $submitBtn.prop('disabled', false);
            }
        });

    });

});