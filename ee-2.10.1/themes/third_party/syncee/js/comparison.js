$(function ($) {
    var $comparisonCollectionTable = $('.comparison-collection-table'),
        summaryCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByRowIdx = []
    ;

    $comparisonCollectionTable.find('.comparison-summary').on('click', function (e) {
        if ($(e.target || e.srcElement).is(':input')) {
            return;
        }

        $(e.currentTarget).next('.comparison-details').find('.nested-table-container div').slideToggle();
    });

    function calculateMergeResult ($checkbox) {
        // TODO
    }

    function updateCheckbox (e, state, triggerOtherCheckboxesOnSummaryRow) {
        var isEvent = !!e.currentTarget,
            $checkbox = isEvent ? $(e.currentTarget) : e,
            isChecked = typeof state !== 'undefined' ? state : $checkbox.prop('checked'),
            $cell = $checkbox.closest('td, th'),
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

        calculateMergeResult($row);

        summaryRowIdx = $comparisonSummaryRow.data('row-idx');

        if (!resultCheckboxesByColIdxAndSummaryRowIdx[colIdx]) {
            resultCheckboxesByColIdxAndSummaryRowIdx[colIdx] = [];
        }

        if (!resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx]) {
            resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx] = $comparisonResultsRow.find('[data-col-idx="' + colIdx + '"]').find(':checkbox');
        }

        if (!resultCheckboxesByRowIdx[rowIdx]) {
            resultCheckboxesByRowIdx[rowIdx] = $comparisonResultsRow.find('[data-row-idx="' + rowIdx + '"]').find(':checkbox');
        }

        if (!summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx]) {
            summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx] = [];
        }

        if (!summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx]) {
            summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx] = $comparisonSummaryRow.find('[data-col-idx="' + colIdx + '"]').find(':checkbox');
        }

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
                hasOnlyOneCheckbox = $checkboxes.length === 1
            ;

            if (!hasOnlyOneCheckbox) {
                return true; // continue
            }

            updateCheckbox($checkboxes, true);
        });
    }());
});