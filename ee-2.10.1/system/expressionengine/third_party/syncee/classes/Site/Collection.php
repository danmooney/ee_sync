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

class Syncee_Site_Collection extends Syncee_Collection_Abstract implements Syncee_Site_Container_Interface
{
    /**
     * @var Syncee_Site_Request_Log_Collection
     */
    private $_request_log_collection;

    protected $_row_model = 'Syncee_Site';

    public function getRequestLogCollection()
    {
        return $this->_request_log_collection;
    }

    public function __construct(array $rows = array())
    {
        parent::__construct($rows);
        $this->_request_log_collection = new Syncee_Site_Request_Log_Collection();
    }

    public function getSiteCollection()
    {
        return $this;
    }

    /**
     * @param Syncee_Collection_Library_Comparator_Abstract $comparator_library
     * @param Syncee_Request_Remote_Entity_Chain_Interface $remote_entity
     * @return Syncee_Entity_Comparison_Collection_Library
     * @throws Syncee_Exception
     */
    public function getComparisonCollectionLibrary(Syncee_Collection_Library_Comparator_Abstract $comparator_library, Syncee_Request_Remote_Entity_Chain_Interface $remote_entity)
    {
        /**
         * @var $row Syncee_Site
         * @var $collection Syncee_Entity_Comparate_Collection_Abstract
         */
        foreach ($this->_rows as $row) {
            $request     = new Syncee_Request();

            // don't log incoming requests on local site
            $request_log = $row->isRemote() ? new Syncee_Site_Request_Log() : null;

            $response    = $request->makeEntityCallToSite($row, $remote_entity, $request_log);

            // TODO - this obviously isn't that great...
            while ($remote_entity_override = $remote_entity->getNextRemoteEntityRequestInChain()) {
                $response->setEntity($remote_entity_override);
            }

            $collection            = $response->getResponseDataDecodedAsCollection();
            $row->last_request_log = $request_log;

            $collection->setSite($row);

            if (!$comparator_library->collectionAlreadyExistsInLibrary($collection)) {
                $comparator_library->appendToLibraryAsCollection($collection);
            }

            if ($request_log) {
                $this->_request_log_collection->appendToCollectionAsEntity($request_log);
            }
        }

        $entity_comparison_library = $comparator_library->compareCollections();

        return $entity_comparison_library;
    }

    /**
     * TODO - deprecate... but how?  This function has some added functionality that separates it from \Syncee_Site_Collection::getComparisonCollectionLibrary.  Maybe add function on arguments passed to it?
     * @deprecated
     * @return Syncee_Entity_Comparison_Collection_Library
     * @throws Syncee_Exception
     */
    public function getChannelFieldComparisonCollectionLibrary()
    {
        $site_channel_field_library    = new Syncee_Entity_Channel_Field_Collection_Library();

        // get channels/fields first
        $channel_remote_request_entity = new Syncee_Request_Remote_Entity_Channel();

        /**
         * @var $row Syncee_Site
         * @var $channel_collection Syncee_Entity_Channel_Collection
         * @var $channel_entity Syncee_Entity_Channel
         */
        foreach ($this->_rows as $row) {
            $request            = new Syncee_Request();
            $request_log        = $row->isRemote() ? new Syncee_Site_Request_Log() : null;
            $response           = $request->makeEntityCallToSite($row, $channel_remote_request_entity, $request_log);
            $channel_collection = $response->getResponseDataDecodedAsCollection();

            // get fields from channel collection and add to channel field collection

            foreach ($channel_collection as $channel_entity) {
                $channel_field_collection = $channel_entity->getFieldCollection();
                $channel_field_collection->setSite($row);

                $row->last_request_log = $request_log;

                if (!$site_channel_field_library->collectionAlreadyExistsInLibrary($channel_field_collection)) {
                    $site_channel_field_library->appendToLibraryAsCollection($channel_field_collection);
                }

                if ($request_log) {
                    $this->_request_log_collection->appendToCollectionAsEntity($request_log);
                }
            }
        }

        $site_channel_field_comparison_library = $site_channel_field_library->compareCollections();

        return $site_channel_field_comparison_library;
    }

    // TODO - implement getSynchronizationProfileCollection
}