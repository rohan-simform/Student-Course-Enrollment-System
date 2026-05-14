function initTable(tableId, columns, data = null, ajaxUrl = null) {
    let options = {
        processing: true,
        responsive: true,
        columns: columns,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            paginate: {
                first: '<<',
                last: '>>',
                next: '>',
                previous: '<'
            }
        }
    };

    // Server-side processing
    if (ajaxUrl) {
        options.serverSide = true;
        options.ajax = {
            url: ajaxUrl,
            type: 'GET'
        };
    }

    // Client-side data
    if (data) {
        options.data = data;
    }

    const table = $(`#${tableId}`).DataTable(options);

    // Auto resize on window resize
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            table.columns.adjust().responsive.recalc();
        }, 250);
    });

    return table;
}
