let instructorTable = null;

$(document).ready(function () {
    initializeDataTable();
});

function initializeDataTable() {
    instructorTable = initTable(
        'instructorsTable',
        [
            { data: 'id' },
            { data: 'email' },
            { data: 'name' },
            { data: 'phone' },
            { data: 'salary' },
            { data: 'status' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="action-buttons">
                            <button class="action-btn btn-edit" onclick="editInstructor(${row.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn btn-disable" onclick="disableUser(${row.id}, reloadTable)">
                                <i class="fas fa-ban"></i> Disable
                            </button>
                        </div>
                    `;
                }
            }
        ],
        null,
        APP.baseUrl + 'users/getInstructorsList.php'
    );
}

function editInstructor(id) {
    window.location.href = `editInstructor.php?user_id=${id}`;
}

function reloadTable() {
    if (instructorTable) {
        instructorTable.ajax.reload();
    }
}