<?php

require_once dirname(__FILE__) . '/../_init.php';

class Syncee_Site
{
    const TABLE_NAME = 'syncee_site';

    private $_is_empty_row = false;

    private $_is_new = true;

    private $_primary = array('site_id', 'site_url');

    public $site_id;

    public $site_url;

    public $use_https;

    public $ip_whitelist;

    public function __construct(array $row = array(), $is_new = true)
    {
        foreach ($row as $key => $val) {
            $this->$key = $val;
        }

        if (empty($row)) {
            $this->_is_empty_row = true;
        }

        $this->_is_new = $is_new;
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

    public function save()
    {
        $table_properties  = ee()->db->list_fields(static::TABLE_NAME);
        $data              = array();

        foreach ($table_properties as $table_property) {
            $data[$table_property] = $this->$table_property;
        }

        if ($this->_is_new) {
            return ee()->db->insert(static::TABLE_NAME, $data);
        }

        $where = array();

        foreach ($this->_primary as $primary_key) {
            $where[$primary_key] = $this->$primary_key;
        }

        return ee()->db->update(static::TABLE_NAME, $data, $where);
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

//    public function __call($method, $args) {}

    public function __set($property, $value)
    {
        $this->$property     = $value;
        $this->_is_empty_row = false;
    }

//    public function __get($property) {}
}