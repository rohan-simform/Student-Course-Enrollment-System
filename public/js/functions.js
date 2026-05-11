function renderPagination(pagination, onPageChange) {
    const container = document.getElementById("pagination");
    container.innerHTML = "";

    if (!pagination || pagination.total_pages <= 1) return;

    if (pagination.page > 1) {
        container.innerHTML += `<button class="pagination-btn" data-page="${pagination.page - 1}">◀ Prev</button>`;
    } else {
        container.innerHTML += `<button class="pagination-btn" disabled>◀ Prev</button>`;
    }

    for (let i = 1; i <= pagination.total_pages; i++) {
        const isActive = i === pagination.page ? 'active' : '';
        container.innerHTML += `<button class="pagination-btn ${isActive}" data-page="${i}">${i}</button>`;
    }

    if (pagination.page < pagination.total_pages) {
        container.innerHTML += `<button class="pagination-btn" data-page="${pagination.page + 1}">Next ▶</button>`;
    } else {
        container.innerHTML += `<button class="pagination-btn" disabled>Next ▶</button>`;
    }

    // attach events (IMPORTANT)
    container.querySelectorAll("button:not(:disabled)").forEach(btn => {
        btn.addEventListener("click", () => {
            const page = parseInt(btn.getAttribute("data-page"));
            onPageChange(page);
        });
    });
}
window.renderPagination = renderPagination;

function escapeHtml(str) {
    return str.replace(/[&<>"']/g, function (m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[m];
    });
}
window.escapeHtml = escapeHtml;


async function disableUser(id, reloadFn) {
    if (!confirm("Disable this user?")) return;

    try {
        const res = await fetch(APP.baseUrl + 'users/updateUser.php', {
            method: 'POST',
            body: new URLSearchParams({
                user_id: id,
                status: 'disabled',
                csrf_token: APP.csrfToken
            })
        });

        const text = await res.text();

        reloadFn();

    } catch (err) {
        alert("Failed to disable user");
        console.error(err);
    }
}
window.disableUser = disableUser;

async function getCurrentUserInfo() {
    try {
        const res = await fetch(APP.baseUrl + 'auth/getCurrentUser.php');
        const data = await res.json();

        if (data.status) {
            // User info retrieved successfully
            // Dashboard navigation is now in the sidebar
        }
    } catch (err) {
        console.error("Failed to fetch current user", err);
    }
}
window.getCurrentUserInfo = getCurrentUserInfo;
