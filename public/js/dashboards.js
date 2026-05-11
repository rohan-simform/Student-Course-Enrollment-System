/**
 * Dashboard Data Management
 * Fetches and displays role-based dashboard metrics
 */

let dashboardData = null;

/**
 * Escape HTML special characters for safe display
 */
function escapeHtml(str) {
    if (!str) return '';
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

/**
 * Fetch dashboard data from server
 */
async function loadDashboardData() {
    try {
        const res = await fetch(APP.baseUrl + 'dashboards/getDashboardData.php');
        const response = await res.json();

        if (!response.status) {
            console.error('Failed to load dashboard data:', response.message);
            return null;
        }

        dashboardData = response.data;
        
        // Render based on role
        if (dashboardData.role === 'admin') {
            renderAdminDashboard(dashboardData);
        } else if (dashboardData.role === 'instructor') {
            renderInstructorDashboard(dashboardData);
        } else if (dashboardData.role === 'student') {
            renderStudentDashboard(dashboardData);
        }

        return dashboardData;

    } catch (err) {
        console.error('Error fetching dashboard data:', err);
        return null;
    }
}

/**
 * Render Admin Dashboard Stats
 */
function renderAdminDashboard(data) {
    // Set user name in welcome message
    const welcomeHeading = document.querySelector('.welcome-section h1');
    if (welcomeHeading) {
        welcomeHeading.innerHTML = '<i class="fas fa-dashboard"></i> Welcome Back, ' + escapeHtml(data.userName || 'Admin') + '!';
    }

    // Request count
    const requestCountEl = document.getElementById('admin-request-count');
    if (requestCountEl) {
        requestCountEl.textContent = data.requestCount || 0;
        if (data.requestCount > 0) {
            requestCountEl.parentElement.classList.add('has-badge');
        }
    }

    // Total students
    const totalStudentsEl = document.getElementById('admin-total-students');
    if (totalStudentsEl) {
        totalStudentsEl.textContent = data.totalStudents || 0;
    }

    // Active courses
    const activeCoursesEl = document.getElementById('admin-active-courses');
    if (activeCoursesEl) {
        activeCoursesEl.textContent = data.activeCourses || 0;
    }

    // Instructors
    const instructorsEl = document.getElementById('admin-instructors');
    if (instructorsEl) {
        instructorsEl.textContent = data.instructors || 0;
    }

    // Pending requests stat card
    const pendingRequestsEl = document.getElementById('admin-pending-requests');
    if (pendingRequestsEl) {
        pendingRequestsEl.textContent = data.pendingRequests || 0;
    }

    // Pending requests badge
    const pendingBadgeEl = document.querySelector('.badge.bg-danger');
    if (pendingBadgeEl) {
        pendingBadgeEl.textContent = data.pendingRequests || 0;
    }

    // Update stats section
    const statsBoxes = document.querySelectorAll('.admin-stats-section .dashboard-card:nth-child(1) .card-body, .admin-stats-section .dashboard-card:nth-child(2) .card-body');
    if (statsBoxes.length > 0) {
        // Find and update the stat boxes
        updateStatBoxes(data, 'admin');
    }
}

/**
 * Render Instructor Dashboard Stats
 */
function renderInstructorDashboard(data) {
    // Set user name in welcome message
    const welcomeHeading = document.querySelector('.welcome-section h1');
    if (welcomeHeading) {
        welcomeHeading.innerHTML = '<i class="fas fa-graduation-cap"></i> Welcome Back, ' + escapeHtml(data.userName || 'Instructor') + '!';
    }

    // Request count
    const requestCountEl = document.getElementById('instructor-request-count');
    if (requestCountEl) {
        requestCountEl.textContent = data.requestCount || 0;
    }

    // Active courses
    const activeCoursesEl = document.getElementById('instructor-active-courses');
    if (activeCoursesEl) {
        activeCoursesEl.textContent = data.activeCourses || 0;
    }

    // Total students
    const totalStudentsEl = document.getElementById('instructor-total-students');
    if (totalStudentsEl) {
        totalStudentsEl.textContent = data.totalStudents || 0;
    }

    // Total enrollments (active + completed)
    const totalEnrollmentsEl = document.getElementById('instructor-total-enrollments');
    if (totalEnrollmentsEl) {
        totalEnrollmentsEl.textContent = data.totalEnrollments || 0;
    }

    // Pending requests badge
    const enrollmentRequestsBadge = document.querySelector('.instructor-requests-badge');
    if (enrollmentRequestsBadge) {
        enrollmentRequestsBadge.textContent = data.pendingRequests || 0;
    }

    // Update teaching statistics
    updateInstructorStats(data);
}

/**
 * Render Student Dashboard Stats
 */
function renderStudentDashboard(data) {
    // Set user name in welcome message
    const welcomeHeading = document.querySelector('.welcome-section h1');
    if (welcomeHeading) {
        welcomeHeading.innerHTML = '<i class="fas fa-graduation-cap"></i> Welcome Back, ' + escapeHtml(data.userName || 'Student') + '!';
    }

    // Request count
    const requestCountEl = document.getElementById('student-request-count');
    if (requestCountEl) {
        requestCountEl.textContent = data.requestCount || 0;
    }

    // Active courses
    const activeCoursesEl = document.getElementById('student-active-courses');
    if (activeCoursesEl) {
        activeCoursesEl.textContent = data.activeCourses || 0;
    }

    // Completed
    const completedEl = document.getElementById('student-completed');
    if (completedEl) {
        completedEl.textContent = data.completed || 0;
    }

    // In progress
    const inProgressEl = document.getElementById('student-in-progress');
    if (inProgressEl) {
        inProgressEl.textContent = data.inProgress || 0;
    }

    // Pending
    const pendingEl = document.getElementById('student-pending');
    if (pendingEl) {
        pendingEl.textContent = data.pending || 0;
    }

    // Learning progress
    if (data.learningProgress) {
        const lpActive = document.getElementById('student-lp-active-courses');
        if (lpActive) {
            lpActive.textContent = data.learningProgress.activeCourses || 0;
        }

        const lpCompleted = document.getElementById('student-lp-completed');
        if (lpCompleted) {
            lpCompleted.textContent = data.learningProgress.completed || 0;
        }

        const lpTotal = document.getElementById('student-lp-total');
        if (lpTotal) {
            lpTotal.textContent = data.learningProgress.totalEnrollments || 0;
        }

        // Overall progress percentage
        const overallProgress = data.learningProgress.overallProgress;
        if (overallProgress) {
            const progressPercent = overallProgress.total > 0 
                ? Math.round((overallProgress.completed / overallProgress.total) * 100)
                : 0;
            
            const progressEl = document.getElementById('student-overall-progress');
            if (progressEl) {
                progressEl.textContent = progressPercent + '%';
            }
        }
    }

    // Update stats cards
    updateStudentStatsCards(data);

    // Pending requests badge
    const myRequestsBadge = document.querySelector('.student-requests-badge');
    if (myRequestsBadge) {
        myRequestsBadge.textContent = data.requestCount || 0;
    }
}

/**
 * Update stat boxes dynamically for admin dashboard
 */
function updateStatBoxes(data, role) {
    const statBoxes = document.querySelectorAll('.dashboard-card .card-body');
    
    if (role === 'admin') {
        // Find the quick stats section row
        const rows = document.querySelectorAll('.row');
        rows.forEach(row => {
            const cards = row.querySelectorAll('.dashboard-card');
            
            // Looking for stats section with 4 cards
            if (cards.length === 4) {
                // Update each card
                if (cards[0]) {
                    const h5 = cards[0].querySelector('h5');
                    if (h5) h5.textContent = data.totalStudents || 0;
                }
                if (cards[1]) {
                    const h5 = cards[1].querySelector('h5');
                    if (h5) h5.textContent = data.activeCourses || 0;
                }
                if (cards[2]) {
                    const h5 = cards[2].querySelector('h5');
                    if (h5) h5.textContent = data.instructors || 0;
                }
                if (cards[3]) {
                    const h5 = cards[3].querySelector('h5');
                    if (h5) h5.textContent = data.pendingRequests || 0;
                }
            }
        });
    }
}

/**
 * Update instructor teaching statistics
 */
function updateInstructorStats(data) {
    const statBoxes = document.querySelectorAll('.instructor-stats .stat-box');
    
    if (statBoxes.length >= 4) {
        // Active courses
        const h4s = statBoxes[0].querySelector('h4');
        if (h4s) h4s.textContent = data.activeCourses || 0;

        // Total students
        if (statBoxes[1]) {
            const h4 = statBoxes[1].querySelector('h4');
            if (h4) h4.textContent = data.totalStudents || 0;
        }

        // Pending requests
        if (statBoxes[2]) {
            const h4 = statBoxes[2].querySelector('h4');
            if (h4) h4.textContent = data.pendingRequests || 0;
        }

        // Total enrollments
        if (statBoxes[3]) {
            const h4 = statBoxes[3].querySelector('h4');
            if (h4) h4.textContent = data.totalEnrollments || 0;
        }
    }
}

/**
 * Update student stats cards
 */
function updateStudentStatsCards(data) {
    const statCards = document.querySelectorAll('.progress-stats-item');
    
    if (statCards.length >= 4) {
        // Active courses
        if (statCards[0]) {
            const num = statCards[0].querySelector('.stat-number');
            if (num) num.textContent = data.activeCourses || 0;
        }statBoxes

        // Completed
        if (statCards[1]) {
            const num = statCards[1].querySelector('.stat-number');
            if (num) num.textContent = data.completed || 0;
        }

        // Overall progress
        if (statCards[2]) {
            const num = statCards[2].querySelector('.stat-number');
            if (num) {
                const overallProgress = data.learningProgress?.overallProgress;
                const progressPercent = overallProgress && overallProgress.total > 0 
                    ? Math.round((overallProgress.completed / overallProgress.total) * 100)
                    : 0;
                num.textContent = progressPercent + '%';
            }
        }

        // Total enrollments
        if (statCards[3]) {
            const num = statCards[3].querySelector('.stat-number');
            if (num) num.textContent = data.learningProgress?.totalEnrollments || 0;
        }
    }

    // Update quick stats section (second row with 4 columns)
    const allCards = document.querySelectorAll('.row .col-md-3 .dashboard-card');
    if (allCards.length >= 4) {
        // Active
        if (allCards[0]) {
            const h5 = allCards[0].querySelector('h5');
            if (h5) h5.textContent = data.activeCourses || 0;
        }

        // Completed
        if (allCards[1]) {
            const h5 = allCards[1].querySelector('h5');
            if (h5) h5.textContent = data.completed || 0;
        }

        // In progress
        if (allCards[2]) {
            const h5 = allCards[2].querySelector('h5');
            if (h5) h5.textContent = data.inProgress || 0;
        }

        // Pending
        if (allCards[3]) {
            const h5 = allCards[3].querySelector('h5');
            if (h5) h5.textContent = data.pending || 0;
        }
    }
}

window.loadDashboardData = loadDashboardData;
window.updateStudentStatsCards = updateStudentStatsCards;
