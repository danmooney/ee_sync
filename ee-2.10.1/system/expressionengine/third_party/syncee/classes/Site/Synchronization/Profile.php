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

class Syncee_Site_Synchronization_Profile extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site_synchronization_profile';

    protected static $_cols;

    protected $_primary_key_names = array('synchronization_profile_id');

    /**
     * @var Syncee_Site_Request_Log_Collection
     */
    private $_request_log_collection;

    /**
     * @var Syncee_Site_Container_Interface
     */
    private $_site_container;

    /**
     * @var Syncee_Entity_Comparison_Collection_Library
     */
    private $_comparison_collection_library;

    /**
     * @var string
     */
    protected $_entity_name;

    public function save()
    {
        parent::save();

        if (!$this->_request_log_collection) {
            return;
        }

        // save request log collection/synchronization_profile_id map
        foreach ($this->_request_log_collection as $request_log) {
            $synchronization_profile_request_log = new Syncee_Site_Synchronization_Profile_Request_Log(array(
                'synchronization_profile_id' => $this->getPrimaryKeyValues(true),
                'request_log_id'             => $request_log->getPrimaryKeyValues(true)
            ));

            $synchronization_profile_request_log->save();
        }
    }

    public function getEntityName()
    {
        return $this->_entity_name;
    }

    public function setEntityName($entity_name)
    {
        $this->_entity_name = $entity_name;

        if (!is_string($entity_name)) {
            // Set string value on self::$entity_class_name (column in DB table)
            $this->entity_class_name = get_class($entity_name);
        }

        return $this;
    }

    public function setComparatorLibrary(Syncee_Collection_Library_Comparator_Abstract $comparator_library)
    {
        $this->comparator_library_class_name = get_class($comparator_library);
        return $this;
    }

    public function getComparatorLibrary()
    {
        return $this->comparator_library_class_name;
    }

    /**
     * @return Syncee_Request_Remote_Entity_Abstract
     */
    public function getEntity()
    {
        if (!$this->_entity_name) {
            $this->determineAndSetEntityNameOnInstance();
        }

        $entity_str = $this->_entity_name;

        return new $entity_str();
    }

    public function setRequestLogCollection(Syncee_Site_Request_Log_Collection $request_log_collection)
    {
        $this->_request_log_collection = $request_log_collection;
        return $this;
    }

    public function getRequestLogCollection()
    {
        if (!isset($this->_request_log_collection)) {
            if ($this->isNew()) {
                throw new Syncee_Exception('No new objects should be created using ' . __CLASS__ . '::__construct.  Use the corresponding factory class to generate a new instance.');
            }

            // get request log collection
            // dynamically set site group based on ids used in request logs... the site group may have changed since the synchronization so only fetch based on the sites in the list (the user can't change the sites active records... yet)
            $request_logs    = Syncee_Site_Synchronization_Profile_Request_Log::findAllByCondition($this->getPrimaryKeyNamesValuesMap());

            $request_log_ids = array();

            foreach ($request_logs as $request_log) {
                $request_log_ids[] = $request_log->request_log_id;
            }

            $this->_request_log_collection = Syncee_Site_Request_Log::findAllByCondition(array(
                'request_log_id' => $request_log_ids
            ));
        }

        return $this->_request_log_collection;
    }

    public function setSiteContainer(Syncee_Site_Container_Interface $site_container)
    {
        $this->_site_container = $site_container;
        $this->site_group_id   = $site_container->getPrimaryKeyValues(true);
        return $this;
    }

    public function getSiteIdsInvolvedInSynchronizationProfile()
    {
        $request_log_collection = $this->getRequestLogCollection();

        $site_ids_involved_in_synchronization_profile = array();

        foreach ($request_log_collection as $request_log) {
            $site_ids_involved_in_synchronization_profile[] = $request_log->site_id;
        }

        $site_ids_involved_in_synchronization_profile = array_unique($site_ids_involved_in_synchronization_profile);

        $site_ids_involved_in_synchronization_profile[] = $this->local_site_id;

        return $site_ids_involved_in_synchronization_profile;
    }

    public function getSiteContainer($populate_empty_sites_if_missing = true)
    {
        if (!isset($this->_site_container)) {
            $site_ids_involved_in_synchronization_profile = $this->getSiteIdsInvolvedInSynchronizationProfile();

            $this->_site_container = Syncee_Site::findAllByCondition(array(
                'site_id' => $site_ids_involved_in_synchronization_profile
            ));

            // populate site ids that may have been removed by user since
            if ($populate_empty_sites_if_missing) {
                $site_container =& $this->_site_container;

                foreach ($site_ids_involved_in_synchronization_profile as $site_id) {
                    $site_missing = $site_container->filterByCondition(array('site_id' => $site_id), true)->isEmptyRow();

                    if ($site_missing) {
                        $placeholder_site = Syncee_Site::getRemoteSitePlaceholderInstance();
                        $placeholder_site->site_id = $site_id;
                        $site_container->appendToCollectionAsEntity($placeholder_site);
                    }
                }
            }
        }

        return $this->_site_container;
    }

    public function getComparisonCollectionLibrary()
    {
        if (!isset($this->_comparison_collection_library)) {

            $request_log_collection          = $this->getRequestLogCollection();
            $site_container                  = $this->getSiteContainer();
            $entity                          = $this->getEntity();
            $comparator_collection_library   = $entity->getEmptyCollectionInstance()->getComparatorCollectionLibrary();

            $local_site_is_in_request_log_collection = false;

            /**
             * @var $site Syncee_Site
             * @var $local_site Syncee_Site
             * @var $collection Syncee_Entity_Comparate_Collection_Abstract
             */
            foreach ($request_log_collection as $request_log) {
                $site = $site_container->filterByCondition(array(
                    'site_id' => $request_log->site_id
                ), true);

                if ($site->isLocal()) {
                    $local_site_is_in_request_log_collection = true;
                }

                $response   = new Syncee_Response($request_log, $site, $entity);
                $collection = $response->getResponseDataDecodedAsCollection();

                $collection->setSite($site);

                $comparator_collection_library->appendToLibraryAsCollection($collection);
            }

            // get local site data
            if (!$local_site_is_in_request_log_collection) {
                $local_site  = $site_container->filterByCondition(array('is_local' => true), true);
                $request     = new Syncee_Request();
                $response    = $request->makeEntityCallToSite($local_site, $entity);

                $collection  = $response->getResponseDataDecodedAsCollection();

                $comparator_collection_library->appendToLibraryAsCollection($collection);
                $collection->setSite($local_site);
            }

            $this->_comparison_collection_library = $comparator_collection_library->compareCollections();
        }

        return $this->_comparison_collection_library;
    }

    public function setComparisonCollectionLibrary(Syncee_Entity_Comparison_Collection_Library $comparison_collection_Library)
    {
        $this->_comparison_collection_library = $comparison_collection_Library;
        return $this;
    }

    public function determineAndSetEntityNameOnInstance()
    {
        /**
         * @var $request_log Syncee_Site_Request_Log
         */
        foreach ($this->getRequestLogCollection() as $request_log) {
            $this->_entity_name = get_class($request_log->request_entity);
            break;
        }
    }
}