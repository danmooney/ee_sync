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

    protected $_is_empty_row = false;

    protected $_is_new = true;

    protected $_primary = array('site_id', 'site_url');

    public $site_id;

    public $site_url;

    public $use_https;

    public $ip_whitelist;

    public $action_id;

    /**
     * @var Syncee_Site_Rsa
     */
    public $rsa;

    public function __construct(array $row = array(), $is_new = true)
    {
        $this->rsa = new Syncee_Site_Rsa();

        parent::__construct($row, $is_new);
    }

    public function isEmptyRow()
    {
        return $this->_is_empty_row;
    }

    /**
     * Is this instance of Syncee_Site the current website?
     * Checks based on site_url
     * @return bool
     */
    public function isCurrentLocal()
    {
        return (
            strpos('http://'  . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $this->site_url) === 0 ||
            strpos('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $this->site_url) === 0
        );
    }

    public function isRemote()
    {
        return !$this->isCurrentLocal();
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
}