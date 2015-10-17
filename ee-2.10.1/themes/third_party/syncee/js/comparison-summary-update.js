Syncee.updateSummaryBasedOnRow = function ($row, isMerged) {
    var $questionMark = $row.find('.question-mark'),
        uniqueIdentifierValue = $row.data('name'),
        $mergeResultSummaryTargetSite = $row.find('.merge-result-summary-target'),
        $mergeResultSummarySourceSites = $row.find('.merge-result-summary-source-site'),
        mergeResultSummarySourceSiteNames = [],
        mergeResultSummarySourceSiteNamesHtmlCollection = [],
        mergeIsAllFromTargetSite = isMerged && !$mergeResultSummarySourceSites.length,
        $summarizationTableCell = $('#syncee').find('.summary-table [data-name="' + uniqueIdentifierValue + '"]').siblings('.summary'),
        $summarizationRow = $summarizationTableCell.closest('tr'),
        questionMarkHtml = ''
    ;

    $mergeResultSummarySourceSites.each(function () {
        var siteName = $(this).text().replace(/:\s+\d+\/\d+$/, '');

        mergeResultSummarySourceSiteNames.push(siteName);
    });

    [].forEach.call(mergeResultSummarySourceSiteNames, function (siteName) {
        mergeResultSummarySourceSiteNamesHtmlCollection.push(''); // TODO
    });

    if (isMerged) {
        if (mergeIsAllFromTargetSite) {
            questionMarkHtml += 'requires no further input.  However, nothing will change since all options have been chosen from the local site.';
        } else {
            questionMarkHtml += 'requires no further input.  It will be merged from ' + mergeResultSummarySourceSiteNames.join(' ');
        }
    } else {
        questionMarkHtml += 'still requires your input.  No action will be taken until all inputs are filled.';
    }

    $summarizationTableCell.html(questionMarkHtml);
    questionMarkHtml = '<strong><i>' + uniqueIdentifierValue + '</i></strong> ' + questionMarkHtml;
    $questionMark.changeQtipContent(questionMarkHtml);

    if (isMerged) {
        $summarizationRow.removeClass('negative').addClass('positive');
    } else {
        $summarizationRow.removeClass('positive').addClass('negative');
    }
};

// bind summary name event click to bring summary row to top of viewport
$(function ($) {
    $('#syncee').find('.summary-table [data-name] a').on('click', (function scrollToSummaryRow() {
        var isCurrentlyScrollingToSummaryRow = false;

        return function (e) {
            var uniqueIdentifierValue = $(this).closest('[data-name]').data('name'),
                $summaryRowToScrollTo = $('.comparison-summary[data-name="' + uniqueIdentifierValue + '"]'),
                summaryRowIsStuck = $summaryRowToScrollTo.hasClass('stuck'),
                combinedStuckRowHeight = 0,
                summaryRowToScrollToIsCollapsedAndIsInViewportAlready
            ;

            e.preventDefault();

            if (isCurrentlyScrollingToSummaryRow) {
                return;
            }

            isCurrentlyScrollingToSummaryRow = true;

            $summaryRowToScrollTo.find('.syncee-highlight').removeClass('syncee-highlight');

            function highlightRow () {
                if (!isCurrentlyScrollingToSummaryRow) {
                    return;
                }

                isCurrentlyScrollingToSummaryRow = false;
                $('.comparison-summary[data-name="' + uniqueIdentifierValue + '"]').children('.comparate-field-container').addClass('syncee-highlight');
            }

            if ($summaryRowToScrollTo.length > 1) {
                $summaryRowToScrollTo = $summaryRowToScrollTo.filter('.sticky-placeholder');
            }

            summaryRowToScrollToIsCollapsedAndIsInViewportAlready = !summaryRowIsStuck && $summaryRowToScrollTo.get(0).getBoundingClientRect().top >= 0;

            if (summaryRowToScrollToIsCollapsedAndIsInViewportAlready) {
                highlightRow();
                return;
            }

            $('.stuck')
                .filter(function () {
                    var isInViewport = $(this).get(0).getBoundingClientRect().top >= 0,
                        isNotComparisonSummary = !$(this).hasClass('comparison-summary')
                    ;

                    if (summaryRowIsStuck) {
                        return isInViewport && isNotComparisonSummary;
                    } else {
                        return isInViewport;
                    }

                }).each(function () {
                    combinedStuckRowHeight += $(this).outerHeight();
                })
            ;

            $('html, body').animate({
                scrollTop: $summaryRowToScrollTo.offset().top - combinedStuckRowHeight
            }, {
                duration: 1000,
                complete: highlightRow
            });
        };
    }()));
});
