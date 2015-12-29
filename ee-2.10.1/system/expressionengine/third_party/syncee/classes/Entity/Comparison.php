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

    protected static $_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_VALUE_DIFFERS,
        self::RESULT_COMPARATE_VALUE_SAME
    );

    protected static $_sameness_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
        self::RESULT_COMPARATE_VALUE_SAME
    );

    protected static $_difference_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_VALUE_DIFFERS,
    );

    protected static $_missing_in_target_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_TARGET,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
    );

    protected static $_missing_in_source_comparison_results = array(
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE,
        self::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE_AND_TARGET,
    );

    /**
     * @var string
     */
    protected $_comparison_result;

    /**
     * @var Syncee_Entity_Comparate_Abstract
     */
    protected $_source;

    /**
     * @var Syncee_Entity_Comparate_Abstract
     */
    protected $_target;

    /**
     * @var mixed
     */
    protected $_source_value;

    /**
     * @var bool
     */
    protected $_comparate_column_exists_in_source = true;

    /**
     * @var mixed
     */
    protected $_target_value;

    /**
     * @var bool
     */
    protected $_comparate_column_exists_in_target = true;

    /**
     * @var string
     */
    protected $_comparate_column_name;

    /**
     * @var Syncee_Entity_Comparison_Fix_Generic
     */
    protected $_fix = 'Syncee_Entity_Comparison_Fix_Generic';

    /**
     * TODO - these bools are simply for ease of inspection when debugging
     * @var bool
     */
    protected $_column_is_ignored_in_comparison;

    /**
     * TODO - these bools are simply for ease of inspection when debugging
     * @var bool
     */
    protected $_column_is_hidden_in_comparison;

    /**
     * TODO - these bools are simply for ease of inspection when debugging
     * @var bool
     */
    protected $_column_is_serialized_and_base64_encoded;

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

    public function getSourceValue($prepend_type_and_coerce_to_string_if_necessary = false, $format_for_human_readable_output = false)
    {
        $source_value = is_string($format_for_human_readable_output) ? $format_for_human_readable_output : $this->_source_value;

        if (is_bool($format_for_human_readable_output) && $this->comparateColumnIsSerializedAndBase64Encoded()) {
            $source_value = @unserialize(base64_decode(($source_value)));
        }

        if ($prepend_type_and_coerce_to_string_if_necessary) {
            $source_value_str = is_array($source_value) ? serialize($source_value) : $source_value;
            return gettype($source_value) . $source_value_str;
        }

        return $source_value;
    }

    public function setTargetValue($target_value)
    {
        $this->_target_value = $target_value;
        return $this;
    }

    public function getTargetValue($prepend_type_and_coerce_to_string_if_necessary = false, $format_for_human_readable_output = false)
    {
        $target_value = is_string($format_for_human_readable_output) ? $format_for_human_readable_output : $this->_target_value;

        if (is_bool($format_for_human_readable_output) && $this->comparateColumnIsSerializedAndBase64Encoded()) {
            $target_value = @unserialize(base64_decode(($target_value)));
        }

        if ($prepend_type_and_coerce_to_string_if_necessary) {
            $target_value_str = is_array($target_value) ? serialize($target_value) : $target_value;
            return gettype($target_value) . $target_value_str;
        }

        return $target_value;
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

    public function comparateColumnIsHiddenInComparison()
    {
        if (!isset($this->_column_is_hidden_in_comparison)) {
            $this->_column_is_hidden_in_comparison = in_array(
                $this->getComparateColumnName(),
                $this->_source->getHiddenColumnsFromComparison()
            );
        }

        return $this->_column_is_hidden_in_comparison;
    }

    public function comparateColumnIsSerializedAndBase64Encoded()
    {
        if (!isset($this->_column_is_serialized_and_base64_encoded)) {
            $this->_column_is_serialized_and_base64_encoded = in_array(
                $this->getComparateColumnName(),
                $this->_source->getSerializedBase64ColumnsFromComparison()
            );
        }

        return $this->_column_is_serialized_and_base64_encoded;
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

    /**
     * @return Syncee_Entity_Comparison_Fix_Generic
     */
    public function getFix()
    {
        if (is_string($this->_fix)) {
            $fix_class_name = $this->_fix;
            $this->_fix     = new $fix_class_name($this);
        }

        return $this->_fix;
    }
}