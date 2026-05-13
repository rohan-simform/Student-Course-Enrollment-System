$(document).ready(function () {

    $('#menuToggle').on('click', function () {

        $('.sidebar').toggleClass('active');
        $('body').toggleClass('sidebar-open');

    });

});