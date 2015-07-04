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

abstract class Syncee_Upd_Abstract
{
    protected $_fields = array();

    abstract public function install();

    public function uninstall()
    {
        ee()->dbforge->drop_table($this->getTableName());
    }

    public function getTableName()
    {
        $class_name = strtolower(get_class($this));
        return str_replace('upd_', '', $class_name);
    }
}