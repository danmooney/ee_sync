$(function ($) {
    var $comparisonCollectionTable = $('.comparison-collection-table'),
        summaryCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByRowIdx = [],
        resultCheckboxesBySummaryRowIdx = [],
        summaryRowIdxs = [],
        siteNamesByColIdx = [],
        colIdxCount = $comparisonCollectionTable.children('thead').find('tr th').length
    ;

    // TODO - if values are all the same in the comparison detail row, then check off the local column for that row, or mark it as a completely matching row and maybe have checkbox to show/hide those rows

    // get list of summary row indexes
    $('[data-summary-row-idx]').each(function () {
        summaryRowIdxs.push($(this).data('summary-row-idx'));
    });

    summaryRowIdxs = $.unique(summaryRowIdxs);


    // get list of site names by col index
    $('[data-site-title]').each(function () {
        siteNamesByColIdx[$(this).data('col-idx')] = $(this).data('site-title');
    });

    function getSummaryCheckboxesByColIdxAndSummaryRowIdx (colIdx, summaryRowIdx) {
        if (!summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx]) {
            summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx] = [];
        }

        if (!summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx]) {
            summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx] = $comparisonCollectionTable.find('[data-row-idx="' + summaryRowIdx + '"]').find('[data-col-idx="' + colIdx + '"]').find(':checkbox');
        }

        return summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx];
    }

    function getResultCheckboxesByColIdxAndSummaryRowIdx (colIdx, summaryRowIdx) {
        if (!resultCheckboxesByColIdxAndSummaryRowIdx[colIdx]) {
            resultCheckboxesByColIdxAndSummaryRowIdx[colIdx] = [];
        }

        if (!resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx]) {
            resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx] = $comparisonCollectionTable.find('[data-summary-row-idx="' + summaryRowIdx + '"]').find('[data-col-idx="' + colIdx + '"]').find(':checkbox');
        }

        return resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx];
    }

    // fetch all source checkboxes by summary row index
    function getSourceCheckboxesBySummaryRowIdx (summaryRowIdx, arrOrigin) {
        var $checkboxes;

        // seed the data first
        $.each(summaryRowIdxs, function (idx, summaryRowIdx) {
            for (var colIdx = 1; colIdx < colIdxCount; colIdx += 1) {
                getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx);
                getSummaryCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx);
            }
        });

        if (!arrOrigin['sourcesBySummaryRowIdxOnly']) {
            arrOrigin['sourcesBySummaryRowIdxOnly'] = [];
        }

        if (!arrOrigin['sourcesBySummaryRowIdxOnly'][summaryRowIdx]) {
            $checkboxes = $();
            $.each(arrOrigin, function (idx, arr) {
                if (typeof idx === 'number' && arr && arr[summaryRowIdx]) {
                    $checkboxes = $checkboxes.add(arr[summaryRowIdx].filter(function () {
                        return $(this).closest('.source-field').length;
                    }));
                }
            });

            arrOrigin['sourcesBySummaryRowIdxOnly'][summaryRowIdx] = $checkboxes;
        }

        return arrOrigin['sourcesBySummaryRowIdxOnly'][summaryRowIdx];
    }

    function getResultCheckboxesByRowIdx (rowIdx) {
        if (!resultCheckboxesByRowIdx[rowIdx]) {
            resultCheckboxesByRowIdx[rowIdx] = $comparisonCollectionTable.find('[data-row-idx="' + rowIdx + '"]').find(':checkbox');
        }

        return resultCheckboxesByRowIdx[rowIdx];
    }

    function getResultCheckboxesBySummaryRowIdx (summaryRowIdx) {
        if (!resultCheckboxesBySummaryRowIdx[summaryRowIdx]) {
            resultCheckboxesBySummaryRowIdx[summaryRowIdx] = $comparisonCollectionTable.find(' .comparison-details [data-summary-row-idx="' + summaryRowIdx + '"]').find(':checkbox');
        }

        return resultCheckboxesBySummaryRowIdx[summaryRowIdx];
    }

    $comparisonCollectionTable.find('.comparison-summary').on('click', function (e) {
        var $comparisonSummary = $(e.currentTarget),
            $comparisonDetails,
            comparisonDetailsIsSlidUp
        ;

        if ($(e.target || e.srcElement).is(':input')) {
            return;
        }

        $comparisonDetails        = $comparisonSummary.next('.comparison-details');
        comparisonDetailsIsSlidUp = $comparisonDetails.find('table').is(':hidden');

        if (comparisonDetailsIsSlidUp) { // corresponding comparison details is about to be exposed; add sticky row data attribute to comparison summary
            $comparisonSummary.attr('data-sticky-table-row', 1);
        } else { // remove sticky row data attribute
            $comparisonSummary.removeAttr('data-sticky-table-row');
        }

        $comparisonDetails.find('.nested-table-container div').slideToggle();
    });

    function calculateMergeResult ($checkbox) {
        var $cell = $checkbox.closest('td'),
            $row  = $cell.closest('tr'),
            colIdx = $cell.data('col-idx'),
            rowIdx = $row.data('row-idx'),
            summaryRowIdx = $row.data('summary-row-idx') || rowIdx,
            $correspondingMergeCell = $cell.siblings('.merge-result'),
            $correspondingMergeCellSpan = $correspondingMergeCell.children('span'),
            isTargetField = $cell.hasClass('target-field'),
            isSourceField = !isTargetField,
            isExistenceSummaryCell = $cell.hasClass('comparison-site-collection-existence-container'),
            $summaryCheckbox = getSummaryCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx),
            $summaryMergeCell = $summaryCheckbox.closest('tr').find('.merge-result'),
            $summaryMergeCellSpan = $summaryMergeCell.children('span'),
            checkedCheckboxesExistInTarget = (
                getSummaryCheckboxesByColIdxAndSummaryRowIdx(1, summaryRowIdx).filter(':checked').length ||
                getResultCheckboxesByColIdxAndSummaryRowIdx(1, summaryRowIdx).filter(':checked').length
            ),
            checkedCheckboxesExistInSources = (
                getSourceCheckboxesBySummaryRowIdx(summaryRowIdx, summaryCheckboxesByColIdxAndSummaryRowIdx).filter(':checked').length ||
                getSourceCheckboxesBySummaryRowIdx(summaryRowIdx, resultCheckboxesByColIdxAndSummaryRowIdx).filter(':checked').length
            ),
            detailCellHtml = $.trim($cell.find('.value').html()) ? '<span>' + $cell.find('.value').html() + '</span>' : '<span>&nbsp;</span>',
            summaryCellTargetHtmlArr = [],
            summaryCellSourceHtmlArr = [],
            totalCheckboxInColumnCount = getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx).length,
            hasAllDetailedRowsChecked = getResultCheckboxesBySummaryRowIdx(summaryRowIdx).filter(':checked').length === totalCheckboxInColumnCount,
            checkboxesCheckedInDetailResultsByColIdx = [],
            i
        ;

        // calculate number of checkboxes checked for this deatail result in each column
        for (i = 1; i < colIdxCount; i += 1) {
            if (!getResultCheckboxesByColIdxAndSummaryRowIdx(i, summaryRowIdx).length) { // if no checkboxes in column, assign null
                checkboxesCheckedInDetailResultsByColIdx[i] = null;
                continue;
            }

            checkboxesCheckedInDetailResultsByColIdx[i] = getResultCheckboxesByColIdxAndSummaryRowIdx(i, summaryRowIdx).filter(':checked').length;
        }

        if (!$correspondingMergeCell.data('original-content')) {
            $correspondingMergeCell.data('original-content', $correspondingMergeCell.html());
        }

        if (!checkedCheckboxesExistInTarget) {
            $summaryMergeCell.removeClass('target');
        } else {
            $summaryMergeCell.addClass('target');
        }

        if (!checkedCheckboxesExistInSources) {
            $summaryMergeCell.removeClass('source');
        } else {
            $summaryMergeCell.addClass('source');
        }

        if (!isExistenceSummaryCell) {
            if (isTargetField) {
                if ($checkbox.is(':checked')) {
                    $correspondingMergeCell.addClass('target');
                } else {
                    $correspondingMergeCell.removeClass('target');
                }
            } else {
                if ($checkbox.is(':checked')) {
                    $correspondingMergeCell.addClass('source');
                } else {
                    $correspondingMergeCell.removeClass('source');
                }
            }
        }

        // TODO - update result text
        if (!isExistenceSummaryCell) {
            if ($checkbox.is(':checked')) {
                $correspondingMergeCell.addClass('merged').addClass('positive');
                $correspondingMergeCell.html(detailCellHtml);
            } else if (!$row.find(':checked').length) {
                $correspondingMergeCell.removeClass('merged').removeClass('positive');
                $correspondingMergeCell.html($correspondingMergeCell.data('original-content'));
            }
        }

        $.each(checkboxesCheckedInDetailResultsByColIdx, function (idx, value) {
            var isTargetColIdx = idx === 1,
                hasOnlyTwoColumns = $('.source-site-header').length === 1,
                arrToPushOnto = isTargetColIdx ? summaryCellTargetHtmlArr : summaryCellSourceHtmlArr;

            if (typeof idx === 'undefined' || typeof value === 'undefined') {
                return true;
            }

            if (value === null || parseInt(value, 10) == 0) {

            } else {
                if (isTargetColIdx || hasOnlyTwoColumns) { // if there's only one site being compared on either side or if comparing target, then forgo outpuuting the site name
                    arrToPushOnto.push(value + '/' + totalCheckboxInColumnCount);
                } else {
                    arrToPushOnto.push(siteNamesByColIdx[idx] + ': ' + value + '/' + totalCheckboxInColumnCount);
                }
            }
        });

        $summaryMergeCell.html(
            '<span class="merge-result-summary-target">'
        +       summaryCellTargetHtmlArr.join('')
        +   '</span>'
        +   '<span class="merge-result-summary-source">'
        +       summaryCellSourceHtmlArr.join('<br>')
        +   '<span>'


        );

        if (hasAllDetailedRowsChecked) {
            $summaryMergeCell.addClass('positive');
        } else {
            $summaryMergeCell.removeClass('positive');
        }
    }

    function updateCheckbox (e, state, triggerOtherCheckboxesOnSummaryRow) {
        var isEvent = !!e.currentTarget,
            $checkbox = isEvent ? $(e.currentTarget) : e,
            isChecked = typeof state !== 'undefined' ? state : $checkbox.prop('checked'),
            $cell = $checkbox.closest('td'),
            $row = $checkbox.closest('tr'),
            isSummaryRow = !!$checkbox.closest('.comparison-summary').length,
            $comparisonSummaryRow,
            $comparisonResultsRow,
            colIdx = $cell.data('col-idx'),
            rowIdx = $row.data('row-idx'),
            summaryRowIdx,
            $colCheckboxes,
            $rowCheckboxes,
            $otherCheckboxesToTrigger,
            allCheckboxesInColumnAreChecked,
            $checkboxesToCheck,
            $checkboxesToUncheck
        ;

        triggerOtherCheckboxesOnSummaryRow = typeof triggerOtherCheckboxesOnSummaryRow === 'boolean' ? triggerOtherCheckboxesOnSummaryRow : true;

        if (isSummaryRow) {
            $comparisonSummaryRow = $checkbox.closest('.comparison-summary');
            $comparisonResultsRow = $comparisonSummaryRow.next('.comparison-details');
        } else {
            $comparisonResultsRow = $checkbox.closest('.comparison-details');
            $comparisonSummaryRow = $comparisonResultsRow.prev('.comparison-summary');
        }

        $checkbox.prop('checked', isChecked);

        if (isChecked) {
            $cell.addClass('clicked');
        } else {
            $cell.removeClass('clicked');
        }

        summaryRowIdx = $comparisonSummaryRow.data('row-idx');

        getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx);

        getResultCheckboxesByRowIdx(rowIdx);

        getSummaryCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx);

        calculateMergeResult($checkbox);

        $colCheckboxes = resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx];
        $rowCheckboxes = resultCheckboxesByRowIdx[rowIdx];

        if (isSummaryRow && triggerOtherCheckboxesOnSummaryRow) { // trigger entire column of checkboxes
            $otherCheckboxesToTrigger = $colCheckboxes;
            $otherCheckboxesToTrigger.each(function () {
                updateCheckbox($(this), isChecked, triggerOtherCheckboxesOnSummaryRow);
            });
        }

        if (isChecked) {
            if (isSummaryRow) { // all checkboxes in all adjacent columns must be unchecked
                $checkboxesToUncheck = $comparisonResultsRow.find('[data-col-idx]').not('[data-col-idx="' + colIdx + '"]').find(':checked');
            } else { // uncheck corresponding checkboxes in row
                $checkboxesToUncheck = $rowCheckboxes.filter(':checked').not($checkbox);

                allCheckboxesInColumnAreChecked = $colCheckboxes.length === $colCheckboxes.filter(':checked').length;

                if (allCheckboxesInColumnAreChecked) {
                    $checkboxesToCheck = summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx].filter(':not(":checked")');
                }
            }
        } else {
            if (!isSummaryRow) { // uncheck summary checkbox, since now not all of the fields in the column are checked
                $checkboxesToUncheck = summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx].filter(':checked');
                triggerOtherCheckboxesOnSummaryRow = false;
            }
        }

        if ($checkboxesToCheck) {
            $checkboxesToCheck.each(function () {
                updateCheckbox($(this), true, triggerOtherCheckboxesOnSummaryRow);
            });
        }

        if ($checkboxesToUncheck) {
            $checkboxesToUncheck.each(function () {
                updateCheckbox($(this), false, triggerOtherCheckboxesOnSummaryRow);
            });
        }
    }

    $comparisonCollectionTable.find('.decision-checkbox :checkbox').on('change', updateCheckbox);

    (function checkOffCellsThatHaveNoOtherOption() {
        $comparisonCollectionTable.find('.comparison-summary').each(function () {
            var $row = $(this),
                $checkboxes = $row.find(':checkbox'),
                $cell,
                hasOnlyOneCheckbox = $checkboxes.length === 1,// if only one checkbox in summary, no other option exists
                colIdx,
                summaryRowIdx = $row.data('row-idx'),
                isInTargetColumnOnly
            ;

            if (!hasOnlyOneCheckbox) {
                return true; // continue
            }

            $cell = $checkboxes.closest('td');
            colIdx = $cell.data('col-idx');

            isInTargetColumnOnly = parseInt(summaryRowIdx, 10) === 1;

            if (isInTargetColumnOnly) { // don't allow unchecking of local checkbox with no other option
                $checkboxes.prop('disabled', 'disabled');
            }

            updateCheckbox($checkboxes, true);

            getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx).prop('disabled', 'disabled');
        });
    }());
});