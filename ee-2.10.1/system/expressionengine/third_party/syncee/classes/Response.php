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
    private $_errors = array();

    /**
     * @var string
     */
    private $_message;

    /**
     * @var int
     */
    private $_status_code;

    /**
     * @var string
     */
    private $_content_type;

    /**
     * @var Syncee_Site
     */
    private $_site;

    /**
     * Execute the request and store/parse the response
     * @param Syncee_Request_Interface|Syncee_Request $request
     * @param Syncee_Site $site
     * @param Syncee_Request_Remote_Entity_Interface|null $entity
     * @throws Syncee_Exception
     */
    public function __construct(Syncee_Request_Interface $request, Syncee_Site $site, Syncee_Request_Remote_Entity_Interface $entity = null)
    {
        $this->_site   = $site;
        $this->_entity = $entity;

        if ($request->requestHasAlreadyBeenMade()) {
            $response = (string) $request;
        } else {
            $response = $this->_executeRequest($request, $site, $entity);
        }

        $decoded_response = json_decode($response, true);

        if (!is_array($decoded_response)) {
            $this->_errors[] = 'Invalid JSON returned in response.';
            return;
        }

        $this->_errors = array_merge(
            $this->_errors,
            isset($decoded_response['errors']) ? $decoded_response['errors'] : array()
        );

        if (isset($decoded_response['data']) && is_string($decoded_response['data'])) {
            if (!is_array(json_decode($decoded_response['data'], true))) {
                $decoded_response['data'] = $this->_decryptResponseData($site, $decoded_response['data']);
            }

            $decoded_response['data'] = json_decode($decoded_response['data'], true);


            if (!is_array($decoded_response['data'])) {
                $this->_errors[] = 'Unable to decode response data with private key on this local machine.';
            }
        }

        $this->_message             = isset($decoded_response['message']) ? $decoded_response['message'] : '';

        $decoded_response['errors'] = $this->_errors;
        $this->_response_decoded    = $decoded_response;

        $this->_raw_response        = json_encode($decoded_response);
    }

    public function getStatusCode()
    {
        return $this->_status_code;
    }

    public function getContentType()
    {
        return $this->_content_type;
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
        return $this->_raw_response ?: null;
    }

    public function getResponseDecoded($key = null)
    {
        if (null === $key) {
            return $this->_response_decoded;
        }

        return is_array($this->_response_decoded) && isset($this->_response_decoded[$key])
            ? $this->_response_decoded[$key]
            : null
        ;
    }

    public function getResponseDataDecoded()
    {
        if (!is_array($this->_response_decoded) || !isset($this->_response_decoded['data'])) {
            return false;
        }

        if (!is_array($this->_response_decoded['data'])) {
            $this->_response_decoded['data'] = json_decode($this->_response_decoded['data'], true);
        }

        return $this->_response_decoded['data'];
    }

    /**
     * @return Syncee_Collection_Abstract
     */
    public function getResponseDataDecodedAsCollection()
    {
        if (!$this->_entity) {
            return false;
        }

        $collection_class_name = $this->_entity->getCollectionClassName();
        return new $collection_class_name((array) $this->getResponseDataDecoded());
    }

    public function setEntity(Syncee_Request_Remote_Entity_Interface $entity)
    {
        $this->_entity = $entity;
    }

    public function __toString()
    {
        return $this->_raw_response;
    }

    private function _executeRequest(Syncee_Request $request, Syncee_Site $site, Syncee_Request_Remote_Entity_Interface $entity = null)
    {
        if ($site->isLocal()) {
            ob_start();

            $entity->setRequestedEeSiteId($site->ee_site_id);

            $remote_request      = new Syncee_Request_Remote($site, $entity, null, false, true);
            $this->_raw_response = $response = ob_get_clean();
            $this->_status_code  = $remote_request->getStatusCode();
            $this->_content_type = $remote_request->getJsonMimeType();
        } else {
            $curl_handle         = $request->getCurlHandle();
            $this->_raw_response = $response = $request->execute();
            $this->_status_code  = (int) $curl_handle->http_status_code;
            $this->_content_type = isset($curl_handle->response_headers, $curl_handle->response_headers['Content-Type'])
                ? $curl_handle->response_headers['Content-Type']
                : null
            ;

            if ($curl_handle->curl_error_code) {
                $this->_errors[$curl_handle->curl_error_code] = $curl_handle->curl_error_message;
            }
        }


        return $response;
    }

    private function _decryptResponseData(Syncee_Site $site, $data)
    {
        $crypt = $site->rsa->getCrypt();
        $crypt->loadKey($site->rsa->getPrivateKey());
        return $crypt->decrypt(base64_decode($data));
    }
}