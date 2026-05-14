let currentEnrollmentId = '';
let originalData = {};

function loadEnrollmentDetails() {

    const urlParams = new URLSearchParams(window.location.search);

    currentEnrollmentId = urlParams.get('id');

    if (!currentEnrollmentId) {
        alert('Enrollment ID not provided');
        window.history.back();
        return;
    }

    $.ajax({
        url: APP.baseUrl + `enrollments/getEnrollmentDetails.php?id=${currentEnrollmentId}`,
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert(result.message);
                window.history.back();
                return;
            }

            const { enrollment, courseInstructors } = result.data;

            $('input[name="id"]').val(enrollment.id);

            $('#student_info').val(
                enrollment.student_id + ' - ' + escapeHtml(enrollment.student_name)
            );

            $('#course_info').val(
                enrollment.course_id + ' - ' + escapeHtml(enrollment.course_name)
            );

            $('#instructor_id').html('');

            $.each(courseInstructors, function (index, instr) {

                $('#instructor_id').append(`
                    <option value="${instr.id}" ${instr.id == enrollment.instructor_id ? 'selected' : ''}>
                        ${instr.id} - ${escapeHtml(instr.name)}
                    </option>
                `);
            });

            $('#status').val(enrollment.status || 'active');

            originalData = {
                instructor_id: String(enrollment.instructor_id),
                status: enrollment.status || 'active'
            };
        },

        error: function () {

            alert('Failed to load enrollment details');

            window.history.back();
        }
    });
}

$(document).ready(function () {

    loadEnrollmentDetails();

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.integer($('#instructor_id').val(), 'Instructor ID', 1);

        } catch (error) {

            alert(error.message);

            return;
        }

        const formData = {
            csrf_token: $('input[name="csrf_token"]').val(),
            id: $('input[name="id"]').val()
        };

        if ($('#instructor_id').val() !== originalData.instructor_id) {
            formData.instructor_id = $('#instructor_id').val();
        }

        if ($('#status').val() !== originalData.status) {
            formData.status = $('#status').val();
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
                    window.history.back();
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