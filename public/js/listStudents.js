let currentPage = 1;
let limit = 10;
let dashboardPath = null;

async function getCurrentUserInfo() {
    try {
        const data = await $.ajax({
            url: APP.baseUrl + 'auth/getCurrentUser.php',
            type: 'GET',
            dataType: 'json'
        });

        if (data.status) {
            dashboardPath = data.data.dashboard;
            // Dashboard link is now in the sidebar
        }
    } catch (err) {
        console.error("Failed to fetch current user", err);
    }
}

async function loadStudents(page = 1) {
    currentPage = page;

    try {
        const data = await $.ajax({
            url: APP.baseUrl + `users/getStudentsList.php?page=${page}&limit=${limit}`,
            type: 'GET',
            dataType: 'json'
        });

        if (!data.status) {
            alert(data.message);
            return;
        }

        renderTable(data.data.students);
        renderPagination(data.data.pagination, loadStudents);
    } catch (err) {
        alert("Failed to load students");
        console.error(err);
    }
}

function renderTable(students) {
    const tbody = document.getElementById("studentTableBody");
    tbody.innerHTML = "";

    students.forEach(s => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${s.id}</td>
            <td>${escapeHtml(s.email)}</td>
            <td>${escapeHtml(s.name)}</td>
            <td>${escapeHtml(s.phone)}</td>
            <td>${s.enrolled_on}</td>
            <td>${s.status}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn btn-edit" onclick="editStudent(${s.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="action-btn btn-disable" onclick="disableUser(${s.id}, () => loadStudents(currentPage))"><i class="fas fa-ban"></i> Disable</button>
                </div>
            </td>
        `;

        tbody.appendChild(row);
    });
}

function editStudent(id) {
    window.location.href = `editStudent.php?user_id=${id}`;
}

$(document).ready(function () {
    getCurrentUserInfo();
    loadStudents();
});