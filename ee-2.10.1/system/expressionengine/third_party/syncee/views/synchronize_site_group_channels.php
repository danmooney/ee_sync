<?php
/**
 * @var $syncee_site_group Syncee_Site_Group
 * @var $channel_comparison_library Syncee_Entity_Comparison_Collection_Library
 * @var $channel_comparison_collection Syncee_Entity_Comparison_Collection
 * @var $channel_comparison_entity Syncee_Entity_Comparison
 * @var $target_channel_entity Syncee_Entity_Channel
 */

$site_collection        = $syncee_site_group->getSiteCollection();
$remote_site_collection = $site_collection->filterByCondition(array('is_local' => false));
$local_site             = $site_collection->filterByCondition(array('is_local' => true), true);

$remote_site_collection->sortByCallback(function ($a, $b) {
    return $a->getPrimaryKeyValues(true) - $b->getPrimaryKeyValues(true);
});

if ($channel_comparison_library->hasNoComparisons()): ?>
    <p>They're the same!</p>
<?php
else:
    $outer_column_count             = count($channel_comparison_library) + 2;
    $unique_identifier_key          = $channel_comparison_library->getUniqueIdentifierKey();
    $unique_identifier_values       = $channel_comparison_library->getAllUniqueIdentifierValues(); // store array of all of the unique identifier values (channel_name, which is short name)
    $channel_comparate_column_names = $channel_comparison_library->getAllComparateColumnNames();

    sort($unique_identifier_values, SORT_STRING);

    $target_channel_entity          = $channel_comparison_library[0]->getTarget();
    ?>
    <table class="collection-table">
        <thead>
            <?php // output the target site name and all of the sources after ?>
            <tr>
                <th><span>Channel Short Name</span></th>
                <th><span><?= $local_site->title ?></span></th>
                <?php
                    foreach ($remote_site_collection as $remote_site): ?>
                        <th><span><?= $remote_site->title ?></span></th>
                <?php
                    endforeach ?>
            </tr>
        </thead>
        <tbody>
        <?php
            // iterate through all of the channels comparisons one by one, grouped by unique identifier value (channel_name, which is short name)
            foreach ($unique_identifier_values as $unique_identifier_value):
                $channel_comparison_library_with_unique_identifier_value = $channel_comparison_library->getComparisonLibraryByUniqueIdentifierKeyAndValue($unique_identifier_key, $unique_identifier_value);
                $target_has_entity_missing = $channel_comparison_library_with_unique_identifier_value[0]->getTarget()->isEmptyRow();
            ?>
                <?php /*
                <tr>
                    <td colspan="<?= $outer_column_count ?>" <?php /* rowspan="<?= count($channel_comparison_collection) ?>"  ?>>
                        <table>
                */ ?>
                            <tr>
                                <td>
                                    <span><?= $unique_identifier_value ?></span>
                                    <?php /*
                                    <table>
                                        <tbody>
                                            <?php
                                                foreach ($channel_comparison_collection as $channel_comparison_entity): ?>
                                                    <tr>
                                                        <td><?= $channel_comparison_entity->getComparateColumnName() ?></td>
                                                    </tr>
                                            <?php
                                                endforeach ?>
                                        </tbody>
                                    </table>*/ ?>
                                </td>
                                <td>
                                    <span><?= $target_has_entity_missing ? 'MISSING' : 'EXISTS' ?></span>
                                </td>

                                <?php
                                    foreach ($remote_site_collection as $remote_site):

                                    endforeach;

                                    foreach ($channel_comparison_library_with_unique_identifier_value as $channel_comparison_collection): ?>
                                        <td><span><?= $channel_comparison_collection->getSource()->isEmptyRow() ? 'MISSING' : 'EXISTS' ?></span></td>
                                <?php
                                    endforeach ?>
                            </tr>
                            <?php
                                foreach ($channel_comparate_column_names as $idx => $comparate_column_name): ?>
                                    <tr>
                                        <td><span><?= $comparate_column_name ?></span></td>
                                        <td>
                                            <span><?= Syncee_Helper::ifNull($channel_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name)->getTargetValue(), '<i>(NULL)</i>') ?></span>
                                        </td>
                                        <?php
                                            foreach ($channel_comparison_library_with_unique_identifier_value as $channel_comparison_collection): ?>
                                                <td>
                                                    <span><?= Syncee_Helper::ifNull($channel_comparison_collection->getComparisonEntityByComparateColumnName($comparate_column_name)->getSourceValue(), '<i>(NULL)</i>') ?></span>
                                                </td>
                                        <?php
                                            endforeach ?>
                                    </tr>
                            <?php
                                endforeach ?>
                            <?php /*
                                foreach ($channel_comparison_library_with_unique_identifier_value as $channel_comparison_collection): ?>
                                    <tr>
                                        <td><?= $channel_comparison_entity->getComparateColumnName() ?></td>
                                        <?php
                                            foreach ($channel_comparison_collection as $channel_comparison_entity): ?>
                                                <td><?= $channel_comparison_entity->getSourceValue() ?></td>
                                        <?php
                                            endforeach ?>
                                    </tr>
                            <?php
                                endforeach ?>

                        </table>
                    </td>
                </tr>*/ ?>
        <?php
            endforeach ?>
        </tbody>
    </table>
<?php
endif;