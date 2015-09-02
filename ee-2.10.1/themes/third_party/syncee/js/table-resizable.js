$(function ($) {
    var pressed = false,
        $start,
        startX,
        startWidth,
        $tableHeaders = $('[data-resizable-table] > thead > tr > th'),
        $tableCells = $('[data-resizable-table] > tbody > tr > td'),
        startWidthAsPercentage
    ;

    $tableHeaders
        .mousedown(function (e) {
            // if mousedown triggered over something other than th, return;
            if ((e.target || e.srcElement) != e.currentTarget) {
                return;
            }

            $start = $(this);
            pressed = true;
            startX = e.pageX;
            startWidth = $start.width();
            //startWidthAsPercentage = parseFloat($start.get(0).style.width);

            //console.log(startWidthAsPercentage);

            $start
                .addClass('resizing')
                .addClass('no-select')
            ;
        })
        .dblclick(function (e) {
            var $th = $(e.currentTarget),
                $thChild = $th.children(),
                thIdx = $th.siblings('th').andSelf().index($th),
                $correspondingTdsInColumn = $th.closest('table').find('tbody tr td:nth-child(' + (thIdx + 1) + ')'),
                maxWidthOfCellInColumn
            ;

            $thChild.css('display', 'inline-block');
            maxWidthOfCellInColumn = $thChild.outerWidth();
            $thChild.css('display', 'block');

            $correspondingTdsInColumn.each(function () {
                var $td = $(this),
                    tdChildrenWidth = 0,
                    $tdChildren = $td.children()
                ;

                $tdChildren.each(function () {
                    var $el = $(this),
                        displayStyle = $el.css('display');

                    tdChildrenWidth += $el.css('display', 'inline-block').outerWidth();
                    $el.css('display', displayStyle);
                });

                if (tdChildrenWidth > maxWidthOfCellInColumn) {
                    maxWidthOfCellInColumn = tdChildrenWidth;
                }
            });

            //console.log(maxWidthOfCellInColumn);

            if (!maxWidthOfCellInColumn) {
                return;
            }

            $th.width(maxWidthOfCellInColumn);
        })
    ;

    $tableHeaders.each(function () {  // set pixel widths based on percentage assigned
        $(this).width($(this).width());
    });

    $tableCells.each(function () {  // set pixel widths based on percentage assigned
        $(this).width($(this).width());
    });


    $(document).mousemove(function detectResizeStart (e) {
        var mouseMoveDifference = e.pageX - startX;

        if (!pressed) {
            return;
        }

        console.log('startX: ' + startX, 'e.pageX: ' + e.pageX);

        // TODO - try to make this resize as a percentage, because resizing the window down messes it up... or is it not that big a deal if it's not responsive?

        $start.width(startWidth + mouseMoveDifference);
        //$start.width(startWidth + (e.pageX - startX));
    });

    $(document).mouseup(function detectResizeFinish () {
        if (!pressed) {
            return;
        }

        $($start)
            .removeClass('resizing')
            .removeClass('no-select')
        ;

        pressed = false;
    });
});