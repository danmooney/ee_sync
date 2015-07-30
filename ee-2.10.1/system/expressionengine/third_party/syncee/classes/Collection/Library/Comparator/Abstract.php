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

abstract class Syncee_Collection_Library_Comparator_Abstract extends Syncee_Collection_Library_Abstract
    implements Syncee_Collection_Library_Comparator_Interface
{
    public function compareCollections()
    {
        $current_local_site_collection = null;
        $current_local_site            = null;
        $other_site_collections        = array();

        $unique_identifier_values      = $this->getAllUniqueIdentifierValues();

        /**
         * @var $collection Syncee_Entity_Comparate_Collection_Abstract
         * @var $local_entity  Syncee_Entity_Abstract
         * @var $remote_entity Syncee_Entity_Abstract
         * @var $empty_entity  Syncee_Entity_Abstract
         */
        foreach ($this->_collections as $collection) {

            $is_current_local_site = $collection->getSite()->isLocal();

            if ($is_current_local_site) {
                $current_local_site_collection = $collection;
                $current_local_site            = $collection->getSite();
            } else {
                $other_site_collections[$collection->getSite()->getUniqueIdentifier()] = $collection;
            }
        }

        if (!$current_local_site_collection) {
            throw new Syncee_Exception('Could not find target site for comparison in ' . __METHOD__);
        }

        $comparison_library      = new Syncee_Entity_Comparison_Collection_Library();

        $comparison_library->setTargetSite($current_local_site);

        $entity_comparator       = new Syncee_Entity_Comparator();
        $empty_entity_model_name = $current_local_site_collection->getRowModel();
        $empty_entity            = new $empty_entity_model_name();

        foreach ($unique_identifier_values as $unique_identifier_value) {
            foreach ($other_site_collections as $site_identifier => $collection) {
                // if entity doesn't exist in local, then instantiate empty entity for comparison (to give result of RESULT_ENTITY_MISSING_IN_TARGET in Syncee_Entity_Comparison_Collection)
                if (!($local_entity = $current_local_site_collection->getEntityByUniqueIdentifierValue($unique_identifier_value))) {
                    $local_entity = new $empty_entity();
                    $local_entity->setSite($current_local_site_collection->getSite())->setUniqueIdentifierValue($unique_identifier_value);
                }

                // if entity doesn't exist in remote, then instantiate empty entity for comparison (to give result of RESULT_ENTITY_MISSING_IN_SOURCE in Syncee_Entity_Comparison_Collection)
                if (!($remote_entity = $collection->getEntityByUniqueIdentifierValue($unique_identifier_value))) {
                    $remote_entity = new $empty_entity();
                    $remote_entity->setSite($collection->getSite())->setUniqueIdentifierValue($unique_identifier_value);
                }

                $comparison_collection = $entity_comparator->compareEntities($remote_entity, $local_entity);

                $comparison_library->appendToLibraryAsCollection($comparison_collection);
            }
        }

        return $comparison_library;
    }
}