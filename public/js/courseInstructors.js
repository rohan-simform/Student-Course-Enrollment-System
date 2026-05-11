let courseId = new URLSearchParams(window.location.search).get('course_id');
let isAdmin = false;

function loadCourseInstructors() {

    $.ajax({
        url: APP.baseUrl + `courses/getCourseInstructorsList.php?course_id=${courseId}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            isAdmin = data.data.isAdmin;

            renderTable(data.data.instructors);
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load course instructors');
        }
    });
}

function renderTable(instructors) {

    const $tbody = $('#instructorTableBody');

    $tbody.html('');

    if (instructors.length === 0) {

        const colspan = isAdmin ? 3 : 2;

        $tbody.append(`
            <tr>
                <td colspan="${colspan}">
                    No Instructor Assigned
                </td>
            </tr>
        `);

        return;
    }

    $.each(instructors, function (index, instructor) {

        let rowHtml = `
            <td>${instructor.instructor_id}</td>
            <td>${escapeHtml(instructor.instructor_name)}</td>
        `;

        if (isAdmin) {

            rowHtml += `
                <td>
                    <div class="action-buttons">
                        <button
                            class="action-btn btn-delete"
                            onclick="removeInstructor(${instructor.instructor_id})"
                        >
                            <i class="fas fa-user-minus"></i>
                            Remove
                        </button>
                    </div>
                </td>
            `;
        }

        $tbody.append(`
            <tr>
                ${rowHtml}
            </tr>
        `);
    });

    $('thead tr th:last-child').prop('hidden', !isAdmin);
}

function removeInstructor(instructorId) {

    if (!confirm('Remove assignment?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'assignedCourses/updateAssignedCourse.php',
        type: 'POST',

        data: {
            course_id: courseId,
            instructor_id: instructorId
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadCourseInstructors();
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
}

$(document).ready(function () {
    loadCourseInstructors();
});