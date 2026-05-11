let courseId = new URLSearchParams(window.location.search).get('course_id');

async function loadCourseDetails() {
    if (!courseId) {
        alert('Invalid course ID');
        window.history.back();
        return;
    }

    try {
        const res = await fetch(APP.baseUrl + `courses/getCourseDetails.php?course_id=${courseId}`);
        const data = await res.json();

        if (!data.status) {
            alert(data.message);
            window.history.back();
            return;
        }

        const course = data.data.course ?? data.data;
        const backPath = data.data.backPath ?? 'listCourses.php';

        document.getElementById("backBtn").href = backPath;
        renderCourseDetails(course);
        console.log(data);

    } catch (err) {
        alert("Failed to load course details");
        console.error(err);
        window.history.back();
    }
}

function renderCourseDetails(course) {
    const container = document.getElementById("detailsContainer");
    const rows = [
        ['Course ID', course.course_id],
        ['Course Name', escapeHtml(course.course_name)],
        ['Instructor', `${course.instructor_id} - ${escapeHtml(course.instructor_name)}`],
        ['Duration', `${course.duration_weeks} Weeks`],
        ['Course Status', formatStatusBadge(course.course_status)]
    ];

    if (course.available_seats !== undefined) {
        rows.push(['Available Seats', course.available_seats]);
        rows.push(['Max Seats', course.max_seats]);
    }

    if (course.enrollment_status !== undefined) {
        rows.push(['Enrollment Status', formatStatusBadge(course.enrollment_status)]);
        rows.push(['Enrolled Date', escapeHtml(course.enrolled_date ?? '-')]);
    }

    container.innerHTML = rows.map(([label, value]) => `
        <tr>
            <td><strong>${label}</strong></td>
            <td>${value}</td>
        </tr>
    `).join('');
}

function formatStatusBadge(status) {
    const normalizedStatus = String(status ?? '').toLowerCase();
    const label = normalizedStatus
        ? normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1)
        : '-';

    const statusClassMap = {
        active: 'badge-active',
        inactive: 'badge-inactive',
        requested: 'badge-pending',
        pending: 'badge-pending',
        completed: 'badge-completed',
        enrolled: 'badge-enrolled'
    };

    const badgeClass = statusClassMap[normalizedStatus];
    return badgeClass ? `<span class="badge ${badgeClass}">${label}</span>` : escapeHtml(label);
}

loadCourseDetails();
