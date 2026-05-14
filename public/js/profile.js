let currentUserRole = '';
let currentUserId = '';

let originalData = {};

function loadUserInfo() {

    return $.ajax({
        url: APP.baseUrl + 'auth/getCurrentUser.php',
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (result.status) {
                currentUserRole = result.data.role;
                currentUserId = result.data.user_id;
            }
        },

        error: function (xhr, status, error) {
            console.error('Error loading user info:', error);
        }
    });
}

function loadProfile() {

    return $.ajax({
        url: APP.baseUrl + 'auth/getProfile.php',
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert(result.message);
                return;
            }

            const profile = result.data;

            currentUserRole = profile.role;
            currentUserId = profile.user_id;

            $('input[name="user_id"]').val(profile.id);
            $('#email').val(profile.email || '');

            if (
                currentUserRole === 'student' ||
                currentUserRole === 'instructor'
            ) {

                $('#nameField').show();
                $('#phoneField').show();

                $('#name').val(profile.name || '');
                $('#phone').val(profile.phone || '');
            }

            if (currentUserRole === 'instructor') {

                $('#salaryField').show();

                $('#salary').val(profile.salary || '');
            }

            // Store original values
            originalData = {
                email: profile.email || '',
                name: profile.name || '',
                phone: profile.phone || ''
            };
        },

        error: function (xhr, status, error) {

            console.error('Error loading profile:', error);

            alert('Failed to load profile');
        }
    });
}

$(document).ready(function () {

    loadUserInfo();

    loadProfile();

    $('#profileForm').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.email($('#email').val());

            if ($('#nameField').is(':visible')) {
                Validator.name($('#name').val());
            }

            if ($('#phone').val()) {
                Validator.phone($('#phone').val());
            }

            if ($('#password').val()) {
                Validator.password($('#password').val());
            }

        } catch (error) {

            alert(error.message);

            return;
        }

        // Build only changed data
        const formData = {
            csrf_token: $('input[name="csrf_token"]').val(),
            user_id: $('input[name="user_id"]').val()
        };

        if ($('#email').val() !== originalData.email) {
            formData.email = $('#email').val();
        }

        if ($('#name').val() !== originalData.name) {
            formData.name = $('#name').val();
        }

        if ($('#phone').val() !== originalData.phone) {
            formData.phone = $('#phone').val();
        }

        // Always send password if entered
        if ($('#password').val()) {
            formData.password = $('#password').val();
        }

        // No changes
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

                    $('#password').val('');

                    // Update original values after successful save
                    originalData.email = $('#email').val();
                    originalData.name = $('#name').val();
                    originalData.phone = $('#phone').val();
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