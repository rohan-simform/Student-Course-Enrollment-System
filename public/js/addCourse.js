$(document).ready(function () {

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.name($('#name').val(), 'Course Name');
            Validator.integer($('#durationWeeks').val(), 'Duration Weeks', 1);
            Validator.integer($('#maxSeats').val(), 'Max Seats', 1);

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

                if (response.status && response.redirect) {
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