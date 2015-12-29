$(function ($) {

    var parentOptions = jQuery.extend(true, {}, Syncee.Mfp.optionsEditSymbol), // untouched parent options
        options = $.extend(true, jQuery.extend(true, {}, parentOptions), {
            events: {
                // TODO here - DRY
                'click $btnOk': function ($mergeResultValue, $mfpMergeResultValue, $editButton) {
                    var payloadValueObj = {};

                    $mfpMergeResultValue.each(function () {
                        var $valueInput = $(this),
                            isNull = $valueInput.closest('.syncee-merge-result-value').find(':checkbox').is(':checked')
                        ;

                        payloadValueObj[$valueInput.attr('name')] = isNull ? null : $valueInput.val();
                    });

                    $mergeResultValue.attr('data-value', JSON.stringify(payloadValueObj));

                    $editButton.trigger('merge-result-edited');

                    this.close();
                },
                'change $checkboxSetToNull': function ($checkboxSetToNull, $mfpMergeResultValue) {
                    var currentEvent = this.currentEvent;

                    $checkboxSetToNull = $checkboxSetToNull.filter(function () {
                        return this == currentEvent.target;
                    });

                    $mfpMergeResultValue = $checkboxSetToNull.closest('.syncee-merge-result-value').find(':input').not($checkboxSetToNull);

                    parentOptions.events['change $checkboxSetToNull']($checkboxSetToNull, $mfpMergeResultValue);
                },
                'keyup $mfpMergeResultValue': function ($mfpMergeResultValue, $checkboxSetToNull) {
                    var currentEvent = this.currentEvent;

                    $checkboxSetToNull = $checkboxSetToNull.filter(function () {
                        return this == $(currentEvent.target).closest('.syncee-merge-result-value').find(':checkbox').get(0);
                    });

                    $mfpMergeResultValue = $(currentEvent.target);

                    parentOptions.events['keyup $mfpMergeResultValue']($mfpMergeResultValue, $checkboxSetToNull);
                }
            },
            selectors: {
                $editButton: function ($valueEl) {
                    return $valueEl.closest('.merge-result').find('.merge-result-edit-symbol');
                },
                $valueEl: function ($valueEl) {
                    return $valueEl;
                },
                $mergeResultValuesContainer: function () {
                    return this.container.find('.syncee-merge-result-values-container');
                },
                $btnContainer: function () {
                    return this.container.find('.syncee-btn-container');
                }
            },
            inline: {
                markup: (
                    '<div class="syncee-popup">' +
                        '<div class="mfp-close"></div>' +
                        '<h1 class="mfp-uniqueIdentifierValue"></h1>' +
                        '<h2 class="mfp-comparateColumnName"></h2>' +
                        '<div class="syncee-merge-result-values-container"></div>' +
                        '<div class="syncee-btn-container syncee-btn-editable-container">' +
                            '<button class="syncee-btn syncee-btn-ok">OK</button>' +
                            '<button class="syncee-btn syncee-btn-cancel">Cancel</button>' +
                            '<button class="syncee-btn syncee-btn-clear-value">Clear Edited Values</button>' +
                        '</div>' +
                    '</div>'
                ),
                markupValueHeading: '<h3 class="syncee-value-heading"></h3>',
                markupTextarea: '<textarea class="syncee-merge-result-value-input"></textarea>',
                markupSetAsNullInput: '<input class="syncee-set-to-null" type="checkbox" name="set_to_null">',
                markupSetAsNullLabel: '<label>Set as <i>(NULL)</i></label>',
                markupSynceeBtnContainerUneditable: (
                    '<div class="syncee-btn-container syncee-btn-uneditable-container">' +
                        '<button class="syncee-btn syncee-btn-cancel">Close</button>' +
                    '</div>'
                )
            },
            callbacks: {
                open: function () {
                    var instance = this;

                    // assign remaining elements now that all of them exist at this point
                    this.st.init._assignElements.call(instance);

                    this.st.init._bindEvents.call(instance);

                    setTimeout(function () {
                        instance.elements.$mfpMergeResultValue.first().focus();
                    }, 100);
                },
                markupParse: function (template, mfpValues) {
                    var valueElObj,
                        dynamicallyCreatedElsArr = [],
                        that = this
                    ;

                    parentOptions.callbacks.markupParse.apply(this, arguments);

                    valueElObj = JSON.parse(this.elements.$valueEl.attr('data-value'));

                    // add textareas for each value
                    $.each(valueElObj, function (key, val) {
                        var $valueHeading = $(that.st.inline.markupValueHeading),
                            $textarea = $(that.st.inline.markupTextarea),
                            $nullInput = $(that.st.inline.markupSetAsNullInput),
                            $nullLabel = $(that.st.inline.markupSetAsNullLabel),
                            inputContainerArr = [],
                            shouldBeEditable = that.st.isMergeResultField
                        ;

                        inputContainerArr.push('<div class="syncee-merge-result-value">');

                        $valueHeading.text(key);
                        inputContainerArr.push($valueHeading.outerHTML());

                        // assign name/val to textarea
                        $textarea.attr('name', key);

                        if (val === null) {
                            $textarea.attr('placeholder', '(NULL)').closest('.syncee-merge-result-value');
                        } else {
                            $textarea.text(val).val(val);
                        }

                        // if shouldn't be editable, then set readonly and replace button container
                        if (!shouldBeEditable) {
                            $textarea.attr('readonly', 'readonly');
                            template.find(that.elements.$btnContainer.selector).replaceWith($(that.st.inline.markupSynceeBtnContainerUneditable))
                        } else {
                            $nullInput.attr('id',  'syncee-set-to-null-' + key);
                            $nullLabel.attr('for', 'syncee-set-to-null-' + key);

                            if (val === null) {
                                $nullInput.prop('checked', true).attr('checked', 'checked');
                            }

                            inputContainerArr = inputContainerArr.concat([
                                '<div class="syncee-set-to-null-container">',
                                    $nullInput.outerHTML(),
                                    $nullLabel.outerHTML(),
                                '</div>'
                            ]);
                        }

                        inputContainerArr = inputContainerArr.concat([
                            $textarea.outerHTML(),
                            '</div>' // .syncee-merge-result-value
                        ]);

                        dynamicallyCreatedElsArr = dynamicallyCreatedElsArr.concat(inputContainerArr);
                    });

                    template.find(this.elements.$mergeResultValuesContainer.selector).html(dynamicallyCreatedElsArr.join(''));
                }
            }
        })
    ;

    $(document).on('click', '.has-data-value', function (e) {
        e.mfpEl = this;

        options.isMergeResultField = !!$(this).closest('.merge-result').length;

        $.magnificPopup.instance._openClick(e, this, options);
    });
});