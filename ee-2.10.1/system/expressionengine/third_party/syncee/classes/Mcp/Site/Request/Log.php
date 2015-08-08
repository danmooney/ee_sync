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

class Syncee_Mcp_Site_Request_Log extends Syncee_Mcp_Abstract
{
    public function viewRequestLogList()
    {
        $paginator              = new Syncee_Paginator_Site_Request_Log($_GET, $this);
        $request_log_collection = Syncee_Site_Request_Log::findAll($paginator);

        return Syncee_View::render(__FUNCTION__, array(
            'paginator'              => $paginator,
            'request_log_collection' => $request_log_collection
        ), $this);
    }

    public function viewRequestLog()
    {
        $request_log_id = ee()->input->get('request_log_id');
        $request_log    = Syncee_Site_Request_Log::findByPk($request_log_id);

        if ($request_log->isEmptyRow()) {
            // TODO
        }

        // set active state on proper submenu button by assigning the appropriate request_direction of the request log to the $_GET superglobal
        $_GET['request_direction'] = $request_log->request_direction;

        return Syncee_View::render(__FUNCTION__, array(
            'request_log' => $request_log
        ), $this);
    }
}