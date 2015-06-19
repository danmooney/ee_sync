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
    implements Syncee_Site_Storage_Interface
{
    const TABLE_NAME = 'syncee_site_group';

    /**
     * @var Syncee_Site
     */
    private $_site;

    protected $_primary = array('site_group_id');

    protected $_collection_model = 'Syncee_Site_Collection';

    public $site_group_id;

    public $title;

    public $create_datetime;

    public $last_sync_datetime;

    public function setSite(Syncee_Site $site)
    {
        $this->_site = $site;
    }

    public function getSite()
    {
        return $this->_site;
    }
}