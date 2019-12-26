var PAGES = ['home', 'new', 'active'];

$(document).ready(function () {
    var hash = window.location.hash;
    if (hash == '#new') changePage('new');
    if (hash == '#active') changePage('active');

    $('#save-btn').bind('click', onSaveButtonClick);
    $('.delete-all-btn').bind('click', onDeleteAllJobsClick);
});

function onDeleteAllJobsClick(event) {
    $.ajax({url: "wap/cron/deleteall"}).done(function (data) {
        location.reload();
    });
}

function onDeleteJobClick(jobID) {
    $.post("wap/cron/deletejob", { "jobid": jobID },
        function (data) {
            location.reload();
        }
    );
}

function onSaveButtonClick(event) {
    var minute = $('.add-minute').val();
    var hour = $('.add-hour').val();
    var dayweek = $('.add-dayweek').val();
    var daymonth = $('.add-daymonth').val();
    var month = $('.add-month').val();
    var name = $('.add-name').val();
    var command = $('.add-command').val();

    $.post("wap/cron/add", { "minute": minute, "hour": hour, "dayweek": dayweek, "daymonth": daymonth, "month": month, "name": name, "command": command },
        function (data) {
            console.log(data);
            changePage('active');
        }
    );
}

function changePage(page) {
    getActiveCronjobs();

    // Hide all pages
    for (var i = 0; i < PAGES.length; i++) {
        $('#' + PAGES[i]).hide();
    }
    $('.nav li').removeClass('active');

    // Show the specified page.
    $('#' + page).show();
    $('.nav-' + page).addClass('active');
}

function getActiveCronjobs() {
    $.getJSON('wap/cron/active.', function (data) {
        var items = '';

        $.each(data, function (key, val) {
            var n = val.split(' ');

            items += '<tr>';

            $.each(n, function (x, y) {
                items += '<td>' + y + '</td>';
            });
            items += '<td><a class="btn btn-mini btn-warning remove_' + key + '" onclick="onDeleteJobClick(' + key + ')">删除</a></td>';
            items += '</tr>';
        });

        $('.active-job-list').html(items);
    });
}