$(function ($) {

    var options = {
        init: {
            _assignElements: function () {
                var instance = this,
                    selectors = instance.st.selectors
                ;

                instance.elements = instance.elements || {};

                $.each(selectors, function (selector, selectorFn) {
                    // if element already assigned to elements object, then return (continue)
                    if (instance.elements[selector] && instance.elements[selector].length) {
                        return;
                    }

                    instance.elements[selector] = selectorFn.call(instance, instance.st.el);
                });
            },
            _bindEvents: function (unbind) {
                var instance = this;

                unbind = unbind || false;

                $.each(this.st.events, function (eventStr, callback) {
                    var eventStrSplit = eventStr.split(' '),
                        eventType = eventStrSplit.shift(),
                        selector = eventStrSplit.join(' '),
                        bindMethod = unbind ? 'off' : 'on',
                        $el = instance.elements[selector],
                        callbackParams = $.map(Syncee.Helper.getFunctionParametersFromFunction(callback), function (param) {
                            return instance.elements[param];
                        })
                    ;

                    if (!eventType) {
                        console.error('eventStr %s does not have a parseable eventType', eventStr);
                        return;
                    } else if (!$el) {
                        console.error('element %s does not exist in elements object', selector);
                        return;
                    }

                    $el[bindMethod](eventType, function () {
                        callback.apply(instance, callbackParams);
                    });
                });
            }
        },
        items: {},
        events: {
            'click $btnOk': function ($mergeResultValue, $mfpMergeResultValue, $editButton) {
                var valueIsSpecialAssignment = $mfpMergeResultValue.attr('placeholder') && !$mfpMergeResultValue.val().length;

                $editButton.trigger('merge-result-pre-edit');

                if (valueIsSpecialAssignment) {
                    $mergeResultValue.html('<i>' + $mfpMergeResultValue.attr('placeholder') + '</i>');
                } else {
                    $mergeResultValue.text($mfpMergeResultValue.val());
                }

                $editButton.trigger('merge-result-edited');
                this.close();
            },
            'click $btnCancel': function () {
                this.close();
            },
            'click $btnClear': function ($editButton) {
                $editButton.trigger('merge-result-reverted');
                this.close();
            },
            'change $checkboxSetToEmptyString': function () {}, // TODO
            'change $checkboxSetToNull': function ($checkboxSetToNull, $mfpMergeResultValue) {
                if ($checkboxSetToNull.is(':checked')) {
                    $mfpMergeResultValue.attr('data-overridden-content', $mfpMergeResultValue.val());
                    $mfpMergeResultValue.attr('placeholder', '(NULL)').val('');
                } else {
                    $mfpMergeResultValue.val($mfpMergeResultValue.attr('data-overridden-content')).removeAttr('placeholder');
                }
            },
            'keyup $mfpMergeResultValue': function ($mfpMergeResultValue, $checkboxSetToNull) {
                var mfpMergeResultValue = $mfpMergeResultValue.val();

                $mfpMergeResultValue.attr('data-overridden-content', mfpMergeResultValue);

                if (!$checkboxSetToNull.is(':checked')) {
                    $mfpMergeResultValue.removeAttr('placeholder');
                } else if (mfpMergeResultValue.length) {
                    $checkboxSetToNull.prop('checked', false).trigger('change');
                }
            }
        },
        elements: {},
        selectors: {
            $editButton: function ($editButton) {
                return $editButton;
            },
            $container: function () {
                return this.container;
            },
            $mergeResult: function ($editButton) {
                return $editButton.closest('.merge-result');
            },
            $mergeResultValue: function ($editButton) {
                return $editButton.closest('.merge-result').find('.value');
            },
            $mfpMergeResultValue: function () {
                return this.container.find('.mfp-mergeResultValue');
            },
            $checkboxSetToNull: function () {
                return this.container.find('#syncee-set-to-null');
            },
            $btnOk: function () {
                return this.container.find('.syncee-btn-ok');
            },
            $btnCancel: function () {
                return this.container.find('.syncee-btn-cancel');
            },
            $btnClear: function () {
                return this.container.find('.syncee-btn-clear-value');
            },
            $comparateColumnContainer: function ($editButton) {
                return $editButton.closest('.comparate-column-container');
            },
            $comparisonDetailsRow: function ($editButton) {
                return $editButton.closest('.comparison-details');
            }
        },
        inline: {
            markup: (
                // TODO - implement overflow support
                '<div class="syncee-popup">' +
                    '<div class="mfp-close"></div>' +
                    '<h1 class="mfp-uniqueIdentifierValue"></h1>' +
                    '<h2 class="mfp-comparateColumnName"></h2>' +
                    '<input id="syncee-set-to-null" type="checkbox" name="set_to_null">' +
                    '<label for="syncee-set-to-null">Set as <i>(NULL)</i></label>' +
                    '<textarea class="mfp-mergeResultValue"></textarea>' +
                    '<button class="syncee-btn syncee-btn-ok">OK</button>' +
                    '<button class="syncee-btn syncee-btn-cancel">Cancel</button>' +
                    '<button class="syncee-btn syncee-btn-clear-value">Clear Edited Value</button>' +
                '</div>'
            )
        },
        callbacks: {
            markupParse: function (template, values) {
                var instance = this,
                    $mergeResultValue
                ;

                // assign elements that already exist
                this.st.init._assignElements.call(instance);

                $mergeResultValue            = this.elements.$mergeResultValue;

                values.comparateColumnName   = this.elements.$comparateColumnContainer.data('name') || '';
                values.mergeResultValue      = $mergeResultValue.text();
                values.uniqueIdentifierValue = this.elements.$comparisonDetailsRow.data('name') || '';
            },
            open: function () {
                // focus textarea on open.  need to delay the focus here because there is a coinciding blur with the release of the mouse, or something of that nature that is overriding the focus
                var instance = this,
                    valueIsSpecialAssignment,
                    valueIsNullAssignment
                ;

                // assign remaining elements now that all of them exist at this point
                this.st.init._assignElements.call(instance);

                this.st.init._bindEvents.call(instance);

                valueIsSpecialAssignment = $.trim(this.elements.$mergeResultValue.html()).indexOf('<i>') === 0;
                valueIsNullAssignment    = valueIsSpecialAssignment && this.elements.$mergeResultValue.html().indexOf('NULL') !== -1;

                if (valueIsNullAssignment) {
                    this.elements.$mfpMergeResultValue.attr('placeholder', this.elements.$mfpMergeResultValue.val()).val('');
                    this.elements.$checkboxSetToNull.prop('checked', true).trigger('change');
                } else if (valueIsSpecialAssignment) {
                    this.elements.$mfpMergeResultValue.val(''); // set to empty string
                }

                setTimeout(function () {
                    instance.elements.$mfpMergeResultValue.focus();
                }, 100);
            },
            close: function () {
                this.st.init._bindEvents.call(this, true);
                this.elements = {};
            }
        }
    };

    $(document).on('click', '.merge-result-edit-symbol', function (e) {
        e.mfpEl = this;
        $.magnificPopup.instance._openClick(e, this, options);
    });
});