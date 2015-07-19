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

class Syncee_Table_Column_Value_Formatter_Datetime extends Syncee_Table_Column_Value_Formatter_Abstract
{
    private $_empty_value;

    public function __construct($empty_value = null)
    {
        $this->_empty_value = $empty_value;
    }

    public function format($value, Syncee_ActiveRecord_Abstract $row)
    {
        return strtotime($value) ? Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($value) : $this->_empty_value;
    }
}