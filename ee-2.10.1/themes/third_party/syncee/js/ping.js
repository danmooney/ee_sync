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

            },
            error: function () {

            },
            complete: function (response) {
                var responseText = response.responseText || '(Empty Response)'
                ;

                try {
                    responseText = JSON.stringify(JSON.parse(responseText), null, 4);
                } catch (e) {}

                $remoteSitePayloadContents.text(responseText).wrap('<pre></pre>');
                isCurrentlyPinging = false;
            }
        });
    });
});