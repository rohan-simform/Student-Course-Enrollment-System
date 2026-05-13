let currentUserId = null;
let adminTable = null;

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
                initializeDataTable();
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to fetch current user', error);
        }
    });
}

function initializeDataTable() {
    adminTable = initTable(
        'adminsTable',
        [
            { data: 'id' },
            { data: 'email' },
            { data: 'status' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let actions = `
                        <div class="action-buttons">
                            <button class="action-btn btn-edit" onclick="editAdmin(${row.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                    `;

                    if (row.id != currentUserId) {
                        actions += `
                            <button class="action-btn btn-disable" onclick="disableUser(${row.id}, reloadTable)">
                                <i class="fas fa-ban"></i> Disable
                            </button>
                        `;
                    }

                    actions += `</div>`;
                    return actions;
                }
            }
        ],
        null,
        APP.baseUrl + 'users/getAdminsList.php'
    );
}

function editAdmin(id) {
    window.location.href = `editAdmin.php?user_id=${id}`;
}

function reloadTable() {
    if (adminTable) {
        adminTable.ajax.reload();
    }
}
