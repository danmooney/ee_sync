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

class Syncee_Entity_Reference_Collection extends Syncee_Entity_Comparate_Collection_Abstract
{
    protected $_row_model = 'Syncee_Entity_Reference_Collection_Subset';

    public function appendToCollectionAsArray(array $row, $key = null)
    {
        $collection_subset_row_model = $this->_getCollectionSubsetRowModelBasedOnKey($key);

        $row = array_map(function ($arr) use ($collection_subset_row_model) {
            return new $collection_subset_row_model($arr);
        }, $row);

        parent::appendToCollectionAsArray($row, $key);
        $this->_rows[$key]->setRowModel($collection_subset_row_model);
    }

    private function _getCollectionSubsetRowModelBasedOnKey($key)
    {
        $remote_entity_objects    = Syncee_Request_Remote_Entity_Abstract::getAllRemoteEntityClassObjects();

        /**
         * @var $remote_entity_object Syncee_Request_Remote_Entity_Abstract
         * @var $collection_row Syncee_Entity_Reference_Collection_Subset
         */
        foreach ($remote_entity_objects as $remote_entity_object) {
            if ($remote_entity_object->getName() === $key) {
                $collection_subset_row_model = $remote_entity_object->getEmptyCollectionInstance()->getRowModel();
                break;
            }
        }

        if (!isset($collection_subset_row_model)) {
            $collection_row_model        = $this->_row_model;
            $collection_row              = new $collection_row_model();
            $collection_subset_row_model = $collection_row->getRowModel();
        }

        return $collection_subset_row_model;
    }
}