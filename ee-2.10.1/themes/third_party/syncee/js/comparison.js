$(function ($) {
    $('.comparison-collection-table').find('.comparison-summary').on('click', function (e) {
        $(e.currentTarget).next('.comparison-results').find('.nested-table-container div').slideToggle();
    });
});