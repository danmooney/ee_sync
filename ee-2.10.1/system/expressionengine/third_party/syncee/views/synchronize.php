<?php
/**
 * @var $site_collection Syncee_Site_Collection
 * @var $site_group Syncee_Site_Group
 * @var $entity_comparison_library Syncee_Entity_Comparison_Collection_Library
 * @var $entity_comparison_collection Syncee_Entity_Comparison_Collection
 * @var $entity_comparison Syncee_Entity_Comparison
 * @var $local_site Syncee_Site
 * @var $remote_site Syncee_Site
 * @var $synchronization_profile Syncee_Site_Synchronization_Profile
 * @var $remote_entity_name string
 */

$remote_site_collection = $site_collection->filterByCondition(array('is_local' => false));
$local_site             = $site_collection->filterByCondition(array('is_local' => true), true);

$remote_site_collection->sortByCallback(function ($a, $b) {
    return $a->getPrimaryKeyValues(true) - $b->getPrimaryKeyValues(true);
});

if ($entity_comparison_library->hasNoDifferingComparisons()):
    if (false/*!count($entity_comparison_library)*/): ?>

<?php
    else: ?>
        <p>They're the same!</p>
<?php
    endif;
endif;

if (!count($entity_comparison_library)) {
    return;
}

$outer_column_count             = count($entity_comparison_library) + 2;
$unique_identifier_key          = $entity_comparison_library->getUniqueIdentifierKey();
$unique_identifier_values       = $entity_comparison_library->getAllUniqueIdentifierValues(); // store array of all of the unique identifier values (channel_name, which is short name)
$entity_comparate_column_names  = $entity_comparison_library->getAllComparateColumnNames();
$total_columns                  = count($site_collection) + 2;

$unique_identifier_column_percentage_width = 10;
$other_columns_percentage_width            = round(
    (100 - $unique_identifier_column_percentage_width) / ($total_columns - 1),
    2
);

sort($unique_identifier_values, SORT_STRING);

$synchronize_profile_list_url = Syncee_Helper::createModuleCpUrl('viewSynchronizeProfileList', array_merge($site_group->getPrimaryKeyNamesValuesMap(), array(
    'comparator_library' => $synchronization_profile->comparator_library_class_name,
    'remote_entity'      => $synchronization_profile->entity_class_name
)));

?>

<br>

<table class="collection-table comparison-collection-table" data-sticky-table data-sticky-table-max-rows="3" data-resizable-table data-total-entity-comparate-column-names="<?= count($entity_comparate_column_names) ?>">
    <thead>
        <?php $row_idx = 0; $col_idx = 0; ?>
        <tr class="display-options" data-sticky-table-row>
            <th colspan="<?= $total_columns ?>">
                <label for="data-no-action">Only show rows where action can be taken</label>

                <?php // These inputs need to be hidden because EE indiscriminately binds listeners to th :checkbox which alter the state of other checkboxes through triggering random events ?>
                <input type="hidden" name="data-no-action" id="data-no-action">

                <label for="data-action-taken">Dim rows where action has been chosen</label>
                <input type="hidden" name="data-action-taken" id="data-action-taken">

                Back to Top

                Collapse All
            </th>
        </tr>
        <tr class="comparison-table-header-row" data-row-idx="<?= $row_idx++ ?>" data-sticky-table-row>
            <th class="comparate-column-header" style="width: <?= $unique_identifier_column_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>"><span><?= $unique_identifier_key ?></span></th>
            <th class="target-site-header" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>" data-site-title="<?= Syncee_Helper::xssCleanAndFormat($local_site->title) ?>" data-site-id="<?= $local_site->getPrimaryKeyValues(true) ?>">
                <span>
                    <?= $local_site->title ?> - <em>(Local Site)</em>
                </span>
            </th>
            <th class="merge-result-header" data-col-idx="<?= $col_idx++ ?>">
                <span>
                    Merge Result
                </span>
            </th>
            <?php
                foreach ($remote_site_collection as $remote_site): ?>
                    <th class="source-site-header" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>" data-site-title="<?= Syncee_Helper::xssCleanAndFormat($remote_site->title) ?>" data-site-id="<?= $remote_site->getPrimaryKeyValues(true) ?>">
                        <span>
                            <?php
                                $remote_site_title = $remote_site->isRemotePlaceholder()
                                    ? "<i>{$remote_site->title}</i>"
                                    : $remote_site->title
                                ;

                                echo Syncee_Helper::xssCleanAndFormat($remote_site_title);

                                if (!$remote_site->last_request_log->isSuccess()): ?>
                                    <br><br>
                                    <a class="warning" href="<?= Syncee_Helper::createModuleCpUrl('viewRequestLog', $remote_site->last_request_log->getPrimaryKeyNamesValuesMap()) ?>">Requests to this site contained errors!</a>
                            <?php
                                endif ?>
                        </span>
                    </th>
            <?php
                endforeach ?>
        </tr>
    </thead>
    <tbody>
    <?php
        // iterate through all of the comparisons one by one, grouped by unique identifier value (channel_name, for example, which is short name in channel comparisons)
        foreach ($unique_identifier_values as $unique_identifier_value):
            $entity_comparison_library_with_unique_identifier_value = $entity_comparison_library->getComparisonLibraryByUniqueIdentifierKeyAndValue($unique_identifier_key, $unique_identifier_value);
            $target_has_entity_missing = $entity_comparison_library_with_unique_identifier_value[0]->getTarget()->isEmptyRow();
            $col_idx = 0;
            $comparison_summary_row_idx = $row_idx++;
        ?>
        <tr class="comparison-summary" data-row-idx="<?= $comparison_summary_row_idx ?>" data-name="<?= Syncee_Helper::xssCleanAndFormat($unique_identifier_value) ?>">
            <td class="comparate-field-container comparate-key-field-container" data-col-idx="<?= $col_idx++ ?>">
                <span>
                    <?= $unique_identifier_value ?>
                    <span class="question-mark"></span>
                </span>
            </td>
            <td class="comparison-site-collection-existence-container target-field-container target-field" data-col-idx="<?= $col_idx++ ?>">
                <span>
                    <span class="diagnosis-<?= $target_has_entity_missing ? 'neutral' : 'positive' ?>">
                        <?= $target_has_entity_missing ? 'MISSING' : 'EXISTS' ?>
                    </span>
                    <?php
                        if (!$target_has_entity_missing): ?>
                            <span class="decision-checkbox">
                                <input type="checkbox">
                            </span>
                    <?php
                        endif ?>
                </span>
            </td>
            <td class="comparison-site-collection-merge-result-container merge-result" data-col-idx="<?= $col_idx++ ?>">
                <span></span>
            </td>
            <?php
                foreach ($remote_site_collection as $remote_site):
                    $entity_comparison_collection = $entity_comparison_library_with_unique_identifier_value->getComparisonCollectionBySourceSiteAndTargetSiteAndUniqueIdentifierValue($remote_site);
                    $source_has_entity_missing    = $entity_comparison_collection->getSource()->isEmptyRow();
                    $percentage_difference        = null;
                    ?>
                    <td class="comparison-site-collection-existence-container source-field-container comparison-site-field-container source-field" data-col-idx="<?= $col_idx++ ?>">
                        <span>
                            <span class="diagnosis-<?= $source_has_entity_missing ? 'neutral' : 'positive' ?>">
                                <?= $source_has_entity_missing ? 'MISSING' : 'EXISTS' ?>
                                <?php
                                    if (!$source_has_entity_missing && !$target_has_entity_missing):
                                        $total_columns_in_target    = count($entity_comparison_collection->getTarget()->toArray());
                                        $total_sameness_comparisons = $entity_comparison_collection->getTotalComparisonEntityCountByResult(Syncee_Entity_Comparison::getSamenessComparisonResults());
                                        $total_comparisons          = $entity_comparison_collection->getTotalComparisonEntityCountByResult();
                                        $percentage_difference      = intval(($total_sameness_comparisons / $total_comparisons) * 100);

                                        // determine whether PK differs from target (if it exists)
                                        $primary_key_names = $entity_comparison_collection->getTarget()->getActiveRecord()->getPrimaryKeyNames();

                                        $primary_key_differs_from_target = false;

                                        foreach ($primary_key_names as $primary_key_name) {
                                            $primary_key_differs_from_target = (
                                                $entity_comparison_collection->getComparisonEntityByComparateColumnName($primary_key_name)->getSourceValue() !== $entity_comparison_collection->getComparisonEntityByComparateColumnName($primary_key_name)->getTargetValue()
                                            );

                                            if ($primary_key_differs_from_target) {
                                                break;
                                            }
                                        }

                                        echo sprintf(
                                            '(%s%% match with %s%s)',
                                            $percentage_difference,
                                            $entity_comparison_collection->getTarget()->getSite()->title,
                                            $primary_key_differs_from_target ? ' - PK Differs' : ''
                                        );

//                                        echo sprintf(
//                                            '(%s %s; %s%% match with %s)',
//                                            $total_columns_in_target,
//                                            Syncee_Helper::pluralize($total_columns_in_target, 'column'),
//                                            $percentage_difference,
//                                            $entity_comparison_collection->getTarget()->getSite()->title
//                                        );
                                    endif ?>
                            </span>
                        <?php
                            if (!$source_has_entity_missing && (!isset($percentage_difference) || $percentage_difference < 100)): ?>
                                <span class="decision-checkbox">
                                    <input type="checkbox">
                                </span>
                        <?php
                            endif ?>
                        </span>
                    </td>
            <?php
                endforeach ?>
        </tr>

        <?php // BEGIN Comparison Details ?>
        <tr class="comparison-details" data-row-idx="<?= $row_idx++ ?>" data-name="<?= Syncee_Helper::xssCleanAndFormat($unique_identifier_value) ?>">
            <td colspan="<?= $total_columns ?>" class="nested-table-container">
                <div style="display: none;">
                    <table>
                        <?php
                            foreach ($entity_comparate_column_names as $comparate_column_name):
                                $col_idx = 0;
                                $entity_comparison        = $entity_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name);

                                if ($entity_comparison->comparateColumnIsHiddenInComparison()) {
                                    continue;
                                }

                                $entity_missing_in_target = $entity_comparison->isMissingInTarget();

                                // get all unique values from this comparate, but prepend primitive type to value so null doesn't get casted to empty string and such
                                $unique_values_for_this_comparate       = array_unique($entity_comparison_library_with_unique_identifier_value->getAllValuesByComparateColumnName($comparate_column_name, true));

                                $comparate_output_count_by_unique_value = array_combine(
                                    $unique_values_for_this_comparate,
                                    array_fill(0, count($unique_values_for_this_comparate), 0)
                                );

                                $entity_comparison_has_only_one_unique_value_or_less_across_all_sites = (
                                    count($unique_values_for_this_comparate) <= 1
                                );

                                $comparate_column_is_ignored_in_comparison         = $entity_comparison->comparateColumnIsIgnoredInComparison();
                                $comparate_column_is_primary_key                   = $entity_comparison->comparateColumnIsPrimaryKey();
                                $comparate_column_is_serialized_and_base64_encoded = $entity_comparison->comparateColumnIsSerializedAndBase64Encoded();

                                $checkbox_should_be_hidden_because_no_action_needs_to_be_taken = $entity_comparison_has_only_one_unique_value_or_less_across_all_sites || $comparate_column_is_primary_key;

                                if (!$entity_missing_in_target && null === $entity_comparison->getTargetValue()) {
                                    $target_value_to_output = '<i>(NULL)</i>';
                                } elseif (!$entity_missing_in_target && $comparate_column_is_serialized_and_base64_encoded) {
                                    $target_value_to_output = '<i>(Click to Inspect Values)</i>';
                                } else {
                                    $target_value_to_output = $entity_comparison->getTargetValue(false, true);
                                }

                                $comparate_column_class = 'comparate-column-container ';

                                if ($comparate_column_is_ignored_in_comparison) {
                                    $comparate_column_class .= 'comparate-column-ignored';
                                }

                                if ($comparate_column_is_primary_key) {
                                    $comparate_column_class .= 'comparate-column-primary-key';
                                }

                                if ($comparate_column_is_serialized_and_base64_encoded) {
                                    $comparate_column_class .= 'comparate-column-serialized-and-base64-encoded';
                                }

                                if ($comparate_column_class) {
                                    $comparate_column_class = sprintf('class="%s"', $comparate_column_class);
                                }

                                // increment unique value output counter
                                if (!$entity_comparison->isMissingInTarget()) {
                                    $comparate_output_count_by_unique_value[$entity_comparison->getTargetValue(true)] += 1;
                                }

                                ?>
                                <tr <?= $comparate_column_class ?> data-row-idx="<?= $row_idx++ ?>" data-name="<?= $comparate_column_name ?>" data-summary-row-idx="<?= $comparison_summary_row_idx ?>" <?= $comparate_column_is_ignored_in_comparison ? 'data-comparate-column-ignored' : '' ?> <?= $comparate_column_is_serialized_and_base64_encoded ? 'data-serialized-and-base64-encoded' : '' ?> <?= $checkbox_should_be_hidden_because_no_action_needs_to_be_taken ? 'data-no-action' : '' ?>>
                                    <td class="comparate-key-field" style="width: <?= $unique_identifier_column_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                        <span>
                                            <?= $comparate_column_name ?>
                                            <?php
                                                if ($comparate_column_is_ignored_in_comparison): ?>
                                                    <span title="This column is being ignored in comparison." class="comparate-column-ignored-symbol"></span>
                                            <?php
                                                endif ?>

                                            <?php
                                                if ($comparate_column_is_primary_key): ?>
                                                    <span title="This column is a primary key." class="comparate-column-primary-key-symbol"></span>
                                            <?php
                                                endif ?>
                                        </span>
                                    </td>
                                    <td class="target-field comparate-value-field <?= $entity_missing_in_target ? 'comparate-value-field-missing' : '' ?>" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                        <span>
                                            <span class="value<?= $comparate_column_is_serialized_and_base64_encoded ?  sprintf(' has-data-value" data-value="%s"', htmlspecialchars(json_encode($entity_comparison->getTargetValue(false, true))), ENT_QUOTES, 'UTF-8') : '"' ?>><?= Syncee_Helper::xssCleanAndFormat($target_value_to_output) ?></span>
                                            <?php
                                                if (!$entity_missing_in_target): ?>
                                                    <span class="decision-checkbox">
                                                        <input type="checkbox" <?= $checkbox_should_be_hidden_because_no_action_needs_to_be_taken ? 'class="checkbox-no-action-needed"' : '' ?>">
                                                    </span>
                                            <?php
                                                endif ?>
                                        </span>
                                    </td>
                                    <td class="merge-result" data-col-idx="<?= $col_idx++ ?>">
                                        <span>
                                            <span class="value"><i>(No Action Chosen)</i></span>
                                            <?php
                                                if ($comparate_column_name !== $unique_identifier_key): ?>
                                                    <span class="merge-result-edit-symbol" title="Click to edit this value"></span>
                                            <?php
                                                endif ?>
                                        </span>
                                        <input type="checkbox" class="merge-result-edit-checkbox">
                                    </td>
                                    <?php
                                        foreach ($entity_comparison_library_with_unique_identifier_value as $entity_comparison_collection):
                                            $entity_comparison        = $entity_comparison_collection->getComparisonEntityByComparateColumnName($comparate_column_name);
                                            $entity_missing_in_source = $entity_comparison->isMissingInSource();

                                            if (!$entity_missing_in_source && null === $entity_comparison->getSourceValue()) {
                                                $source_value_to_output = '<i>(NULL)</i>';
                                            } elseif (!$entity_missing_in_source && $comparate_column_is_serialized_and_base64_encoded) {
                                                $source_value_to_output = '<i>(Click to Inspect Values)</i>';
                                            } else {
                                                $source_value_to_output = $entity_comparison->getSourceValue(false, true);
                                            }

                                            $entity_exists_in_both_source_and_target = (
                                                !$entity_missing_in_source &&
                                                !$entity_missing_in_target
                                            );

                                            $source_value_same_as_target_value = (
                                                $entity_exists_in_both_source_and_target &&
                                                $source_value_to_output === $target_value_to_output
                                            );


                                            // increment unique value output counter
                                            if (!$entity_comparison->isMissingInSource()) {
                                                $comparate_output_count_by_unique_value[$entity_comparison->getSourceValue(true)] += 1;
                                                $unique_value_already_output_in_row = $comparate_output_count_by_unique_value[$entity_comparison->getSourceValue(true)] > 1;
                                            } else {
                                                $unique_value_already_output_in_row = false;
                                            }


                                            $column_is_not_primary_key_or_is_unique_primary_key_and_entity_is_missing_in_target = !$comparate_column_is_primary_key || ($comparate_column_is_primary_key && $entity_missing_in_target && !$unique_value_already_output_in_row);
                                            $checkbox_should_be_output = !$entity_missing_in_source && !$unique_value_already_output_in_row && !$source_value_same_as_target_value && $column_is_not_primary_key_or_is_unique_primary_key_and_entity_is_missing_in_target;

                                            if ($entity_exists_in_both_source_and_target && $column_is_not_primary_key_or_is_unique_primary_key_and_entity_is_missing_in_target) {
                                                $match_class = $source_value_same_as_target_value ? 'match-with-target' : 'no-match-with-target';
                                            } else {
                                                $match_class = '';
                                            }

                                            ?>
                                            <td class="source-field comparate-value-field <?= $match_class ?> <?= $entity_missing_in_source ? 'comparate-value-field-missing' : '' ?>" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                                <span>
                                                    <span class="value" <?= $comparate_column_is_serialized_and_base64_encoded ? sprintf(' has-data-value" data-value="%s"', htmlspecialchars(json_encode($entity_comparison->getSourceValue(false, true))), ENT_QUOTES, 'UTF-8') : '"' ?>><?= Syncee_Helper::xssCleanAndFormat($source_value_to_output) ?></span>
                                                    <?php
                                                        $checkbox_should_be_hidden_because_no_action_needs_to_be_taken = $entity_comparison_has_only_one_unique_value_or_less_across_all_sites;

                                                        if ($checkbox_should_be_output /*|| (!$comparate_column_is_primary_key)*/ /*&& !$entity_comparison_has_only_one_unique_value_or_less_across_all_sites*/ /*&& !$comparate_column_is_ignored_in_comparison*/ ): ?>
                                                            <span class="decision-checkbox">
                                                                <input type="checkbox" <?= $checkbox_should_be_hidden_because_no_action_needs_to_be_taken ? 'class="checkbox-no-action-needed"' : '' ?>">
                                                            </span>
                                                    <?php
                                                        endif ?>
                                                </span>
                                            </td>
                                    <?php
                                        endforeach ?>
                                </tr>
                        <?php
                            endforeach ?>
                    </table>
                </div>
            </td>
        </tr>
    <?php
        endforeach ?>
    </tbody>
</table>

<table class="summary-table">
    <thead>
        <tr>
            <th colspan="2">
                Action Summary
            </th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($unique_identifier_values as $unique_identifier_value): ?>
        <tr>
            <td class="site-name" data-name="<?= Syncee_Helper::xssCleanAndFormat($unique_identifier_value) ?>">
                <a href="#"><?= $unique_identifier_value ?></a>
            </td>
            <td class="summary"></td>
        </tr>
    <?php
    endforeach ?>
    </tbody>
</table>

<form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronizeFix', $synchronization_profile->getPrimaryKeyNamesValuesMap()) ?>">
    <button type="submit" class="btn">Synchronize</button>
    <input type="hidden" name="payload">
    <input type="hidden" name="post_max_size" value="<?= @Syncee_Helper::getBytesFromPossibleShorthand(ini_get('post_max_size')) ?>">
    <input type="hidden" name="max_input_vars" value="<?= @ini_get('max_input_vars') ?>">
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>