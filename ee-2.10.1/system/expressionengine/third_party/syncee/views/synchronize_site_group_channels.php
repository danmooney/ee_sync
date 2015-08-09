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
$total_columns                  = count($site_collection) + 1;

$unique_identifier_column_percentage_width = 10;
$other_columns_percentage_width            = round(
    (100 - $unique_identifier_column_percentage_width) / ($total_columns - 1),
    2
);

sort($unique_identifier_values, SORT_STRING);

?>
<table class="collection-table comparison-collection-table">
    <thead>
        <?php // output the target site name and all of the sources after ?>
        <tr>
            <th class="comparate-column-header" style="width: <?= $unique_identifier_column_percentage_width ?>%"><span><?= $unique_identifier_key ?></span></th>
            <th class="target-site-header" style="width: <?= $other_columns_percentage_width ?>%">
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
            <?php
                foreach ($remote_site_collection as $remote_site): ?>
                    <th class="source-site-header" style="width: <?= $other_columns_percentage_width ?>%">
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
        ?>
        <tr class="comparison-summary">
            <td class="comparate-field-container comparate-key-field-container">
                <span><?= $unique_identifier_value ?></span>
            </td>
            <td class="target-field-container target-field">
                <span>
                    <span class="diagnosis-<?= $target_has_entity_missing ? 'negative' : 'positive' ?>"><?= $target_has_entity_missing ? 'MISSING' : 'EXISTS' ?></span>
                </span>
            </td>

            <?php
                foreach ($remote_site_collection as $remote_site):
                    $source_has_entity_missing = $entity_comparison_library_with_unique_identifier_value->getComparisonCollectionBySourceSite($remote_site)->getSource()->isEmptyRow();
                    ?>
                    <td class="source-field-container source-field">
                        <span>
                            <span class="diagnosis-<?= $source_has_entity_missing ? 'negative' : 'positive' ?>"><?= $source_has_entity_missing ? 'MISSING' : 'EXISTS' ?></span>
                        </span>
                    </td>
            <?php
                endforeach ?>
        </tr>
        <tr class="comparison-results">
            <td colspan="<?= $total_columns ?>" class="nested-table-container">
                <div style="display: none;">
                    <table>
                        <?php
                            foreach ($entity_comparate_column_names as $idx => $comparate_column_name): ?>
                                <tr>
                                    <td class="comparate-key-field" style="width: <?= $unique_identifier_column_percentage_width ?>%"><span><?= $comparate_column_name ?></span></td>
                                    <td class="target-field comparate-value-field" style="width: <?= $other_columns_percentage_width ?>%">
                                        <span class="value">
                                            <?= Syncee_Helper::ifNull($entity_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name)->getTargetValue(), '<i>(NULL)</i>') ?>
                                        </span>
                                        <span class="decision-checkbox">
                                            <input type="checkbox">
                                        </span>
                                    </td>
                                    <?php
                                        foreach ($entity_comparison_library_with_unique_identifier_value as $entity_comparison_collection): ?>
                                            <td class="source-field comparate-value-field" style="width: <?= $other_columns_percentage_width ?>%">
                                                <span class="value">
                                                    <?= Syncee_Helper::ifNull($entity_comparison_collection->getComparisonEntityByComparateColumnName($comparate_column_name)->getSourceValue(), '<i>(NULL)</i>') ?>
                                                </span>
                                                <span class="decision-checkbox">
                                                    <input type="checkbox">
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