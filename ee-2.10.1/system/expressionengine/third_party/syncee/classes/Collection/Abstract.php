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

abstract class Syncee_Collection_Abstract implements Syncee_Collection_Interface, Countable, Iterator, ArrayAccess
{
    /**
     * @var Syncee_Site
     */
    protected $_site;

    protected $_position = 0;

    protected $_rows = array();

    protected $_row_model;

    public function __construct(array $rows = array())
    {
        foreach ($rows as $row) {
            if (is_array($row)) {
                $this->appendToCollectionAsArray($row);
            } else {
                $this->appendToCollectionAsEntity($row);
            }
        }
    }

    public function setSite(Syncee_Site $site)
    {
        $this->_site = $site;
    }

    public function getSite()
    {
        return $this->_site;
    }

    public function appendToCollectionAsArray(array $row)
    {
        $this->_rows[] = new $this->_row_model($row, false);
    }

    public function appendToCollectionAsEntity(Syncee_Entity_Interface $row)
    {
        $row_model = $this->_row_model;
        if (!$row instanceof $row_model) {
            throw new Syncee_Exception('Row passed to ' . __METHOD__ . ' must be instance of ' . $this->_row_model . ', instance of ' . get_class($row) . ' passed');
        }

        $this->_rows[] = $row;
    }

    public function getRowModel()
    {
        return $this->_row_model;
    }

    public function getEntityByUniqueIdentifierKeyAndValue($identifier_value, $identifier_key_override = null)
    {
        /**
         * @var $row Syncee_Entity_Abstract
         */
        if ($identifier_key_override) {
            $unique_identifier_key = $identifier_key_override;
        } else {
            $row_model             = $this->_row_model;
            $row                   = new $row_model();
            $unique_identifier_key = $row->getUniqueIdentifierKey();
        }

        foreach ($this->_rows as $row) {
            $row_arr = $row->toArray(false);

            if (isset($row_arr[$unique_identifier_key]) &&
                $row_arr[$unique_identifier_key] === $identifier_value
            ) {
                $found_entity = $row;
                break;
            }
        }

        return isset($found_entity)
            ? $found_entity
            : false
        ;
    }

    public function entityAlreadyExistsInCollection(Syncee_Entity_Abstract $row)
    {
        $entity_already_exists = false;

        /**
         * @var $row_to_test Syncee_Entity_Comparison
         */
        foreach ($this->_rows as $row_to_test) {
            if ($row->getUniqueIdentifierValue() === $row_to_test->getUniqueIdentifierValue()) {
                $entity_already_exists = true;
                break;
            }
        }

        return $entity_already_exists;
    }

    public function isEmptyCollection()
    {
        return !count($this->_rows);
    }

    public function toArray($table_data_only = true)
    {
        $rows = array();

        /**
         * @var $row Syncee_Entity_Abstract
         */
        foreach ($this->_rows as $row) {
            $rows[] = $row->toArray($table_data_only);
        }

        return $rows;
    }

    public function count()
    {
        return count($this->_rows);
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        return $this->_rows[$this->_position];
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        return $this->_position += 1;
    }

    public function valid()
    {
        return isset($this->_rows[$this->_position]);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_rows[] = $value;
        } else {
            $this->_rows[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_rows[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_rows[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_rows[$offset]) ? $this->_rows[$offset] : null;
    }
}