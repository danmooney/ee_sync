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

class Syncee_Entity_Channel_Collection_Library extends Syncee_Collection_Library_Abstract
    implements Syncee_Collection_Library_Comparator_Interface
{
    protected $_collection_model = 'Syncee_Entity_Channel_Collection';

    public function compareCollections()
    {
        $current_local_site_channel_collection = null;

        $other_site_channel_collections = array();

        /**
         * @var $collection Syncee_Entity_Channel_Collection
         * @var $local_channel_entity Syncee_Entity_Channel
         * @var $remote_channel_entity Syncee_Entity_Channel
         */
        foreach ($this->_collections as $collection) {

            $is_current_local_site = $collection->getSite()->isCurrentLocal();

            if ($is_current_local_site) {
                $current_local_site_channel_collection = $collection;
            } else {
                $other_site_channel_collections[$collection->getSite()->getUniqueIdentifier()] = $collection;
            }
        }

        if (!$current_local_site_channel_collection) {
            throw new Syncee_Exception('Could not find target site for comparison in ' . __METHOD__);
        }

        $channel_comparison_library = new Syncee_Entity_Comparison_Collection_Library();
        $entity_comparator          = new Syncee_Entity_Comparator();

        // get comparisons for channel names that exist in target
        foreach ($current_local_site_channel_collection as $local_channel_entity) {
            foreach ($other_site_channel_collections as $site_identifier => $collection) {
                if (!($remote_channel_entity = $collection->getChannelByName($local_channel_entity->channel_name))) {
                    $entity_class_name     = get_class($local_channel_entity);
                    $remote_channel_entity = new $entity_class_name();
                }

                $comparison_collection = $entity_comparator->compareEntities($remote_channel_entity, $local_channel_entity);

                $comparison_collection
                    ->setSource($remote_channel_entity)
                    ->setTarget($local_channel_entity)
                ;

                $channel_comparison_library->appendToLibraryAsCollection($comparison_collection);
            }
        }

        // get comparisons for channel names that exist in sources
        foreach ($other_site_channel_collections as $site_identifier => $collection) {
            foreach ($collection as $remote_channel_entity) {
                if (!($local_channel_entity = $collection->getChannelByName($remote_channel_entity->channel_name))) {
                    $entity_class_name    = get_class($remote_channel_entity);
                    $local_channel_entity = new $entity_class_name();
                }

                $comparison_collection = $entity_comparator->compareEntities($remote_channel_entity, $local_channel_entity);

                $comparison_collection
                    ->setSource($remote_channel_entity)
                    ->setTarget($local_channel_entity)
                ;

                if (!$channel_comparison_library->comparisonCollectionAlreadyExists($comparison_collection)) {
                    $channel_comparison_library->appendToLibraryAsCollection($comparison_collection);
                }
            }
        }

        return $channel_comparison_library;
    }
}