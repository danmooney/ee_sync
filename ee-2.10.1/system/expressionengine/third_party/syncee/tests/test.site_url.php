<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Site_Url extends Syncee_Unit_Test_Case_Abstract
{
    /**
     * @var Syncee_Mcp
     */
    private $_mcp;

    /**
     * @var Syncee_Site_Collection
     */
    private $_site_collection;

    /**
     * @var Syncee_Site
     */
    private $_current_local_site;

    protected $_sql_file = '150525_1_ee_sync_FRESH_INSTALL.sql';

    protected $_truncation_setup_type = self::TRUNCATE_SYNCEE_ONLY;

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_site_collection = Syncee_Site_Group::findByPk(1)->getSiteCollection();

        $current_local_site     = $this->_current_local_site = $this->_site_collection->filterByCondition('isLocal', true);
        $_SERVER['HTTP_HOST']   = parse_url($current_local_site->site_url, PHP_URL_HOST);
    }

    public function testSiteUrlSchemeChangePasses()
    {
        $current_local_site = $this->_current_local_site;

        $this->assertEqual(parse_url($current_local_site->getSiteUrl(), PHP_URL_SCHEME), 'http', 'Site\'s scheme is HTTP: %s');

        $current_local_site->use_https = true;

        $this->assertEqual(parse_url($current_local_site->getSiteUrl(), PHP_URL_SCHEME), 'https', 'Site\'s scheme is HTTPS: %s');

        $current_local_site->use_https = false;

        $this->assertEqual(parse_url($current_local_site->getSiteUrl(), PHP_URL_SCHEME), 'http', 'Site\'s scheme is HTTP: %s');

        $current_local_site->use_https = true;
        $current_local_site->save();

        $site_from_db = $this->_current_local_site;
        $this->assertEqual(parse_url($site_from_db->getSiteUrl(), PHP_URL_SCHEME), 'https', 'Site\'s scheme is HTTPS: %s');
    }
}