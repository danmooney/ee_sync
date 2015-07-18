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

class Syncee_Table_Column_Value_Formatter_Link extends Syncee_Table_Column_Value_Formatter_Abstract
{
    /**
     * @var string
     */
    private $_method;

    /**
     * @var Syncee_ActiveRecord_Abstract
     */
    private $_row_override;

    public function __construct($method, Syncee_ActiveRecord_Abstract $row_override = null)
    {
        $this->_method       = $method;
        $this->_row_override = $row_override;
    }

    public function format($value, Syncee_ActiveRecord_Abstract $row)
    {
        $row = $this->_row_override ?: $row;

        return sprintf(
            '<a href="%s">%s</a>',
            Syncee_Helper::createModuleCpUrl($this->_method, $row->getPrimaryKeyNamesValuesMap()),
            $value
        );
    }
}