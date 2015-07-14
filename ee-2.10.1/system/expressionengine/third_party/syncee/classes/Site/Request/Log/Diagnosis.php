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
 * Class Syncee_Site_Request_Log_Diagnosis
 */
class Syncee_Site_Request_Log_Diagnosis extends Syncee_Entity_Abstract
{
    /**
     * @var Syncee_Site_Request_Log
     */
    private $_request_log;


    public function __construct(Syncee_Site_Request_Log $request_log)
    {
        $this->_request_log = $request_log;
        $this->_diagnose();
    }

    private function _diagnose()
    {

    }
}