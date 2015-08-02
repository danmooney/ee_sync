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

class Syncee_Paginator_Site_Request_Log extends Syncee_Paginator
{
    protected $_order_by = 'request_log_id';

    protected $_order_dir = 'desc';

    public function __construct(array $params = array(), Syncee_Mcp_Abstract $mcp)
    {
        parent::__construct($params, $mcp);

        if (!isset($this->_params['request_direction'])) {
            $this->_params['request_direction'] = Syncee_Site_Request_Log::REQUEST_DIRECTION_DEFAULT;
        }
    }

    public function modifyQueryOnDriver($db)
    {
        if (isset($this->_params['request_direction']) && $this->_params['request_direction']) {
            $db->where('request_direction', $this->_params['request_direction']);
        }

        parent::modifyQueryOnDriver($db);
    }
}