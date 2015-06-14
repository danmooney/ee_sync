<?php

if (!defined('SYNCEE_PATH')) {
    $current_dir = dirname(__FILE__);
    $i           = 1;

    while (($ancestor_realpath = realpath($current_dir . str_repeat('/..', $i++)) . '/_init.php') && !is_readable($ancestor_realpath)) {
        $is_at_root = substr($ancestor_realpath, -1) === PATH_SEPARATOR;
        if ($is_at_root) {
            break;
        }
    }

    if (!is_readable($ancestor_realpath)) {
        show_error('Could not find _init.php for module');
    }

    require_once $ancestor_realpath;
}

class Syncee_Entity_Comparator implements Syncee_Entity_Comparator_Interface
{
    public function compareEntities(Syncee_Entity_Abstract $source, Syncee_Entity_Abstract $target)
    {
        $comparison_collection = new Syncee_Entity_Comparison_Collection();

        $source_data = $source->toArray();
        $target_data = $target->toArray();

        $differences_from_source = array_diff_assoc($source_data, $target_data);

        foreach ($differences_from_source as $key => $value_in_source) {
            $comparison = new Syncee_Entity_Comparison($source, $target);

            $comparison->setComparateColumnName($key)->setSourceValue($value_in_source);

            $column_missing_in_target = !isset($target_data[$key]);

            if ($column_missing_in_target) {
                $comparison->setComparateColumnExistsInTarget(false);
            } else {
                $comparison->setTargetValue($target_data[$key]);
            }

            $comparison->getComparisonResult();

            $comparison_collection->appendToCollectionAsEntity($comparison);
        }

        $differences_from_target = array_diff_assoc($target_data, $source_data);

        foreach ($differences_from_target as $key => $value_in_target) {
            $column_missing_in_source = !isset($source_data[$key]);

            // the only comparisons we need to add in this loop are array keys that exist in target but not in source,
            // since they would've been overlooked in the first loop.
            // Return if column exists in both source and target since the comparison would be a redundant addition to the collection
            if (!$column_missing_in_source) {
                continue;
            }

            $comparison = new Syncee_Entity_Comparison($source, $target);
            $comparison->setComparateColumnName($key)->setTargetValue($value_in_target);

            $comparison->setComparateColumnExistsInSource(false);

            $comparison->getComparisonResult();

            $comparison_collection->appendToCollectionAsEntity($comparison);
        }

        return $comparison_collection;
    }
}