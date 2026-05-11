let currentPage = 1;
let limit = 10;
let currentUserId = null;


function loadAdmins(page = 1) {

    currentPage = page;

    $.ajax({
        url: APP.baseUrl + `users/getAdminsList.php?page=${page}&limit=${limit}`,
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data.status) {
                alert(data.message);
                return;
            }

            renderTable(data.data.admins);

            renderPagination(data.data.pagination, loadAdmins);
        }
    });
}

function renderTable(admins) {

    const $tbody = $('#adminTableBody');

    $tbody.html('');

    $.each(admins, function (index, admin) {

        $tbody.append(`
            <tr>
                <td>${admin.id}</td>
                <td>${escapeHtml(admin.email)}</td>
                <td>${admin.status}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn btn-edit" onclick="editAdmin(${admin.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        ${admin.id != currentUserId
                            ? `
                                <button class="action-btn btn-disable" onclick="disableUser(${admin.id}, loadAdmins)">
                                    <i class="fas fa-ban"></i> Disable
                                </button>
                            `
                            : ''
                        }
                    </div>
                </td>
            </tr>
        `);
    });
}

function editAdmin(id) {
    window.location.href = `editAdmin.php?user_id=${id}`;
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

                currentUserId = data.data.user_id;

                loadAdmins();
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