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
    /**
     * @var Syncee_Site
     */
    private $_target_site;

    private $_unique_identifier_key_override;

    protected $_collection_model = 'Syncee_Entity_Comparison_Collection';

    public function __construct(array $collections = array())
    {
        $args = func_get_args();

        if (isset($args[1])) {
            $this->setTargetSite($args[1]);
        }

        if (isset($args[2])) {
            $this->setUniqueIdentifierKey($args[2]);
        }

        parent::__construct($collections);
    }

    public function setTargetSite(Syncee_Site $target_site)
    {
        $this->_target_site = $target_site;
    }

    public function getTargetSite()
    {
        return $this->_target_site;
    }

    /**
     * Override Syncee_Collection_Library_Abstract::appendToLibraryAsCollection to ensure that target inside $collection is identical to self::$_target
     * @param Syncee_Collection_Abstract $collection
     * @throws Syncee_Exception
     */
    public function appendToLibraryAsCollection(Syncee_Collection_Abstract $collection)
    {
        /**
         * @var $collection Syncee_Entity_Comparison_Collection
         */
        if ($collection->getTarget()->getSite() !== $this->_target_site) {
            throw new Syncee_Exception('Comparison collection passed to ' . __METHOD__ . ' must have target site identical to target site inside the containing library');
        }

        if (!$this->getUniqueIdentifierKey()) {
            $this->setUniqueIdentifierKey($collection->getUniqueIdentifierKey());
        } elseif ($this->getUniqueIdentifierKey() !== $collection->getUniqueIdentifierKey()) {
            throw new Syncee_Exception('Unique identifier key in collection passed to ' . __METHOD__ . ' is not the same as that inside the containing library');
        }

        parent::appendToLibraryAsCollection($collection);
    }

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

        return new $this($non_empty_collections, $this->_target_site, $this->_unique_identifier_key_override);
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

        // TODO - this really shouldn't be returning false, should it?
        return isset($collection)
            ? $collection
            : false
        ;
    }

    /**
     * @param $unique_identifier_value
     * @return bool|Syncee_Entity_Comparison_Collection
     * @deprecated - TODO - has to be deprecated because since we allow multiple sources to compare against, there may well be multiple collections that match the test in the written logic
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

    /**
     * @param $unique_identifier_key
     * @param $unique_identifier_value
     * @return Syncee_Entity_Comparison_Collection_Library
     */
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

        return new $this($matching_collections, $this->_target_site, $this->_unique_identifier_key_override);
    }

    public function getAllComparateColumnNames()
    {
        $comparate_column_names = array();

        /**
         * @var $collection Syncee_Entity_Comparison_Collection
         * @var $row Syncee_Entity_Comparison
         */
        foreach ($this->_collections as $collection) {
            foreach ($collection as $row) {
                $comparate_column_names[] = $row->getComparateColumnName();
            }
        }

        return $comparate_column_names;
    }

    public function getUniqueIdentifierKey()
    {
        return $this->_unique_identifier_key_override;
    }

    public function setUniqueIdentifierKey($unique_identifier_key)
    {
        $this->_unique_identifier_key_override = $unique_identifier_key;
    }
}