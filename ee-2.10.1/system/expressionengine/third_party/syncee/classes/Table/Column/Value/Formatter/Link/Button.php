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

class Syncee_Table_Column_Value_Formatter_Link_Button extends Syncee_Table_Column_Value_Formatter_Link
{
    private $_btn_class = 'btn';

    public function __construct($method, Syncee_ActiveRecord_Abstract $row_override = null, array $extra_keys_to_create_module_cp_url_from = array())
    {
        parent::__construct($method, $row_override, $extra_keys_to_create_module_cp_url_from);

        if (count(func_get_args()) > 3 && ($btn_class = func_get_arg(3))) {
            $this->_btn_class = $btn_class;
        }
    }

    public function format($value, Syncee_ActiveRecord_Abstract $row)
    {
        $link_html   = parent::format($value, $row);
        $button_html = preg_replace('#^<a href#', sprintf('<a class="%s" href', $this->_btn_class), $link_html);

        return $button_html;
    }
}