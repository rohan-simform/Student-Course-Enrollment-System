let assignedCoursesTable = null;

$(document).ready(function () {
    initializeDataTable();
});

function initializeDataTable() {
    const columns = [
        { data: 'course_id' },
        { data: 'course_name' },
        { data: 'instructor_id' },
        { data: 'instructor_name' },
        {
            data: 'course_id',
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <div class="action-buttons">
                        <button class="action-btn btn-delete" onclick="removeCourse(${data}, ${row.instructor_id})">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                `;
            }
        }
    ];

    assignedCoursesTable = initTable(
        'assignedCoursesTable',
        columns,
        null,
        APP.baseUrl + 'assignedCourses/getAssignedCoursesList.php'
    );
}

function removeCourse(courseId, instructorId) {
    if (!confirm('Remove assignment?')) {
        return;
    }

    $.ajax({
        url: APP.baseUrl + 'assignedCourses/updateAssignedCourse.php',
        type: 'POST',
        data: {
            action: 'remove',
            course_id: courseId,
            instructor_id: instructorId
        },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            if (response.status && assignedCoursesTable) {
                assignedCoursesTable.ajax.reload();
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