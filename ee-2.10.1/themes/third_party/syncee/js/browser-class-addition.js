$(function ($) {
    if (!platform || typeof platform.name !== 'string') {
        return;
    }

    $('html').addClass('syncee-' + platform.name.toLowerCase());
});
