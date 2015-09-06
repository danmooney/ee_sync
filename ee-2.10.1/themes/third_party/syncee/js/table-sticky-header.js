$(function ($) {
    var observers = [],
        stickyTablesSelectorStr = '[data-sticky-table]',
        stickyRowsSelectorStr = '[data-sticky-table-row]',
        $stickyTables = $(stickyTablesSelectorStr),
        $stickyRows = $(stickyRowsSelectorStr),
        bracketRegex = new RegExp('[\\[|\\]]', 'g'),
        $stuckRows
    ;

    function evaluateStickyRowHighestBottomInTable ($stickyTable) {
        var $alreadyStuckRows = $stickyTable.find('.stuck, .transitioning-to-stuck'),
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
        $stickyRows.each(function evaluateStickinessOfStickyRow (idx, stickyRow) {
            var $stickyRow = $(stickyRow),
                $nextStickyRow,
                $stickyTable = $stickyRow.closest(stickyTablesSelectorStr),
                $stickyRowsInTable = $stickyTable.find(stickyRowsSelectorStr)/*.not('.transitioning-to-stuck')*/,
                stickyRowIndexInTable,
                stickyTableMaxNumberOfStickyRows = $stickyTable.data('sticky-table-max-rows') || Infinity,
                maxNumberOfStickyRowsLimitMet = stickyTableMaxNumberOfStickyRows <= $stickyTable.find('.stuck').length,
                isLastStuckRow,
                stickyRowTopRelativeToViewport,
                nextStickyRowTopRelativeToViewport,
                shouldBeSticky,
                nextRowShouldBeStuck,
                nextRowIsTransitioningToStuck,
                nextRowShouldBeTransitioningToStuck,
                nextRowShouldStopTransitioningToStuck,
                topToAssignToNextRow,
                shouldBeUnsticky,
                differenceBetweenNextStickyRowCurrentOffsetTopAndTriggerOffsetTop
            ;

            assignUnstickyTopPxTriggerToStickyRow($stickyRow);

            if (!stickyRow || !stickyRow.getBoundingClientRect) {
                return true; // continue
            }

            stickyRowTopRelativeToViewport = stickyRow.getBoundingClientRect().top;

            stickyRowIndexInTable = $stickyRowsInTable.index($stickyRow);
            isLastStuckRow        = $stickyRow.hasClass('stuck') && stickyRowIndexInTable === $stickyRowsInTable.filter('.stuck').length - 1;

            if (maxNumberOfStickyRowsLimitMet) {
                if (isLastStuckRow) {
                    $nextStickyRow                        = $stickyRowsInTable.eq(stickyRowIndexInTable + 1);
                    nextRowIsTransitioningToStuck         = $nextStickyRow.hasClass('transitioning-to-stuck');

                    if ($nextStickyRow.length) {
                        nextStickyRowTopRelativeToViewport = $nextStickyRow.get(0).getBoundingClientRect().top;
                    }

                    shouldBeUnsticky                      = $stickyRow.hasClass('stuck') && $stickyRow.offset().top <= $stickyRow.data('unsticky-top-px-trigger');

                    // lastStuckRow needs to be unsticky, and next (unstuck) row needs to be sticky
                    nextRowShouldBeTransitioningToStuck   = !nextRowIsTransitioningToStuck && $nextStickyRow.length && nextStickyRowTopRelativeToViewport <= evaluateStickyRowHighestBottomInTable($stickyTable);
                    nextRowShouldStopTransitioningToStuck = nextRowIsTransitioningToStuck && $nextStickyRow.offset().top <= $nextStickyRow.data('unsticky-top-px-trigger');
                    nextRowShouldBeStuck                  = nextStickyRowTopRelativeToViewport <= stickyRowTopRelativeToViewport;
                }
            } else {
                shouldBeSticky        = !$stickyRow.hasClass('stuck') && stickyRowTopRelativeToViewport <= evaluateStickyRowHighestBottomInTable($stickyTable); // need to evaluate most bottom sticky row clientrectbottom for determining the top pixel value of the viewport for triggerring stickiness
                shouldBeUnsticky      = $stickyRow.hasClass('stuck') && isLastStuckRow && $stickyRow.offset().top <= $stickyRow.data('unsticky-top-px-trigger');
            }

            function addStickyPlaceholder ($stickyRow) {
                var $stickyTrPlaceholder = $stickyRow.clone();

                // if sticky placeholder already exists, return
                if ($stickyRow.next('.sticky-placeholder').length) {
                    return;
                }

                $stickyTrPlaceholder
                    .removeAttr(stickyRowsSelectorStr.replace(bracketRegex, ''))
                    .addClass('sticky-placeholder')
                    .css({
                        top: 'auto',
                        position: 'static'
                    })
                ;

                $stickyTrPlaceholder.insertAfter($stickyRow);
            }

            function removeStickyPlaceholder ($stickyRow) {
                $stickyRow.next('.sticky-placeholder').remove();
            }

            function assignUnstickyTopPxTriggerToStickyRow($stickyRow) {
                var $correspondingStickyPlaceholderRow = $stickyRow.next('.sticky-placeholder');

                if (typeof $stickyRow.data('unsticky-top-px-trigger') === 'undefined') {
                    $stickyRow.data('unsticky-top-px-trigger', $stickyRow.offset().top);
                } else if ($correspondingStickyPlaceholderRow.length) {
                    $stickyRow.data('unsticky-top-px-trigger', $correspondingStickyPlaceholderRow.offset().top);
                }
            }

            function stickify ($stickyRow, isTransitioning, setTopProperty) {
                var highestClientRectBottom = evaluateStickyRowHighestBottomInTable($stickyTable)
                ;

                setTopProperty = typeof setTopProperty === 'boolean' ? setTopProperty : true;

                assignUnstickyTopPxTriggerToStickyRow($stickyRow);

                addStickyPlaceholder($stickyRow);

                if (!isTransitioning) {
                    $stickyRow
                        .data('sticky-table-row-stuck', 1)
                        .addClass('stuck')
                    ;

                    if (setTopProperty) {
                        $stickyRow.css({
                            top: highestClientRectBottom
                        });
                    }
                }

                $stickyTable.addClass('table-layout-auto');
            }

            function unstickify ($stickyRow, isTransitioning) {
                var isLastRowToUnstick = $stickyTable.find(stickyRowsSelectorStr).length === 1;

                removeStickyPlaceholder($stickyRow);

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
                stickify($stickyRow);
            }

            if (shouldBeUnsticky) {
                console.log('Time to UNstickify: ', stickyRow);
                unstickify($stickyRow);
            }

            if (nextRowShouldStopTransitioningToStuck) {
                unstickify($nextStickyRow, true);
                $nextStickyRow.removeClass('transitioning-to-stuck');
            } else if (nextRowShouldBeTransitioningToStuck) {
                console.log('Time to transition to stuck for NEXT row: ', $nextStickyRow);
                topToAssignToNextRow = Math.max(nextStickyRowTopRelativeToViewport || 0, evaluateStickyRowHighestBottomInTable($stickyTable));

                assignUnstickyTopPxTriggerToStickyRow($nextStickyRow);

                $nextStickyRow.addClass('transitioning-to-stuck').css({
                    top: topToAssignToNextRow,
                    'z-index': (+$stickyRow.css('z-index') || 0) + 1
                });

                stickify($nextStickyRow, true);
            } else if (nextRowIsTransitioningToStuck) { // adjust position top based on scroll position
                if (nextRowShouldBeStuck) {
                    console.log('Transitioning row should now be stuck', $nextStickyRow);
                    $nextStickyRow.removeClass('transitioning-to-stuck').css('top', stickyRowTopRelativeToViewport);
                    stickify($nextStickyRow, false, false);
                } else { // simulate scrolling up the row by adjusting top
                    differenceBetweenNextStickyRowCurrentOffsetTopAndTriggerOffsetTop = $nextStickyRow.offset().top - $nextStickyRow.data('unsticky-top-px-trigger');
                    if (differenceBetweenNextStickyRowCurrentOffsetTopAndTriggerOffsetTop > 0) {
                        $nextStickyRow.css('top', parseInt($nextStickyRow.css('top'), 10) + (differenceBetweenNextStickyRowCurrentOffsetTopAndTriggerOffsetTop * -1));
                    }
                }
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