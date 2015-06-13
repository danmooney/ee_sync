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

class Syncee_Site_Collection extends Syncee_Collection_Abstract
{
    protected $_row_model = 'Syncee_Site';

    public static function getAllBySiteId($site_id)
    {
        $rows = ee()->db->select('*')->from(Syncee_Site::TABLE_NAME)->where('site_id', $site_id)->get()->result_array();
        return new static($rows);
    }

    public function filterByCondition($method, $return_single_row_model = false)
    {
        $filtered_rows = array_values(array_filter($this->_rows, function ($row) use ($method) {
            if (is_string($method) && method_exists($row, $method)) {
                return $row->$method();
            } elseif (is_callable($method)) {
                return $method($row);
            } else {
                throw new Syncee_Exception('Argument passed to ' . __METHOD__ . ' must be callable or a string on which method exists.  Method passed: ' . $method);
            }
        }));

        if ($return_single_row_model) {
            if (count($filtered_rows) > 1) {
                trigger_error('Count of filtered rows in ' . __METHOD__ . ' is greater than 1, but asked to return single row model only', E_USER_WARNING);
            }

            return isset($filtered_rows[0]) ? $filtered_rows[0] : new $this->_row_model();
        } else {
            return new static($filtered_rows);
        }
    }

    /**
     * @return Syncee_Entity_Comparison_Collection_Library
     */
    public function getChannelComparisonCollectionLibrary()
    {
        $site_channel_library = new Syncee_Entity_Channel_Collection_Library();

        // get channels/fields first
        $channel_remote_request_entity = new Syncee_Request_Remote_Entity_Channel();

        /**
         * @var $row Syncee_Site
         * @var $collection Syncee_Entity_Channel_Collection
         */
        foreach ($this->_rows as $row) {
            $request  = new Syncee_Request();

            $response   = $request->makeEntityCallToSite($row, $channel_remote_request_entity);
            $collection = $response->getResponseDataDecodedAsCollection();

            $collection->setSite($row);

            $site_channel_library->appendToLibraryAsCollection($collection);
        }

        $site_channel_comparison_library = $site_channel_library->compareCollections();

        return $site_channel_comparison_library;
    }

    // TODO - implement getSynchronizationProfileCollection
}