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

class Syncee_Entity_Comparison extends Syncee_Entity_Abstract
{
    const RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE = 'RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE';
    const RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET = 'RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET';
    const RESULT_COMPARATE_VALUE_DIFFERS            = 'RESULT_COMPARATE_VALUE_DIFFERS';

    private $_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_VALUE_DIFFERS
    );

    /**
     * @var string
     */
    private $_comparison_result;

    /**
     * @var Syncee_Entity_Abstract
     */
    private $_source;

    /**
     * @var Syncee_Entity_Abstract
     */
    private $_target;

    /**
     * @var mixed
     */
    private $_source_value;

    /**
     * @var bool
     */
    private $_comparate_column_exists_in_source = true;

    /**
     * @var mixed
     */
    private $_target_value;

    /**
     * @var bool
     */
    private $_comparate_column_exists_in_target = true;

    /**
     * @var string
     */
    private $_comparate_column_name;

    /**
     * @var Syncee_Entity_Comparison_Fix_Generic
     */
    private $_fix;

    public function __construct(Syncee_Entity_Abstract $source, Syncee_Entity_Abstract $target)
    {
        $this->_source = $source;
        $this->_target = $target;
    }

    public function setComparateColumnName($comparate_column_name)
    {
        $this->_comparate_column_name = $comparate_column_name;
        return $this;
    }

    public function getComparateColumnName()
    {
        return $this->_comparate_column_name;
    }

    public function setSourceValue($source_value)
    {
        $this->_source_value = $source_value;
        return $this;
    }

    public function getSourceValue()
    {
        return $this->_source_value;
    }

    public function setTargetValue($target_value)
    {
        $this->_target_value = $target_value;
        return $this;
    }

    public function getTargetValue()
    {
        return $this->_target_value;
    }

    /**
     * @param bool $comparate_column_exists_in_source
     */
    public function setComparateColumnExistsInSource($comparate_column_exists_in_source)
    {
        $this->_comparate_column_exists_in_source = $comparate_column_exists_in_source;
    }

    /**
     * @param bool $comparate_column_exists_in_target
     */
    public function setComparateColumnExistsInTarget($comparate_column_exists_in_target)
    {
        $this->_comparate_column_exists_in_target = $comparate_column_exists_in_target;
    }

    public function getComparisonResult()
    {
        if (!isset($this->_comparison_result)) {
            if (!$this->_comparate_column_exists_in_source) {
                $this->_comparison_result = self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE;
            } elseif (!$this->_comparate_column_exists_in_target) {
                $this->_comparison_result = self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET;
            } else {
                $this->_comparison_result = self::RESULT_COMPARATE_VALUE_DIFFERS;
            }
        }

        return $this->_comparison_result;
    }

    public function getFix()
    {
        if (!$this->_fix) {
            $possible_fix_class_name = 'Syncee_Entity_Comparison_Fix_Field_' . $this->_comparate_column_name;
            if (class_exists($possible_fix_class_name)) {
                $this->_fix = new $possible_fix_class_name($this);
            } else {
                $this->_fix = new Syncee_Entity_Comparison_Fix_Generic($this);
            }
        }

        return $this->_fix;
    }

    public function getUniqueIdentifierValue()
    {
        return md5(
            serialize($this->_source_value)
        .   serialize($this->_target_value)
        .   serialize($this->getComparateColumnName())
        .   $this->getComparisonResult()
        .   serialize($this->_source->toArray())
        .   serialize($this->_target->toArray())
        );
    }
}