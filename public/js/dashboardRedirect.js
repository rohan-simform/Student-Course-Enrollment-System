async function loadDashboard() {
    try {
        const res = await fetch(APP.baseUrl + 'auth/dashboardPath.php', {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await res.json();

        if (data.status && data.data && data.data.dashboard) {
            window.location.href = data.data.dashboard;
            return data.data.dashboard;
        }

        if (data.message) {
            alert(data.message);
        }
    } catch (err) {
        console.error('Dashboard fetch failed', err);
        alert('Unable to load dashboard');
    }

    return null;
}

window.loadDashboard = loadDashboard;