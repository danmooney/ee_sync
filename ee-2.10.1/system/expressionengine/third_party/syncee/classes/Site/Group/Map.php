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

class Syncee_Site_Group_Map extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site_group_map';

    protected static $_cols;

    protected $_primary_key_names = array('site_id', 'site_group_id');

    protected $_belongs_to = array(
        'Syncee_Site'        => 'site_id',
        'Syncee_Site_Group'  => 'site_group_id'
    );
}