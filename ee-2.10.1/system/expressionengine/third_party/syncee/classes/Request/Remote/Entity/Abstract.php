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

abstract class Syncee_Request_Remote_Entity_Abstract implements Syncee_Request_Remote_Entity_Interface
{
    protected $_ee_site_id;

    protected $_collection_class_name;

    public function getName()
    {
        $class_exploded = explode('_', get_class($this));
        return strtolower($class_exploded[count($class_exploded) - 1]);
    }

    public function setEeSiteId($ee_site_id)
    {
        $this->_ee_site_id = intval($ee_site_id);
    }

    public function getCollectionClassName()
    {
        return $this->_collection_class_name;
    }
}