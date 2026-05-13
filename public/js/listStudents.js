let studentTable = null;

$(document).ready(function () {
    initializeDataTable();
});

function initializeDataTable() {
    studentTable = initTable(
        'studentsTable',
        [
            { data: 'id' },
            { data: 'email' },
            { data: 'name' },
            { data: 'phone' },
            { data: 'enrolled_on' },
            { data: 'status' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="action-buttons">
                            <button class="action-btn btn-edit" onclick="editStudent(${row.id})">
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
        APP.baseUrl + 'users/getStudentsList.php'
    );
}

function editStudent(id) {
    window.location.href = `editStudent.php?user_id=${id}`;
}

function reloadTable() {
    if (studentTable) {
        studentTable.ajax.reload();
    }
}