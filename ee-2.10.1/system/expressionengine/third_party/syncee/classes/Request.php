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
    private $_last_curl_info;

    private $_last_response_decoded;

    public function makeEntityCallToSite(Syncee_Site $site, Syncee_Request_Remote_Entity_Interface $entity)
    {
        $remote_site_url = $this->_generateRemoteRequestUrl($site, $entity);

        $ch = curl_init($remote_site_url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
        ));

        $response              = curl_exec($ch);
        $this->_last_curl_info = curl_getinfo($ch);

        $decoded_response      = json_decode($response, true);

        if (is_array($decoded_response) && isset($decoded_response['data']) && is_string($decoded_response['data'])) {
            $this->_decryptResponseData($site, $decoded_response['data']);

            $decoded_response['data']     = $this->_decryptResponseData($site, $decoded_response['data']);
            $this->_last_response_decoded = $decoded_response;
            $response = json_encode($decoded_response);
        }

        return $response;
    }

    private function _decryptResponseData(Syncee_Site $site, $data)
    {
        $crypt = $site->rsa->getCrypt();
        $crypt->loadKey($site->rsa->getPrivateKey());
        return $crypt->decrypt(base64_decode($data));
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

    public function getLastCurlInfo()
    {
        return $this->_last_curl_info;
    }

    public function getLastResponseDecoded()
    {
        return $this->_last_response_decoded;
    }

    public function getLastResponseDataDecoded()
    {
        return is_array($this->_last_response_decoded) && isset($this->_last_response_decoded['data'])
            ? json_decode($this->_last_response_decoded['data'], true)
            : false
        ;
    }
}