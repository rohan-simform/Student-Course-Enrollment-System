let courseId = new URLSearchParams(window.location.search).get('course_id');
let courseStudentsTable = null;

$(document).ready(function () {
    if (!courseId) {
        alert('Course ID is missing');
        return;
    }
    initializeDataTable();
});

function initializeDataTable() {
    const columns = [
        { data: 'student_name' },
        { data: 'instructor_name' },
        { data: 'enrolled_date' },
        { data: 'enrollment_status' },
        {
            data: 'enrollment_id',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                let actions = `
                    <div class="action-buttons">
                        <button class="action-btn btn-edit" onclick="editEnrollment(${data})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                `;

                if (row.enrollment_status === 'requested') {
                    actions += `
                        <button class="action-btn btn-approve" onclick="approveRequest(${data})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="action-btn btn-reject" onclick="rejectRequest(${data})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    `;
                }

                if (row.enrollment_status === 'active') {
                    actions += `
                        <button class="action-btn btn-disable" onclick="cancelEnrollment(${data})">
                            <i class="fas fa-ban"></i> Cancel
                        </button>
                        <button class="action-btn btn-view" onclick="completeEnrollment(${data})">
                            <i class="fas fa-check-double"></i> Complete
                        </button>
                    `;
                }

                actions += `</div>`;
                return actions;
            }
        }
    ];

    courseStudentsTable = initTable(
        'courseStudentsTable',
        columns,
        null,
        APP.baseUrl + 'courses/getCourseStudentsList.php?course_id=' + courseId
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
            if (response.status && courseStudentsTable) {
                courseStudentsTable.ajax.reload();
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