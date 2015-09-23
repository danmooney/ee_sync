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

abstract class Syncee_Request_Remote_Entity_Abstract implements Syncee_Request_Remote_Entity_Chain_Interface
{
    /**
     * @var string
     */
    protected $_requested_ee_site_id;

    /**
     * @var Syncee_Entity_Channel_Collection
     */
    protected $_collection_class_name;

    public function getName()
    {
        $class_exploded = explode('_', get_class($this));
        return strtolower($class_exploded[count($class_exploded) - 1]);
    }

    public function setRequestedEeSiteId($ee_site_id)
    {
        $this->_requested_ee_site_id = (string) intval($ee_site_id);
    }

    public function getRequestedEeSiteId()
    {
        return $this->_requested_ee_site_id;
    }

    public function getCollectionClassName()
    {
        return $this->_collection_class_name;
    }

    public function getCollectionClass()
    {
        $collection_class_name = $this->_collection_class_name;
        return new $collection_class_name();
    }

    public function getNextRemoteEntityRequestInChain()
    {
        return false;
    }

    public function appendRemoteEntityRequestToChain(Syncee_Request_Remote_Entity_Chain_Interface $remote_entity_request)
    {
        return false;
    }
}