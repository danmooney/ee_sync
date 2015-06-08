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

class Syncee_Request
{
    /**
     * @var Syncee_Helper_Curl
     */
    private $_curl_handle;

    /**
     * @var Syncee_Response
     */
    private $_response;

    public function makeEntityCallToSite(Syncee_Site $site, Syncee_Request_Remote_Entity_Interface $entity)
    {
        $remote_site_url = $this->_generateRemoteRequestUrl($site, $entity);

        $ch = $this->_curl_handle = new Syncee_Helper_Curl($remote_site_url);
        $ch->setOpt(CURLOPT_RETURNTRANSFER, true);

        $this->_response = $response = new Syncee_Response($this, $site);

        return $response;
    }

    public function isReadyToExecute()
    {
        return $this->_curl_handle && $this->_curl_handle->getOpt(CURLOPT_URL);
    }

    public function execute()
    {
        if (!$this->isReadyToExecute()) {
            throw new Syncee_Exception('Request isn\'t ready for execution');
        }

        $response = $this->_curl_handle->exec();

        // close curl handle; request has been made and isn't ready to execute anymore
        $this->_curl_handle->close();

        return $response;
    }

    public function getCurlHandle()
    {
        return $this->_curl_handle;
    }

    private function _generateRemoteRequestUrl(Syncee_Site $site, Syncee_Request_Remote_Entity_Interface $entity)
    {
        $entity_class_str                  = get_class($entity);
        $module_name                       = Syncee_Upd::MODULE_NAME;

        // remove module name for later insertion to prevent a request instantiating a class that shouldn't be instantiated
        $entity_class_str_sans_module_name = preg_replace("#^{$module_name}_#", '', $entity_class_str);
        $remote_site_url                   = $site->getSiteUrl() . "?ACT={$site->action_id}&entity={$entity_class_str_sans_module_name}&site_id={$site->site_id}";

        return $remote_site_url;
    }
}