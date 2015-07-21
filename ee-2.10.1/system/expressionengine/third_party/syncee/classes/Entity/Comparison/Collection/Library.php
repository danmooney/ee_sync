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

class Syncee_Entity_Comparison_Collection_Library extends Syncee_Collection_Library_Abstract
{
    protected $_collection_model = 'Syncee_Entity_Comparison_Collection';

    public function hasNoComparisons()
    {
        $has_no_comparisons = true;

        /**
         * @var $collection Syncee_Entity_Comparison_Collection
         */
        foreach ($this->_collections as $collection) {
            if (!$collection->isEmptyCollection()) {
                $has_no_comparisons = false;
                break;
            }
        }

        return $has_no_comparisons;
    }

    /**
     * @return Syncee_Entity_Comparison_Collection_Library
     */
    public function getNonEmptyComparisonCollectionLibrary()
    {
        $non_empty_collections = array();

        /**
         * @var $collection Syncee_Entity_Comparison_Collection
         */
        foreach ($this->_collections as $collection) {
            if (!$collection->isEmptyCollection()) {
                $non_empty_collections[] = $collection;
            }
        }

        return new $this($non_empty_collections);
    }

    public function getTotalComparisonEntityCountAcrossAllCollections()
    {
        $comparison_entity_count = 0;

        foreach ($this->_collections as $collection) {
            $comparison_entity_count += count($collection);
        }

        return $comparison_entity_count;
    }

    /**
     * @param Syncee_Entity_Abstract $source
     * @param Syncee_Entity_Abstract $target
     * @return Syncee_Entity_Comparison_Collection|bool
     */
    public function getComparisonCollectionBySourceAndTarget(Syncee_Entity_Abstract $source, Syncee_Entity_Abstract $target)
    {
        /**
         * @var $collection_to_test Syncee_Entity_Comparison_Collection
         */
        foreach ($this->_collections as $collection_to_test) {
            if ($collection_to_test->getSource() === $source &&
                $collection_to_test->getTarget() === $target
            ) {
                $collection = $collection_to_test;
                break;
            }
        }

        return isset($collection)
            ? $collection
            : false
        ;
    }

    /**
     * @param $unique_identifier_value
     * @return bool|Syncee_Entity_Comparison_Collection
     * @deprecated
     */
    public function getComparisonCollectionByUniqueIdentifierValue($unique_identifier_value)
    {
        /**
         * @var $collection_to_test Syncee_Entity_Comparison_Collection
         */
        foreach ($this->_collections as $collection_to_test) {
            $source_unique_value = $collection_to_test->getSource()->getUniqueIdentifierValue();
            $target_unique_value = $collection_to_test->getTarget()->getUniqueIdentifierValue();

            if (in_array($unique_identifier_value, array($source_unique_value, $target_unique_value), true)) {
                $collection = $collection_to_test;
                break;
            }
        }

        return isset($collection)
            ? $collection
            : false
        ;
    }

    public function getComparisonLibraryByUniqueIdentifierKeyAndValue($unique_identifier_key, $unique_identifier_value)
    {
        $matching_collections = array();

        /**
         * @var $collection Syncee_Entity_Comparison_Collection
         */
        foreach ($this->_collections as $collection) {
            if ($unique_identifier_key === $collection->getUniqueIdentifierKey() &&
                $unique_identifier_value === $collection->getUniqueIdentifierValue()
            ) {
                $matching_collections[] = $collection;
            }
        }

        return new $this($matching_collections);
    }
}