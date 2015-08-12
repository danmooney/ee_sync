$(function () {
    var pressed = false,
        start,
        startX,
        startWidth
    ;

    $("table th").mousedown(function (e) {
        start = $(this);
        pressed = true;
        startX = e.pageX;
        startWidth = $(this).width();

        $(start)
            .addClass("resizing")
            .addClass("noSelect")
        ;
    });

    $(document).mousemove(function (e) {
        if (!pressed) {
            return;
        }

        $(start).width(startWidth + (e.pageX - startX));
    });

    $(document).mouseup(function () {
        if (!pressed) {
            return;
        }

        $(start)
            .removeClass("resizing")
            .removeClass("no-select")
        ;

        pressed = false;
    });
});