<?php
/**
 * @var $syncee_site_group Syncee_Site_Group
 * @var $entity_comparison_library Syncee_Entity_Comparison_Collection_Library
 * @var $entity_comparison_collection Syncee_Entity_Comparison_Collection
 * @var $local_site Syncee_Site
 * @var $remote_site Syncee_Site
 */

$site_collection        = $syncee_site_group->getSiteCollection();
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
<table class="collection-table comparison-collection-table">
    <thead>
        <?php $row_idx = 0; $col_idx = 0; ?>
        <tr data-row-idx="<?= $row_idx++ ?>">
            <th class="comparate-column-header" style="width: <?= $unique_identifier_column_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>"><span><?= $unique_identifier_key ?></span></th>
            <th class="target-site-header" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                <span>
                    <?= $local_site->title ?> - <em>(Local Site)</em>
                    <?php
                        if (!$local_site->last_request_log->isSuccess()): ?>
                            <br><br>
                            <a class="warning" href="<?= Syncee_Helper::createModuleCpUrl('viewRequestLog', $remote_site->last_request_log->getPrimaryKeyNamesValuesMap()) ?>">Requests to this site contained errors!</a>
                    <?php
                        endif ?>
                </span>
            </th>
            <th>
                <span>
                    Merge Result
                </span>
            </th>
            <?php
                foreach ($remote_site_collection as $remote_site): ?>
                    <th class="source-site-header" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
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
                    <span class="diagnosis-<?= $target_has_entity_missing ? 'negative' : 'positive' ?>">
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
                    Merge Result Summary
                </span>
            </td>
            <?php
                foreach ($remote_site_collection as $remote_site):
                    $entity_comparison_collection = $entity_comparison_library_with_unique_identifier_value->getComparisonCollectionBySourceSite($remote_site);
                    $source_has_entity_missing    = $entity_comparison_collection->getSource()->isEmptyRow();
                    ?>
                    <td class="comparison-site-collection-existence-container source-field-container comparison-site-field-container source-field" data-col-idx="<?= $col_idx++ ?>">
                        <span>
                            <span class="diagnosis-<?= $source_has_entity_missing ? 'negative' : 'positive' ?>">
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
                                $entity_missing_in_target = $entity_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name)->getComparisonResult() === Syncee_Entity_Comparison::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET ?>
                                <tr data-row-idx="<?= $row_idx++ ?>" data-summary-row-idx="<?= $comparison_summary_row_idx ?>">
                                    <td class="comparate-key-field" style="width: <?= $unique_identifier_column_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>"><span><?= $comparate_column_name ?></span></td>
                                    <td class="target-field comparate-value-field" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                        <span class="value">
                                            <?= Syncee_Helper::ifNull($entity_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name)->getTargetValue(), '<i>(NULL)</i>') ?>
                                        </span>
                                        <?php
                                            if (!$entity_missing_in_target): ?>
                                                <span class="decision-checkbox">
                                                    <input type="checkbox">
                                                </span>
                                        <?php
                                            endif ?>
                                    </td>
                                    <td class="merge-result" data-col-idx="<?= $col_idx++ ?>">
                                        <span>
                                            <i>(Action Required)</i>
                                        </span>
                                    </td>
                                    <?php
                                        foreach ($entity_comparison_library_with_unique_identifier_value as $entity_comparison_collection):
                                            $entity_comparison        = $entity_comparison_collection->getComparisonEntityByComparateColumnName($comparate_column_name);
                                            $entity_missing_in_source = $entity_comparison->getComparisonResult() === Syncee_Entity_Comparison::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE ?>
                                            <td class="source-field comparate-value-field" style="width: <?= $other_columns_percentage_width ?>%" data-col-idx="<?= $col_idx++ ?>">
                                                <span class="value">
                                                    <?= Syncee_Helper::ifNull($entity_comparison->getSourceValue(), '<i>(NULL)</i>') ?>
                                                </span>
                                                <?php
                                                    if (!$entity_missing_in_source): ?>
                                                        <span class="decision-checkbox">
                                                            <input type="checkbox">
                                                        </span>
                                                <?php
                                                    endif ?>
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