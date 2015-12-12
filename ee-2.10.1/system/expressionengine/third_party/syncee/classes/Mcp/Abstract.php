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

abstract class Syncee_Mcp_Abstract
{
    /**
     * @var string
     */
    protected $_called_method;

    /**
     * @var Syncee_Form_Abstract
     */
    protected $_form;

    public function __construct() {}

    public function setCalledMethod($called_method)
    {
        $this->_called_method = $called_method;
    }

    public function getCalledMethod()
    {
        return $this->_called_method;
    }
}