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

class Syncee_Paginator
{
    protected static $_order_dirs = array('asc', 'desc');

    protected $_order_dir = 'asc';

    protected $_order_by;

    protected $_count_per_page = 20;

    protected $_offset;

    protected $_total_rows;

    protected $_params = array();

    /**
     * @var Syncee_Mcp_Abstract
     */
    protected $_mcp;

    public function __construct(array $params = array(), Syncee_Mcp_Abstract $mcp)
    {
        foreach ($params as $key => $val) {
            $protected_member_name = '_' . $key;
            if (property_exists($this, $protected_member_name)) {
                $this->$protected_member_name = $val;
            }
        }

        $this->_params = $params;
        $this->_mcp    = $mcp;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getMcp()
    {
        return $this->_mcp;
    }

    public function getOrderDir()
    {
        return $this->_order_dir;
    }

    public function getOppositeOrderDir()
    {
        $opposite_order_dir_arr = array_diff(static::$_order_dirs, (array) $this->_order_dir);
        return reset($opposite_order_dir_arr);
    }

    public function getOrderBy()
    {
        return $this->_order_by;
    }

    /**
     * Modify query based on pagination parameters passed through GET
     * @param $db
     */
    public function modifyQueryOnDriver($db)
    {
        $count_db          = clone $db;
        $this->_total_rows = $total_rows = $count_db->get()->num_rows();

        if ($this->_order_by) {
            $db->order_by($this->_order_by, $this->_order_dir);
        }

        if ($this->_count_per_page) {
            $db->limit($this->_count_per_page);
        }

        if ($this->_offset && $this->_offsetIsLessThanTotalRows()) {
            $db->offset($this->_offset);
        }
    }

    public function getCountPerPage()
    {
        return $this->_count_per_page;
    }

    public function getCurrentPageNumber()
    {
        return (int) ceil($this->_offset / $this->_count_per_page) + 1;
    }

    public function hasNextPage()
    {
        return $this->_offset + $this->_count_per_page < $this->_total_rows;
    }

    public function hasPrevPage()
    {
        return $this->_offset - $this->_count_per_page >= 0;
    }

//    public function getPrevOffset($page_number = null)
//    {
//        if (!$page_number && !$this->hasPrevPage()) {
//            return false;
//        }
//
//        if (!$page_number) {
//            $page_number = $this->getCurrentPageNumber();
//        }
//
//        return ($this->_count_per_page * $page_number) - $this->_count_per_page;
//    }

    public function getCurrentOffset()
    {
        return $this->_offset;
    }

    public function getOffsetByPageNumber($page_number = null)
    {
        if (!$page_number) {
            $page_number = $this->getCurrentPageNumber();
        }

        return ($this->_count_per_page * ($page_number - 1));
    }

//    public function getNextOffset($page_number = null)
//    {
//        if (!$page_number && !$this->hasNextPage()) {
//            return false;
//        }
//
//        if (!$page_number) {
//            $page_number = $this->getCurrentPageNumber();
//        }
//
//        return ($this->_count_per_page * $page_number) + $this->_count_per_page;
//    }

    public function getTotalPages()
    {
        return (int) ceil($this->_total_rows / $this->_count_per_page);
    }

    private function _offsetIsLessThanTotalRows()
    {
        return $this->_offset < $this->_total_rows;
    }
}