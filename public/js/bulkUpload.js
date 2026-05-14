$(document).ready(function () {

    const $fileInput = $('#csv_file');
    const $fileNameDisplay = $('#fileNameDisplay');
    const $submitBtn = $('#submitBtn');
    const $dropArea = $('.file-upload-area');

    $fileInput.on('change', function () {

        if (this.files && this.files[0]) {

            const file = this.files[0];

            const fileSize = (file.size / 1024).toFixed(2);

            $fileNameDisplay.html(`
                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                Selected: <strong>${file.name}</strong> (${fileSize} KB)
            `);

            $fileNameDisplay.addClass('show');

            $submitBtn.prop('disabled', false);

        } else {

            $fileNameDisplay.removeClass('show');

            $submitBtn.prop('disabled', true);
        }
    });

    $dropArea.on('dragenter dragover', function (e) {

        e.preventDefault();
        e.stopPropagation();

        $dropArea.addClass('drag-highlight');
    });

    $dropArea.on('dragleave drop', function (e) {

        e.preventDefault();
        e.stopPropagation();

        $dropArea.removeClass('drag-highlight');
    });

    $dropArea.on('drop', function (e) {

        const files = e.originalEvent.dataTransfer.files;

        $fileInput[0].files = files;

        $fileInput.trigger('change');
    });

    $('form').on('submit', function (e) {

        e.preventDefault();

        if (!$fileInput[0].files.length) {
            alert('Please select a CSV file');
            return;
        }

        const formData = new FormData(this);

        $submitBtn.prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (response) {

                if(!response.status) {

                    let message =
                        response.message +
                        '\n\nNo users were created.' +
                        '\nAll rows must be valid before upload can continue.';

                    if (response.errors?.length) {

                        message += '\n\n';

                        response.errors.forEach(error => {

                            message +=
                                `Row ${error.row} | Column: ${error.column} | ${error.error}\n`;
                        });
                    }

                    alert(message);

                    return;
                }

                alert(response.message);

                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },

            error: function (xhr) {
                console.log(xhr);
                let message = 'Upload failed';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            },

            complete: function () {

                $submitBtn.prop('disabled', false);
            }
        });
    });
});