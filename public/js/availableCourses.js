let availableCoursesTable = null;

$(document).ready(function () {
    initializeDataTable();
});

function initializeDataTable() {
    const columns = [
        { data: 'course_name' },
        { data: 'instructor_name' },
        { data: 'duration_weeks' },
        { data: 'available_seats' },
        { data: 'max_seats' },
        {
            data: 'course_id',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                let isDisabled = row.available_seats <= 0;

                return `
                    <div class="action-buttons">
                        <button class="action-btn btn-enroll" onclick="enrollCourse(${row.course_id}, ${row.instructor_id})" ${isDisabled ? 'disabled' : ''}>
                            <i class="fas fa-check"></i> Enroll
                        </button>
                    </div>
                `;
            }
        }
    ];

    availableCoursesTable = initTable(
        'availableCoursesTable',
        columns,
        null,
        APP.baseUrl + 'courses/getAvailableCoursesList.php'
    );
}

function enrollCourse(courseId, instructorId) {
    if (!confirm('Enroll in this course?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'enrollments/requestEnrollment.php',
        type: 'POST',
        data: {
            course_id: courseId,
            instructor_id: instructorId
        },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && availableCoursesTable) {
                availableCoursesTable.ajax.reload();
            }
        },
        error: function (xhr) {
            let message = 'Something went wrong';
            if (xhr.responseJSON?.message) {
                message = xhr.responseJSON.message;
            }
            alert(message);
        }
    });
}