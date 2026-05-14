let enrollmentRequestsTable = null;
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

    // Add Action column with role-specific buttons
    columns.push({
        data: 'id',
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
            let actions = `<div class="action-buttons">`;

            if (currentUserRole === 'student') {
                // Students can withdraw their requests
                actions += `
                    <button class="action-btn btn-reject" onclick="withdrawRequest(${data})">
                        <i class="fas fa-times"></i> Withdraw
                    </button>
                `;
            } else {
                // Admins and instructors can approve/reject
                actions += `
                    <button class="action-btn btn-approve" onclick="approveRequest(${data})">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="action-btn btn-reject" onclick="rejectRequest(${data})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                `;
            }

            actions += `</div>`;
            return actions;
        }
    });

    // Update table headers
    $('#enrollmentRequestsTable thead tr').html(`
        <th>ID</th>
        <th>Student</th>
        <th>Course</th>
        <th>Instructor</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
    `);

    enrollmentRequestsTable = initTable(
        'enrollmentRequestsTable',
        columns,
        null,
        APP.baseUrl + 'enrollments/getEnrollmentRequestsList.php'
    );
}

function approveRequest(id) {
    $.ajax({
        url: APP.baseUrl + 'enrollments/approveEnrollmentRequest.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && enrollmentRequestsTable) {
                enrollmentRequestsTable.ajax.reload();
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

function rejectRequest(id) {
    $.ajax({
        url: APP.baseUrl + 'enrollments/rejectEnrollmentRequest.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && enrollmentRequestsTable) {
                enrollmentRequestsTable.ajax.reload();
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

function withdrawRequest(id) {
    if (!confirm('Withdraw enrollment request?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'enrollments/withdrawEnrollmentRequest.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && enrollmentRequestsTable) {
                enrollmentRequestsTable.ajax.reload();
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