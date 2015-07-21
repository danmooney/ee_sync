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
        $other_site_collections        = array();

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
            } else {
                $other_site_collections[$collection->getSite()->getUniqueIdentifier()] = $collection;
            }
        }

        if (!$current_local_site_collection) {
            throw new Syncee_Exception('Could not find target site for comparison in ' . __METHOD__);
        }

        $comparison_library      = new Syncee_Entity_Comparison_Collection_Library();
        $entity_comparator       = new Syncee_Entity_Comparator();
        $empty_entity_model_name = $current_local_site_collection->getRowModel();
        $empty_entity            = new $empty_entity_model_name();

        // get comparisons for unique identifier keys that exist in target
        foreach ($current_local_site_collection as $local_entity) {
            foreach ($other_site_collections as $site_identifier => $collection) {
                if (!($remote_entity = $collection->getEntityByUniqueIdentifierValue($local_entity->getUniqueIdentifierValue()))) {
                    $remote_entity = new $empty_entity();
                    $remote_entity->setSite($collection->getSite());
                }

                $comparison_collection = $entity_comparator->compareEntities($remote_entity, $local_entity);

                $comparison_library->appendToLibraryAsCollection($comparison_collection);
            }
        }

        // get comparisons for unique identifier values that exist in sources
        foreach ($other_site_collections as $site_identifier => $collection) {
            foreach ($collection as $remote_entity) {
                if (!($local_entity = $current_local_site_collection->getEntityByUniqueIdentifierValue($remote_entity->getUniqueIdentifierValue()))) {
                    $local_entity = new $empty_entity();
                    $local_entity->setSite($current_local_site_collection->getSite());
                }

                $comparison_collection          = $entity_comparator->compareEntities($remote_entity, $local_entity);

                // check for existent comparison collection and append missing entities to it if need be, without creating a new comparison collection that represents the comparison of the same two entities
                $existent_comparison_collection = $comparison_library->getComparisonCollectionBySourceAndTarget($remote_entity, $local_entity);

                if (!$existent_comparison_collection) {
                    $comparison_library->appendToLibraryAsCollection($comparison_collection);
                } else {
                    foreach ($comparison_collection as $comparison) {
                        if (!$existent_comparison_collection->entityAlreadyExistsInCollection($comparison)) {
                            $existent_comparison_collection->appendToCollectionAsEntity($comparison);
                        }
                    }
                }
            }
        }

        return $comparison_library;
    }
}