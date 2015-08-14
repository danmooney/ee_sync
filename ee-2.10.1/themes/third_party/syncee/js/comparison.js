$(function ($) {
    var $comparisonCollectionTable = $('.comparison-collection-table'),
        summaryCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByRowIdx = [],
        summaryRowIdxs = [],
        colIdxCount = $comparisonCollectionTable.children('thead').find('tr th').length
    ;

    $('[data-summary-row-idx]').each(function () {
        summaryRowIdxs.push($(this).data('summary-row-idx'));
    });

    summaryRowIdxs = $.unique(summaryRowIdxs);

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
        var $checkboxes = $();

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

    $comparisonCollectionTable.find('.comparison-summary').on('click', function (e) {
        if ($(e.target || e.srcElement).is(':input')) {
            return;
        }

        $(e.currentTarget).next('.comparison-details').find('.nested-table-container div').slideToggle();
    });

    function calculateMergeResult ($checkbox) {
        var $cell = $checkbox.closest('td'),
            $row  = $cell.closest('tr'),
            colIdx = $cell.data('col-idx'),
            rowIdx = $row.data('row-idx'),
            summaryRowIdx = $row.data('summary-row-idx') || rowIdx,
            $correspondingMergeCell = $cell.siblings('.merge-result'),
            isTargetField = $cell.hasClass('target-field'),
            isSourceField = !isTargetField,
            isExistenceSummaryCell = $cell.hasClass('comparison-site-collection-existence-container'),
            $summaryCheckbox = getSummaryCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx),
            $summaryMergeCell = $summaryCheckbox.closest('tr').find('.merge-result'),
            checkedCheckboxesExistInTarget = (
                getSummaryCheckboxesByColIdxAndSummaryRowIdx(1, summaryRowIdx).filter(':checked').length ||
                getResultCheckboxesByColIdxAndSummaryRowIdx(1, summaryRowIdx).filter(':checked').length
            ),
            checkedCheckboxesExistInSources = (
                getSourceCheckboxesBySummaryRowIdx(summaryRowIdx, summaryCheckboxesByColIdxAndSummaryRowIdx).filter(':checked').length ||
                getSourceCheckboxesBySummaryRowIdx(summaryRowIdx, resultCheckboxesByColIdxAndSummaryRowIdx).filter(':checked').length
            )
        ;

        if (!checkedCheckboxesExistInTarget) {
            $correspondingMergeCell.removeClass('left');
            $summaryMergeCell.removeClass('left');
        } else {
            $correspondingMergeCell.addClass('left');
            $summaryMergeCell.addClass('left');
        }

        if (!checkedCheckboxesExistInSources) {
            $correspondingMergeCell.removeClass('right');
            $summaryMergeCell.removeClass('right');
        } else {
            $correspondingMergeCell.addClass('right');
            $summaryMergeCell.addClass('right');
        }

        // TODO - update result text
        if (isExistenceSummaryCell) {
            //if (hasTargetCheckboxesChecked) {
            //    $summaryMergeCell.addClass('left');
            //}
            //
            //if (hasSourceCheckboxesChecked) {
            //    $summaryMergeCell.addClass('right');
            //}

        } else {

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
                updateCheckbox($checkboxesToUncheck, false, triggerOtherCheckboxesOnSummaryRow);
            });
        }
    }

    $comparisonCollectionTable.find('.decision-checkbox :checkbox').on('change', updateCheckbox);

    (function checkOffCellsThatHaveNoOtherOption() {
        $comparisonCollectionTable.find('.comparison-summary').each(function () {
            var $row = $(this),
                $checkboxes = $row.find(':checkbox'),
                $cell,
                hasOnlyOneCheckbox = $checkboxes.length === 1,
                colIdx,
                summaryRowIdx = $row.data('row-idx')
            ;

            if (!hasOnlyOneCheckbox) {
                return true; // continue
            }

            $cell = $checkboxes.closest('td');
            colIdx = $cell.data('col-idx');

            $checkboxes.prop('disabled', 'disabled');
            updateCheckbox($checkboxes, true);

            resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx].prop('disabled', 'disabled');
        });
    }());
});