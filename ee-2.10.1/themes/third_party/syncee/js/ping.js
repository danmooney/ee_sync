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
                var emptyResponseStr = '(Empty Response)',
                    responseText = response.responseText /*|| responseText.response*/,
                    diagnoses = [],
                    diagnosesStr = '',
                    diagnosesClassStr = 'negative',
                    pingedUrl,
                    requestLogUrl,
                    rawResponseStr
                ;

                try {
                    responseText  = JSON.parse(responseText);
                    diagnoses     = responseText.diagnoses;
                    requestLogUrl = responseText.request_log_url;
                    pingedUrl     = responseText.url;

                    if (responseText.response) {
                        responseText = responseText.response;
                        responseText = JSON.stringify(JSON.parse(responseText), null, 4);
                    } else {
                        responseText = emptyResponseStr;
                    }

                    if (diagnoses.length) {
                        //diagnosesStr += 'Request Errors\n';
                        diagnosesStr += diagnoses.join('\n');
                    } else {
                        diagnosesStr += 'Request Successful!\n';
                        diagnosesClassStr = 'positive';
                    }

                    if (pingedUrl) {
                        diagnosesStr += '<br>Pinged Url: <a target="_blank" href="' + pingedUrl + '">' + pingedUrl + '</a>';
                    }

                    if (requestLogUrl) {
                        diagnosesStr += '<br><a href="' + requestLogUrl + '">Click here to view Request Log in detail</a>';
                    }

                } catch (e) {
                    diagnosesStr = 'An error occurred while parsing the response\n';
                }

                rawResponseStr = '\n\n-----------BEGIN RAW RESPONSE----------\n' + responseText;

                $remoteSitePayloadContents.text(rawResponseStr).wrapInner('<pre></pre>').prepend('<span class="diagnosis-' + diagnosesClassStr + '">' + diagnosesStr + '</span>');
                isCurrentlyPinging = false;
            }
        });
    });
});