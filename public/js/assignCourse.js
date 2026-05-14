let currentUserId = '';
let currentUserRole = '';

function loadUserInfo() {

    return $.ajax({
        url: APP.baseUrl + 'auth/getCurrentUser.php',
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (result.status) {
                currentUserId = result.data.user_id;
                currentUserRole = result.data.role;
            }
        },

        error: function (xhr, status, error) {
            console.error('Error loading user info:', error);
        }
    });
}

function loadCourses() {

    return $.ajax({
        url: APP.baseUrl + 'assignedCourses/getAssignCourseData.php',
        type: 'GET',
        dataType: 'json',

        success: function (result) {

            if (!result.status) {
                alert('Failed to load courses: ' + (result.message || 'Unknown error'));
                return;
            }

            const courses = result.data;
            const $courseSelect = $('select[name="course_id"]');

            $courseSelect.html('<option value="">Select Course</option>');

            $.each(courses, function (index, course) {

                $courseSelect.append(
                    $('<option>', {
                        value: course.id,
                        text: course.id + ' - ' + escapeHtml(course.name)
                    })
                );
            });
        },

        error: function (xhr, status, error) {
            console.error('Error loading courses:', error);
            alert('Failed to load courses');
        }
    });
}

function loadInstructorsForCourse(courseId) {

    const $instructorSelect = $('select[name="instructor_id"]');

    if (!courseId) {
        populateInstructorDropdown([]);
        $instructorSelect.prop('disabled', true);
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'assignedCourses/getInstructorsNotAssigned.php',
        type: 'GET',
        data: {
            course_id: courseId
        },
        dataType: 'json',

        success: function (result) {

            console.log(result);

            if (!result.status) {
                alert('Failed to load available instructors');

                populateInstructorDropdown([]);

                $instructorSelect.prop('disabled', true);

                return;
            }
            // If no instructors available
            if (!result.data || result.data.length === 0) {

                populateInstructorDropdown([]);

                $instructorSelect
                    .html('<option value="">All instructors are already assigned to this course</option>')
                    .prop('disabled', true);

                return;
            }

            populateInstructorDropdown(result.data);
            $instructorSelect.prop('disabled', false);

        },

        error: function (xhr, status, error) {

            console.error('Error loading instructors:', error);

            populateInstructorDropdown([]);

            $instructorSelect.prop('disabled', true);
        }
    });
}

function populateInstructorDropdown(instructors) {

    const $instructorSelect = $('select[name="instructor_id"]');

    $instructorSelect.html('<option value="">Select Instructor</option>');

    $.each(instructors, function (index, instr) {

        $instructorSelect.append(
            $('<option>', {
                value: instr.id,
                text: instr.id + ' - ' + escapeHtml(instr.name)
            })
        );
    });
}

$(document).ready(function () {

    loadUserInfo();

    loadCourses();

    $('#instructor_id').prop('disabled', true);

    $('#course_id').on('change', function () {
        loadInstructorsForCourse($(this).val());
    });

    $('#courseForm').on('submit', function (e) {

        e.preventDefault();
        try {

            Validator.integer($('#course_id').val());
            Validator.integer($('#instructor_id').val());

        } catch (error) {

            alert(error.message);
            return;
        }

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',

            success: function (response) {

                alert(response.message);

                if (response.status) {
                    window.location.href = 'listAssignedCourse.php';
                }
            },

            error: function (xhr) {

                let message = 'Something went wrong';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            }
        });
    });
});