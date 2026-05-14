let courseId = new URLSearchParams(window.location.search).get('course_id');
let currentPage = 1;
let limit = 10;

function loadCourseStudents(page = 1) {

    currentPage = page;

    $.ajax({
        url: APP.baseUrl + `courses/getCourseStudentsList.php?course_id=${courseId}&page=${page}&limit=${limit}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            renderTable(data.data.students);

            renderPagination(
                data.data.pagination,
                loadCourseStudents
            );
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load course students');
        }
    });
}

function renderTable(students) {

    const $tbody = $('#studentTableBody');

    $tbody.html('');

    if (students.length === 0) {

        $tbody.append(`
            <tr>
                <td colspan="6">
                    No Students Enrolled
                </td>
            </tr>
        `);

        return;
    }

    $.each(students, function (index, student) {

        let actionButtons = `
            <button
                class="action-btn btn-edit"
                onclick="editEnrollment(${student.id})"
            >
                <i class="fas fa-edit"></i>
                Edit
            </button>
        `;

        if (student.status === 'requested') {

            actionButtons += `
                <button
                    class="action-btn btn-approve"
                    onclick="approveRequest(${student.id})"
                >
                    <i class="fas fa-check"></i>
                    Approve
                </button>

                <button
                    class="action-btn btn-reject"
                    onclick="rejectRequest(${student.id})"
                >
                    <i class="fas fa-times"></i>
                    Reject
                </button>
            `;
        }

        if (student.status === 'active') {

            actionButtons += `
                <button
                    class="action-btn btn-disable"
                    onclick="cancelEnrollment(${student.id})"
                >
                    <i class="fas fa-ban"></i>
                    Cancel
                </button>

                <button
                    class="action-btn btn-view"
                    onclick="completeEnrollment(${student.id})"
                >
                    <i class="fas fa-check-double"></i>
                    Complete
                </button>
            `;
        }

        $tbody.append(`
            <tr>
                <td>${student.id}</td>

                <td>
                    ${student.student_id}
                    -
                    ${escapeHtml(student.student_name)}
                </td>

                <td>
                    ${student.instructor_id}
                    -
                    ${escapeHtml(student.instructor_name)}
                </td>

                <td>${student.enrolled_date}</td>

                <td>${student.status}</td>

                <td>
                    <div class="action-buttons">
                        ${actionButtons}
                    </div>
                </td>
            </tr>
        `);
    });
}

function editEnrollment(id) {
    window.location.href = `editEnrollment.php?id=${id}`;
}

function enrollmentAction(url, id) {

    $.ajax({
        url: APP.baseUrl + 'enrollments/' + url,
        type: 'POST',

        data: {
            id: id
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadCourseStudents(currentPage);
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

function approveRequest(id) {
    enrollmentAction('approveEnrollmentRequest.php', id);
}

function rejectRequest(id) {
    enrollmentAction('rejectEnrollmentRequest.php', id);
}

function cancelEnrollment(id) {
    enrollmentAction('cancelEnrollment.php', id);
}

function completeEnrollment(id) {
    enrollmentAction('completeEnrollment.php', id);
}

$(document).ready(function () {
    loadCourseStudents();
});