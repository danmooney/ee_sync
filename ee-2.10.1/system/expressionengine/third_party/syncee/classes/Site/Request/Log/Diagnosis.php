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
class Syncee_Site_Request_Log_Diagnosis
{
    const REQUEST_DIAGNOSIS_SYNCEE_VERSIONS_DIFFER = 'REQUEST_DIAGNOSIS_SYNCEE_VERSIONS_DIFFER';
    const REQUEST_DIAGNOSIS_EE_VERSIONS_DIFFER     = 'REQUEST_DIAGNOSIS_EE_VERSIONS_DIFFER';
    const REQUEST_DIAGNOSIS_UNABLE_TO_REACH_SERVER = 'REQUEST_DIAGNOSIS_UNABLE_TO_REACH_SERVER';
    const REQUEST_DIAGNOSIS_ACTION_ID_INVALID      = 'REQUEST_DIAGNOSIS_ACTION_ID_INVALID';
    const REQUEST_FORBIDDEN_FROM_THIS_IP           = 'REQUEST_FORBIDDEN_FROM_THIS_IP';
    const REQUEST_FORBIDDEN_FROM_MASTER_OVERRIDE   = 'REQUEST_FORBIDDEN_FROM_MASTER_OVERRIDE';

    /**
     * @var Syncee_Site_Request_Log
     */
    private $_request_log;

    private $_diagnoses = array();

    public function __construct(Syncee_Site_Request_Log $request_log)
    {
        $this->_request_log = $request_log;

        if ($request_log->isEmptyRow()) {
            return;
        }

        $this->_diagnose();
    }

    public function getDiagnoses()
    {
        return $this->_diagnoses;
    }

    private function _diagnose()
    {
        $request_log = $this->_request_log;
        $diagnoses   = array();
        $code        = intval($request_log->code);

        if ($code === 0) {
            $diagnoses[] = static::REQUEST_DIAGNOSIS_UNABLE_TO_REACH_SERVER;
        }

        if ($code === 403) {
            if (stripos($request_log->message, 'master override') !== false) {
                $diagnoses[] = static::REQUEST_FORBIDDEN_FROM_MASTER_OVERRIDE;
            } else {
                $diagnoses[] = static::REQUEST_FORBIDDEN_FROM_THIS_IP;
            }
        }

        if ($request_log->content_type === 'text/html') {
            $diagnoses[] = static::REQUEST_DIAGNOSIS_ACTION_ID_INVALID;
        }

        if ($request_log->version && $request_log->version !== $request_log->request_version) {
            $diagnoses[] = static::REQUEST_DIAGNOSIS_SYNCEE_VERSIONS_DIFFER;
        }

        if (defined('APP_VER') && $request_log->ee_version && $request_log->ee_version !== APP_VER) {
            $diagnoses[] = static::REQUEST_DIAGNOSIS_EE_VERSIONS_DIFFER;
        }

        $this->_diagnoses = array_unique($diagnoses);
    }
}