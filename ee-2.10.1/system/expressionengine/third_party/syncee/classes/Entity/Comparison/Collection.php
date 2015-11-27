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

class Syncee_Entity_Comparison_Collection extends Syncee_Collection_Abstract implements Syncee_Comparison_Result_Interface, Syncee_Comparison_Differ_Interface
{
    const RESULT_ENTITY_MISSING_IN_SOURCE_AND_TARGET = 'RESULT_ENTITY_MISSING_IN_SOURCE_AND_TARGET';
    const RESULT_ENTITY_MISSING_IN_SOURCE            = 'RESULT_ENTITY_MISSING_IN_SOURCE';
    const RESULT_ENTITY_MISSING_IN_TARGET            = 'RESULT_ENTITY_MISSING_IN_TARGET';
    const RESULT_ENTITY_EXISTS_IN_SOURCE_AND_TARGET  = 'RESULT_ENTITY_EXISTS_IN_SOURCE_AND_TARGET';

    /**
     * @var Syncee_Entity_Abstract
     */
    private $_source;

    /**
     * @var Syncee_Entity_Abstract
     */
    private $_target;

    private $_comparison_result;

    private $_unique_identifier_key_override;

    protected $_row_model = 'Syncee_Entity_Comparison';

    public function setSource(Syncee_Entity_Abstract $source)
    {
        $this->_source = $source;
        return $this;
    }

    public function setTarget(Syncee_Entity_Abstract $target)
    {
        $this->_target = $target;
        return $this;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function getTarget()
    {
        return $this->_target;
    }

    public function getComparisonResult()
    {
        if (!isset($this->_comparison_result)) {
            if ($this->_source->isEmptyRow() && $this->_target->isEmptyRow()) {
                $this->_comparison_result = self::RESULT_ENTITY_MISSING_IN_SOURCE_AND_TARGET;
            } elseif ($this->_source->isEmptyRow()) {
                $this->_comparison_result = self::RESULT_ENTITY_MISSING_IN_SOURCE;
            } elseif ($this->_target->isEmptyRow()) {
                $this->_comparison_result = self::RESULT_ENTITY_MISSING_IN_TARGET;
            } else {
                $this->_comparison_result = self::RESULT_ENTITY_EXISTS_IN_SOURCE_AND_TARGET;
            }
        }

        return $this->_comparison_result;
    }

    public function getTotalComparisonEntityCountByResult($comparison_results = array(), $exclude_ignored_and_hidden_columns = true, $exclude_primary_key_columns = true)
    {
        $total_comparison_entity_count = 0;

        if (!is_array($comparison_results)) {
            $comparison_results = (array) $comparison_results;
        }

        foreach ($comparison_results as $comparison_result) {
            if (!in_array($comparison_result, Syncee_Entity_Comparison::getComparisonResults())) {
                throw new Syncee_Exception('Result passed to ' . __METHOD__ . ' not in list of comparison results; result passed: '. print_r($comparison_result, true));
            }
        }

        if (empty($comparison_results)) {
            $comparison_results = Syncee_Entity_Comparison::getComparisonResults();
        }

        /**
         * @var $row Syncee_Entity_Comparison
         */
        foreach ($this->_rows as $row) {
            if ($exclude_ignored_and_hidden_columns &&
                ($row->comparateColumnIsIgnoredInComparison() || $row->comparateColumnIsHiddenInComparison())
            ) {
                continue;
            }

            if ($exclude_primary_key_columns && $row->comparateColumnIsPrimaryKey()) {
                continue;
            }

            if (in_array($row->getComparisonResult(), $comparison_results)) {
                $total_comparison_entity_count += 1;
            }
        }

        return $total_comparison_entity_count;
    }

    /**
     * @param bool|true $include_differing_entity_count_only
     * @param bool|true $exclude_ignored_columns
     * @return int
     * @throws Syncee_Exception
     * @deprecated - use self::getTotalComparisonEntityCountByResult
     */
    public function getTotalComparisonEntityCount($include_differing_entity_count_only = true, $exclude_ignored_columns = true)
    {
        if ($include_differing_entity_count_only) {
            $comparison_results_to_test = Syncee_Entity_Comparison::getDifferenceComparisonResults();
        } else {
            $comparison_results_to_test = Syncee_Entity_Comparison::getComparisonResults(); // all
        }

        return $this->getTotalComparisonEntityCountByResult($comparison_results_to_test, $exclude_ignored_columns);
    }

    /**
     * @param $comparate_column_name
     * @return Syncee_Entity_Comparison
     */
    public function getComparisonEntityByComparateColumnName($comparate_column_name)
    {
        /**
         * @var $row Syncee_Entity_Comparison
         */
        foreach ($this->_rows as $row) {
            if ($row->getComparateColumnName() === $comparate_column_name) {
                $comparison_entity = $row;
                break;
            }
        }

        if (!isset($comparison_entity)) {
            $comparison_entity = new Syncee_Entity_Comparison($this->_source, $this->_target);

            $comparison_entity
                ->setComparateColumnName($comparate_column_name)
                ->setComparateColumnExistsInSource(false)
                ->setComparateColumnExistsInTarget(false)
                ->getComparisonResult()
            ;
        }

        return $comparison_entity;
    }

    public function setUniqueIdentifierKey($unique_identifier_key)
    {
        $this->_unique_identifier_key_override = $unique_identifier_key;
        $this->getSource()->setUniqueIdentifierKey($unique_identifier_key);
        $this->getTarget()->setUniqueIdentifierKey($unique_identifier_key);
    }

    public function getUniqueIdentifierKey()
    {
        return $this->_unique_identifier_key_override ?: $this->getTarget()->getUniqueIdentifierKey();
    }

    public function getUniqueIdentifierValue()
    {
        return $this->getTarget()->getUniqueIdentifierValue() ?: $this->getSource()->getUniqueIdentifierValue();
    }

    public function hasNoDifferingComparisons()
    {
        $has_no_comparisons = true;

        /**
         * @var $row Syncee_Entity_Comparison
         */
        foreach ($this->_rows as $row) {
            if (!$row->hasNoDifferingComparisons()) {
                $has_no_comparisons = false;
                break;
            }
        }

        return $has_no_comparisons;
    }

    /**
     * @return Syncee_Entity_Comparison_Collection
     */
    public function getDifferingComparisonEntityCollection()
    {
        $differing_entities = array();

        /**
         * @var $row Syncee_Entity_Comparison
         */
        foreach ($this->_rows as $row) {
            if (!$row->hasNoDifferingComparisons()) {
                $differing_entities[] = $row;
            }
        }

        /**
         * @var $collection Syncee_Entity_Comparison_Collection
         */
        $collection = new $this($differing_entities);

        $collection->setSource($this->_source);
        $collection->setTarget($this->_target);
        $collection->setUniqueIdentifierKey($this->getUniqueIdentifierKey());

        return $collection;
    }
}