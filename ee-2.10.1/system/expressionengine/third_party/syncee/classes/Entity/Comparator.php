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
    /**
     * @param Syncee_Entity_Abstract $source
     * @param Syncee_Entity_Abstract $target
     * @return Syncee_Entity_Comparison_Collection
     * @throws Syncee_Exception
     */
    public function compareEntities(Syncee_Entity_Abstract $source, Syncee_Entity_Abstract $target)
    {
        $comparison_collection = new Syncee_Entity_Comparison_Collection();

        $comparison_collection
            ->setSource($source)
            ->setTarget($target)
        ;

        // store collection comparison result for easy inspection
        $comparison_collection->getComparisonResult();

        $source_data = $source->toArray();
        $target_data = $target->toArray();

        foreach ($source_data as $key => $value_in_source) {
            $comparison = new Syncee_Entity_Comparison($source, $target);
            $comparison->setComparateColumnName($key)->setSourceValue($value_in_source);

            $column_exists_in_target = array_key_exists($key, $target_data);

            if ($column_exists_in_target) {
                $comparison->setTargetValue($target_data[$key]);
            } else {
                $comparison->setComparateColumnExistsInTarget(false);
            }

            $comparison->getComparisonResult();

            $comparison_collection->appendToCollectionAsEntity($comparison);
        }

        // iterate through target data, since there may be columns in target that are missing in source
        foreach ($target_data as $key => $value_in_target) {
            $column_exists_in_source = array_key_exists($key, $source_data);

            // the only comparisons we need to add in this loop are array keys that exist in target but not in source,
            // since they would've been overlooked in the first loop.
            // Return if column exists in both source and target since the comparison would be a redundant addition to the collection
            if ($column_exists_in_source) {
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