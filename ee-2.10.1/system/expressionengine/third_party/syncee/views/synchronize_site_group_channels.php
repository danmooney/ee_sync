<?php
/**
 * @var $channel_comparison_library Syncee_Entity_Comparison_Collection_Library
 * @var $channel_comparison_collection Syncee_Entity_Comparison_Collection
 * @var $channel_comparison_entity Syncee_Entity_Comparison
 * @var $target_channel_entity Syncee_Entity_Channel
 */

//$sortChannelComparisonCollectionAlhabeticallyBySources = function ($a, $b) {
//    $c = 2;
//};

if ($channel_comparison_library->hasNoComparisons()): ?>
    <p>They're the same!</p>
<?php
else:
    $outer_column_count             = count($channel_comparison_library) + 2;
    $unique_identifier_key          = null;
    $unique_identifier_values       = array(); // store array of all of the unique identifier values (channel_name, which is short name)

    $channel_comparate_column_names = array();

    foreach ($channel_comparison_library as $channel_comparison_collection) {
        $unique_identifier_key      = $channel_comparison_collection->getUniqueIdentifierKey();
        $unique_identifier_values[] = $channel_comparison_collection->getUniqueIdentifierValue();
    }

    foreach ($channel_comparison_collection as $channel_comparison_entity) {
        $channel_comparate_column_names[] = $channel_comparison_entity->getComparateColumnName();
    }

    // remove duplicates from unique identifier values and sort alphabetically
    $unique_identifier_values = array_unique($unique_identifier_values);
    sort($unique_identifier_values, SORT_STRING);

    $target_channel_entity    = $channel_comparison_library[0]->getTarget();
    ?>
    <table class="collection-table">
        <thead>
            <?php // output the target site name and all of the sources after ?>
            <tr>
                <th>Channel Short Name</th>
                <th><?= $target_channel_entity->getSite()->title ?></th>
                <?php
                    foreach ($channel_comparison_library as $idx => $channel_comparison_collection): ?>
                        <th><?= $channel_comparison_collection->getSource()->getSite()->title ?></th>
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
                                    <?= $unique_identifier_value ?>
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
                                    <?= $target_has_entity_missing ? 'MISSING' : 'EXISTS' ?>
                                </td>

                                <?php
                                    foreach ($channel_comparison_library_with_unique_identifier_value as $channel_comparison_collection): ?>
                                        <td><?= $channel_comparison_collection->getSource()->isEmptyRow() ? 'MISSING' : 'EXISTS' ?></td>
                                <?php
                                    endforeach ?>
                            </tr>
                            <?php
                                foreach ($channel_comparate_column_names as $idx => $comparate_column_name): ?>
                                    <tr>
                                        <td><?= $comparate_column_name ?></td>
                                        <td><?= $channel_comparison_library_with_unique_identifier_value[0]->getComparisonEntityByComparateColumnName($comparate_column_name)->getTargetValue() ?></td>
                                        <?php
                                            foreach ($channel_comparison_library_with_unique_identifier_value as $channel_comparison_collection): ?>
                                                <td><?= $channel_comparison_collection->getComparisonEntityByComparateColumnName($comparate_column_name)->getSourceValue() ?></td>
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