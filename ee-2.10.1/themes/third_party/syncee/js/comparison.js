$(function ($) {
    var $comparisonCollectionTable = $('.comparison-collection-table'),
        summaryCheckboxesByColIdx = [],
        resultCheckboxesByColIdx = [],
        resultCheckboxesByRowIdx = []
    ;

    $comparisonCollectionTable.find('.comparison-summary').on('click', function (e) {
        if ($(e.target || e.srcElement).is(':input')) {
            return;
        }

        $(e.currentTarget).next('.comparison-results').find('.nested-table-container div').slideToggle();
    });

    function calculateMergeResult () {
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
            $comparisonResultsRow = $comparisonSummaryRow.next('.comparison-results');
        } else {
            $comparisonResultsRow = $checkbox.closest('.comparison-results');
            $comparisonSummaryRow = $comparisonResultsRow.prev('.comparison-summary');
        }

        $checkbox.prop('checked', isChecked);

        if (isChecked) {
            $cell.addClass('clicked');
        } else {
            $cell.removeClass('clicked');
        }

        if (!resultCheckboxesByColIdx[colIdx]) {
            resultCheckboxesByColIdx[colIdx] = $comparisonResultsRow.find('[data-col-idx="' + colIdx + '"]').find(':checkbox');
        }

        if (!resultCheckboxesByRowIdx[rowIdx]) {
            resultCheckboxesByRowIdx[rowIdx] = $comparisonResultsRow.find('[data-row-idx="' + rowIdx + '"]').find(':checkbox');
        }

        if (!summaryCheckboxesByColIdx[colIdx]) {
            summaryCheckboxesByColIdx[colIdx] = $comparisonSummaryRow.find('[data-col-idx="' + colIdx + '"]').find(':checkbox');
        }

        $colCheckboxes = resultCheckboxesByColIdx[colIdx];
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
                    $checkboxesToCheck = summaryCheckboxesByColIdx[colIdx].filter(':not(":checked")');
                }
            }
        } else {
            if (!isSummaryRow) { // uncheck summary checkbox, since now not all of the fields in the column are checked
                $checkboxesToUncheck = summaryCheckboxesByColIdx[colIdx].filter(':checked');
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
});