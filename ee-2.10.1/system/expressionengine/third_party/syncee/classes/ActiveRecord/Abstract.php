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

    protected $_collection_model;

    protected $_is_empty_row = false;

    protected $_is_new = true;

    /**
     * @var array
     */
    protected static $_cols;

    protected $_col_val_mapping = array();

    protected $_primary = array();

    public static function findAll()
    {
        $rows             = ee()->db->select('*')->from(static::TABLE_NAME)->get()->result_array();
        $empty_row        = new static();
        $collection_model = $empty_row->getCollectionModel();

        return new $collection_model($rows);
    }

    public static function findAllByCondition(array $conditions)
    {
        ee()->db->select('*')->from(static::TABLE_NAME);

        foreach ($conditions as $column => $value) {
            if (is_numeric($column)) {
                ee()->db->where($value);
            } else {
                ee()->db->where($column, $value);
            }
        }

        $rows             = ee()->db->get()->result_array();
        $empty_row        = new static();
        $collection_model = $empty_row->getCollectionModel();

        return new $collection_model($rows);
    }

    // TODO - test this!
    public static function findByPk($primary_key_value)
    {
        /**
         * @var $empty_row Syncee_ActiveRecord_Abstract
         */
        $empty_row            = new static();
        $primary_keys_on_row  = (array) $empty_row->getPrimary();

        ee()->db->select('*')->from(static::TABLE_NAME);

        if (count($primary_keys_on_row) === 1) {
            $primary_key_value = (string) $primary_key_value;
            ee()->db->where((string) $primary_keys_on_row, $primary_key_value);
        } else {
            $primary_key_value = (array) $primary_key_value;
            foreach ($primary_key_value as $key => $value) {
                if (is_numeric($key)) {
                    if (!isset($primary_keys_on_row[$key])) {
                        break;
                    }

                    ee()->db->where($primary_keys_on_row[$key], $primary_key_value[$key]);
                } else {
                    if (!in_array($key, $primary_keys_on_row)) {
                        continue;
                    }

                    ee()->db->where($key, $primary_key_value);
                }
            }
        }

        $row = ee()->db->get()->row();
        return new static($row);
    }

    public function __construct(array $row = array(), $is_new = true)
    {
        if (!isset(static::$_cols)) {
            static::$_cols = ee()->db->list_fields(static::TABLE_NAME);
        }

        $object_properties = get_object_vars($this);

        foreach ($row as $key => $val) {
            if (in_array($key, static::$_cols)) {
                $this->$key = $val;
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

    public function isEmptyRow()
    {
        return $this->_is_empty_row;
    }

    public function getPrimary()
    {
        return $this->_primary;
    }

    public function getCollectionModel()
    {
        return $this->_collection_model;
    }

    public function save()
    {
        $row = $this->toArray(true);

        if ($this->_is_new) {
            $success = ee()->db->insert(static::TABLE_NAME, $row);
        } else {
            $where = array();

            foreach ($this->_primary as $primary_key) {
                $where[$primary_key] = $this->_col_val_mapping[$primary_key];
            }

            $success = ee()->db->update(static::TABLE_NAME, $row, $where);
        }

        return $success;
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

    public function getUniqueIdentifier()
    {
        $identifiers = array();

        foreach ($this->_primary as $key) {
            $identifiers[] = $this->$key;
        }

        return implode('|', $identifiers);
    }

    //    public function __call($method, $args) {}

    public function __set($property, $value)
    {
        if (!array_key_exists($property, $this->_col_val_mapping)) {
            $this->$property = $value;
        } else {
            $this->_col_val_mapping[$property] = $value;
            $this->_is_empty_row               = false;
        }

        return $this;
    }

    public function __get($property)
    {
        if (!isset($this->_col_val_mapping[$property])) {
            trigger_error('Undefined property: ' . __CLASS__ . '::' . $property, E_USER_NOTICE);
            return null;
        }

        return $this->_col_val_mapping[$property];
    }
}