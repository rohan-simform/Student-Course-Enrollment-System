let courseId = new URLSearchParams(window.location.search).get('course_id');
let courseInstructorsTable = null;
let currentUserRole = null;

$(document).ready(function () {
    if (!courseId) {
        alert('Course ID is missing');
        return;
    }
    getCurrentUserInfo();
});

function getCurrentUserInfo() {
    $.ajax({
        url: APP.baseUrl + 'auth/getCurrentUser.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                currentUserRole = data.data.role;
                initializeDataTable();
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to fetch current user', error);
        }
    });
}

function initializeDataTable() {
    let columns = [
        { data: 'instructor_id' },
        { data: 'instructor_name' },
    ];

    if (currentUserRole === 'admin') {
        columns.push({
            data: 'instructor_id',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <div class="action-buttons">
                        <button class="action-btn btn-delete" onclick="removeInstructor(${data})">
                            <i class="fas fa-user-minus"></i> Remove
                        </button>
                    </div>
                `;
            }
        });
    } else {
        $('#courseInstructorsTable thead tr').html(`
            <th>Instructor ID</th>
            <th>Instructor Name</th>
        `);
    }

    courseInstructorsTable = initTable(
        'courseInstructorsTable',
        columns,
        null,
        APP.baseUrl + 'courses/getCourseInstructorsList.php?course_id=' + courseId
    );
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
            if (response.status && courseInstructorsTable) {
                courseInstructorsTable.ajax.reload();
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