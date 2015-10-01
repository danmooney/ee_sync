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

class Syncee_Entity_Comparison extends Syncee_Entity_Abstract implements Syncee_Comparison_Result_Interface, Syncee_Entity_Comparison_Interface, Syncee_Comparison_Differ_Interface
{
    const RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET = 'RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET';
    const RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE            = 'RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE';
    const RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET            = 'RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET';
    const RESULT_COMPARATE_VALUE_DIFFERS                       = 'RESULT_COMPARATE_VALUE_DIFFERS';
    const RESULT_COMPARATE_VALUE_SAME                          = 'RESULT_COMPARATE_VALUE_SAME';

    private static $_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_VALUE_DIFFERS,
        self::RESULT_COMPARATE_VALUE_SAME
    );

    private static $_sameness_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
        self::RESULT_COMPARATE_VALUE_SAME
    );

    private static $_difference_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_VALUE_DIFFERS,
    );

    private static $_missing_in_target_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
    );

    private static $_missing_in_source_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
    );

    /**
     * @var string
     */
    private $_comparison_result;

    /**
     * @var Syncee_Entity_Comparate_Abstract
     */
    private $_source;

    /**
     * @var Syncee_Entity_Comparate_Abstract
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

    /**
     * @var bool
     */
    private $_column_is_ignored_in_comparison;

    public function __construct(Syncee_Entity_Abstract $source, Syncee_Entity_Abstract $target)
    {
        $this->_source = $source;
        $this->_target = $target;
    }

    public static function getComparisonResults()
    {
        return self::$_comparison_results;
    }

    public static function getSamenessComparisonResults()
    {
        return self::$_sameness_comparison_results;
    }

    public static function getDifferenceComparisonResults()
    {
        return self::$_difference_comparison_results;
    }

    public function setComparateColumnName($comparate_column_name)
    {
        $this->_comparate_column_name = $comparate_column_name;
        $this->comparateColumnIsIgnoredInComparison();
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

    public function getSourceValue($type_prepended = false)
    {
        if ($type_prepended) {
            return gettype($this->_source_value) . $this->_source_value;
        }

        return $this->_source_value;
    }

    public function setTargetValue($target_value)
    {
        $this->_target_value = $target_value;
        return $this;
    }

    public function getTargetValue($type_prepended = false)
    {
        if ($type_prepended) {
            return gettype($this->_target_value) . $this->_target_value;
        }

        return $this->_target_value;
    }

    /**
     * @param bool $comparate_column_exists_in_source
     * @return $this
     */
    public function setComparateColumnExistsInSource($comparate_column_exists_in_source)
    {
        $this->_comparate_column_exists_in_source = $comparate_column_exists_in_source;
        return $this;
    }

    /**
     * @param bool $comparate_column_exists_in_target
     * @return $this
     */
    public function setComparateColumnExistsInTarget($comparate_column_exists_in_target)
    {
        $this->_comparate_column_exists_in_target = $comparate_column_exists_in_target;
        return $this;
    }

    public function getComparisonResult()
    {
        if (!isset($this->_comparison_result)) {
            if (!$this->_comparate_column_exists_in_source && !$this->_comparate_column_exists_in_target) {
                $this->_comparison_result = self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET;
            } elseif (!$this->_comparate_column_exists_in_source) {
                $this->_comparison_result = self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE;
            } elseif (!$this->_comparate_column_exists_in_target) {
                $this->_comparison_result = self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET;
            } elseif ($this->getSourceValue() !== $this->getTargetValue()) {
                $this->_comparison_result = self::RESULT_COMPARATE_VALUE_DIFFERS;
            } else {
                $this->_comparison_result = self::RESULT_COMPARATE_VALUE_SAME;
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
        return $this->_source->getUniqueIdentifierValue() ?: $this->_target->getUniqueIdentifierValue();
    }

    public function getUniqueIdentifierHash()
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

    public function comparateColumnIsIgnoredInComparison()
    {
        if (!isset($this->_column_is_ignored_in_comparison)) {
            $this->_column_is_ignored_in_comparison = in_array(
                $this->getComparateColumnName(),
                $this->_source->getIgnoredColumnsFromComparison()
            );
        }

        return $this->_column_is_ignored_in_comparison;
    }

    public function comparateColumnIsPrimaryKey()
    {
        return in_array(
            $this->getComparateColumnName(),
            $this->_source->getActiveRecord()->getPrimaryKeyNames()
        );
    }

    public function isMissingInTarget()
    {
        return in_array($this->getComparisonResult(), self::$_missing_in_target_comparison_results);
    }

    public function isMissingInSource()
    {
        return in_array($this->getComparisonResult(), self::$_missing_in_source_comparison_results);
    }

    public function hasNoDifferingComparisons()
    {
        return in_array($this->getComparisonResult(), self::$_sameness_comparison_results);
    }
}