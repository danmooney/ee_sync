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
    protected $_method;

    /**
     * @var Syncee_ActiveRecord_Abstract
     */
    protected $_row_override;

    protected $_extra_keys_to_create_module_cp_url_from;

    public function __construct($method, Syncee_ActiveRecord_Abstract $row_override = null, array $extra_keys_to_create_module_cp_url_from = array())
    {
        $this->_method                                  = $method;
        $this->_row_override                            = $row_override;
        $this->_extra_keys_to_create_module_cp_url_from = $extra_keys_to_create_module_cp_url_from;
    }

    public function format($value, Syncee_ActiveRecord_Abstract $row)
    {
        $row = $this->_row_override ?: $row;

        $extra_key_values_to_create_module_cp_url_from = array();

        foreach ($this->_extra_keys_to_create_module_cp_url_from as $extra_key_to_create_module_cp_url_from) {
            if (strlen($row->$extra_key_to_create_module_cp_url_from)) {
                $extra_key_values_to_create_module_cp_url_from[$extra_key_to_create_module_cp_url_from] = $row->$extra_key_to_create_module_cp_url_from;
            }
        }

        return sprintf(
            '<a href="%s">%s</a>',
            Syncee_Helper::createModuleCpUrl($this->_method, array_merge($row->getPrimaryKeyNamesValuesMap(), $extra_key_values_to_create_module_cp_url_from)),
            $value
        );
    }
}