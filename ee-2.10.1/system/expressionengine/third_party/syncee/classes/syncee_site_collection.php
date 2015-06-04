<?php

require_once dirname(__FILE__) . '/../_init.php';

class Syncee_Site_Collection implements Countable, Iterator, ArrayAccess
{
    private $_position = 0;

    private $_rows = array();

    private $_row_model = 'Syncee_Site';

    public function __construct(array $rows)
    {
        foreach ($rows as $row) {
            if (is_array($row)) {
                $row = new $this->_row_model($row, false);
            }

            $this->_rows[] = $row;
        }
    }

    public static function getAllBySiteId($site_id)
    {
        $rows = ee()->db->select('*')->from(Syncee_Site::TABLE_NAME)->where('site_id', $site_id)->get()->result_array();
        return new static($rows);
    }

    public function filterByCondition($method, $return_single_row_model = false)
    {
        $filtered_rows = array_values(array_filter($this->_rows, function ($row) use ($method) {
            if (is_string($method) && method_exists($row, $method)) {
                return $row->$method();
            } elseif (is_callable($method)) {
                return $method($row);
            } else {
                throw new Syncee_Exception('Argument passed to ' . __METHOD__ . ' must be callable or a string on which method exists.  Method passed: ' . $method);
            }
        }));

        if ($return_single_row_model) {
            if (count($filtered_rows) > 1) {
                trigger_error('Count of filtered rows in ' . __METHOD__ . ' is greater than 1, but asked to return single row model only', E_USER_WARNING);
            }

            return isset($filtered_rows[0]) ? $filtered_rows[0] : new $this->_row_model();
        } else {
            return new static($filtered_rows);
        }
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