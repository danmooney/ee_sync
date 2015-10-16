$(function ($, undefined) {
    var $comparisonCollectionTable = $('.comparison-collection-table'),
        totalEntityComparateColumnNames = $comparisonCollectionTable.data('total-entity-comparate-column-names'),
        summaryCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByColIdxAndSummaryRowIdx = [],
        resultCheckboxesByRowIdx = [],
        resultCheckboxesBySummaryRowIdx = [],
        summaryRowIdxs = [],
        siteNamesByColIdx = [],
        siteIdsByColIdx = [],
        colIdxCount = $comparisonCollectionTable.children('thead').find('tr th').length,
        $form = $comparisonCollectionTable.siblings('form'),
        $payloadHiddenInput = $form.find('[name="payload"]'),
        $displayOptionInputs = $comparisonCollectionTable.find('.display-options input')
    ;

    // TODO - if values are all the same in the comparison detail row, then check off the local column for that row, or mark it as a completely matching row and maybe have checkbox to show/hide those rows

    // get list of summary row indexes
    $('[data-summary-row-idx]').each(function () {
        summaryRowIdxs.push($(this).data('summary-row-idx'));
    });

    summaryRowIdxs = $.unique(summaryRowIdxs);


    // get list of site names and ids by col index
    $('[data-site-title]').each(function () {
        siteNamesByColIdx[$(this).data('col-idx')] = $(this).data('site-title');
        siteIdsByColIdx[$(this).data('col-idx')] = $(this).data('site-id');
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
            noActionableRowsExistInComparisonDetails,
            comparisonDetailsIsSlidUp
        ;

        if ($(e.target || e.srcElement).is(':input')) {
            return;
        }

        $comparisonDetails        = $comparisonSummary.nextAll('.comparison-details').first();

        noActionableRowsExistInComparisonDetails = !$comparisonDetails.find('tr').not('.display-option-data-no-action').length;

        if (noActionableRowsExistInComparisonDetails) {
            return;
        }

        comparisonDetailsIsSlidUp = $comparisonDetails.find('table').is(':hidden');

        $comparisonDetails.addClass('sliding').find('.nested-table-container div').slideToggle(undefined, function () {
            $comparisonDetails.removeClass('sliding');

            if (comparisonDetailsIsSlidUp) { // corresponding comparison details is about to be exposed; add sticky row data attribute to comparison summary
                $comparisonSummary.attr('data-sticky-table-row', 1);
                $comparisonDetails.addClass('slid-down').removeClass('slid-up');
            } else { // remove sticky row data attribute
                $comparisonSummary.removeAttr('data-sticky-table-row');
                $comparisonDetails.addClass('slid-up').removeClass('slid-down');
            }
        });
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
            $summaryRow = $summaryCheckbox.closest('tr'),
            $summaryMergeCell = $summaryRow.find('.merge-result'),
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
            totalCheckboxInColumnCount = totalEntityComparateColumnNames/*getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx).length*/,
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

            if ($checkbox.is(':checked')) {
                $correspondingMergeCell.addClass('merged').addClass('positive');
                $correspondingMergeCell.html(detailCellHtml);

                $row.attr('data-action-taken', 1);

            } else if (!$row.find(':checked').length) {
                $correspondingMergeCell.removeClass('merged').removeClass('positive');
                $correspondingMergeCell.html($correspondingMergeCell.data('original-content'));

                $row.removeAttr('data-action-taken');
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
                if (isTargetColIdx || hasOnlyTwoColumns) { // if there's only one site being compared on either side or if comparing target, then forgo outputting the site name
                    arrToPushOnto.push(value + '/' + totalCheckboxInColumnCount);
                } else {
                    arrToPushOnto.push(
                        '<span class="merge-result-summary-source-site">' + siteNamesByColIdx[idx] + ': ' + value + '/' + totalCheckboxInColumnCount + '</span>'
                    );
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

        Syncee.updateSummaryBasedOnRow($summaryRow, $summaryMergeCell.hasClass('positive'));
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
            $comparisonResultsRow = $comparisonSummaryRow.nextAll('.comparison-details').first();
        } else {
            $comparisonResultsRow = $checkbox.closest('.comparison-details');
            $comparisonSummaryRow = $comparisonResultsRow.prevAll('.comparison-summary').first();
        }

        if (!$checkbox.prop('disabled')) {
            $checkbox.prop('checked', isChecked);
        }

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
            $otherCheckboxesToTrigger = $colCheckboxes.not(':disabled');
            $otherCheckboxesToTrigger.each(function () {
                updateCheckbox($(this), isChecked, triggerOtherCheckboxesOnSummaryRow);
            });
        }

        if (isChecked) {
            if (isSummaryRow) { // all checkboxes in all adjacent columns must be unchecked
                $checkboxesToUncheck = $();

                getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx).each(function getOnlyCheckboxesFromRowsThatExistInThisColumn() {
                    var rowIdx = $(this).data('row-idx');

                    $checkboxesToUncheck = $checkboxesToUncheck.add($comparisonResultsRow.find('[data-row-idx="' + rowIdx + '"] td')).not('[data-col-idx="' + colIdx + '"]').find(':checked');
                });
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

        $displayOptionInputs.trigger('checkboxes:changed');
    }

    function updatePayloadData () {
        var payloadData = {},
            $resultCheckboxes,
            $summaryCheckbox,
            i,
            j,
            colIdx,
            summaryRowIdx,
            uniqueIdentifierKey,
            fieldName,
            siteId,
            isTargetColumn,
            checkboxesOnlyExistInTarget
        ;

        for (i = 0; i < resultCheckboxesByColIdxAndSummaryRowIdx.length; i += 1) {
            if (!resultCheckboxesByColIdxAndSummaryRowIdx[i]) {
                continue;
            }

            colIdx = i;
            siteId = siteIdsByColIdx[colIdx];

            for (j = 0; j < resultCheckboxesByColIdxAndSummaryRowIdx[i].length; j += 1) {
                if (!resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][j]) {
                    continue;
                }

                summaryRowIdx               = j;
                $resultCheckboxes           = resultCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx];
                $summaryCheckbox            = summaryCheckboxesByColIdxAndSummaryRowIdx[colIdx][summaryRowIdx];
                uniqueIdentifierKey         = $comparisonCollectionTable.find('tr[data-row-idx="' + summaryRowIdx + '"] .comparate-key-field-container').text().trim();
                isTargetColumn              = colIdx === 1;
                checkboxesOnlyExistInTarget = isTargetColumn && $summaryCheckbox.prop('disabled');

                if (isTargetColumn) { // no need to merge in anything that exists in target!
                    continue;
                }

                $resultCheckboxes.each(function (idx) {
                    var $resultCheckbox = $(this),
                        $row = $(this).closest('tr[data-row-idx]'),
                        isChecked = $resultCheckbox.prop('checked')
                    ;

                    if (!isChecked) {
                        return true; // continue
                    }

                    fieldName = $row.children('.comparate-key-field').text().trim();

                    if (typeof payloadData[uniqueIdentifierKey] === 'undefined') {
                        payloadData[uniqueIdentifierKey] = {};
                    }

                    payloadData[uniqueIdentifierKey][fieldName] = siteId;
                });
            }
        }

        console.dir(payloadData);
        $payloadHiddenInput.val(JSON.stringify(payloadData));
    }

    $comparisonCollectionTable.find('.decision-checkbox :checkbox').on('click', function (e) {
        updateCheckbox(e);
        updatePayloadData();
    });

    (function checkOffAndDisableCellsThatHaveNoOtherOption() {
        // check off summary rows that have no other option
        $comparisonCollectionTable.find('.comparison-summary').each(function () {
            var $row = $(this),
                $checkboxes = $row.find(':checkbox'),
                $cell,
                hasOnlyOneCheckbox = $checkboxes.filter(':visible').length === 1,// if only one checkbox in summary, no other option exists
                colIdx,
                summaryRowIdx = $row.data('row-idx'),
                isInTargetColumnOnly
            ;

            if (!hasOnlyOneCheckbox) { // if summary row has more than one checkbox in it, continue
                return true; // continue
            }

            $cell = $checkboxes.closest('td');
            colIdx = $cell.data('col-idx');

            isInTargetColumnOnly = parseInt(summaryRowIdx, 10) === 1;

            updateCheckbox($checkboxes, true);

            if (isInTargetColumnOnly) { // don't allow unchecking of local checkbox with no other option
                $checkboxes.prop('disabled', 'disabled');
            }

            updatePayloadData();

            getResultCheckboxesByColIdxAndSummaryRowIdx(colIdx, summaryRowIdx).prop('disabled', 'disabled');
        });

        $('.checkbox-no-action-needed').each(function () {
            $(this)
                .prop('checked', true)
                .prop('disabled', 'disabled')
            ;

            updateCheckbox($(this));
        });

        // check off detail rows that have no other option
        $comparisonCollectionTable.find('.comparison-details [data-row-idx]').each(function () {
            var $resultCheckboxes = $(this).find(':checkbox');

            if ($resultCheckboxes.filter(':visible').length === 1) {
                $resultCheckboxes.prop('checked', 'checked');

                updateCheckbox($resultCheckboxes);

                $resultCheckboxes.prop('disabled', 'disabled');
            }
        });

        updatePayloadData();
    }());

    $form.on('submit', function updatePayloadDataAndSubmit (e) {
        e.preventDefault();
        updatePayloadData();
        $form.off().submit();
    });

    // These inputs are hidden from the server side template because EE indiscriminately binds listeners to th :checkbox which alter the state of other checkboxes through triggering random events.
    // So we change them to checkboxes afterwards here so no EE events get bound to them, and then we bind display option row toggling event
    $displayOptionInputs.prop('type', 'checkbox').on('click init checkboxes:changed', function (e) {
        var $optionCheckbox = $(e.currentTarget),
            dataAttribute = $optionCheckbox.attr('id'),
            displayOptionClass = 'display-option-' + dataAttribute,
            $rowsToChange = $comparisonCollectionTable.find('tbody tr[' + dataAttribute + ']'),
            dataSummaryRowIdxsEvaluated = [],
            $rowsThatHaveDisplayOptionClassButMissingCorrespondingDataAttribute = $('.' + displayOptionClass).not('[' + dataAttribute + ']')
        ;

        if ($optionCheckbox.is(':checked')) {
            $rowsToChange.addClass(displayOptionClass);
        } else {
            $rowsToChange.removeClass(displayOptionClass);
        }

        $rowsThatHaveDisplayOptionClassButMissingCorrespondingDataAttribute.removeClass(displayOptionClass);

        // determine when all checkboxes in a column have no action required and either lock or hide the parent row
        $rowsToChange.each(function evaluateWhetherEntireRecordHasNoAction () {
            var $row = $(this),
                dataSummaryRowIdx = $row.data('summary-row-idx'),
                $summaryRow,
                recordAlreadyEvaluated = $.inArray(dataSummaryRowIdx, dataSummaryRowIdxsEvaluated) !== -1,
                $comparisonDetailsRow,
                entireRecordHasNoAction,
                $nestedContainerDiv
            ;

            if (recordAlreadyEvaluated) {
                return true; // continue
            }

            dataSummaryRowIdxsEvaluated.push(dataSummaryRowIdx);

            $comparisonDetailsRow = $row.closest('.comparison-details');
            entireRecordHasNoAction = $comparisonDetailsRow.find('tr[class^="display-option-data-"],tr[class*=" display-option-data-"]'/*'[data-no-action]'*/).length >= $comparisonDetailsRow.find('[data-summary-row-idx="' + dataSummaryRowIdx + '"]').length;

            $nestedContainerDiv = $comparisonDetailsRow.find('.nested-table-container div');

            if (entireRecordHasNoAction) {
                $summaryRow = $('[data-row-idx="' + dataSummaryRowIdx + '"]');
                $summaryRow.addClass('comparison-summary-no-action-needed');
                //$nestedContainerDiv.hide();
            } else {
                //$nestedContainerDiv.show();
            }
        });
    }).trigger('init');
});