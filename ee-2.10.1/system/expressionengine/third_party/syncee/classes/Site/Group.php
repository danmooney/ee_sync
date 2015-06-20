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

    /**
     * @var Syncee_Site_Collection
     */
    private $_site_collection;

    protected $_primary_key_names = array('site_group_id');

    protected $_collection_model = 'Syncee_Site_Group_Collection';

    protected static $_cols;

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