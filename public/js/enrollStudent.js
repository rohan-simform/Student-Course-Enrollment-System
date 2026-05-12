let currentUserId = '';
let currentUserRole = '';
let allCourses = [];

async function loadUserInfo() {
    try {
        const result = await $.ajax({
            url: APP.baseUrl + 'auth/getCurrentUser.php',
            type: 'GET',
            dataType: 'json'
        });
        
        if (result.status) {
            currentUserId = result.data.user_id;
            currentUserRole = result.data.role;
            // Dashboard navigation is now in the sidebar
            return true;
        }
    } catch (error) {
        console.error('Error loading user info:', error);
    }
    return false;
}

async function loadEnrollData() {
    try {
        const result = await $.ajax({
            url: APP.baseUrl + 'enrollments/getEnrollStudentData.php',
            type: 'GET',
            dataType: 'json'
        });
        
        if (!result.status) {
            alert('Failed to load data: ' + (result.message || 'Unknown error'));
            return;
        }

        const { students, courses } = result.data;
        allCourses = courses;

        // Populate students dropdown
        const studentSelect = document.querySelector('select[name="student_id"]');
        studentSelect.innerHTML = '<option value="">Select Student</option>';
        
        students.forEach(student => {
            const option = document.createElement('option');
            option.value = student.id;
            option.textContent = student.id + ' - ' + escapeHtml(student.name || '');
            studentSelect.appendChild(option);
        });

        // Keep courses dropdown empty initially - populate only when student is selected
        populateCoursesDropdown([]);

    } catch (error) {
        console.error('Error loading enroll data:', error);
        alert('Failed to load data');
    }
}

async function loadAvailableCoursesForStudent(studentId) {
    try {
        if (!studentId) {
            populateCoursesDropdown(allCourses);
            return;
        }

        const result = await $.ajax({
            url: APP.baseUrl + `enrollments/getAvailableCoursesForStudent.php?student_id=${studentId}`,
            type: 'GET',
            dataType: 'json'
        });
        
        if (!result.status) {
            alert('Failed to load available courses');
            populateCoursesDropdown(allCourses);
            return;
        }

        populateCoursesDropdown(result.data);

    } catch (error) {
        console.error('Error loading available courses:', error);
        populateCoursesDropdown(allCourses);
    }
}

function populateCoursesDropdown(courses) {
    const courseSelect = document.querySelector('select[name="assignment"]');
    courseSelect.innerHTML = '<option value="">Select Course</option>';
    
    courses.forEach(course => {
        const option = document.createElement('option');
        option.value = course.course_id + '|' + course.instructor_id;
        option.textContent = course.course_id + ' - ' + escapeHtml(course.course_name) + 
                            ' - ' + escapeHtml(course.instructor_name);
        courseSelect.appendChild(option);
    });
}

$(document).ready(async function () {
    await loadUserInfo();
    await loadEnrollData();

    // Add listener for student selection
    $('select[name="student_id"]').on('change', function () {
        loadAvailableCoursesForStudent(this.value);
    });
});
