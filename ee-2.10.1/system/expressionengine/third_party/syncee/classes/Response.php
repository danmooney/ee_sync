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

class Syncee_Response
{
    /**
     * @var string
     */
    private $_raw_response;

    /**
     * @var Syncee_Request_Remote_Entity_Interface
     */
    private $_entity;

    private $_response_decoded;

    /**
     * @var array
     */
    private $_errors;

    /**
     * @var string
     */
    private $_message;

    /**
     * @var int
     */
    private $_status_code;

    /**
     * @var Syncee_Site
     */
    private $_site;

    /**
     * Execute the request and store/parse the response
     * @param Syncee_Request $request
     * @param Syncee_Site $site
     * @param Syncee_Request_Remote_Entity_Interface|null $entity
     * @throws Syncee_Exception
     */
    public function __construct(Syncee_Request $request, Syncee_Site $site, Syncee_Request_Remote_Entity_Interface $entity = null)
    {
        $this->_site         = $site;
        $this->_entity       = $entity;

        $curl_handle         = $request->getCurlHandle();
        $this->_raw_response = $response = $request->execute();
        $this->_status_code  = (int) $curl_handle->http_status_code;

        $decoded_response   = json_decode($response, true);

        if (is_array($decoded_response) && isset($decoded_response['data']) && is_string($decoded_response['data'])) {
            $decoded_response['data']  = $this->_decryptResponseData($site, $decoded_response['data']);
            $this->_errors             = isset($decoded_response['errors']) ? $decoded_response['errors'] : null;
            $this->_message            = isset($decoded_response['message']) ? $decoded_response['message'] : null;
            $this->_response_decoded   = $decoded_response;
            $this->_raw_response       = json_encode($decoded_response);
        }
    }

    public function getStatusCode()
    {
        return $this->_status_code;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function getRawResponse()
    {
        return $this->_raw_response;
    }

    public function getResponseDecoded()
    {
        return $this->_response_decoded;
    }

    public function getResponseDataDecoded()
    {
        return is_array($this->_response_decoded) && isset($this->_response_decoded['data'])
            ? json_decode($this->_response_decoded['data'], true)
            : false
        ;
    }

    /**
     * @return Syncee_Collection_Abstract
     */
    public function getResponseDataDecodedAsCollection()
    {
        if (!$this->_entity) {
            return false;
        }

        $class_name = $this->_entity->getCollectionClassName();
        return new $class_name($this->getResponseDataDecoded());
    }

    public function __toString()
    {
        return $this->_raw_response;
    }

    private function _decryptResponseData(Syncee_Site $site, $data)
    {
        $crypt = $site->rsa->getCrypt();
        $crypt->loadKey($site->rsa->getPrivateKey());
        return $crypt->decrypt(base64_decode($data));
    }
}