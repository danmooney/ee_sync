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

abstract class Syncee_Collection_Library_Abstract implements Syncee_Collection_Library_Interface, Countable, Iterator, ArrayAccess
{
    protected $_position = 0;
    
    protected $_collections = array();

    protected $_collection_model;

    public function __construct(array $collections)
    {
        foreach ($collections as $collection) {
            if (is_array($collection)) {
                $collection = new $this->_collection_model($collection);
            }

            $this->_collections[] = $collection;
        }
    }

    public function appendToLibraryAsArray(array $collection)
    {
        $this->_collections[] = new $this->_collection_model($collection, false);
    }

    public function appendToLibraryAsCollection(Syncee_Collection_Abstract $collection)
    {
        $collection_model = $this->_collection_model;
        if (!$collection instanceof $collection_model) {
            throw new Syncee_Exception('Collection passed to ' . __METHOD__ . ' must be instance of ' . $this->_collection_model . ', instance of ' . get_class($collection) . ' passed');
        }

        $this->_collections[] = $collection;
    }
    
    public function count()
    {
        return count($this->_collections);
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        return $this->_collections[$this->_position];
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
        return isset($this->_collections[$this->_position]);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_collections[] = $value;
        } else {
            $this->_collections[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_collections[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_collections[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_collections[$offset]) ? $this->_collections[$offset] : null;
    }
}