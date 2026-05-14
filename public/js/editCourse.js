let currentCourseId = '';
let originalData = {};

function loadCourseDetails() {

    const urlParams = new URLSearchParams(window.location.search);

    currentCourseId = urlParams.get('id');

    if (!currentCourseId) {
        alert('Course ID not provided');
        window.history.back();
        return;
    }

    $.ajax({
        url: APP.baseUrl + `courses/getCourseDetails.php?id=${currentCourseId}`,
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert(result.message);
                window.history.back();
                return;
            }

            const course = result.data;

            $('input[name="course_id"]').val(course.id);
            $('#name').val(course.name || '');
            $('#duration_weeks').val(course.duration_weeks || '');
            $('#max_seats').val(course.max_seats || '');
            $('#is_active').val(course.is_active || 'active');

            originalData = {
                name: course.name || '',
                duration_weeks: String(course.duration_weeks || ''),
                max_seats: String(course.max_seats || ''),
                is_active: course.is_active || 'active'
            };
        },

        error: function () {

            alert('Failed to load course details');

            window.history.back();
        }
    });
}

$(document).ready(function () {

    loadCourseDetails();

    $('form').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        try {

            Validator.name($('#name').val(), 'Course Name');
            Validator.integer($('#duration_weeks').val(), 'Duration Weeks', 1);
            Validator.integer($('#max_seats').val(), 'Max Seats', 1);

        } catch (error) {

            alert(error.message);

            return;
        }

        const formData = {
            csrf_token: $('input[name="csrf_token"]').val(),
            course_id: $('input[name="course_id"]').val()
        };

        if ($('#name').val() !== originalData.name) {
            formData.name = $('#name').val();
        }

        if ($('#duration_weeks').val() !== originalData.duration_weeks) {
            formData.duration_weeks = $('#duration_weeks').val();
        }

        if ($('#max_seats').val() !== originalData.max_seats) {
            formData.max_seats = $('#max_seats').val();
        }

        if ($('#is_active').val() !== originalData.is_active) {
            formData.is_active = $('#is_active').val();
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
                    window.location.href = 'listCourses.php';
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