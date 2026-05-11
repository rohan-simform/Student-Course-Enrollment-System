let currentPage = 1;
let limit = 10;
let dashboardPath = null;

async function getCurrentUserInfo() {
    try {
        const res = await fetch(APP.baseUrl + 'auth/getCurrentUser.php');
        const data = await res.json();

        if (data.status) {
            dashboardPath = data.data.dashboard;
            // Dashboard link is now in the sidebar
        }
    } catch (err) {
        console.error("Failed to fetch current user", err);
    }
}

async function loadInstructors(page = 1) {
    currentPage = page;

    try {
        const res = await fetch(APP.baseUrl + `users/getInstructorsList.php?page=${page}&limit=${limit}`);
        const data = await res.json();

        if (!data.status) {
            alert(data.message);
            return;
        }

        renderTable(data.data.instructors);
        renderPagination(data.data.pagination, loadInstructors);
    } catch (err) {
        alert("Failed to load instructors");
        console.error(err);
    }
}

function renderTable(instructors) {
    const tbody = document.getElementById("instructorTableBody");
    tbody.innerHTML = "";

    instructors.forEach(i => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${i.id}</td>
            <td>${escapeHtml(i.email)}</td>
            <td>${escapeHtml(i.name)}</td>
            <td>${escapeHtml(i.phone)}</td>
            <td>${i.salary}</td>
            <td>${i.status}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn btn-edit" onclick="editInstructor(${i.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="action-btn btn-disable" onclick="disableUser(${i.id}, () => loadInstructors(currentPage))"><i class="fas fa-ban"></i> Disable</button>
                </div>
            </td>
        `;

        tbody.appendChild(row);
    });
}

function editInstructor(id) {
    window.location.href = `editInstructor.php?user_id=${id}`;
}

getCurrentUserInfo();
loadInstructors();