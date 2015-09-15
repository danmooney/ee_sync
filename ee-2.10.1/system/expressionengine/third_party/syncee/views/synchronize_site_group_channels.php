<?php
/**
 * @var $site_collection Syncee_Site_Collection
 * @var $entity_comparison_library Syncee_Entity_Comparison_Collection_Library
 * @var $entity_comparison_collection Syncee_Entity_Comparison_Collection
 * @var $entity_comparison Syncee_Entity_Comparison
 * @var $local_site Syncee_Site
 * @var $remote_site Syncee_Site
 * @var $synchronization_profile Syncee_Site_Synchronization_Profile
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

?>

<div class="summary"> <?php // Possibly duplicate below table ?>
    <ul>
        <li>
            <a href="#" <?php // anchor is for going to ?>></a>
            Meteors channel is being merged into local site from EE Remote 3
        </li>
        <li>Planets requires your input</li>
    </ul>
</div>

<table class="collection-table comparison-collection-table" data-sticky-table data-sticky-table-max-rows="3" data-resizable-table>
    <thead>
        <?php $row_idx = 0; $col_idx = 0; ?>
        <tr data-sticky-table-row>
            <th colspan="<?= $total_columns ?>">
                <label for="a">Only show rows where action can be taken</label>
                <input type="checkbox" name="a" id="a">

                <label for="b">Only show rows where action still needs to be taken</label>
                <input type="checkbox" name="b" id="b">

                Back to Top

                Collapse All
            </th>
        </tr>
        <tr data-row-idx="<?= $row_idx++ ?>" data-sticky-table-row>
            <th class="comparate-column-header" style="width: <?= $unique_identifier_column_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>"><span><?= $unique_identifier_key ?></span></th>
            <th class="target-site-header" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>" data-site-title="<?= ee()->security->xss_clean($local_site->title) ?>" data-site-id="<?= $local_site->getPrimaryKeyValues(true) ?>">
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
                    <th class="source-site-header" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>" data-site-title="<?= ee()->security->xss_clean($remote_site->title) ?>" data-site-id="<?= $remote_site->getPrimaryKeyValues(true) ?>">
                        <span>
                            <?= $remote_site->title ?>
                            <?php
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
        // iterate through all of the channels comparisons one by one, grouped by unique identifier value (channel_name, which is short name)
        foreach ($unique_identifier_values as $unique_identifier_value):
            $entity_comparison_library_with_unique_identifier_value = $entity_comparison_library->getComparisonLibraryByUniqueIdentifierKeyAndValue($unique_identifier_key, $unique_identifier_value);
            $target_has_entity_missing = $entity_comparison_library_with_unique_identifier_value[0]->getTarget()->isEmptyRow();
            $col_idx = 0;
            $comparison_summary_row_idx = $row_idx++;
        ?>
        <tr class="comparison-summary" data-row-idx="<?= $comparison_summary_row_idx ?>">
            <td class="comparate-field-container comparate-key-field-container" data-col-idx="<?= $col_idx++ ?>">
                <span><?= $unique_identifier_value ?></span>
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
                <span>
                    &nbsp;
                </span>
            </td>
            <?php
                foreach ($remote_site_collection as $remote_site):
                    $entity_comparison_collection = $entity_comparison_library_with_unique_identifier_value->getComparisonCollectionBySourceSite($remote_site);
                    $source_has_entity_missing    = $entity_comparison_collection->getSource()->isEmptyRow();
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

                                        echo sprintf(
                                            '(%s%% match with %s)',
                                            $percentage_difference,
                                            $entity_comparison_collection->getTarget()->getSite()->title
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
                            if (!$source_has_entity_missing): ?>
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
        <tr class="comparison-details" data-row-idx="<?= $row_idx++ ?>">
            <td colspan="<?= $total_columns ?>" class="nested-table-container">
                <div style="display: none;">
                    <table>
                        <?php
                            foreach ($entity_comparate_column_names as $comparate_column_name):
                                $col_idx = 0;
                                $entity_comparison        = $entity_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name);
                                $entity_missing_in_target = $entity_comparison->isMissingInTarget();

                                $entity_comparison_has_only_one_unique_value_or_less = (
                                    count($entity_comparison_library_with_unique_identifier_value->getAllValuesByComparateColumnName($comparate_column_name, true)) <= 1 &&
                                    count($entity_comparison_library_with_unique_identifier_value->getAllValuesByComparateColumnName($comparate_column_name, false)) > 1
                                );

                                if (!$entity_missing_in_target && null === $entity_comparison->getTargetValue()) {
                                    $target_value_to_output = '<i>(NULL)</i>';
                                } else {
                                    $target_value_to_output = strlen(trim($entity_comparison->getTargetValue())) > 0 ? trim($entity_comparison->getTargetValue()) : '&nbsp;';
                                }

                                ?>
                                <tr data-row-idx="<?= $row_idx++ ?>" data-summary-row-idx="<?= $comparison_summary_row_idx ?>">
                                    <td class="comparate-key-field" style="width: <?= $unique_identifier_column_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>"><span><?= $comparate_column_name ?></span></td>
                                    <td class="target-field comparate-value-field <?= $entity_missing_in_target ? 'comparate-value-field-missing' : '' ?>" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                        <span>
                                            <span class="value">
                                                <?= ee()->security->xss_clean($target_value_to_output) ?>
                                            </span>
                                            <?php
                                                if (!$entity_missing_in_target): ?>
                                                    <span class="decision-checkbox">
                                                        <input type="checkbox">
                                                    </span>
                                            <?php
                                                endif ?>
                                        </span>
                                    </td>
                                    <td class="merge-result" data-col-idx="<?= $col_idx++ ?>">
                                        <span>
                                            <i>(No Action Chosen)</i>
                                        </span>
                                    </td>
                                    <?php
                                        foreach ($entity_comparison_library_with_unique_identifier_value as $entity_comparison_collection):
                                            $entity_comparison        = $entity_comparison_collection->getComparisonEntityByComparateColumnName($comparate_column_name);
                                            $entity_missing_in_source = $entity_comparison->isMissingInSource();

                                            if (!$entity_missing_in_source && null === $entity_comparison->getSourceValue()) {
                                                $source_value_to_output = '<i>(NULL)</i>';
                                            } else {
                                                $source_value_to_output = strlen(trim($entity_comparison->getSourceValue())) > 0 ? trim($entity_comparison->getSourceValue()) : '&nbsp;';
                                            }

                                            $entity_exists_in_both_source_and_target = (
                                                !$entity_missing_in_source &&
                                                !$entity_missing_in_target
                                            );

                                            $source_value_same_as_target_value = (
                                                $entity_exists_in_both_source_and_target &&
                                                $source_value_to_output === $target_value_to_output
                                            );

                                            if ($entity_exists_in_both_source_and_target) {
                                                $match_class = $source_value_same_as_target_value ? 'match-with-target' : 'no-match-with-target';
                                            } else {
                                                $match_class = '';
                                            }

                                            ?>
                                            <td class="source-field comparate-value-field <?= $match_class ?> <?= $entity_missing_in_source ? 'comparate-value-field-missing' : '' ?>" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                                <span>
                                                    <span class="value">
                                                        <?= ee()->security->xss_clean($source_value_to_output) ?>
                                                    </span>
                                                    <?php
                                                        if (!$entity_missing_in_source && !$entity_comparison_has_only_one_unique_value_or_less && !$source_value_same_as_target_value): ?>
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

<form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroupChannelsFix', $synchronization_profile->getPrimaryKeyNamesValuesMap()) ?>">
    <button type="submit" class="btn">Synchronize</button>
    <input type="hidden" name="payload"> <?php // TODO - populate this nonsense with json so we don't hit input_max_vars ?>
    <input type="hidden" name="post_max_size" value="<?= @Syncee_Helper::getBytesFromPossibleShorthand(ini_get('post_max_size')) ?>">
    <input type="hidden" name="max_input_vars" value="<?= @ini_get('max_input_vars') ?>">
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>