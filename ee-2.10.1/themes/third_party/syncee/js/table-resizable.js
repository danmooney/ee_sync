$(function ($) {
    var pressed = false,
        $start,
        startX,
        startWidth,
        resizableTableSelectorStr = '[data-resizable-table]',
        $resizableTables = $(resizableTableSelectorStr),
        $tableHeaders = $(resizableTableSelectorStr + ' > thead > tr > th'),
        $tableCells = $(resizableTableSelectorStr + ' > tbody > tr > td'),
        startWidthAsPercentage
    ;

    $tableHeaders
        .mousedown(function (e) {
            // if mousedown triggered over something other than th, return
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
                $correspondingTdsInColumn = $th.closest(resizableTableSelectorStr).find('tbody tr td:nth-child(' + (thIdx + 1) + ')'),
                maxWidthOfCellInColumn
            ;

            // if dblclick triggered over something other than th, return
            if ((e.target || e.srcElement) != e.currentTarget) {
                return;
            }

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
        var width = $(this).width();

        $(this).width(width).data('stored-width', width);
    });

    $tableCells.each(function () {  // set pixel widths based on width assigned to corresponding header column
        var $tableCell = $(this),
            $resizableTable = $tableCell.closest(resizableTableSelectorStr),
            idx = $tableCells.index($tableCell),
            colIdx = $tableCell.data('col-idx'),
            width = $tableCell.width()
        ;

        $tableCell.width(width).data('stored-width', width);

        if (typeof colIdx !== 'undefined') {
            $resizableTable.find('[data-col-idx="' + colIdx + '"]').width(width);
        }
    });

    function evaluateMaxWidthsInEachColumn () {

    }

    $(document).on('stuck', resizableTableSelectorStr + ' tr', function (e) {
        var $table = $(e.currentTarget).closest('table'),
            $tableRows = $table.find('tr'),
            $tableCells = $tableRows.find('td, th'),
            colCellsByIdx = [],
            colIdxsResizedMaxWidths = [],
            highestColIdx = $table.find('tr td').last().data('col-idx'),
            i
        ;

        for (i = 0; i <= 0/*<= highestColIdx*/; i += 1) {
            colIdxsResizedMaxWidths[i] = 0;
            colCellsByIdx[i]           = $tableCells.filter('[data-col-idx="' + i + '"]');

            colCellsByIdx[i].slice(0, 10).each(function () {
                var $tableCell = $(this),
                    width
                ;

                if ($tableCell.parent('.stuck').length) {
                    return true; // continue
                }

                width = $tableCell.width();

                if (width > colIdxsResizedMaxWidths[i]) {
                    colIdxsResizedMaxWidths[i] = width;
                }
            });
        }

        for (i = 0; i <= 0; i += 1) {
            colCellsByIdx[i] = colCellsByIdx[i].filter(function () {
                return !$(this).closest('.nested-table-container').length
            });

            colCellsByIdx[i].each(function (idx) {
                var $tableCell = $(this),
                    storedWidthData = $tableCell.data('stored-width'),
                    tableCellWidth = $tableCell.width(),
                    maxColumnWidth = colIdxsResizedMaxWidths[i],
                    newTableCellWidth = storedWidthData
                ;

                if (typeof storedWidthData === 'undefined' || tableCellWidth >= maxColumnWidth) {
                    return true; // continue
                }

                if (tableCellWidth !== storedWidthData) {
                    //newTableCellWidth += (storedWidthData - tableCellWidth);
                }

                //if (tableCellWidth !== colIdxsResizedMaxWidths[i]) {
                    newTableCellWidth += (colIdxsResizedMaxWidths[i] - tableCellWidth) /*+ 1*/;
                //}

                if (tableCellWidth !== newTableCellWidth) {
                    $tableCell.width(newTableCellWidth);

                    if (!$tableCell.parent('.stuck').length) { // if has already altered stuck and non stuck cell in column, then break out of loop
                        colCellsByIdx[i].slice(idx).width(newTableCellWidth);
                        return false;
                    }
                }
            });
        }
    });


    // reset all cells back to original stored width when everything's unstuck
    $(document).on('all-unstuck', resizableTableSelectorStr, function setAllCellsToOriginalStoredWidth () {
        $(this).find('td, th').each(function () {
            var $tableCell = $(this),
                storedWidthData = $tableCell.data('stored-width');

            if (typeof storedWidthData !== 'undefined') {
                $(this).width(storedWidthData);
            }
        });
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

        $start
            .removeClass('resizing')
            .removeClass('no-select')
        ;

        pressed = false;
    });
});