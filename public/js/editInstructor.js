let currentInstructorId = '';
let originalData = {};

function loadInstructorDetails() {

    const urlParams = new URLSearchParams(window.location.search);

    currentInstructorId = urlParams.get('user_id');

    if (!currentInstructorId) {
        alert('Instructor ID not provided');
        window.history.back();
        return;
    }

    $.ajax({
        url: APP.baseUrl + `users/getInstructorDetails.php?user_id=${currentInstructorId}`,
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert(result.message);
                window.history.back();
                return;
            }

            const instructor = result.data;

            $('input[name="user_id"]').val(instructor.id);
            $('#email').val(instructor.email || '');
            $('#name').val(instructor.name || '');
            $('#phone').val(instructor.phone || '');
            $('#salary').val(instructor.salary || '');
            $('#status').val(instructor.status || 'active');

            originalData = {
                email: instructor.email || '',
                name: instructor.name || '',
                phone: instructor.phone || '',
                salary: String(instructor.salary || ''),
                status: instructor.status || 'active'
            };
        }
    });
}

$(document).ready(function () {

    loadInstructorDetails();

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.email($('#email').val());
            Validator.name($('#name').val());
            Validator.phone($('#phone').val());
            Validator.integer($('#salary').val(), 'Salary', 0);

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

        if ($('#email').val() !== originalData.email) formData.email = $('#email').val();
        if ($('#name').val() !== originalData.name) formData.name = $('#name').val();
        if ($('#phone').val() !== originalData.phone) formData.phone = $('#phone').val();
        if ($('#salary').val() !== originalData.salary) formData.salary = $('#salary').val();
        if ($('#status').val() !== originalData.status) formData.status = $('#status').val();

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
                    window.location.href = 'listInstructors.php';
                }
            },

            complete: function () {
                $submitBtn.prop('disabled', false);
            }
        });
    });
});