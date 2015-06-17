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

class Syncee_Entity_Comparison_Collection extends Syncee_Collection_Abstract implements Syncee_Comparison_Result_Interface
{
    const RESULT_ENTITY_MISSING_IN_SOURCE           = 'RESULT_ENTITY_MISSING_IN_SOURCE';
    const RESULT_ENTITY_MISSING_IN_TARGET           = 'RESULT_ENTITY_MISSING_IN_TARGET';
    const RESULT_ENTITY_EXISTS_IN_SOURCE_AND_TARGET = 'RESULT_ENTITY_EXISTS_IN_SOURCE_AND_TARGET';

    /**
     * @var Syncee_Entity_Abstract
     */
    private $_source;

    /**
     * @var Syncee_Entity_Abstract
     */
    private $_target;

    private $_comparison_result;

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
            if ($this->_source->isEmptyRow()) {
                $this->_comparison_result = self::RESULT_ENTITY_MISSING_IN_SOURCE;
            } elseif ($this->_target->isEmptyRow()) {
                $this->_comparison_result = self::RESULT_ENTITY_MISSING_IN_TARGET;
            } else {
                $this->_comparison_result = self::RESULT_ENTITY_EXISTS_IN_SOURCE_AND_TARGET;
            }
        }

        return $this->_comparison_result;
    }

    /**
     * @param $comparate_column_name
     * @return bool|Syncee_Entity_Comparison
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

        return isset($comparison_entity)
            ? $comparison_entity
            : false
        ;
    }
}