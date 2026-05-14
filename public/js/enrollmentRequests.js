let currentPage = 1;
let limit = 10;
let currentUserRole = null;

function loadEnrollmentRequests(page = 1) {

    currentPage = page;

    $.ajax({
        url: APP.baseUrl + `enrollments/getEnrollmentRequestsList.php?page=${page}&limit=${limit}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            currentUserRole = data.data.role;

            renderTable(data.data.requests);

            renderPagination(data.data.pagination, loadEnrollmentRequests);
        },

        error: function (xhr, status, error) {

            console.error(error);

            alert('Failed to load enrollment requests');
        }
    });
}

function renderTable(requests) {

    const $tbody = $('#requestTableBody');

    $tbody.html('');

    if (requests.length === 0) {

        $tbody.append(`
            <tr>
                <td colspan="7">No Enrollment Requests Found</td>
            </tr>
        `);

        return;
    }

    $.each(requests, function (index, request) {

        let actionButtons = '';

        if (currentUserRole !== 'student') {

            actionButtons = `
                <div class="action-buttons">
                    <button class="action-btn btn-approve" onclick="approveRequest(${request.id})">
                        <i class="fas fa-check"></i> Approve
                    </button>

                    <button class="action-btn btn-reject" onclick="rejectRequest(${request.id})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            `;

        } else {

            actionButtons = `
                <div class="action-buttons">
                    <button class="action-btn btn-reject" onclick="withdrawRequest(${request.id})">
                        <i class="fas fa-times"></i> Withdraw
                    </button>
                </div>
            `;
        }

        $tbody.append(`
            <tr>
                <td>${request.id}</td>
                <td>${request.student_id} - ${escapeHtml(request.student_name)}</td>
                <td>${request.course_id} - ${escapeHtml(request.course_name)}</td>
                <td>${request.instructor_id} - ${escapeHtml(request.instructor_name)}</td>
                <td>${escapeHtml(request.enrolled_date)}</td>
                <td>${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</td>
                <td>${actionButtons}</td>
            </tr>
        `);
    });
}

function approveRequest(id) {

    $.ajax({
        url: APP.baseUrl + 'enrollments/approveEnrollmentRequest.php',
        type: 'POST',

        data: {
            id: id
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadEnrollmentRequests(currentPage);
            }
        }
    });
}

function rejectRequest(id) {

    $.ajax({
        url: APP.baseUrl + 'enrollments/rejectEnrollmentRequest.php',
        type: 'POST',

        data: {
            id: id
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadEnrollmentRequests(currentPage);
            }
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

        data: {
            id: id
        },

        dataType: 'json',

        success: function (response) {

            alert(response.message);

            if (response.status) {
                loadEnrollmentRequests(currentPage);
            }
        }
    });
}

$(document).ready(function () {
    loadEnrollmentRequests();
});