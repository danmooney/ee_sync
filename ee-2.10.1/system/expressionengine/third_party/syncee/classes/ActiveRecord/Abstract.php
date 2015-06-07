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

abstract class Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = '';

    public function __construct(array $row = array(), $is_new = true)
    {
        $class_properties = get_class_vars(get_class($this));

        foreach ($row as $key => $val) {
            if (in_array($key, $class_properties)) {
                $this->$key = $val;
            }
        }

        if (empty($row)) {
            $this->_is_empty_row = true;
        }

        $this->_is_new = $is_new;
    }

    public function save()
    {
        $table_properties  = ee()->db->list_fields(static::TABLE_NAME);
        $data              = array();

        foreach ($table_properties as $table_property) {
            $data[$table_property] = $this->$table_property;
        }

        if ($this->_is_new) {
            return ee()->db->insert(static::TABLE_NAME, $data);
        }

        $where = array();

        foreach ($this->_primary as $primary_key) {
            $where[$primary_key] = $this->$primary_key;
        }

        return ee()->db->update(static::TABLE_NAME, $data, $where);
    }

    //    public function __call($method, $args) {}

    public function __set($property, $value)
    {
        $this->$property     = $value;
        $this->_is_empty_row = false;
    }

//    public function __get($property) {}
}