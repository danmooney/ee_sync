$(function ($) {
    var observers = [],
        stickyTablesSelectorStr = '[data-sticky-table]',
        stickyRowsSelectorStr = '[data-sticky-table-row]',
        $stickyTables = $(stickyTablesSelectorStr),
        $stickyRows = $(stickyRowsSelectorStr),
        bracketRegex = new RegExp('[\\[|\\]]', 'g'),
        $stuckRows
    ;

    function evaluateStickyRowHighestBottom () {
        var $alreadyStuckRows = $('.stuck'),
            highestClientRectBottom = 0
        ;

        $alreadyStuckRows.each(function () {
            var clientRectBottom = this.getBoundingClientRect().bottom;

            if (clientRectBottom > highestClientRectBottom) {
                highestClientRectBottom = clientRectBottom;
            }
        });

        return highestClientRectBottom;
    }

    function evaluateStickiness () {
        $stickyRows.each(function (idx, stickyRow) {
            var $stickyRow = $(stickyRow),
                $stickyTable = $stickyRow.closest(stickyTablesSelectorStr),
                topRelativeToViewport,
                shouldBeSticky,
                shouldBeUnsticky
            ;

            if (!stickyRow || !stickyRow.getBoundingClientRect) {
                return true; // continue
            }

            topRelativeToViewport = stickyRow.getBoundingClientRect().top;
            shouldBeSticky        = !$stickyRow.hasClass('stuck') && topRelativeToViewport <= evaluateStickyRowHighestBottom(); // need to evaluate most bottom sticky row clientrectbottom for determining the top pixel value of the viewport for triggerring stickiness
            shouldBeUnsticky      = $stickyRow.hasClass('stuck') && $stickyRow.position().top <= $stickyRow.data('unsticky-top-px-trigger');

            function stickify () {
                var highestClientRectBottom = evaluateStickyRowHighestBottom(),

                    $stickyTrPlaceholder = $stickyRow.clone()
                ;

                $stickyTrPlaceholder
                    .removeAttr(stickyRowsSelectorStr.replace(bracketRegex, ''))
                    .addClass('sticky-placeholder')
                ;

                $stickyTrPlaceholder.height($stickyRow.height()).insertAfter($stickyRow);

                $stickyRow
                    .data('sticky-table-row-stuck', 1).css({
                        top: highestClientRectBottom
                    })
                    .addClass('stuck')
                    .data('unsticky-top-px-trigger', $stickyRow.position().top)
                ;

                $stickyTable.addClass('table-layout-auto');
            }

            function unstickify () {
                var isLastRowToUnstick = $stickyTable.find(stickyRowsSelectorStr).length === 1;

                $stickyRow.next('.sticky-placeholder').remove();

                $stickyRow
                    .removeData('sticky-table-row-stuck')
                    .removeClass('stuck')
                ;

                if (isLastRowToUnstick) {
                    $stickyTable.removeClass('table-layout-auto');
                }
            }

            if (shouldBeSticky) {
                console.log('Time to stickify: ', stickyRow);
                stickify();
            }

            if (shouldBeUnsticky) {
                console.log('Time to UNstickify: ', stickyRow);
                unstickify();
            }
        });
    }

    function makeRowSticky ($row) {
        $stickyRows = $stickyRows.add($row);
    }

    function makeRowUnsticky ($row) {
        $stickyRows = $stickyRows.not($row);
    }

    $(document).on('scroll', evaluateStickiness);

    // add observers to sticky table row
    $stickyTables.each(function () {
        var stickyRowSelectorSansBrackets = stickyRowsSelectorStr.replace(bracketRegex, ''),
            observer,
            options = {
                subtree: true,
                attributeFilter: [stickyRowSelectorSansBrackets]
            }
        ;

         observer = new MutationObserver(function (mutations) {
            [].forEach.call(mutations, function (mutation) {
                var $targetRow = $(mutation.target),
                    functionToExecute = typeof $targetRow.attr(stickyRowSelectorSansBrackets) !== 'undefined'
                        ? makeRowSticky
                        : makeRowUnsticky
                ;

                // if row is a dynamically created sticky placeholder, then don't do anything
                if ($targetRow.hasClass('sticky-placeholder')) {
                    return;
                }

                evaluateStickiness();
                functionToExecute($targetRow);
            });

            console.dir(mutations);
        });

        observer.observe(this, options);
        observers.push(observer);
    });

    $stickyRows.each(function () {
        makeRowSticky($(this));
    });
});