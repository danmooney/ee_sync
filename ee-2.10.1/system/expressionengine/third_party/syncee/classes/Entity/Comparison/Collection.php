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

class Syncee_Entity_Comparison_Collection extends Syncee_Collection_Abstract
{
    private $_source;

    private $_target;

    protected $_row_model = 'Syncee_Entity_Comparison';

    public function setSource(Syncee_Entity_Abstract $source)
    {
        $this->_source = $source;
        return $this;
    }

    public function setTarget(Syncee_Entity_Abstract $target)
    {
        $this->_target = $target;
        return $this;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function getTarget()
    {
        return $this->_target;
    }
}