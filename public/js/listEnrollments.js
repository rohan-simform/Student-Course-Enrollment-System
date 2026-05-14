let currentPage = 1;
let limit = 10;
let currentUserRole = null;

function loadEnrollments(page = 1) {

    currentPage = page;

    $.ajax({
        url: APP.baseUrl + `enrollments/getEnrollmentsList.php?page=${page}&limit=${limit}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            currentUserRole = data.data.role;

            renderTable(data.data.enrollments);

            renderPagination(data.data.pagination, loadEnrollments);
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load enrollments');
        }
    });
}

function renderTable(enrollments) {

    const $tbody = $('#enrollmentTableBody');

    $tbody.html('');

    $.each(enrollments, function (index, row) {

        let actionButtons = '';

        if (currentUserRole !== 'student') {

            actionButtons += `
                <button class="action-btn btn-edit" onclick="editEnrollment(${row.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
            `;

            if (row.status === 'requested') {

                actionButtons += `
                    <button class="action-btn btn-approve" onclick="approveRequest(${row.id})">
                        <i class="fas fa-check"></i> Approve
                    </button>

                    <button class="action-btn btn-reject" onclick="rejectRequest(${row.id})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                `;
            }

            if (row.status === 'active') {

                actionButtons += `
                    <button class="action-btn btn-reject" onclick="cancelEnrollment(${row.id})">
                        <i class="fas fa-ban"></i> Cancel
                    </button>

                    <button class="action-btn btn-approve" onclick="completeEnrollment(${row.id})">
                        <i class="fas fa-check-circle"></i> Complete
                    </button>
                `;
            }

            actionButtons = `<div class="action-buttons">${actionButtons}</div>`;
        }

        $tbody.append(`
            <tr>
                <td>${row.id}</td>
                <td>${row.student_id} - ${escapeHtml(row.student_name)}</td>
                <td>${row.course_id} - ${escapeHtml(row.course_name)}</td>
                <td>${row.instructor_id} - ${escapeHtml(row.instructor_name)}</td>
                <td>${row.enrolled_date}</td>
                <td>${row.status.charAt(0).toUpperCase() + row.status.slice(1)}</td>
                ${currentUserRole !== 'student' ? `<td>${actionButtons}</td>` : ''}
            </tr>
        `);
    });
}

function editEnrollment(id) {
    window.location.href = `editEnrollment.php?id=${id}`;
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
                loadEnrollments(currentPage);
            }
        }
    });
}

$(document).ready(function () {
    loadEnrollments();
});