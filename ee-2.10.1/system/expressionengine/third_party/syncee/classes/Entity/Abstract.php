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

abstract class Syncee_Entity_Abstract /*extends Syncee_ActiveRecord_Abstract*/ implements Syncee_Entity_Interface
{
    protected $_data;

    public function __construct(array $row = array(), $is_new = true) // TODO - is entity going to really extend active record.  It sorta makes sense.  But channel logic is way more complicated and is based in multiple tables.
    {
        $this->_data = $row;
    }

    public function toArray($table_data_only = true)
    {
        return $this->_data;
    }

    public function __get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    public function __set($key, $val)
    {
        $this->_data[$key] = $val;
    }
}