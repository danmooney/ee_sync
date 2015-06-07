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

abstract class Syncee_ActiveRecord_Abstract implements Syncee_Entity_Interface
{
    const TABLE_NAME = '';

    protected $_is_empty_row = false;

    protected $_is_new = true;

    protected static $_cols = array();

    protected $_col_val_mapping = array();

    protected $_primary = array();

    public function __construct(array $row = array(), $is_new = true)
    {
        if (isset(static::$_cols)) {
            static::$_cols = ee()->db->list_fields(static::TABLE_NAME);
        }

        $object_properties = get_object_vars($this);

        foreach ($row as $key => $val) {
            if (in_array($key, array_keys($object_properties))) {
                $this->$key = $val;
                $this->_col_val_mapping[$key] =& $this->$key;
            } else {
                // assign values in row to nested objects if properties are defined
                foreach ($object_properties as $object_key => $possible_nested_object) {
                    if (is_object($possible_nested_object)) {
                        $nested_object                   = $possible_nested_object;
                        $nested_object_object_properties = get_object_vars($nested_object);

                        if (in_array($key, array_keys($nested_object_object_properties))) {
                            $this->$object_key->$key = $val;
                            $this->_col_val_mapping[$key] =& $this->$object_key->$key;
                        }
                    }
                }
            }
        }

        foreach ($object_properties as $object_key => $possible_nested_object) {
            if (is_object($possible_nested_object)) {
                $nested_object                   = $possible_nested_object;
                $nested_object_object_properties = get_object_vars($nested_object);

                foreach (static::$_cols as $col) {
                    if (in_array($col, array_keys($nested_object_object_properties))) {
                        $this->_col_val_mapping[$col] =& $this->$object_key->$col;
                    }
                }
            }
        }

        if (empty($row)) {
            $this->_is_empty_row = true;
        }

        $this->_is_new = $is_new;
    }

    public function save()
    {
        $row = $this->toArray();

        if ($this->_is_new) {
            return ee()->db->insert(static::TABLE_NAME, $row);
        }

        $where = array();

        foreach ($this->_primary as $primary_key) {
            $where[$primary_key] = $this->_col_val_mapping[$primary_key];
        }

        return ee()->db->update(static::TABLE_NAME, $row, $where);
    }

    public function toArray($table_data_only = true)
    {
        $table_cols = static::$_cols;
        $row        = array();

        foreach ($table_cols as $table_property) {
            if (isset($this->_col_val_mapping[$table_property])) {
                $row[$table_property] = $this->_col_val_mapping[$table_property];
            }
        }

        return $row;
    }

    //    public function __call($method, $args) {}

    public function __set($property, $value)
    {
        $this->$property                   =  $value;
        $this->_col_val_mapping[$property] =& $this->$property;
        $this->_is_empty_row               =  false;
    }

//    public function __get($property) {}
}