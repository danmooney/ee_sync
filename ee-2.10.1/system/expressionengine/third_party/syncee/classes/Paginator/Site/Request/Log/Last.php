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

/**
 * Class Syncee_Paginator_Site_Request_Log_Last
 * For fetching the last request log for a site
 */
class Syncee_Paginator_Site_Request_Log_Last extends Syncee_Paginator
{
    protected $_order_by = 'request_log_id';

    protected $_order_dir = 'desc';

    protected $_count_per_page = 1;
}