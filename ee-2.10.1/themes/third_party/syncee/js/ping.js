$(function ($) {
    var isCurrentlyPinging = false;

    $('.btn-ping-site').on('click', function (e) {
        var $anchor = $(e.currentTarget),
            $remoteSitePayloadContents = $('#remote_site_settings_payload > *')
        ;

        e.preventDefault();

        if (isCurrentlyPinging) {
            return;
        }

        isCurrentlyPinging = true;

        $remoteSitePayloadContents.html('<p>Pinging...</p>');

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: $anchor.attr('href'),
            success: function (data) {
                $remoteSitePayloadContents.html('<pre>' + JSON.stringify(data, null, 4) + '</pre>');
            },
            error: function () {

            },
            complete: function () {
                isCurrentlyPinging = false;
            }
        });
    });
});