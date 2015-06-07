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

class Syncee_Request_Remote
{
    private $_site_id;

    private $_public_key;

    /**
     * @var Syncee_Site_Rsa
     */
    private $_site_rsa;

    private $_json_mime_type = 'text/javascript';

    /**
     * Handle the request and send JSON response
     * @param Syncee_Request_Remote_Entity_Interface $entity
     * @param null $site_id
     */
    public function __construct(Syncee_Request_Remote_Entity_Interface $entity = null, $site_id = null)
    {
        $this->_site_id = intval($site_id);

        $errors  = array();

        $message = '';
        $this->_site_rsa = $site_rsa = new Syncee_Site_Rsa();

        if (!$entity || !$site_id) {
            if (!$entity) {
                $errors[] = 'Missing/invalid entity passed to request.';
            }

            if (!$site_id) {
                $errors[] = 'Missing/invalid site id passed to request.';
            }

            $code = 400;
        } else {
            $code = 200;
        }

        /**
         * @var $this_site Syncee_Site
         */
        $this_site         = Syncee_Site_Collection::getAllBySiteId($site_id)->filterByCondition('isCurrentLocal', true);
        $public_key        = $this->_public_key = $this_site->rsa->getPublicKey();

        if (!$public_key) {
            $code    = 500;

            if ($this_site->isEmptyRow()) {
                $message = 'Could not find data for the requesting site.  Make sure you have pasted the source site data into the target site.';
            } else {
                $message = 'Could not find public key for the requesting site.';
            }
        } elseif (!$this_site->allowsRemoteRequestFromIp(ee()->input->ip_address())) {
            $code    = 403;
            $message = 'Request forbidden from this IP.';
        } elseif ($this_site->isEmptyRow()) {
            $code    = 404;
            $message = 'Unable to find local site object to instantiate.';
        }

        if ($code !== 200) {
            $this->_sendJsonResponse(array(), $errors, $code, $message);
        }

        $collection = $entity->getCollection();

        $this->_sendJsonResponse($collection->toArray(), $errors, $code, $message);
    }

    private function _sendJsonResponse($data = array(), $errors = array(), $code = 200, $message = '', $meta = array())
    {
        header("Content-Type: {$this->_json_mime_type}", true, $code);

        $site_rsa = $this->_site_rsa;
        $site_rsa->getCrypt()->loadKey($this->_public_key);

        $data = base64_encode($site_rsa->getCrypt()->encrypt(json_encode($data)));

		$data = array(
            'version' => Syncee_Upd::VERSION,
			'code'    => $code,
			'data'    => $data,
			'errors'  => $errors
		);

		if ($meta) {
			$data['meta'] = $meta;
		}

		if ($message) {
			$data['message'] = $message;
		}

		echo json_encode($data, SYNCEE_TEST_MODE ? JSON_PRETTY_PRINT : 0);
        exit;
    }
}