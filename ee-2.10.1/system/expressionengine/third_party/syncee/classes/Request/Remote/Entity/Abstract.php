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
     * @var Syncee_Entity_Comparate_Collection_Abstract
     */
    protected $_collection_class_name;

    protected $_references = array();

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

    public function getEmptyCollectionInstance()
    {
        $collection_class_name = $this->_collection_class_name;
        return new $collection_class_name();
    }

    public function getReferenceLibraryBasedOnCollection(Syncee_Collection_Abstract $collection)
    {
        $reference_class_names_fetched = array();
        $reference_collections         = array();

        /**
         * @var $remote_entity Syncee_Request_Remote_Entity_Abstract
         */
        foreach ($this->_references as $column => $remote_entity_class_name) {
            $already_fetched_reference_collection_for_this_class = (
                in_array($remote_entity_class_name, $reference_class_names_fetched) ||
                get_class($this) === $remote_entity_class_name
            );

            if ($already_fetched_reference_collection_for_this_class) {
                continue;
            }

            $remote_entity                   = new $remote_entity_class_name();
            $reference_collection            = $remote_entity->queryDatabaseAndGenerateCollection();

            // set row model $remote_entity on $reference_collection so we can fetch the entity's name when the request gets grouped with the other references
            $reference_collection->setRowModel($remote_entity);

            $reference_collections[]         = $reference_collection;
            $reference_class_names_fetched[] = $remote_entity_class_name;
        }

        return new Syncee_Collection_Library_Generic($reference_collections);
    }

    public function isAReferenceColumn($reference_column_name)
    {
        return isset($this->_references[$reference_column_name]);
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