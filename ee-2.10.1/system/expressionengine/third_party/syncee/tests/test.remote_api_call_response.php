<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Remote_Api_Call_Response extends Syncee_Unit_Test_Case_Abstract
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
    private $_remote_site;

    protected $_sql_file = '150525_1_ee_sync_FRESH_INSTALL.sql';

    protected $_truncation_setup_type = self::TRUNCATE_ALL;

    protected $_seed_data_files = array(
        'animals_channel'
    );

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_mcp             = new Syncee_Mcp();
        $this->_site_collection = Syncee_Site_Collection::getAllBySiteId(1);

        $current_local_site     = $this->_site_collection[0];
        $_SERVER['HTTP_HOST']   = parse_url($current_local_site->site_url, PHP_URL_HOST);
        $this->_remote_site     = $this->_site_collection->filterByCondition('isRemote', true);
    }

    public function testApiCallResponseHasData()
    {
        $mcp              = $this->_mcp;
        $remote_site      = $this->_remote_site;
        $response         = $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');

        $decoded_response = json_decode($response, true);

        $this->assertTrue(isset($decoded_response['data']) && $decoded_response['data'], 'Data in response exists and is non-empty: %s');
    }

    public function testMissingPublicKeyReturnsBadPublicKeyMessage()
    {
        $this->fail('Need to implement ' . __METHOD__);
    }
}