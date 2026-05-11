function toggleFields() {

    const role = $('#role').val();

    $('#nameField').hide();
    $('#phoneField').hide();
    $('#salaryField').hide();

    if (role === 'student') {

        $('#nameField').show();
        $('#phoneField').show();
    }

    else if (role === 'instructor') {

        $('#nameField').show();
        $('#phoneField').show();
        $('#salaryField').show();
    }
}

$(document).ready(function () {

    $('#role').on('change', toggleFields);

    $('#role').trigger('change');

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        const role = $('#role').val();

        try {

            Validator.role(role);

            Validator.email($('#email').val());

            Validator.password($('#password').val());

            if (
                role === 'student' ||
                role === 'instructor'
            ) {

                Validator.name($('#name').val());

                Validator.phone($('#phone').val());
            }

            if (role === 'instructor') {

                Validator.integer(
                    $('#salary').val(),
                    'Salary',
                    0
                );
            }

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

                if (
                    response.status &&
                    response.redirect
                ) {

                    window.location.href = response.redirect;
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