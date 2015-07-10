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

class Syncee_Site_Request_Log extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site_request_log';

    protected $_collection_model = 'Syncee_Site_Request_Log_Collection';

    protected $_primary_key_names = array('request_log_id');

    /**
     * @var Syncee_Site
     */
    public $site;

    public function __construct(array $row = array(), $is_new = true)
    {
        parent::__construct($row, $is_new);

        $this->site = Syncee_Site::findByPk($this->site_id);
    }
}