let coursesTable = null;
let currentUserRole = null;

$(document).ready(function () {
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
    let columns;

    if (currentUserRole === 'student') {
        $('#coursesTable thead tr').html(`
            <th>Course Name</th>
            <th>Instructor</th>
            <th>Duration</th>
            <th>Enrolled Date</th>
            <th>Status</th>
            <th>Action</th>
        `);

        columns = [
            { data: 'course_name' },
            { data: 'instructor_name' },
            { data: 'duration_weeks' },
            { data: 'enrolled_date' },
            { data: 'enrollment_status' },
            {
                data: 'course_id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let actions = `
                        <div class="action-buttons">
                            <button class="action-btn btn-view" onclick="viewCourseDetails(${row.course_id}, ${row.enrollment_id})">
                                <i class="fas fa-eye"></i> Details
                            </button>
                    `;

                    if (row.enrollment_status === 'requested') {
                        actions += `
                            <button class="action-btn btn-reject" onclick="withdrawRequest(${row.enrollment_id})">
                                <i class="fas fa-times"></i> Withdraw
                            </button>
                        `;
                    }

                    actions += `</div>`;
                    return actions;
                }
            }
        ];
    } else {
        $('#coursesTable thead tr').html(`
            <th>ID</th>
            <th>Name</th>
            <th>Duration</th>
            <th>Available Seats</th>
            <th>Max Seats</th>
            <th>Status</th>
            <th>Action</th>
        `);

        columns = [
            { data: 'course_id' },
            { data: 'course_name' },
            { data: 'duration_weeks' },
            { data: 'available_seats' },
            { data: 'max_seats' },
            { data: 'course_status' },
            {
                data: 'course_id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let actions = `
                        <div class="action-buttons">
                            <button class="action-btn btn-edit" onclick="editCourse(${row.course_id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                    `;

                    if (currentUserRole === 'admin') {
                        actions += `
                            <button class="action-btn btn-delete" onclick="deleteCourse(${row.course_id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        `;
                    }

                    actions += `
                            <button class="action-btn btn-view" onclick="viewCourseStudents(${row.course_id})">
                                <i class="fas fa-users"></i> Students
                            </button>
                            <button class="action-btn btn-view" onclick="viewCourseInstructors(${row.course_id})">
                                <i class="fas fa-chalkboard-user"></i> Instructors
                            </button>
                        </div>
                    `;
                    return actions;
                }
            }
        ];
    }

    coursesTable = initTable(
        'coursesTable',
        columns,
        null,
        APP.baseUrl + 'courses/getCoursesList.php'
    );
}

function viewCourseDetails(courseId, enrollmentId) {
    window.location.href = `courseDetails.php?course_id=${courseId}&enrollment_id=${enrollmentId}`;
}

function withdrawRequest(enrollmentId) {
    if (!confirm('Withdraw enrollment request?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'enrollments/withdrawEnrollmentRequest.php',
        type: 'POST',
        data: { id: enrollmentId },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && coursesTable) {
                coursesTable.ajax.reload();
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

function editCourse(id) {
    window.location.href = `editCourse.php?id=${id}`;
}

function deleteCourse(courseId) {
    if (!confirm('Deactivate this course?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'courses/deleteCourse.php',
        type: 'POST',
        data: { course_id: courseId },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && coursesTable) {
                coursesTable.ajax.reload();
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

function viewCourseStudents(courseId) {
    window.location.href = `courseStudents.php?course_id=${courseId}`;
}

function viewCourseInstructors(courseId) {
    window.location.href = `courseInstructors.php?course_id=${courseId}`;
}