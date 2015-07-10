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
 * Class Syncee_Site_Request_Log
 */
class Syncee_Site_Request_Log extends Syncee_ActiveRecord_Abstract implements Syncee_Request_Interface
{
    const TABLE_NAME = 'syncee_site_request_log';

    protected $_collection_model = 'Syncee_Site_Request_Log_Collection';

    protected $_primary_key_names = array('request_log_id');

    /**
     * @var Syncee_Site
     */
    public $site;

    /**
     * @var Syncee_Request_Remote_Entity_Abstract
     */
    public $request_entity;

    public function __construct(array $row = array(), $is_new = true)
    {
        parent::__construct($row, $is_new);

        $this->site           = Syncee_Site::findByPk($this->site_id);

        $this->request_entity = class_exists($this->entity_class_name)
            ? new $this->entity_class_name()
            : new Syncee_Request_Remote_Entity_Empty()
        ;
    }

    public function requestHasAlreadyBeenMade()
    {
        return true;
    }

    public function isSuccess()
    {
        $response     = new Syncee_Response($this, $this->site, $this->request_entity);
        $decoded_data = $response->getResponseDataDecoded();

        $success = (
            intval($this->code) === 200 &&
            empty($this->errors) &&
            is_array($decoded_data)
        );

        return $success;
    }

//    public function getDecryptedResponseData()
//    {
//        if (!is_string($this->raw_response)) {
//            return false;
//        }
//
//        $raw_response = $this->raw_response;
//
//        if (!($response_decoded = json_decode($raw_response, true))) {
//            return false;
//        }
//
//        if (!isset($response_decoded['data'])) {
//            return false;
//        }
//    }


    public function __toString()
    {
        return (string) $this->raw_response;
    }
}