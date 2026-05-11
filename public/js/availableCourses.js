let currentPage = 1;
let limit = 10;

function loadAvailableCourses(page = 1) {

    currentPage = page;

    $.ajax({
        url: APP.baseUrl + `courses/getAvailableCoursesList.php?page=${page}&limit=${limit}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            renderTable(data.data.courses);

            renderPagination(
                data.data.pagination,
                loadAvailableCourses
            );
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load available courses');
        }
    });
}

function renderTable(courses) {

    const $tbody = $('#courseTableBody');

    $tbody.html('');

    if (courses.length === 0) {

        $tbody.append(`
            <tr>
                <td colspan="6">
                    No Available Courses Found
                </td>
            </tr>
        `);

        return;
    }

    $.each(courses, function (index, course) {

        $tbody.append(`
            <tr>
                <td>
                    ${course.course_id}
                    -
                    ${escapeHtml(course.course_name)}
                </td>

                <td>
                    ${course.instructor_id}
                    -
                    ${escapeHtml(course.instructor_name)}
                </td>

                <td>
                    ${course.duration_weeks} Weeks
                </td>

                <td>${course.available_seats}</td>

                <td>${course.max_seats}</td>

                <td>
                    <div class="action-buttons">

                        <button
                            class="action-btn btn-view"
                            onclick="viewCourseDetails(${course.course_id})"
                        >
                            <i class="fas fa-eye"></i>
                            Details
                        </button>

                        <button
                            class="action-btn btn-approve"
                            onclick="requestToJoin(${course.course_id}, ${course.instructor_id})"
                        >
                            <i class="fas fa-user-plus"></i>
                            Join
                        </button>

                    </div>
                </td>
            </tr>
        `);
    });
}

function viewCourseDetails(courseId) {

    window.location.href =
        `courseDetails.php?course_id=${courseId}&source=available`;
}

function requestToJoin(courseId, instructorId) {

    $.ajax({
        url: APP.baseUrl + 'enrollments/requestEnrollment.php',
        type: 'POST',

        data: {
            course_id: courseId,
            instructor_id: instructorId
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadAvailableCourses(currentPage);
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
    loadAvailableCourses();
});