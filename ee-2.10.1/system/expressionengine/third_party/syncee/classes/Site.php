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
 * Class Syncee_Site
 * @property Syncee_Site_Request_Log $last_request_log
 */
class Syncee_Site extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site';

    private static $_localhost_always_allowed = true;

    private $_default_ip_whitelist_separator = "\n";

    protected static $_cols;

    protected $_primary_key_names = array('site_id');

    protected $_collection_model = 'Syncee_Site_Collection';

    protected $_has_many_maps = array(
        'Syncee_Site_Group_Map',
        'Syncee_Site_Request_Log'
    );

    /**
     * @var Syncee_Site_Rsa
     */
    public $rsa;

    /**
     * @param Syncee_Paginator $paginator
     * @return Syncee_Site_Collection
     */
    public static function getLocalSiteCollection(Syncee_Paginator $paginator = null)
    {
        return static::findAllByCondition(array('is_local' => true), $paginator);
    }

    /**
     * @param Syncee_Paginator $paginator
     * @return Syncee_Site_Collection
     */
    public static function getRemoteSiteCollection(Syncee_Paginator $paginator = null)
    {
        return static::findAllByCondition(array('is_local' => false), $paginator);
    }

    public static function getByDecodingRemoteSiteSettingsPayload($remote_site_settings_payload)
    {
        $decoded_payload = @unserialize(base64_decode($remote_site_settings_payload));

        // if decoding payload fails, return empty instance
        if (!is_array($decoded_payload)) {
            return new static();
        }

        return new static($decoded_payload);
    }

    public static function setLocalhostAlwaysAllowed($localhost_always_allowed)
    {
        static::$_localhost_always_allowed = $localhost_always_allowed;
    }

    public function __construct(array $row = array(), $is_new = true)
    {
        $this->rsa              = new Syncee_Site_Rsa();
        parent::__construct($row, $is_new);
    }

    /**
     * @return bool
     */
    public function isLocal()
    {
        return $this->is_local;
    }

    /**
     * @return bool
     */
    public function isRemote()
    {
        return !$this->is_local;
    }

    public function getSiteUrl()
    {
        return preg_replace(
            '#^https?#',
            $this->use_https ? 'https' : 'http',
            $this->site_url
        );
    }

    public function addToIpWhitelist($ip)
    {
        $ip_whitelist_exploded = $this->getIpWhitelistExploded();

        if (in_array($ip, $ip_whitelist_exploded)) {
            return $this;
        }

        $ip_whitelist_exploded[] = $ip;
        $this->ip_whitelist      = implode($this->_getIpWhitelistNewlineCharacter(), $ip_whitelist_exploded);

        // if ip whitelist is empty, set to null
        if (!$this->ip_whitelist) {
            $this->ip_whitelist = null;
        }

        return $this;
    }

    public function removeFromIpWhitelist($ip)
    {
        $ip_whitelist_exploded = $this->getIpWhitelistExploded();

        if (!in_array($ip, $ip_whitelist_exploded)) {
            return $this;
        }

        unset($ip_whitelist_exploded[array_search($ip, $ip_whitelist_exploded)]);

        $this->ip_whitelist = implode($this->_getIpWhitelistNewlineCharacter(), $ip_whitelist_exploded);

        // if ip whitelist is empty, set to null
        if (!$this->ip_whitelist) {
            $this->ip_whitelist = null;
        }

        return $this;
    }

    public function allowsRemoteRequestFromIp($ip)
    {
        // if no ip whitelist and requests_from_remote_sites_enabled, then request is allowed
        if (!$this->ip_whitelist && $this->requests_from_remote_sites_enabled) {
            return true;
        }

        // localhost needs to be open always because we use HTTP protocol on the local instance (this is something we may change in the future)
        if (static::$_localhost_always_allowed && in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
            return true;
        }

        $ip_whitelist_exploded = $this->getIpWhitelistExploded();

        return in_array($ip, $ip_whitelist_exploded) && $this->requests_from_remote_sites_enabled;
    }

    /**
     * @return stdClass
     * @throws Syncee_Exception
     */
    public function getCorrespondingLocalEeSiteRow()
    {
        if (!$this->isLocal()) {
            throw new Syncee_Exception('Cannot fetch corresponding local ee site row in ' . __METHOD__ . ' since this row is remote');
        }

        $corresponding_local_ee_site = ee()->db->select('*')->from('sites')->where('site_id', $this->ee_site_id)->get()->row();

        if (!$corresponding_local_ee_site) {
            throw new Syncee_Exception('Could not find corresponding local EE site in ' . __METHOD__ . '.   Site id is ' . $this->ee_site_id);
        }

        return $corresponding_local_ee_site;
    }

    public function generateRemoteSiteSettingsPayload()
    {
        return base64_encode(serialize(array(
            'site_url'     => $this->site_url,
            'site_host'    => $this->site_host,
            'ee_site_id'   => $this->ee_site_id,
            'public_key'   => $this->public_key,
            'private_key'  => $this->private_key,
            'action_id'    => $this->action_id
        )));
    }

    public function save()
    {
        if ($this->isNew()) {
            $this->public_key  = $this->rsa->getPublicKey();

            $this->private_key = $this->rsa->getPrivateKey();

            if ($this->isLocal()) {
                // set action id on the row
                if (null === $this->action_id) {
                    $this->action_id = ee()->db->select('action_id')->from('actions')->where('method', 'actionHandleRemoteDataApiCall')->get()->row('action_id');
                }

                // if requests_from_remote_sites_enabled isn't set, set it to default false
                if (null === $this->requests_from_remote_sites_enabled) {
                    $this->requests_from_remote_sites_enabled = false;
                }
            }
        }

        // make sure ip whitelist is formatted properly
        if ($newline_character = $this->_getIpWhitelistNewlineCharacter()) {
            $ip_whitelist = array_map(function ($ip) {
                return trim($ip);
            }, explode($newline_character, $this->ip_whitelist));

            $this->ip_whitelist = implode($newline_character, $ip_whitelist);
        }

        return parent::save();
    }

    /**
     * @return array
     */
    public function getIpWhitelistExploded()
    {
        return array_filter(explode($this->_getIpWhitelistNewlineCharacter(), $this->ip_whitelist));
    }

    /**
     * Override Syncee_ActiveRecord_Abstract::__set in order to dynamically get title
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if ($property === 'title' && $this->isLocal()) {
            return $this->getCorrespondingLocalEeSiteRow()->site_label;
        } elseif ($property === 'last_request_log') {
            if (!isset($this->last_request_log)) {
                $request_log_collection = Syncee_Site_Request_Log::findAllByCondition(
                    $this->getPrimaryKeyNamesValuesMap(),
                    new Syncee_Paginator_Site_Request_Log_Last(array(), new Syncee_Mcp_Empty())
                );

                $this->last_request_log = isset($request_log_collection[0])
                    ? $request_log_collection[0]
                    : new Syncee_Site_Request_Log()
                ;
            }

            return $this->_non_col_val_mapping['last_request_log'];
        }

        return parent::__get($property);
    }

    /**
     * Override Syncee_ActiveRecord_Abstract::__set in order to dynamically set site_host if site_url is being set
     * @param $property
     * @param $value
     * @return $this
     */
    public function __set($property, $value)
    {
        $return_value = parent::__set($property, $value);

        if ($property === 'site_url') {
            $this->site_host = parse_url($this->site_url, PHP_URL_HOST);
        }

        return $return_value;
    }

    private function _getIpWhitelistNewlineCharacter()
    {
        preg_match("#\r\n|[\r\n]#", $this->ip_whitelist, $matches);

        return isset($matches[0])
            ? $matches[0]
            : $this->_default_ip_whitelist_separator
        ;
    }
}