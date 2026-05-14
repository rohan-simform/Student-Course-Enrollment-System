let targetStudentId = '';
let originalData = {};

function loadStudentDetails() {

    const urlParams = new URLSearchParams(window.location.search);

    targetStudentId = urlParams.get('user_id');

    if (!targetStudentId) {
        alert('Student ID not provided');
        window.history.back();
        return;
    }

    $.ajax({
        url: APP.baseUrl + `users/getStudentDetails.php?user_id=${targetStudentId}`,
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert(result.message);
                window.history.back();
                return;
            }

            const student = result.data;

            $('input[name="user_id"]').val(student.id);
            $('#email').val(student.email || '');
            $('#name').val(student.name || '');
            $('#phone').val(student.phone || '');
            $('#status').val(student.status || 'active');

            originalData = {
                email: student.email || '',
                name: student.name || '',
                phone: student.phone || '',
                status: student.status || 'active'
            };
        }
    });
}

$(document).ready(function () {

    loadStudentDetails();

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.email($('#email').val());
            Validator.name($('#name').val());
            Validator.phone($('#phone').val());

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
                    window.location.href = 'listStudents.php';
                }
            },

            complete: function () {
                $submitBtn.prop('disabled', false);
            }
        });
    });
});