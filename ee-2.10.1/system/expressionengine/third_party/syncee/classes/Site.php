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

class Syncee_Site extends Syncee_ActiveRecord_Abstract
{
    const TABLE_NAME = 'syncee_site';

    protected static $_cols;

    protected $_primary_key_names = array('site_id');

    protected $_collection_model = 'Syncee_Site_Collection';

    protected $_has_many_map = 'Syncee_Site_Group_Map';

    /**
     * @var Syncee_Site_Rsa
     */
    public $rsa;

    /**
     * @return Syncee_Site_Collection
     */
    public static function getLocalSiteCollection()
    {
        return static::findAllByCondition(array('is_local' => true));
    }

    /**
     * @return Syncee_Site_Collection
     */
    public static function getRemoteSiteCollection()
    {
        return static::findAllByCondition(array('is_local' => false));
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

    public function __construct(array $row = array(), $is_new = true)
    {
        $this->rsa = new Syncee_Site_Rsa();

        parent::__construct($row, $is_new);
    }

    /**
     * Is this instance of Syncee_Site the current website?
     * Checks based on site_url
     * @return bool
     */
    public function isCurrentLocal()
    {
        return (
            $this->is_local && $this->getPrimaryKeyValues(true) // TODO
//            strpos('http://'  . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $this->site_url) === 0 ||
//            strpos('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $this->site_url) === 0
        );
    }

    public function isLocal()
    {
        return $this->is_local;
    }

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
        $ip_whitelist_exploded = array_filter(explode('|', $this->ip_whitelist));

        if (in_array($ip, $ip_whitelist_exploded)) {
            return $this;
        }

        $ip_whitelist_exploded[]  = $ip;
        $this->ip_whitelist = implode('|', $ip_whitelist_exploded);

        if (!$this->ip_whitelist) {
            $this->ip_whitelist = null;
        }

        return $this;
    }

    public function removeFromIpWhitelist($ip)
    {
        $ip_whitelist_exploded = array_filter(explode('|', $this->ip_whitelist));

        if (!in_array($ip, $ip_whitelist_exploded)) {
            return $this;
        }

        unset($ip_whitelist_exploded[array_search($ip, $ip_whitelist_exploded)]);

        $this->ip_whitelist = implode('|', $ip_whitelist_exploded);

        if (!$this->ip_whitelist) {
            $this->ip_whitelist = null;
        }

        return $this;
    }

    public function allowsRemoteRequestFromIp($ip)
    {
        if (!$this->ip_whitelist) {
            return true;
        }

        $ip_whitelist_exploded = array_filter(explode('|', $this->ip_whitelist));

        return in_array($ip, $ip_whitelist_exploded);
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
            'site_url'    => $this->site_url,
            'site_host'   => $this->site_host,
            'ee_site_id'  => $this->ee_site_id,
            'public_key'  => $this->public_key,
            'action_id'   => $this->action_id
        )));
    }

    public function save()
    {
        if ($this->_is_new) {
            $this->action_id   = ee()->db->select('action_id')->from('actions')->where('method', 'actionHandleRemoteDataApiCall')->get()->row('action_id');
            $this->public_key  = $this->rsa->getPublicKey();
            $this->private_key = $this->rsa->getPrivateKey();
        }

        return parent::save();
    }
}