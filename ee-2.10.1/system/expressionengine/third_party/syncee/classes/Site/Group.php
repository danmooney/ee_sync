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

class Syncee_Site_Group extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site_group';

    protected static $_cols;

    /**
     * @var Syncee_Site_Collection
     */
    private $_site_collection;

    protected $_primary_key_names = array('site_group_id');

    protected $_has_many_map = 'Syncee_Site_Group_Map';

    protected $_collection_model = 'Syncee_Site_Group_Collection';

    /**
     * @var Syncee_Site
     */
    public $local_site;

    /**
     * @var Syncee_Site_Collection
     */
    public $remote_sites;

    public function __construct(array $row = array(), $is_new = true)
    {
        parent::__construct($row, $is_new);
        $this->getSiteCollection();

        if (isset($row['local_site_id'])) {
            $this->local_site   = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('site_id' => $row['local_site_id']), true);
        } else {
            $this->local_site   = $this->_site_collection->filterByCondition(array('is_local' => true), true);
        }

        if (isset($row['remote_site_id'])) {
            $this->remote_sites = Syncee_Site::getRemoteSiteCollection()->filterByCondition(array('site_id' => $row['remote_site_id']));
        } else {
            $this->remote_sites = $this->_site_collection->filterByCondition(array('is_local' => false));
        }
    }

    public function toArray($table_data_only = true)
    {
        $row = parent::toArray($table_data_only);

        if (!$table_data_only) {
            $row = array_merge($row, $this->local_site->toArray());
        }

        return $row;
    }

    public function getSiteCollection()
    {
        if (!isset($this->_site_collection)) {
            $this->_site_collection = Syncee_Site::findAllByCondition(array(
                'site_group_id' => $this->site_group_id
            ));
        }

        return $this->_site_collection;
    }
}