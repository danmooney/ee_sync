Syncee.updateSummaryBasedOnRow = function ($row, isMerged) {
    var $questionMark = $row.find('.question-mark');

    if (isMerged) {
        $questionMark.changeQtipContent('I am merged!');
    }
};