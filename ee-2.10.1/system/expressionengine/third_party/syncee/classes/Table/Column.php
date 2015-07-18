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

class Syncee_Table_Column implements Syncee_Entity_Interface
{
    /**
     * @var string
     */
    private $_label;

    /**
     * @var callable|null|string
     */
    private $_column_reference_value;

    /**
     * @var bool
     */
    private $_orderable;

    /**
     * @var string
     */
    private $_align;

    /**
     * @var Syncee_Table_Column_Value_Formatter_Abstract
     */
    private $_formatter;

    /**
     * @param string $label
     * @param string|callable|null $column_reference_value
     * @param bool $orderable
     * @param string $align
     * @param Syncee_Table_Column_Value_Formatter_Abstract $formatter
     */
    public function __construct($label, $column_reference_value = null, $orderable = false, $align = 'center', Syncee_Table_Column_Value_Formatter_Abstract $formatter = null)
    {
        $this->_label                  = $label;
        $this->_column_reference_value = $column_reference_value;
        $this->_orderable              = $orderable;
        $this->_align                  = $align;
        $this->_formatter              = $formatter;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function getColumnReferenceValue()
    {
        return $this->_column_reference_value;
    }

    public function isOrderable()
    {
        return $this->_orderable;
    }

    public function getAlign()
    {
        return $this->_align;
    }

    public function getFormatter()
    {
        return $this->_formatter;
    }

    public function toArray($table_data_only = true)
    {
        return get_object_vars($this);
    }
}