let currentAdminId = '';
let originalData = {};

function loadAdminDetails() {

    const urlParams = new URLSearchParams(window.location.search);

    currentAdminId = urlParams.get('user_id');

    if (!currentAdminId) {
        alert('Admin ID not provided');
        window.history.back();
        return;
    }

    $.ajax({
        url: APP.baseUrl + `users/getAdminDetails.php?user_id=${currentAdminId}`,
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert(result.message);
                window.history.back();
                return;
            }

            const admin = result.data;

            $('input[name="user_id"]').val(admin.id);
            $('#email').val(admin.email || '');
            $('#status').val(admin.status || 'active');

            originalData = {
                email: admin.email || '',
                status: admin.status || 'active'
            };
        },

        error: function () {

            alert('Failed to load admin details');

            window.history.back();
        }
    });
}

$(document).ready(function () {

    loadAdminDetails();

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.email($('#email').val());

            if ($('#password').val()) {
                Validator.password($('#password').val());
            }

        } catch (error) {

            alert(error.message);

            return;
        }

        const formData = {
            csrf_token: $('input[name="csrf_token"]').val(),
            user_id: $('input[name="user_id"]').val()
        };

        if ($('#email').val() !== originalData.email) {
            formData.email = $('#email').val();
        }

        if ($('#status').val() !== originalData.status) {
            formData.status = $('#status').val();
        }

        if ($('#password').val()) {
            formData.password = $('#password').val();
        }

        if (Object.keys(formData).length === 2) {
            alert('No changes detected');
            return;
        }

        $submitBtn.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',

            success: function (response) {

                alert(response.message);

                if (response.status) {
                    window.location.href = 'listAdmins.php';
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