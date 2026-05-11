function loadAssignedCourses() {

    $.ajax({
        url: APP.baseUrl + 'assignedCourses/getAssignedCoursesList.php',
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            renderTable(data.data.courses);
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load assigned courses');
        }
    });
}

function renderTable(courses) {

    const $tbody = $('#courseTableBody');

    $tbody.html('');

    if (courses.length === 0) {

        $tbody.append(`
            <tr>
                <td colspan="5">No Assigned Courses</td>
            </tr>
        `);

        return;
    }

    $.each(courses, function (index, course) {

        $tbody.append(`
            <tr>
                <td>${course.course_id}</td>
                <td>${escapeHtml(course.course_name)}</td>
                <td>${course.instructor_id}</td>
                <td>${escapeHtml(course.instructor_name)}</td>
                <td>
                    <div class="action-buttons">
                        <button
                            class="action-btn btn-delete"
                            onclick="removeCourse(
                                ${course.course_id},
                                ${course.instructor_id}
                            )"
                        >
                            <i class="fas fa-trash"></i>
                            Remove
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

function removeCourse(courseId, instructorId) {

    if (!confirm('Remove assignment?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'assignedCourses/updateAssignedCourse.php',
        type: 'POST',

        data: {
            action: 'remove',
            course_id: courseId,
            instructor_id: instructorId
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadAssignedCourses();
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
    loadAssignedCourses();
});