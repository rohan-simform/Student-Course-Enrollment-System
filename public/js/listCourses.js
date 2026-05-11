let currentPage = 1;
let limit = 10;
let currentUserRole = null;

function loadCourses(page = 1) {

    currentPage = page;

    $.ajax({
        url: APP.baseUrl + `courses/getCoursesList.php?page=${page}&limit=${limit}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            renderTable(data.data.courses);
            renderPagination(data.data.pagination, loadCourses);
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load courses');
        }
    });
}

function updateTableHeaders() {

    if (currentUserRole === 'student') {

        $('#tableHead').html(`
            <tr>
                <th>Course</th>
                <th>Instructor</th>
                <th>Duration</th>
                <th>Enrolled Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        `);

        $('#courseTitle').text('My Courses');

    } else {

        $('#tableHead').html(`
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Duration</th>
                <th>Available Seats</th>
                <th>Max Seats</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        `);

        $('#courseTitle').text('Courses');
    }
}

function renderTable(courses) {

    const $tbody = $('#courseTableBody');

    $tbody.html('');

    if (currentUserRole === 'student') {
        renderStudentView(courses, $tbody);
    } else {
        renderAdminInstructorView(courses, $tbody);
    }
}

function renderStudentView(courses, $tbody) {

    $.each(courses, function (index, course) {

        let actionButtons = `
            <div class="action-buttons">
                <button class="action-btn btn-view" onclick="viewCourseDetails(${course.course_id})">
                    <i class="fas fa-eye"></i> Details
                </button>
        `;

        if (course.enrollment_status === 'requested') {

            actionButtons += `
                <button class="action-btn btn-reject" onclick="withdrawRequest(${course.enrollment_id})">
                    <i class="fas fa-times"></i> Withdraw
                </button>
            `;
        }

        actionButtons += `</div>`;

        $tbody.append(`
            <tr>
                <td>${course.course_id} - ${escapeHtml(course.course_name)}</td>
                <td>${course.instructor_id} - ${escapeHtml(course.instructor_name)}</td>
                <td>${course.duration_weeks} Weeks</td>
                <td>${escapeHtml(course.enrolled_date)}</td>
                <td>${course.enrollment_status.charAt(0).toUpperCase() + course.enrollment_status.slice(1)}</td>
                <td>${actionButtons}</td>
            </tr>
        `);
    });
}

function renderAdminInstructorView(courses, $tbody) {

    $.each(courses, function (index, course) {

        $tbody.append(`
            <tr>
                <td>${course.course_id}</td>
                <td>${escapeHtml(course.course_name)}</td>
                <td>${course.duration_weeks} Weeks</td>
                <td>${course.available_seats}</td>
                <td>${course.max_seats}</td>
                <td>${course.course_status}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn btn-edit" onclick="editCourse(${course.course_id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        ${currentUserRole === 'admin'
                            ? `
                                <button class="action-btn btn-delete" onclick="deleteCourse(${course.course_id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            `
                            : ''
                        }

                        <button class="action-btn btn-view" onclick="viewCourseStudents(${course.course_id})">
                            <i class="fas fa-users"></i> Students
                        </button>

                        <button class="action-btn btn-view" onclick="viewCourseInstructors(${course.course_id})">
                            <i class="fas fa-chalkboard-user"></i> Instructors
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

function viewCourseDetails(courseId) {
    window.location.href = `courseDetails.php?course_id=${courseId}`;
}

function withdrawRequest(enrollmentId) {

    if (!confirm('Withdraw enrollment request?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'enrollments/withdrawEnrollmentRequest.php',
        type: 'POST',

        data: {
            id: enrollmentId
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadCourses(currentPage);
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

        data: {
            course_id: courseId
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadCourses(currentPage);
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

                updateTableHeaders();

                // LOAD AFTER ROLE IS READY
                loadCourses();
            }
        },

        error: function (xhr, status, error) {

            console.error(
                'Failed to fetch current user',
                error
            );
        }
    });
}