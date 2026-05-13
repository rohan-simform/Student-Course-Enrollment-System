let enrollmentsTable = null;
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
    let columns = [
        { data: 'id' },
        { data: 'student_name' },
        { data: 'course_name' },
        { data: 'instructor_name' },
        { data: 'enrolled_date' },
        { data: 'status' }
    ];

    // Only add Action column for non-students
    if (currentUserRole !== 'student') {
        columns.push({
            data: 'id',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                let actions = `
                    <div class="action-buttons">
                        <button class="action-btn btn-edit" onclick="editEnrollment(${data})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                `;

                if (row.status === 'requested') {
                    actions += `
                        <button class="action-btn btn-approve" onclick="approveRequest(${data})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="action-btn btn-reject" onclick="rejectRequest(${data})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    `;
                }

                if (row.status === 'active') {
                    actions += `
                        <button class="action-btn btn-disable" onclick="cancelEnrollment(${data})">
                            <i class="fas fa-ban"></i> Cancel
                        </button>
                        <button class="action-btn btn-approve" onclick="completeEnrollment(${data})">
                            <i class="fas fa-check-circle"></i> Complete
                        </button>
                    `;
                }

                actions += `</div>`;
                return actions;
            }
        });

        // Update table header for non-students
        $('#enrollmentsTable thead tr').html(`
            <th>ID</th>
            <th>Student</th>
            <th>Course</th>
            <th>Instructor</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        `);
    } else {
        // Update table header for students (no Action column)
        $('#enrollmentsTable thead tr').html(`
            <th>ID</th>
            <th>Student</th>
            <th>Course</th>
            <th>Instructor</th>
            <th>Date</th>
            <th>Status</th>
        `);
    }

    enrollmentsTable = initTable(
        'enrollmentsTable',
        columns,
        null,
        APP.baseUrl + 'enrollments/getEnrollmentsList.php'
    );
}

function editEnrollment(id) {
    window.location.href = `editEnrollment.php?id=${id}`;
}

function enrollmentAction(url, id) {
    $.ajax({
        url: APP.baseUrl + 'enrollments/' + url,
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && enrollmentsTable) {
                enrollmentsTable.ajax.reload();
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