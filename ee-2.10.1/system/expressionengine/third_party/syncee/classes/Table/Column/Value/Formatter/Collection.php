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

class Syncee_Table_Column_Value_Formatter_Collection extends Syncee_Table_Column_Value_Formatter_Abstract
{
    private $_formatters = array();

    public function __construct()
    {
        $args = func_get_args();


        foreach ($args as $idx => $arg) {
            if (!($arg instanceof Syncee_Table_Column_Value_Formatter_Abstract)) {
                throw new Syncee_Exception('All arguments must be instances of Syncee_Table_Column_Value_Formatter_Abstract in ' . __METHOD__);
            }

            $this->_formatters[] = $arg;
        }
    }

    public function format($value, Syncee_ActiveRecord_Abstract $row)
    {
        /**
         * @var $formatter Syncee_Table_Column_Value_Formatter_Abstract
         */
        foreach ($this->_formatters as $formatter) {
            $value = $formatter->format($value, $row);
        }

        return $value;
    }
}