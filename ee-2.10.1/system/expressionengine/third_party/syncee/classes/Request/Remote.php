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
    private $_ee_site_id;

    private $_site_id;

    private $_public_key;

    /**
     * @var Syncee_Site_Rsa
     */
    private $_site_rsa;

    private $_json_mime_type = 'text/javascript';

    public function __construct($method = null, $site_id = null, $public_key = null)
    {
        $this->_site_id = intval($site_id);

        $errors  = array();

        $message = '';
        $this->_site_rsa = $site_rsa = new Syncee_Site_Rsa();

        if (!$method || !$site_id || !$public_key) {
            if (!$method) {
                $errors[] = 'No method passed to request.';
            }

            if (!$site_id) {
                $errors[] = 'No site id passed to request.';
            }

            if (!$public_key) {
                $errors = 'No public key passed to request.';
            }

            $code = 400;
        } elseif ($public_key && !$site_rsa->getCrypt()->loadKey($public_key)) {
            $errors[] = 'Could not load public key properly.';

            $code = 400;
        } else {
            $code = 200;
        }

        $this->_public_key = $public_key;

        /**
         * @var $this_site Syncee_Site
         */
        $this_site         = Syncee_Site_Collection::getAllBySiteId($site_id)->filterByCondition('isCurrentLocal', true);
        $method_to_execute = 'get' . $method;

        if (!$this_site->allowsRemoteRequestFromIp(ee()->input->ip_address())) {
            $code = 403;
            $message = 'Request forbidden from this IP.';
        } elseif ($this_site->isEmptyRow()) {
            $code    = 404;
            $message = 'Unable to find local site object to instantiate.';
        } elseif (!method_exists($this, $method_to_execute)) {
            $code    = 404;
            $message = "Unknown method '$method'";
        }

        if ($code !== 200) {
            $this->_sendJsonResponse(array(), $errors, $code, $message);
        }

        $data = $this->$method_to_execute();

        $this->_sendJsonResponse($data, $errors, $code, $message);
    }

    public function getChannels()
    {
        $channels     = ee()->db->get('channels');
        $field_groups = ee()->db->get('field_groups');
        $fields       = ee()->db->get('channel_fields');

        $data = array();

        foreach ($channels->result_array() as $channel) {
            if (intval($channel['site_id']) !== $this->_site_id) {
                continue;
            }

            $channel['fields'] = array();

            foreach ($fields->result_array() as $field) {
                if ($field['group_id'] === $channel['field_group']) {
                    $channel['fields'][] = array(
                        'field_id'    => $field['field_id'],
                        'field_name'  => $field['field_name'],
                        'field_label' => $field['field_label'],
                        'field_type'  => $field['field_type']
                    );
                }
            }

            $channel['field_group_name'] = '';
            foreach ($field_groups->result_array() as $field_group) {
                if ($field_group['group_id'] === $channel['field_group']) {
                    $channel['field_group_name'] = $field_group['group_name'];
                    break;
                }
            }

            $data[] = $channel;
        }

        return $data;
    }

    private function _sendJsonResponse($data = array(), $errors = array(), $code = 200, $message = '', $meta = array())
    {
        header("Content-Type: {$this->_json_mime_type}", true, $code);

        $site_rsa = $this->_site_rsa;

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

		echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}