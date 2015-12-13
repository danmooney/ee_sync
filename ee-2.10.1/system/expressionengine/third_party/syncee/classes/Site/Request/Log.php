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

    const REQUEST_DIRECTION_BOTH     = 0;
    const REQUEST_DIRECTION_INBOUND  = 1;
    const REQUEST_DIRECTION_OUTBOUND = 2;

    const REQUEST_DIRECTION_DEFAULT  = self::REQUEST_DIRECTION_OUTBOUND;

    private $_request_directions = array(
        self::REQUEST_DIRECTION_INBOUND,
        self::REQUEST_DIRECTION_OUTBOUND
    );

    private $_request_direction = self::REQUEST_DIRECTION_DEFAULT;

    protected $_collection_model = 'Syncee_Site_Request_Log_Collection';

    protected $_primary_key_names = array('request_log_id');

    protected $_has_many_maps = array(
        'Syncee_Site_Synchronization_Profile_Request_Log'
    );

    protected $_has_many_maps_join_types = array(
        'Syncee_Site_Synchronization_Profile_Request_Log' => 'left'
    );

    /**
     * @var array
     */
    protected static $_cols;

    /**
     * @var Syncee_Site
     */
    public $site;

    /**
     * @var Syncee_Request_Remote_Entity_Abstract
     */
    public $request_entity;

    /**
     * @var Syncee_Site_Request_Log_Diagnosis
     */
    public $diagnosis;


    public function __construct(array $row = array(), $is_new = true)
    {
        parent::__construct($row, $is_new);

        $this->site           = Syncee_Site::findByPk($this->site_id);

        if ($this->site->isEmptyRow()) {
            $this->site = Syncee_Site::getRemoteSitePlaceholderInstance();
        }

        $this->request_entity = class_exists($this->entity_class_name)
            ? new $this->entity_class_name()
            : new Syncee_Request_Remote_Entity_Empty()
        ;

        $this->diagnosis = new Syncee_Site_Request_Log_Diagnosis($this);
    }

    public function requestHasAlreadyBeenMade()
    {
        return true;
    }

    public function setRequestDirection($request_direction)
    {
        $this->_request_direction = $request_direction;
    }

    public function getRawResponseWithDataDecoded()
    {
        $response              = new Syncee_Response($this, $this->site, $this->request_entity);
        $response_decoded      = $response->getResponseDecoded();

        if (!is_array($response_decoded)) {
            return false;
        }

        $response_data_decoded    = json_decode(Syncee_Helper::prettyPrintJson($response->getResponseDataDecoded()), true);
        $response_decoded['data'] = $response_data_decoded;

        return json_encode($response_decoded);
    }

    public function isSuccess()
    {
        $response     = new Syncee_Response($this, $this->site, $this->request_entity);
        $decoded_data = $response->getResponseDataDecoded();

        $success = (
            intval($this->code) === 200 &&
            !count($this->errors) &&
            is_array($decoded_data)
        );

        return $success;
    }

    public function __toString()
    {
        return (string) $this->raw_response;
    }
}